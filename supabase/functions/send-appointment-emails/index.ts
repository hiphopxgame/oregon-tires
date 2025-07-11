import { serve } from "https://deno.land/std@0.190.0/http/server.ts";
import { createClient } from 'https://esm.sh/@supabase/supabase-js@2';
import { Resend } from "npm:resend@2.0.0";

const resend = new Resend(Deno.env.get("RESEND_API_KEY"));
const supabaseUrl = Deno.env.get('SUPABASE_URL')!;
const supabaseServiceKey = Deno.env.get('SUPABASE_SERVICE_ROLE_KEY')!;

const supabase = createClient(supabaseUrl, supabaseServiceKey);

const corsHeaders = {
  "Access-Control-Allow-Origin": "*",
  "Access-Control-Allow-Headers": "authorization, x-client-info, apikey, content-type",
};

interface EmailRequest {
  type: 'appointment_created' | 'appointment_assigned' | 'appointment_completed';
  appointmentId: string;
  employeeEmail?: string;
}

const handler = async (req: Request): Promise<Response> => {
  if (req.method === "OPTIONS") {
    return new Response(null, { headers: corsHeaders });
  }

  try {
    const { type, appointmentId, employeeEmail }: EmailRequest = await req.json();

    // Fetch appointment details
    const { data: appointment, error: appointmentError } = await supabase
      .from('oregon_tires_appointments')
      .select(`
        *,
        assigned_employee:oregon_tires_employees(name, email)
      `)
      .eq('id', appointmentId)
      .single();

    if (appointmentError || !appointment) {
      throw new Error('Appointment not found');
    }

    console.log('Processing email for appointment:', appointment.id, 'Type:', type);

    if (type === 'appointment_created') {
      // Send confirmation email to customer
      await resend.emails.send({
        from: "Oregon Tires <appointments@oregontires.com>",
        to: [appointment.email],
        subject: "Appointment Confirmation - Oregon Tires",
        html: `
          <h2>Appointment Confirmation</h2>
          <p>Dear ${appointment.first_name} ${appointment.last_name},</p>
          <p>Thank you for booking an appointment with Oregon Tires!</p>
          
          <h3>Appointment Details:</h3>
          <ul>
            <li><strong>Service:</strong> ${appointment.service}</li>
            <li><strong>Date:</strong> ${appointment.preferred_date}</li>
            <li><strong>Time:</strong> ${appointment.preferred_time}</li>
            <li><strong>Location:</strong> ${appointment.service_location === 'mobile' ? 'Mobile Service' : 'Our Shop'}</li>
            ${appointment.tire_size ? `<li><strong>Tire Size:</strong> ${appointment.tire_size}</li>` : ''}
            ${appointment.license_plate ? `<li><strong>Vehicle:</strong> ${appointment.license_plate}</li>` : ''}
          </ul>
          
          ${appointment.message ? `<p><strong>Notes:</strong> ${appointment.message}</p>` : ''}
          
          <p>We'll contact you soon to confirm the details and let you know when a technician has been assigned.</p>
          <p>If you have any questions, please don't hesitate to contact us.</p>
          
          <p>Best regards,<br>The Oregon Tires Team</p>
        `,
      });

    } else if (type === 'appointment_assigned' && appointment.assigned_employee?.email) {
      // Send assignment notification to employee
      await resend.emails.send({
        from: "Oregon Tires <assignments@oregontires.com>",
        to: [appointment.assigned_employee.email],
        subject: "New Appointment Assignment",
        html: `
          <h2>New Appointment Assignment</h2>
          <p>Hello ${appointment.assigned_employee.name},</p>
          <p>You have been assigned to a new appointment:</p>
          
          <h3>Customer Information:</h3>
          <ul>
            <li><strong>Name:</strong> ${appointment.first_name} ${appointment.last_name}</li>
            <li><strong>Phone:</strong> ${appointment.phone || 'Not provided'}</li>
            <li><strong>Email:</strong> ${appointment.email}</li>
          </ul>
          
          <h3>Appointment Details:</h3>
          <ul>
            <li><strong>Service:</strong> ${appointment.service}</li>
            <li><strong>Date:</strong> ${appointment.preferred_date}</li>
            <li><strong>Time:</strong> ${appointment.preferred_time}</li>
            <li><strong>Location:</strong> ${appointment.service_location === 'mobile' ? 'Mobile Service' : 'Our Shop'}</li>
            ${appointment.tire_size ? `<li><strong>Tire Size:</strong> ${appointment.tire_size}</li>` : ''}
            ${appointment.license_plate ? `<li><strong>Vehicle:</strong> ${appointment.license_plate}</li>` : ''}
          </ul>
          
          ${appointment.service_location === 'mobile' && appointment.customer_address ? `
            <h3>Service Address:</h3>
            <p>${appointment.customer_address}<br>
            ${appointment.customer_city}, ${appointment.customer_state} ${appointment.customer_zip}</p>
          ` : ''}
          
          ${appointment.message ? `<p><strong>Customer Notes:</strong> ${appointment.message}</p>` : ''}
          
          <p>Please log into the admin panel to view more details and update the appointment status.</p>
          
          <p>Best regards,<br>Oregon Tires Management</p>
        `,
      });

    } else if (type === 'appointment_completed') {
      // Send completion notification to customer
      await resend.emails.send({
        from: "Oregon Tires <service@oregontires.com>",
        to: [appointment.email],
        subject: "Service Completed - Oregon Tires",
        html: `
          <h2>Service Completed</h2>
          <p>Dear ${appointment.first_name} ${appointment.last_name},</p>
          <p>Your service appointment has been completed!</p>
          
          <h3>Service Details:</h3>
          <ul>
            <li><strong>Service:</strong> ${appointment.service}</li>
            <li><strong>Date:</strong> ${appointment.preferred_date}</li>
            <li><strong>Technician:</strong> ${appointment.assigned_employee?.name || 'Oregon Tires Team'}</li>
            ${appointment.tire_size ? `<li><strong>Tire Size:</strong> ${appointment.tire_size}</li>` : ''}
            ${appointment.license_plate ? `<li><strong>Vehicle:</strong> ${appointment.license_plate}</li>` : ''}
          </ul>
          
          <p>Thank you for choosing Oregon Tires! We hope you're satisfied with our service.</p>
          <p>If you have any questions or feedback, please don't hesitate to contact us.</p>
          
          <p>We look forward to serving you again in the future!</p>
          
          <p>Best regards,<br>The Oregon Tires Team</p>
        `,
      });
    }

    return new Response(JSON.stringify({ success: true }), {
      status: 200,
      headers: { ...corsHeaders, "Content-Type": "application/json" },
    });

  } catch (error: any) {
    console.error("Error sending email:", error);
    return new Response(
      JSON.stringify({ error: error.message }),
      {
        status: 500,
        headers: { ...corsHeaders, "Content-Type": "application/json" },
      }
    );
  }
};

serve(handler);