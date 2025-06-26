
// Navigation component
function createNavigation() {
    return `
        <nav class="bg-white shadow-md sticky top-0 z-50">
            <div class="container mx-auto px-4">
                <div class="flex justify-center space-x-8 py-4">
                    <a href="#services" onclick="scrollToSection('services')" id="nav-services" class="text-primary hover:text-green-700 font-medium">Services</a>
                    <a href="#about" onclick="scrollToSection('about')" id="nav-about" class="text-primary hover:text-green-700 font-medium">About</a>
                    <a href="#testimonials" onclick="scrollToSection('testimonials')" id="nav-testimonials" class="text-primary hover:text-green-700 font-medium">Reviews</a>
                    <a href="#contact" onclick="scrollToSection('contact')" id="nav-contact" class="text-primary hover:text-green-700 font-medium">Contact</a>
                    <a href="/book-appointment" id="nav-schedule" class="text-primary hover:text-green-700 font-medium">Book Appointment</a>
                </div>
            </div>
        </nav>
    `;
}

// Initialize navigation when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    const navContainer = document.getElementById('navigation-container');
    if (navContainer) {
        navContainer.innerHTML = createNavigation();
    }
});
