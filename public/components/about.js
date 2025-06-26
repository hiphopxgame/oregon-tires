
// About component
function createAbout() {
    return `
        <section id="about" class="py-16 bg-white">
            <div class="container mx-auto px-4">
                <div class="max-w-4xl mx-auto text-center">
                    <h2 class="text-4xl font-bold primary-color mb-6">About Oregon Tires Auto Care</h2>
                    <p id="about-subtitle" class="text-xl text-gray-600 mb-8">Serving Portland with honest, reliable automotive services since 2008</p>
                    <div class="grid md:grid-cols-2 gap-8 text-left">
                        <div>
                            <h3 class="text-2xl font-semibold primary-color mb-4">Our Mission</h3>
                            <p class="text-gray-600 mb-4">To provide exceptional automotive services with honest pricing and reliable results. We treat every customer like family and every vehicle with the care it deserves.</p>
                        </div>
                        <div>
                            <h3 class="text-2xl font-semibold primary-color mb-4">Why Choose Us</h3>
                            <ul class="text-gray-600 space-y-2">
                                <li>• Bilingual staff (English & Spanish)</li>
                                <li>• Honest, transparent pricing</li>
                                <li>• Quality workmanship guaranteed</li>
                                <li>• Fast, reliable service</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    `;
}

// Initialize about when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    const aboutContainer = document.getElementById('about-container');
    if (aboutContainer) {
        aboutContainer.innerHTML = createAbout();
    }
});
