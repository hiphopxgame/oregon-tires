# Oregon Tires — SEO Maintenance Guide

## Sitemap
The sitemap is dynamically generated at `/api/sitemap.php` and served at `/sitemap.xml` via .htaccess rewrite. It automatically includes:
- All static pages (homepage, contact, services, etc.)
- Service detail pages (tire-installation, brake-service, etc.)
- Service area pages (tires-se-portland, tires-clackamas, etc.)
- Blog posts from database (`oretir_blog_posts` where status='published')
- Trust pages (FAQ, reviews, service-areas)

No manual updates needed for new blog posts — they appear automatically.

### Adding a New Static Page
1. Create the PHP file in `public_html/`
2. Add the URL to the `$staticPages` array in `api/sitemap.php`
3. Deploy and verify at https://oregon.tires/sitemap.xml

## Search Platform Verification
Set these in `.env` (or `.env.oregon-tires` on server):
```
GOOGLE_SITE_VERIFICATION=your_verification_code
BING_SITE_VERIFICATION=your_verification_code
```
The verification meta tags are output via `includes/seo-head.php`.

### Google Search Console Setup
1. Go to https://search.google.com/search-console
2. Add property: `https://oregon.tires`
3. Choose "HTML tag" verification method
4. Copy the content value, set as `GOOGLE_SITE_VERIFICATION` in .env
5. Deploy and verify

### Bing Webmaster Tools Setup
1. Go to https://www.bing.com/webmasters
2. Add site: `https://oregon.tires`
3. Choose "Meta tag" verification
4. Copy the content value, set as `BING_SITE_VERIFICATION` in .env
5. Deploy and verify

## Business Config
All business data (NAP, hours, services, areas) is centralized in `includes/seo-config.php`.
Update this file when business info changes (phone, hours, new services, etc.).

## IndexNow (Bing Fast Indexing)
Submit new/updated URLs to Bing instantly:
```bash
php cli/indexnow-submit.php https://oregon.tires/new-page
```

Setup:
1. Generate key: `php -r "echo bin2hex(random_bytes(16)) . PHP_EOL;"`
2. Add to .env: `INDEXNOW_KEY=your_key`
3. Create key file: `echo 'your_key' > public_html/your_key.txt`
4. Deploy the key file to server

## JSON-LD Schema
Every page includes structured data. Key types:
- **Homepage**: AutomotiveBusiness (full)
- **Service pages**: Service + AutomotiveBusiness
- **Service area pages**: AutomotiveBusiness + areaServed
- **Blog**: Article + BreadcrumbList
- **FAQ**: FAQPage + BreadcrumbList
- **Reviews**: AutomotiveBusiness + AggregateRating
- **Contact**: ContactPage + AutomotiveBusiness

Test at: https://search.google.com/test/rich-results

## Google Business Profile
- Place ID: `ChIJLSxZDQyflVQRWXEi9LpJGxs`
- Review link: https://search.google.com/local/writereview?placeid=ChIJLSxZDQyflVQRWXEi9LpJGxs
- See `docs/business-listing-pack.md` for complete listing info
