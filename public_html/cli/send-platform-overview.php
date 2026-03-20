<?php
/**
 * Oregon Tires — Platform Overview Email (EN + ES)
 * Sends a branded proposal email to the client showing all 113 features,
 * pricing estimates, $5,000 starter package, and partnership proposal.
 *
 * Usage:
 *   php cli/send-platform-overview.php --preview   # Write HTML to cli/logs/
 *   php cli/send-platform-overview.php --send       # Send both EN + ES emails
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

// Load env
$envDir = dirname(__DIR__, 3);
$envFile = '.env.oregon-tires';
if (!file_exists($envDir . '/' . $envFile)) {
    $envDir = __DIR__ . '/..';
    $envFile = '.env';
}
$dotenv = Dotenv\Dotenv::createImmutable($envDir, $envFile);
$dotenv->load();

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/mail.php';

// ─── Config ─────────────────────────────────────────────────────────────────
$baseUrl = rtrim($_ENV['APP_URL'] ?? 'https://oregon.tires', '/');

$recipients = [
    'en' => [
        'to'      => 'tyronenorris@gmail.com',
        'subject' => 'Your Platform is Ready — Oregon Tires Auto Care',
    ],
    'es' => [
        'to'      => 'oregontirespdx@gmail.com',
        'subject' => 'Tu Plataforma Está Lista — Oregon Tires Auto Care',
    ],
];

// ─── Parse CLI args ─────────────────────────────────────────────────────────
$mode = null;
foreach ($argv as $arg) {
    if ($arg === '--preview') $mode = 'preview';
    if ($arg === '--send')    $mode = 'send';
}

if (!$mode) {
    echo "Usage:\n";
    echo "  php cli/send-platform-overview.php --preview   # Write HTML previews\n";
    echo "  php cli/send-platform-overview.php --send       # Send emails\n";
    exit(1);
}

// ─── Build emails ───────────────────────────────────────────────────────────
$htmlEn = buildPlatformEmail('en', $baseUrl);
$htmlEs = buildPlatformEmail('es', $baseUrl);

if ($mode === 'preview') {
    $logDir = __DIR__ . '/logs';
    file_put_contents($logDir . '/platform-overview-en.html', $htmlEn);
    file_put_contents($logDir . '/platform-overview-es.html', $htmlEs);
    echo "✓ Preview files written:\n";
    echo "  cli/logs/platform-overview-en.html\n";
    echo "  cli/logs/platform-overview-es.html\n";
    exit(0);
}

// ─── Send mode ──────────────────────────────────────────────────────────────
$allOk = true;

foreach ($recipients as $lang => $cfg) {
    $html = ($lang === 'en') ? $htmlEn : $htmlEs;
    $plainText = strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>', '</td>', '</tr>', '</li>'], "\n", $html));
    $plainText = preg_replace('/\n{3,}/', "\n\n", $plainText);

    echo "Sending {$lang} → {$cfg['to']}... ";

    $result = sendMail(
        $cfg['to'],
        $cfg['subject'],
        $html,
        $plainText,
        'tyronenorris@gmail.com'
    );

    if ($result['success']) {
        echo "✓ Sent\n";
        try {
            logEmail('platform_overview', "Platform overview email ({$lang}) sent to {$cfg['to']}");
        } catch (\Throwable $e) {
            echo "  (DB log skipped: {$e->getMessage()})\n";
        }
    } else {
        echo "✗ Failed: {$result['error']}\n";
        $allOk = false;
    }
}

echo $allOk ? "\n✓ All emails sent successfully.\n" : "\n⚠ Some emails failed. Check error log.\n";
exit($allOk ? 0 : 1);

// ═════════════════════════════════════════════════════════════════════════════
// HTML Builder
// ═════════════════════════════════════════════════════════════════════════════

function buildPlatformEmail(string $lang, string $baseUrl): string
{
    $content = getEmailContent($lang);
    $logoUrl = $baseUrl . '/assets/logo.png';
    $onvsmLogoUrl = $baseUrl . '/assets/img/1vsm-logo.jpg';

    // Build feature sections HTML
    $sectionsHtml = '';
    foreach ($content['sections'] as $section) {
        $featuresHtml = '';
        foreach ($section['features'] as $feat) {
            $featuresHtml .= '<tr><td style="padding:4px 0 4px 16px;color:#374151;font-size:14px;line-height:1.5;">• ' . esc($feat) . '</td></tr>';
        }

        $sectionsHtml .= <<<HTML
  <tr>
    <td style="padding:24px 36px 8px;">
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f0fdf4;border-radius:12px;border:1px solid #dcfce7;">
        <tr>
          <td style="padding:20px 24px 8px;">
            <h3 style="color:#15803d;font-size:18px;margin:0 0 4px;">{$section['title']}</h3>
            <p style="color:#6b7280;font-size:13px;font-style:italic;margin:0 0 16px;">{$section['subtitle']}</p>
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
              {$featuresHtml}
            </table>
          </td>
        </tr>
        <tr>
          <td style="padding:12px 24px 16px;">
            <p style="color:#15803d;font-size:15px;font-weight:700;margin:0;">{$content['labels']['estimated_value']}: {$section['value']}</p>
          </td>
        </tr>
      </table>
    </td>
  </tr>
HTML;
    }

    // Total value table
    $totalRowsHtml = '';
    foreach ($content['sections'] as $section) {
        $totalRowsHtml .= '<tr><td style="padding:6px 16px;color:#374151;font-size:14px;border-bottom:1px solid #dcfce7;">' . esc($section['title_short']) . '</td><td style="padding:6px 16px;color:#374151;font-size:14px;text-align:right;border-bottom:1px solid #dcfce7;">' . $section['value'] . '</td></tr>';
    }

    // Replacement services table
    $replacesHtml = '';
    foreach ($content['replaces'] as $row) {
        $replacesHtml .= '<tr><td style="padding:6px 16px;color:#374151;font-size:14px;border-bottom:1px solid #e5e7eb;">' . esc($row['service']) . '</td><td style="padding:6px 16px;color:#374151;font-size:14px;text-align:right;border-bottom:1px solid #e5e7eb;">' . esc($row['cost']) . '</td></tr>';
    }

    // Roadmap features
    $roadmapHtml = '';
    foreach ($content['roadmap'] as $feat) {
        $roadmapHtml .= '<tr><td style="padding:4px 0 4px 16px;color:#374151;font-size:14px;line-height:1.5;">• ' . esc($feat) . '</td></tr>';
    }

    // Offer items
    $offerHtml = '';
    foreach ($content['offer_items'] as $i => $item) {
        $num = $i + 1;
        $offerHtml .= '<tr><td style="padding:6px 0;color:#374151;font-size:15px;line-height:1.6;"><strong>' . $num . '.</strong> ' . $item . '</td></tr>';
    }

    // After items
    $afterHtml = '';
    foreach ($content['after_items'] as $item) {
        $afterHtml .= '<tr><td style="padding:4px 0 4px 16px;color:#374151;font-size:14px;line-height:1.5;">• ' . $item . '</td></tr>';
    }

    // Pillar sections (Mind/Body/Soul)
    $pillarsHtml = '';
    foreach ($content['pillars'] as $pillar) {
        $pillarsHtml .= <<<HTML
        <tr>
          <td style="padding:12px 0;">
            <p style="color:#15803d;font-size:16px;font-weight:700;margin:0 0 6px;">{$pillar['title']}</p>
            <p style="color:#374151;font-size:14px;line-height:1.6;margin:0;">{$pillar['desc']}</p>
          </td>
        </tr>
HTML;
    }

    $l = $content['labels'];

    return <<<HTML
<!DOCTYPE html>
<html lang="{$lang}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{$l['email_title']}</title>
</head>
<body style="margin:0;padding:0;background-color:#f0fdf4;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;">

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f0fdf4;">
<tr><td align="center" style="padding:30px 15px;">

<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background-color:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">

  <!-- HEADER -->
  <tr>
    <td style="background:linear-gradient(135deg,#15803d 0%,#166534 50%,#1a1a2e 100%);padding:0;">
      <div style="height:4px;background:linear-gradient(90deg,#d4a843,#f5d78e,#d4a843);"></div>
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
          <td align="center" style="padding:32px 30px 24px;">
            <img src="{$logoUrl}" alt="Oregon Tires Auto Care" width="140" style="display:block;max-width:140px;height:auto;margin-bottom:16px;">
            <p style="color:#f5d78e;font-size:14px;margin:0;letter-spacing:1px;font-weight:600;">{$l['header_tagline']}</p>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <!-- GREETING -->
  <tr>
    <td style="padding:36px 36px 0;">
      <p style="color:#111827;font-size:16px;line-height:1.7;margin:0 0 16px;">{$l['greeting']}</p>
      <p style="color:#374151;font-size:15px;line-height:1.7;margin:0 0 16px;">{$l['intro_1']}</p>
      <p style="color:#374151;font-size:15px;line-height:1.7;margin:0 0 16px;">{$l['intro_2']}</p>
      <p style="color:#374151;font-size:15px;line-height:1.7;margin:0;">{$l['intro_3']}</p>
    </td>
  </tr>

  <!-- SECTION HEADER: What We Built -->
  <tr>
    <td style="padding:32px 36px 0;">
      <h2 style="color:#15803d;font-size:22px;margin:0 0 8px;border-bottom:2px solid #15803d;padding-bottom:8px;">{$l['what_we_built']}</h2>
      <p style="color:#374151;font-size:14px;line-height:1.6;margin:0;">{$l['what_we_built_desc']}</p>
    </td>
  </tr>

  <tr>
    <td style="padding:8px 36px 0;">
      <p style="color:#6b7280;font-size:13px;line-height:1.6;font-style:italic;margin:0;">{$l['rate_disclaimer']}</p>
    </td>
  </tr>

{$sectionsHtml}

  <!-- TOTAL VALUE TABLE -->
  <tr>
    <td style="padding:32px 36px 0;">
      <h2 style="color:#15803d;font-size:22px;margin:0 0 16px;border-bottom:2px solid #15803d;padding-bottom:8px;">{$l['total_value_title']}</h2>
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f0fdf4;border-radius:12px;border:1px solid #dcfce7;">
        <tr>
          <td style="padding:12px 16px;color:#15803d;font-size:14px;font-weight:700;border-bottom:2px solid #15803d;">{$l['features_built']}</td>
          <td style="padding:12px 16px;color:#15803d;font-size:14px;font-weight:700;text-align:right;border-bottom:2px solid #15803d;">{$l['freelancer_estimate']}</td>
        </tr>
        {$totalRowsHtml}
        <tr>
          <td style="padding:12px 16px;color:#15803d;font-size:16px;font-weight:700;">{$l['total_label']}</td>
          <td style="padding:12px 16px;color:#15803d;font-size:16px;font-weight:700;text-align:right;">$52,400</td>
        </tr>
      </table>
      <p style="color:#6b7280;font-size:12px;line-height:1.5;font-style:italic;margin:8px 0 0;">{$l['total_footnote']}</p>
    </td>
  </tr>

  <!-- REPLACES -->
  <tr>
    <td style="padding:32px 36px 0;">
      <h2 style="color:#15803d;font-size:22px;margin:0 0 8px;border-bottom:2px solid #15803d;padding-bottom:8px;">{$l['replaces_title']}</h2>
      <p style="color:#374151;font-size:14px;line-height:1.6;margin:0 0 16px;">{$l['replaces_intro']}</p>
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f9fafb;border-radius:12px;border:1px solid #e5e7eb;">
        <tr>
          <td style="padding:12px 16px;color:#374151;font-size:14px;font-weight:700;border-bottom:2px solid #d1d5db;">{$l['service_label']}</td>
          <td style="padding:12px 16px;color:#374151;font-size:14px;font-weight:700;text-align:right;border-bottom:2px solid #d1d5db;">{$l['monthly_cost']}</td>
        </tr>
        {$replacesHtml}
        <tr>
          <td style="padding:12px 16px;color:#15803d;font-size:15px;font-weight:700;">{$l['estimated_total']}</td>
          <td style="padding:12px 16px;color:#15803d;font-size:15px;font-weight:700;text-align:right;">~\$650–850/{$l['mo']}</td>
        </tr>
        <tr>
          <td style="padding:12px 16px;color:#15803d;font-size:15px;font-weight:700;">{$l['annual_cost']}</td>
          <td style="padding:12px 16px;color:#15803d;font-size:15px;font-weight:700;text-align:right;">\$7,800–\$10,200/{$l['yr']}</td>
        </tr>
      </table>
      <p style="color:#374151;font-size:14px;line-height:1.6;margin:16px 0 0;">{$l['replaces_outro']}</p>
    </td>
  </tr>

  <!-- ROADMAP -->
  <tr>
    <td style="padding:32px 36px 0;">
      <h2 style="color:#15803d;font-size:22px;margin:0 0 8px;border-bottom:2px solid #15803d;padding-bottom:8px;">{$l['roadmap_title']}</h2>
      <p style="color:#374151;font-size:14px;line-height:1.6;margin:0 0 16px;">{$l['roadmap_intro']}</p>
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        {$roadmapHtml}
      </table>
      <p style="color:#15803d;font-size:14px;font-weight:700;font-style:italic;margin:16px 0 0;">{$l['roadmap_value']}</p>
    </td>
  </tr>

  <!-- DOMAIN -->
  <tr>
    <td style="padding:32px 36px 0;">
      <h2 style="color:#15803d;font-size:22px;margin:0 0 8px;border-bottom:2px solid #15803d;padding-bottom:8px;">{$l['domain_title']}</h2>
      <p style="color:#374151;font-size:15px;line-height:1.7;margin:0;">{$l['domain_desc']}</p>
    </td>
  </tr>

  <!-- THE OFFER -->
  <tr>
    <td style="padding:32px 36px 0;">
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:linear-gradient(135deg,#15803d 0%,#166534 100%);border-radius:12px;">
        <tr>
          <td style="padding:28px 24px;">
            <h2 style="color:#f5d78e;font-size:22px;margin:0 0 16px;text-align:center;">{$l['offer_title']}</h2>
            <p style="color:#dcfce7;font-size:15px;line-height:1.6;margin:0 0 20px;">{$l['offer_intro']}</p>
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:rgba(255,255,255,0.12);border-radius:8px;">
              <tr><td style="padding:16px 20px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                  {$offerHtml}
                </table>
              </td></tr>
            </table>
            <p style="color:#dcfce7;font-size:14px;line-height:1.6;margin:20px 0 0;">{$l['offer_summary']}</p>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <!-- AFTER 3 MONTHS -->
  <tr>
    <td style="padding:24px 36px 0;">
      <p style="color:#15803d;font-size:16px;font-weight:700;margin:0 0 12px;">{$l['after_title']}</p>
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        {$afterHtml}
      </table>
    </td>
  </tr>

  <!-- PARTNERSHIP -->
  <tr>
    <td style="padding:32px 36px 0;">
      <h2 style="color:#15803d;font-size:22px;margin:0 0 16px;border-bottom:2px solid #15803d;padding-bottom:8px;">{$l['partnership_title']}</h2>
      <p style="color:#374151;font-size:15px;line-height:1.7;margin:0 0 16px;">{$l['partnership_intro']}</p>
      <p style="color:#374151;font-size:15px;line-height:1.7;margin:0 0 16px;">{$l['partnership_pillars_intro']}</p>
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f9fafb;border-radius:12px;border:1px solid #e5e7eb;">
        <tr><td style="padding:20px 24px;">
          <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
            {$pillarsHtml}
          </table>
        </td></tr>
      </table>
      <p style="color:#374151;font-size:15px;line-height:1.7;margin:16px 0 0;">{$l['partnership_exciting']}</p>
      <p style="color:#374151;font-size:15px;line-height:1.7;margin:16px 0 0;">{$l['partnership_together']}</p>
      <p style="color:#374151;font-size:15px;line-height:1.7;margin:16px 0 0;">{$l['partnership_logistics']}</p>
      <p style="color:#374151;font-size:15px;line-height:1.7;margin:16px 0 0;">{$l['partnership_flexible']}</p>
    </td>
  </tr>

  <!-- NEXT STEPS -->
  <tr>
    <td style="padding:32px 36px 0;">
      <h2 style="color:#15803d;font-size:22px;margin:0 0 16px;border-bottom:2px solid #15803d;padding-bottom:8px;">{$l['next_steps_title']}</h2>
      <p style="color:#374151;font-size:15px;line-height:1.7;margin:0 0 16px;">{$l['next_steps_1']}</p>
      <p style="color:#374151;font-size:15px;line-height:1.7;margin:0;">{$l['next_steps_2']}</p>
    </td>
  </tr>

  <!-- CTA BUTTON -->
  <tr>
    <td align="center" style="padding:28px 36px;">
      <table role="presentation" cellpadding="0" cellspacing="0">
        <tr>
          <td style="background:linear-gradient(135deg,#15803d,#166534);border-radius:8px;">
            <a href="{$baseUrl}" target="_blank" style="display:inline-block;padding:14px 36px;color:#ffffff;font-size:16px;font-weight:700;text-decoration:none;letter-spacing:0.5px;">{$l['cta_button']}</a>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <!-- CLOSING -->
  <tr>
    <td style="padding:0 36px 16px;">
      <p style="color:#374151;font-size:15px;line-height:1.7;margin:0;">{$l['closing']}</p>
    </td>
  </tr>

  <!-- SIGNATURE -->
  <tr>
    <td style="padding:16px 36px 32px;">
      <table role="presentation" cellpadding="0" cellspacing="0">
        <tr>
          <td style="padding-right:16px;vertical-align:top;">
            <div style="background-color:#1a1a2e;border-radius:8px;padding:8px;">
              <img src="{$onvsmLogoUrl}" alt="1vsM" width="80" style="display:block;max-width:80px;height:auto;border-radius:4px;">
            </div>
          </td>
          <td style="vertical-align:top;">
            <p style="color:#111827;font-size:16px;font-weight:700;margin:0 0 4px;">Tyrone "Mental Stamina" Norris</p>
            <p style="color:#6b7280;font-size:13px;margin:0 0 2px;">
              <a href="mailto:tyronenorris@gmail.com" style="color:#15803d;text-decoration:none;">tyronenorris@gmail.com</a>
            </p>
            <p style="color:#6b7280;font-size:13px;margin:0;">
              <a href="https://1vsM.com" style="color:#15803d;text-decoration:none;">https://1vsM.com</a>
            </p>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <!-- FOOTER -->
  <tr>
    <td style="background-color:#1a1a2e;padding:0;">
      <div style="height:3px;background:linear-gradient(90deg,#d4a843,#f5d78e,#d4a843);"></div>
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
          <td align="center" style="padding:24px 30px;">
            <p style="color:#d4a843;font-size:14px;font-weight:700;margin:0 0 6px;">Oregon Tires Auto Care</p>
            <p style="color:#9ca3af;font-size:12px;margin:0 0 4px;">8536 SE 82nd Ave, Portland, OR 97266</p>
            <p style="color:#9ca3af;font-size:12px;margin:0 0 4px;">(503) 367-9714</p>
            <p style="color:#9ca3af;font-size:12px;margin:0;">{$l['footer_hours']}</p>
          </td>
        </tr>
      </table>
    </td>
  </tr>

</table>
</td></tr>
</table>

</body>
</html>
HTML;
}

function esc(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

// ═════════════════════════════════════════════════════════════════════════════
// Content (EN / ES)
// ═════════════════════════════════════════════════════════════════════════════

function getEmailContent(string $lang): array
{
    if ($lang === 'es') {
        return getSpanishContent();
    }
    return getEnglishContent();
}

function getEnglishContent(): array
{
    return [
        'labels' => [
            'email_title'      => 'Your Platform is Ready — Oregon Tires Auto Care',
            'header_tagline'   => 'YOUR COMPLETE DIGITAL PLATFORM',
            'greeting'         => 'Hi Damian,',
            'intro_1'          => "I'm writing to share the full picture of what we've built for Oregon Tires Auto Care — and to talk about where we can go from here, together.",
            'intro_2'          => "Over the past months, I've built a complete digital platform designed specifically for your shop. This isn't a template or a subscription service — it's a custom system built around how your business actually works, fully functional in both English and Spanish.",
            'intro_3'          => 'This email breaks down every feature with estimated pricing, and includes a proposal for how we can partner going forward.',
            'what_we_built'    => 'What We Built — 113 Features, Built for Your Shop',
            'what_we_built_desc' => 'Everything below is live and working right now at oregon.tires.',
            'rate_disclaimer'  => 'The estimated value shown for each section is based on what a freelance developer in the US typically charges (~$50/hr) to build equivalent features from scratch. These are industry estimates to give you a clear picture of the real-world value of what\'s been built for your business.',
            'estimated_value'  => 'Estimated freelancer value',
            'total_value_title' => 'Total Platform Value',
            'features_built'   => '113 Features Built',
            'freelancer_estimate' => 'Estimated Freelancer Value (~$50/hr)',
            'total_label'      => 'TOTAL',
            'total_footnote'   => 'Estimated at standard US freelancer rates (~$50/hr). A development agency would typically charge 2–3x this amount ($125–175/hr). Each feature is valued as a standalone deliverable — what it would cost to build from scratch.',
            'replaces_title'   => 'What This Platform Replaces',
            'replaces_intro'   => 'If you were to piece together equivalent functionality using existing software subscriptions:',
            'service_label'    => 'Service',
            'monthly_cost'     => 'Typical Monthly Cost',
            'estimated_total'  => 'Estimated total',
            'annual_cost'      => 'Annual cost',
            'mo'               => 'mo',
            'yr'               => 'yr',
            'replaces_outro'   => 'Your custom platform replaces all of these — and it\'s built specifically for your business, not a generic solution.',
            'roadmap_title'    => 'Coming Soon — 12 Additional Features on the Roadmap',
            'roadmap_intro'    => 'The platform is designed to grow with your business. These enhancements are already planned:',
            'roadmap_value'    => 'Estimated additional value: $14,000+ at freelancer rates',
            'domain_title'     => 'The Perfect Domain: oregon.tires',
            'domain_desc'      => "We conducted a domain consultation together and selected <strong>oregon.tires</strong> — a premium .tires top-level domain. It's short, instantly memorable, and tells people exactly what you do and where you are. This is a premium domain that transfers to you permanently with payment.",
            'offer_title'      => 'The Offer — $5,000 Starter Package',
            'offer_intro'      => "Here's what I'm proposing to get us started:",
            'offer_items'      => [], // set below
            'offer_summary'    => "That's the entire platform, the domain, and three months of dedicated support to help you grow your business, streamline your operations, and build your brand.",
            'after_title'      => 'After the initial 3 months:',
            'partnership_title' => 'Beyond a Service — A Real Partnership',
            'partnership_intro' => "Damian, I want to be straightforward with you. I'm not just offering you a website. I'm proposing a partnership.",
            'partnership_pillars_intro' => 'I see every business as having three pillars:',
            'partnership_exciting' => "<strong>Here's where it gets exciting:</strong> You have years of experience running a tire shop — the hands-on knowledge of what customers need, what works on the ground, and how the industry operates. I have the ability to turn that knowledge into digital solutions.",
            'partnership_together' => "I'd like us to work together not just on Oregon Tires, but on building solutions that we can offer to other tire shops and auto care businesses. A platform built from real experience, not guesswork. Together, we can create something that helps other shop owners the same way this platform helps you — and we both benefit from that growth.",
            'partnership_logistics' => "I believe the logistics of running a shop can feel overwhelming. The tools I've built are designed to lift that weight so you can focus on what you do best — taking care of cars and taking care of customers. When your operations run smoother, you serve more people. When you serve more people, we both grow.",
            'partnership_flexible' => "I'm flexible on pricing and completely open to conversation. My primary goal is that you're satisfied with the platform, that it genuinely helps your business, and that we find a way to build something lasting together.",
            'next_steps_title' => 'Next Steps',
            'next_steps_1'     => "I'd love to sit down with you and walk through the platform together. Everything is live right now at <strong>oregon.tires</strong> — you can explore it anytime.",
            'next_steps_2'     => "Reply to this email, or call me. I'm available in English and Spanish, and I'm here to help however I can. Let's build something great together.",
            'cta_button'       => 'Visit oregon.tires',
            'closing'          => 'Best regards,',
            'footer_hours'     => 'Monday–Saturday 7:00 AM – 7:00 PM',
        ],
        'offer_items' => [
            '<span style="color:#f5d78e;font-weight:700;">Full ownership of oregon.tires</span> <span style="color:#dcfce7;">— the domain is yours, permanently</span>',
            '<span style="color:#f5d78e;font-weight:700;">The complete platform</span> <span style="color:#dcfce7;">— all 113 features, live and working today</span>',
            '<span style="color:#f5d78e;font-weight:700;">3 months of managed hosting</span> <span style="color:#dcfce7;">— server management, SSL certificates, backups, security patches, Cloudflare CDN, and uptime monitoring ($50/mo value)</span>',
            '<span style="color:#f5d78e;font-weight:700;">3 months of marketing &amp; management support</span> <span style="color:#dcfce7;">— SEO optimization, content updates, blog management, social media support, Google Business management, analytics reporting, platform enhancements, and daily business support (starting at $2,000/mo value)</span>',
        ],
        'after_items' => [
            'Managed hosting continues at <strong>$50/month</strong>',
            'Marketing &amp; management services available <strong>starting at $2,000/month</strong>',
            'All pricing is flexible — we can customize a plan that works for your budget',
        ],
        'pillars' => [
            ['title' => 'Mind — Your Digital Presence', 'desc' => 'Your website, booking system, customer portal, admin tools. This is the intelligence behind your operation — how information flows, how customers interact with you online, how your team stays organized. This is already built and live at oregon.tires.'],
            ['title' => 'Body — Your Physical Business', 'desc' => 'Your shop, your team, your daily operations, your revenue. The systems I\'ve built (repair orders, scheduling, employee management, invoicing, inspections) are designed to make your day-to-day work smoother and more profitable. Better operations mean more cars through the door.'],
            ['title' => 'Soul — Your Brand &amp; Community', 'desc' => 'Marketing, social media, Google Business profile, customer loyalty programs, referrals, reviews. This is how people discover you, learn to trust you, and become repeat customers. This is where ongoing marketing and management support creates the most impact.'],
        ],
        'sections' => [
            [
                'title'       => 'Your Online Presence — 25 features',
                'title_short' => 'Your Online Presence (25)',
                'subtitle'    => 'A bilingual, mobile-first website that makes a strong first impression',
                'value'       => '$9,200',
                'features'    => [
                    'Custom responsive website with dark mode toggle',
                    'Full bilingual system — English/Spanish on every page, every email, every notification',
                    'Homepage with dynamic hero section, 7 service feature cards, reviews, and gallery',
                    '10 individual service pages (tire installation, brakes, oil changes, alignment, and more)',
                    '8 regional SEO pages targeting Portland neighborhoods (SE Portland, Woodstock, Lents, Happy Valley, etc.)',
                    'Blog, FAQ, promotions, and testimonials — all manageable from your admin panel',
                    'Photo gallery with video support and bilingual captions',
                    'Google Reviews displayed live directly from your Google Business profile',
                    'Care plan enrollment page with PayPal subscription payments',
                    'Checkout, financing info, customer feedback, and system status pages',
                    'SSL security, clean professional URLs, SEO meta tags, and structured data for Google',
                ],
            ],
            [
                'title'       => 'Online Booking System — 9 features',
                'title_short' => 'Online Booking System (9)',
                'subtitle'    => 'Your customers book online. You get organized automatically.',
                'value'       => '$4,400',
                'features'    => [
                    'Online appointment booking with real-time available time slots',
                    'VIN number lookup — customers type their VIN and vehicle info fills in automatically (NHTSA database)',
                    'License plate lookup — enter a plate number, get the vehicle details instantly',
                    'Appointment cancellation and rescheduling via secure email links (no login needed)',
                    'SMS opt-in during booking + bilingual confirmation emails with calendar links',
                    'Downloadable calendar events (.ics) so appointments sync to any phone or computer',
                    'Configurable business hours and holiday calendar — you control when slots are available',
                    'Multi-bay capacity with employee schedule-aware slot availability',
                    'Service-specific booking fields (tire preference new/used, tire count, service type)',
                ],
            ],
            [
                'title'       => 'Shop Operations — Repair Orders — 8 features',
                'title_short' => 'Shop Operations (8)',
                'subtitle'    => 'Your complete digital workflow from intake to invoice',
                'value'       => '$6,700',
                'features'    => [
                    'Repair Order system with 10 lifecycle stages (intake, diagnosis, estimate, approval, in-progress, completed, invoiced, and more)',
                    'Kanban board — drag-and-drop visual management of all repair orders with time-in-status tracking',
                    'Digital Vehicle Inspection (DVI) — 35 inspection items across 12 categories with traffic light ratings (green/yellow/red) and photo capture',
                    'Estimate builder — auto-generates from inspection findings, customers approve or decline each item individually',
                    'Digital invoice generation from completed repair orders with customer-facing view',
                    'Labor hours tracking per technician per repair order',
                    'Customer visit tracking with check-in and check-out timestamps',
                    'Print-optimized reports for inspections, estimates, and invoices',
                ],
            ],
            [
                'title'       => 'Customer Management & Member Portal — 10 features',
                'title_short' => 'Customer Management & Portal (10)',
                'subtitle'    => 'Know your customers. Let them manage their own accounts.',
                'value'       => '$4,500',
                'features'    => [
                    'Customer database with search — records are auto-created when someone books an appointment',
                    'Vehicle records linked to each customer (VIN, year/make/model, tire sizes)',
                    'Tire fitment lookup — enter year/make/model, get the right tire sizes',
                    'Smart account linking — booking customers are automatically connected to their member accounts',
                    'Customer language preference — the system remembers if they prefer English or Spanish',
                    'Member registration and login (bilingual interface)',
                    'Customer dashboard: My Appointments, My Vehicles, My Estimates, My Invoices',
                    'Two-way messaging — customers can message the shop directly from their portal',
                    'Care plan subscription status and billing details',
                    'Loyalty points and Refer-a-Friend dashboards',
                ],
            ],
            [
                'title'       => 'Employee Management — 6 features',
                'title_short' => 'Employee Management (6)',
                'subtitle'    => 'Organize your team and track their work',
                'value'       => '$2,600',
                'features'    => [
                    'Employee schedule management — set weekly schedules, employees view their own',
                    'Assigned work view — each employee sees their repair orders',
                    'Employee-to-customer relationships tracking',
                    'Skills and certifications tracking (searchable by admin)',
                    'Schedule overrides and per-employee daily capacity settings',
                    'Job assignment with automatic email notifications to the assigned technician',
                ],
            ],
            [
                'title'       => 'Customer Engagement & Loyalty — 6 features',
                'title_short' => 'Customer Engagement (6)',
                'subtitle'    => 'Programs that keep customers coming back',
                'value'       => '$3,200',
                'features'    => [
                    'Care plan subscriptions — 3 service tiers with PayPal recurring billing',
                    'Loyalty points program with a redeemable rewards catalog',
                    'Customer referral program — unique codes, tracking, and bonus points for both parties',
                    'Walk-in waitlist and queue management',
                    'Tire quote request system — customers submit requests, you respond from the admin panel',
                    'Roadside assistance cost estimator',
                ],
            ],
            [
                'title'       => 'Communications — 7 features',
                'title_short' => 'Communications (7)',
                'subtitle'    => 'Reach your customers in their language',
                'value'       => '$3,900',
                'features'    => [
                    'Bilingual email system — 6+ branded email template types, all in English and Spanish',
                    'In-app messaging — conversation threads between admin and customers with notification bell',
                    'Inbound email integration — customer email replies automatically appear in your messaging inbox',
                    'SMS notification system (ready to activate)',
                    'Complete email audit trail and delivery logging',
                    'Email template variable system for easy customization',
                    'Automatic estimate expiry follow-up emails',
                ],
            ],
            [
                'title'       => 'Push Notifications & Mobile App Experience — 5 features',
                'title_short' => 'Push & Mobile Experience (5)',
                'subtitle'    => 'Your shop, in your customer\'s pocket',
                'value'       => '$3,000',
                'features'    => [
                    'Progressive Web App (PWA) — customers can install your site like a real app on their phone',
                    'Web Push notifications — send bilingual, targeted notifications directly to customers\' browsers',
                    'Smart notification queue with language preferences and retry logic',
                    'Offline booking — customers can fill out the booking form even without internet; it syncs when they reconnect',
                    'Admin push broadcast tool (rate-limited for responsible use)',
                ],
            ],
            [
                'title'       => 'Admin Panel — Your Command Center — 11 features',
                'title_short' => 'Admin Panel (11)',
                'subtitle'    => 'Everything in one place',
                'value'       => '$5,300',
                'features'    => [
                    'Dashboard with analytics charts — revenue, appointments, and traffic at a glance',
                    'Appointment management with calendar and list views',
                    'Customer and vehicle management with full search',
                    'Employee management with skills and schedule configuration',
                    'Content management — Blog, FAQ, Promotions, and Testimonials, all bilingual with image upload',
                    'Gallery management with bilingual captions and video support',
                    'Newsletter subscriber management with export',
                    'Site settings and email template editor',
                    'Resource planner — multi-date scheduling with skill gap analysis and recommendations',
                    'Business hours and holiday configuration',
                    'Customer feedback management',
                ],
            ],
            [
                'title'       => 'Security, Automation & Infrastructure — 18 features',
                'title_short' => 'Security, Automation & Infrastructure (18)',
                'subtitle'    => 'Enterprise-grade reliability running behind the scenes',
                'value'       => '$9,600',
                'features'    => [
                    'Role-based access control (admin, employee, and member roles)',
                    'Google sign-in (link/unlink accounts)',
                    'Secure password reset with bilingual email flow',
                    'CSRF protection and session security',
                    'Admin invitation system via setup emails',
                    '7 automated background jobs — appointment reminders, review requests, Google Reviews refresh, push notifications, service reminders, Google Business sync, and inbound email processing',
                    'PayPal integration with subscription webhooks',
                    'Google Places API for live reviews',
                    'NHTSA VIN decode API with permanent caching',
                    'Cloudflare CDN integration',
                    'API versioning system',
                    'Data export to CSV',
                    'API rate limiting for abuse protection',
                    'Global admin search (Ctrl+K)',
                    'Advanced reporting and analytics dashboards',
                    'Image optimization pipeline (automatic modern format selection)',
                    'Error tracking (3-tier monitoring system)',
                    'Health monitoring and automated deployment system',
                ],
            ],
        ],
        'replaces' => [
            ['service' => 'Shop management software (Tekmetric, ShopBoss)', 'cost' => '$300–500/mo'],
            ['service' => 'Online appointment booking (Calendly, Acuity)',   'cost' => '$50/mo'],
            ['service' => 'Email marketing platform (Mailchimp)',            'cost' => '$50/mo'],
            ['service' => 'Push notifications service (OneSignal)',          'cost' => '$50/mo'],
            ['service' => 'Customer loyalty/referral program (Smile.io)',    'cost' => '$100/mo'],
            ['service' => 'Website + hosting',                               'cost' => '$100/mo'],
        ],
        'roadmap' => [
            'SMS & WhatsApp messaging (infrastructure ready, just needs credentials)',
            'Stripe payment integration (additional payment option)',
            'Inventory management system',
            'Online tire ordering for customers',
            'Fleet management portal for commercial accounts',
            'Automated follow-up marketing campaigns',
            'Tire recommendation engine',
            'Seasonal promotion automation',
            'Auto-assignment of jobs by technician skills',
            'Multi-location support (for when you expand)',
        ],
    ];
}

function getSpanishContent(): array
{
    return [
        'labels' => [
            'email_title'      => 'Tu Plataforma Está Lista — Oregon Tires Auto Care',
            'header_tagline'   => 'TU PLATAFORMA DIGITAL COMPLETA',
            'greeting'         => 'Hola Damian,',
            'intro_1'          => 'Te escribo para compartir el panorama completo de lo que hemos construido para Oregon Tires Auto Care — y para hablar sobre hacia dónde podemos ir juntos.',
            'intro_2'          => 'Durante los últimos meses, he construido una plataforma digital completa diseñada específicamente para tu taller. Esto no es una plantilla ni un servicio de suscripción — es un sistema personalizado construido alrededor de cómo realmente funciona tu negocio, completamente funcional en inglés y español.',
            'intro_3'          => 'Este correo detalla cada función con precios estimados, e incluye una propuesta de cómo podemos trabajar juntos en el futuro.',
            'what_we_built'    => 'Lo Que Construimos — 113 Funciones, Hechas Para Tu Taller',
            'what_we_built_desc' => 'Todo lo que se muestra abajo está en vivo y funcionando ahora mismo en oregon.tires.',
            'rate_disclaimer'  => 'El valor estimado que se muestra para cada sección se basa en lo que un desarrollador freelance en EE.UU. normalmente cobra (~$50/hr) para construir funciones equivalentes desde cero. Estas son estimaciones de la industria para darte una imagen clara del valor real de lo que se ha construido para tu negocio.',
            'estimated_value'  => 'Valor estimado freelancer',
            'total_value_title' => 'Valor Total de la Plataforma',
            'features_built'   => '113 Funciones Construidas',
            'freelancer_estimate' => 'Valor Estimado Freelancer (~$50/hr)',
            'total_label'      => 'TOTAL',
            'total_footnote'   => 'Estimado a tarifas estándar de freelancer en EE.UU. (~$50/hr). Una agencia de desarrollo típicamente cobra 2–3 veces este monto ($125–175/hr). Cada función se valora como un entregable independiente — lo que costaría construirla desde cero.',
            'replaces_title'   => 'Lo Que Esta Plataforma Reemplaza',
            'replaces_intro'   => 'Si tuvieras que armar funcionalidad equivalente usando suscripciones de software existentes:',
            'service_label'    => 'Servicio',
            'monthly_cost'     => 'Costo Mensual Típico',
            'estimated_total'  => 'Total estimado',
            'annual_cost'      => 'Costo anual',
            'mo'               => 'mes',
            'yr'               => 'año',
            'replaces_outro'   => 'Tu plataforma personalizada reemplaza todos estos — y está construida específicamente para tu negocio, no es una solución genérica.',
            'roadmap_title'    => 'Próximamente — 12 Funciones Adicionales en el Plan',
            'roadmap_intro'    => 'La plataforma está diseñada para crecer con tu negocio. Estas mejoras ya están planificadas:',
            'roadmap_value'    => 'Valor adicional estimado: $14,000+ a tarifas de freelancer',
            'domain_title'     => 'El Dominio Perfecto: oregon.tires',
            'domain_desc'      => 'Realizamos una consulta de dominio juntos y seleccionamos <strong>oregon.tires</strong> — un dominio premium de nivel superior .tires. Es corto, instantáneamente memorable, y le dice a la gente exactamente lo que haces y dónde estás. Este es un dominio premium que se transfiere a ti permanentemente con el pago.',
            'offer_title'      => 'La Oferta — Paquete Inicial de $5,000',
            'offer_intro'      => 'Esto es lo que propongo para comenzar:',
            'offer_items'      => [],
            'offer_summary'    => 'Eso es toda la plataforma, el dominio, y tres meses de soporte dedicado para ayudarte a hacer crecer tu negocio, optimizar tus operaciones y construir tu marca.',
            'after_title'      => 'Después de los 3 meses iniciales:',
            'partnership_title' => 'Más Allá de un Servicio — Una Verdadera Asociación',
            'partnership_intro' => 'Damian, quiero ser directo contigo. No solo te estoy ofreciendo un sitio web. Te estoy proponiendo una asociación.',
            'partnership_pillars_intro' => 'Veo cada negocio como teniendo tres pilares:',
            'partnership_exciting' => '<strong>Aquí es donde se pone emocionante:</strong> Tienes años de experiencia manejando un taller de llantas — el conocimiento práctico de lo que los clientes necesitan, lo que funciona en el terreno, y cómo opera la industria. Yo tengo la capacidad de convertir ese conocimiento en soluciones digitales.',
            'partnership_together' => 'Me gustaría que trabajemos juntos no solo en Oregon Tires, sino en construir soluciones que podamos ofrecer a otros talleres de llantas y negocios de cuidado automotriz. Una plataforma construida desde experiencia real, no desde suposiciones. Juntos, podemos crear algo que ayude a otros dueños de talleres de la misma manera que esta plataforma te ayuda a ti — y ambos nos beneficiamos de ese crecimiento.',
            'partnership_logistics' => 'Creo que la logística de manejar un taller puede sentirse abrumadora. Las herramientas que he construido están diseñadas para levantar ese peso para que puedas enfocarte en lo que mejor haces — cuidar autos y cuidar clientes. Cuando tus operaciones funcionan mejor, atiendes a más personas. Cuando atiendes a más personas, ambos crecemos.',
            'partnership_flexible' => 'Soy flexible con los precios y completamente abierto a conversar. Mi objetivo principal es que estés satisfecho con la plataforma, que genuinamente ayude a tu negocio, y que encontremos una manera de construir algo duradero juntos.',
            'next_steps_title' => 'Próximos Pasos',
            'next_steps_1'     => 'Me encantaría sentarme contigo y recorrer la plataforma juntos. Todo está en vivo ahora mismo en <strong>oregon.tires</strong> — puedes explorarlo cuando quieras.',
            'next_steps_2'     => 'Responde a este correo, o llámame. Estoy disponible en inglés y español, y estoy aquí para ayudar en lo que pueda. Construyamos algo grandioso juntos.',
            'cta_button'       => 'Visita oregon.tires',
            'closing'          => 'Saludos cordiales,',
            'footer_hours'     => 'Lunes–Sábado 7:00 AM – 7:00 PM',
        ],
        'offer_items' => [
            '<span style="color:#f5d78e;font-weight:700;">Propiedad total de oregon.tires</span> <span style="color:#dcfce7;">— el dominio es tuyo, permanentemente</span>',
            '<span style="color:#f5d78e;font-weight:700;">La plataforma completa</span> <span style="color:#dcfce7;">— las 113 funciones, en vivo y funcionando hoy</span>',
            '<span style="color:#f5d78e;font-weight:700;">3 meses de hosting administrado</span> <span style="color:#dcfce7;">— administración del servidor, certificados SSL, respaldos, parches de seguridad, Cloudflare CDN, y monitoreo de disponibilidad (valor de $50/mes)</span>',
            '<span style="color:#f5d78e;font-weight:700;">3 meses de soporte de marketing y administración</span> <span style="color:#dcfce7;">— optimización SEO, actualizaciones de contenido, gestión de blog, soporte de redes sociales, gestión de Google Business, reportes de analítica, mejoras a la plataforma, y soporte diario del negocio (valor desde $2,000/mes)</span>',
        ],
        'after_items' => [
            'El hosting administrado continúa a <strong>$50/mes</strong>',
            'Servicios de marketing y administración disponibles <strong>desde $2,000/mes</strong>',
            'Todos los precios son flexibles — podemos personalizar un plan que funcione para tu presupuesto',
        ],
        'pillars' => [
            ['title' => 'Mente — Tu Presencia Digital', 'desc' => 'Tu sitio web, sistema de reservas, portal de clientes, herramientas de administración. Esta es la inteligencia detrás de tu operación — cómo fluye la información, cómo los clientes interactúan contigo en línea, cómo tu equipo se mantiene organizado. Esto ya está construido y en vivo en oregon.tires.'],
            ['title' => 'Cuerpo — Tu Negocio Físico', 'desc' => 'Tu taller, tu equipo, tus operaciones diarias, tus ingresos. Los sistemas que he construido (órdenes de reparación, programación, gestión de empleados, facturación, inspecciones) están diseñados para hacer tu trabajo diario más fluido y más rentable. Mejores operaciones significan más autos atendidos.'],
            ['title' => 'Alma — Tu Marca y Comunidad', 'desc' => 'Marketing, redes sociales, perfil de Google Business, programas de lealtad, referidos, reseñas. Así es como la gente te descubre, aprende a confiar en ti, y se convierte en cliente recurrente. Aquí es donde el soporte continuo de marketing y administración crea el mayor impacto.'],
        ],
        'sections' => [
            [
                'title'       => 'Tu Presencia en Línea — 25 funciones',
                'title_short' => 'Tu Presencia en Línea (25)',
                'subtitle'    => 'Un sitio web bilingüe y mobile-first que causa una gran primera impresión',
                'value'       => '$9,200',
                'features'    => [
                    'Sitio web responsivo personalizado con modo oscuro',
                    'Sistema bilingüe completo — inglés/español en cada página, cada correo, cada notificación',
                    'Página principal con sección hero dinámica, 7 tarjetas de servicios, reseñas y galería',
                    '10 páginas individuales de servicios (instalación de llantas, frenos, cambios de aceite, alineación y más)',
                    '8 páginas SEO regionales enfocadas en vecindarios de Portland (SE Portland, Woodstock, Lents, Happy Valley, etc.)',
                    'Blog, FAQ, promociones y testimonios — todo administrable desde tu panel de admin',
                    'Galería de fotos con soporte de video y subtítulos bilingües',
                    'Reseñas de Google mostradas en vivo directamente desde tu perfil de Google Business',
                    'Página de inscripción a planes de cuidado con pagos de suscripción PayPal',
                    'Páginas de checkout, información de financiamiento, retroalimentación de clientes y estado del sistema',
                    'Seguridad SSL, URLs profesionales limpias, meta tags SEO y datos estructurados para Google',
                ],
            ],
            [
                'title'       => 'Sistema de Reservas en Línea — 9 funciones',
                'title_short' => 'Sistema de Reservas (9)',
                'subtitle'    => 'Tus clientes reservan en línea. Tú te organizas automáticamente.',
                'value'       => '$4,400',
                'features'    => [
                    'Reserva de citas en línea con horarios disponibles en tiempo real',
                    'Búsqueda por VIN — los clientes escriben su VIN y la información del vehículo se llena automáticamente (base de datos NHTSA)',
                    'Búsqueda por placa — ingresa un número de placa, obtén los detalles del vehículo al instante',
                    'Cancelación y reprogramación de citas mediante enlaces seguros por correo (sin necesidad de iniciar sesión)',
                    'Opción de SMS durante la reserva + correos de confirmación bilingües con enlaces de calendario',
                    'Eventos de calendario descargables (.ics) para sincronizar citas con cualquier teléfono o computadora',
                    'Horarios de negocio y calendario de días festivos configurables — tú controlas cuándo hay espacios disponibles',
                    'Capacidad multi-bahía con disponibilidad basada en horarios de empleados',
                    'Campos de reserva específicos por servicio (preferencia de llantas nuevas/usadas, cantidad de llantas, tipo de servicio)',
                ],
            ],
            [
                'title'       => 'Operaciones del Taller — Órdenes de Reparación — 8 funciones',
                'title_short' => 'Operaciones del Taller (8)',
                'subtitle'    => 'Tu flujo de trabajo digital completo desde recepción hasta facturación',
                'value'       => '$6,700',
                'features'    => [
                    'Sistema de Órdenes de Reparación con 10 etapas de ciclo de vida (recepción, diagnóstico, estimado, aprobación, en progreso, completado, facturado y más)',
                    'Tablero Kanban — gestión visual de arrastrar y soltar de todas las órdenes de reparación con seguimiento de tiempo en estado',
                    'Inspección Digital de Vehículo (DVI) — 35 ítems de inspección en 12 categorías con calificaciones de semáforo (verde/amarillo/rojo) y captura de fotos',
                    'Constructor de estimados — se genera automáticamente desde los hallazgos de inspección, los clientes aprueban o rechazan cada ítem individualmente',
                    'Generación de facturas digitales desde órdenes completadas con vista para el cliente',
                    'Seguimiento de horas de trabajo por técnico por orden de reparación',
                    'Seguimiento de visitas de clientes con marcas de tiempo de entrada y salida',
                    'Reportes optimizados para impresión de inspecciones, estimados y facturas',
                ],
            ],
            [
                'title'       => 'Gestión de Clientes y Portal de Miembros — 10 funciones',
                'title_short' => 'Gestión de Clientes y Portal (10)',
                'subtitle'    => 'Conoce a tus clientes. Déjalos administrar sus propias cuentas.',
                'value'       => '$4,500',
                'features'    => [
                    'Base de datos de clientes con búsqueda — los registros se crean automáticamente cuando alguien reserva una cita',
                    'Registros de vehículos vinculados a cada cliente (VIN, año/marca/modelo, tamaños de llantas)',
                    'Búsqueda de ajuste de llantas — ingresa año/marca/modelo, obtén los tamaños correctos',
                    'Vinculación inteligente de cuentas — los clientes que reservan se conectan automáticamente a sus cuentas de miembro',
                    'Preferencia de idioma del cliente — el sistema recuerda si prefieren inglés o español',
                    'Registro e inicio de sesión de miembros (interfaz bilingüe)',
                    'Panel del cliente: Mis Citas, Mis Vehículos, Mis Estimados, Mis Facturas',
                    'Mensajería bidireccional — los clientes pueden enviar mensajes al taller directamente desde su portal',
                    'Estado de suscripción al plan de cuidado y detalles de facturación',
                    'Paneles de puntos de lealtad y Refiere a un Amigo',
                ],
            ],
            [
                'title'       => 'Gestión de Empleados — 6 funciones',
                'title_short' => 'Gestión de Empleados (6)',
                'subtitle'    => 'Organiza a tu equipo y rastrea su trabajo',
                'value'       => '$2,600',
                'features'    => [
                    'Gestión de horarios de empleados — establece horarios semanales, los empleados ven los suyos',
                    'Vista de trabajo asignado — cada empleado ve sus órdenes de reparación',
                    'Seguimiento de relaciones empleado-cliente',
                    'Seguimiento de habilidades y certificaciones (buscable por admin)',
                    'Anulaciones de horario y configuración de capacidad diaria por empleado',
                    'Asignación de trabajos con notificaciones automáticas por correo al técnico asignado',
                ],
            ],
            [
                'title'       => 'Compromiso del Cliente y Lealtad — 6 funciones',
                'title_short' => 'Compromiso del Cliente (6)',
                'subtitle'    => 'Programas que hacen que los clientes regresen',
                'value'       => '$3,200',
                'features'    => [
                    'Suscripciones a planes de cuidado — 3 niveles de servicio con facturación recurrente de PayPal',
                    'Programa de puntos de lealtad con catálogo de recompensas canjeables',
                    'Programa de referidos — códigos únicos, seguimiento y puntos de bonificación para ambas partes',
                    'Lista de espera y gestión de cola para walk-ins',
                    'Sistema de solicitud de cotización de llantas — los clientes envían solicitudes, tú respondes desde el panel de admin',
                    'Estimador de costos de asistencia en carretera',
                ],
            ],
            [
                'title'       => 'Comunicaciones — 7 funciones',
                'title_short' => 'Comunicaciones (7)',
                'subtitle'    => 'Llega a tus clientes en su idioma',
                'value'       => '$3,900',
                'features'    => [
                    'Sistema de correo bilingüe — 6+ tipos de plantillas de correo con marca, todos en inglés y español',
                    'Mensajería en la app — hilos de conversación entre admin y clientes con campana de notificación',
                    'Integración de correo entrante — las respuestas de correo de los clientes aparecen automáticamente en tu bandeja de mensajes',
                    'Sistema de notificaciones SMS (listo para activar)',
                    'Registro completo de auditoría de correos y seguimiento de entregas',
                    'Sistema de variables de plantillas de correo para personalización fácil',
                    'Correos automáticos de seguimiento por vencimiento de estimados',
                ],
            ],
            [
                'title'       => 'Notificaciones Push y Experiencia de App Móvil — 5 funciones',
                'title_short' => 'Push y Experiencia Móvil (5)',
                'subtitle'    => 'Tu taller, en el bolsillo de tu cliente',
                'value'       => '$3,000',
                'features'    => [
                    'Progressive Web App (PWA) — los clientes pueden instalar tu sitio como una app real en su teléfono',
                    'Notificaciones Web Push — envía notificaciones bilingües y dirigidas directamente a los navegadores de los clientes',
                    'Cola de notificaciones inteligente con preferencias de idioma y lógica de reintento',
                    'Reserva sin conexión — los clientes pueden llenar el formulario de reserva sin internet; se sincroniza cuando se reconectan',
                    'Herramienta de transmisión push para admin (con límite de frecuencia para uso responsable)',
                ],
            ],
            [
                'title'       => 'Panel de Admin — Tu Centro de Comando — 11 funciones',
                'title_short' => 'Panel de Admin (11)',
                'subtitle'    => 'Todo en un solo lugar',
                'value'       => '$5,300',
                'features'    => [
                    'Panel con gráficas de analítica — ingresos, citas y tráfico de un vistazo',
                    'Gestión de citas con vistas de calendario y lista',
                    'Gestión de clientes y vehículos con búsqueda completa',
                    'Gestión de empleados con configuración de habilidades y horarios',
                    'Gestión de contenido — Blog, FAQ, Promociones y Testimonios, todo bilingüe con carga de imágenes',
                    'Gestión de galería con subtítulos bilingües y soporte de video',
                    'Gestión de suscriptores del newsletter con exportación',
                    'Configuración del sitio y editor de plantillas de correo',
                    'Planificador de recursos — programación multi-fecha con análisis de brechas de habilidades y recomendaciones',
                    'Configuración de horarios de negocio y días festivos',
                    'Gestión de retroalimentación de clientes',
                ],
            ],
            [
                'title'       => 'Seguridad, Automatización e Infraestructura — 18 funciones',
                'title_short' => 'Seguridad, Automatización e Infraestructura (18)',
                'subtitle'    => 'Confiabilidad de nivel empresarial funcionando tras bambalinas',
                'value'       => '$9,600',
                'features'    => [
                    'Control de acceso basado en roles (roles de admin, empleado y miembro)',
                    'Inicio de sesión con Google (vincular/desvincular cuentas)',
                    'Restablecimiento seguro de contraseña con flujo de correo bilingüe',
                    'Protección CSRF y seguridad de sesión',
                    'Sistema de invitación de admin mediante correos de configuración',
                    '7 trabajos automatizados en segundo plano — recordatorios de citas, solicitudes de reseñas, actualización de Google Reviews, notificaciones push, recordatorios de servicio, sincronización de Google Business y procesamiento de correo entrante',
                    'Integración con PayPal con webhooks de suscripción',
                    'API de Google Places para reseñas en vivo',
                    'API de decodificación VIN de NHTSA con caché permanente',
                    'Integración CDN de Cloudflare',
                    'Sistema de versionamiento de API',
                    'Exportación de datos a CSV',
                    'Limitación de tasa de API para protección contra abuso',
                    'Búsqueda global de admin (Ctrl+K)',
                    'Paneles avanzados de reportes y analítica',
                    'Pipeline de optimización de imágenes (selección automática de formato moderno)',
                    'Rastreo de errores (sistema de monitoreo de 3 niveles)',
                    'Monitoreo de salud y sistema de despliegue automatizado',
                ],
            ],
        ],
        'replaces' => [
            ['service' => 'Software de gestión de taller (Tekmetric, ShopBoss)', 'cost' => '$300–500/mes'],
            ['service' => 'Reserva de citas en línea (Calendly, Acuity)',         'cost' => '$50/mes'],
            ['service' => 'Plataforma de email marketing (Mailchimp)',            'cost' => '$50/mes'],
            ['service' => 'Servicio de notificaciones push (OneSignal)',           'cost' => '$50/mes'],
            ['service' => 'Programa de lealtad/referidos (Smile.io)',             'cost' => '$100/mes'],
            ['service' => 'Sitio web + hosting',                                   'cost' => '$100/mes'],
        ],
        'roadmap' => [
            'Mensajería SMS y WhatsApp (infraestructura lista, solo necesita credenciales)',
            'Integración de pagos con Stripe (opción de pago adicional)',
            'Sistema de gestión de inventario',
            'Pedidos de llantas en línea para clientes',
            'Portal de gestión de flotas para cuentas comerciales',
            'Campañas de marketing automatizadas de seguimiento',
            'Motor de recomendación de llantas',
            'Automatización de promociones estacionales',
            'Asignación automática de trabajos por habilidades del técnico',
            'Soporte multi-ubicación (para cuando te expandas)',
        ],
    ];
}
