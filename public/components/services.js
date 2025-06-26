
// Services component
function createServices() {
    return `
        <section id="services" class="py-16 bg-gray-50">
            <div class="container mx-auto px-4">
                <div class="text-center mb-12">
                    <h2 id="services-title" class="text-4xl font-bold primary-color mb-4">Services</h2>
                    <p id="services-subtitle" class="text-xl text-gray-600">Comprehensive automotive services you can trust</p>
                </div>
                
                <div class="grid md:grid-cols-3 gap-8">
                    <div class="bg-white p-6 rounded-lg shadow-lg hover:shadow-xl transition-shadow">
                        <div class="w-16 h-16 bg-primary rounded-full flex items-center justify-center mb-4 mx-auto">
                            <i data-lucide="circle" class="h-8 w-8 text-white"></i>
                        </div>
                        <h3 id="tire-services" class="text-xl font-semibold mb-3 text-center">Tire Services</h3>
                        <ul class="text-gray-600 space-y-2">
                            <li id="footer-tire-installation">Tire Installation</li>
                            <li id="footer-tire-repair">Tire Repair</li>
                            <li>Tire Rotation & Balancing</li>
                        </ul>
                    </div>
                    
                    <div class="bg-white p-6 rounded-lg shadow-lg hover:shadow-xl transition-shadow">
                        <div class="w-16 h-16 bg-primary rounded-full flex items-center justify-center mb-4 mx-auto">
                            <i data-lucide="wrench" class="h-8 w-8 text-white"></i>
                        </div>
                        <h3 id="auto-maintenance" class="text-xl font-semibold mb-3 text-center">Auto Maintenance</h3>
                        <ul class="text-gray-600 space-y-2">
                            <li id="footer-wheel-alignment">Wheel Alignment</li>
                            <li id="footer-brake-service">Brake Service</li>
                            <li id="footer-oil-change">Oil Change</li>
                        </ul>
                    </div>
                    
                    <div class="bg-white p-6 rounded-lg shadow-lg hover:shadow-xl transition-shadow">
                        <div class="w-16 h-16 bg-primary rounded-full flex items-center justify-center mb-4 mx-auto">
                            <i data-lucide="phone" class="h-8 w-8 text-white"></i>
                        </div>
                        <h3 id="emergency-service" class="text-xl font-semibold mb-3 text-center">Emergency Services</h3>
                        <ul class="text-gray-600 space-y-2">
                            <li>24/7 Emergency Repair</li>
                            <li>Roadside Assistance</li>
                            <li>Mobile Tire Service</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>
    `;
}

// Initialize services when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    const servicesContainer = document.getElementById('services-container');
    if (servicesContainer) {
        servicesContainer.innerHTML = createServices();
    }
});
