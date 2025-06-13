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
