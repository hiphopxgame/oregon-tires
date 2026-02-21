<?php
/**
 * Oregon Tires — Calendar Event Download (.ics)
 * GET /api/calendar-event.php?ref=OT-XXXXXXXX
 *
 * Generates an ICS file for a booked appointment.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

try {
    requireMethod('GET');

    $ref = sanitize((string) ($_GET['ref'] ?? ''), 20);
    if (empty($ref) || !preg_match('/^OT-[A-Z0-9]{8}$/', $ref)) {
        jsonError('Invalid reference number.', 400);
    }

    $db = getDB();
    $stmt = $db->prepare(
        'SELECT service, preferred_date, preferred_time, first_name, last_name, email
         FROM oretir_appointments WHERE reference_number = ? LIMIT 1'
    );
    $stmt->execute([$ref]);
    $appt = $stmt->fetch();

    if (!$appt) {
        jsonError('Appointment not found.', 404);
    }

    $serviceDisplay = ucwords(str_replace('-', ' ', $appt['service']));
    $customerName   = trim($appt['first_name'] . ' ' . $appt['last_name']);

    // Build datetime in Pacific time
    $tz = new DateTimeZone('America/Los_Angeles');
    $start = new DateTime($appt['preferred_date'] . ' ' . $appt['preferred_time'], $tz);
    $end   = clone $start;
    $end->modify('+1 hour');

    // Format for ICS (local time with TZID)
    $dtStart = $start->format('Ymd\THis');
    $dtEnd   = $end->format('Ymd\THis');
    $dtstamp = gmdate('Ymd\THis\Z');
    $uid     = strtolower($ref) . '@oregon.tires';

    // Escape ICS text fields
    $icsEscape = fn(string $s): string => str_replace(
        ["\r\n", "\n", "\r", ',', ';', '\\'],
        ['\\n', '\\n', '\\n', '\\,', '\\;', '\\\\'],
        $s
    );

    $summary     = $icsEscape("{$serviceDisplay} — Oregon Tires Auto Care");
    $description = $icsEscape("Appointment Ref: {$ref}\nService: {$serviceDisplay}\nCustomer: {$customerName}\n\nOregon Tires Auto Care\n(503) 367-9714");
    $location    = $icsEscape("Oregon Tires Auto Care, 8536 SE 82nd Ave, Portland, OR 97266");

    $ics = "BEGIN:VCALENDAR\r\n"
         . "VERSION:2.0\r\n"
         . "PRODID:-//Oregon Tires Auto Care//Booking//EN\r\n"
         . "CALSCALE:GREGORIAN\r\n"
         . "METHOD:PUBLISH\r\n"
         . "BEGIN:VTIMEZONE\r\n"
         . "TZID:America/Los_Angeles\r\n"
         . "BEGIN:STANDARD\r\n"
         . "DTSTART:19701101T020000\r\n"
         . "RRULE:FREQ=YEARLY;BYMONTH=11;BYDAY=1SU\r\n"
         . "TZOFFSETFROM:-0700\r\n"
         . "TZOFFSETTO:-0800\r\n"
         . "TZNAME:PST\r\n"
         . "END:STANDARD\r\n"
         . "BEGIN:DAYLIGHT\r\n"
         . "DTSTART:19700308T020000\r\n"
         . "RRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=2SU\r\n"
         . "TZOFFSETFROM:-0800\r\n"
         . "TZOFFSETTO:-0700\r\n"
         . "TZNAME:PDT\r\n"
         . "END:DAYLIGHT\r\n"
         . "END:VTIMEZONE\r\n"
         . "BEGIN:VEVENT\r\n"
         . "UID:{$uid}\r\n"
         . "DTSTAMP:{$dtstamp}\r\n"
         . "DTSTART;TZID=America/Los_Angeles:{$dtStart}\r\n"
         . "DTEND;TZID=America/Los_Angeles:{$dtEnd}\r\n"
         . "SUMMARY:{$summary}\r\n"
         . "DESCRIPTION:{$description}\r\n"
         . "LOCATION:{$location}\r\n"
         . "STATUS:CONFIRMED\r\n"
         . "END:VEVENT\r\n"
         . "END:VCALENDAR\r\n";

    header('Content-Type: text/calendar; charset=utf-8');
    header('Content-Disposition: attachment; filename="oregon-tires-' . strtolower($ref) . '.ics"');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    echo $ics;
    exit;

} catch (\Throwable $e) {
    error_log("Oregon Tires calendar-event.php error: " . $e->getMessage());
    jsonError('Server error', 500);
}
