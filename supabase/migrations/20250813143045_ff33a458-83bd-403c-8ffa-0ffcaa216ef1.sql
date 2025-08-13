-- Fix infinite recursion in RLS policies by creating security definer functions

-- 1. Create security definer functions to avoid infinite recursion
CREATE OR REPLACE FUNCTION public.user_participates_in_game(game_id uuid, user_id uuid)
RETURNS boolean
LANGUAGE sql
STABLE SECURITY DEFINER
SET search_path = public
AS $$
  SELECT EXISTS (
    SELECT 1 
    FROM hiphop_bingo_game_participants gp 
    WHERE gp.game_id = $1 AND gp.user_id = $2
  );
$$;

CREATE OR REPLACE FUNCTION public.user_hosts_game(game_id uuid, user_id uuid)
RETURNS boolean
LANGUAGE sql
STABLE SECURITY DEFINER
SET search_path = public
AS $$
  SELECT EXISTS (
    SELECT 1 
    FROM hiphop_bingo_games g 
    WHERE g.id = $1 AND g.host_user_id = $2
  );
$$;

-- 2. Drop existing problematic policies
DROP POLICY IF EXISTS "Users can view games they participate in" ON public.hiphop_bingo_games;
DROP POLICY IF EXISTS "Users can view game participants for their games" ON public.hiphop_bingo_game_participants;

-- 3. Create new policies using security definer functions
CREATE POLICY "Users can view games they participate in"
  ON public.hiphop_bingo_games
  FOR SELECT
  USING (
    auth.uid() = host_user_id OR 
    public.user_participates_in_game(id, auth.uid())
  );

CREATE POLICY "Users can view game participants for their games"
  ON public.hiphop_bingo_game_participants
  FOR SELECT
  USING (
    auth.uid() = user_id OR 
    public.user_hosts_game(game_id, auth.uid())
  );

-- 4. Enable leaked password protection in auth settings
-- Note: This requires updating auth configuration, which is done via the dashboard