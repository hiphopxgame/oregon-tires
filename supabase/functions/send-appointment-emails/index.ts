
import { serve } from "https://deno.land/std@0.190.0/http/server.ts";
import { createClient } from 'https://esm.sh/@supabase/supabase-js@2';
import { Resend } from "npm:resend@2.0.0";

const supabaseUrl = Deno.env.get('SUPABASE_URL')!;
const supabaseServiceKey = Deno.env.get('SUPABASE_SERVICE_ROLE_KEY')!;
const resendApiKey = Deno.env.get('RESEND_API_KEY')!;

const supabase = createClient(supabaseUrl, supabaseServiceKey);
const resend = new Resend(resendApiKey);

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

    // Fetch appointment details with formatted service name
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

    // Format the service name
    const { data: formattedServiceData } = await supabase
      .rpc('format_service_name', { service_slug: appointment.service });
    
    const formattedServiceName = formattedServiceData || appointment.service;


    console.log('Processing email for appointment:', appointment.id, 'Type:', type);

    let emailResponse;

    if (type === 'appointment_created') {
      // Send confirmation email to customer
      const subject = "Appointment Confirmation - Oregon Tires";
      const emailBody = `
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
          <h2 style="color: #007030;">Appointment Confirmation</h2>
          <p>Dear ${appointment.first_name} ${appointment.last_name},</p>
          <p>Thank you for booking an appointment with Oregon Tires!</p>
          
          <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <h3 style="color: #007030; margin-top: 0;">Appointment Details:</h3>
            <ul style="line-height: 1.6;">
              <li><strong>Service:</strong> ${formattedServiceName}</li>
              <li><strong>Date:</strong> ${appointment.preferred_date}</li>
              <li><strong>Time:</strong> ${appointment.preferred_time}</li>
              <li><strong>Location:</strong> ${appointment.service_location === 'mobile' ? 'Mobile Service' : 'Our Shop'}</li>
              ${appointment.tire_size ? `<li><strong>Tire Size:</strong> ${appointment.tire_size}</li>` : ''}
              ${appointment.license_plate ? `<li><strong>Vehicle:</strong> ${appointment.license_plate}</li>` : ''}
            </ul>
          </div>
          
          ${appointment.message ? `<p><strong>Notes:</strong> ${appointment.message}</p>` : ''}
          
          <p>We'll contact you soon to confirm the details and let you know when a technician has been assigned.</p>
          <p>If you have any questions, please don't hesitate to contact us.</p>
          
          <p>Best regards,<br><strong>The Oregon Tires Team</strong></p>
          
          <hr style="margin: 30px 0; border: none; border-top: 1px solid #e0e0e0;">
          <p style="font-size: 12px; color: #666;">
            Oregon Tires - Professional Tire Services<br>
            Visit us at: <a href="https://oregon.tires" style="color: #007030;">oregon.tires</a>
          </p>
        </div>
      `;
      
      emailResponse = await resend.emails.send({
        from: 'Oregon Tires <appointments@oregon.tires>',
        to: [appointment.email],
        subject: subject,
        html: emailBody,
      });

      // Log the email
      await supabase.from('oregon_tires_email_logs').insert({
        email_type: type,
        recipient_email: appointment.email,
        recipient_name: `${appointment.first_name} ${appointment.last_name}`,
        recipient_type: 'customer',
        subject: subject,
        body: emailBody,
        appointment_id: appointmentId,
        resend_message_id: emailResponse.data?.id || null
      });

    } else if (type === 'appointment_assigned' && appointment.assigned_employee?.email) {
      // Send assignment notification to employee
      const subject = "New Appointment Assignment - Oregon Tires";
      const emailBody = `
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
          <h2 style="color: #007030;">New Appointment Assignment</h2>
          <p>Hello ${appointment.assigned_employee.name},</p>
          <p>You have been assigned to a new appointment:</p>
          
          <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <h3 style="color: #007030; margin-top: 0;">Customer Information:</h3>
            <ul style="line-height: 1.6;">
              <li><strong>Name:</strong> ${appointment.first_name} ${appointment.last_name}</li>
              <li><strong>Phone:</strong> ${appointment.phone || 'Not provided'}</li>
              <li><strong>Email:</strong> ${appointment.email}</li>
            </ul>
            
            <h3 style="color: #007030;">Appointment Details:</h3>
            <ul style="line-height: 1.6;">
              <li><strong>Service:</strong> ${formattedServiceName}</li>
              <li><strong>Date:</strong> ${appointment.preferred_date}</li>
              <li><strong>Time:</strong> ${appointment.preferred_time}</li>
              <li><strong>Location:</strong> ${appointment.service_location === 'mobile' ? 'Mobile Service' : 'Our Shop'}</li>
              ${appointment.tire_size ? `<li><strong>Tire Size:</strong> ${appointment.tire_size}</li>` : ''}
              ${appointment.license_plate ? `<li><strong>Vehicle:</strong> ${appointment.license_plate}</li>` : ''}
            </ul>
          </div>
          
          ${appointment.service_location === 'mobile' && appointment.customer_address ? `
            <div style="background-color: #fff3cd; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #ffc107;">
              <h3 style="color: #856404; margin-top: 0;">Service Address:</h3>
              <p style="margin: 0;">${appointment.customer_address}<br>
              ${appointment.customer_city}, ${appointment.customer_state} ${appointment.customer_zip}</p>
            </div>
          ` : ''}
          
          ${appointment.message ? `<p><strong>Customer Notes:</strong> ${appointment.message}</p>` : ''}
          
          <p>Please log into the admin panel to view more details and update the appointment status.</p>
          
          <p>Best regards,<br><strong>Oregon Tires Management</strong></p>
          
          <hr style="margin: 30px 0; border: none; border-top: 1px solid #e0e0e0;">
          <p style="font-size: 12px; color: #666;">
            Access the admin panel: <a href="https://oregon.tires/admin" style="color: #007030;">oregon.tires/admin</a>
          </p>
        </div>
      `;
      
      emailResponse = await resend.emails.send({
        from: 'Oregon Tires <assignments@oregon.tires>',
        to: [appointment.assigned_employee.email],
        subject: subject,
        html: emailBody,
      });

      // Log the email
      await supabase.from('oregon_tires_email_logs').insert({
        email_type: type,
        recipient_email: appointment.assigned_employee.email,
        recipient_name: appointment.assigned_employee.name,
        recipient_type: 'employee',
        subject: subject,
        body: emailBody,
        appointment_id: appointmentId,
        resend_message_id: emailResponse.data?.id || null
      });

    } else if (type === 'appointment_completed') {
      // Send completion notification to customer
      const durationText = appointment.actual_duration_minutes 
        ? `${appointment.actual_duration_minutes} minutes`
        : 'N/A';
        
      const subject = "Service Completed - Oregon Tires";
      const emailBody = `
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
          <h2 style="color: #007030;">✅ Your Service is Complete!</h2>
          <p>Dear ${appointment.first_name} ${appointment.last_name},</p>
          <p>Great news! Your tire service appointment has been completed successfully.</p>
          
          <div style="background-color: #d4edda; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #28a745;">
            <h3 style="color: #155724; margin-top: 0;">📋 Service Summary:</h3>
            <ul style="line-height: 1.8;">
              <li><strong>Service:</strong> ${formattedServiceName}</li>
              <li><strong>Date:</strong> ${appointment.preferred_date}</li>
              <li><strong>Technician:</strong> ${appointment.assigned_employee?.name || 'Oregon Tires Team'}</li>
              <li><strong>Service Duration:</strong> ${durationText}</li>
              ${appointment.tire_size ? `<li><strong>Tire Size:</strong> ${appointment.tire_size}</li>` : ''}
              ${appointment.license_plate ? `<li><strong>Vehicle:</strong> ${appointment.license_plate}</li>` : ''}
            </ul>
          </div>
          
          <div style="background-color: #fff3cd; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #ffc107;">
            <p style="margin: 0; color: #856404;"><strong>Thank you for being an Oregon Tires customer!</strong></p>
          </div>
          
          <p>We appreciate your business and trust in our professional tire services. Your satisfaction is our priority, and we're glad we could take care of your vehicle today.</p>
          
          <p><strong>We'd love to see you again!</strong> Whether you need tire rotation, balancing, repairs, or new tires, Oregon Tires is here to keep you safely on the road.</p>
          
          <p>If you have any questions about the service performed or need assistance in the future, please don't hesitate to contact us.</p>
          
          <p>Safe travels!<br><strong>The Oregon Tires Team</strong></p>
          
          <hr style="margin: 30px 0; border: none; border-top: 1px solid #e0e0e0;">
          <p style="font-size: 12px; color: #666;">
            Oregon Tires - Professional Tire Installation, Repair & Automotive Services<br>
            We speak Spanish and English! | Visit us at: <a href="https://oregon.tires" style="color: #007030;">oregon.tires</a>
          </p>
        </div>
      `;
      
      emailResponse = await resend.emails.send({
        from: 'Oregon Tires <service@oregon.tires>',
        to: [appointment.email],
        subject: subject,
        html: emailBody,
      });

      // Log the email
      await supabase.from('oregon_tires_email_logs').insert({
        email_type: type,
        recipient_email: appointment.email,
        recipient_name: `${appointment.first_name} ${appointment.last_name}`,
        recipient_type: 'customer',
        subject: subject,
        body: emailBody,
        appointment_id: appointmentId,
        resend_message_id: emailResponse.data?.id || null
      });
    }

    console.log('Email sent successfully via Resend:', emailResponse);

    return new Response(JSON.stringify({ 
      success: true, 
      messageId: emailResponse?.data?.id 
    }), {
      status: 200,
      headers: { ...corsHeaders, "Content-Type": "application/json" },
    });

  } catch (error: any) {
    console.error("Error sending email via Resend:", error);
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
