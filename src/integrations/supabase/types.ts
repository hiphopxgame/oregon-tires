export type Json =
  | string
  | number
  | boolean
  | null
  | { [key: string]: Json | undefined }
  | Json[]

export type Database = {
  // Allows to automatically instantiate createClient with right options
  // instead of createClient<Database, { PostgrestVersion: 'XX' }>(URL, KEY)
  __InternalSupabase: {
    PostgrestVersion: "12.2.3 (519615d)"
  }
  public: {
    Tables: {
      _members: {
        Row: {
          avatar_url: string | null
          bio: string | null
          created_at: string
          display_name: string | null
          email: string | null
          id: string
          is_public: boolean | null
          social_accounts: Json | null
          updated_at: string
          user_id: string
          username: string
          verified: boolean | null
        }
        Insert: {
          avatar_url?: string | null
          bio?: string | null
          created_at?: string
          display_name?: string | null
          email?: string | null
          id?: string
          is_public?: boolean | null
          social_accounts?: Json | null
          updated_at?: string
          user_id: string
          username: string
          verified?: boolean | null
        }
        Update: {
          avatar_url?: string | null
          bio?: string | null
          created_at?: string
          display_name?: string | null
          email?: string | null
          id?: string
          is_public?: boolean | null
          social_accounts?: Json | null
          updated_at?: string
          user_id?: string
          username?: string
          verified?: boolean | null
        }
        Relationships: []
      }
      admin_accounts: {
        Row: {
          created_at: string | null
          email: string
          full_name: string
          id: string
          is_active: boolean | null
          project_id: string
          updated_at: string | null
          user_id: string | null
        }
        Insert: {
          created_at?: string | null
          email: string
          full_name: string
          id?: string
          is_active?: boolean | null
          project_id?: string
          updated_at?: string | null
          user_id?: string | null
        }
        Update: {
          created_at?: string | null
          email?: string
          full_name?: string
          id?: string
          is_active?: boolean | null
          project_id?: string
          updated_at?: string | null
          user_id?: string | null
        }
        Relationships: []
      }
      appointments: {
        Row: {
          admin_comments: string | null
          appointment_date: string
          appointment_time: string
          client_id: string
          confirmation_sent: boolean | null
          created_at: string
          estimated_duration: number | null
          id: string
          price_quote: number | null
          special_requests: string | null
          status: string
          style_id: string | null
          updated_at: string
        }
        Insert: {
          admin_comments?: string | null
          appointment_date: string
          appointment_time: string
          client_id: string
          confirmation_sent?: boolean | null
          created_at?: string
          estimated_duration?: number | null
          id?: string
          price_quote?: number | null
          special_requests?: string | null
          status?: string
          style_id?: string | null
          updated_at?: string
        }
        Update: {
          admin_comments?: string | null
          appointment_date?: string
          appointment_time?: string
          client_id?: string
          confirmation_sent?: boolean | null
          created_at?: string
          estimated_duration?: number | null
          id?: string
          price_quote?: number | null
          special_requests?: string | null
          status?: string
          style_id?: string | null
          updated_at?: string
        }
        Relationships: [
          {
            foreignKeyName: "appointments_client_id_fkey"
            columns: ["client_id"]
            isOneToOne: false
            referencedRelation: "braiding_profiles"
            referencedColumns: ["user_id"]
          },
          {
            foreignKeyName: "appointments_style_id_fkey"
            columns: ["style_id"]
            isOneToOne: false
            referencedRelation: "hair_styles"
            referencedColumns: ["id"]
          },
        ]
      }
      artist_applications: {
        Row: {
          created_at: string
          id: string
          processed_at: string | null
          processed_by: string | null
          reason: string | null
          status: string
          user_id: string
        }
        Insert: {
          created_at?: string
          id?: string
          processed_at?: string | null
          processed_by?: string | null
          reason?: string | null
          status?: string
          user_id: string
        }
        Update: {
          created_at?: string
          id?: string
          processed_at?: string | null
          processed_by?: string | null
          reason?: string | null
          status?: string
          user_id?: string
        }
        Relationships: []
      }
      artist_content: {
        Row: {
          category: Database["public"]["Enums"]["content_category"]
          created_at: string
          id: string
          status: string | null
          title: string
          updated_at: string
          user_id: string
          youtube_url: string
        }
        Insert: {
          category: Database["public"]["Enums"]["content_category"]
          created_at?: string
          id?: string
          status?: string | null
          title: string
          updated_at?: string
          user_id: string
          youtube_url: string
        }
        Update: {
          category?: Database["public"]["Enums"]["content_category"]
          created_at?: string
          id?: string
          status?: string | null
          title?: string
          updated_at?: string
          user_id?: string
          youtube_url?: string
        }
        Relationships: []
      }
      artist_photos: {
        Row: {
          artist_id: string
          caption: string | null
          created_at: string
          display_order: number | null
          id: string
          image_url: string
          is_featured: boolean | null
          position_x: number | null
          position_y: number | null
          scale: number | null
          updated_at: string
        }
        Insert: {
          artist_id: string
          caption?: string | null
          created_at?: string
          display_order?: number | null
          id?: string
          image_url: string
          is_featured?: boolean | null
          position_x?: number | null
          position_y?: number | null
          scale?: number | null
          updated_at?: string
        }
        Update: {
          artist_id?: string
          caption?: string | null
          created_at?: string
          display_order?: number | null
          id?: string
          image_url?: string
          is_featured?: boolean | null
          position_x?: number | null
          position_y?: number | null
          scale?: number | null
          updated_at?: string
        }
        Relationships: [
          {
            foreignKeyName: "artist_photos_artist_id_fkey"
            columns: ["artist_id"]
            isOneToOne: false
            referencedRelation: "artist_profiles"
            referencedColumns: ["id"]
          },
        ]
      }
      artist_profiles: {
        Row: {
          apple_music_url: string | null
          avatar_url: string | null
          bandcamp_url: string | null
          bio: string | null
          created_at: string
          display_order: number | null
          email: string | null
          facebook_url: string | null
          id: string
          instagram_url: string | null
          is_archived: boolean | null
          is_email_public: boolean | null
          is_featured: boolean | null
          is_public: boolean | null
          name: string
          soundcloud_url: string | null
          spotify_url: string | null
          tiktok_url: string | null
          twitter_url: string | null
          updated_at: string
          user_id: string | null
          website_url: string | null
          youtube_url: string | null
        }
        Insert: {
          apple_music_url?: string | null
          avatar_url?: string | null
          bandcamp_url?: string | null
          bio?: string | null
          created_at?: string
          display_order?: number | null
          email?: string | null
          facebook_url?: string | null
          id?: string
          instagram_url?: string | null
          is_archived?: boolean | null
          is_email_public?: boolean | null
          is_featured?: boolean | null
          is_public?: boolean | null
          name: string
          soundcloud_url?: string | null
          spotify_url?: string | null
          tiktok_url?: string | null
          twitter_url?: string | null
          updated_at?: string
          user_id?: string | null
          website_url?: string | null
          youtube_url?: string | null
        }
        Update: {
          apple_music_url?: string | null
          avatar_url?: string | null
          bandcamp_url?: string | null
          bio?: string | null
          created_at?: string
          display_order?: number | null
          email?: string | null
          facebook_url?: string | null
          id?: string
          instagram_url?: string | null
          is_archived?: boolean | null
          is_email_public?: boolean | null
          is_featured?: boolean | null
          is_public?: boolean | null
          name?: string
          soundcloud_url?: string | null
          spotify_url?: string | null
          tiktok_url?: string | null
          twitter_url?: string | null
          updated_at?: string
          user_id?: string | null
          website_url?: string | null
          youtube_url?: string | null
        }
        Relationships: []
      }
      artist_videos: {
        Row: {
          artist_id: string
          created_at: string
          description: string | null
          display_order: number | null
          id: string
          is_featured: boolean | null
          thumbnail_url: string | null
          title: string
          updated_at: string
          youtube_id: string
          youtube_url: string
        }
        Insert: {
          artist_id: string
          created_at?: string
          description?: string | null
          display_order?: number | null
          id?: string
          is_featured?: boolean | null
          thumbnail_url?: string | null
          title: string
          updated_at?: string
          youtube_id: string
          youtube_url: string
        }
        Update: {
          artist_id?: string
          created_at?: string
          description?: string | null
          display_order?: number | null
          id?: string
          is_featured?: boolean | null
          thumbnail_url?: string | null
          title?: string
          updated_at?: string
          youtube_id?: string
          youtube_url?: string
        }
        Relationships: [
          {
            foreignKeyName: "artist_videos_artist_id_fkey"
            columns: ["artist_id"]
            isOneToOne: false
            referencedRelation: "artist_profiles"
            referencedColumns: ["id"]
          },
        ]
      }
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
      braiding_profiles: {
        Row: {
          address: string | null
          allergies: string | null
          city: string | null
          created_at: string
          email: string | null
          first_name: string
          hair_type: string | null
          id: string
          last_name: string
          phone: string | null
          preferred_contact: string | null
          project_id: string
          state: string | null
          updated_at: string
          user_id: string
          zip_code: string | null
        }
        Insert: {
          address?: string | null
          allergies?: string | null
          city?: string | null
          created_at?: string
          email?: string | null
          first_name: string
          hair_type?: string | null
          id?: string
          last_name: string
          phone?: string | null
          preferred_contact?: string | null
          project_id?: string
          state?: string | null
          updated_at?: string
          user_id: string
          zip_code?: string | null
        }
        Update: {
          address?: string | null
          allergies?: string | null
          city?: string | null
          created_at?: string
          email?: string | null
          first_name?: string
          hair_type?: string | null
          id?: string
          last_name?: string
          phone?: string | null
          preferred_contact?: string | null
          project_id?: string
          state?: string | null
          updated_at?: string
          user_id?: string
          zip_code?: string | null
        }
        Relationships: []
      }
      cbake_messages: {
        Row: {
          created_at: string
          email: string
          id: string
          inquiry_type: string
          message: string
          name: string
          status: string
          updated_at: string
          user_id: string | null
        }
        Insert: {
          created_at?: string
          email: string
          id?: string
          inquiry_type: string
          message: string
          name: string
          status?: string
          updated_at?: string
          user_id?: string | null
        }
        Update: {
          created_at?: string
          email?: string
          id?: string
          inquiry_type?: string
          message?: string
          name?: string
          status?: string
          updated_at?: string
          user_id?: string | null
        }
        Relationships: [
          {
            foreignKeyName: "cbake_messages_user_id_fkey"
            columns: ["user_id"]
            isOneToOne: false
            referencedRelation: "cbake_profiles"
            referencedColumns: ["user_id"]
          },
        ]
      }
      cbake_newsletter_subscriptions: {
        Row: {
          created_at: string
          email: string
          id: string
          status: string
          updated_at: string
        }
        Insert: {
          created_at?: string
          email: string
          id?: string
          status?: string
          updated_at?: string
        }
        Update: {
          created_at?: string
          email?: string
          id?: string
          status?: string
          updated_at?: string
        }
        Relationships: []
      }
      cbake_orders: {
        Row: {
          created_at: string
          delivery: string
          dough_type: string
          email: string
          estimated_total: number | null
          filling: string
          id: string
          name: string
          order_type: string
          phone: string | null
          product_id: string | null
          product_name: string | null
          quantity: number
          special_instructions: string | null
          status: string
          updated_at: string
          user_id: string | null
        }
        Insert: {
          created_at?: string
          delivery: string
          dough_type: string
          email: string
          estimated_total?: number | null
          filling: string
          id?: string
          name: string
          order_type: string
          phone?: string | null
          product_id?: string | null
          product_name?: string | null
          quantity?: number
          special_instructions?: string | null
          status?: string
          updated_at?: string
          user_id?: string | null
        }
        Update: {
          created_at?: string
          delivery?: string
          dough_type?: string
          email?: string
          estimated_total?: number | null
          filling?: string
          id?: string
          name?: string
          order_type?: string
          phone?: string | null
          product_id?: string | null
          product_name?: string | null
          quantity?: number
          special_instructions?: string | null
          status?: string
          updated_at?: string
          user_id?: string | null
        }
        Relationships: [
          {
            foreignKeyName: "cbake_orders_product_id_fkey"
            columns: ["product_id"]
            isOneToOne: false
            referencedRelation: "cbake_products"
            referencedColumns: ["id"]
          },
          {
            foreignKeyName: "cbake_orders_user_id_fkey"
            columns: ["user_id"]
            isOneToOne: false
            referencedRelation: "cbake_profiles"
            referencedColumns: ["user_id"]
          },
        ]
      }
      cbake_products: {
        Row: {
          base_price: number | null
          created_at: string
          description: string | null
          display_order: number | null
          id: string
          image_url: string | null
          ingredients: string | null
          is_active: boolean
          name: string
          origin: string | null
          product_type: string
          tags: string[] | null
          updated_at: string
        }
        Insert: {
          base_price?: number | null
          created_at?: string
          description?: string | null
          display_order?: number | null
          id?: string
          image_url?: string | null
          ingredients?: string | null
          is_active?: boolean
          name: string
          origin?: string | null
          product_type: string
          tags?: string[] | null
          updated_at?: string
        }
        Update: {
          base_price?: number | null
          created_at?: string
          description?: string | null
          display_order?: number | null
          id?: string
          image_url?: string | null
          ingredients?: string | null
          is_active?: boolean
          name?: string
          origin?: string | null
          product_type?: string
          tags?: string[] | null
          updated_at?: string
        }
        Relationships: []
      }
      cbake_profiles: {
        Row: {
          created_at: string
          email: string
          full_name: string | null
          id: string
          is_admin: boolean
          updated_at: string
          user_id: string
        }
        Insert: {
          created_at?: string
          email: string
          full_name?: string | null
          id?: string
          is_admin?: boolean
          updated_at?: string
          user_id: string
        }
        Update: {
          created_at?: string
          email?: string
          full_name?: string | null
          id?: string
          is_admin?: boolean
          updated_at?: string
          user_id?: string
        }
        Relationships: []
      }
      cbake_quotes: {
        Row: {
          admin_notes: string | null
          business_name: string | null
          catering_services: Json | null
          created_at: string
          email: string
          event_date: string | null
          event_type: string | null
          guest_count: number | null
          id: string
          name: string
          phone: string | null
          quoted_amount: number | null
          special_requirements: string | null
          status: string
          updated_at: string
        }
        Insert: {
          admin_notes?: string | null
          business_name?: string | null
          catering_services?: Json | null
          created_at?: string
          email: string
          event_date?: string | null
          event_type?: string | null
          guest_count?: number | null
          id?: string
          name: string
          phone?: string | null
          quoted_amount?: number | null
          special_requirements?: string | null
          status?: string
          updated_at?: string
        }
        Update: {
          admin_notes?: string | null
          business_name?: string | null
          catering_services?: Json | null
          created_at?: string
          email?: string
          event_date?: string | null
          event_type?: string | null
          guest_count?: number | null
          id?: string
          name?: string
          phone?: string | null
          quoted_amount?: number | null
          special_requirements?: string | null
          status?: string
          updated_at?: string
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
      donations: {
        Row: {
          amount: number
          created_at: string
          currency: string | null
          donor_name: string | null
          email: string | null
          id: string
          message: string | null
          status: string | null
          stripe_session_id: string | null
          updated_at: string
          user_id: string | null
        }
        Insert: {
          amount: number
          created_at?: string
          currency?: string | null
          donor_name?: string | null
          email?: string | null
          id?: string
          message?: string | null
          status?: string | null
          stripe_session_id?: string | null
          updated_at?: string
          user_id?: string | null
        }
        Update: {
          amount?: number
          created_at?: string
          currency?: string | null
          donor_name?: string | null
          email?: string | null
          id?: string
          message?: string | null
          status?: string | null
          stripe_session_id?: string | null
          updated_at?: string
          user_id?: string | null
        }
        Relationships: []
      }
      featured_artists: {
        Row: {
          apple_music_url: string | null
          bandcamp_url: string | null
          bio: string | null
          created_at: string
          created_by: string | null
          display_order: number | null
          facebook_url: string | null
          id: string
          image_url: string | null
          is_active: boolean
          name: string
          soundcloud_url: string | null
          spotify_url: string | null
          tiktok_url: string | null
          twitter_url: string | null
          updated_at: string
          website_url: string | null
          youtube_url: string | null
        }
        Insert: {
          apple_music_url?: string | null
          bandcamp_url?: string | null
          bio?: string | null
          created_at?: string
          created_by?: string | null
          display_order?: number | null
          facebook_url?: string | null
          id?: string
          image_url?: string | null
          is_active?: boolean
          name: string
          soundcloud_url?: string | null
          spotify_url?: string | null
          tiktok_url?: string | null
          twitter_url?: string | null
          updated_at?: string
          website_url?: string | null
          youtube_url?: string | null
        }
        Update: {
          apple_music_url?: string | null
          bandcamp_url?: string | null
          bio?: string | null
          created_at?: string
          created_by?: string | null
          display_order?: number | null
          facebook_url?: string | null
          id?: string
          image_url?: string | null
          is_active?: boolean
          name?: string
          soundcloud_url?: string | null
          spotify_url?: string | null
          tiktok_url?: string | null
          twitter_url?: string | null
          updated_at?: string
          website_url?: string | null
          youtube_url?: string | null
        }
        Relationships: []
      }
      hair_styles: {
        Row: {
          base_price: number | null
          created_at: string
          description: string | null
          duration_hours: number | null
          id: string
          name: string
          project_id: string
        }
        Insert: {
          base_price?: number | null
          created_at?: string
          description?: string | null
          duration_hours?: number | null
          id?: string
          name: string
          project_id?: string
        }
        Update: {
          base_price?: number | null
          created_at?: string
          description?: string | null
          duration_hours?: number | null
          id?: string
          name?: string
          project_id?: string
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
      hiphopworld_orders: {
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
      import_batches: {
        Row: {
          created_at: string
          created_by: string | null
          file_type: string
          filename: string
          id: string
          notes: string | null
          reviewed_at: string | null
          reviewed_by: string | null
          status: string
          total_events: number
          total_venues: number
          updated_at: string
        }
        Insert: {
          created_at?: string
          created_by?: string | null
          file_type: string
          filename: string
          id?: string
          notes?: string | null
          reviewed_at?: string | null
          reviewed_by?: string | null
          status?: string
          total_events?: number
          total_venues?: number
          updated_at?: string
        }
        Update: {
          created_at?: string
          created_by?: string | null
          file_type?: string
          filename?: string
          id?: string
          notes?: string | null
          reviewed_at?: string | null
          reviewed_by?: string | null
          status?: string
          total_events?: number
          total_venues?: number
          updated_at?: string
        }
        Relationships: []
      }
      iwitty_admin_accounts: {
        Row: {
          created_at: string
          email: string
          full_name: string
          id: string
          is_active: boolean
          updated_at: string
          user_id: string
        }
        Insert: {
          created_at?: string
          email: string
          full_name: string
          id?: string
          is_active?: boolean
          updated_at?: string
          user_id: string
        }
        Update: {
          created_at?: string
          email?: string
          full_name?: string
          id?: string
          is_active?: boolean
          updated_at?: string
          user_id?: string
        }
        Relationships: []
      }
      iwitty_appointments: {
        Row: {
          admin_comments: string | null
          appointment_date: string
          appointment_time: string
          client_id: string
          confirmation_sent: boolean | null
          created_at: string
          estimated_duration: number | null
          id: string
          price_quote: number | null
          special_requests: string | null
          status: string
          style_id: string | null
          updated_at: string
        }
        Insert: {
          admin_comments?: string | null
          appointment_date: string
          appointment_time: string
          client_id: string
          confirmation_sent?: boolean | null
          created_at?: string
          estimated_duration?: number | null
          id?: string
          price_quote?: number | null
          special_requests?: string | null
          status?: string
          style_id?: string | null
          updated_at?: string
        }
        Update: {
          admin_comments?: string | null
          appointment_date?: string
          appointment_time?: string
          client_id?: string
          confirmation_sent?: boolean | null
          created_at?: string
          estimated_duration?: number | null
          id?: string
          price_quote?: number | null
          special_requests?: string | null
          status?: string
          style_id?: string | null
          updated_at?: string
        }
        Relationships: [
          {
            foreignKeyName: "fk_iwitty_appointments_client_profile"
            columns: ["client_id"]
            isOneToOne: false
            referencedRelation: "iwitty_profiles"
            referencedColumns: ["user_id"]
          },
          {
            foreignKeyName: "iwitty_appointments_style_id_fkey"
            columns: ["style_id"]
            isOneToOne: false
            referencedRelation: "iwitty_hair_styles"
            referencedColumns: ["id"]
          },
        ]
      }
      iwitty_hair_styles: {
        Row: {
          base_price: number | null
          created_at: string
          description: string | null
          duration_hours: number | null
          id: string
          name: string
        }
        Insert: {
          base_price?: number | null
          created_at?: string
          description?: string | null
          duration_hours?: number | null
          id?: string
          name: string
        }
        Update: {
          base_price?: number | null
          created_at?: string
          description?: string | null
          duration_hours?: number | null
          id?: string
          name?: string
        }
        Relationships: []
      }
      iwitty_portfolio_images: {
        Row: {
          client_name: string | null
          completion_date: string | null
          created_at: string
          description: string | null
          display_order: number | null
          id: string
          image_url: string
          is_featured: boolean | null
          style_id: string | null
          title: string
          updated_at: string
        }
        Insert: {
          client_name?: string | null
          completion_date?: string | null
          created_at?: string
          description?: string | null
          display_order?: number | null
          id?: string
          image_url: string
          is_featured?: boolean | null
          style_id?: string | null
          title: string
          updated_at?: string
        }
        Update: {
          client_name?: string | null
          completion_date?: string | null
          created_at?: string
          description?: string | null
          display_order?: number | null
          id?: string
          image_url?: string
          is_featured?: boolean | null
          style_id?: string | null
          title?: string
          updated_at?: string
        }
        Relationships: [
          {
            foreignKeyName: "iwitty_portfolio_images_style_id_fkey"
            columns: ["style_id"]
            isOneToOne: false
            referencedRelation: "iwitty_hair_styles"
            referencedColumns: ["id"]
          },
        ]
      }
      iwitty_profiles: {
        Row: {
          address: string | null
          allergies: string | null
          city: string | null
          created_at: string
          email: string | null
          first_name: string
          hair_type: string | null
          id: string
          last_name: string
          phone: string | null
          preferred_contact: string | null
          state: string | null
          updated_at: string
          user_id: string
          zip_code: string | null
        }
        Insert: {
          address?: string | null
          allergies?: string | null
          city?: string | null
          created_at?: string
          email?: string | null
          first_name: string
          hair_type?: string | null
          id?: string
          last_name: string
          phone?: string | null
          preferred_contact?: string | null
          state?: string | null
          updated_at?: string
          user_id: string
          zip_code?: string | null
        }
        Update: {
          address?: string | null
          allergies?: string | null
          city?: string | null
          created_at?: string
          email?: string | null
          first_name?: string
          hair_type?: string | null
          id?: string
          last_name?: string
          phone?: string | null
          preferred_contact?: string | null
          state?: string | null
          updated_at?: string
          user_id?: string
          zip_code?: string | null
        }
        Relationships: []
      }
      member_social_accounts: {
        Row: {
          connected_at: string
          id: string
          member_id: string
          provider: string
          provider_email: string | null
          provider_id: string
          provider_username: string | null
        }
        Insert: {
          connected_at?: string
          id?: string
          member_id: string
          provider: string
          provider_email?: string | null
          provider_id: string
          provider_username?: string | null
        }
        Update: {
          connected_at?: string
          id?: string
          member_id?: string
          provider?: string
          provider_email?: string | null
          provider_id?: string
          provider_username?: string | null
        }
        Relationships: [
          {
            foreignKeyName: "member_social_accounts_member_id_fkey"
            columns: ["member_id"]
            isOneToOne: false
            referencedRelation: "members"
            referencedColumns: ["id"]
          },
        ]
      }
      members: {
        Row: {
          avatar_url: string | null
          bio: string | null
          created_at: string
          display_name: string | null
          id: string
          updated_at: string
          user_id: string
          username: string
        }
        Insert: {
          avatar_url?: string | null
          bio?: string | null
          created_at?: string
          display_name?: string | null
          id?: string
          updated_at?: string
          user_id: string
          username: string
        }
        Update: {
          avatar_url?: string | null
          bio?: string | null
          created_at?: string
          display_name?: string | null
          id?: string
          updated_at?: string
          user_id?: string
          username?: string
        }
        Relationships: []
      }
      multichain_profiles: {
        Row: {
          avatar_url: string | null
          created_at: string
          display_name: string | null
          id: string
          updated_at: string
          user_id: string
          username: string | null
        }
        Insert: {
          avatar_url?: string | null
          created_at?: string
          display_name?: string | null
          id?: string
          updated_at?: string
          user_id: string
          username?: string | null
        }
        Update: {
          avatar_url?: string | null
          created_at?: string
          display_name?: string | null
          id?: string
          updated_at?: string
          user_id?: string
          username?: string | null
        }
        Relationships: []
      }
      music_videos: {
        Row: {
          approved_at: string | null
          approved_by: string | null
          artist_id: string
          created_at: string
          id: string
          rejection_reason: string | null
          status: string
          title: string
          updated_at: string
          youtube_id: string
          youtube_url: string
        }
        Insert: {
          approved_at?: string | null
          approved_by?: string | null
          artist_id: string
          created_at?: string
          id?: string
          rejection_reason?: string | null
          status?: string
          title: string
          updated_at?: string
          youtube_id: string
          youtube_url: string
        }
        Update: {
          approved_at?: string | null
          approved_by?: string | null
          artist_id?: string
          created_at?: string
          id?: string
          rejection_reason?: string | null
          status?: string
          title?: string
          updated_at?: string
          youtube_id?: string
          youtube_url?: string
        }
        Relationships: [
          {
            foreignKeyName: "music_videos_approved_by_fkey"
            columns: ["approved_by"]
            isOneToOne: false
            referencedRelation: "por_eve_profiles"
            referencedColumns: ["id"]
          },
          {
            foreignKeyName: "music_videos_artist_id_fkey"
            columns: ["artist_id"]
            isOneToOne: false
            referencedRelation: "por_eve_profiles"
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
        Relationships: []
      }
      oretir_appointments: {
        Row: {
          actual_duration_minutes: number | null
          actual_duration_seconds: number | null
          admin_notes: string | null
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
          actual_duration_seconds?: number | null
          admin_notes?: string | null
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
          actual_duration_seconds?: number | null
          admin_notes?: string | null
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
        Relationships: []
      }
      oretir_contact_messages: {
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
        }
        Relationships: []
      }
      oretir_custom_hours: {
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
      oretir_email_logs: {
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
        Relationships: []
      }
      oretir_employee_schedules: {
        Row: {
          created_at: string
          employee_id: string
          end_time: string
          id: string
          is_active: boolean
          schedule_date: string
          start_time: string
          updated_at: string
        }
        Insert: {
          created_at?: string
          employee_id: string
          end_time: string
          id?: string
          is_active?: boolean
          schedule_date: string
          start_time: string
          updated_at?: string
        }
        Update: {
          created_at?: string
          employee_id?: string
          end_time?: string
          id?: string
          is_active?: boolean
          schedule_date?: string
          start_time?: string
          updated_at?: string
        }
        Relationships: [
          {
            foreignKeyName: "oregon_tires_employee_schedules_employee_id_fkey"
            columns: ["employee_id"]
            isOneToOne: false
            referencedRelation: "oretir_employees"
            referencedColumns: ["id"]
          },
        ]
      }
      oretir_employees: {
        Row: {
          created_at: string
          email: string | null
          id: string
          is_active: boolean
          name: string
          phone: string | null
          role: string | null
          updated_at: string
        }
        Insert: {
          created_at?: string
          email?: string | null
          id?: string
          is_active?: boolean
          name: string
          phone?: string | null
          role?: string | null
          updated_at?: string
        }
        Update: {
          created_at?: string
          email?: string | null
          id?: string
          is_active?: boolean
          name?: string
          phone?: string | null
          role?: string | null
          updated_at?: string
        }
        Relationships: []
      }
      oretir_gallery_images: {
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
      oretir_profiles: {
        Row: {
          created_at: string
          id: string
          is_admin: boolean
          project_id: string
          updated_at: string
        }
        Insert: {
          created_at?: string
          id: string
          is_admin?: boolean
          project_id?: string
          updated_at?: string
        }
        Update: {
          created_at?: string
          id?: string
          is_admin?: boolean
          project_id?: string
          updated_at?: string
        }
        Relationships: []
      }
      oretir_service_images: {
        Row: {
          created_at: string
          id: string
          image_url: string
          is_current: boolean
          position_x: number
          position_y: number
          scale: number
          service_key: string
          title: string
          updated_at: string
        }
        Insert: {
          created_at?: string
          id?: string
          image_url: string
          is_current?: boolean
          position_x?: number
          position_y?: number
          scale?: number
          service_key: string
          title: string
          updated_at?: string
        }
        Update: {
          created_at?: string
          id?: string
          image_url?: string
          is_current?: boolean
          position_x?: number
          position_y?: number
          scale?: number
          service_key?: string
          title?: string
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
      pdxbus_businesses: {
        Row: {
          address: string | null
          business_hours: Json | null
          business_name: string
          category: string
          city: string
          created_at: string
          description: string | null
          email: string | null
          facebook_url: string | null
          id: string
          image_urls: string[] | null
          instagram_url: string | null
          is_approved: boolean
          is_featured: boolean
          linkedin_url: string | null
          logo_url: string | null
          owner_id: string
          phone: string | null
          state: string
          twitter_url: string | null
          updated_at: string
          website_url: string | null
          zip_code: string | null
        }
        Insert: {
          address?: string | null
          business_hours?: Json | null
          business_name: string
          category: string
          city?: string
          created_at?: string
          description?: string | null
          email?: string | null
          facebook_url?: string | null
          id?: string
          image_urls?: string[] | null
          instagram_url?: string | null
          is_approved?: boolean
          is_featured?: boolean
          linkedin_url?: string | null
          logo_url?: string | null
          owner_id: string
          phone?: string | null
          state?: string
          twitter_url?: string | null
          updated_at?: string
          website_url?: string | null
          zip_code?: string | null
        }
        Update: {
          address?: string | null
          business_hours?: Json | null
          business_name?: string
          category?: string
          city?: string
          created_at?: string
          description?: string | null
          email?: string | null
          facebook_url?: string | null
          id?: string
          image_urls?: string[] | null
          instagram_url?: string | null
          is_approved?: boolean
          is_featured?: boolean
          linkedin_url?: string | null
          logo_url?: string | null
          owner_id?: string
          phone?: string | null
          state?: string
          twitter_url?: string | null
          updated_at?: string
          website_url?: string | null
          zip_code?: string | null
        }
        Relationships: []
      }
      pdxbus_categories: {
        Row: {
          created_at: string
          description: string | null
          id: string
          is_active: boolean
          name: string
        }
        Insert: {
          created_at?: string
          description?: string | null
          id?: string
          is_active?: boolean
          name: string
        }
        Update: {
          created_at?: string
          description?: string | null
          id?: string
          is_active?: boolean
          name?: string
        }
        Relationships: []
      }
      pdxbus_profiles: {
        Row: {
          avatar_url: string | null
          bio: string | null
          company_name: string | null
          created_at: string
          email: string
          full_name: string
          id: string
          is_approved: boolean
          linkedin_url: string | null
          phone: string | null
          position: string | null
          updated_at: string
          user_id: string
          website_url: string | null
        }
        Insert: {
          avatar_url?: string | null
          bio?: string | null
          company_name?: string | null
          created_at?: string
          email: string
          full_name: string
          id?: string
          is_approved?: boolean
          linkedin_url?: string | null
          phone?: string | null
          position?: string | null
          updated_at?: string
          user_id: string
          website_url?: string | null
        }
        Update: {
          avatar_url?: string | null
          bio?: string | null
          company_name?: string | null
          created_at?: string
          email?: string
          full_name?: string
          id?: string
          is_approved?: boolean
          linkedin_url?: string | null
          phone?: string | null
          position?: string | null
          updated_at?: string
          user_id?: string
          website_url?: string | null
        }
        Relationships: []
      }
      por_eve_profiles: {
        Row: {
          avatar_url: string | null
          bandcamp_url: string | null
          city: string | null
          created_at: string
          display_name: string | null
          email: string
          facebook_url: string | null
          id: string
          instagram_url: string | null
          is_email_public: boolean | null
          project_id: string
          soundcloud_url: string | null
          spotify_url: string | null
          state: string | null
          twitter_url: string | null
          updated_at: string
          username: string | null
          website_url: string | null
          youtube_url: string | null
          zip_code: string | null
        }
        Insert: {
          avatar_url?: string | null
          bandcamp_url?: string | null
          city?: string | null
          created_at?: string
          display_name?: string | null
          email: string
          facebook_url?: string | null
          id: string
          instagram_url?: string | null
          is_email_public?: boolean | null
          project_id?: string
          soundcloud_url?: string | null
          spotify_url?: string | null
          state?: string | null
          twitter_url?: string | null
          updated_at?: string
          username?: string | null
          website_url?: string | null
          youtube_url?: string | null
          zip_code?: string | null
        }
        Update: {
          avatar_url?: string | null
          bandcamp_url?: string | null
          city?: string | null
          created_at?: string
          display_name?: string | null
          email?: string
          facebook_url?: string | null
          id?: string
          instagram_url?: string | null
          is_email_public?: boolean | null
          project_id?: string
          soundcloud_url?: string | null
          spotify_url?: string | null
          state?: string | null
          twitter_url?: string | null
          updated_at?: string
          username?: string | null
          website_url?: string | null
          youtube_url?: string | null
          zip_code?: string | null
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
          selected_services: string[] | null
          service_type: string | null
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
          selected_services?: string[] | null
          service_type?: string | null
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
          selected_services?: string[] | null
          service_type?: string | null
          status?: string | null
          updated_at?: string
          wants_consultation?: boolean | null
          website_url?: string | null
        }
        Relationships: []
      }
      portfolio_images: {
        Row: {
          client_name: string | null
          completion_date: string | null
          created_at: string
          description: string | null
          display_order: number | null
          id: string
          image_url: string
          is_featured: boolean | null
          project_id: string
          style_id: string | null
          title: string
          updated_at: string
        }
        Insert: {
          client_name?: string | null
          completion_date?: string | null
          created_at?: string
          description?: string | null
          display_order?: number | null
          id?: string
          image_url: string
          is_featured?: boolean | null
          project_id?: string
          style_id?: string | null
          title: string
          updated_at?: string
        }
        Update: {
          client_name?: string | null
          completion_date?: string | null
          created_at?: string
          description?: string | null
          display_order?: number | null
          id?: string
          image_url?: string
          is_featured?: boolean | null
          project_id?: string
          style_id?: string | null
          title?: string
          updated_at?: string
        }
        Relationships: [
          {
            foreignKeyName: "portfolio_images_style_id_fkey"
            columns: ["style_id"]
            isOneToOne: false
            referencedRelation: "hair_styles"
            referencedColumns: ["id"]
          },
        ]
      }
      staging_events: {
        Row: {
          api_source: string | null
          category: string
          created_at: string
          description: string | null
          end_time: string | null
          external_id: string | null
          facebook_url: string | null
          id: string
          image_url: string | null
          import_batch_id: string
          instagram_url: string | null
          is_recurring: boolean | null
          price_display: string | null
          recurrence_end_date: string | null
          recurrence_pattern: string | null
          recurrence_type: string | null
          start_date: string
          start_time: string | null
          ticket_url: string | null
          title: string
          twitter_url: string | null
          venue_address: string | null
          venue_city: string | null
          venue_name: string
          venue_state: string | null
          venue_zip: string | null
          website_url: string | null
          youtube_url: string | null
        }
        Insert: {
          api_source?: string | null
          category: string
          created_at?: string
          description?: string | null
          end_time?: string | null
          external_id?: string | null
          facebook_url?: string | null
          id?: string
          image_url?: string | null
          import_batch_id: string
          instagram_url?: string | null
          is_recurring?: boolean | null
          price_display?: string | null
          recurrence_end_date?: string | null
          recurrence_pattern?: string | null
          recurrence_type?: string | null
          start_date: string
          start_time?: string | null
          ticket_url?: string | null
          title: string
          twitter_url?: string | null
          venue_address?: string | null
          venue_city?: string | null
          venue_name: string
          venue_state?: string | null
          venue_zip?: string | null
          website_url?: string | null
          youtube_url?: string | null
        }
        Update: {
          api_source?: string | null
          category?: string
          created_at?: string
          description?: string | null
          end_time?: string | null
          external_id?: string | null
          facebook_url?: string | null
          id?: string
          image_url?: string | null
          import_batch_id?: string
          instagram_url?: string | null
          is_recurring?: boolean | null
          price_display?: string | null
          recurrence_end_date?: string | null
          recurrence_pattern?: string | null
          recurrence_type?: string | null
          start_date?: string
          start_time?: string | null
          ticket_url?: string | null
          title?: string
          twitter_url?: string | null
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
            foreignKeyName: "staging_events_import_batch_id_fkey"
            columns: ["import_batch_id"]
            isOneToOne: false
            referencedRelation: "import_batches"
            referencedColumns: ["id"]
          },
        ]
      }
      staging_venues: {
        Row: {
          address: string | null
          ages: string | null
          api_source: string | null
          city: string | null
          created_at: string
          facebook_url: string | null
          id: string
          image_urls: string[] | null
          import_batch_id: string
          instagram_url: string | null
          name: string
          phone: string | null
          state: string | null
          twitter_url: string | null
          website: string | null
          youtube_url: string | null
          zip_code: string | null
        }
        Insert: {
          address?: string | null
          ages?: string | null
          api_source?: string | null
          city?: string | null
          created_at?: string
          facebook_url?: string | null
          id?: string
          image_urls?: string[] | null
          import_batch_id: string
          instagram_url?: string | null
          name: string
          phone?: string | null
          state?: string | null
          twitter_url?: string | null
          website?: string | null
          youtube_url?: string | null
          zip_code?: string | null
        }
        Update: {
          address?: string | null
          ages?: string | null
          api_source?: string | null
          city?: string | null
          created_at?: string
          facebook_url?: string | null
          id?: string
          image_urls?: string[] | null
          import_batch_id?: string
          instagram_url?: string | null
          name?: string
          phone?: string | null
          state?: string | null
          twitter_url?: string | null
          website?: string | null
          youtube_url?: string | null
          zip_code?: string | null
        }
        Relationships: [
          {
            foreignKeyName: "staging_venues_import_batch_id_fkey"
            columns: ["import_batch_id"]
            isOneToOne: false
            referencedRelation: "import_batches"
            referencedColumns: ["id"]
          },
        ]
      }
      user_events: {
        Row: {
          api_source: string | null
          category: string
          created_at: string
          created_by: string | null
          description: string | null
          end_time: string | null
          external_id: string | null
          facebook_url: string | null
          id: string
          image_url: string | null
          image_urls: string[] | null
          instagram_url: string | null
          is_featured: boolean
          is_recurring: boolean | null
          price_display: string | null
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
          api_source?: string | null
          category: string
          created_at?: string
          created_by?: string | null
          description?: string | null
          end_time?: string | null
          external_id?: string | null
          facebook_url?: string | null
          id?: string
          image_url?: string | null
          image_urls?: string[] | null
          instagram_url?: string | null
          is_featured?: boolean
          is_recurring?: boolean | null
          price_display?: string | null
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
          api_source?: string | null
          category?: string
          created_at?: string
          created_by?: string | null
          description?: string | null
          end_time?: string | null
          external_id?: string | null
          facebook_url?: string | null
          id?: string
          image_url?: string | null
          image_urls?: string[] | null
          instagram_url?: string | null
          is_featured?: boolean
          is_recurring?: boolean | null
          price_display?: string | null
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
          ages: string | null
          api_source: string | null
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
          image_urls: string[] | null
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
          ages?: string | null
          api_source?: string | null
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
          image_urls?: string[] | null
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
          ages?: string | null
          api_source?: string | null
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
          image_urls?: string[] | null
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
      approve_import_batch: {
        Args: { batch_id: string }
        Returns: undefined
      }
      create_admin_user: {
        Args: Record<PropertyKey, never>
        Returns: undefined
      }
      create_employee_auth_account: {
        Args: { employee_email: string; temporary_password?: string }
        Returns: string
      }
      format_service_name: {
        Args: { service_slug: string }
        Returns: string
      }
      get_admin_users: {
        Args: Record<PropertyKey, never>
        Returns: {
          created_at: string
          email: string
          id: string
          is_admin: boolean
          last_sign_in_at: string
          name: string
          updated_at: string
        }[]
      }
      handle_community_purchase: {
        Args: { p_cash_amount: number; p_user_id: string }
        Returns: undefined
      }
      has_role: {
        Args: {
          _role: Database["public"]["Enums"]["app_role"]
          _user_id: string
        }
        Returns: boolean
      }
      is_admin: {
        Args: Record<PropertyKey, never>
        Returns: boolean
      }
      is_cbake_admin: {
        Args: Record<PropertyKey, never>
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
      is_iwitty_admin: {
        Args: Record<PropertyKey, never>
        Returns: boolean
      }
      is_pdxbus_admin: {
        Args: Record<PropertyKey, never>
        Returns: boolean
      }
      is_super_admin: {
        Args: Record<PropertyKey, never>
        Returns: boolean
      }
      set_admin_by_email: {
        Args:
          | {
              admin_status?: boolean
              target_project_id?: string
              user_email: string
            }
          | { admin_status?: boolean; user_email: string }
          | { user_email: string }
        Returns: boolean
      }
      setup_admin_user: {
        Args: Record<PropertyKey, never>
        Returns: undefined
      }
      upgrade_to_artist: {
        Args: Record<PropertyKey, never>
        Returns: undefined
      }
      user_hosts_game: {
        Args: { game_id: string; user_id: string }
        Returns: boolean
      }
      user_participates_in_game: {
        Args: { game_id: string; user_id: string }
        Returns: boolean
      }
    }
    Enums: {
      app_role: "admin" | "moderator" | "user" | "member" | "artist"
      content_category:
        | "Live Footage"
        | "Music Videos"
        | "Interviews"
        | "Miscellaneous"
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
      app_role: ["admin", "moderator", "user", "member", "artist"],
      content_category: [
        "Live Footage",
        "Music Videos",
        "Interviews",
        "Miscellaneous",
      ],
    },
  },
} as const
