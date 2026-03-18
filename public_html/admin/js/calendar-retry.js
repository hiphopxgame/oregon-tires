// Oregon Tires Admin — Calendar Sync Retry
// Extracted from admin/index.html inline script

async function retryCalendarSync(appointmentId) {
  try {
    var res = await fetch('/api/admin/calendar-retry-sync.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '' },
      credentials: 'include',
      body: JSON.stringify({ appointment_id: appointmentId })
    });
    var json = await res.json();
    if (json.success) {
      showToast('Calendar sync retried successfully');
      loadAppointments();
    } else {
      showToast(json.error || 'Retry failed', true);
    }
  } catch (err) {
    console.error('retryCalendarSync error:', err);
    showToast('Network error', true);
  }
}
