export interface News {
  id: number;
  title: string;
  content: string;
  imageUrl?: string;
  videoUrl?: string;
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
  name?: string; // Pour compatibilité avec l'ancien format
  firstName?: string; // Format retourné par l'API
  lastName?: string; // Format retourné par l'API
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

export interface Announcement {
  id: number;
  userId: number;
  title: string;
  description: string;
  category: 'service' | 'emploi' | 'vente' | 'location' | 'autre';
  price?: number;
  contactPhone?: string;
  contactEmail?: string;
  imageUrl?: string;
  status: 'pending' | 'approved' | 'rejected' | 'expired';
  isPublic: boolean;
  approvedBy?: number;
  approvedAt?: Date;
  rejectionReason?: string;
  expiresAt?: Date;
  createdAt: Date;
  updatedAt: Date;
  // Champs joints depuis la base de données
  firstName?: string;
  lastName?: string;
  userEmail?: string;
  approvedByFirstName?: string;
  approvedByLastName?: string;
}

export interface Project {
  id: number;
  title: string;
  description: string;
  category: 'culturel' | 'sportif' | 'social' | 'environnement' | 'autre';
  status: 'planning' | 'in_progress' | 'completed' | 'cancelled';
  priority: 'low' | 'medium' | 'high' | 'urgent';
  startDate?: Date;
  endDate?: Date;
  budget?: number;
  imageUrl?: string;
  createdBy: number;
  assignedTo?: number;
  progress: number;
  isPublic: boolean;
  createdAt: Date;
  updatedAt: Date;
  // Champs joints depuis la base de données
  createdByFirstName?: string;
  createdByLastName?: string;
  assignedToFirstName?: string;
  assignedToLastName?: string;
}
