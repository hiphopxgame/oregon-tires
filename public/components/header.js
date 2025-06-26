
// Header component
function createHeader() {
    return `
        <header class="hero-bg text-white shadow-lg">
            <div class="container mx-auto px-4 py-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 id="site-title" class="text-2xl font-bold">Oregon Tires Auto Care</h1>
                        <p id="site-subtitle" class="text-white/80">Professional Tire & Auto Services</p>
                    </div>
                    <button onclick="toggleLanguage()" class="text-white hover:text-yellow-200">
                        English | Español
                    </button>
                </div>
            </div>
        </header>
    `;
}

// Initialize header when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    const headerContainer = document.getElementById('header-container');
    if (headerContainer) {
        headerContainer.innerHTML = createHeader();
    }
});
