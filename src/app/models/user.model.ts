export interface User {
  id: number;
  email: string;
  password: string;
  firstName: string;
  lastName: string;
  phone: string;
  address: string;
  city: string;
  postalCode: string;
  role: 'admin' | 'adherent';
  isActive: boolean;
  createdAt: Date;
  updatedAt: Date;
}

export interface Adherent extends User {
  memberNumber: string;
  joinDate: Date;
  subscriptionStatus: 'active' | 'expired' | 'pending';
  lastPaymentDate?: Date;
  nextPaymentDate?: Date;
  subscriptionAmount: number;
  emergencyContact: string;
  emergencyPhone: string;
}

export interface Admin extends User {
  permissions: string[];
  lastLogin?: Date;
}

// Les modèles Subscription, Report, ContactMessage, News et Event 
// sont définis dans association.model.ts pour éviter les conflits
