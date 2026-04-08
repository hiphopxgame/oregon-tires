<?php
$secret = 'OT_SETUP_2026';
if (($_GET['key'] ?? '') !== $secret) { http_response_code(403); die('Forbidden'); }

error_reporting(E_ALL);
ini_set('display_errors', '1');

$action = $_GET['action'] ?? '';
$home = '/home2/avadpnmy';
$domain = 'oregon.tires';

switch ($action) {
    case 'get-dkim':
        // Try multiple methods to find BlueHost's DKIM key

        // Method 1: cPanel UAPI
        $cmd = "/usr/local/cpanel/bin/uapi Email get_dkim_records domain=$domain --output=json 2>&1";
        exec($cmd, $out1, $rc1);
        $json1 = implode('', $out1);
        echo "UAPI Email get_dkim_records (rc=$rc1):\n$json1\n\n";

        // Method 2: cPanel API2
        $cmd2 = "/usr/local/cpanel/bin/cpapi2 --user=avadpnmy Email listdkims --output=json 2>&1";
        exec($cmd2, $out2, $rc2);
        echo "API2 listdkims (rc=$rc2):\n" . implode('', $out2) . "\n\n";

        // Method 3: Check cPanel's DKIM storage
        $dkimPaths = [
            "/var/cpanel/domain_keys/private/$domain",
            "/var/cpanel/domain_keys/public/$domain",
            "$home/etc/$domain/dkim.private",
            "$home/etc/$domain/dkim.public",
            "$home/.dkim/$domain.pem",
            "/etc/domainkeys/$domain/default",
        ];
        echo "File search:\n";
        foreach ($dkimPaths as $p) {
            $exists = @file_exists($p);
            if ($exists) {
                echo "  FOUND: $p\n";
                $content = @file_get_contents($p);
                if ($content) echo "  Content: " . substr($content, 0, 200) . "...\n";
            }
        }

        // Method 4: UAPI for email deliverability
        $cmd3 = "/usr/local/cpanel/bin/uapi Email get_main_account_status --output=json 2>&1";
        exec($cmd3, $out3, $rc3);
        echo "\nUAPI get_main_account_status (rc=$rc3):\n" . implode('', $out3) . "\n\n";

        // Method 5: Try to install DKIM
        $cmd4 = "/usr/local/cpanel/bin/uapi Email install_dkim_private_keys domain=$domain --output=json 2>&1";
        exec($cmd4, $out4, $rc4);
        echo "UAPI install_dkim (rc=$rc4):\n" . implode('', $out4) . "\n\n";

        // Method 6: Get suggested records
        $cmd5 = "/usr/local/cpanel/bin/uapi Email get_client_settings domain=$domain --output=json 2>&1";
        exec($cmd5, $out5, $rc5);
        echo "UAPI get_client_settings (rc=$rc5):\n" . implode('', $out5) . "\n\n";

        // Method 7: Zone editor - list records
        $cmd6 = "/usr/local/cpanel/bin/uapi DNS parse_zone zone=$domain --output=json 2>&1";
        exec($cmd6, $out6, $rc6);
        $parsed = json_decode(implode('', $out6), true);
        if (!empty($parsed['result']['data'])) {
            echo "Zone records with DKIM:\n";
            foreach ($parsed['result']['data'] as $rec) {
                $name = $rec['dname'] ?? $rec['name'] ?? '';
                if (stripos($name, 'dkim') !== false || stripos($name, '_domainkey') !== false) {
                    echo "  " . json_encode($rec) . "\n";
                }
            }
        } else {
            echo "DNS parse_zone (rc=$rc6): " . implode('', $out6) . "\n";
        }
        break;

    case 'ensure-dkim':
        // Ensure DKIM is enabled and get the public key
        $cmd = "/usr/local/cpanel/bin/uapi Email ensure_dkim_keys_present domain=$domain --output=json 2>&1";
        exec($cmd, $out, $rc);
        echo "ensure_dkim_keys_present (rc=$rc):\n" . implode('', $out) . "\n";
        break;

    case 'cleanup':
        @unlink(__FILE__);
        echo "Removed.";
        break;

    default:
        echo "Actions: get-dkim, ensure-dkim, cleanup";
}
