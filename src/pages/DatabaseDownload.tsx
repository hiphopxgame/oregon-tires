import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Download, Database, FileText, ArrowLeft, Package } from 'lucide-react';
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

  const downloadSimpleSiteZip = async () => {
    setZipLoading(true);
    setZipProgress('Fetching simplified site...');

    try {
      const zip = new JSZip();

      // Fetch the simplified HTML files
      const htmlRes = await fetch('/simple/index.html');
      if (htmlRes.ok) {
        const htmlContent = await htmlRes.text();
        zip.file('index.html', htmlContent);
      }

      setZipProgress('Fetching admin panel...');
      const adminRes = await fetch('/simple/admin.html');
      if (adminRes.ok) {
        const adminContent = await adminRes.text();
        zip.file('admin.html', adminContent);
      }

      // Add a simple README
      zip.file('README.md', `# Oregon Tires - Simple Website

## How to Run

1. Open \`index.html\` in any web browser - that's it!
2. Or upload the entire folder to any web hosting service.

## Features
- Bilingual (English/Spanish) toggle
- Contact form connected to Supabase
- Customer reviews
- All services listed
- Google Maps embed
- Mobile responsive (Tailwind CSS CDN)
- Book appointment link

## No build step required!
This is a single HTML file with embedded CSS and JavaScript.
Just open it in a browser or upload to any static hosting.

## Hosting Options
- GitHub Pages
- Netlify (drag and drop)
- Any Apache/Nginx server
- Amazon S3 + CloudFront
- Google Cloud Storage
`);

      // Fetch the logo image
      setZipProgress('Fetching logo...');
      try {
        const logoRes = await fetch('/lovable-uploads/1290fb5e-e45c-4fc3-b523-e71d756ec1ef.png');
        if (logoRes.ok) {
          const logoData = await logoRes.arrayBuffer();
          zip.file('lovable-uploads/1290fb5e-e45c-4fc3-b523-e71d756ec1ef.png', logoData);
        }
      } catch { /* skip */ }

      // Fetch favicon
      try {
        const favRes = await fetch('/lovable-uploads/b0182aa8-dde3-4175-8f09-21b6122f47f4.png');
        if (favRes.ok) {
          const favData = await favRes.arrayBuffer();
          zip.file('lovable-uploads/b0182aa8-dde3-4175-8f09-21b6122f47f4.png', favData);
        }
      } catch { /* skip */ }

      setZipProgress('Generating zip...');
      const blob = await zip.generateAsync({ type: 'blob' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = 'oregon-tires-simple.zip';
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
                <p className="text-white/80 text-sm">Download simplified site & database files</p>
              </div>
            </div>
          </div>
        </div>
      </header>

      <main className="container mx-auto px-4 py-8 max-w-3xl space-y-8">
        {/* Preview link */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <FileText className="h-5 w-5" />
              Preview Simplified Site
            </CardTitle>
            <CardDescription>
              View the simplified HTML/CSS/JS version of Oregon Tires before downloading.
            </CardDescription>
          </CardHeader>
          <CardContent>
            <Link to="/simple">
              <Button size="lg" className="w-full" variant="outline">
                Open /simple Preview →
              </Button>
            </Link>
          </CardContent>
        </Card>

        {/* Simple Site Download */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Package className="h-5 w-5" />
              Download Simple Site (.zip)
            </CardTitle>
            <CardDescription>
              A single <code>index.html</code> file with embedded CSS &amp; JavaScript. No build step required — just open in a browser or upload to any web host. Includes bilingual support, contact form, reviews, and all services.
            </CardDescription>
          </CardHeader>
          <CardContent>
            <Button
              onClick={downloadSimpleSiteZip}
              disabled={zipLoading}
              size="lg"
              className="w-full"
              style={{ backgroundColor: '#007030' }}
            >
              <Package className="h-5 w-5 mr-2" />
              {zipLoading ? zipProgress : 'Download Simple Site (.zip)'}
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
      </main>
    </div>
  );
};

export default DatabaseDownload;
