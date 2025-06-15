
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
