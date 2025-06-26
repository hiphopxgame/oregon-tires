
import React from 'react';
import { MessageCircle } from 'lucide-react';
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";

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
  contactForm,
  setContactForm,
  handleContactSubmit
}) => {
  const t = translations;

  return (
    <Card>
      <CardHeader>
        <CardTitle style={{ color: primaryColor }}>
          {t.contact}
        </CardTitle>
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

          <Button 
            type="submit" 
            className="w-full text-white"
            style={{ backgroundColor: primaryColor }}
          >
            <MessageCircle className="h-4 w-4 mr-2" />
            {t.sendMessage}
          </Button>
        </form>
      </CardContent>
    </Card>
  );
};

export default ContactForm;
