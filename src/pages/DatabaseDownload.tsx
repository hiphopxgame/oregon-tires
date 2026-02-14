import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Download, Database, FileText, ArrowLeft, Package } from 'lucide-react';
import { Link } from 'react-router-dom';
import { useState } from 'react';
import JSZip from 'jszip';

// Tables required by /simple and /simple/admin
const REQUIRED_TABLES = [
  'oretir_profiles',
  'oretir_appointments',
  'oretir_contact_messages',
  'oretir_employees',
  'oretir_email_logs',
  'oretir_gallery_images',
  'oretir_service_images',
];

const DatabaseDownload = () => {
  const [sqlLoading, setSqlLoading] = useState(false);
  const [sqlProgress, setSqlProgress] = useState('');
  const [zipLoading, setZipLoading] = useState(false);
  const [zipProgress, setZipProgress] = useState('');

  const downloadSqlBundle = async () => {
    setSqlLoading(true);
    setSqlProgress('Filtering schema for Oregon Tires tables...');

    try {
      const schemaRes = await fetch('/database-schema.sql');
      if (!schemaRes.ok) throw new Error('Could not fetch schema file');
      const fullSchema = await schemaRes.text();

      // Extract only Oregon Tires relevant CREATE TABLE blocks and related SQL
      const tablePatterns = ['oretir_', 'oregon_tires_', 'admin_accounts', 'customer_vehicles'];
      const lines = fullSchema.split('\n');

      let filteredContent = `-- ========================================================\n`;
      filteredContent += `-- Oregon Tires - Simple Site Database Schema\n`;
      filteredContent += `-- Tables: ${REQUIRED_TABLES.join(', ')}\n`;
      filteredContent += `-- ========================================================\n\n`;

      let capturing = false;
      let parenDepth = 0;

      for (const line of lines) {
        // Always include ENUM definitions
        if (line.match(/^CREATE TYPE/i)) {
          filteredContent += line + '\n';
          continue;
        }

        // Start capturing relevant CREATE TABLE blocks
        const createMatch = line.match(/CREATE TABLE\s+public\.(\w+)/i);
        if (createMatch) {
          capturing = tablePatterns.some(p => createMatch[1].startsWith(p));
          parenDepth = 0;
        }

        // Capture ALTER TABLE / RLS / policies for relevant tables
        const alterMatch = line.match(/(?:ALTER TABLE|ON)\s+public\.(\w+)/i);
        if (alterMatch && !capturing) {
          if (tablePatterns.some(p => alterMatch[1].startsWith(p))) {
            capturing = true;
            parenDepth = 0;
          }
        }

        // Capture relevant functions
        if (line.match(/CREATE OR REPLACE FUNCTION\s+public\.(is_admin|is_super_admin|handle_new_user|set_admin_by_email|format_service_name|notify_admin|create_employee|update_oretir|update_oregon_tires)/i)) {
          capturing = true;
          parenDepth = 0;
        }

        if (capturing) {
          filteredContent += line + '\n';
          parenDepth += (line.match(/\(/g) || []).length;
          parenDepth -= (line.match(/\)/g) || []).length;

          // End capture after complete statement
          if (line.trimEnd().endsWith(';') && parenDepth <= 0) {
            filteredContent += '\n';
            capturing = false;
          }
        }
      }

      const blob = new Blob([filteredContent], { type: 'text/sql' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = 'oregon-tires-simple-schema.sql';
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
              Exports only the {REQUIRED_TABLES.length} Oregon Tires tables needed by the Simple Site into a single .sql file.
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
