export interface News {
  id: number;
  title: string;
  content: string;
  imageUrl?: string;
  author: string;
  authorId: number;
  publishedAt?: Date;
  isPublished: boolean;
  category: 'general' | 'event' | 'announcement' | 'urgent';
  createdAt: Date;
  updatedAt: Date;
}

export interface Event {
  id: number;
  title: string;
  description: string;
  startDate: Date;
  endDate?: Date;
  location: string;
  imageUrl?: string;
  maxParticipants?: number;
  currentParticipants: number;
  isPublished: boolean;
  isPublic: boolean;
  registrationRequired: boolean;
  registrationDeadline?: Date;
  createdAt: Date;
  updatedAt: Date;
}

export interface ContactMessage {
  id: number;
  name: string;
  email: string;
  phone?: string;
  subject: string;
  message: string;
  status: 'new' | 'read' | 'replied' | 'closed';
  createdAt: Date;
  repliedAt?: Date;
  reply?: string;
}

export interface Report {
  id: number;
  adherentId: number;
  title: string;
  description: string;
  category: 'incident' | 'complaint' | 'suggestion' | 'other';
  status: 'pending' | 'in_progress' | 'resolved' | 'closed';
  priority: 'low' | 'medium' | 'high' | 'urgent';
  createdAt: Date;
  updatedAt: Date;
  adminNotes?: string;
  resolution?: string;
}

export interface Subscription {
  id: number;
  adherentId: number;
  amount: number;
  paymentDate: Date;
  period: string; // e.g., "2024"
  status: 'paid' | 'pending' | 'overdue';
  paymentMethod: 'cash' | 'check' | 'transfer' | 'card';
  reference?: string;
}
