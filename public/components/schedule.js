
// Schedule component
function createSchedule() {
    return `
        <section id="schedule" class="py-16 bg-white">
            <div class="container mx-auto px-4">
                <div class="text-center mb-12">
                    <h2 class="text-4xl font-bold primary-color mb-4">Schedule Your Service</h2>
                    <p class="text-xl text-gray-600">Book your appointment online - it's quick and easy!</p>
                </div>
                
                <div class="max-w-4xl mx-auto">
                    <div class="bg-gray-50 rounded-lg p-8">
                        <iframe 
                            src="/book-appointment" 
                            class="w-full h-[800px] border-0 rounded-lg"
                            title="Book Appointment"
                            loading="lazy">
                        </iframe>
                        
                        <!-- Fallback for browsers that don't support iframes -->
                        <noscript>
                            <div class="text-center py-8">
                                <p class="text-gray-600 mb-4">Please enable JavaScript or visit our booking page directly:</p>
                                <a href="/book-appointment" class="bg-primary text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-700 transition-colors inline-block">
                                    Go to Booking Page
                                </a>
                            </div>
                        </noscript>
                    </div>
                </div>
            </div>
        </section>
    `;
}

// Initialize schedule when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    const scheduleContainer = document.getElementById('schedule-container');
    if (scheduleContainer) {
        scheduleContainer.innerHTML = createSchedule();
    }
});
