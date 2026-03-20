<?php
/**
 * Oregon Tires — Dynamic Service Page
 * Loads service data from DB by $serviceSlug, sets template variables,
 * and renders via templates/service-detail.php.
 *
 * Usage: Set $serviceSlug before requiring this file.
 *   <?php $serviceSlug = 'tire-installation'; require __DIR__ . '/service.php';
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

if (empty($serviceSlug)) {
    http_response_code(404);
    include __DIR__ . '/404.php';
    exit;
}

$db = getDB();

// Load service
$stmt = $db->prepare('SELECT * FROM oretir_services WHERE slug = ? AND is_active = 1 AND has_detail_page = 1 LIMIT 1');
$stmt->execute([$serviceSlug]);
$service = $stmt->fetch(\PDO::FETCH_ASSOC);

if (!$service) {
    http_response_code(404);
    include __DIR__ . '/404.php';
    exit;
}

// Set template variables expected by service-detail.php
$serviceName = $service['name_en'];
$serviceNameEs = $service['name_es'];
$serviceSlug = $service['slug'];
$serviceIcon = $service['icon'];
$serviceDescription = $service['description_en'];
$serviceDescriptionEs = $service['description_es'];
$serviceBody = $service['body_en'];
$serviceBodyEs = $service['body_es'];

// Load FAQs
$faqStmt = $db->prepare(
    'SELECT question_en, question_es, answer_en, answer_es
     FROM oretir_service_faqs
     WHERE service_id = ?
     ORDER BY sort_order ASC, id ASC'
);
$faqStmt->execute([$service['id']]);
$faqRows = $faqStmt->fetchAll(\PDO::FETCH_ASSOC);

$faqItems = [];
foreach ($faqRows as $row) {
    $faqItems[] = [
        'q' => $row['question_en'],
        'a' => $row['answer_en'],
        'qEs' => $row['question_es'],
        'aEs' => $row['answer_es'],
    ];
}

// Load related services
$relStmt = $db->prepare(
    'SELECT s.name_en, s.name_es, s.slug
     FROM oretir_service_related r
     JOIN oretir_services s ON s.id = r.related_service_id AND s.is_active = 1
     WHERE r.service_id = ?
     ORDER BY r.sort_order ASC'
);
$relStmt->execute([$service['id']]);
$relRows = $relStmt->fetchAll(\PDO::FETCH_ASSOC);

$relatedServices = [];
foreach ($relRows as $row) {
    $relatedServices[] = [
        'name' => $row['name_en'],
        'nameEs' => $row['name_es'],
        'slug' => $row['slug'],
    ];
}

// Optional custom sections (roadside-assistance estimator, mobile-service coverage)
if (!empty($service['custom_sections_html'])) {
    $customSectionsBeforeCTA = $service['custom_sections_html'];
}
if (!empty($service['custom_scripts_html'])) {
    $customScripts = $service['custom_scripts_html'];
}
if (!empty($service['custom_translations'])) {
    $customTranslations = $service['custom_translations'];
}

require __DIR__ . '/templates/service-detail.php';
