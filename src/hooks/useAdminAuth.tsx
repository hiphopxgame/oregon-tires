import { useState, useEffect } from 'react';
import { supabase } from '@/integrations/supabase/client';
import { useToast } from '@/hooks/use-toast';
import type { User, Session } from '@supabase/supabase-js';

export const useAdminAuth = () => {
  const [user, setUser] = useState<User | null>(null);
  const [session, setSession] = useState<Session | null>(null);
  const [loading, setLoading] = useState(true);
  const [isAdmin, setIsAdmin] = useState(false);
  const { toast } = useToast();

  const checkAdminStatus = async (userId: string) => {
    try {
      const { data, error } = await supabase
        .from('oretir_profiles')
        .select('is_admin')
        .eq('id', userId)
        .single();

      if (error) throw error;
      return data?.is_admin || false;
    } catch (error) {
      console.error('Error checking admin status:', error);
      return false;
    }
  };

  const signIn = async (email: string, password: string) => {
    try {
      const { data, error } = await supabase.auth.signInWithPassword({
        email,
        password,
      });

      if (error) throw error;

      if (data.user) {
        const adminStatus = await checkAdminStatus(data.user.id);
        if (!adminStatus) {
          await supabase.auth.signOut();
          throw new Error('Access denied. Admin privileges required.');
        }
      }

      return { data, error: null };
    } catch (error: any) {
      return { data: null, error };
    }
  };

  const signOut = async () => {
    try {
      const { error } = await supabase.auth.signOut();
      if (error) throw error;
      setUser(null);
      setSession(null);
      setIsAdmin(false);
    } catch (error) {
      console.error('Error signing out:', error);
      toast({
        title: "Error",
        description: "Failed to sign out",
        variant: "destructive",
      });
    }
  };

  useEffect(() => {
    // Set up auth state listener
    const { data: { subscription } } = supabase.auth.onAuthStateChange(
      (event, session) => {
        setSession(session);
        setUser(session?.user ?? null);
        
        if (session?.user) {
          // Defer admin status check to avoid deadlock
          setTimeout(() => {
            checkAdminStatus(session.user.id).then(adminStatus => {
              setIsAdmin(adminStatus);
              setLoading(false); // Set loading false after admin check
              
              if (!adminStatus && event === 'SIGNED_IN') {
                supabase.auth.signOut().then(() => {
                  toast({
                    title: "Access Denied",
                    description: "Admin privileges required",
                    variant: "destructive",
                  });
                });
              }
            });
          }, 0);
        } else {
          setIsAdmin(false);
          setLoading(false); // Set loading false for no session
        }
      }
    );

    // Check for existing session
    supabase.auth.getSession().then(({ data: { session } }) => {
      setSession(session);
      setUser(session?.user ?? null);
      
      if (session?.user) {
        checkAdminStatus(session.user.id).then(adminStatus => {
          setIsAdmin(adminStatus);
          
          if (!adminStatus) {
            supabase.auth.signOut();
          }
          setLoading(false);
        });
      } else {
        setLoading(false);
      }
    });

    return () => subscription.unsubscribe();
  }, [toast]);

  return {
    user,
    session,
    loading,
    isAdmin,
    signIn,
    signOut
  };
};