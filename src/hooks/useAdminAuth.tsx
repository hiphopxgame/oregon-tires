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
      console.log('Checking admin status for user:', userId);
      const { data, error } = await supabase
        .from('oretir_profiles')
        .select('is_admin')
        .eq('id', userId)
        .single();

      if (error) {
        console.error('Error in admin status query:', error);
        // If profile doesn't exist, create one
        if (error.code === 'PGRST116') {
          console.log('Profile not found, creating one...');
          const { data: insertData, error: insertError } = await supabase
            .from('oretir_profiles')
            .insert({ id: userId, is_admin: false })
            .select('is_admin')
            .single();
          
          if (insertError) {
            console.error('Error creating profile:', insertError);
            return false;
          }
          console.log('Profile created:', insertData);
          return insertData?.is_admin || false;
        }
        throw error;
      }
      
      console.log('Admin status data:', data);
      const isAdmin = data?.is_admin || false;
      console.log('Is admin:', isAdmin);
      return isAdmin;
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
          console.log('User session found, checking admin status...');
          // Defer admin status check to avoid deadlock
          setTimeout(() => {
            checkAdminStatus(session.user.id).then(adminStatus => {
              console.log('Admin status result:', adminStatus);
              setIsAdmin(adminStatus);
              setLoading(false); // Set loading false after admin check
              
              if (!adminStatus && event === 'SIGNED_IN') {
                console.log('User is not admin, signing out...');
                supabase.auth.signOut().then(() => {
                  toast({
                    title: "Access Denied",
                    description: "Admin privileges required",
                    variant: "destructive",
                  });
                });
              }
            }).catch(error => {
              console.error('Error in admin status check:', error);
              setLoading(false);
            });
          }, 0);
        } else {
          console.log('No user session found');
          setIsAdmin(false);
          setLoading(false); // Set loading false for no session
        }
      }
    );

    // Check for existing session
    supabase.auth.getSession().then(({ data: { session } }) => {
      console.log('Initial session check:', session?.user?.id);
      setSession(session);
      setUser(session?.user ?? null);
      
      if (session?.user) {
        checkAdminStatus(session.user.id).then(adminStatus => {
          console.log('Initial admin status:', adminStatus);
          setIsAdmin(adminStatus);
          
          if (!adminStatus) {
            console.log('Initial check: User is not admin, signing out...');
            supabase.auth.signOut();
          }
          setLoading(false);
        }).catch(error => {
          console.error('Error in initial admin check:', error);
          setLoading(false);
        });
      } else {
        console.log('No initial session found');
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