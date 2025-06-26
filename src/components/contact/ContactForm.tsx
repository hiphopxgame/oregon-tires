
import React from 'react';
import { MessageCircle, Calendar } from 'lucide-react';
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Checkbox } from "@/components/ui/checkbox";

interface ContactFormProps {
  translations: any;
  primaryColor: string;
  isScheduleMode: boolean;
  setIsScheduleMode: (value: boolean) => void;
  contactForm: any;
  setContactForm: (form: any) => void;
  handleContactSubmit: (e: React.FormEvent) => void;
}

const ContactForm: React.FC<ContactFormProps> = ({
  translations,
  primaryColor,
  isScheduleMode,
  setIsScheduleMode,
  contactForm,
  setContactForm,
  handleContactSubmit
}) => {
  const t = translations;

  return (
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
  );
};

export default ContactForm;
