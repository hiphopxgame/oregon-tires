import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Download, Database, FileText, ArrowLeft, Package, FolderArchive } from 'lucide-react';
import { Link } from 'react-router-dom';
import { useState } from 'react';
import JSZip from 'jszip';

const MIGRATION_FILES = [
  "20250613064349-4388eb16-b9ef-474d-81c2-a7aae0caaa6a.sql",
  "20250702133318-9fac1b91-b8ad-4e3e-af47-3c32017c0a4c.sql",
  "20250703021154-7d894e8c-0194-4a61-8444-276fef65fadb.sql",
  "20250703023829-464f32ef-e2b0-4fa4-b7f1-dfe7c9bec5cb.sql",
  "20250703171734-d841f990-90c0-43b1-8ab0-58b2bc14478d.sql",
  "20250707010936-5adb668d-a889-4bd5-a6da-c591c0d641b0.sql",
  "20250707010958-6df02fb2-657e-4fd9-b0d9-15b49e68c390.sql",
  "20250707011155-db1a92bc-cb07-4e16-acaf-c49f88ca2356.sql",
  "20250709063909-6a265458-ef81-423d-b34f-1f75d356a746.sql",
  "20250710100825-ad6dfe47-3293-4dff-8767-44cb89d36a7a.sql",
  "20250710103341-e12b7af0-5e09-43d3-a432-2ac78027a321.sql",
  "20250710200649-a2718f13-01d2-49f3-a9d9-ed720eccb95b.sql",
  "20250710201509-50e0aa39-4c47-4e0a-879b-57d1d5d71ded.sql",
  "20250710202010-7281392d-4611-4b86-863d-291ec64f1d71.sql",
  "20250710222351-36ebdaf5-cc54-4b0d-a391-74aa1ffdbc49.sql",
  "20250711041713-9d801d72-aacf-49d4-bcc5-52bfeca8f5c4.sql",
  "20250711055620-e4dd3fc5-ee19-4548-ac71-029da0bdea6c.sql",
  "20250714082859-0b4296ce-fa1f-4e70-b61b-2af840eb6305.sql",
  "20250717100221-42e060c9-cce2-4e9f-88f7-c63a61a48489.sql",
  "20250721144424-5eecc965-4481-47eb-9cf3-779cfa887a67.sql",
  "20250725061147-bab42681-4329-4e6e-8874-cc6bd263f437.sql",
  "20250725062747-631d942c-9d51-492b-9927-fffc8a94f1b1.sql",
  "20250725063712-19b265a7-f5c6-4f8b-9e55-73d6524a871c.sql",
  "20250725081631-9240ebff-b99e-47bf-8392-4b2b86cebe93.sql",
  "20250725094514-d02be985-71db-4b66-bd98-d7756b5fe6a5.sql",
  "20250725100818-eb622e50-9932-4008-b688-f1cc944b5259.sql",
  "20250725212132-f799578c-d58c-4dbe-8d74-8f405d42cc4b.sql",
  "20250725212238-5a64bd34-60c8-4c7c-b610-c77b84387e67.sql",
  "20250725212309-3b3b315c-1f5d-4e45-8738-bf41faf38ac3.sql",
  "20250726002243-4ed2fd44-c4a8-4c43-a470-3d7230eb0687.sql",
  "20250726002322-f957f09c-8ef2-4a14-8865-051efb9ed3f2.sql",
  "20250726165900-cce01117-2384-4364-8c6e-7bbe69243b52.sql",
  "20250726175309-e55f16b5-7954-433a-992d-daaa98d57979.sql",
  "20250726180552-eb83bf42-17d2-42d3-b059-efca20fb153d.sql",
  "20250726191436-0c5cecf9-6caf-4479-a044-3edfdc20d552.sql",
  "20250726192359-f7cd8e53-3fdb-4edb-93d5-5a3e47168496.sql",
  "20250726192747-c0719ed8-b951-4612-b569-3469b3de4669.sql",
  "20250726202843-ab52aa30-9583-4b19-a3a6-d87dd932f079.sql",
  "20250727050143-17202924-fa43-4b8d-90df-e99a4bbe5fd8.sql",
  "20250727050433-6a8854d8-2dd4-40cd-848b-77870c079b48.sql",
  "20250727070032-fbce32e6-d63c-4c7c-ba5a-2934f63d4b84.sql",
  "20250727072235-d2fbe011-ecf7-4a31-bc06-47ef08a3b633.sql",
  "20250727083816-8238a970-354b-4d4a-b7fa-d9786b0fbf13.sql",
  "20250727234912-273036d5-daee-48b2-bccd-1cc4a2eeed01.sql",
  "20250728002549-de32285e-bba4-4043-a360-78f48efc4f20.sql",
  "20250728002804-4b0a2d35-ecf4-4175-a47e-a5eea156d5a9.sql",
  "20250728012314-ab681ae8-86e6-42eb-9dd2-a0834d947777.sql",
  "20250813142738_2b9390b5-2fb7-4449-879e-929840146d95.sql",
  "20250813143045_ff33a458-83bd-403c-8ffa-0ffcaa216ef1.sql",
  "20250821224355_c138e7af-f63d-49ff-9fbb-feda76360f47.sql",
  "20250821224647_274a0cd4-af34-4a0e-aaf8-ebedaf738753.sql",
  "20250826205650_955dcf1c-dd12-44bc-b214-348160bbd77b.sql",
  "20250826205859_f22af4b9-a9cd-485b-9215-0af9e4af3b11.sql",
  "20250827043255_54f250df-96a5-44df-8f95-a47d12b7e07f.sql",
  "20250827043334_3d0f9452-bb82-459c-ae2a-dd67a6b455d8.sql",
  "20250828200903_6b3bfc61-f1cd-4344-865b-bd68facb3c74.sql",
  "20250828200947_098b625b-ea12-4014-bb1b-aeb140abcaed.sql",
  "20250828201352_6c351bec-adbd-4db8-8f65-e5185ec53707.sql",
  "20250828201541_bab48b09-64da-4e23-ae13-07f9a2b94380.sql",
  "20250828202033_01827519-639c-4da7-8f72-436adc07fd8a.sql",
  "20250909171122_23a1935b-f762-4c9b-8d1e-9f137668c74e.sql",
  "20250910013116_1aee9116-eb12-453c-b646-212be2c09d12.sql",
  "20250910025150_de8f20e7-65ee-47e9-95f5-19e95525f829.sql",
  "20250910041056_5d05db18-e884-43e1-8e40-3b74bfa1d9dd.sql",
  "20250910050508_811c04ba-9b28-4343-bf5c-3ab8928d0a0b.sql",
  "20250910050536_4598f75a-b537-4c3c-a021-809605212e84.sql",
  "20250912044704_cdef16e4-72eb-4fd0-aae2-cf95c267865c.sql",
  "20250912044730_0666f3e5-e5ac-47ad-ad67-03f2692d3baa.sql",
  "20250912052518_26564c75-d986-40a0-be59-3caafe8ba40f.sql",
  "20250912052705_b23c6059-1f61-4410-9161-5013cefedda3.sql",
  "20250912052809_e0f9b22a-7686-49ed-9ec0-2f25d5397f88.sql",
  "20250912052838_e221b336-9c92-4f31-b062-0fa299e6290f.sql",
  "20250912052951_9079d402-139f-46d0-919a-e0c532a8e913.sql",
  "20250912053024_ad545f96-6a7f-4748-a286-7a0e26e5e976.sql",
  "20260210201918_efbae300-0626-48d1-bb7e-ba7d56730082.sql",
  "20260210202939_91cfa57c-4b77-4982-a4bc-928d9bd0611f.sql",
];

// Source files needed to build & deploy the React app for / and /admin
const SOURCE_FILES = [
  // Config (index.html and vite.config.ts are hardcoded separately)
  'tailwind.config.ts',
  'postcss.config.js',
  'tsconfig.json',
  'tsconfig.app.json',
  'tsconfig.node.json',
  'components.json',
  'eslint.config.js',
  '.env',

  // Entry
  'src/main.tsx',
  'src/App.tsx',
  'src/App.css',
  'src/index.css',
  'src/vite-env.d.ts',
  'src/lib/utils.ts',

  // Pages
  'src/pages/Index.tsx',
  'src/pages/OregonTires.tsx',
  'src/pages/OregonTiresAdmin.tsx',
  'src/pages/AdminLogin.tsx',
  'src/pages/AppointmentBooking.tsx',
  'src/pages/ProgramarServicio.tsx',
  'src/pages/Translate.tsx',
  'src/pages/EmployeeProfile.tsx',
  'src/pages/NotFound.tsx',

  // Oregon Tires components
  'src/components/OregonTiresHeader.tsx',
  'src/components/OregonTiresHero.tsx',
  'src/components/OregonTiresServices.tsx',
  'src/components/OregonTiresAbout.tsx',
  'src/components/OregonTiresTestimonials.tsx',
  'src/components/OregonTiresContact.tsx',
  'src/components/OregonTiresFooter.tsx',
  'src/components/OregonTiresGallery.tsx',
  'src/components/WeeklySchedule.tsx',
  'src/components/AppointmentPreview.tsx',
  'src/components/ProtectedAdminRoute.tsx',

  // Admin components
  'src/components/admin/AdminHeader.tsx',
  'src/components/admin/AdminFooter.tsx',
  'src/components/admin/AdminTabs.tsx',
  'src/components/admin/AdminCalendar.tsx',
  'src/components/admin/AdminAccountManager.tsx',
  'src/components/admin/AnalyticsView.tsx',
  'src/components/admin/AppointmentCard.tsx',
  'src/components/admin/AppointmentNotesEditor.tsx',
  'src/components/admin/AppointmentsTab.tsx',
  'src/components/admin/AppointmentsView.tsx',
  'src/components/admin/CalendarPanel.tsx',
  'src/components/admin/CalendarTab.tsx',
  'src/components/admin/DailySummary.tsx',
  'src/components/admin/DashboardOverview.tsx',
  'src/components/admin/DashboardView.tsx',
  'src/components/admin/DaySchedulePanel.tsx',
  'src/components/admin/DayView.tsx',
  'src/components/admin/DayViewAppointmentCard.tsx',
  'src/components/admin/DayViewTimeSlot.tsx',
  'src/components/admin/EmailLogsView.tsx',
  'src/components/admin/EmailTestPanel.tsx',
  'src/components/admin/EmployeeAppointments.tsx',
  'src/components/admin/EmployeeCalendarSchedule.tsx',
  'src/components/admin/EmployeeEditDialog.tsx',
  'src/components/admin/EmployeeManager.tsx',
  'src/components/admin/EmployeeScheduleAlert.tsx',
  'src/components/admin/EmployeesView.tsx',
  'src/components/admin/ExpandedCalendarView.tsx',
  'src/components/admin/GalleryManager.tsx',
  'src/components/admin/HoursEditor.tsx',
  'src/components/admin/MessagesTab.tsx',
  'src/components/admin/MessagesView.tsx',
  'src/components/admin/ScheduleConflictAlert.tsx',
  'src/components/admin/ServiceImagesManager.tsx',
  'src/components/admin/TimeSlot.tsx',
  'src/components/admin/UpcomingAppointmentsView.tsx',

  // Admin hours sub-components
  'src/components/admin/hours/ClosedToggle.tsx',
  'src/components/admin/hours/HoursActions.tsx',
  'src/components/admin/hours/HoursInfo.tsx',
  'src/components/admin/hours/SimultaneousBookingsEditor.tsx',
  'src/components/admin/hours/TimeRangeEditor.tsx',
  'src/components/admin/hours/useHoursEditor.tsx',

  // Booking components
  'src/components/booking/BookingConfirmation.tsx',
  'src/components/booking/BookingSummary.tsx',
  'src/components/booking/CustomerInfoStep.tsx',
  'src/components/booking/DistanceCalculator.tsx',
  'src/components/booking/ScheduleViewStep.tsx',
  'src/components/booking/TimeSlotGrid.tsx',

  // Contact components
  'src/components/contact/ContactForm.tsx',
  'src/components/contact/ContactInformation.tsx',
  'src/components/contact/LocationMap.tsx',

  // UI components (shadcn)
  'src/components/ui/accordion.tsx',
  'src/components/ui/alert-dialog.tsx',
  'src/components/ui/alert.tsx',
  'src/components/ui/aspect-ratio.tsx',
  'src/components/ui/avatar.tsx',
  'src/components/ui/badge.tsx',
  'src/components/ui/breadcrumb.tsx',
  'src/components/ui/button.tsx',
  'src/components/ui/calendar.tsx',
  'src/components/ui/carousel.tsx',
  'src/components/ui/chart.tsx',
  'src/components/ui/checkbox.tsx',
  'src/components/ui/collapsible.tsx',
  'src/components/ui/command.tsx',
  'src/components/ui/context-menu.tsx',
  'src/components/ui/dialog.tsx',
  'src/components/ui/drawer.tsx',
  'src/components/ui/dropdown-menu.tsx',
  'src/components/ui/form.tsx',
  'src/components/ui/hover-card.tsx',
  'src/components/ui/input-otp.tsx',
  'src/components/ui/input.tsx',
  'src/components/ui/label.tsx',
  'src/components/ui/menubar.tsx',
  'src/components/ui/navigation-menu.tsx',
  'src/components/ui/pagination.tsx',
  'src/components/ui/popover.tsx',
  'src/components/ui/progress.tsx',
  'src/components/ui/radio-group.tsx',
  'src/components/ui/resizable.tsx',
  'src/components/ui/scroll-area.tsx',
  'src/components/ui/select.tsx',
  'src/components/ui/separator.tsx',
  'src/components/ui/sheet.tsx',
  'src/components/ui/sidebar.tsx',
  'src/components/ui/skeleton.tsx',
  'src/components/ui/slider.tsx',
  'src/components/ui/sonner.tsx',
  'src/components/ui/switch.tsx',
  'src/components/ui/table.tsx',
  'src/components/ui/tabs.tsx',
  'src/components/ui/textarea.tsx',
  'src/components/ui/toast.tsx',
  'src/components/ui/toaster.tsx',
  'src/components/ui/toggle-group.tsx',
  'src/components/ui/toggle.tsx',
  'src/components/ui/tooltip.tsx',
  'src/components/ui/use-toast.ts',

  // Hooks
  'src/hooks/use-mobile.tsx',
  'src/hooks/use-toast.ts',
  'src/hooks/useAdminAuth.tsx',
  'src/hooks/useAdminData.tsx',
  'src/hooks/useAdminView.tsx',
  'src/hooks/useAppointmentTimer.tsx',
  'src/hooks/useContactForm.tsx',
  'src/hooks/useCustomHours.tsx',
  'src/hooks/useDesignTheme.tsx',
  'src/hooks/useEmailNotifications.tsx',
  'src/hooks/useEmployeeAppointments.tsx',
  'src/hooks/useEmployeeSchedules.tsx',
  'src/hooks/useEmployees.tsx',
  'src/hooks/useGalleryImages.tsx',
  'src/hooks/useLanguage.tsx',
  'src/hooks/useNavigation.tsx',
  'src/hooks/useScheduleAvailability.tsx',
  'src/hooks/useServiceImages.tsx',
  'src/hooks/useServiceImagesForFrontend.tsx',

  // Integrations
  'src/integrations/supabase/client.ts',
  'src/integrations/supabase/types.ts',

  // Types & Utils
  'src/types/admin.ts',
  'src/utils/translations.ts',

  // Note: Edge functions are hardcoded into the ZIP to avoid Vite import resolution issues
  'supabase/config.toml',
];

// Binary/image assets to include (fetched as arraybuffer)
const IMAGE_FILES = [
  'public/favicon.ico',
  'public/placeholder.svg',
  'public/robots.txt',
  'public/_redirects',
  'public/images/auto-maintenance.jpg',
  'public/images/bilingual-service.jpg',
  'public/images/expert-technicians.jpg',
  'public/images/fast-cars.jpg',
  'public/images/quality-parts.jpg',
  'public/images/specialized-services.jpg',
  'public/images/tire-services.jpg',
];

const SRC_IMAGE_FILES = [
  'src/assets/auto-repair.jpg',
  'src/assets/bilingual-support.jpg',
  'src/assets/expert-service.jpg',
  'src/assets/quality-car-parts.jpg',
  'src/assets/quick-service.jpg',
  'src/assets/specialized-tools.jpg',
  'src/assets/tire-shop.jpg',
];

// Hardcoded source index.html to avoid fetching the Vite-transformed version
const INDEX_HTML_CONTENT = `<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Oregon Tires Auto Care - Professional Tire & Auto Services in Portland</title>
    <meta name="description" content="Oregon Tires Auto Care - Professional tire sales, installation, brake services, and auto care in Portland, Oregon. Bilingual service in English and Spanish. Call (503) 367-9714" />
    <meta name="author" content="Oregon Tires Auto Care" />
    <meta name="keywords" content="tires, auto care, brake service, oil change, Portland Oregon, bilingual service, Spanish English speaking" />
    <link rel="icon" href="/lovable-uploads/b0182aa8-dde3-4175-8f09-21b6122f47f4.png" type="image/png">
    <meta property="og:title" content="Oregon Tires" />
    <meta property="og:description" content="Oregon Tires is serving Portland with honest, reliable automotive services since 2008." />
    <meta property="og:image" content="https://oregon.tires/assets/logo.jpg" />
    <meta property="og:url" content="https://oregon.tires/" />
    <meta property="og:type" content="article" />
    <meta property="og:locale" content="en_US" />
    <meta property="og:site_name" content="Oregon Tires" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="Oregon Tires Auto Care" />
    <meta name="twitter:description" content="Professional tire and auto services in Portland, Oregon" />
    <meta name="twitter:image" content="/lovable-uploads/b0182aa8-dde3-4175-8f09-21b6122f47f4.png" />
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "AutomotiveBusiness",
      "name": "Oregon Tires Auto Care",
      "description": "Professional tire sales, installation, brake services, and auto care in Portland, Oregon",
      "telephone": "(503) 367-9714",
      "address": {
        "@type": "PostalAddress",
        "addressLocality": "Portland",
        "addressRegion": "Oregon",
        "addressCountry": "US"
      },
      "openingHours": ["Mo-Fr 08:00-18:00", "Sa 08:00-16:00"],
      "priceRange": "$$",
      "image": "/lovable-uploads/b0182aa8-dde3-4175-8f09-21b6122f47f4.png"
    }
    </script>
  </head>
  <body>
    <div id="root"></div>
    <script type="module" src="/src/main.tsx"></script>
  </body>
</html>`;

// Hardcoded vite.config.ts without lovable-tagger dependency
const VITE_CONFIG_CONTENT = `import { defineConfig } from "vite";
import react from "@vitejs/plugin-react-swc";
import path from "path";

export default defineConfig(({ mode }) => ({
  server: {
    host: "::",
    port: 8080,
  },
  plugins: [
    react(),
  ].filter(Boolean),
  resolve: {
    alias: {
      "@": path.resolve(__dirname, "./src"),
    },
  },
}));`;

const PACKAGE_JSON_CONTENT = `{
  "name": "oregon-tires",
  "private": true,
  "version": "1.0.0",
  "type": "module",
  "scripts": {
    "dev": "vite",
    "build": "vite build",
    "preview": "vite preview"
  },
  "dependencies": {
    "@hookform/resolvers": "^3.9.0",
    "@radix-ui/react-accordion": "^1.2.0",
    "@radix-ui/react-alert-dialog": "^1.1.1",
    "@radix-ui/react-aspect-ratio": "^1.1.0",
    "@radix-ui/react-avatar": "^1.1.0",
    "@radix-ui/react-checkbox": "^1.1.1",
    "@radix-ui/react-collapsible": "^1.1.0",
    "@radix-ui/react-context-menu": "^2.2.1",
    "@radix-ui/react-dialog": "^1.1.2",
    "@radix-ui/react-dropdown-menu": "^2.1.1",
    "@radix-ui/react-hover-card": "^1.1.1",
    "@radix-ui/react-label": "^2.1.0",
    "@radix-ui/react-menubar": "^1.1.1",
    "@radix-ui/react-navigation-menu": "^1.2.0",
    "@radix-ui/react-popover": "^1.1.1",
    "@radix-ui/react-progress": "^1.1.0",
    "@radix-ui/react-radio-group": "^1.2.0",
    "@radix-ui/react-scroll-area": "^1.1.0",
    "@radix-ui/react-select": "^2.1.1",
    "@radix-ui/react-separator": "^1.1.0",
    "@radix-ui/react-slider": "^1.2.0",
    "@radix-ui/react-slot": "^1.1.0",
    "@radix-ui/react-switch": "^1.1.0",
    "@radix-ui/react-tabs": "^1.1.0",
    "@radix-ui/react-toast": "^1.2.1",
    "@radix-ui/react-toggle": "^1.1.0",
    "@radix-ui/react-toggle-group": "^1.1.0",
    "@radix-ui/react-tooltip": "^1.1.4",
    "@supabase/supabase-js": "^2.50.0",
    "@tanstack/react-query": "^5.56.2",
    "class-variance-authority": "^0.7.1",
    "clsx": "^2.1.1",
    "cmdk": "^1.0.0",
    "date-fns": "^3.6.0",
    "dompurify": "^3.3.1",
    "embla-carousel-react": "^8.3.0",
    "input-otp": "^1.2.4",
    "lucide-react": "^0.462.0",
    "next-themes": "^0.3.0",
    "react": "^18.3.1",
    "react-day-picker": "^8.10.1",
    "react-dom": "^18.3.1",
    "react-hook-form": "^7.53.0",
    "react-resizable-panels": "^2.1.3",
    "react-router-dom": "^6.26.2",
    "recharts": "^2.12.7",
    "sonner": "^1.5.0",
    "tailwind-merge": "^2.5.2",
    "tailwindcss-animate": "^1.0.7",
    "vaul": "^0.9.3",
    "zod": "^3.23.8"
  },
  "devDependencies": {
    "@types/dompurify": "^3.0.5",
    "@types/react": "^18.3.1",
    "@types/react-dom": "^18.3.0",
    "@vitejs/plugin-react-swc": "^3.5.0",
    "autoprefixer": "^10.4.19",
    "postcss": "^8.4.38",
    "tailwindcss": "^3.4.3",
    "typescript": "^5.4.5",
    "vite": "^5.2.0"
  }
}`;

const README_CONTENT = `# Oregon Tires - Web Application

## Setup & Deployment

### Prerequisites
- Node.js 18+ and npm

### Install
\`\`\`bash
npm install
\`\`\`

### Environment
The \`.env\` file is pre-configured with your Supabase credentials.  
If you need to change them, edit \`.env\`:
\`\`\`
VITE_SUPABASE_URL=https://vtknmauyvmuaryttnenx.supabase.co
VITE_SUPABASE_PUBLISHABLE_KEY=your-anon-key
VITE_SUPABASE_PROJECT_ID=vtknmauyvmuaryttnenx
\`\`\`

### Build for Production
\`\`\`bash
npm run build
\`\`\`
This creates a \`dist/\` folder. Upload its contents to your web server.

### SPA Routing
For client-side routing to work, configure your web server to serve \`index.html\` for all routes.

**Apache (.htaccess)** — included in dist automatically:
\`\`\`
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.html [L]
\`\`\`

**Nginx:**
\`\`\`
location / {
  try_files $uri $uri/ /index.html;
}
\`\`\`

### Routes
- \`/\` — Public Oregon Tires website
- \`/admin/login\` — Admin login
- \`/admin\` — Admin dashboard (requires authentication)
- \`/book-appointment\` — Appointment booking
- \`/programar-servicio\` — Spanish appointment booking

### Edge Functions
The \`supabase/functions/\` directory contains Supabase Edge Functions.
Deploy them via the Supabase CLI:
\`\`\`bash
supabase functions deploy send-appointment-emails
supabase functions deploy create-employee-account
\`\`\`
`;

const DatabaseDownload = () => {
  const [sqlLoading, setSqlLoading] = useState(false);
  const [sqlProgress, setSqlProgress] = useState('');
  const [zipLoading, setZipLoading] = useState(false);
  const [zipProgress, setZipProgress] = useState('');

  const downloadSqlBundle = async () => {
    setSqlLoading(true);
    setSqlProgress('Fetching schema file...');

    try {
      let bundledContent = '';

      const schemaRes = await fetch('/database-schema.sql');
      if (schemaRes.ok) {
        const schemaText = await schemaRes.text();
        bundledContent += `-- ========================================================\n`;
        bundledContent += `-- FILE: database-schema.sql (Complete Schema Reference)\n`;
        bundledContent += `-- ========================================================\n\n`;
        bundledContent += schemaText;
        bundledContent += `\n\n`;
      }

      for (let i = 0; i < MIGRATION_FILES.length; i++) {
        const file = MIGRATION_FILES[i];
        setSqlProgress(`Fetching migration ${i + 1} of ${MIGRATION_FILES.length}...`);
        try {
          const res = await fetch(`/supabase/migrations/${file}`);
          if (res.ok) {
            const text = await res.text();
            if (text.trim().length > 1) {
              bundledContent += `-- ========================================================\n`;
              bundledContent += `-- MIGRATION: ${file}\n`;
              bundledContent += `-- ========================================================\n\n`;
              bundledContent += text;
              bundledContent += `\n\n`;
            }
          }
        } catch {
          // skip
        }
      }

      const blob = new Blob([bundledContent], { type: 'text/sql' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = 'oregon-tires-database-bundle.sql';
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      URL.revokeObjectURL(url);
      setSqlProgress('Download complete!');
    } catch (error) {
      console.error('Download error:', error);
      setSqlProgress('Error during download. Check console.');
    } finally {
      setSqlLoading(false);
    }
  };

  const fetchTextFile = async (path: string, raw = false): Promise<string | null> => {
    try {
      const suffix = raw ? '?raw' : '';
      const res = await fetch(`/${path}${suffix}`);
      if (res.ok) {
        const text = await res.text();
        // ?raw returns an ES module with default export, extract the content
        if (raw && text.startsWith('export default')) {
          try {
            // The raw response is: export default "...escaped content..."
            return JSON.parse(text.replace(/^export default /, '').replace(/;?\s*$/, ''));
          } catch {
            return text;
          }
        }
        return text;
      }
    } catch { /* skip */ }
    return null;
  };

  const fetchBinaryFile = async (path: string): Promise<ArrayBuffer | null> => {
    try {
      const res = await fetch(`/${path}`);
      if (res.ok) return await res.arrayBuffer();
    } catch { /* skip */ }
    return null;
  };

  const downloadAppZip = async () => {
    setZipLoading(true);
    setZipProgress('Initializing...');

    try {
      const zip = new JSZip();

      // Add package.json, README, and source index.html
      zip.file('package.json', PACKAGE_JSON_CONTENT);
      zip.file('README.md', README_CONTENT);
      zip.file('index.html', INDEX_HTML_CONTENT);
      zip.file('vite.config.ts', VITE_CONFIG_CONTENT);
      zip.file('public/.htaccess', `Options -MultiViews
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.html [L]

<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain text/html text/xml text/css application/xml application/xhtml+xml application/rss+xml application/javascript application/x-javascript
</IfModule>

<IfModule mod_expires.c>
    ExpiresActive on
    ExpiresByType text/css "access plus 1 year"
    ExpiresByType application/javascript "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
</IfModule>`);

      // Edge functions use Deno imports that Vite can't resolve - fetch as raw
      for (const efPath of [
        'supabase/functions/send-appointment-emails/index.ts',
        'supabase/functions/create-employee-account/index.ts',
      ]) {
        const content = await fetchTextFile(efPath, true);
        if (content) {
          zip.file(efPath, content);
        }
      }

      // Fetch all source text files
      let fetched = 0;
      const total = SOURCE_FILES.length + IMAGE_FILES.length + SRC_IMAGE_FILES.length + MIGRATION_FILES.length;

      for (const filePath of SOURCE_FILES) {
        fetched++;
        setZipProgress(`Fetching source files... (${fetched}/${total})`);
        const content = await fetchTextFile(filePath);
        if (content) {
          zip.file(filePath, content);
        }
      }

      // Fetch public assets as binary
      for (const filePath of IMAGE_FILES) {
        fetched++;
        setZipProgress(`Fetching assets... (${fetched}/${total})`);
        const data = await fetchBinaryFile(filePath);
        if (data) {
          zip.file(filePath, data);
        }
      }

      // Fetch src/assets as binary
      for (const filePath of SRC_IMAGE_FILES) {
        fetched++;
        setZipProgress(`Fetching src assets... (${fetched}/${total})`);
        const data = await fetchBinaryFile(filePath);
        if (data) {
          zip.file(filePath, data);
        }
      }

      // Fetch database schema
      setZipProgress('Fetching database schema...');
      const schema = await fetchTextFile('database-schema.sql');
      if (schema) {
        zip.file('public/database-schema.sql', schema);
      }

      // Fetch migration files
      for (let i = 0; i < MIGRATION_FILES.length; i++) {
        fetched++;
        setZipProgress(`Fetching migrations... (${fetched}/${total})`);
        const content = await fetchTextFile(`supabase/migrations/${MIGRATION_FILES[i]}`);
        if (content && content.trim().length > 1) {
          zip.file(`supabase/migrations/${MIGRATION_FILES[i]}`, content);
        }
      }

      // Generate and download
      setZipProgress('Generating zip file...');
      const blob = await zip.generateAsync({ type: 'blob' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = 'oregon-tires-app.zip';
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      URL.revokeObjectURL(url);
      setZipProgress('Download complete!');
    } catch (error) {
      console.error('Zip error:', error);
      setZipProgress('Error creating zip. Check console.');
    } finally {
      setZipLoading(false);
    }
  };

  return (
    <div className="min-h-screen bg-gray-50">
      <header style={{ backgroundColor: '#007030' }} className="text-white py-6">
        <div className="container mx-auto px-4">
          <div className="flex items-center gap-4">
            <Link to="/" className="hover:opacity-80">
              <ArrowLeft className="h-6 w-6" />
            </Link>
            <div className="flex items-center gap-3">
              <Database className="h-8 w-8" />
              <div>
                <h1 className="text-2xl font-bold">Project Downloads</h1>
                <p className="text-white/80 text-sm">Download database files & deployable app source</p>
              </div>
            </div>
          </div>
        </div>
      </header>

      <main className="container mx-auto px-4 py-8 max-w-3xl space-y-8">
        {/* App Source Zip */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <FolderArchive className="h-5 w-5" />
              Download Full App Source (.zip)
            </CardTitle>
            <CardDescription>
              Complete React source code for <code>/</code> and <code>/admin</code> routes with all components, hooks, styles, assets, database migrations, edge functions, and a README with build &amp; deploy instructions. Run <code>npm install &amp;&amp; npm run build</code> then upload <code>dist/</code> to your server.
            </CardDescription>
          </CardHeader>
          <CardContent>
            <Button
              onClick={downloadAppZip}
              disabled={zipLoading}
              size="lg"
              className="w-full"
              style={{ backgroundColor: '#007030' }}
            >
              <Package className="h-5 w-5 mr-2" />
              {zipLoading ? zipProgress : 'Download App Source (.zip)'}
            </Button>
            {zipProgress && !zipLoading && (
              <p className="text-sm text-muted-foreground mt-2 text-center">{zipProgress}</p>
            )}
          </CardContent>
        </Card>

        {/* Database SQL Bundle */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Download className="h-5 w-5" />
              Download Database Bundle (.sql)
            </CardTitle>
            <CardDescription>
              Bundles the complete schema and all {MIGRATION_FILES.length} migration files into a single .sql file.
            </CardDescription>
          </CardHeader>
          <CardContent>
            <Button
              onClick={downloadSqlBundle}
              disabled={sqlLoading}
              size="lg"
              className="w-full"
              style={{ backgroundColor: '#007030' }}
            >
              {sqlLoading ? sqlProgress : 'Download Database Bundle (.sql)'}
            </Button>
            {sqlProgress && !sqlLoading && (
              <p className="text-sm text-muted-foreground mt-2 text-center">{sqlProgress}</p>
            )}
          </CardContent>
        </Card>

        {/* File list */}
        <Card>
          <CardHeader>
            <CardTitle className="text-lg flex items-center gap-2">
              <FileText className="h-5 w-5" />
              Included in App Zip
            </CardTitle>
            <CardDescription>
              {SOURCE_FILES.length} source files + {IMAGE_FILES.length + SRC_IMAGE_FILES.length} assets + {MIGRATION_FILES.length} migrations
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="space-y-1 max-h-96 overflow-y-auto text-sm">
              <div className="p-2 rounded bg-green-50 text-green-800 font-medium">📦 package.json</div>
              <div className="p-2 rounded bg-green-50 text-green-800 font-medium">📖 README.md</div>
              {SOURCE_FILES.map((file) => (
                <div key={file} className="p-2 rounded hover:bg-gray-100 text-muted-foreground">
                  {file}
                </div>
              ))}
              {[...IMAGE_FILES, ...SRC_IMAGE_FILES].map((file) => (
                <div key={file} className="p-2 rounded hover:bg-gray-100 text-muted-foreground">
                  🖼️ {file}
                </div>
              ))}
            </div>
          </CardContent>
        </Card>
      </main>
    </div>
  );
};

export default DatabaseDownload;
