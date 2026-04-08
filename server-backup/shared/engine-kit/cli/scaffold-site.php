<?php
/**
 * scaffold-site.php — CLI entry point for site scaffolding.
 *
 * Usage:
 *   php cli/scaffold-site.php --domain=portland.food --name="Portland Food Guide" --kits=form,member,directory --auto
 *
 * Options:
 *   --domain       (required) Domain name (e.g., portland.food)
 *   --name         (required) Display name (e.g., "Portland Food Guide")
 *   --kits         Comma-separated kit list (default: form,engine)
 *   --description  Natural language description (auto-detects kits when --kits omitted)
 *   --type         Site type: 1vsm, hiphop, client (default: 1vsm)
 *   --svg          Path to logo SVG for brand generation
 *   --image        Path to logo image (PNG/JPG/WebP) for brand generation
 *   --auto         Auto-generate branding from domain hash
 *   --table-prefix DB table prefix (auto-generated if omitted)
 *   --member-mode  SSO mode: independent or hw (default: independent)
 *   --deploy       Push to server after scaffold
 *   --provision    Run composer install + onboard-site.php + .env setup
 *   --dry-run      Preview without writing files
 *   --help         Show this help
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('CLI only');
}

require_once __DIR__ . '/../scaffolder/ScaffoldEngine.php';

// Parse CLI arguments
$options = [];
$flags   = ['auto', 'deploy', 'provision', 'dry-run', 'help'];

foreach ($argv as $i => $arg) {
    if ($i === 0) continue;
    if (str_starts_with($arg, '--')) {
        $arg = substr($arg, 2);
        $pos = strpos($arg, '=');
        if ($pos !== false) {
            $key = substr($arg, 0, $pos);
            $val = substr($arg, $pos + 1);
            $options[$key] = $val;
        } else {
            $options[$arg] = true;
        }
    }
}

// Help
if (!empty($options['help']) || empty($options['domain']) || empty($options['name'])) {
    echo <<<HELP

  Site Scaffolder — 1vsM Network
  ===============================

  Usage:
    php cli/scaffold-site.php --domain=<domain> --name="<name>" [options]

  Required:
    --domain          Domain name (e.g., portland.food)
    --name            Display name (e.g., "Portland Food Guide")

  Options:
    --kits=<list>       Comma-separated kits (default: form,engine)
                        Available: engine, form, member, commerce, event, directory, song
    --description=<str> Natural language description (auto-detects kits when --kits omitted)
    --type=<type>       Site type: 1vsm, hiphop, client (default: 1vsm)
    --svg=<path>        Logo SVG path for brand generation
    --image=<path>      Logo image path (PNG/JPG/WebP) for brand generation
    --auto              Auto-generate branding from domain hash
    --table-prefix      DB table prefix (auto-generated if omitted)
    --member-mode       SSO mode: independent or hw (default: independent)
    --deploy            Deploy to server after scaffolding
    --provision         Run composer install + onboard-site.php + .env setup
    --dry-run           Preview files without writing
    --help              Show this help

  Examples:
    # Brand-first: SVG logo → extract colors → generate site with brand baked in
    php cli/scaffold-site.php --domain=portland.food --name="Portland Food Guide" --svg=/path/to/logo.svg --kits=form,directory

    # Brand-first: raster image → extract colors → generate site with brand baked in
    php cli/scaffold-site.php --domain=tasty.menu --name="Tasty Menu" --image=/path/to/logo.png --kits=form,commerce

    # Auto-brand: deterministic colors from domain hash
    php cli/scaffold-site.php --domain=pdx.marketing --name="PDX Marketing" --kits=form,commerce --auto --dry-run

    # Auto-detect kits from description
    php cli/scaffold-site.php --domain=tasty.menu --name="Tasty Menu" --description="Restaurant with online ordering and contact form" --auto

    # HipHop domain with deploy
    php cli/scaffold-site.php --domain=hiphop.radio --name="HipHop Radio" --kits=form,member,song --type=hiphop --deploy


HELP;
    exit(empty($options['help']) ? 1 : 0);
}

// Run scaffold
$engine = new ScaffoldEngine();
$result = $engine->scaffold($options);

// Output
echo "\n";
foreach ($result['log'] as $msg) {
    echo "  {$msg}\n";
}
echo "\n";

if ($result['success']) {
    $fileCount = count($result['files']);
    if (!empty($result['dry_run'])) {
        echo "  DRY RUN complete. {$fileCount} files would be generated.\n";
        echo "  Site directory: {$result['site_dir']}\n";
    } else {
        echo "  SUCCESS: {$fileCount} files generated.\n";
        echo "  Site directory: {$result['site_dir']}\n";
        echo "\n  Next steps:\n";
        echo "    1. cd {$result['site_dir']}\n";
        echo "    2. Review brand assets in public_html/brand/ (tokens.css, logo, favicons)\n";
        echo "    3. Edit .env.example → .env with real credentials\n";
        echo "    4. Run: composer require vlucas/phpdotenv (for production)\n";
        echo "    5. Run: ./deploy.sh\n";

        // Show onboarding hint
        $config = $result['config'] ?? [];
        if (!empty($config['site_key'])) {
            echo "\n  Onboarding (from hiphop.world):\n";
            echo "    php cli/onboard-site.php --site={$config['site_key']} --domain={$config['domain']} --name=\"{$config['name']}\"";
            if (!empty($config['auto_brand'])) {
                echo " --auto";
            }
            echo "\n";
        }
    }
} else {
    echo "  FAILED: " . ($result['error'] ?? 'Unknown error') . "\n";
    exit(1);
}

echo "\n";
