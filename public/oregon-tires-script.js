
// Main script file - coordinates all modules

// Global utility functions
function scrollToSection(id) {
    const element = document.getElementById(id);
    if (element) {
        const headerHeight = 120;
        const elementPosition = element.offsetTop - headerHeight;
        window.scrollTo({ 
            top: elementPosition, 
            behavior: 'smooth' 
        });
    }
}

// Initialize page when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Lucide icons
    if (window.lucide) {
        lucide.createIcons();
    }
    
    // Initialize reviews
    if (window.populateReviews) {
        window.populateReviews();
    }
    
    // Initialize translations
    if (window.updateTranslations) {
        window.updateTranslations();
    }
    
    // Add appointment preview section
    if (window.addAppointmentPreview) {
        window.addAppointmentPreview();
    }
});

// Export utility functions
window.scrollToSection = scrollToSection;
