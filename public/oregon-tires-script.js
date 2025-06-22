
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
    console.log('DOM loaded, initializing Oregon Tires site...');
    
    // Initialize Lucide icons
    if (window.lucide) {
        lucide.createIcons();
        console.log('Lucide icons initialized');
    }
    
    // Initialize reviews
    if (window.populateReviews) {
        window.populateReviews();
        console.log('Reviews populated');
    } else {
        console.warn('populateReviews function not found');
    }
    
    // Initialize translations
    if (window.updateTranslations) {
        window.updateTranslations();
        console.log('Translations updated');
    } else {
        console.warn('updateTranslations function not found');
    }
    
    // Add appointment preview section
    if (window.addAppointmentPreview) {
        window.addAppointmentPreview();
        console.log('Appointment preview section added');
    } else {
        console.warn('addAppointmentPreview function not found');
    }

    // Check if all required scripts are loaded
    const requiredFunctions = [
        'checkAppointmentConflicts',
        'updateTimeSlotAvailability', 
        'toggleScheduleMode',
        'handleFormSubmit'
    ];
    
    requiredFunctions.forEach(func => {
        if (window[func]) {
            console.log(`✓ ${func} loaded`);
        } else {
            console.error(`✗ ${func} not loaded`);
        }
    });
});

// Export utility functions
window.scrollToSection = scrollToSection;
