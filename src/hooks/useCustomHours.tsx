import { useState, useEffect, useCallback } from 'react';
import { supabase } from '@/integrations/supabase/client';
import { toast } from '@/hooks/use-toast';

export interface CustomHours {
  id: string;
  date: string;
  is_closed: boolean;
  opening_time: string | null;
  closing_time: string | null;
  simultaneous_bookings: number;
  created_at: string;
  updated_at: string;
}

export const useCustomHours = () => {
  const [customHours, setCustomHours] = useState<CustomHours[]>([]);
  const [loading, setLoading] = useState(true);

  const fetchCustomHours = useCallback(async () => {
    try {
      setLoading(true);
      const { data, error } = await supabase
        .from('oretir_custom_hours')
        .select('*')
        .order('date', { ascending: true });

      if (error) throw error;
      setCustomHours(data || []);
    } catch (error) {
      console.error('Error fetching custom hours:', error);
      toast({
        title: "Error",
        description: "Failed to load custom hours",
        variant: "destructive",
      });
    } finally {
      setLoading(false);
    }
  }, [toast]);

  const updateCustomHours = async (date: string, hours: {
    is_closed: boolean;
    opening_time?: string | null;
    closing_time?: string | null;
    simultaneous_bookings?: number;
  }) => {
    try {
      const { data, error } = await supabase
        .from('oretir_custom_hours')
        .upsert({
          date,
          is_closed: hours.is_closed,
          opening_time: hours.opening_time,
          closing_time: hours.closing_time,
          simultaneous_bookings: hours.simultaneous_bookings || 2,
        }, {
          onConflict: 'date'
        })
        .select()
        .single();

      if (error) throw error;

      // Update local state
      setCustomHours(prev => {
        const existing = prev.find(h => h.date === date);
        if (existing) {
          return prev.map(h => h.date === date ? data : h);
        } else {
          return [...prev, data];
        }
      });

      toast({
        title: "Success",
        description: "Store hours updated successfully",
      });

      return data;
    } catch (error) {
      console.error('Error updating custom hours:', error);
      toast({
        title: "Error",
        description: "Failed to update store hours",
        variant: "destructive",
      });
      throw error;
    }
  };

  const deleteCustomHours = async (date: string) => {
    try {
      const { error } = await supabase
        .from('oretir_custom_hours')
        .delete()
        .eq('date', date);

      if (error) throw error;

      setCustomHours(prev => prev.filter(h => h.date !== date));

      toast({
        title: "Success",
        description: "Custom hours removed, using default hours",
      });
    } catch (error) {
      console.error('Error deleting custom hours:', error);
      toast({
        title: "Error",
        description: "Failed to remove custom hours",
        variant: "destructive",
      });
    }
  };

  const getHoursForDate = (date: string) => {
    const custom = customHours.find(h => h.date === date);
    if (custom) {
      return custom;
    }
    
    // Default hours: Sunday closed, Mon-Sat 7AM-7PM
    const dayOfWeek = new Date(date + 'T00:00:00').getDay();
    if (dayOfWeek === 0) { // Sunday
      return {
        id: '',
        date,
        is_closed: true,
        opening_time: null,
        closing_time: null,
        simultaneous_bookings: 2,
        created_at: '',
        updated_at: ''
      };
    }
    
    return {
      id: '',
      date,
      is_closed: false,
      opening_time: '07:00',
      closing_time: '19:00',
      simultaneous_bookings: 2,
      created_at: '',
      updated_at: ''
    };
  };

  useEffect(() => {
    fetchCustomHours();

    // Set up real-time subscription for custom hours changes with unique channel name
    const channelId = Math.random().toString(36).substr(2, 9);
    const channel = supabase
      .channel(`hours-editor-changes-${channelId}`)
      .on(
        'postgres_changes',
        {
          event: '*',
          schema: 'public',
          table: 'oretir_custom_hours'
        },
        (payload) => {
          console.log('Real-time custom hours change detected:', payload);
          setTimeout(() => fetchCustomHours(), 100); // Small delay to avoid rapid calls
        }
      )
      .subscribe();

    return () => {
      supabase.removeChannel(channel);
    };
  }, [fetchCustomHours]);

  return {
    customHours,
    loading,
    updateCustomHours,
    deleteCustomHours,
    getHoursForDate,
    refetch: fetchCustomHours
  };
};