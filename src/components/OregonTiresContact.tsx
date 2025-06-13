
import React from 'react';
import { Phone, MapPin, Clock, MessageCircle, Calendar } from 'lucide-react';
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Checkbox } from "@/components/ui/checkbox";

interface ContactProps {
  language: string;
  translations: any;
  primaryColor: string;
  isScheduleMode: boolean;
  setIsScheduleMode: (value: boolean) => void;
  contactForm: any;
  setContactForm: (form: any) => void;
  handleContactSubmit: (e: React.FormEvent) => void;
  toggleLanguage: () => void;
}

const OregonTiresContact: React.FC<ContactProps> = ({
  language,
  translations,
  primaryColor,
  isScheduleMode,
  setIsScheduleMode,
  contactForm,
  setContactForm,
  handleContactSubmit,
  toggleLanguage
}) => {
  const t = translations;

  return (
    <section id="contact" className="py-16">
      <div className="container mx-auto px-4">
        <div className="text-center mb-12">
          <h2 className="text-4xl font-bold mb-4" style={{ color: primaryColor }}>
            {isScheduleMode ? t.scheduleService : t.contact}
          </h2>
          <p className="text-xl text-gray-600">{t.contactSubtitle}</p>
        </div>

        <div className="grid lg:grid-cols-2 gap-12">
          {/* Contact Information */}
          <div>
            <h3 className="text-2xl font-bold mb-6" style={{ color: primaryColor }}>{t.contactInfo}</h3>
            
            <div className="space-y-6">
              <div className="flex items-center gap-4">
                <div className="bg-green-100 p-3 rounded-full">
                  <Phone className="h-6 w-6" style={{ color: primaryColor }} />
                </div>
                <div>
                  <h4 className="font-semibold text-gray-800">{t.phone}</h4>
                  <p className="text-gray-600">(503) 367-9714</p>
                </div>
              </div>

              <div className="flex items-center gap-4">
                <div className="bg-green-100 p-3 rounded-full">
                  <MapPin className="h-6 w-6" style={{ color: primaryColor }} />
                </div>
                <div>
                  <h4 className="font-semibold text-gray-800">Address</h4>
                  <p className="text-gray-600">8536 SE 82nd Ave, Portland, OR 97266</p>
                </div>
              </div>

              <div className="flex items-center gap-4">
                <div className="bg-green-100 p-3 rounded-full">
                  <Clock className="h-6 w-6" style={{ color: primaryColor }} />
                </div>
                <div>
                  <h4 className="font-semibold text-gray-800">{t.businessHours}</h4>
                  <p className="text-gray-600">{t.monSat}</p>
                  <p className="text-gray-600">{t.sunday}</p>
                </div>
              </div>

              <div className="flex items-center gap-4">
                <div className="bg-green-100 p-3 rounded-full">
                  <MessageCircle className="h-6 w-6" style={{ color: primaryColor }} />
                </div>
                <div>
                  <h4 className="font-semibold text-gray-800">Language / Idioma</h4>
                  <button onClick={toggleLanguage} className="text-gray-600 hover:text-green-700">
                    {language === 'english' ? '🇺🇸 English' : '🇲🇽 Español'}
                  </button>
                </div>
              </div>
            </div>

            <div className="mt-8 p-6 bg-green-50 rounded-lg">
              <h4 className="text-xl font-bold mb-4" style={{ color: primaryColor }}>High Quality Tires</h4>
              <img 
                src="https://images.unsplash.com/photo-1558618666-fcd25c85cd64?auto=format&fit=crop&w=600&q=80" 
                alt="High Quality Tires" 
                className="w-full h-64 object-cover rounded-lg shadow-lg"
              />
              <p className="text-gray-600 mt-4">Premium tire installation and service for all vehicle types</p>
            </div>
          </div>

          {/* Contact Form */}
          <div>
            <Card>
              <CardHeader>
                <div className="flex items-center justify-between">
                  <CardTitle style={{ color: primaryColor }}>
                    {isScheduleMode ? t.scheduleService : t.contact}
                  </CardTitle>
                  <div className="flex items-center space-x-2">
                    <Checkbox 
                      id="schedule-mode" 
                      checked={isScheduleMode}
                      onCheckedChange={(checked) => setIsScheduleMode(checked as boolean)}
                    />
                    <label htmlFor="schedule-mode" className="text-sm font-medium">
                      {isScheduleMode ? t.toggleToContact : t.toggleToSchedule}
                    </label>
                  </div>
                </div>
              </CardHeader>
              <CardContent>
                <form onSubmit={handleContactSubmit} className="space-y-4">
                  <div className="grid grid-cols-2 gap-4">
                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-1">
                        {t.firstName} *
                      </label>
                      <input
                        type="text"
                        required
                        placeholder={t.firstName}
                        className="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500"
                        value={contactForm.firstName}
                        onChange={(e) => setContactForm({...contactForm, firstName: e.target.value})}
                      />
                    </div>
                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-1">
                        {t.lastName} *
                      </label>
                      <input
                        type="text"
                        required
                        placeholder={t.lastName}
                        className="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500"
                        value={contactForm.lastName}
                        onChange={(e) => setContactForm({...contactForm, lastName: e.target.value})}
                      />
                    </div>
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">
                      {t.phone} *
                    </label>
                    <input
                      type="tel"
                      required
                      placeholder="(503) 123-4567"
                      className="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500"
                      value={contactForm.phone}
                      onChange={(e) => setContactForm({...contactForm, phone: e.target.value})}
                    />
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">
                      {t.email} *
                    </label>
                    <input
                      type="email"
                      required
                      placeholder="your@email.com"
                      className="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500"
                      value={contactForm.email}
                      onChange={(e) => setContactForm({...contactForm, email: e.target.value})}
                    />
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">
                      {t.message} *
                    </label>
                    <textarea
                      required
                      rows={4}
                      placeholder={t.message + "..."}
                      className="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500"
                      value={contactForm.message}
                      onChange={(e) => setContactForm({...contactForm, message: e.target.value})}
                    />
                  </div>

                  {isScheduleMode && (
                    <>
                      <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">
                          {t.serviceNeeded} *
                        </label>
                        <select
                          required
                          className="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500"
                          value={contactForm.service}
                          onChange={(e) => setContactForm({...contactForm, service: e.target.value})}
                        >
                          <option value="">{t.selectService}</option>
                          <option value="Tire Installation">Tire Installation</option>
                          <option value="Tire Repair">Tire Repair</option>
                          <option value="Wheel Alignment">Wheel Alignment</option>
                          <option value="Brake Service">Brake Service</option>
                          <option value="Oil Change">Oil Change</option>
                          <option value="General Maintenance">General Maintenance</option>
                          <option value="Other">Other</option>
                        </select>
                      </div>

                      <div className="grid grid-cols-2 gap-4">
                        <div>
                          <label className="block text-sm font-medium text-gray-700 mb-1">
                            {t.preferredDate} *
                          </label>
                          <input
                            type="date"
                            required
                            className="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500"
                            value={contactForm.preferred_date}
                            onChange={(e) => setContactForm({...contactForm, preferred_date: e.target.value})}
                          />
                        </div>
                        <div>
                          <label className="block text-sm font-medium text-gray-700 mb-1">
                            {t.preferredTime} *
                          </label>
                          <select
                            required
                            className="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500"
                            value={contactForm.preferred_time}
                            onChange={(e) => setContactForm({...contactForm, preferred_time: e.target.value})}
                          >
                            <option value="">{t.selectTime}</option>
                            <option value="07:00">7:00 AM</option>
                            <option value="08:00">8:00 AM</option>
                            <option value="09:00">9:00 AM</option>
                            <option value="10:00">10:00 AM</option>
                            <option value="11:00">11:00 AM</option>
                            <option value="12:00">12:00 PM</option>
                            <option value="13:00">1:00 PM</option>
                            <option value="14:00">2:00 PM</option>
                            <option value="15:00">3:00 PM</option>
                            <option value="16:00">4:00 PM</option>
                            <option value="17:00">5:00 PM</option>
                            <option value="18:00">6:00 PM</option>
                            <option value="19:00">7:00 PM</option>
                          </select>
                        </div>
                      </div>
                    </>
                  )}

                  <Button 
                    type="submit" 
                    className="w-full text-white"
                    style={{ backgroundColor: primaryColor }}
                  >
                    {isScheduleMode ? (
                      <>
                        <Calendar className="h-4 w-4 mr-2" />
                        {t.scheduleAppointment}
                      </>
                    ) : (
                      t.sendMessage
                    )}
                  </Button>
                </form>
              </CardContent>
            </Card>
          </div>
        </div>

        {/* Google Map - Always Visible */}
        <div className="mt-12">
          <h3 className="text-2xl font-bold mb-6 text-center" style={{ color: primaryColor }}>{t.visitLocation}</h3>
          <div className="bg-gray-200 h-96 rounded-lg flex items-center justify-center">
            <iframe
              src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2796.8567891234567!2d-122.57895!3d45.46123!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x5495a0b91234567%3A0x1234567890abcdef!2s8536%20SE%2082nd%20Ave%2C%20Portland%2C%20OR%2097266!5e0!3m2!1sen!2sus!4v1234567890123"
              width="100%"
              height="100%"
              style={{ border: 0 }}
              allowFullScreen
              loading="lazy"
              referrerPolicy="no-referrer-when-downgrade"
              className="rounded-lg"
            ></iframe>
          </div>
        </div>
      </div>
    </section>
  );
};

export default OregonTiresContact;
