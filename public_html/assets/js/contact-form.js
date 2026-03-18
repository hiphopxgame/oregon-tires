// Oregon Tires — Real-time Contact Form Validation
// Extracted from index.html inline script

(function() {
  function showFieldError(input, msg) {
    var err = input.parentElement.querySelector('.field-error');
    if (!err) {
      err = document.createElement('p');
      err.className = 'field-error text-red-500 dark:text-red-400 text-xs mt-1';
      input.parentElement.appendChild(err);
    }
    err.textContent = msg;
    input.classList.add('border-red-400');
    input.classList.remove('border-gray-300');
  }
  function clearFieldError(input) {
    var err = input.parentElement.querySelector('.field-error');
    if (err) err.remove();
    input.classList.remove('border-red-400');
    input.classList.add('border-gray-300');
  }
  var emailInput = document.getElementById('contact-email');
  if (emailInput) emailInput.addEventListener('blur', function() {
    var val = this.value.trim();
    if (val && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
      showFieldError(this, t[currentLang].invalidEmail);
    } else { clearFieldError(this); }
  });
  var phoneInput = document.getElementById('contact-phone');
  if (phoneInput) phoneInput.addEventListener('blur', function() {
    var val = this.value.replace(/\D/g, '');
    if (this.value.trim() && (val.length < 7 || val.length > 15)) {
      showFieldError(this, t[currentLang].invalidPhone);
    } else { clearFieldError(this); }
  });
  var nameFields = [document.getElementById('contact-first-name'), document.getElementById('contact-last-name')];
  nameFields.forEach(function(f) {
    if (f) f.addEventListener('blur', function() {
      if (this.value.trim().length > 100) showFieldError(this, t[currentLang].nameTooLong);
      else clearFieldError(this);
    });
  });
  var msgField = document.getElementById('contact-message');
  if (msgField) msgField.addEventListener('blur', function() {
    var val = this.value.trim();
    if (val && val.length < 10) showFieldError(this, t[currentLang].messageTooShort);
    else if (val.length > 2000) showFieldError(this, t[currentLang].messageTooLong);
    else clearFieldError(this);
  });
})();
