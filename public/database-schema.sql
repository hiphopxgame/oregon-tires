-- ============================================
-- Oregon Tires Project - Complete Database Schema
-- Generated from Supabase Types
-- ============================================

-- ============================================
-- ENUMS
-- ============================================

CREATE TYPE public.app_role AS ENUM ('admin', 'user', 'artist');
CREATE TYPE public.content_category AS ENUM ('music_video', 'interview', 'behind_the_scenes', 'live_performance', 'documentary', 'other');

-- ============================================
-- OREGON TIRES TABLES
-- ============================================

-- Admin Accounts
CREATE TABLE public.admin_accounts (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  email TEXT NOT NULL,
  full_name TEXT NOT NULL,
  is_active BOOLEAN DEFAULT true,
  project_id TEXT NOT NULL DEFAULT 'oregon-tires',
  user_id UUID,
  created_at TIMESTAMPTZ DEFAULT now(),
  updated_at TIMESTAMPTZ DEFAULT now()
);

-- Oregon Tires Profiles
CREATE TABLE public.oretir_profiles (
  id UUID PRIMARY KEY,
  is_admin BOOLEAN DEFAULT false,
  project_id TEXT DEFAULT 'oregon-tires',
  created_at TIMESTAMPTZ DEFAULT now(),
  updated_at TIMESTAMPTZ DEFAULT now()
);

-- Oregon Tires Appointments
CREATE TABLE public.oretir_appointments (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  first_name TEXT NOT NULL,
  last_name TEXT NOT NULL,
  email TEXT NOT NULL,
  phone TEXT,
  service TEXT NOT NULL,
  preferred_date TEXT NOT NULL,
  preferred_time TEXT NOT NULL,
  message TEXT,
  status TEXT DEFAULT 'pending',
  language TEXT DEFAULT 'en',
  assigned_employee_id UUID,
  tire_size TEXT,
  license_plate TEXT,
  vin TEXT,
  service_location TEXT,
  customer_address TEXT,
  customer_city TEXT,
  customer_state TEXT,
  customer_zip TEXT,
  vehicle_id UUID,
  travel_distance_miles NUMERIC,
  travel_cost_estimate NUMERIC,
  started_at TIMESTAMPTZ,
  completed_at TIMESTAMPTZ,
  actual_duration_minutes INTEGER,
  actual_duration_seconds INTEGER,
  admin_notes TEXT,
  created_at TIMESTAMPTZ DEFAULT now(),
  updated_at TIMESTAMPTZ DEFAULT now()
);

-- Oregon Tires Contact Messages
CREATE TABLE public.oregon_tires_contact_messages (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  first_name TEXT NOT NULL,
  last_name TEXT NOT NULL,
  email TEXT NOT NULL,
  phone TEXT,
  message TEXT NOT NULL,
  status TEXT DEFAULT 'unread',
  language TEXT DEFAULT 'en',
  created_at TIMESTAMPTZ DEFAULT now(),
  updated_at TIMESTAMPTZ DEFAULT now()
);

-- Oregon Tires Admin Notifications
CREATE TABLE public.oretir_admin_notifications (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  type TEXT NOT NULL,
  title TEXT NOT NULL,
  message TEXT NOT NULL,
  read BOOLEAN DEFAULT false,
  appointment_id UUID,
  created_at TIMESTAMPTZ DEFAULT now()
);

-- Oregon Tires Employees
CREATE TABLE public.oregon_tires_employees (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  name TEXT NOT NULL,
  email TEXT,
  phone TEXT,
  role TEXT DEFAULT 'technician',
  is_active BOOLEAN DEFAULT true,
  color TEXT DEFAULT '#3B82F6',
  user_id UUID,
  created_at TIMESTAMPTZ DEFAULT now(),
  updated_at TIMESTAMPTZ DEFAULT now()
);

-- Oregon Tires Employee Schedules
CREATE TABLE public.oregon_tires_employee_schedules (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  employee_id UUID NOT NULL REFERENCES public.oregon_tires_employees(id) ON DELETE CASCADE,
  day_of_week INTEGER NOT NULL,
  start_time TEXT NOT NULL,
  end_time TEXT NOT NULL,
  is_available BOOLEAN DEFAULT true,
  created_at TIMESTAMPTZ DEFAULT now(),
  updated_at TIMESTAMPTZ DEFAULT now()
);

-- Oregon Tires Custom Hours
CREATE TABLE public.oregon_tires_custom_hours (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  day_of_week INTEGER NOT NULL UNIQUE,
  open_time TEXT NOT NULL,
  close_time TEXT NOT NULL,
  is_closed BOOLEAN DEFAULT false,
  max_simultaneous_bookings INTEGER DEFAULT 2,
  created_at TIMESTAMPTZ DEFAULT now(),
  updated_at TIMESTAMPTZ DEFAULT now()
);

-- Oregon Tires Gallery Images
CREATE TABLE public.oregon_tires_gallery_images (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  image_url TEXT NOT NULL,
  title TEXT,
  description TEXT,
  display_order INTEGER DEFAULT 0,
  is_active BOOLEAN DEFAULT true,
  created_at TIMESTAMPTZ DEFAULT now(),
  updated_at TIMESTAMPTZ DEFAULT now()
);

-- Oregon Tires Service Images
CREATE TABLE public.oregon_tires_service_images (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  service_key TEXT NOT NULL UNIQUE,
  image_url TEXT NOT NULL,
  alt_text TEXT,
  created_at TIMESTAMPTZ DEFAULT now(),
  updated_at TIMESTAMPTZ DEFAULT now()
);

-- Customer Vehicles
CREATE TABLE public.customer_vehicles (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  customer_name TEXT NOT NULL,
  customer_email TEXT NOT NULL,
  make TEXT,
  model TEXT,
  year INTEGER,
  license_plate TEXT,
  vin TEXT,
  created_at TIMESTAMPTZ DEFAULT now(),
  updated_at TIMESTAMPTZ DEFAULT now()
);

-- Contact Messages (Generic)
CREATE TABLE public.contact_messages (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  name TEXT NOT NULL,
  email TEXT NOT NULL,
  phone TEXT,
  company TEXT,
  service_type TEXT,
  message TEXT NOT NULL,
  status TEXT DEFAULT 'new',
  created_at TIMESTAMPTZ DEFAULT now(),
  updated_at TIMESTAMPTZ DEFAULT now()
);

-- ============================================
-- USER ROLES
-- ============================================

CREATE TABLE public.user_roles (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id UUID NOT NULL,
  role app_role NOT NULL,
  created_at TIMESTAMPTZ DEFAULT now(),
  UNIQUE(user_id, role)
);

-- ============================================
-- PORTLAND EVENTS TABLES
-- ============================================

CREATE TABLE public.por_eve_profiles (
  id UUID PRIMARY KEY,
  email TEXT NOT NULL,
  full_name TEXT,
  username TEXT,
  display_name TEXT,
  bio TEXT,
  avatar_url TEXT,
  website_url TEXT,
  facebook_url TEXT,
  instagram_url TEXT,
  twitter_url TEXT,
  youtube_url TEXT,
  is_public BOOLEAN DEFAULT false,
  is_email_public BOOLEAN DEFAULT false,
  city TEXT,
  state TEXT,
  project_id TEXT DEFAULT 'portland-events',
  created_at TIMESTAMPTZ DEFAULT now(),
  updated_at TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE public.venues (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  name TEXT NOT NULL,
  address TEXT,
  city TEXT,
  state TEXT,
  zip_code TEXT,
  phone TEXT,
  website TEXT,
  facebook_url TEXT,
  instagram_url TEXT,
  twitter_url TEXT,
  youtube_url TEXT,
  image_urls TEXT[],
  ages TEXT,
  api_source TEXT,
  status TEXT DEFAULT 'pending',
  created_at TIMESTAMPTZ DEFAULT now(),
  updated_at TIMESTAMPTZ DEFAULT now(),
  UNIQUE(name, city, state, zip_code)
);

CREATE TABLE public.user_events (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  title TEXT NOT NULL,
  description TEXT,
  category TEXT,
  start_date DATE NOT NULL,
  start_time TIME,
  end_time TIME,
  is_recurring BOOLEAN DEFAULT false,
  recurrence_type TEXT,
  recurrence_pattern TEXT,
  recurrence_end_date DATE,
  price_display TEXT,
  ticket_url TEXT,
  website_url TEXT,
  facebook_url TEXT,
  instagram_url TEXT,
  twitter_url TEXT,
  youtube_url TEXT,
  image_url TEXT,
  venue_name TEXT,
  venue_address TEXT,
  venue_city TEXT,
  venue_state TEXT,
  venue_zip TEXT,
  api_source TEXT,
  external_id TEXT,
  status TEXT DEFAULT 'pending',
  created_by UUID,
  created_at TIMESTAMPTZ DEFAULT now(),
  updated_at TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE public.staging_venues (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  import_batch_id UUID,
  name TEXT NOT NULL,
  address TEXT,
  city TEXT,
  state TEXT,
  zip_code TEXT,
  phone TEXT,
  website TEXT,
  facebook_url TEXT,
  instagram_url TEXT,
  twitter_url TEXT,
  youtube_url TEXT,
  image_urls TEXT[],
  ages TEXT,
  api_source TEXT,
  created_at TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE public.staging_events (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  import_batch_id UUID,
  title TEXT NOT NULL,
  description TEXT,
  category TEXT,
  start_date DATE,
  start_time TIME,
  end_time TIME,
  is_recurring BOOLEAN DEFAULT false,
  recurrence_type TEXT,
  recurrence_pattern TEXT,
  recurrence_end_date DATE,
  price_display TEXT,
  ticket_url TEXT,
  website_url TEXT,
  facebook_url TEXT,
  instagram_url TEXT,
  twitter_url TEXT,
  youtube_url TEXT,
  image_url TEXT,
  venue_name TEXT,
  venue_address TEXT,
  venue_city TEXT,
  venue_state TEXT,
  venue_zip TEXT,
  api_source TEXT,
  external_id TEXT,
  created_at TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE public.import_batches (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  filename TEXT NOT NULL,
  file_type TEXT NOT NULL,
  status TEXT DEFAULT 'pending',
  total_events INTEGER DEFAULT 0,
  total_venues INTEGER DEFAULT 0,
  notes TEXT,
  created_by UUID,
  reviewed_by UUID,
  reviewed_at TIMESTAMPTZ,
  created_at TIMESTAMPTZ DEFAULT now(),
  updated_at TIMESTAMPTZ DEFAULT now()
);

-- ============================================
-- ARTIST PROFILES
-- ============================================

CREATE TABLE public.artist_profiles (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id UUID,
  name TEXT NOT NULL,
  slug TEXT NOT NULL UNIQUE,
  bio TEXT,
  email TEXT,
  avatar_url TEXT,
  website_url TEXT,
  facebook_url TEXT,
  instagram_url TEXT,
  twitter_url TEXT,
  youtube_url TEXT,
  spotify_url TEXT,
  apple_music_url TEXT,
  soundcloud_url TEXT,
  bandcamp_url TEXT,
  tiktok_url TEXT,
  is_public BOOLEAN DEFAULT false,
  is_featured BOOLEAN DEFAULT false,
  is_archived BOOLEAN DEFAULT false,
  is_email_public BOOLEAN DEFAULT false,
  display_order INTEGER,
  created_at TIMESTAMPTZ DEFAULT now(),
  updated_at TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE public.artist_photos (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  artist_id UUID NOT NULL REFERENCES public.artist_profiles(id) ON DELETE CASCADE,
  image_url TEXT NOT NULL,
  caption TEXT,
  is_featured BOOLEAN DEFAULT false,
  display_order INTEGER,
  position_x NUMERIC,
  position_y NUMERIC,
  scale NUMERIC,
  created_at TIMESTAMPTZ DEFAULT now(),
  updated_at TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE public.artist_videos (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  artist_id UUID NOT NULL REFERENCES public.artist_profiles(id) ON DELETE CASCADE,
  youtube_url TEXT NOT NULL,
  youtube_id TEXT NOT NULL,
  title TEXT NOT NULL,
  description TEXT,
  thumbnail_url TEXT,
  is_featured BOOLEAN DEFAULT false,
  display_order INTEGER,
  created_at TIMESTAMPTZ DEFAULT now(),
  updated_at TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE public.artist_content (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id UUID NOT NULL,
  title TEXT NOT NULL,
  youtube_url TEXT NOT NULL,
  category content_category NOT NULL,
  status TEXT DEFAULT 'pending',
  created_at TIMESTAMPTZ DEFAULT now(),
  updated_at TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE public.artist_applications (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id UUID NOT NULL UNIQUE,
  status TEXT DEFAULT 'pending',
  reason TEXT,
  processed_by UUID,
  processed_at TIMESTAMPTZ,
  created_at TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE public.music_videos (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  artist_id UUID NOT NULL REFERENCES public.por_eve_profiles(id),
  title TEXT NOT NULL,
  youtube_url TEXT NOT NULL,
  youtube_id TEXT NOT NULL,
  status TEXT DEFAULT 'pending',
  rejection_reason TEXT,
  approved_by UUID REFERENCES public.por_eve_profiles(id),
  approved_at TIMESTAMPTZ,
  created_at TIMESTAMPTZ DEFAULT now(),
  updated_at TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE public.featured_artists (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  name TEXT NOT NULL,
  bio TEXT,
  image_url TEXT,
  website_url TEXT,
  facebook_url TEXT,
  instagram_url TEXT,
  twitter_url TEXT,
  youtube_url TEXT,
  spotify_url TEXT,
  apple_music_url TEXT,
  soundcloud_url TEXT,
  bandcamp_url TEXT,
  tiktok_url TEXT,
  is_active BOOLEAN DEFAULT true,
  display_order INTEGER,
  created_by UUID,
  created_at TIMESTAMPTZ DEFAULT now(),
  updated_at TIMESTAMPTZ DEFAULT now()
);

-- ============================================
-- BRAIDING / HAIR SALON TABLES
-- ============================================

CREATE TABLE public.braiding_profiles (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id UUID NOT NULL,
  first_name TEXT NOT NULL,
  last_name TEXT NOT NULL,
  email TEXT,
  phone TEXT,
  address TEXT,
  city TEXT,
  state TEXT,
  zip_code TEXT,
  hair_type TEXT,
  allergies TEXT,
  preferred_contact TEXT,
  project_id TEXT DEFAULT 'iwitty-hair',
  created_at TIMESTAMPTZ DEFAULT now(),
  updated_at TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE public.hair_styles (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  name TEXT NOT NULL,
  description TEXT,
  base_price NUMERIC,
  duration_hours NUMERIC,
  project_id TEXT DEFAULT 'iwitty-hair',
  created_at TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE public.appointments (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  client_id UUID NOT NULL REFERENCES public.braiding_profiles(user_id),
  style_id UUID REFERENCES public.hair_styles(id),
  appointment_date DATE NOT NULL,
  appointment_time TIME NOT NULL,
  status TEXT DEFAULT 'pending',
  special_requests TEXT,
  price_quote NUMERIC,
  estimated_duration INTEGER,
  admin_comments TEXT,
  confirmation_sent BOOLEAN DEFAULT false,
  created_at TIMESTAMPTZ DEFAULT now(),
  updated_at TIMESTAMPTZ DEFAULT now()
);

-- ============================================
-- IWITTY TABLES
-- ============================================

CREATE TABLE public.iwitty_profiles (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id UUID NOT NULL UNIQUE,
  first_name TEXT NOT NULL,
  last_name TEXT NOT NULL,
  email TEXT,
  phone TEXT,
  address TEXT,
  city TEXT,
  state TEXT,
  zip_code TEXT,
  hair_type TEXT,
  allergies TEXT,
  preferred_contact TEXT,
  created_at TIMESTAMPTZ DEFAULT now(),
  updated_at TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE public.iwitty_admin_accounts (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id UUID NOT NULL UNIQUE,
  email TEXT NOT NULL,
  full_name TEXT NOT NULL,
  is_active BOOLEAN DEFAULT true,
  created_at TIMESTAMPTZ DEFAULT now(),
  updated_at TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE public.iwitty_hair_styles (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  name TEXT NOT NULL,
  description TEXT,
  base_price NUMERIC,
  duration_hours NUMERIC,
  created_at TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE public.iwitty_appointments (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  client_id UUID NOT NULL REFERENCES public.iwitty_profiles(user_id),
  style_id UUID REFERENCES public.iwitty_hair_styles(id),
  appointment_date DATE NOT NULL,
  appointment_time TIME NOT NULL,
  status TEXT DEFAULT 'pending',
  special_requests TEXT,
  price_quote NUMERIC,
  estimated_duration INTEGER,
  admin_comments TEXT,
  confirmation_sent BOOLEAN DEFAULT false,
  created_at TIMESTAMPTZ DEFAULT now(),
  updated_at TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE public.iwitty_portfolio_images (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  title TEXT NOT NULL,
  description TEXT,
  image_url TEXT NOT NULL,
  style_id UUID REFERENCES public.iwitty_hair_styles(id),
  client_name TEXT,
  completion_date DATE,
  is_featured BOOLEAN DEFAULT false,
  display_order INTEGER,
  created_at TIMESTAMPTZ DEFAULT now(),
  updated_at TIMESTAMPTZ DEFAULT now()
);

-- ============================================
-- CBAKE TABLES
-- ============================================

CREATE TABLE public.cbake_profiles (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id UUID NOT NULL UNIQUE,
  email TEXT NOT NULL,
  full_name TEXT,
  is_admin BOOLEAN DEFAULT false,
  created_at TIMESTAMPTZ DEFAULT now(),
  updated_at TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE public.cbake_products (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  name TEXT NOT NULL,
  description TEXT,
  product_type TEXT NOT NULL,
  base_price NUMERIC,
  image_url TEXT,
  ingredients TEXT,
  origin TEXT,
  tags TEXT[],
  is_active BOOLEAN DEFAULT true,
  display_order INTEGER,
  created_at TIMESTAMPTZ DEFAULT now(),
  updated_at TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE public.cbake_orders (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id UUID REFERENCES public.cbake_profiles(user_id),
  product_id UUID REFERENCES public.cbake_products(id),
  product_name TEXT,
  name TEXT NOT NULL,
  first_name TEXT NOT NULL,
  last_name TEXT,
  email TEXT NOT NULL,
  phone TEXT,
  order_type TEXT NOT NULL,
  company_name TEXT,
  business_location TEXT,
  dough_type TEXT NOT NULL,
  filling TEXT NOT NULL,
  quantity INTEGER DEFAULT 1,
  delivery TEXT NOT NULL,
  special_instructions TEXT,
  estimated_total NUMERIC,
  status TEXT DEFAULT 'pending',
  created_at TIMESTAMPTZ DEFAULT now(),
  updated_at TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE public.cbake_cart_items (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id UUID,
  product_id UUID,
  product_name TEXT NOT NULL,
  dough_type TEXT,
  filling TEXT,
  quantity INTEGER DEFAULT 1,
  unit_price NUMERIC NOT NULL,
  special_instructions TEXT,
  created_at TIMESTAMPTZ DEFAULT now(),
  updated_at TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE public.cbake_messages (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id UUID REFERENCES public.cbake_profiles(user_id),
  name TEXT NOT NULL,
  email TEXT NOT NULL,
  inquiry_type TEXT NOT NULL,
  message TEXT NOT NULL,
  status TEXT DEFAULT 'new',
  created_at TIMESTAMPTZ DEFAULT now(),
  updated_at TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE public.cbake_quotes (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  name TEXT NOT NULL,
  email TEXT NOT NULL,
  phone TEXT,
  business_name TEXT,
  event_type TEXT,
  event_date DATE,
  guest_count INTEGER,
  catering_services JSONB,
  special_requirements TEXT,
  status TEXT DEFAULT 'pending',
  quoted_amount NUMERIC,
  admin_notes TEXT,
  created_at TIMESTAMPTZ DEFAULT now(),
  updated_at TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE public.cbake_newsletter_subscriptions (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  email TEXT NOT NULL UNIQUE,
  status TEXT DEFAULT 'active',
  created_at TIMESTAMPTZ DEFAULT now(),
  updated_at TIMESTAMPTZ DEFAULT now()
);

-- ============================================
-- HIPHOP WORLD TABLES
-- ============================================

CREATE TABLE public.hiphopworld_profiles (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id UUID NOT NULL UNIQUE,
  username TEXT,
  display_name TEXT,
  bio TEXT,
  avatar_url TEXT,
  hip_hop_cash_balance NUMERIC DEFAULT 0,
  hip_hop_cards_owned INTEGER DEFAULT 0,
  communities_owned INTEGER DEFAULT 0,
  created_at TIMESTAMPTZ DEFAULT now(),
  updated_at TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE public.hiphopworld_cards (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id UUID NOT NULL REFERENCES public.hiphopworld_profiles(user_id),
  card_type TEXT NOT NULL,
  title TEXT NOT NULL,
  description TEXT,
  content_url TEXT,
  is_public BOOLEAN DEFAULT false,
  individual_balance NUMERIC DEFAULT 0,
  total_balance NUMERIC DEFAULT 0,
  created_at TIMESTAMPTZ DEFAULT now(),
  updated_at TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE public.hiphopworld_communities (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  community_id INTEGER NOT NULL,
  land_id INTEGER NOT NULL,
  owner_user_id UUID REFERENCES public.hiphopworld_profiles(user_id),
  price NUMERIC NOT NULL,
  purchased_at TIMESTAMPTZ,
  created_at TIMESTAMPTZ DEFAULT now(),
  updated_at TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE public.hiphopworld_orders (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id UUID,
  product_type TEXT NOT NULL,
  amount NUMERIC NOT NULL,
  quantity INTEGER DEFAULT 1,
  currency TEXT DEFAULT 'usd',
  status TEXT DEFAULT 'pending',
  stripe_session_id TEXT,
  metadata JSONB,
  created_at TIMESTAMPTZ DEFAULT now(),
  updated_at TIMESTAMPTZ DEFAULT now()
);

-- ============================================
-- HIPHOP BINGO TABLES
-- ============================================

CREATE TABLE public.hiphop_bingo_profiles (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id UUID NOT NULL UNIQUE,
  username TEXT NOT NULL,
  display_name TEXT,
  avatar_url TEXT,
  is_admin BOOLEAN DEFAULT false,
  created_at TIMESTAMPTZ DEFAULT now(),
  updated_at TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE public.hiphop_bingo_videos (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  youtube_url TEXT NOT NULL,
  youtube_id TEXT NOT NULL UNIQUE,
  title TEXT NOT NULL,
  artist TEXT NOT NULL,
  thumbnail_url TEXT,
  duration INTEGER,
  status TEXT DEFAULT 'pending',
  submitted_by UUID,
  approved_by UUID,
  created_at TIMESTAMPTZ DEFAULT now(),
  updated_at TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE public.hiphop_bingo_playlists (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id UUID NOT NULL,
  name TEXT NOT NULL,
  description TEXT,
  is_public BOOLEAN DEFAULT false,
  video_count INTEGER DEFAULT 0,
  created_at TIMESTAMPTZ DEFAULT now(),
  updated_at TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE public.hiphop_bingo_playlist_videos (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  playlist_id UUID NOT NULL REFERENCES public.hiphop_bingo_playlists(id) ON DELETE CASCADE,
  video_id UUID NOT NULL REFERENCES public.hiphop_bingo_videos(id) ON DELETE CASCADE,
  position INTEGER NOT NULL,
  created_at TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE public.hiphop_bingo_games (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  host_user_id UUID NOT NULL,
  playlist_id UUID NOT NULL REFERENCES public.hiphop_bingo_playlists(id),
  status TEXT DEFAULT 'waiting',
  current_video_index INTEGER DEFAULT 0,
  winner_user_id UUID,
  completed_at TIMESTAMPTZ,
  created_at TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE public.hiphop_bingo_game_participants (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  game_id UUID NOT NULL REFERENCES public.hiphop_bingo_games(id) ON DELETE CASCADE,
  user_id UUID NOT NULL,
  board_data JSONB NOT NULL,
  marked_positions JSONB DEFAULT '[]'::jsonb,
  claimed_bingo_at TIMESTAMPTZ,
  joined_at TIMESTAMPTZ DEFAULT now()
);

-- ============================================
-- DC BUSINESS TABLES
-- ============================================

CREATE TABLE public.dc_business_profiles (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id UUID NOT NULL UNIQUE,
  username TEXT,
  full_name TEXT,
  avatar_url TEXT,
  linkedin_access_token TEXT,
  created_at TIMESTAMPTZ DEFAULT now(),
  updated_at TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE public.dc_user_cash (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id UUID NOT NULL UNIQUE,
  balance NUMERIC DEFAULT 0,
  credits INTEGER DEFAULT 0,
  created_at TIMESTAMPTZ DEFAULT now(),
  updated_at TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE public.dc_business_directory (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id UUID,
  business_name TEXT NOT NULL,
  business_description TEXT,
  category TEXT NOT NULL,
  address TEXT,
  city TEXT,
  state TEXT,
  zip_code TEXT,
  phone TEXT,
  email TEXT,
  website TEXT,
  tags TEXT[],
  cash_amount NUMERIC DEFAULT 0,
  is_approved BOOLEAN DEFAULT false,
  is_featured BOOLEAN DEFAULT false,
  created_at TIMESTAMPTZ DEFAULT now(),
  updated_at TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE public.dc_business_discussions (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id UUID,
  title TEXT NOT NULL,
  content TEXT NOT NULL,
  category TEXT NOT NULL,
  tags TEXT[],
  is_pinned BOOLEAN DEFAULT false,
  created_at TIMESTAMPTZ DEFAULT now(),
  updated_at TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE public.dc_business_comments (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  discussion_id UUID NOT NULL REFERENCES public.dc_business_discussions(id) ON DELETE CASCADE,
  user_id UUID,
  content TEXT NOT NULL,
  created_at TIMESTAMPTZ DEFAULT now(),
  updated_at TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE public.dc_advertisements (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id UUID,
  title TEXT NOT NULL,
  description TEXT,
  image_url TEXT,
  target_url TEXT NOT NULL,
  cash_spent NUMERIC DEFAULT 0,
  is_active BOOLEAN DEFAULT true,
  created_at TIMESTAMPTZ DEFAULT now(),
  updated_at TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE public.dc_ad_stats (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  ad_id UUID NOT NULL REFERENCES public.dc_advertisements(id) ON DELETE CASCADE,
  user_id UUID,
  event_type TEXT NOT NULL,
  ip_address INET,
  user_agent TEXT,
  created_at TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE public.dc_credit_purchases (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id UUID,
  credits_purchased INTEGER NOT NULL,
  amount_paid NUMERIC NOT NULL,
  currency TEXT DEFAULT 'usd',
  status TEXT DEFAULT 'pending',
  stripe_session_id TEXT,
  created_at TIMESTAMPTZ DEFAULT now(),
  updated_at TIMESTAMPTZ DEFAULT now()
);

-- ============================================
-- BITCOIN / CRYPTO TABLES
-- ============================================

CREATE TABLE public.bitcoin_crypto_data (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  cmc_id INTEGER NOT NULL,
  name TEXT NOT NULL,
  symbol TEXT NOT NULL,
  slug TEXT NOT NULL,
  rank INTEGER NOT NULL,
  price NUMERIC NOT NULL,
  market_cap NUMERIC NOT NULL,
  volume_24h NUMERIC NOT NULL,
  percent_change_1h NUMERIC,
  percent_change_24h NUMERIC,
  percent_change_7d NUMERIC,
  percent_change_30d NUMERIC,
  logo_url TEXT,
  last_updated TIMESTAMPTZ NOT NULL,
  created_at TIMESTAMPTZ DEFAULT now(),
  updated_at TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE public.bitcoin_payments (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id UUID NOT NULL,
  community_id TEXT NOT NULL,
  land_id INTEGER NOT NULL,
  cash_amount NUMERIC NOT NULL,
  btc_amount NUMERIC NOT NULL,
  payment_address TEXT,
  payment_status TEXT DEFAULT 'pending',
  nowpayments_payment_id TEXT,
  nowpayments_order_id TEXT,
  expires_at TIMESTAMPTZ,
  created_at TIMESTAMPTZ DEFAULT now(),
  updated_at TIMESTAMPTZ DEFAULT now()
);

-- ============================================
-- PDXBUS TABLES
-- ============================================

CREATE TABLE public.pdxbus_profiles (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id UUID NOT NULL UNIQUE,
  full_name TEXT,
  email TEXT,
  created_at TIMESTAMPTZ DEFAULT now(),
  updated_at TIMESTAMPTZ DEFAULT now()
);

-- ============================================
-- MULTICHAIN TABLES
-- ============================================

CREATE TABLE public.multichain_profiles (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id UUID NOT NULL UNIQUE,
  username TEXT,
  display_name TEXT,
  avatar_url TEXT,
  created_at TIMESTAMPTZ DEFAULT now(),
  updated_at TIMESTAMPTZ DEFAULT now()
);

-- ============================================
-- MEMBERS TABLES
-- ============================================

CREATE TABLE public.members (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id UUID NOT NULL UNIQUE,
  username TEXT NOT NULL UNIQUE,
  display_name TEXT,
  bio TEXT,
  avatar_url TEXT,
  created_at TIMESTAMPTZ DEFAULT now(),
  updated_at TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE public.member_social_accounts (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  member_id UUID NOT NULL REFERENCES public.members(id) ON DELETE CASCADE,
  provider TEXT NOT NULL,
  provider_id TEXT NOT NULL,
  provider_username TEXT,
  provider_email TEXT,
  connected_at TIMESTAMPTZ DEFAULT now(),
  UNIQUE(member_id, provider)
);

-- ============================================
-- DONATIONS TABLE
-- ============================================

CREATE TABLE public.donations (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id UUID,
  donor_name TEXT,
  email TEXT,
  amount NUMERIC NOT NULL,
  currency TEXT DEFAULT 'usd',
  message TEXT,
  status TEXT DEFAULT 'pending',
  stripe_session_id TEXT,
  created_at TIMESTAMPTZ DEFAULT now(),
  updated_at TIMESTAMPTZ DEFAULT now()
);

-- ============================================
-- HIPHOP SUBSCRIBERS
-- ============================================

CREATE TABLE public."hiphop-subscribers" (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id UUID,
  email TEXT NOT NULL,
  subscribed BOOLEAN DEFAULT true,
  subscription_tier TEXT,
  subscription_end TIMESTAMPTZ,
  stripe_customer_id TEXT,
  created_at TIMESTAMPTZ DEFAULT now(),
  updated_at TIMESTAMPTZ DEFAULT now()
);

-- ============================================
-- PORMAR CONSULTATION REQUESTS
-- ============================================

CREATE TABLE public.pormar_consultation_requests (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  email TEXT NOT NULL,
  name TEXT,
  phone TEXT,
  message TEXT,
  status TEXT DEFAULT 'pending',
  created_at TIMESTAMPTZ DEFAULT now(),
  updated_at TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE public.consultation_access_log (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  consultation_id UUID REFERENCES public.pormar_consultation_requests(id),
  accessed_by UUID,
  access_type TEXT NOT NULL,
  ip_address INET,
  user_agent TEXT,
  accessed_at TIMESTAMPTZ DEFAULT now()
);

-- ============================================
-- INTERNAL TABLES (Better Auth)
-- ============================================

CREATE TABLE public._hhid_user (
  id TEXT PRIMARY KEY,
  email TEXT NOT NULL UNIQUE,
  name TEXT NOT NULL,
  emailVerified BOOLEAN DEFAULT false,
  image TEXT,
  createdAt TIMESTAMPTZ DEFAULT now(),
  updatedAt TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE public._hhid_session (
  id TEXT PRIMARY KEY,
  userId TEXT NOT NULL REFERENCES public._hhid_user(id) ON DELETE CASCADE,
  token TEXT NOT NULL UNIQUE,
  expiresAt TIMESTAMPTZ NOT NULL,
  ipAddress TEXT,
  userAgent TEXT,
  createdAt TIMESTAMPTZ DEFAULT now(),
  updatedAt TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE public._hhid_account (
  id TEXT PRIMARY KEY,
  userId TEXT NOT NULL REFERENCES public._hhid_user(id) ON DELETE CASCADE,
  accountId TEXT NOT NULL,
  providerId TEXT NOT NULL,
  accessToken TEXT,
  accessTokenExpiresAt TIMESTAMPTZ,
  refreshToken TEXT,
  refreshTokenExpiresAt TIMESTAMPTZ,
  idToken TEXT,
  scope TEXT,
  password TEXT,
  createdAt TIMESTAMPTZ DEFAULT now(),
  updatedAt TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE public._hhid_verification (
  id TEXT PRIMARY KEY,
  identifier TEXT NOT NULL,
  value TEXT NOT NULL,
  expiresAt TIMESTAMPTZ NOT NULL,
  createdAt TIMESTAMPTZ DEFAULT now(),
  updatedAt TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE public._members (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id UUID NOT NULL,
  username TEXT NOT NULL UNIQUE,
  display_name TEXT,
  email TEXT,
  bio TEXT,
  avatar_url TEXT,
  social_accounts JSONB,
  is_public BOOLEAN DEFAULT true,
  verified BOOLEAN DEFAULT false,
  created_at TIMESTAMPTZ DEFAULT now(),
  updated_at TIMESTAMPTZ DEFAULT now()
);

-- ============================================
-- END OF SCHEMA
-- ============================================
