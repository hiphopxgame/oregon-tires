
// Testimonials component
function createTestimonials() {
    return `
        <section id="testimonials" class="py-16 bg-gray-50">
            <div class="container mx-auto px-4">
                <div class="text-center mb-12">
                    <h2 id="testimonials-title" class="text-4xl font-bold primary-color mb-4">Customer Reviews</h2>
                    <p id="testimonials-subtitle" class="text-xl text-gray-600">What our customers say about our service</p>
                </div>

                <div id="reviews-container" class="grid md:grid-cols-3 gap-6 mb-12">
                    <!-- Reviews will be populated by JavaScript -->
                </div>

                <div class="text-center">
                    <div class="inline-block bg-white p-8 rounded-lg shadow-lg">
                        <div class="text-5xl font-bold primary-color mb-2">4.8</div>
                        <div class="text-gray-600 mb-3">out of 5 stars</div>
                        <div class="flex justify-center mb-3">
                            <i data-lucide="star" class="h-6 w-6 fill-current text-yellow-400"></i>
                            <i data-lucide="star" class="h-6 w-6 fill-current text-yellow-400"></i>
                            <i data-lucide="star" class="h-6 w-6 fill-current text-yellow-400"></i>
                            <i data-lucide="star" class="h-6 w-6 fill-current text-yellow-400"></i>
                            <i data-lucide="star" class="h-6 w-6 fill-current text-yellow-400"></i>
                        </div>
                        <div class="text-gray-600 mb-6">Based on 150+ Google Reviews</div>
                        <button onclick="window.open('https://www.google.com/search?q=Oregon+Tires+Reviews', '_blank')" class="border-2 border-primary text-primary px-6 py-3 rounded-lg font-semibold hover:bg-green-50 transition-colors">
                            View All Reviews on Google
                        </button>
                    </div>
                </div>
            </div>
        </section>
    `;
}

// Initialize testimonials when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    const testimonialsContainer = document.getElementById('testimonials-container');
    if (testimonialsContainer) {
        testimonialsContainer.innerHTML = createTestimonials();
    }
});
