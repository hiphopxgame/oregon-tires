interface HoursInfoProps {
  isCustomHours: boolean;
}

export const HoursInfo = ({ isCustomHours }: HoursInfoProps) => {
  return (
    <>
      {isCustomHours && (
        <div className="text-sm text-blue-600 bg-blue-50 p-2 rounded">
          ℹ️ This date has custom hours that override the default schedule.
        </div>
      )}

      <div className="text-sm text-gray-600 bg-gray-50 p-2 rounded">
        <strong>Default Schedule:</strong><br />
        Sunday: Closed<br />
        Monday - Saturday: 7:00 AM - 7:00 PM
      </div>
    </>
  );
};