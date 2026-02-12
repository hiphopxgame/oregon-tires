import { serve } from "https://deno.land/std@0.190.0/http/server.ts";
import { createClient } from "https://esm.sh/@supabase/supabase-js@2.38.5";
import { Resend } from "npm:resend@2.0.0";

const corsHeaders = {
  "Access-Control-Allow-Origin": "*",
  "Access-Control-Allow-Headers":
    "authorization, x-client-info, apikey, content-type, x-supabase-client-platform, x-supabase-client-platform-version, x-supabase-client-runtime, x-supabase-client-runtime-version",
};

interface CreateAccountRequest {
  email: string;
  employeeName: string;
}

function generateRandomPassword(length = 12): string {
  const upper = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
  const lower = "abcdefghijklmnopqrstuvwxyz";
  const digits = "0123456789";
  const special = "!@#$%&*";
  const all = upper + lower + digits + special;
  
  // Ensure at least one of each type
  let password = "";
  password += upper[Math.floor(Math.random() * upper.length)];
  password += lower[Math.floor(Math.random() * lower.length)];
  password += digits[Math.floor(Math.random() * digits.length)];
  password += special[Math.floor(Math.random() * special.length)];
  
  for (let i = password.length; i < length; i++) {
    password += all[Math.floor(Math.random() * all.length)];
  }
  
  // Shuffle
  return password.split("").sort(() => Math.random() - 0.5).join("");
}

const handler = async (req: Request): Promise<Response> => {
  if (req.method === "OPTIONS") {
    return new Response(null, { headers: corsHeaders });
  }

  try {
    // Authenticate the caller
    const authHeader = req.headers.get("Authorization");
    if (!authHeader?.startsWith("Bearer ")) {
      return new Response(
        JSON.stringify({ error: "Unauthorized", success: false }),
        { status: 401, headers: { "Content-Type": "application/json", ...corsHeaders } }
      );
    }

    const callerClient = createClient(
      Deno.env.get("SUPABASE_URL") ?? "",
      Deno.env.get("SUPABASE_ANON_KEY") ?? "",
      { global: { headers: { Authorization: authHeader } } }
    );

    // Verify the caller's identity
    const { data: userData, error: userError } = await callerClient.auth.getUser();
    if (userError || !userData?.user) {
      console.error("Auth error:", userError);
      return new Response(
        JSON.stringify({ error: "Unauthorized", success: false }),
        { status: 401, headers: { "Content-Type": "application/json", ...corsHeaders } }
      );
    }

    // Verify the caller is an admin
    const { data: isAdmin, error: adminError } = await callerClient.rpc("is_admin");
    if (adminError || !isAdmin) {
      console.error("Admin check failed:", adminError);
      return new Response(
        JSON.stringify({ error: "Admin privileges required", success: false }),
        { status: 403, headers: { "Content-Type": "application/json", ...corsHeaders } }
      );
    }

    const { email, employeeName }: CreateAccountRequest = await req.json();
    const temporaryPassword = generateRandomPassword();

    console.log("Creating account for:", email, "name:", employeeName);

    // Create Supabase admin client for user creation
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

    // Check if user already exists
    const { data: existingUsers } = await supabaseAdmin.auth.admin.listUsers();
    const existingUser = existingUsers?.users?.find(u => u.email === email);

    let userId: string;

    if (existingUser) {
      console.log("User already exists, resetting password:", existingUser.id);
      // Update existing user's password
      const { error: updateError } = await supabaseAdmin.auth.admin.updateUserById(existingUser.id, {
        password: temporaryPassword,
        user_metadata: { full_name: employeeName }
      });
      if (updateError) {
        console.error('Error updating user:', updateError);
        throw updateError;
      }
      userId = existingUser.id;
    } else {
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
      userId = authData.user.id;
    }

    console.log('User ready:', userId);

    // Fetch admin notification email from settings
    let adminNotificationEmail = 'tyronenorris@gmail.com';
    try {
      const { data: settingData } = await supabaseAdmin.from('oretir_settings').select('setting_value').eq('setting_key', 'admin_email').maybeSingle();
      if (settingData?.setting_value) adminNotificationEmail = settingData.setting_value;
    } catch (e) { console.error('Could not fetch admin email setting:', e); }

    // Send welcome email with credentials
    try {
      const resend = new Resend(Deno.env.get("RESEND_API_KEY"));
      
      const emailResponse = await resend.emails.send({
        from: "Oregon Tires Auto Care <staff@oregon.tires>",
        to: [email],
        cc: [adminNotificationEmail],
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
      // Don't fail the whole request if email fails - account is still created
    }

    return new Response(
      JSON.stringify({ 
        success: true, 
        message: "Employee account created successfully",
        userId: userId 
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
