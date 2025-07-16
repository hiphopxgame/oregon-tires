export type Json =
  | string
  | number
  | boolean
  | null
  | { [key: string]: Json | undefined }
  | Json[]

export type Database = {
  // Allows to automatically instanciate createClient with right options
  // instead of createClient<Database, { PostgrestVersion: 'XX' }>(URL, KEY)
  __InternalSupabase: {
    PostgrestVersion: "12.2.3 (519615d)"
  }
  public: {
    Tables: {
      bitcoin_crypto_data: {
        Row: {
          cmc_id: number
          created_at: string
          id: string
          last_updated: string
          logo_url: string | null
          market_cap: number
          name: string
          percent_change_1h: number | null
          percent_change_24h: number | null
          percent_change_30d: number | null
          percent_change_7d: number | null
          price: number
          rank: number
          slug: string
          symbol: string
          updated_at: string
          volume_24h: number
        }
        Insert: {
          cmc_id: number
          created_at?: string
          id?: string
          last_updated: string
          logo_url?: string | null
          market_cap: number
          name: string
          percent_change_1h?: number | null
          percent_change_24h?: number | null
          percent_change_30d?: number | null
          percent_change_7d?: number | null
          price: number
          rank: number
          slug: string
          symbol: string
          updated_at?: string
          volume_24h: number
        }
        Update: {
          cmc_id?: number
          created_at?: string
          id?: string
          last_updated?: string
          logo_url?: string | null
          market_cap?: number
          name?: string
          percent_change_1h?: number | null
          percent_change_24h?: number | null
          percent_change_30d?: number | null
          percent_change_7d?: number | null
          price?: number
          rank?: number
          slug?: string
          symbol?: string
          updated_at?: string
          volume_24h?: number
        }
        Relationships: []
      }
      bitcoin_payments: {
        Row: {
          btc_amount: number
          cash_amount: number
          community_id: string
          created_at: string
          expires_at: string | null
          id: string
          land_id: number
          nowpayments_order_id: string | null
          nowpayments_payment_id: string | null
          payment_address: string | null
          payment_status: string
          updated_at: string
          user_id: string
        }
        Insert: {
          btc_amount: number
          cash_amount: number
          community_id: string
          created_at?: string
          expires_at?: string | null
          id?: string
          land_id: number
          nowpayments_order_id?: string | null
          nowpayments_payment_id?: string | null
          payment_address?: string | null
          payment_status?: string
          updated_at?: string
          user_id: string
        }
        Update: {
          btc_amount?: number
          cash_amount?: number
          community_id?: string
          created_at?: string
          expires_at?: string | null
          id?: string
          land_id?: number
          nowpayments_order_id?: string | null
          nowpayments_payment_id?: string | null
          payment_address?: string | null
          payment_status?: string
          updated_at?: string
          user_id?: string
        }
        Relationships: []
      }
      contact_messages: {
        Row: {
          company: string | null
          created_at: string
          email: string
          id: string
          message: string
          name: string
          phone: string | null
          service_type: string | null
          status: string | null
          updated_at: string
        }
        Insert: {
          company?: string | null
          created_at?: string
          email: string
          id?: string
          message: string
          name: string
          phone?: string | null
          service_type?: string | null
          status?: string | null
          updated_at?: string
        }
        Update: {
          company?: string | null
          created_at?: string
          email?: string
          id?: string
          message?: string
          name?: string
          phone?: string | null
          service_type?: string | null
          status?: string | null
          updated_at?: string
        }
        Relationships: []
      }
      customer_vehicles: {
        Row: {
          created_at: string
          customer_email: string
          customer_name: string
          id: string
          license_plate: string | null
          make: string | null
          model: string | null
          updated_at: string
          vin: string | null
          year: number | null
        }
        Insert: {
          created_at?: string
          customer_email: string
          customer_name: string
          id?: string
          license_plate?: string | null
          make?: string | null
          model?: string | null
          updated_at?: string
          vin?: string | null
          year?: number | null
        }
        Update: {
          created_at?: string
          customer_email?: string
          customer_name?: string
          id?: string
          license_plate?: string | null
          make?: string | null
          model?: string | null
          updated_at?: string
          vin?: string | null
          year?: number | null
        }
        Relationships: []
      }
      dc_ad_stats: {
        Row: {
          ad_id: string
          created_at: string
          event_type: string
          id: string
          ip_address: unknown | null
          user_agent: string | null
          user_id: string | null
        }
        Insert: {
          ad_id: string
          created_at?: string
          event_type: string
          id?: string
          ip_address?: unknown | null
          user_agent?: string | null
          user_id?: string | null
        }
        Update: {
          ad_id?: string
          created_at?: string
          event_type?: string
          id?: string
          ip_address?: unknown | null
          user_agent?: string | null
          user_id?: string | null
        }
        Relationships: [
          {
            foreignKeyName: "dc_ad_stats_ad_id_fkey"
            columns: ["ad_id"]
            isOneToOne: false
            referencedRelation: "dc_advertisements"
            referencedColumns: ["id"]
          },
        ]
      }
      dc_advertisements: {
        Row: {
          cash_spent: number
          created_at: string
          description: string | null
          id: string
          image_url: string | null
          is_active: boolean
          target_url: string
          title: string
          updated_at: string
          user_id: string | null
        }
        Insert: {
          cash_spent?: number
          created_at?: string
          description?: string | null
          id?: string
          image_url?: string | null
          is_active?: boolean
          target_url: string
          title: string
          updated_at?: string
          user_id?: string | null
        }
        Update: {
          cash_spent?: number
          created_at?: string
          description?: string | null
          id?: string
          image_url?: string | null
          is_active?: boolean
          target_url?: string
          title?: string
          updated_at?: string
          user_id?: string | null
        }
        Relationships: []
      }
      dc_business_comments: {
        Row: {
          content: string
          created_at: string
          discussion_id: string
          id: string
          updated_at: string
          user_id: string | null
        }
        Insert: {
          content: string
          created_at?: string
          discussion_id: string
          id?: string
          updated_at?: string
          user_id?: string | null
        }
        Update: {
          content?: string
          created_at?: string
          discussion_id?: string
          id?: string
          updated_at?: string
          user_id?: string | null
        }
        Relationships: [
          {
            foreignKeyName: "dc_business_comments_discussion_id_fkey"
            columns: ["discussion_id"]
            isOneToOne: false
            referencedRelation: "dc_business_discussions"
            referencedColumns: ["id"]
          },
        ]
      }
      dc_business_directory: {
        Row: {
          address: string | null
          business_description: string | null
          business_name: string
          cash_amount: number
          category: string
          city: string | null
          created_at: string
          email: string | null
          id: string
          is_approved: boolean
          is_featured: boolean
          phone: string | null
          state: string | null
          tags: string[] | null
          updated_at: string
          user_id: string | null
          website: string | null
          zip_code: string | null
        }
        Insert: {
          address?: string | null
          business_description?: string | null
          business_name: string
          cash_amount?: number
          category: string
          city?: string | null
          created_at?: string
          email?: string | null
          id?: string
          is_approved?: boolean
          is_featured?: boolean
          phone?: string | null
          state?: string | null
          tags?: string[] | null
          updated_at?: string
          user_id?: string | null
          website?: string | null
          zip_code?: string | null
        }
        Update: {
          address?: string | null
          business_description?: string | null
          business_name?: string
          cash_amount?: number
          category?: string
          city?: string | null
          created_at?: string
          email?: string | null
          id?: string
          is_approved?: boolean
          is_featured?: boolean
          phone?: string | null
          state?: string | null
          tags?: string[] | null
          updated_at?: string
          user_id?: string | null
          website?: string | null
          zip_code?: string | null
        }
        Relationships: []
      }
      dc_business_discussions: {
        Row: {
          category: string
          content: string
          created_at: string
          id: string
          is_pinned: boolean
          tags: string[] | null
          title: string
          updated_at: string
          user_id: string | null
        }
        Insert: {
          category: string
          content: string
          created_at?: string
          id?: string
          is_pinned?: boolean
          tags?: string[] | null
          title: string
          updated_at?: string
          user_id?: string | null
        }
        Update: {
          category?: string
          content?: string
          created_at?: string
          id?: string
          is_pinned?: boolean
          tags?: string[] | null
          title?: string
          updated_at?: string
          user_id?: string | null
        }
        Relationships: []
      }
      dc_business_profiles: {
        Row: {
          avatar_url: string | null
          created_at: string
          full_name: string | null
          id: string
          linkedin_access_token: string | null
          updated_at: string
          user_id: string
          username: string | null
        }
        Insert: {
          avatar_url?: string | null
          created_at?: string
          full_name?: string | null
          id?: string
          linkedin_access_token?: string | null
          updated_at?: string
          user_id: string
          username?: string | null
        }
        Update: {
          avatar_url?: string | null
          created_at?: string
          full_name?: string | null
          id?: string
          linkedin_access_token?: string | null
          updated_at?: string
          user_id?: string
          username?: string | null
        }
        Relationships: []
      }
      dc_credit_purchases: {
        Row: {
          amount_paid: number
          created_at: string
          credits_purchased: number
          currency: string | null
          id: string
          status: string | null
          stripe_session_id: string | null
          updated_at: string
          user_id: string | null
        }
        Insert: {
          amount_paid: number
          created_at?: string
          credits_purchased: number
          currency?: string | null
          id?: string
          status?: string | null
          stripe_session_id?: string | null
          updated_at?: string
          user_id?: string | null
        }
        Update: {
          amount_paid?: number
          created_at?: string
          credits_purchased?: number
          currency?: string | null
          id?: string
          status?: string | null
          stripe_session_id?: string | null
          updated_at?: string
          user_id?: string | null
        }
        Relationships: []
      }
      dc_user_cash: {
        Row: {
          balance: number
          created_at: string
          credits: number | null
          id: string
          updated_at: string
          user_id: string
        }
        Insert: {
          balance?: number
          created_at?: string
          credits?: number | null
          id?: string
          updated_at?: string
          user_id: string
        }
        Update: {
          balance?: number
          created_at?: string
          credits?: number | null
          id?: string
          updated_at?: string
          user_id?: string
        }
        Relationships: []
      }
      hiphop_bingo_game_participants: {
        Row: {
          board_data: Json
          claimed_bingo_at: string | null
          game_id: string
          id: string
          joined_at: string
          marked_positions: Json
          user_id: string
        }
        Insert: {
          board_data: Json
          claimed_bingo_at?: string | null
          game_id: string
          id?: string
          joined_at?: string
          marked_positions?: Json
          user_id: string
        }
        Update: {
          board_data?: Json
          claimed_bingo_at?: string | null
          game_id?: string
          id?: string
          joined_at?: string
          marked_positions?: Json
          user_id?: string
        }
        Relationships: [
          {
            foreignKeyName: "hiphop_bingo_game_participants_game_id_fkey"
            columns: ["game_id"]
            isOneToOne: false
            referencedRelation: "hiphop_bingo_games"
            referencedColumns: ["id"]
          },
        ]
      }
      hiphop_bingo_games: {
        Row: {
          completed_at: string | null
          created_at: string
          current_video_index: number
          host_user_id: string
          id: string
          playlist_id: string
          status: string
          winner_user_id: string | null
        }
        Insert: {
          completed_at?: string | null
          created_at?: string
          current_video_index?: number
          host_user_id: string
          id?: string
          playlist_id: string
          status?: string
          winner_user_id?: string | null
        }
        Update: {
          completed_at?: string | null
          created_at?: string
          current_video_index?: number
          host_user_id?: string
          id?: string
          playlist_id?: string
          status?: string
          winner_user_id?: string | null
        }
        Relationships: [
          {
            foreignKeyName: "hiphop_bingo_games_playlist_id_fkey"
            columns: ["playlist_id"]
            isOneToOne: false
            referencedRelation: "hiphop_bingo_playlists"
            referencedColumns: ["id"]
          },
        ]
      }
      hiphop_bingo_playlist_videos: {
        Row: {
          created_at: string
          id: string
          playlist_id: string
          position: number
          video_id: string
        }
        Insert: {
          created_at?: string
          id?: string
          playlist_id: string
          position: number
          video_id: string
        }
        Update: {
          created_at?: string
          id?: string
          playlist_id?: string
          position?: number
          video_id?: string
        }
        Relationships: [
          {
            foreignKeyName: "hiphop_bingo_playlist_videos_playlist_id_fkey"
            columns: ["playlist_id"]
            isOneToOne: false
            referencedRelation: "hiphop_bingo_playlists"
            referencedColumns: ["id"]
          },
          {
            foreignKeyName: "hiphop_bingo_playlist_videos_video_id_fkey"
            columns: ["video_id"]
            isOneToOne: false
            referencedRelation: "hiphop_bingo_videos"
            referencedColumns: ["id"]
          },
        ]
      }
      hiphop_bingo_playlists: {
        Row: {
          created_at: string
          description: string | null
          id: string
          is_public: boolean
          name: string
          updated_at: string
          user_id: string
          video_count: number
        }
        Insert: {
          created_at?: string
          description?: string | null
          id?: string
          is_public?: boolean
          name: string
          updated_at?: string
          user_id: string
          video_count?: number
        }
        Update: {
          created_at?: string
          description?: string | null
          id?: string
          is_public?: boolean
          name?: string
          updated_at?: string
          user_id?: string
          video_count?: number
        }
        Relationships: []
      }
      hiphop_bingo_profiles: {
        Row: {
          avatar_url: string | null
          created_at: string
          display_name: string | null
          id: string
          is_admin: boolean
          updated_at: string
          user_id: string
          username: string
        }
        Insert: {
          avatar_url?: string | null
          created_at?: string
          display_name?: string | null
          id?: string
          is_admin?: boolean
          updated_at?: string
          user_id: string
          username: string
        }
        Update: {
          avatar_url?: string | null
          created_at?: string
          display_name?: string | null
          id?: string
          is_admin?: boolean
          updated_at?: string
          user_id?: string
          username?: string
        }
        Relationships: []
      }
      hiphop_bingo_videos: {
        Row: {
          approved_by: string | null
          artist: string
          created_at: string
          duration: number | null
          id: string
          status: string
          submitted_by: string | null
          thumbnail_url: string | null
          title: string
          updated_at: string
          youtube_id: string
          youtube_url: string
        }
        Insert: {
          approved_by?: string | null
          artist: string
          created_at?: string
          duration?: number | null
          id?: string
          status?: string
          submitted_by?: string | null
          thumbnail_url?: string | null
          title: string
          updated_at?: string
          youtube_id: string
          youtube_url: string
        }
        Update: {
          approved_by?: string | null
          artist?: string
          created_at?: string
          duration?: number | null
          id?: string
          status?: string
          submitted_by?: string | null
          thumbnail_url?: string | null
          title?: string
          updated_at?: string
          youtube_id?: string
          youtube_url?: string
        }
        Relationships: []
      }
      "hiphop-orders": {
        Row: {
          amount: number
          created_at: string
          currency: string | null
          id: string
          metadata: Json | null
          product_type: string
          quantity: number | null
          status: string | null
          stripe_session_id: string | null
          updated_at: string
          user_id: string | null
        }
        Insert: {
          amount: number
          created_at?: string
          currency?: string | null
          id?: string
          metadata?: Json | null
          product_type: string
          quantity?: number | null
          status?: string | null
          stripe_session_id?: string | null
          updated_at?: string
          user_id?: string | null
        }
        Update: {
          amount?: number
          created_at?: string
          currency?: string | null
          id?: string
          metadata?: Json | null
          product_type?: string
          quantity?: number | null
          status?: string | null
          stripe_session_id?: string | null
          updated_at?: string
          user_id?: string | null
        }
        Relationships: []
      }
      "hiphop-profiles": {
        Row: {
          avatar_url: string | null
          created_at: string
          display_name: string | null
          hip_hop_cards_owned: number | null
          hip_hop_cash_balance: number | null
          hip_hop_land_owned: number | null
          id: string
          updated_at: string
          username: string | null
        }
        Insert: {
          avatar_url?: string | null
          created_at?: string
          display_name?: string | null
          hip_hop_cards_owned?: number | null
          hip_hop_cash_balance?: number | null
          hip_hop_land_owned?: number | null
          id: string
          updated_at?: string
          username?: string | null
        }
        Update: {
          avatar_url?: string | null
          created_at?: string
          display_name?: string | null
          hip_hop_cards_owned?: number | null
          hip_hop_cash_balance?: number | null
          hip_hop_land_owned?: number | null
          id?: string
          updated_at?: string
          username?: string | null
        }
        Relationships: []
      }
      "hiphop-subscribers": {
        Row: {
          created_at: string
          email: string
          id: string
          stripe_customer_id: string | null
          subscribed: boolean
          subscription_end: string | null
          subscription_tier: string | null
          updated_at: string
          user_id: string | null
        }
        Insert: {
          created_at?: string
          email: string
          id?: string
          stripe_customer_id?: string | null
          subscribed?: boolean
          subscription_end?: string | null
          subscription_tier?: string | null
          updated_at?: string
          user_id?: string | null
        }
        Update: {
          created_at?: string
          email?: string
          id?: string
          stripe_customer_id?: string | null
          subscribed?: boolean
          subscription_end?: string | null
          subscription_tier?: string | null
          updated_at?: string
          user_id?: string | null
        }
        Relationships: []
      }
      hiphopworld_card_collections: {
        Row: {
          card_id: string
          collected_at: string
          collector_user_id: string
          id: string
          individual_balance: number
        }
        Insert: {
          card_id: string
          collected_at?: string
          collector_user_id: string
          id?: string
          individual_balance?: number
        }
        Update: {
          card_id?: string
          collected_at?: string
          collector_user_id?: string
          id?: string
          individual_balance?: number
        }
        Relationships: [
          {
            foreignKeyName: "hiphopworld_card_collections_card_id_fkey"
            columns: ["card_id"]
            isOneToOne: false
            referencedRelation: "hiphopworld_cards"
            referencedColumns: ["id"]
          },
          {
            foreignKeyName: "hiphopworld_card_collections_collector_user_id_fkey"
            columns: ["collector_user_id"]
            isOneToOne: false
            referencedRelation: "hiphopworld_profiles"
            referencedColumns: ["user_id"]
          },
        ]
      }
      hiphopworld_cards: {
        Row: {
          card_type: string
          content_url: string | null
          created_at: string
          description: string | null
          id: string
          individual_balance: number
          is_public: boolean
          title: string
          total_balance: number
          updated_at: string
          user_id: string
        }
        Insert: {
          card_type: string
          content_url?: string | null
          created_at?: string
          description?: string | null
          id?: string
          individual_balance?: number
          is_public?: boolean
          title: string
          total_balance?: number
          updated_at?: string
          user_id: string
        }
        Update: {
          card_type?: string
          content_url?: string | null
          created_at?: string
          description?: string | null
          id?: string
          individual_balance?: number
          is_public?: boolean
          title?: string
          total_balance?: number
          updated_at?: string
          user_id?: string
        }
        Relationships: [
          {
            foreignKeyName: "hiphopworld_cards_user_id_fkey"
            columns: ["user_id"]
            isOneToOne: false
            referencedRelation: "hiphopworld_profiles"
            referencedColumns: ["user_id"]
          },
        ]
      }
      hiphopworld_communities: {
        Row: {
          community_id: number
          created_at: string
          id: string
          land_id: number
          owner_user_id: string | null
          price: number
          purchased_at: string | null
          updated_at: string
        }
        Insert: {
          community_id: number
          created_at?: string
          id?: string
          land_id: number
          owner_user_id?: string | null
          price: number
          purchased_at?: string | null
          updated_at?: string
        }
        Update: {
          community_id?: number
          created_at?: string
          id?: string
          land_id?: number
          owner_user_id?: string | null
          price?: number
          purchased_at?: string | null
          updated_at?: string
        }
        Relationships: [
          {
            foreignKeyName: "hiphopworld_communities_owner_user_id_fkey"
            columns: ["owner_user_id"]
            isOneToOne: false
            referencedRelation: "hiphopworld_profiles"
            referencedColumns: ["user_id"]
          },
        ]
      }
      hiphopworld_profiles: {
        Row: {
          avatar_url: string | null
          bio: string | null
          communities_owned: number
          created_at: string
          display_name: string | null
          hip_hop_cards_owned: number
          hip_hop_cash_balance: number
          id: string
          updated_at: string
          user_id: string
          username: string | null
        }
        Insert: {
          avatar_url?: string | null
          bio?: string | null
          communities_owned?: number
          created_at?: string
          display_name?: string | null
          hip_hop_cards_owned?: number
          hip_hop_cash_balance?: number
          id?: string
          updated_at?: string
          user_id: string
          username?: string | null
        }
        Update: {
          avatar_url?: string | null
          bio?: string | null
          communities_owned?: number
          created_at?: string
          display_name?: string | null
          hip_hop_cards_owned?: number
          hip_hop_cash_balance?: number
          id?: string
          updated_at?: string
          user_id?: string
          username?: string | null
        }
        Relationships: []
      }
      oregon_tires_appointments: {
        Row: {
          actual_duration_minutes: number | null
          assigned_employee_id: string | null
          completed_at: string | null
          created_at: string
          customer_address: string | null
          customer_city: string | null
          customer_state: string | null
          customer_zip: string | null
          email: string
          first_name: string
          id: string
          language: string
          last_name: string
          license_plate: string | null
          message: string | null
          phone: string | null
          preferred_date: string
          preferred_time: string
          service: string
          service_location: string | null
          started_at: string | null
          status: string
          tire_size: string | null
          travel_cost_estimate: number | null
          travel_distance_miles: number | null
          updated_at: string
          vehicle_id: string | null
          vin: string | null
        }
        Insert: {
          actual_duration_minutes?: number | null
          assigned_employee_id?: string | null
          completed_at?: string | null
          created_at?: string
          customer_address?: string | null
          customer_city?: string | null
          customer_state?: string | null
          customer_zip?: string | null
          email: string
          first_name: string
          id?: string
          language?: string
          last_name: string
          license_plate?: string | null
          message?: string | null
          phone?: string | null
          preferred_date: string
          preferred_time: string
          service: string
          service_location?: string | null
          started_at?: string | null
          status?: string
          tire_size?: string | null
          travel_cost_estimate?: number | null
          travel_distance_miles?: number | null
          updated_at?: string
          vehicle_id?: string | null
          vin?: string | null
        }
        Update: {
          actual_duration_minutes?: number | null
          assigned_employee_id?: string | null
          completed_at?: string | null
          created_at?: string
          customer_address?: string | null
          customer_city?: string | null
          customer_state?: string | null
          customer_zip?: string | null
          email?: string
          first_name?: string
          id?: string
          language?: string
          last_name?: string
          license_plate?: string | null
          message?: string | null
          phone?: string | null
          preferred_date?: string
          preferred_time?: string
          service?: string
          service_location?: string | null
          started_at?: string | null
          status?: string
          tire_size?: string | null
          travel_cost_estimate?: number | null
          travel_distance_miles?: number | null
          updated_at?: string
          vehicle_id?: string | null
          vin?: string | null
        }
        Relationships: [
          {
            foreignKeyName: "oregon_tires_appointments_assigned_employee_id_fkey"
            columns: ["assigned_employee_id"]
            isOneToOne: false
            referencedRelation: "oregon_tires_employees"
            referencedColumns: ["id"]
          },
          {
            foreignKeyName: "oregon_tires_appointments_vehicle_id_fkey"
            columns: ["vehicle_id"]
            isOneToOne: false
            referencedRelation: "customer_vehicles"
            referencedColumns: ["id"]
          },
        ]
      }
      oregon_tires_contact_messages: {
        Row: {
          created_at: string
          email: string
          first_name: string
          id: string
          language: string
          last_name: string
          message: string
          phone: string | null
          status: string
          updated_at: string
        }
        Insert: {
          created_at?: string
          email: string
          first_name: string
          id?: string
          language?: string
          last_name: string
          message: string
          phone?: string | null
          status?: string
          updated_at?: string
        }
        Update: {
          created_at?: string
          email?: string
          first_name?: string
          id?: string
          language?: string
          last_name?: string
          message?: string
          phone?: string | null
          status?: string
          updated_at?: string
        }
        Relationships: []
      }
      oregon_tires_custom_hours: {
        Row: {
          closing_time: string | null
          created_at: string
          date: string
          id: string
          is_closed: boolean
          opening_time: string | null
          simultaneous_bookings: number | null
          updated_at: string
        }
        Insert: {
          closing_time?: string | null
          created_at?: string
          date: string
          id?: string
          is_closed?: boolean
          opening_time?: string | null
          simultaneous_bookings?: number | null
          updated_at?: string
        }
        Update: {
          closing_time?: string | null
          created_at?: string
          date?: string
          id?: string
          is_closed?: boolean
          opening_time?: string | null
          simultaneous_bookings?: number | null
          updated_at?: string
        }
        Relationships: []
      }
      oregon_tires_email_logs: {
        Row: {
          appointment_id: string | null
          body: string
          created_at: string
          email_type: string
          id: string
          recipient_email: string
          recipient_name: string
          recipient_type: string
          resend_message_id: string | null
          sent_at: string
          subject: string
        }
        Insert: {
          appointment_id?: string | null
          body: string
          created_at?: string
          email_type: string
          id?: string
          recipient_email: string
          recipient_name: string
          recipient_type: string
          resend_message_id?: string | null
          sent_at?: string
          subject: string
        }
        Update: {
          appointment_id?: string | null
          body?: string
          created_at?: string
          email_type?: string
          id?: string
          recipient_email?: string
          recipient_name?: string
          recipient_type?: string
          resend_message_id?: string | null
          sent_at?: string
          subject?: string
        }
        Relationships: [
          {
            foreignKeyName: "oregon_tires_email_logs_appointment_id_fkey"
            columns: ["appointment_id"]
            isOneToOne: false
            referencedRelation: "oregon_tires_appointments"
            referencedColumns: ["id"]
          },
        ]
      }
      oregon_tires_employee_schedules: {
        Row: {
          created_at: string
          employee_id: string
          end_time: string
          id: string
          is_available: boolean
          start_time: string
          updated_at: string
          work_date: string
        }
        Insert: {
          created_at?: string
          employee_id: string
          end_time?: string
          id?: string
          is_available?: boolean
          start_time?: string
          updated_at?: string
          work_date: string
        }
        Update: {
          created_at?: string
          employee_id?: string
          end_time?: string
          id?: string
          is_available?: boolean
          start_time?: string
          updated_at?: string
          work_date?: string
        }
        Relationships: [
          {
            foreignKeyName: "oregon_tires_employee_schedules_employee_id_fkey"
            columns: ["employee_id"]
            isOneToOne: false
            referencedRelation: "oregon_tires_employees"
            referencedColumns: ["id"]
          },
        ]
      }
      oregon_tires_employees: {
        Row: {
          created_at: string
          email: string | null
          id: string
          is_active: boolean
          name: string
          phone: string | null
          updated_at: string
        }
        Insert: {
          created_at?: string
          email?: string | null
          id?: string
          is_active?: boolean
          name: string
          phone?: string | null
          updated_at?: string
        }
        Update: {
          created_at?: string
          email?: string | null
          id?: string
          is_active?: boolean
          name?: string
          phone?: string | null
          updated_at?: string
        }
        Relationships: []
      }
      oregon_tires_gallery_images: {
        Row: {
          created_at: string
          description: string | null
          display_order: number | null
          id: string
          image_url: string
          is_active: boolean
          language: string
          title: string
          updated_at: string
        }
        Insert: {
          created_at?: string
          description?: string | null
          display_order?: number | null
          id?: string
          image_url: string
          is_active?: boolean
          language?: string
          title: string
          updated_at?: string
        }
        Update: {
          created_at?: string
          description?: string | null
          display_order?: number | null
          id?: string
          image_url?: string
          is_active?: boolean
          language?: string
          title?: string
          updated_at?: string
        }
        Relationships: []
      }
      oretir_admin_notifications: {
        Row: {
          appointment_id: string | null
          created_at: string
          id: string
          message: string
          read: boolean
          title: string
          type: string
        }
        Insert: {
          appointment_id?: string | null
          created_at?: string
          id?: string
          message: string
          read?: boolean
          title: string
          type: string
        }
        Update: {
          appointment_id?: string | null
          created_at?: string
          id?: string
          message?: string
          read?: boolean
          title?: string
          type?: string
        }
        Relationships: [
          {
            foreignKeyName: "oretir_admin_notifications_appointment_id_fkey"
            columns: ["appointment_id"]
            isOneToOne: false
            referencedRelation: "oretir_appointments"
            referencedColumns: ["id"]
          },
        ]
      }
      oretir_appointments: {
        Row: {
          appointment_date: string
          approved_at: string | null
          approved_by: string | null
          created_at: string
          customer_email: string
          customer_name: string
          customer_phone: string | null
          duration_minutes: number
          end_time: string
          id: string
          message: string | null
          service_type: string
          start_time: string
          station_number: number
          status: string
          updated_at: string
        }
        Insert: {
          appointment_date: string
          approved_at?: string | null
          approved_by?: string | null
          created_at?: string
          customer_email: string
          customer_name: string
          customer_phone?: string | null
          duration_minutes: number
          end_time: string
          id?: string
          message?: string | null
          service_type: string
          start_time: string
          station_number: number
          status?: string
          updated_at?: string
        }
        Update: {
          appointment_date?: string
          approved_at?: string | null
          approved_by?: string | null
          created_at?: string
          customer_email?: string
          customer_name?: string
          customer_phone?: string | null
          duration_minutes?: number
          end_time?: string
          id?: string
          message?: string | null
          service_type?: string
          start_time?: string
          station_number?: number
          status?: string
          updated_at?: string
        }
        Relationships: []
      }
      oretir_contact_messages: {
        Row: {
          created_at: string
          email: string
          id: string
          message: string
          name: string
          phone: string | null
          subject: string | null
        }
        Insert: {
          created_at?: string
          email: string
          id?: string
          message: string
          name: string
          phone?: string | null
          subject?: string | null
        }
        Update: {
          created_at?: string
          email?: string
          id?: string
          message?: string
          name?: string
          phone?: string | null
          subject?: string | null
        }
        Relationships: []
      }
      oretir_profiles: {
        Row: {
          created_at: string
          id: string
          is_admin: boolean
          updated_at: string
        }
        Insert: {
          created_at?: string
          id: string
          is_admin?: boolean
          updated_at?: string
        }
        Update: {
          created_at?: string
          id?: string
          is_admin?: boolean
          updated_at?: string
        }
        Relationships: []
      }
      oretir_settings: {
        Row: {
          created_at: string
          id: string
          setting_key: string
          setting_value: string
          updated_at: string
        }
        Insert: {
          created_at?: string
          id?: string
          setting_key: string
          setting_value: string
          updated_at?: string
        }
        Update: {
          created_at?: string
          id?: string
          setting_key?: string
          setting_value?: string
          updated_at?: string
        }
        Relationships: []
      }
      payments: {
        Row: {
          amount: number
          created_at: string
          currency: string | null
          customer_email: string
          customer_name: string
          id: string
          service_type: string | null
          status: string | null
          stripe_payment_intent_id: string | null
          stripe_session_id: string | null
          updated_at: string
        }
        Insert: {
          amount: number
          created_at?: string
          currency?: string | null
          customer_email: string
          customer_name: string
          id?: string
          service_type?: string | null
          status?: string | null
          stripe_payment_intent_id?: string | null
          stripe_session_id?: string | null
          updated_at?: string
        }
        Update: {
          amount?: number
          created_at?: string
          currency?: string | null
          customer_email?: string
          customer_name?: string
          id?: string
          service_type?: string | null
          status?: string | null
          stripe_payment_intent_id?: string | null
          stripe_session_id?: string | null
          updated_at?: string
        }
        Relationships: []
      }
      por_eve_profiles: {
        Row: {
          avatar_url: string | null
          created_at: string
          display_name: string | null
          email: string
          facebook_url: string | null
          full_name: string | null
          id: string
          instagram_url: string | null
          is_email_public: boolean | null
          twitter_url: string | null
          updated_at: string
          username: string | null
          website_url: string | null
          youtube_url: string | null
        }
        Insert: {
          avatar_url?: string | null
          created_at?: string
          display_name?: string | null
          email: string
          facebook_url?: string | null
          full_name?: string | null
          id: string
          instagram_url?: string | null
          is_email_public?: boolean | null
          twitter_url?: string | null
          updated_at?: string
          username?: string | null
          website_url?: string | null
          youtube_url?: string | null
        }
        Update: {
          avatar_url?: string | null
          created_at?: string
          display_name?: string | null
          email?: string
          facebook_url?: string | null
          full_name?: string | null
          id?: string
          instagram_url?: string | null
          is_email_public?: boolean | null
          twitter_url?: string | null
          updated_at?: string
          username?: string | null
          website_url?: string | null
          youtube_url?: string | null
        }
        Relationships: []
      }
      poreve_api_sync_log: {
        Row: {
          api_source: string
          completed_at: string | null
          error_message: string | null
          events_added: number | null
          events_processed: number | null
          events_updated: number | null
          id: string
          started_at: string
          status: string
          sync_type: string
        }
        Insert: {
          api_source: string
          completed_at?: string | null
          error_message?: string | null
          events_added?: number | null
          events_processed?: number | null
          events_updated?: number | null
          id?: string
          started_at?: string
          status?: string
          sync_type: string
        }
        Update: {
          api_source?: string
          completed_at?: string | null
          error_message?: string | null
          events_added?: number | null
          events_processed?: number | null
          events_updated?: number | null
          id?: string
          started_at?: string
          status?: string
          sync_type?: string
        }
        Relationships: []
      }
      poreve_categories: {
        Row: {
          color: string | null
          created_at: string
          description: string | null
          icon: string | null
          id: string
          is_active: boolean
          name: string
          slug: string
        }
        Insert: {
          color?: string | null
          created_at?: string
          description?: string | null
          icon?: string | null
          id?: string
          is_active?: boolean
          name: string
          slug: string
        }
        Update: {
          color?: string | null
          created_at?: string
          description?: string | null
          icon?: string | null
          id?: string
          is_active?: boolean
          name?: string
          slug?: string
        }
        Relationships: []
      }
      poreve_events: {
        Row: {
          api_source: string
          category: string
          created_at: string
          description: string | null
          end_date: string | null
          external_id: string
          facebook_url: string | null
          id: string
          image_url: string | null
          instagram_url: string | null
          is_active: boolean
          is_featured: boolean
          organizer_name: string | null
          organizer_url: string | null
          price_display: string | null
          price_max: number | null
          price_min: number | null
          start_date: string
          tags: string[] | null
          ticket_url: string | null
          title: string
          twitter_url: string | null
          updated_at: string
          venue_address: string | null
          venue_city: string | null
          venue_name: string
          venue_state: string | null
          venue_zip: string | null
          website_url: string | null
          youtube_url: string | null
        }
        Insert: {
          api_source: string
          category: string
          created_at?: string
          description?: string | null
          end_date?: string | null
          external_id: string
          facebook_url?: string | null
          id?: string
          image_url?: string | null
          instagram_url?: string | null
          is_active?: boolean
          is_featured?: boolean
          organizer_name?: string | null
          organizer_url?: string | null
          price_display?: string | null
          price_max?: number | null
          price_min?: number | null
          start_date: string
          tags?: string[] | null
          ticket_url?: string | null
          title: string
          twitter_url?: string | null
          updated_at?: string
          venue_address?: string | null
          venue_city?: string | null
          venue_name: string
          venue_state?: string | null
          venue_zip?: string | null
          website_url?: string | null
          youtube_url?: string | null
        }
        Update: {
          api_source?: string
          category?: string
          created_at?: string
          description?: string | null
          end_date?: string | null
          external_id?: string
          facebook_url?: string | null
          id?: string
          image_url?: string | null
          instagram_url?: string | null
          is_active?: boolean
          is_featured?: boolean
          organizer_name?: string | null
          organizer_url?: string | null
          price_display?: string | null
          price_max?: number | null
          price_min?: number | null
          start_date?: string
          tags?: string[] | null
          ticket_url?: string | null
          title?: string
          twitter_url?: string | null
          updated_at?: string
          venue_address?: string | null
          venue_city?: string | null
          venue_name?: string
          venue_state?: string | null
          venue_zip?: string | null
          website_url?: string | null
          youtube_url?: string | null
        }
        Relationships: []
      }
      poreve_venues: {
        Row: {
          address: string | null
          capacity: number | null
          city: string | null
          created_at: string
          facebook_url: string | null
          id: string
          instagram_url: string | null
          latitude: number | null
          longitude: number | null
          name: string
          phone: string | null
          state: string | null
          twitter_url: string | null
          updated_at: string
          venue_type: string | null
          website: string | null
          youtube_url: string | null
          zip_code: string | null
        }
        Insert: {
          address?: string | null
          capacity?: number | null
          city?: string | null
          created_at?: string
          facebook_url?: string | null
          id?: string
          instagram_url?: string | null
          latitude?: number | null
          longitude?: number | null
          name: string
          phone?: string | null
          state?: string | null
          twitter_url?: string | null
          updated_at?: string
          venue_type?: string | null
          website?: string | null
          youtube_url?: string | null
          zip_code?: string | null
        }
        Update: {
          address?: string | null
          capacity?: number | null
          city?: string | null
          created_at?: string
          facebook_url?: string | null
          id?: string
          instagram_url?: string | null
          latitude?: number | null
          longitude?: number | null
          name?: string
          phone?: string | null
          state?: string | null
          twitter_url?: string | null
          updated_at?: string
          venue_type?: string | null
          website?: string | null
          youtube_url?: string | null
          zip_code?: string | null
        }
        Relationships: []
      }
      pormar_consultation_requests: {
        Row: {
          additional_guests: number | null
          budget_range: string | null
          company: string | null
          consultation_type: string | null
          created_at: string
          current_challenges: string | null
          email: string
          id: string
          location_preference: string | null
          message: string | null
          name: string
          phone: string | null
          preferred_meeting_time: string | null
          primary_goals: string | null
          status: string | null
          updated_at: string
          wants_consultation: boolean | null
          website_url: string | null
        }
        Insert: {
          additional_guests?: number | null
          budget_range?: string | null
          company?: string | null
          consultation_type?: string | null
          created_at?: string
          current_challenges?: string | null
          email: string
          id?: string
          location_preference?: string | null
          message?: string | null
          name: string
          phone?: string | null
          preferred_meeting_time?: string | null
          primary_goals?: string | null
          status?: string | null
          updated_at?: string
          wants_consultation?: boolean | null
          website_url?: string | null
        }
        Update: {
          additional_guests?: number | null
          budget_range?: string | null
          company?: string | null
          consultation_type?: string | null
          created_at?: string
          current_challenges?: string | null
          email?: string
          id?: string
          location_preference?: string | null
          message?: string | null
          name?: string
          phone?: string | null
          preferred_meeting_time?: string | null
          primary_goals?: string | null
          status?: string | null
          updated_at?: string
          wants_consultation?: boolean | null
          website_url?: string | null
        }
        Relationships: []
      }
      user_events: {
        Row: {
          category: string
          created_at: string
          created_by: string | null
          description: string | null
          end_time: string | null
          facebook_url: string | null
          id: string
          image_url: string | null
          instagram_url: string | null
          is_featured: boolean
          is_recurring: boolean | null
          organizer_email: string | null
          organizer_name: string | null
          organizer_phone: string | null
          price_display: string | null
          price_max: number | null
          price_min: number | null
          recurrence_end_date: string | null
          recurrence_pattern: string | null
          recurrence_type: string | null
          start_date: string
          start_time: string | null
          status: string | null
          ticket_url: string | null
          title: string
          twitter_url: string | null
          updated_at: string
          venue_address: string | null
          venue_city: string | null
          venue_name: string
          venue_state: string | null
          venue_zip: string | null
          website_url: string | null
          youtube_url: string | null
        }
        Insert: {
          category: string
          created_at?: string
          created_by?: string | null
          description?: string | null
          end_time?: string | null
          facebook_url?: string | null
          id?: string
          image_url?: string | null
          instagram_url?: string | null
          is_featured?: boolean
          is_recurring?: boolean | null
          organizer_email?: string | null
          organizer_name?: string | null
          organizer_phone?: string | null
          price_display?: string | null
          price_max?: number | null
          price_min?: number | null
          recurrence_end_date?: string | null
          recurrence_pattern?: string | null
          recurrence_type?: string | null
          start_date: string
          start_time?: string | null
          status?: string | null
          ticket_url?: string | null
          title: string
          twitter_url?: string | null
          updated_at?: string
          venue_address?: string | null
          venue_city?: string | null
          venue_name: string
          venue_state?: string | null
          venue_zip?: string | null
          website_url?: string | null
          youtube_url?: string | null
        }
        Update: {
          category?: string
          created_at?: string
          created_by?: string | null
          description?: string | null
          end_time?: string | null
          facebook_url?: string | null
          id?: string
          image_url?: string | null
          instagram_url?: string | null
          is_featured?: boolean
          is_recurring?: boolean | null
          organizer_email?: string | null
          organizer_name?: string | null
          organizer_phone?: string | null
          price_display?: string | null
          price_max?: number | null
          price_min?: number | null
          recurrence_end_date?: string | null
          recurrence_pattern?: string | null
          recurrence_type?: string | null
          start_date?: string
          start_time?: string | null
          status?: string | null
          ticket_url?: string | null
          title?: string
          twitter_url?: string | null
          updated_at?: string
          venue_address?: string | null
          venue_city?: string | null
          venue_name?: string
          venue_state?: string | null
          venue_zip?: string | null
          website_url?: string | null
          youtube_url?: string | null
        }
        Relationships: [
          {
            foreignKeyName: "fk_user_events_created_by"
            columns: ["created_by"]
            isOneToOne: false
            referencedRelation: "por_eve_profiles"
            referencedColumns: ["id"]
          },
        ]
      }
      user_roles: {
        Row: {
          created_at: string
          id: string
          role: Database["public"]["Enums"]["app_role"]
          updated_at: string
          user_id: string
        }
        Insert: {
          created_at?: string
          id?: string
          role: Database["public"]["Enums"]["app_role"]
          updated_at?: string
          user_id: string
        }
        Update: {
          created_at?: string
          id?: string
          role?: Database["public"]["Enums"]["app_role"]
          updated_at?: string
          user_id?: string
        }
        Relationships: []
      }
      venues: {
        Row: {
          address: string | null
          approved_at: string | null
          approved_by: string | null
          city: string | null
          created_at: string
          facebook_url: string | null
          google_photos: string[] | null
          google_place_id: string | null
          google_rating: number | null
          google_review_count: number | null
          id: string
          instagram_url: string | null
          latitude: number | null
          longitude: number | null
          name: string
          phone: string | null
          state: string | null
          status: string | null
          twitter_url: string | null
          updated_at: string
          website: string | null
          youtube_url: string | null
          zip_code: string | null
        }
        Insert: {
          address?: string | null
          approved_at?: string | null
          approved_by?: string | null
          city?: string | null
          created_at?: string
          facebook_url?: string | null
          google_photos?: string[] | null
          google_place_id?: string | null
          google_rating?: number | null
          google_review_count?: number | null
          id?: string
          instagram_url?: string | null
          latitude?: number | null
          longitude?: number | null
          name: string
          phone?: string | null
          state?: string | null
          status?: string | null
          twitter_url?: string | null
          updated_at?: string
          website?: string | null
          youtube_url?: string | null
          zip_code?: string | null
        }
        Update: {
          address?: string | null
          approved_at?: string | null
          approved_by?: string | null
          city?: string | null
          created_at?: string
          facebook_url?: string | null
          google_photos?: string[] | null
          google_place_id?: string | null
          google_rating?: number | null
          google_review_count?: number | null
          id?: string
          instagram_url?: string | null
          latitude?: number | null
          longitude?: number | null
          name?: string
          phone?: string | null
          state?: string | null
          status?: string | null
          twitter_url?: string | null
          updated_at?: string
          website?: string | null
          youtube_url?: string | null
          zip_code?: string | null
        }
        Relationships: []
      }
    }
    Views: {
      [_ in never]: never
    }
    Functions: {
      create_admin_user: {
        Args: Record<PropertyKey, never>
        Returns: undefined
      }
      handle_community_purchase: {
        Args: { p_user_id: string; p_cash_amount: number }
        Returns: undefined
      }
      has_role: {
        Args: {
          _user_id: string
          _role: Database["public"]["Enums"]["app_role"]
        }
        Returns: boolean
      }
      is_current_user_admin: {
        Args: Record<PropertyKey, never>
        Returns: boolean
      }
      is_hiphop_admin: {
        Args: Record<PropertyKey, never>
        Returns: boolean
      }
      set_admin_by_email: {
        Args: { user_email: string }
        Returns: undefined
      }
      setup_admin_user: {
        Args: Record<PropertyKey, never>
        Returns: undefined
      }
    }
    Enums: {
      app_role: "admin" | "moderator" | "user"
    }
    CompositeTypes: {
      [_ in never]: never
    }
  }
}

type DatabaseWithoutInternals = Omit<Database, "__InternalSupabase">

type DefaultSchema = DatabaseWithoutInternals[Extract<keyof Database, "public">]

export type Tables<
  DefaultSchemaTableNameOrOptions extends
    | keyof (DefaultSchema["Tables"] & DefaultSchema["Views"])
    | { schema: keyof DatabaseWithoutInternals },
  TableName extends DefaultSchemaTableNameOrOptions extends {
    schema: keyof DatabaseWithoutInternals
  }
    ? keyof (DatabaseWithoutInternals[DefaultSchemaTableNameOrOptions["schema"]]["Tables"] &
        DatabaseWithoutInternals[DefaultSchemaTableNameOrOptions["schema"]]["Views"])
    : never = never,
> = DefaultSchemaTableNameOrOptions extends {
  schema: keyof DatabaseWithoutInternals
}
  ? (DatabaseWithoutInternals[DefaultSchemaTableNameOrOptions["schema"]]["Tables"] &
      DatabaseWithoutInternals[DefaultSchemaTableNameOrOptions["schema"]]["Views"])[TableName] extends {
      Row: infer R
    }
    ? R
    : never
  : DefaultSchemaTableNameOrOptions extends keyof (DefaultSchema["Tables"] &
        DefaultSchema["Views"])
    ? (DefaultSchema["Tables"] &
        DefaultSchema["Views"])[DefaultSchemaTableNameOrOptions] extends {
        Row: infer R
      }
      ? R
      : never
    : never

export type TablesInsert<
  DefaultSchemaTableNameOrOptions extends
    | keyof DefaultSchema["Tables"]
    | { schema: keyof DatabaseWithoutInternals },
  TableName extends DefaultSchemaTableNameOrOptions extends {
    schema: keyof DatabaseWithoutInternals
  }
    ? keyof DatabaseWithoutInternals[DefaultSchemaTableNameOrOptions["schema"]]["Tables"]
    : never = never,
> = DefaultSchemaTableNameOrOptions extends {
  schema: keyof DatabaseWithoutInternals
}
  ? DatabaseWithoutInternals[DefaultSchemaTableNameOrOptions["schema"]]["Tables"][TableName] extends {
      Insert: infer I
    }
    ? I
    : never
  : DefaultSchemaTableNameOrOptions extends keyof DefaultSchema["Tables"]
    ? DefaultSchema["Tables"][DefaultSchemaTableNameOrOptions] extends {
        Insert: infer I
      }
      ? I
      : never
    : never

export type TablesUpdate<
  DefaultSchemaTableNameOrOptions extends
    | keyof DefaultSchema["Tables"]
    | { schema: keyof DatabaseWithoutInternals },
  TableName extends DefaultSchemaTableNameOrOptions extends {
    schema: keyof DatabaseWithoutInternals
  }
    ? keyof DatabaseWithoutInternals[DefaultSchemaTableNameOrOptions["schema"]]["Tables"]
    : never = never,
> = DefaultSchemaTableNameOrOptions extends {
  schema: keyof DatabaseWithoutInternals
}
  ? DatabaseWithoutInternals[DefaultSchemaTableNameOrOptions["schema"]]["Tables"][TableName] extends {
      Update: infer U
    }
    ? U
    : never
  : DefaultSchemaTableNameOrOptions extends keyof DefaultSchema["Tables"]
    ? DefaultSchema["Tables"][DefaultSchemaTableNameOrOptions] extends {
        Update: infer U
      }
      ? U
      : never
    : never

export type Enums<
  DefaultSchemaEnumNameOrOptions extends
    | keyof DefaultSchema["Enums"]
    | { schema: keyof DatabaseWithoutInternals },
  EnumName extends DefaultSchemaEnumNameOrOptions extends {
    schema: keyof DatabaseWithoutInternals
  }
    ? keyof DatabaseWithoutInternals[DefaultSchemaEnumNameOrOptions["schema"]]["Enums"]
    : never = never,
> = DefaultSchemaEnumNameOrOptions extends {
  schema: keyof DatabaseWithoutInternals
}
  ? DatabaseWithoutInternals[DefaultSchemaEnumNameOrOptions["schema"]]["Enums"][EnumName]
  : DefaultSchemaEnumNameOrOptions extends keyof DefaultSchema["Enums"]
    ? DefaultSchema["Enums"][DefaultSchemaEnumNameOrOptions]
    : never

export type CompositeTypes<
  PublicCompositeTypeNameOrOptions extends
    | keyof DefaultSchema["CompositeTypes"]
    | { schema: keyof DatabaseWithoutInternals },
  CompositeTypeName extends PublicCompositeTypeNameOrOptions extends {
    schema: keyof DatabaseWithoutInternals
  }
    ? keyof DatabaseWithoutInternals[PublicCompositeTypeNameOrOptions["schema"]]["CompositeTypes"]
    : never = never,
> = PublicCompositeTypeNameOrOptions extends {
  schema: keyof DatabaseWithoutInternals
}
  ? DatabaseWithoutInternals[PublicCompositeTypeNameOrOptions["schema"]]["CompositeTypes"][CompositeTypeName]
  : PublicCompositeTypeNameOrOptions extends keyof DefaultSchema["CompositeTypes"]
    ? DefaultSchema["CompositeTypes"][PublicCompositeTypeNameOrOptions]
    : never

export const Constants = {
  public: {
    Enums: {
      app_role: ["admin", "moderator", "user"],
    },
  },
} as const
