<?php
require_once __DIR__ . '/includes/bootstrap.php';
$appUrl = $_ENV['APP_URL'] ?? 'https://oregon.tires';
$paypalClientId = $_ENV['PAYPAL_CLIENT_ID'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Checkout - Oregon Tires Auto Care Portland, OR</title>
  <meta name="description" content="Complete your payment for Oregon Tires Auto Care services. Pay with card or PayPal.">
  <link rel="icon" href="assets/favicon.ico" sizes="any">
  <link rel="icon" href="assets/favicon.png" type="image/png" sizes="32x32">
  <link rel="apple-touch-icon" href="assets/apple-touch-icon.png">
  <meta name="theme-color" content="#15803d">
  <meta name="msapplication-TileColor" content="#15803d">
  <link rel="canonical" href="<?= htmlspecialchars($appUrl) ?>/checkout">
  <meta name="robots" content="noindex, nofollow">

  <!-- Open Graph -->
  <meta property="og:title" content="Checkout - Oregon Tires Auto Care">
  <meta property="og:description" content="Complete your payment securely.">
  <meta property="og:type" content="website">
  <meta property="og:url" content="<?= htmlspecialchars($appUrl) ?>/checkout">
  <meta property="og:image" content="<?= htmlspecialchars($appUrl) ?>/assets/og-image.jpg">

  <!-- Tailwind CSS (built) -->
  <link rel="stylesheet" href="assets/styles.css">

  <style>
    html { scroll-behavior: smooth; }
    .fade-in { animation: fadeIn 0.6s ease-in; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .payment-method { transition: all 0.2s ease; cursor: pointer; }
    .payment-method:hover { transform: translateY(-2px); }
    .payment-method.selected { border-color: #22c55e !important; box-shadow: 0 0 0 2px #22c55e; }
  </style>

  <!-- Dark mode init (prevent FOUC) -->
  <script>(function(){if(localStorage.getItem('theme')==='dark')document.documentElement.classList.add('dark');})();</script>
</head>
<body class="bg-[#0A0A0A] text-white min-h-screen">
  <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-50 focus:bg-white focus:px-4 focus:py-2 focus:rounded-lg focus:shadow-lg focus:text-green-700 focus:font-semibold">Skip to main content</a>

  <!-- Header -->
  <header class="bg-[#111827] border-b border-gray-800 sticky top-0 z-50">
    <div class="container mx-auto px-4 py-3 flex justify-between items-center">
      <a href="/" class="flex items-center gap-3">
        <picture><source srcset="/assets/logo.webp" type="image/webp"><img src="assets/logo.png" alt="Oregon Tires Auto Care" class="h-10 w-auto" width="781" height="275" loading="eager"></picture>
      </a>
      <div class="flex items-center gap-3">
        <button onclick="toggleLanguage()" class="text-gray-400 hover:text-white font-medium text-sm transition-colors" id="lang-toggle" aria-label="Switch language">🌐 ES</button>
        <a href="/" class="text-gray-400 hover:text-emerald-400 text-sm font-medium transition-colors" data-t="back">← Back to Oregon Tires</a>
      </div>
    </div>
  </header>

  <main id="main-content" class="py-8 pb-24 md:pb-12">
    <div class="container mx-auto px-4 max-w-3xl">

      <!-- Page Title -->
      <div class="text-center mb-8 fade-in">
        <h1 class="text-3xl md:text-4xl font-bold text-white mb-2" data-t="title">Checkout</h1>
        <p class="text-gray-400 text-sm" id="page-subtitle"></p>
      </div>

      <!-- ============ SUCCESS STATE ============ -->
      <div id="state-success" class="hidden fade-in">
        <div class="bg-[#111827] border border-gray-800 rounded-xl p-8 text-center">
          <div class="w-20 h-20 bg-emerald-600 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
          </div>
          <h2 class="text-2xl font-bold text-emerald-400 mb-2" data-t="success">Payment Successful!</h2>
          <p class="text-gray-400 mb-4" data-t="successMsg">Your order has been confirmed.</p>
          <div id="success-order-ref" class="bg-[#0A0A0A] border border-gray-800 rounded-lg p-4 inline-block mb-6">
            <span class="text-gray-400 text-sm" data-t="orderRef">Order Reference</span>
            <div class="text-xl font-bold text-white font-mono mt-1" id="success-ref-value"></div>
          </div>
          <div class="flex justify-center gap-4 flex-wrap">
            <a href="/" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-lg font-semibold transition" data-t="back">Back to Oregon Tires</a>
          </div>
        </div>
      </div>

      <!-- ============ CANCELLED STATE ============ -->
      <div id="state-cancelled" class="hidden fade-in">
        <div class="bg-[#111827] border border-gray-800 rounded-xl p-8 text-center">
          <div class="w-20 h-20 bg-red-600 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
          </div>
          <h2 class="text-2xl font-bold text-red-400 mb-2" data-t="cancelled">Payment Cancelled</h2>
          <p class="text-gray-400 mb-6" data-t="cancelledMsg">Your payment was cancelled. You can try again.</p>
          <button onclick="resetToCheckout()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-lg font-semibold transition" data-t="tryAgain">Try Again</button>
        </div>
      </div>

      <!-- ============ MAIN CHECKOUT STATE ============ -->
      <div id="state-checkout" class="space-y-6 fade-in">

        <!-- Order Summary -->
        <div class="bg-[#111827] border border-gray-800 rounded-xl p-6">
          <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
            <span data-t="orderSummary">Order Summary</span>
          </h2>
          <div id="order-items" class="space-y-3">
            <!-- Items rendered by JS -->
          </div>
          <div class="border-t border-gray-700 mt-4 pt-4 flex justify-between items-center">
            <span class="text-lg font-semibold text-gray-300" data-t="total">Total</span>
            <span class="text-2xl font-bold text-emerald-400" id="order-total">$0.00</span>
          </div>
        </div>

        <!-- Payment Method -->
        <div class="bg-[#111827] border border-gray-800 rounded-xl p-6">
          <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
            <span data-t="paymentMethod">Payment Method</span>
          </h2>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <!-- Stripe -->
            <div class="payment-method bg-[#0A0A0A] border-2 border-gray-700 rounded-xl p-4 text-center" data-provider="stripe" onclick="selectPaymentMethod('stripe')">
              <div class="text-3xl mb-2">💳</div>
              <div class="font-semibold text-white text-sm" data-t="payWithCard">Pay with Card</div>
              <div class="text-xs text-gray-500 mt-1">Visa, Mastercard, Amex</div>
            </div>
            <!-- PayPal -->
            <div class="payment-method bg-[#0A0A0A] border-2 border-gray-700 rounded-xl p-4 text-center" data-provider="paypal" onclick="selectPaymentMethod('paypal')">
              <div class="text-3xl mb-2">🅿️</div>
              <div class="font-semibold text-white text-sm" data-t="payWithPaypal">Pay with PayPal</div>
              <div class="text-xs text-gray-500 mt-1">PayPal Balance or Card</div>
            </div>
          </div>
        </div>

        <!-- Customer Information -->
        <div class="bg-[#111827] border border-gray-800 rounded-xl p-6">
          <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            <span data-t="customerInfo">Customer Information</span>
          </h2>
          <div class="space-y-4">
            <div>
              <label for="customer-name" class="block text-sm font-medium text-gray-400 mb-1" data-t="name">Full Name</label>
              <input type="text" id="customer-name" name="customer_name" required autocomplete="name"
                     class="w-full p-3 bg-[#0A0A0A] border border-gray-700 rounded-lg text-white placeholder-gray-600 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label for="customer-email" class="block text-sm font-medium text-gray-400 mb-1" data-t="email">Email</label>
                <input type="email" id="customer-email" name="customer_email" required autocomplete="email"
                       class="w-full p-3 bg-[#0A0A0A] border border-gray-700 rounded-lg text-white placeholder-gray-600 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
              </div>
              <div>
                <label for="customer-phone" class="block text-sm font-medium text-gray-400 mb-1" data-t="phone">Phone</label>
                <input type="tel" id="customer-phone" name="customer_phone" required autocomplete="tel" placeholder="(503) 123-4567"
                       class="w-full p-3 bg-[#0A0A0A] border border-gray-700 rounded-lg text-white placeholder-gray-600 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
              </div>
            </div>
          </div>
        </div>

        <!-- Pay Button -->
        <div class="text-center">
          <button type="button" id="pay-btn" onclick="submitCheckout()" disabled
                  class="w-full sm:w-auto bg-emerald-600 hover:bg-emerald-700 disabled:bg-gray-700 disabled:cursor-not-allowed text-white px-10 py-4 rounded-xl font-bold text-lg transition shadow-lg">
            <span id="pay-btn-text" data-t="pay">Pay</span> <span id="pay-btn-amount">$0.00</span>
          </button>
          <p class="text-xs text-gray-500 mt-3" data-t="securePayment">Your payment is processed securely.</p>
        </div>

        <!-- Error Message -->
        <div id="checkout-error" class="hidden bg-red-900/30 border border-red-700 rounded-xl p-4 text-center text-red-400 text-sm"></div>
      </div>

    </div>
  </main>

  <!-- Footer -->
  <footer class="bg-brand text-white py-8">
    <div class="container mx-auto px-4">
      <div class="grid sm:grid-cols-3 gap-6 text-sm text-gray-200 mb-6">
        <div>
          <p class="font-semibold text-white mb-1">Oregon Tires Auto Care</p>
          <p>📍 8536 SE 82nd Ave, Portland, OR 97266</p>
        </div>
        <div>
          <p>📞 <a href="tel:5033679714" class="hover:text-amber-300">(503) 367-9714</a></p>
          <p>✉️ <a href="mailto:oregontirespdx@gmail.com" class="hover:text-amber-300">oregontirespdx@gmail.com</a></p>
        </div>
        <div class="flex items-center gap-4 sm:justify-end">
          <a href="https://www.facebook.com/61571913202998/" target="_blank" rel="noopener noreferrer" class="hover:text-amber-300">Facebook</a>
          <a href="https://www.instagram.com/oregontires" target="_blank" rel="noopener noreferrer" class="hover:text-amber-300">Instagram</a>
        </div>
      </div>
      <div class="border-t border-green-600 pt-4 text-center text-xs text-gray-300">
        <p>&copy; <span data-t="copyright">2026 Oregon Tires Auto Care. All rights reserved.</span></p>
        <p class="mt-1"><span data-t="poweredBy">Powered by</span> <a href="https://1vsM.com" target="_blank" rel="noopener noreferrer" class="text-amber-200 hover:text-amber-100">1vsM.com</a></p>
      </div>
    </div>
  </footer>

<script>
// ===== CONFIG =====
var APP_URL = <?= json_encode($appUrl) ?>;
var API_BASE = '/api';

// ===== TRANSLATIONS =====
var currentLang = (function() {
  var urlLang = new URLSearchParams(window.location.search).get('lang');
  if (urlLang === 'es' || urlLang === 'en') { localStorage.setItem('oregontires_lang', urlLang); return urlLang; }
  var saved = localStorage.getItem('oregontires_lang');
  if (saved === 'es' || saved === 'en') return saved;
  if (navigator.language && navigator.language.startsWith('es')) return 'es';
  return 'en';
})();

var t = {
  en: {
    title: 'Checkout',
    orderSummary: 'Order Summary',
    paymentMethod: 'Payment Method',
    customerInfo: 'Customer Information',
    payWithCard: 'Pay with Card',
    payWithPaypal: 'Pay with PayPal',
    success: 'Payment Successful!',
    successMsg: 'Your order has been confirmed.',
    cancelled: 'Payment Cancelled',
    cancelledMsg: 'Your payment was cancelled. You can try again.',
    tryAgain: 'Try Again',
    pay: 'Pay',
    total: 'Total',
    expiresIn: 'Expires in',
    amountDue: 'Amount Due',
    name: 'Full Name',
    email: 'Email',
    phone: 'Phone',
    back: 'Back to Oregon Tires',
    orderRef: 'Order Reference',
    securePayment: 'Your payment is processed securely.',
    selectPayment: 'Please select a payment method',
    fillRequired: 'Please fill in all required fields',
    noItems: 'No items found for checkout.',
    copyright: '2026 Oregon Tires Auto Care. All rights reserved.',
    poweredBy: 'Powered by',
    qty: 'Qty',
    submitting: 'Processing...',
    errorGeneric: 'Something went wrong. Please try again.',
    phonePlaceholder: '(503) 123-4567',
    namePlaceholder: 'John Doe',
    emailPlaceholder: 'your@email.com'
  },
  es: {
    title: 'Pagar',
    orderSummary: 'Resumen del Pedido',
    paymentMethod: 'Metodo de Pago',
    customerInfo: 'Informacion del Cliente',
    payWithCard: 'Pagar con Tarjeta',
    payWithPaypal: 'Pagar con PayPal',
    success: 'Pago Exitoso!',
    successMsg: 'Tu orden ha sido confirmada.',
    cancelled: 'Pago Cancelado',
    cancelledMsg: 'Tu pago fue cancelado. Puedes intentar de nuevo.',
    tryAgain: 'Intentar de Nuevo',
    pay: 'Pagar',
    total: 'Total',
    expiresIn: 'Expira en',
    amountDue: 'Monto a Pagar',
    name: 'Nombre Completo',
    email: 'Correo Electronico',
    phone: 'Telefono',
    back: 'Volver a Oregon Tires',
    orderRef: 'Referencia de Orden',
    securePayment: 'Tu pago se procesa de forma segura.',
    selectPayment: 'Por favor selecciona un metodo de pago',
    fillRequired: 'Por favor completa todos los campos requeridos',
    noItems: 'No se encontraron articulos para el pago.',
    copyright: '2026 Oregon Tires Auto Care. Todos los derechos reservados.',
    poweredBy: 'Desarrollado por',
    qty: 'Cant',
    submitting: 'Procesando...',
    errorGeneric: 'Algo salio mal. Por favor intenta de nuevo.',
    phonePlaceholder: '(503) 123-4567',
    namePlaceholder: 'Juan Perez',
    emailPlaceholder: 'tu@correo.com'
  }
};

// ===== STATE =====
var checkoutState = {
  items: [],
  orderRef: null,
  provider: null,
  total: 0
};

// ===== INIT =====
(function init() {
  var params = new URLSearchParams(window.location.search);
  var status = params.get('status');
  var orderRef = params.get('order_ref');
  var provider = params.get('provider');

  // Handle return states
  if (status === 'success') {
    showState('success');
    if (orderRef) {
      document.getElementById('success-ref-value').textContent = orderRef;
    }
    return;
  }

  if (status === 'cancelled') {
    checkoutState.orderRef = orderRef;
    showState('cancelled');
    return;
  }

  // Parse items from URL or fetch from order_ref
  if (params.get('items')) {
    try {
      checkoutState.items = JSON.parse(decodeURIComponent(params.get('items')));
    } catch (e) {
      checkoutState.items = [];
    }
  }

  if (orderRef) {
    checkoutState.orderRef = orderRef;
  }

  if (provider) {
    checkoutState.provider = provider;
  }

  // Pre-fill customer info from URL params
  var prefillName = params.get('name') || params.get('customer_name') || '';
  var prefillEmail = params.get('email') || params.get('customer_email') || '';
  var prefillPhone = params.get('phone') || params.get('customer_phone') || '';
  if (prefillName) document.getElementById('customer-name').value = prefillName;
  if (prefillEmail) document.getElementById('customer-email').value = prefillEmail;
  if (prefillPhone) document.getElementById('customer-phone').value = prefillPhone;

  // If we have an order_ref but no items, fetch order details
  if (checkoutState.orderRef && checkoutState.items.length === 0) {
    fetchOrderDetails(checkoutState.orderRef);
  } else if (checkoutState.items.length > 0) {
    renderOrderSummary();
    showState('checkout');
  } else {
    // Show empty state with a sample
    checkoutState.items = [];
    renderOrderSummary();
    showState('checkout');
  }

  // Pre-select payment method if specified
  if (provider && (provider === 'stripe' || provider === 'paypal')) {
    selectPaymentMethod(provider);
  }

  applyLanguage();
})();

// ===== FETCH ORDER DETAILS =====
function fetchOrderDetails(ref) {
  fetch(API_BASE + '/commerce/order?order_ref=' + encodeURIComponent(ref), {
    credentials: 'include'
  })
  .then(function(res) { return res.json(); })
  .then(function(data) {
    if (data.success && data.order) {
      checkoutState.items = data.order.items || [];
      checkoutState.total = parseFloat(data.order.total) || 0;
      if (data.order.customer_name) document.getElementById('customer-name').value = data.order.customer_name;
      if (data.order.customer_email) document.getElementById('customer-email').value = data.order.customer_email;
      if (data.order.customer_phone) document.getElementById('customer-phone').value = data.order.customer_phone;
      renderOrderSummary();
      showState('checkout');
    } else {
      showError(data.error || t[currentLang].errorGeneric);
      showState('checkout');
    }
  })
  .catch(function() {
    showError(t[currentLang].errorGeneric);
    showState('checkout');
  });
}

// ===== RENDER ORDER SUMMARY =====
function renderOrderSummary() {
  var container = document.getElementById('order-items');
  container.textContent = '';
  var total = 0;

  if (checkoutState.items.length === 0) {
    var emptyDiv = document.createElement('div');
    emptyDiv.className = 'text-center text-gray-500 py-4';
    emptyDiv.textContent = t[currentLang].noItems;
    container.appendChild(emptyDiv);
    updateTotal(0);
    return;
  }

  checkoutState.items.forEach(function(item) {
    var price = parseFloat(item.price) || 0;
    var qty = parseInt(item.quantity) || 1;
    var lineTotal = price * qty;
    total += lineTotal;

    var row = document.createElement('div');
    row.className = 'flex items-center justify-between py-2';

    var left = document.createElement('div');
    left.className = 'flex-1';

    var nameEl = document.createElement('div');
    nameEl.className = 'text-white font-medium';
    nameEl.textContent = item.name || item.description || 'Item';
    left.appendChild(nameEl);

    if (qty > 1) {
      var qtyEl = document.createElement('div');
      qtyEl.className = 'text-xs text-gray-500';
      qtyEl.textContent = t[currentLang].qty + ': ' + qty + ' x $' + price.toFixed(2);
      left.appendChild(qtyEl);
    }

    var priceEl = document.createElement('div');
    priceEl.className = 'text-emerald-400 font-semibold ml-4';
    priceEl.textContent = '$' + lineTotal.toFixed(2);

    row.appendChild(left);
    row.appendChild(priceEl);
    container.appendChild(row);
  });

  // Use pre-calculated total if available and items match
  if (checkoutState.total > 0) {
    total = checkoutState.total;
  }

  updateTotal(total);
}

function updateTotal(total) {
  checkoutState.total = total;
  document.getElementById('order-total').textContent = '$' + total.toFixed(2);
  document.getElementById('pay-btn-amount').textContent = '$' + total.toFixed(2);
}

// ===== PAYMENT METHOD SELECTION =====
function selectPaymentMethod(provider) {
  checkoutState.provider = provider;

  // Update UI
  document.querySelectorAll('.payment-method').forEach(function(el) {
    el.classList.remove('selected');
  });
  var selected = document.querySelector('.payment-method[data-provider="' + provider + '"]');
  if (selected) selected.classList.add('selected');

  validateForm();
}

// ===== FORM VALIDATION =====
function validateForm() {
  var name = document.getElementById('customer-name').value.trim();
  var email = document.getElementById('customer-email').value.trim();
  var phone = document.getElementById('customer-phone').value.trim();
  var provider = checkoutState.provider;
  var hasItems = checkoutState.items.length > 0 || checkoutState.orderRef;

  var valid = name && email && provider && hasItems;

  document.getElementById('pay-btn').disabled = !valid;
  return valid;
}

// Listen for input changes
document.getElementById('customer-name').addEventListener('input', validateForm);
document.getElementById('customer-email').addEventListener('input', validateForm);
document.getElementById('customer-phone').addEventListener('input', validateForm);

// ===== SUBMIT CHECKOUT =====
function submitCheckout() {
  if (!validateForm()) return;

  var btn = document.getElementById('pay-btn');
  var btnText = document.getElementById('pay-btn-text');
  var origText = btnText.textContent;
  btn.disabled = true;
  btnText.textContent = t[currentLang].submitting;
  document.getElementById('pay-btn-amount').textContent = '';
  hideError();

  var payload = {
    provider: checkoutState.provider,
    items: checkoutState.items,
    customer_name: document.getElementById('customer-name').value.trim(),
    customer_email: document.getElementById('customer-email').value.trim(),
    customer_phone: document.getElementById('customer-phone').value.trim(),
    return_url: APP_URL + '/checkout.php?status=success',
    cancel_url: APP_URL + '/checkout.php?status=cancelled',
    metadata: { source: 'checkout', lang: currentLang }
  };

  if (checkoutState.orderRef) {
    payload.order_ref = checkoutState.orderRef;
  }

  fetch(API_BASE + '/commerce/checkout', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    credentials: 'include',
    body: JSON.stringify(payload)
  })
  .then(function(res) { return res.json(); })
  .then(function(data) {
    if (!data.success) {
      showError(data.error || t[currentLang].errorGeneric);
      btn.disabled = false;
      btnText.textContent = origText;
      document.getElementById('pay-btn-amount').textContent = '$' + checkoutState.total.toFixed(2);
      return;
    }

    // Store order ref
    if (data.order_ref) {
      checkoutState.orderRef = data.order_ref;
    }

    // Handle by provider
    if (checkoutState.provider === 'stripe' && data.checkout_url) {
      window.location.href = data.checkout_url;
    } else if (checkoutState.provider === 'paypal' && data.approval_url) {
      window.location.href = data.approval_url;
    } else if (data.checkout_url) {
      window.location.href = data.checkout_url;
    } else if (data.approval_url) {
      window.location.href = data.approval_url;
    } else {
      showError(t[currentLang].errorGeneric);
      btn.disabled = false;
      btnText.textContent = origText;
      document.getElementById('pay-btn-amount').textContent = '$' + checkoutState.total.toFixed(2);
    }
  })
  .catch(function(err) {
    showError(t[currentLang].errorGeneric);
    btn.disabled = false;
    btnText.textContent = origText;
    document.getElementById('pay-btn-amount').textContent = '$' + checkoutState.total.toFixed(2);
  });
}

// ===== STATE MANAGEMENT =====
function showState(state) {
  var states = ['checkout', 'success', 'cancelled'];
  states.forEach(function(s) {
    var el = document.getElementById('state-' + s);
    if (el) {
      if (s === state) {
        el.classList.remove('hidden');
      } else {
        el.classList.add('hidden');
      }
    }
  });
}

function resetToCheckout() {
  // Clear status from URL
  var url = new URL(window.location.href);
  url.searchParams.delete('status');
  window.history.replaceState({}, '', url.toString());

  // Reset form state
  checkoutState.provider = null;
  document.querySelectorAll('.payment-method').forEach(function(el) {
    el.classList.remove('selected');
  });

  hideError();
  renderOrderSummary();
  validateForm();
  showState('checkout');
}

// ===== ERROR DISPLAY =====
function showError(msg) {
  var el = document.getElementById('checkout-error');
  el.textContent = msg;
  el.classList.remove('hidden');
  el.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function hideError() {
  document.getElementById('checkout-error').classList.add('hidden');
}

// ===== LANGUAGE TOGGLE =====
function toggleLanguage() {
  currentLang = currentLang === 'en' ? 'es' : 'en';
  localStorage.setItem('oregontires_lang', currentLang);
  document.documentElement.lang = currentLang;
  if (typeof gtag === 'function') gtag('event', 'language_switch', { event_category: 'engagement', event_label: currentLang });
  applyLanguage();
}

function applyLanguage() {
  document.getElementById('lang-toggle').textContent = currentLang === 'en' ? '🌐 ES' : '🌐 EN';

  document.querySelectorAll('[data-t]').forEach(function(el) {
    var key = el.getAttribute('data-t');
    if (t[currentLang][key]) el.textContent = t[currentLang][key];
  });

  // Update placeholders
  var nameInput = document.getElementById('customer-name');
  if (nameInput) nameInput.placeholder = t[currentLang].namePlaceholder;
  var emailInput = document.getElementById('customer-email');
  if (emailInput) emailInput.placeholder = t[currentLang].emailPlaceholder;
  var phoneInput = document.getElementById('customer-phone');
  if (phoneInput) phoneInput.placeholder = t[currentLang].phonePlaceholder;

  // Re-render items (names might change in future i18n)
  renderOrderSummary();
}

// Apply saved language on load
if (currentLang !== 'en') {
  document.documentElement.lang = currentLang;
  applyLanguage();
}
</script>
</body>
</html>
