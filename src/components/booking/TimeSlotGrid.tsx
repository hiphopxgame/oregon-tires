
import React from 'react';

interface TimeSlot {
  time: string;
  display: string;
  status: 'available' | 'unavailable';
  conflictCount: number;
  message?: string;
}

interface TimeSlotGridProps {
  timeSlots: TimeSlot[];
  selectedTime: string;
  onTimeSelect: (time: string) => void;
}

export const TimeSlotGrid: React.FC<TimeSlotGridProps> = ({ timeSlots, selectedTime, onTimeSelect }) => {
  const availableSlots = timeSlots.filter(slot => slot.status === 'available');
  const unavailableSlots = timeSlots.filter(slot => slot.status === 'unavailable');

  const renderSlotGroup = (
    slots: TimeSlot[], 
    title: string, 
    colorClass: string, 
    selectedColorClass: string,
    clickable: boolean = true
  ) => {
    if (slots.length === 0) return null;

    return (
      <div className="mb-6">
        <h4 className={`font-semibold mb-3 ${title.includes('Available') ? 'text-green-700' : 'text-red-700'}`}>
          {title}
        </h4>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-2">
          {slots.map(slot => (
            clickable ? (
              <button
                key={slot.time}
                onClick={() => onTimeSelect(slot.time)}
                className={`p-3 rounded-lg border-2 transition-colors text-left ${
                  selectedTime === slot.time
                    ? selectedColorClass
                    : `${colorClass} hover:opacity-80`
                }`}
              >
                <div className="font-medium">{slot.display}</div>
                <div className="text-xs opacity-75">{slot.message}</div>
              </button>
            ) : (
              <div
                key={slot.time}
                className={`p-3 rounded-lg border-2 ${colorClass} opacity-75 cursor-not-allowed`}
                title={slot.message}
              >
                <div className="font-medium">{slot.display}</div>
                <div className="text-xs opacity-75">{slot.message}</div>
              </div>
            )
          ))}
        </div>
      </div>
    );
  };

  return (
    <div>
      {/* Legend */}
      <div className="flex flex-wrap gap-4 mb-6 text-sm">
        <div className="flex items-center gap-2">
          <div className="w-4 h-4 bg-green-100 border border-green-300 rounded"></div>
          <span>Available ({availableSlots.length})</span>
        </div>
        <div className="flex items-center gap-2">
          <div className="w-4 h-4 bg-red-100 border border-red-300 rounded"></div>
          <span>Unavailable ({unavailableSlots.length})</span>
        </div>
      </div>

      {/* Available times */}
      {renderSlotGroup(
        availableSlots,
        'Available Times',
        'bg-green-100 border-green-300 text-green-800',
        'bg-green-600 text-white border-green-600'
      )}

      {/* Unavailable times */}
      {renderSlotGroup(
        unavailableSlots,
        'Unavailable Times',
        'bg-red-100 border-red-300 text-red-800',
        '',
        false
      )}
    </div>
  );
};
