
// Hero component
function createHero() {
    return `
        <section class="hero-bg text-white py-20">
            <div class="container mx-auto px-4 text-center">
                <h1 id="hero-title" class="text-5xl font-bold mb-6">Your Trusted Auto Care Experts in Portland</h1>
                <p id="hero-subtitle" class="text-xl mb-8 max-w-3xl mx-auto">Professional tire installation, repair, and complete automotive services. We speak Spanish and English!</p>
                <div class="flex justify-center gap-4 flex-wrap">
                    <button onclick="openContactForm()" id="contact-btn" class="secondary-color text-black px-8 py-3 rounded-lg font-semibold hover:bg-yellow-400 transition-colors">Contact Us</button>
                    <button onclick="window.location.href='/book-appointment'" id="appointment-btn" class="bg-white text-primary px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors">Book Appointment</button>
                </div>
            </div>
        </section>
    `;
}

// Initialize hero when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    const heroContainer = document.getElementById('hero-container');
    if (heroContainer) {
        heroContainer.innerHTML = createHero();
    }
});
