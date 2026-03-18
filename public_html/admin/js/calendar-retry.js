// Oregon Tires Admin — Calendar Sync Retry
// Extracted from admin/index.html inline script

function t(key, fb) {
  return (typeof adminT !== 'undefined' && adminT[currentLang] && adminT[currentLang][key]) || fb;
}

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
      showToast(t('calSyncRetried', 'Calendar sync retried successfully'));
      loadAppointments();
    } else {
      showToast(json.error || t('calRetryFailed', 'Retry failed'), true);
    }
  } catch (err) {
    console.error('retryCalendarSync error:', err);
    showToast(t('calNetworkError', 'Network error'), true);
  }
}
