
-- 1. Enable RLS on _hhid tables (CRITICAL: credentials exposure)
ALTER TABLE public._hhid_account ENABLE ROW LEVEL SECURITY;
ALTER TABLE public._hhid_session ENABLE ROW LEVEL SECURITY;
ALTER TABLE public._hhid_user ENABLE ROW LEVEL SECURITY;
ALTER TABLE public._hhid_verification ENABLE ROW LEVEL SECURITY;

-- _hhid_account policies
CREATE POLICY "hhid_account_select_own"
  ON public._hhid_account FOR SELECT TO authenticated
  USING ("userId" = auth.uid()::text);

CREATE POLICY "hhid_account_manage_own"
  ON public._hhid_account FOR ALL TO authenticated
  USING ("userId" = auth.uid()::text)
  WITH CHECK ("userId" = auth.uid()::text);

-- _hhid_session policies
CREATE POLICY "hhid_session_select_own"
  ON public._hhid_session FOR SELECT TO authenticated
  USING ("userId" = auth.uid()::text);

CREATE POLICY "hhid_session_manage_own"
  ON public._hhid_session FOR ALL TO authenticated
  USING ("userId" = auth.uid()::text)
  WITH CHECK ("userId" = auth.uid()::text);

-- _hhid_user policies
CREATE POLICY "hhid_user_select_own"
  ON public._hhid_user FOR SELECT TO authenticated
  USING (id = auth.uid()::text);

CREATE POLICY "hhid_user_update_own"
  ON public._hhid_user FOR UPDATE TO authenticated
  USING (id = auth.uid()::text);

-- _hhid_verification policies
CREATE POLICY "hhid_verification_select_own"
  ON public._hhid_verification FOR SELECT TO authenticated
  USING (identifier = auth.email());

-- 2. Remove duplicate/overly permissive policies
DROP POLICY IF EXISTS "Settings access policy" ON public.oretir_settings;
DROP POLICY IF EXISTS "System can insert email logs" ON public.oretir_email_logs;
DROP POLICY IF EXISTS "Anyone can update cbake messages for admin" ON public.cbake_messages;
DROP POLICY IF EXISTS "Anyone can update cbake quotes for admin" ON public.cbake_quotes;
DROP POLICY IF EXISTS "Service can update payments" ON public.bitcoin_payments;
DROP POLICY IF EXISTS "Anyone can view crypto data" ON public.bitcoin_crypto_data;
DROP POLICY IF EXISTS "Edge functions can manage purchases" ON public.dc_credit_purchases;
DROP POLICY IF EXISTS "Service role can manage subscriptions" ON public."hiphop-subscribers";
DROP POLICY IF EXISTS "Service role can manage orders" ON public.hiphopworld_orders;

-- 3. Replace with proper policies

-- bitcoin_payments: service role only for updates
CREATE POLICY "Service role updates payments"
  ON public.bitcoin_payments FOR UPDATE TO service_role
  USING (true);

-- cbake_messages: admin update only
CREATE POLICY "Admin updates cbake messages"
  ON public.cbake_messages FOR UPDATE TO authenticated
  USING (public.is_cbake_admin());

-- cbake_quotes: admin update only
CREATE POLICY "Admin updates cbake quotes"
  ON public.cbake_quotes FOR UPDATE TO authenticated
  USING (public.is_cbake_admin());

-- dc_credit_purchases: service role for management, users for viewing
CREATE POLICY "Service manages purchases"
  ON public.dc_credit_purchases FOR ALL TO service_role
  USING (true) WITH CHECK (true);

-- hiphop-subscribers: service role for management
CREATE POLICY "Service manages subscriptions"
  ON public."hiphop-subscribers" FOR ALL TO service_role
  USING (true) WITH CHECK (true);

-- hiphopworld_orders: service role for management
CREATE POLICY "Service manages orders"
  ON public.hiphopworld_orders FOR ALL TO service_role
  USING (true) WITH CHECK (true);

-- oretir_email_logs: admin insert
CREATE POLICY "Admin inserts email logs"
  ON public.oretir_email_logs FOR INSERT TO authenticated
  WITH CHECK (public.is_admin());
