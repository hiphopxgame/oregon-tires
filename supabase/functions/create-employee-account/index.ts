import { serve } from "https://deno.land/std@0.190.0/http/server.ts";
import { createClient } from "https://esm.sh/@supabase/supabase-js@2.38.5";
import { Resend } from "npm:resend@2.0.0";

const corsHeaders = {
  "Access-Control-Allow-Origin": "*",
  "Access-Control-Allow-Headers":
    "authorization, x-client-info, apikey, content-type",
};

interface CreateAccountRequest {
  email: string;
  employeeName: string;
  temporaryPassword?: string;
}

const handler = async (req: Request): Promise<Response> => {
  // Handle CORS preflight requests
  if (req.method === "OPTIONS") {
    return new Response(null, { headers: corsHeaders });
  }

  try {
    const { email, employeeName, temporaryPassword = "TempPass123!" }: CreateAccountRequest = await req.json();

    // Create Supabase admin client
    const supabaseAdmin = createClient(
      Deno.env.get("SUPABASE_URL") ?? "",
      Deno.env.get("SUPABASE_SERVICE_ROLE_KEY") ?? "",
      {
        auth: {
          autoRefreshToken: false,
          persistSession: false
        }
      }
    );

    // Create the user account
    const { data: authData, error: authError } = await supabaseAdmin.auth.admin.createUser({
      email: email,
      password: temporaryPassword,
      email_confirm: true,
      user_metadata: {
        full_name: employeeName
      }
    });

    if (authError) {
      console.error('Error creating auth user:', authError);
      throw authError;
    }

    console.log('User created successfully:', authData.user.id);

    // Initialize Resend for sending welcome email
    const resend = new Resend(Deno.env.get("RESEND_API_KEY"));
    
    if (resend) {
      try {
        const emailResponse = await resend.emails.send({
          from: "Oregon Tires Auto Care <onboarding@resend.dev>",
          to: [email],
          subject: "Welcome to Oregon Tires - Your Account Details",
          html: `
            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
              <h2 style="color: #007030;">Welcome to Oregon Tires Auto Care!</h2>
              <p>Hello ${employeeName},</p>
              <p>An admin has created an account for you to access the Oregon Tires dashboard.</p>
              
              <div style="background-color: #f5f5f5; padding: 15px; border-radius: 5px; margin: 20px 0;">
                <h3>Your Login Details:</h3>
                <p><strong>Email:</strong> ${email}</p>
                <p><strong>Temporary Password:</strong> ${temporaryPassword}</p>
              </div>
              
              <p>Please follow these steps:</p>
              <ol>
                <li>Go to the admin login page</li>
                <li>Use the credentials above to sign in</li>
                <li>Change your password immediately after first login</li>
              </ol>
              
              <p style="color: #d32f2f; font-weight: bold;">
                ⚠️ Important: Please change your password as soon as you log in for security purposes.
              </p>
              
              <p>If you have any questions, please contact your administrator.</p>
              
              <p>Best regards,<br>Oregon Tires Auto Care Team</p>
            </div>
          `,
        });

        console.log("Welcome email sent successfully:", emailResponse);
      } catch (emailError) {
        console.error("Error sending welcome email:", emailError);
        // Don't fail the whole process if email fails
      }
    }

    return new Response(
      JSON.stringify({ 
        success: true, 
        message: "Employee account created successfully",
        userId: authData.user.id 
      }),
      {
        status: 200,
        headers: {
          "Content-Type": "application/json",
          ...corsHeaders,
        },
      }
    );
  } catch (error: any) {
    console.error("Error in create-employee-account function:", error);
    return new Response(
      JSON.stringify({ 
        error: error.message,
        success: false 
      }),
      {
        status: 400,
        headers: { "Content-Type": "application/json", ...corsHeaders },
      }
    );
  }
};

serve(handler);