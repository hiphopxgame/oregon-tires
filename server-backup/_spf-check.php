<?php
$secret = 'OT_SETUP_2026';
if (($_GET['key'] ?? '') !== $secret) { http_response_code(403); die('Forbidden'); }

// Get BlueHost's outbound mail IP and SPF info
echo "=== Server Mail Info ===\n";
echo "Hostname: " . gethostname() . "\n";
echo "Server IP: " . $_SERVER['SERVER_ADDR'] . "\n";

// Get the actual IP that mail.oregon.tires resolves to from this server
$mailIp = gethostbyname('mail.oregon.tires');
echo "mail.oregon.tires resolves to: $mailIp\n";

$serverIp = gethostbyname('oregon.tires');
echo "oregon.tires resolves to: $serverIp\n";

// Check BlueHost's mail hostname
$bhMail = gethostbyname('mail.ava.dpn.mybluehost.me');
echo "mail.ava.dpn.mybluehost.me resolves to: $bhMail\n";

// Check outbound SMTP IP by looking at Received headers
echo "\n=== Outbound Mail Test ===\n";
$headers = "From: test@oregon.tires\r\nX-Test: spf-check";
$sent = @mail('devnull@example.com', 'SPF Test', 'test', $headers);
echo "mail() available: " . ($sent ? 'yes' : 'no/blocked') . "\n";

// Check what SPF BlueHost recommends
echo "\n=== DNS Lookups ===\n";
$spfRecords = dns_get_record('bluehost.com', DNS_TXT);
foreach ($spfRecords as $r) {
    if (stripos($r['txt'] ?? '', 'spf') !== false) {
        echo "bluehost.com SPF: {$r['txt']}\n";
    }
}

// Check box hostname SPF
$boxSpf = dns_get_record('ava.dpn.mybluehost.me', DNS_TXT);
foreach ($boxSpf as $r) {
    if (stripos($r['txt'] ?? '', 'spf') !== false) {
        echo "ava.dpn.mybluehost.me SPF: {$r['txt']}\n";
    }
}

// What IP does this server use for outbound connections?
echo "\n=== Outbound IP ===\n";
exec("curl -s https://ifconfig.me 2>&1", $out);
echo "External IP: " . implode('', $out) . "\n";

// Recommended SPF
$extIp = trim(implode('', $out));
echo "\n=== Recommended SPF ===\n";
echo "v=spf1 +a +mx +ip4:$extIp include:bluehost.com ~all\n";
