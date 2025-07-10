
export interface Appointment {
  id: string;
  first_name: string;
  last_name: string;
  email: string;
  phone: string;
  service: string;
  preferred_date: string;
  preferred_time: string;
  message: string;
  status: string;
  language: string;
  created_at: string;
  assigned_employee_id: string | null;
  tire_size?: string | null;
  license_plate?: string | null;
  vin?: string | null;
  service_location?: string | null;
  customer_address?: string | null;
  customer_city?: string | null;
  customer_state?: string | null;
  customer_zip?: string | null;
  vehicle_id?: string | null;
  travel_distance_miles?: number | null;
  travel_cost_estimate?: number | null;
}

export interface ContactMessage {
  id: string;
  first_name: string;
  last_name: string;
  email: string;
  phone: string;
  message: string;
  status: string;
  language: string;
  created_at: string;
}
