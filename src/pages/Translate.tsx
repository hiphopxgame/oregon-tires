
import React from 'react';
import { useLanguage } from "@/hooks/useLanguage";
import translations from "@/utils/translations";

const Translate = () => {
  const { t } = useLanguage();

  const TranslationRow = ({ labelKey, englishKey, spanishKey }: { 
    labelKey: string; 
    englishKey: string; 
    spanishKey: string; 
  }) => (
    <div className="grid grid-cols-3 gap-4 py-2 border-b border-gray-200">
      <div className="font-medium text-sm text-gray-600">{labelKey}</div>
      <div className="text-sm">{englishKey}</div>
      <div className="text-sm">{spanishKey}</div>
    </div>
  );

  const SectionHeader = ({ title }: { title: string }) => (
    <div className="col-span-3 bg-gray-100 px-4 py-2 font-bold text-lg mt-6 mb-2">
      {title}
    </div>
  );

  return (
    <div className="min-h-screen bg-white p-8">
      <div className="max-w-7xl mx-auto">
        <h1 className="text-3xl font-bold mb-8 text-center">Translation Comparison</h1>
        
        <div className="bg-white shadow-lg rounded-lg overflow-hidden">
          <div className="grid grid-cols-3 gap-4 bg-gray-800 text-white p-4 font-bold">
            <div>Key</div>
            <div>English</div>
            <div>Spanish</div>
          </div>

          <div className="p-4">
            <SectionHeader title="Header & Navigation" />
            <TranslationRow 
              labelKey="title" 
              englishKey={translations.english.title} 
              spanishKey={translations.spanish.title} 
            />
            <TranslationRow 
              labelKey="subtitle" 
              englishKey={translations.english.subtitle} 
              spanishKey={translations.spanish.subtitle} 
            />
            <TranslationRow 
              labelKey="services" 
              englishKey={translations.english.services} 
              spanishKey={translations.spanish.services} 
            />
            <TranslationRow 
              labelKey="about" 
              englishKey={translations.english.about} 
              spanishKey={translations.spanish.about} 
            />
            <TranslationRow 
              labelKey="contact" 
              englishKey={translations.english.contact} 
              spanishKey={translations.spanish.contact} 
            />

            <SectionHeader title="Hero Section" />
            <TranslationRow 
              labelKey="heroTitle" 
              englishKey={translations.english.heroTitle} 
              spanishKey={translations.spanish.heroTitle} 
            />
            <TranslationRow 
              labelKey="heroSubtitle" 
              englishKey={translations.english.heroSubtitle} 
              spanishKey={translations.spanish.heroSubtitle} 
            />
            <TranslationRow 
              labelKey="contactButton" 
              englishKey={translations.english.contactButton} 
              spanishKey={translations.spanish.contactButton} 
            />
            <TranslationRow 
              labelKey="appointmentButton" 
              englishKey={translations.english.appointmentButton} 
              spanishKey={translations.spanish.appointmentButton} 
            />

            <SectionHeader title="Services Section" />
            <TranslationRow 
              labelKey="servicesSubtitle" 
              englishKey={translations.english.servicesSubtitle} 
              spanishKey={translations.spanish.servicesSubtitle} 
            />
            <TranslationRow 
              labelKey="tireServices" 
              englishKey={translations.english.tireServices} 
              spanishKey={translations.spanish.tireServices} 
            />
            <TranslationRow 
              labelKey="autoMaintenance" 
              englishKey={translations.english.autoMaintenance} 
              spanishKey={translations.spanish.autoMaintenance} 
            />
            <TranslationRow 
              labelKey="specializedServices" 
              englishKey={translations.english.specializedServices} 
              spanishKey={translations.spanish.specializedServices} 
            />

            <SectionHeader title="Tire Services" />
            <TranslationRow 
              labelKey="newOrUsedTires" 
              englishKey={translations.english.newOrUsedTires} 
              spanishKey={translations.spanish.newOrUsedTires} 
            />
            <TranslationRow 
              labelKey="newOrUsedTiresDesc" 
              englishKey={translations.english.newOrUsedTiresDesc} 
              spanishKey={translations.spanish.newOrUsedTiresDesc} 
            />
            <TranslationRow 
              labelKey="mountAndBalance" 
              englishKey={translations.english.mountAndBalance} 
              spanishKey={translations.spanish.mountAndBalance} 
            />
            <TranslationRow 
              labelKey="mountAndBalanceDesc" 
              englishKey={translations.english.mountAndBalanceDesc} 
              spanishKey={translations.spanish.mountAndBalanceDesc} 
            />
            <TranslationRow 
              labelKey="tireRepairService" 
              englishKey={translations.english.tireRepairService} 
              spanishKey={translations.spanish.tireRepairService} 
            />
            <TranslationRow 
              labelKey="tireRepairServiceDesc" 
              englishKey={translations.english.tireRepairServiceDesc} 
              spanishKey={translations.spanish.tireRepairServiceDesc} 
            />

            <SectionHeader title="Auto Maintenance" />
            <TranslationRow 
              labelKey="oilChangeService" 
              englishKey={translations.english.oilChangeService} 
              spanishKey={translations.spanish.oilChangeService} 
            />
            <TranslationRow 
              labelKey="oilChangeServiceDesc" 
              englishKey={translations.english.oilChangeServiceDesc} 
              spanishKey={translations.spanish.oilChangeServiceDesc} 
            />
            <TranslationRow 
              labelKey="brakeServices" 
              englishKey={translations.english.brakeServices} 
              spanishKey={translations.spanish.brakeServices} 
            />
            <TranslationRow 
              labelKey="brakeServicesDesc" 
              englishKey={translations.english.brakeServicesDesc} 
              spanishKey={translations.spanish.brakeServicesDesc} 
            />
            <TranslationRow 
              labelKey="tuneup" 
              englishKey={translations.english.tuneup} 
              spanishKey={translations.spanish.tuneup} 
            />
            <TranslationRow 
              labelKey="tuneupDesc" 
              englishKey={translations.english.tuneupDesc} 
              spanishKey={translations.spanish.tuneupDesc} 
            />

            <SectionHeader title="Specialized Services" />
            <TranslationRow 
              labelKey="alignment" 
              englishKey={translations.english.alignment} 
              spanishKey={translations.spanish.alignment} 
            />
            <TranslationRow 
              labelKey="alignmentDesc" 
              englishKey={translations.english.alignmentDesc} 
              spanishKey={translations.spanish.alignmentDesc} 
            />
            <TranslationRow 
              labelKey="mechanicalInspection" 
              englishKey={translations.english.mechanicalInspection} 
              spanishKey={translations.spanish.mechanicalInspection} 
            />
            <TranslationRow 
              labelKey="mechanicalInspectionDesc" 
              englishKey={translations.english.mechanicalInspectionDesc} 
              spanishKey={translations.spanish.mechanicalInspectionDesc} 
            />

            <SectionHeader title="General Services" />
            <TranslationRow 
              labelKey="tireInstallation" 
              englishKey={translations.english.tireInstallation} 
              spanishKey={translations.spanish.tireInstallation} 
            />
            <TranslationRow 
              labelKey="tireRepair" 
              englishKey={translations.english.tireRepair} 
              spanishKey={translations.spanish.tireRepair} 
            />
            <TranslationRow 
              labelKey="tireRotation" 
              englishKey={translations.english.tireRotation} 
              spanishKey={translations.spanish.tireRotation} 
            />
            <TranslationRow 
              labelKey="tireBalancing" 
              englishKey={translations.english.tireBalancing} 
              spanishKey={translations.spanish.tireBalancing} 
            />
            <TranslationRow 
              labelKey="oilChange" 
              englishKey={translations.english.oilChange} 
              spanishKey={translations.spanish.oilChange} 
            />
            <TranslationRow 
              labelKey="brakeService" 
              englishKey={translations.english.brakeService} 
              spanishKey={translations.spanish.brakeService} 
            />
            <TranslationRow 
              labelKey="wheelAlignment" 
              englishKey={translations.english.wheelAlignment} 
              spanishKey={translations.spanish.wheelAlignment} 
            />
            <TranslationRow 
              labelKey="batteryService" 
              englishKey={translations.english.batteryService} 
              spanishKey={translations.spanish.batteryService} 
            />
            <TranslationRow 
              labelKey="engineDiagnostics" 
              englishKey={translations.english.engineDiagnostics} 
              spanishKey={translations.spanish.engineDiagnostics} 
            />
            <TranslationRow 
              labelKey="fluidChecks" 
              englishKey={translations.english.fluidChecks} 
              spanishKey={translations.spanish.fluidChecks} 
            />

            <SectionHeader title="Emergency Services" />
            <TranslationRow 
              labelKey="emergencyService" 
              englishKey={translations.english.emergencyService} 
              spanishKey={translations.spanish.emergencyService} 
            />
            <TranslationRow 
              labelKey="roadSideAssistance" 
              englishKey={translations.english.roadSideAssistance} 
              spanishKey={translations.spanish.roadSideAssistance} 
            />
            <TranslationRow 
              labelKey="flatTireRepair" 
              englishKey={translations.english.flatTireRepair} 
              spanishKey={translations.spanish.flatTireRepair} 
            />
            <TranslationRow 
              labelKey="jumpStart" 
              englishKey={translations.english.jumpStart} 
              spanishKey={translations.spanish.jumpStart} 
            />
            <TranslationRow 
              labelKey="emergencyTowing" 
              englishKey={translations.english.emergencyTowing} 
              spanishKey={translations.spanish.emergencyTowing} 
            />
            <TranslationRow 
              labelKey="lockoutService" 
              englishKey={translations.english.lockoutService} 
              spanishKey={translations.spanish.lockoutService} 
            />
            <TranslationRow 
              labelKey="needServiceToday" 
              englishKey={translations.english.needServiceToday} 
              spanishKey={translations.spanish.needServiceToday} 
            />

            <SectionHeader title="About Section" />
            <TranslationRow 
              labelKey="aboutSubtitle" 
              englishKey={translations.english.aboutSubtitle} 
              spanishKey={translations.spanish.aboutSubtitle} 
            />
            <TranslationRow 
              labelKey="vision" 
              englishKey={translations.english.vision} 
              spanishKey={translations.spanish.vision} 
            />
            <TranslationRow 
              labelKey="visionText" 
              englishKey={translations.english.visionText} 
              spanishKey={translations.spanish.visionText} 
            />
            <TranslationRow 
              labelKey="mission" 
              englishKey={translations.english.mission} 
              spanishKey={translations.spanish.mission} 
            />
            <TranslationRow 
              labelKey="missionText" 
              englishKey={translations.english.missionText} 
              spanishKey={translations.spanish.missionText} 
            />
            <TranslationRow 
              labelKey="goals" 
              englishKey={translations.english.goals} 
              spanishKey={translations.spanish.goals} 
            />
            <TranslationRow 
              labelKey="whyChooseUs" 
              englishKey={translations.english.whyChooseUs} 
              spanishKey={translations.spanish.whyChooseUs} 
            />
            <TranslationRow 
              labelKey="servingPortland" 
              englishKey={translations.english.servingPortland} 
              spanishKey={translations.spanish.servingPortland} 
            />
            <TranslationRow 
              labelKey="servingPortlandText" 
              englishKey={translations.english.servingPortlandText} 
              spanishKey={translations.spanish.servingPortlandText} 
            />

            <SectionHeader title="Testimonials" />
            <TranslationRow 
              labelKey="customerReviews" 
              englishKey={translations.english.customerReviews} 
              spanishKey={translations.spanish.customerReviews} 
            />
            <TranslationRow 
              labelKey="customerReviewsSubtitle" 
              englishKey={translations.english.customerReviewsSubtitle} 
              spanishKey={translations.spanish.customerReviewsSubtitle} 
            />

            <SectionHeader title="Contact & Forms" />
            <TranslationRow 
              labelKey="contactSubtitle" 
              englishKey={translations.english.contactSubtitle} 
              spanishKey={translations.spanish.contactSubtitle} 
            />
            <TranslationRow 
              labelKey="firstName" 
              englishKey={translations.english.firstName} 
              spanishKey={translations.spanish.firstName} 
            />
            <TranslationRow 
              labelKey="lastName" 
              englishKey={translations.english.lastName} 
              spanishKey={translations.spanish.lastName} 
            />
            <TranslationRow 
              labelKey="email" 
              englishKey={translations.english.email} 
              spanishKey={translations.spanish.email} 
            />
            <TranslationRow 
              labelKey="phone" 
              englishKey={translations.english.phone} 
              spanishKey={translations.spanish.phone} 
            />
            <TranslationRow 
              labelKey="message" 
              englishKey={translations.english.message} 
              spanishKey={translations.spanish.message} 
            />
            <TranslationRow 
              labelKey="sendMessage" 
              englishKey={translations.english.sendMessage} 
              spanishKey={translations.spanish.sendMessage} 
            />
            <TranslationRow 
              labelKey="scheduleAppointment" 
              englishKey={translations.english.scheduleAppointment} 
              spanishKey={translations.spanish.scheduleAppointment} 
            />
            <TranslationRow 
              labelKey="serviceNeeded" 
              englishKey={translations.english.serviceNeeded} 
              spanishKey={translations.spanish.serviceNeeded} 
            />
            <TranslationRow 
              labelKey="selectService" 
              englishKey={translations.english.selectService} 
              spanishKey={translations.spanish.selectService} 
            />
            <TranslationRow 
              labelKey="preferredDate" 
              englishKey={translations.english.preferredDate} 
              spanishKey={translations.spanish.preferredDate} 
            />
            <TranslationRow 
              labelKey="preferredTime" 
              englishKey={translations.english.preferredTime} 
              spanishKey={translations.spanish.preferredTime} 
            />
            <TranslationRow 
              labelKey="selectTime" 
              englishKey={translations.english.selectTime} 
              spanishKey={translations.spanish.selectTime} 
            />

            <SectionHeader title="Business Information" />
            <TranslationRow 
              labelKey="contactInfo" 
              englishKey={translations.english.contactInfo} 
              spanishKey={translations.spanish.contactInfo} 
            />
            <TranslationRow 
              labelKey="businessHours" 
              englishKey={translations.english.businessHours} 
              spanishKey={translations.spanish.businessHours} 
            />
            <TranslationRow 
              labelKey="monSat" 
              englishKey={translations.english.monSat} 
              spanishKey={translations.spanish.monSat} 
            />
            <TranslationRow 
              labelKey="sunday" 
              englishKey={translations.english.sunday} 
              spanishKey={translations.spanish.sunday} 
            />
            <TranslationRow 
              labelKey="visitLocation" 
              englishKey={translations.english.visitLocation} 
              spanishKey={translations.spanish.visitLocation} 
            />

            <SectionHeader title="Quality Features" />
            <TranslationRow 
              labelKey="expertService" 
              englishKey={translations.english.expertService} 
              spanishKey={translations.spanish.expertService} 
            />
            <TranslationRow 
              labelKey="expertServiceDesc" 
              englishKey={translations.english.expertServiceDesc} 
              spanishKey={translations.spanish.expertServiceDesc} 
            />
            <TranslationRow 
              labelKey="quickService" 
              englishKey={translations.english.quickService} 
              spanishKey={translations.spanish.quickService} 
            />
            <TranslationRow 
              labelKey="quickServiceDesc" 
              englishKey={translations.english.quickServiceDesc} 
              spanishKey={translations.spanish.quickServiceDesc} 
            />
            <TranslationRow 
              labelKey="qualityParts" 
              englishKey={translations.english.qualityParts} 
              spanishKey={translations.spanish.qualityParts} 
            />
            <TranslationRow 
              labelKey="qualityPartsDesc" 
              englishKey={translations.english.qualityPartsDesc} 
              spanishKey={translations.spanish.qualityPartsDesc} 
            />
            <TranslationRow 
              labelKey="bilingualService" 
              englishKey={translations.english.bilingualService} 
              spanishKey={translations.spanish.bilingualService} 
            />
            <TranslationRow 
              labelKey="bilingualServiceDesc" 
              englishKey={translations.english.bilingualServiceDesc} 
              spanishKey={translations.spanish.bilingualServiceDesc} 
            />

            <SectionHeader title="Miscellaneous" />
            <TranslationRow 
              labelKey="formSuccess" 
              englishKey={translations.english.formSuccess} 
              spanishKey={translations.spanish.formSuccess} 
            />
            <TranslationRow 
              labelKey="formError" 
              englishKey={translations.english.formError} 
              spanishKey={translations.spanish.formError} 
            />
            <TranslationRow 
              labelKey="allRightsReserved" 
              englishKey={translations.english.allRightsReserved} 
              spanishKey={translations.spanish.allRightsReserved} 
            />
            <TranslationRow 
              labelKey="readyToHelp" 
              englishKey={translations.english.readyToHelp} 
              spanishKey={translations.spanish.readyToHelp} 
            />

            <SectionHeader title="Additional Content" />
            <TranslationRow 
              labelKey="aboutOregonTires" 
              englishKey={translations.english.aboutOregonTires} 
              spanishKey={translations.spanish.aboutOregonTires} 
            />
            <TranslationRow 
              labelKey="careCatchphrase" 
              englishKey={translations.english.careCatchphrase} 
              spanishKey={translations.spanish.careCatchphrase} 
            />
            <TranslationRow 
              labelKey="callImmediately" 
              englishKey={translations.english.callImmediately} 
              spanishKey={translations.spanish.callImmediately} 
            />
            <TranslationRow 
              labelKey="availableHours" 
              englishKey={translations.english.availableHours} 
              spanishKey={translations.spanish.availableHours} 
            />
            <TranslationRow 
              labelKey="ourServicePromise" 
              englishKey={translations.english.ourServicePromise} 
              spanishKey={translations.spanish.ourServicePromise} 
            />
            <TranslationRow 
              labelKey="servicePromiseDesc" 
              englishKey={translations.english.servicePromiseDesc} 
              spanishKey={translations.spanish.servicePromiseDesc} 
            />
            <TranslationRow 
              labelKey="qualityGuarantee" 
              englishKey={translations.english.qualityGuarantee} 
              spanishKey={translations.spanish.qualityGuarantee} 
            />
            <TranslationRow 
              labelKey="fairPricing" 
              englishKey={translations.english.fairPricing} 
              spanishKey={translations.spanish.fairPricing} 
            />
            <TranslationRow 
              labelKey="expertServicePromise" 
              englishKey={translations.english.expertServicePromise} 
              spanishKey={translations.spanish.expertServicePromise} 
            />

            <SectionHeader title="Goals Content" />
            <TranslationRow 
              labelKey="goal1" 
              englishKey={translations.english.goal1} 
              spanishKey={translations.spanish.goal1} 
            />
            <TranslationRow 
              labelKey="goal2" 
              englishKey={translations.english.goal2} 
              spanishKey={translations.spanish.goal2} 
            />
            <TranslationRow 
              labelKey="goal3" 
              englishKey={translations.english.goal3} 
              spanishKey={translations.spanish.goal3} 
            />
            <TranslationRow 
              labelKey="goal4" 
              englishKey={translations.english.goal4} 
              spanishKey={translations.spanish.goal4} 
            />
            <TranslationRow 
              labelKey="goal5" 
              englishKey={translations.english.goal5} 
              spanishKey={translations.spanish.goal5} 
            />
            <TranslationRow 
              labelKey="goal6" 
              englishKey={translations.english.goal6} 
              spanishKey={translations.spanish.goal6} 
            />
            <TranslationRow 
              labelKey="goal7" 
              englishKey={translations.english.goal7} 
              spanishKey={translations.spanish.goal7} 
            />

            <SectionHeader title="Why Choose Us Bullet Points" />
            <TranslationRow 
              labelKey="bilingualStaff" 
              englishKey={translations.english.bilingualStaff} 
              spanishKey={translations.spanish.bilingualStaff} 
            />
            <TranslationRow 
              labelKey="honestPricing" 
              englishKey={translations.english.honestPricing} 
              spanishKey={translations.spanish.honestPricing} 
            />
            <TranslationRow 
              labelKey="qualityWorkmanship" 
              englishKey={translations.english.qualityWorkmanship} 
              spanishKey={translations.spanish.qualityWorkmanship} 
            />
            <TranslationRow 
              labelKey="fastReliableService" 
              englishKey={translations.english.fastReliableService} 
              spanishKey={translations.spanish.fastReliableService} 
            />

            <SectionHeader title="Booking Page Elements" />
            <div className="grid gap-2 text-sm">
              {Object.entries(translations.english.booking).map(([key, value]) => (
                <TranslationRow 
                  key={key}
                  labelKey={`booking.${key}`} 
                  englishKey={value} 
                  spanishKey={translations.spanish.booking[key as keyof typeof translations.spanish.booking]} 
                />
              ))}
            </div>
            
            <div className="mt-6 p-4 bg-green-50 border border-green-200 rounded-lg">
              <p className="font-medium text-green-800 mb-2">✅ COMPLETE TRANSLATION STATUS:</p>
              <ul className="text-sm text-green-700 space-y-1">
                <li>• ✅ <strong>Personal Information section:</strong> All form fields translated</li>
                <li>• ✅ <strong>Service Information section:</strong> All service options translated</li>
                <li>• ✅ <strong>Additional Information section:</strong> All text translated</li>
                <li>• ✅ <strong>All placeholder text:</strong> Tire size, vehicle info, address fields</li>
                <li>• ✅ <strong>All validation messages:</strong> Error messages and service names</li>
                <li>• ✅ <strong>All service dropdown options:</strong> New Tires, Oil Change, Brake Service, etc.</li>
                <li>• ✅ <strong>Complete Spanish support:</strong> /book-appointment fully bilingual</li>
              </ul>
              <div className="mt-3 p-3 bg-green-100 rounded border border-green-300">
                <p className="text-green-800 font-medium">✅ All hardcoded English text has been replaced with translations!</p>
                <p className="text-green-700 text-sm mt-1">Toggle between English/Spanish to test complete functionality.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Translate;
