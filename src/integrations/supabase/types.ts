export type Json =
  | string
  | number
  | boolean
  | null
  | { [key: string]: Json | undefined }
  | Json[]

export type Database = {
  public: {
    Tables: {
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
      oregon_tires_appointments: {
        Row: {
          created_at: string
          email: string
          first_name: string
          id: string
          language: string
          last_name: string
          message: string | null
          phone: string | null
          preferred_date: string
          preferred_time: string
          service: string
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
          message?: string | null
          phone?: string | null
          preferred_date: string
          preferred_time: string
          service: string
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
          message?: string | null
          phone?: string | null
          preferred_date?: string
          preferred_time?: string
          service?: string
          status?: string
          updated_at?: string
        }
        Relationships: []
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
      por_eve_profiles: {
        Row: {
          avatar_url: string | null
          created_at: string
          email: string
          full_name: string | null
          id: string
          updated_at: string
          username: string | null
        }
        Insert: {
          avatar_url?: string | null
          created_at?: string
          email: string
          full_name?: string | null
          id: string
          updated_at?: string
          username?: string | null
        }
        Update: {
          avatar_url?: string | null
          created_at?: string
          email?: string
          full_name?: string | null
          id?: string
          updated_at?: string
          username?: string | null
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
          id: string
          image_url: string | null
          is_active: boolean
          organizer_name: string | null
          organizer_url: string | null
          price_display: string | null
          price_max: number | null
          price_min: number | null
          start_date: string
          tags: string[] | null
          ticket_url: string | null
          title: string
          updated_at: string
          venue_address: string | null
          venue_city: string | null
          venue_name: string
          venue_state: string | null
          venue_zip: string | null
        }
        Insert: {
          api_source: string
          category: string
          created_at?: string
          description?: string | null
          end_date?: string | null
          external_id: string
          id?: string
          image_url?: string | null
          is_active?: boolean
          organizer_name?: string | null
          organizer_url?: string | null
          price_display?: string | null
          price_max?: number | null
          price_min?: number | null
          start_date: string
          tags?: string[] | null
          ticket_url?: string | null
          title: string
          updated_at?: string
          venue_address?: string | null
          venue_city?: string | null
          venue_name: string
          venue_state?: string | null
          venue_zip?: string | null
        }
        Update: {
          api_source?: string
          category?: string
          created_at?: string
          description?: string | null
          end_date?: string | null
          external_id?: string
          id?: string
          image_url?: string | null
          is_active?: boolean
          organizer_name?: string | null
          organizer_url?: string | null
          price_display?: string | null
          price_max?: number | null
          price_min?: number | null
          start_date?: string
          tags?: string[] | null
          ticket_url?: string | null
          title?: string
          updated_at?: string
          venue_address?: string | null
          venue_city?: string | null
          venue_name?: string
          venue_state?: string | null
          venue_zip?: string | null
        }
        Relationships: []
      }
      poreve_venues: {
        Row: {
          address: string | null
          capacity: number | null
          city: string | null
          created_at: string
          id: string
          latitude: number | null
          longitude: number | null
          name: string
          phone: string | null
          state: string | null
          updated_at: string
          venue_type: string | null
          website: string | null
          zip_code: string | null
        }
        Insert: {
          address?: string | null
          capacity?: number | null
          city?: string | null
          created_at?: string
          id?: string
          latitude?: number | null
          longitude?: number | null
          name: string
          phone?: string | null
          state?: string | null
          updated_at?: string
          venue_type?: string | null
          website?: string | null
          zip_code?: string | null
        }
        Update: {
          address?: string | null
          capacity?: number | null
          city?: string | null
          created_at?: string
          id?: string
          latitude?: number | null
          longitude?: number | null
          name?: string
          phone?: string | null
          state?: string | null
          updated_at?: string
          venue_type?: string | null
          website?: string | null
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
          id: string
          image_url: string | null
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
          updated_at: string
          venue_address: string | null
          venue_city: string | null
          venue_name: string
          venue_state: string | null
          venue_zip: string | null
        }
        Insert: {
          category: string
          created_at?: string
          created_by?: string | null
          description?: string | null
          end_time?: string | null
          id?: string
          image_url?: string | null
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
          updated_at?: string
          venue_address?: string | null
          venue_city?: string | null
          venue_name: string
          venue_state?: string | null
          venue_zip?: string | null
        }
        Update: {
          category?: string
          created_at?: string
          created_by?: string | null
          description?: string | null
          end_time?: string | null
          id?: string
          image_url?: string | null
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
          updated_at?: string
          venue_address?: string | null
          venue_city?: string | null
          venue_name?: string
          venue_state?: string | null
          venue_zip?: string | null
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
    }
    Views: {
      [_ in never]: never
    }
    Functions: {
      create_admin_user: {
        Args: Record<PropertyKey, never>
        Returns: undefined
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
    }
    Enums: {
      [_ in never]: never
    }
    CompositeTypes: {
      [_ in never]: never
    }
  }
}

type DefaultSchema = Database[Extract<keyof Database, "public">]

export type Tables<
  DefaultSchemaTableNameOrOptions extends
    | keyof (DefaultSchema["Tables"] & DefaultSchema["Views"])
    | { schema: keyof Database },
  TableName extends DefaultSchemaTableNameOrOptions extends {
    schema: keyof Database
  }
    ? keyof (Database[DefaultSchemaTableNameOrOptions["schema"]]["Tables"] &
        Database[DefaultSchemaTableNameOrOptions["schema"]]["Views"])
    : never = never,
> = DefaultSchemaTableNameOrOptions extends { schema: keyof Database }
  ? (Database[DefaultSchemaTableNameOrOptions["schema"]]["Tables"] &
      Database[DefaultSchemaTableNameOrOptions["schema"]]["Views"])[TableName] extends {
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
    | { schema: keyof Database },
  TableName extends DefaultSchemaTableNameOrOptions extends {
    schema: keyof Database
  }
    ? keyof Database[DefaultSchemaTableNameOrOptions["schema"]]["Tables"]
    : never = never,
> = DefaultSchemaTableNameOrOptions extends { schema: keyof Database }
  ? Database[DefaultSchemaTableNameOrOptions["schema"]]["Tables"][TableName] extends {
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
    | { schema: keyof Database },
  TableName extends DefaultSchemaTableNameOrOptions extends {
    schema: keyof Database
  }
    ? keyof Database[DefaultSchemaTableNameOrOptions["schema"]]["Tables"]
    : never = never,
> = DefaultSchemaTableNameOrOptions extends { schema: keyof Database }
  ? Database[DefaultSchemaTableNameOrOptions["schema"]]["Tables"][TableName] extends {
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
    | { schema: keyof Database },
  EnumName extends DefaultSchemaEnumNameOrOptions extends {
    schema: keyof Database
  }
    ? keyof Database[DefaultSchemaEnumNameOrOptions["schema"]]["Enums"]
    : never = never,
> = DefaultSchemaEnumNameOrOptions extends { schema: keyof Database }
  ? Database[DefaultSchemaEnumNameOrOptions["schema"]]["Enums"][EnumName]
  : DefaultSchemaEnumNameOrOptions extends keyof DefaultSchema["Enums"]
    ? DefaultSchema["Enums"][DefaultSchemaEnumNameOrOptions]
    : never

export type CompositeTypes<
  PublicCompositeTypeNameOrOptions extends
    | keyof DefaultSchema["CompositeTypes"]
    | { schema: keyof Database },
  CompositeTypeName extends PublicCompositeTypeNameOrOptions extends {
    schema: keyof Database
  }
    ? keyof Database[PublicCompositeTypeNameOrOptions["schema"]]["CompositeTypes"]
    : never = never,
> = PublicCompositeTypeNameOrOptions extends { schema: keyof Database }
  ? Database[PublicCompositeTypeNameOrOptions["schema"]]["CompositeTypes"][CompositeTypeName]
  : PublicCompositeTypeNameOrOptions extends keyof DefaultSchema["CompositeTypes"]
    ? DefaultSchema["CompositeTypes"][PublicCompositeTypeNameOrOptions]
    : never

export const Constants = {
  public: {
    Enums: {},
  },
} as const
