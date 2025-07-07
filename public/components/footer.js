
// Footer component
function createFooter() {
    return `
        <footer class="bg-primary text-white py-12">
            <div class="container mx-auto px-4">
                <div class="grid md:grid-cols-4 gap-8">
                    <div>
                        <h3 class="text-xl font-bold mb-4" id="footer-contact-info">Contact Information</h3>
                        <div class="space-y-2">
                            <div class="flex items-center gap-2">
                                <i data-lucide="phone" class="h-4 w-4"></i>
                                <span>(503) 367-9714</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <i data-lucide="map-pin" class="h-4 w-4"></i>
                                <span>8536 SE 82nd Ave, Portland, OR 97266</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <i data-lucide="clock" class="h-4 w-4"></i>
                                <span id="footer-hours">Mon-Sat 7AM-7PM</span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-xl font-bold mb-4" id="footer-services">Services</h3>
                        <ul class="space-y-1 text-gray-200">
                            <li id="footer-tire-installation">Tire Installation</li>
                            <li id="footer-tire-repair">Tire Repair</li>
                            <li id="footer-wheel-alignment">Wheel Alignment</li>
                            <li id="footer-brake-service">Brake Service</li>
                            <li id="footer-oil-change">Oil Change</li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-xl font-bold mb-4">Follow Us</h3>
                        <div class="space-y-3">
                            <a href="https://www.facebook.com/people/Oregon-Tires/61571913202998/?_rdr" target="_blank" rel="noopener noreferrer" class="text-white hover:text-yellow-200 flex items-center gap-2">
                                <i data-lucide="facebook" class="h-4 w-4"></i>
                                Facebook
                            </a>
                            <a href="https://www.instagram.com/oregontires" target="_blank" rel="noopener noreferrer" class="text-white hover:text-yellow-200 flex items-center gap-2">
                                <i data-lucide="instagram" class="h-4 w-4"></i>
                                Instagram
                            </a>
                        </div>
                        <div class="mt-4">
                            <h4 class="text-lg font-bold mb-2">Language / Idioma</h4>
                            <button onclick="toggleLanguage()" class="text-white hover:text-yellow-200 text-left">
                                English | Español
                            </button>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-xl font-bold mb-4">Other Versions</h3>
                        <div class="space-y-2">
                            <a href="/oregon-tires" class="text-white hover:text-yellow-200 flex items-center gap-2 block">
                                <i data-lucide="external-link" class="h-4 w-4"></i>
                                React Version
                            </a>
                            <a href="/admin" class="text-white hover:text-yellow-200 flex items-center gap-2 block">
                                <i data-lucide="external-link" class="h-4 w-4"></i>
                                Admin Dashboard
                            </a>
                        </div>
                    </div>
                </div>

                <div class="border-t border-green-600 mt-8 pt-8 text-center text-gray-200">
                    <p>&copy; 2025 Oregon Tires Auto Care. <span id="all-rights">All rights reserved</span></p>
                </div>
            </div>
        </footer>
    `;
}

// Initialize footer when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    const footerContainer = document.getElementById('footer-container');
    if (footerContainer) {
        footerContainer.innerHTML = createFooter();
    }
});
