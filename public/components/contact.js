
// Contact component
function createContact() {
    return `
        <section id="contact" class="py-16 bg-white">
            <div class="container mx-auto px-4">
                <div class="max-w-6xl mx-auto">
                    <div class="text-center mb-12">
                        <h2 id="contact-title" class="text-4xl font-bold primary-color mb-4">Contact</h2>
                        <p id="contact-subtitle" class="text-xl text-gray-600">Get in touch with us for questions or general inquiries</p>
                        <p class="text-lg text-gray-600 mt-2">
                            For appointment scheduling, please use our 
                            <a href="/book-appointment" class="text-primary hover:underline font-semibold">dedicated booking form</a>.
                        </p>
                    </div>

                    <div class="grid lg:grid-cols-2 gap-12">
                        <!-- Contact Information -->
                        <div>
                            <h3 id="contact-info" class="text-2xl font-semibold primary-color mb-6">Contact Information</h3>
                            <div class="space-y-4">
                                <div class="flex items-center gap-3">
                                    <i data-lucide="phone" class="h-5 w-5 text-primary"></i>
                                    <div>
                                        <p class="font-medium" id="phone-label">Phone</p>
                                        <p class="text-gray-600">(503) 367-9714</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <i data-lucide="map-pin" class="h-5 w-5 text-primary"></i>
                                    <div>
                                        <p class="font-medium">Address</p>
                                        <p class="text-gray-600">8536 SE 82nd Ave, Portland, OR 97266</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <i data-lucide="clock" class="h-5 w-5 text-primary"></i>
                                    <div>
                                        <p class="font-medium" id="business-hours">Business Hours</p>
                                        <p class="text-gray-600" id="hours-mon-sat">Mon-Sat 7AM-7PM</p>
                                        <p class="text-gray-600" id="hours-sunday">Sunday: Closed</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Social Media Links -->
                            <div class="mt-6">
                                <h4 class="text-lg font-semibold primary-color mb-3">Follow Us</h4>
                                <div class="flex gap-4">
                                    <a href="https://www.facebook.com/people/Oregon-Tires/61571913202998/?_rdr" target="_blank" rel="noopener noreferrer" class="bg-primary text-white p-3 rounded-full hover:bg-green-700 transition-colors">
                                        <i data-lucide="facebook" class="h-5 w-5"></i>
                                    </a>
                                    <a href="https://www.instagram.com/oregontires" target="_blank" rel="noopener noreferrer" class="bg-primary text-white p-3 rounded-full hover:bg-green-700 transition-colors">
                                        <i data-lucide="instagram" class="h-5 w-5"></i>
                                    </a>
                                </div>
                            </div>

                            <div class="mt-8 space-y-4">
                                <button onclick="window.open('https://maps.google.com/?q=8536+SE+82nd+Ave,+Portland,+OR+97266', '_blank')" id="visit-location" class="bg-primary text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-700 transition-colors block w-full sm:w-auto">
                                    Visit Our Location
                                </button>
                                <button onclick="window.location.href='/book-appointment'" class="bg-yellow-400 text-black px-6 py-3 rounded-lg font-semibold hover:bg-yellow-500 transition-colors block w-full sm:w-auto">
                                    Book Your Appointment
                                </button>
                            </div>
                        </div>

                        <!-- Contact Form -->
                        <div class="bg-white p-8 rounded-lg shadow-lg">
                            <div class="mb-6">
                                <h3 id="form-title" class="text-2xl font-semibold primary-color mb-4">Send Us a Message</h3>
                                <p class="text-gray-600">For appointment scheduling, please use our <a href="/book-appointment" class="text-primary hover:underline font-medium">dedicated booking form</a>.</p>
                            </div>

                            <form onsubmit="handleContactFormSubmit(event)">
                                <div class="grid md:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label for="firstName" id="first-name-label" class="block text-gray-700 font-medium mb-2">First Name *</label>
                                        <input type="text" id="firstName" name="firstName" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    </div>
                                    <div>
                                        <label for="lastName" id="last-name-label" class="block text-gray-700 font-medium mb-2">Last Name *</label>
                                        <input type="text" id="lastName" name="lastName" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    </div>
                                </div>

                                <div class="grid md:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label for="phone" id="phone-label-form" class="block text-gray-700 font-medium mb-2">Phone *</label>
                                        <input type="tel" id="phone" name="phone" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    </div>
                                    <div>
                                        <label for="email" id="email-label" class="block text-gray-700 font-medium mb-2">Email *</label>
                                        <input type="email" id="email" name="email" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    </div>
                                </div>

                                <div class="mb-6">
                                    <label for="message" id="message-label" class="block text-gray-700 font-medium mb-2">Message *</label>
                                    <textarea id="message" name="message" rows="4" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
                                </div>

                                <button type="submit" id="submit-text" class="w-full bg-primary text-white py-3 rounded-lg font-semibold hover:bg-green-700 transition-colors">
                                    Send Message
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    `;
}

// Contact form submission handler
async function handleContactFormSubmit(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const data = {
        first_name: formData.get('firstName'),
        last_name: formData.get('lastName'),
        phone: formData.get('phone'),
        email: formData.get('email'),
        message: formData.get('message'),
        language: window.currentLanguage || 'english',
        status: 'new'
    };

    try {
        const { error } = await window.supabaseClient
            .from('oretir_contact_messages')
            .insert(data);

        if (error) throw error;
        
        alert(window.currentLanguage === 'english' ? 
            'Message sent successfully!' : 
            '¡Mensaje enviado exitosamente!'
        );
        
        event.target.reset();
        
    } catch (error) {
        console.error('Contact form submission error:', error);
        alert(window.currentLanguage === 'english' ? 
            'Error sending message. Please try again.' : 
            'Error al enviar mensaje. Por favor intente de nuevo.'
        );
    }
}

// Initialize contact when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    const contactContainer = document.getElementById('contact-container');
    if (contactContainer) {
        contactContainer.innerHTML = createContact();
    }
});
