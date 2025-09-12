import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { AuthService } from '../../services/auth.service';
import { AssociationService } from '../../services/association.service';
import { Adherent, Admin } from '../../models/user.model';
import { News, Event, ContactMessage, Report, Subscription } from '../../models/association.model';

@Component({
  selector: 'app-admin-space',
  templateUrl: './admin-space.component.html',
  styleUrls: ['./admin-space.component.scss']
})
export class AdminSpaceComponent implements OnInit {
  currentUser: Admin | null = null;
  activeTab = 'dashboard';
  isLoading = false;
  
  // Data arrays
  adherents: Adherent[] = [];
  news: News[] = [];
  events: Event[] = [];
  messages: ContactMessage[] = [];
  reports: Report[] = [];
  subscriptions: Subscription[] = [];
  
  // Statistics
  stats = {
    totalAdherents: 0,
    activeAdherents: 0,
    totalNews: 0,
    totalEvents: 0,
    pendingMessages: 0,
    pendingReports: 0,
    totalSubscriptions: 0,
    monthlyRevenue: 0
  };
  
  // Forms
  adherentForm: FormGroup;
  newsForm: FormGroup;
  eventForm: FormGroup;
  
  // Filters and search
  searchTerm = '';
  statusFilter = 'all';
  dateFilter = 'all';
  
  // Pagination
  currentPage = 1;
  itemsPerPage = 10;
  totalItems = 0;

  constructor(
    private fb: FormBuilder,
    private authService: AuthService,
    private associationService: AssociationService
  ) {
    this.adherentForm = this.fb.group({
      firstName: ['', [Validators.required, Validators.minLength(2)]],
      lastName: ['', [Validators.required, Validators.minLength(2)]],
      email: ['', [Validators.required, Validators.email]],
      phone: ['', [Validators.pattern(/^[0-9+\-\s()]+$/)]],
      address: [''],
      city: [''],
      postalCode: ['', [Validators.pattern(/^[0-9]{5}$/)]],
      role: ['adherent', Validators.required],
      isActive: [true]
    });

    this.newsForm = this.fb.group({
      title: ['', [Validators.required, Validators.minLength(5)]],
      content: ['', [Validators.required, Validators.minLength(20)]],
      category: ['general', Validators.required],
      isPublished: [true]
    });

    this.eventForm = this.fb.group({
      title: ['', [Validators.required, Validators.minLength(5)]],
      description: ['', [Validators.required, Validators.minLength(20)]],
      startDate: ['', Validators.required],
      endDate: [''],
      location: ['', Validators.required],
      maxParticipants: [''],
      registrationRequired: [false],
      registrationDeadline: [''],
      isPublished: [true]
    });
  }

  ngOnInit() {
    this.loadUserData();
    this.loadData();
    this.initializeAnimations();
  }

  loadUserData() {
    const user = this.authService.getCurrentUser();
    if (user && user.role === 'admin') {
      this.currentUser = user as Admin;
    }
  }

  loadData() {
    this.isLoading = true;
    
    // Load all data
    Promise.all([
      this.loadAdherents(),
      this.loadNews(),
      this.loadEvents(),
      this.loadMessages(),
      this.loadReports(),
      this.loadSubscriptions()
    ]).finally(() => {
      this.isLoading = false;
      this.updateStats();
    });
  }

  loadAdherents() {
    return new Promise<void>((resolve) => {
      // This would be an API call to get all adherents
      // For now, we'll simulate with empty data
      this.adherents = [];
      this.totalItems = this.adherents.length;
      resolve();
    });
  }

  loadNews() {
    return new Promise<void>((resolve) => {
      this.associationService.getNews().subscribe({
        next: (news) => {
          this.news = news;
          resolve();
        },
        error: (error) => {
          console.error('Error loading news:', error);
          this.news = [];
          resolve();
        }
      });
    });
  }

  loadEvents() {
    return new Promise<void>((resolve) => {
      this.associationService.getEvents().subscribe({
        next: (events) => {
          this.events = events;
          resolve();
        },
        error: (error) => {
          console.error('Error loading events:', error);
          this.events = [];
          resolve();
        }
      });
    });
  }

  loadMessages() {
    return new Promise<void>((resolve) => {
      this.associationService.getMessages().subscribe({
        next: (messages) => {
          this.messages = messages;
          resolve();
        },
        error: (error) => {
          console.error('Error loading messages:', error);
          this.messages = [];
          resolve();
        }
      });
    });
  }

  loadReports() {
    return new Promise<void>((resolve) => {
      this.associationService.getReports().subscribe({
        next: (reports) => {
          this.reports = reports;
          resolve();
        },
        error: (error) => {
          console.error('Error loading reports:', error);
          this.reports = [];
          resolve();
        }
      });
    });
  }

  loadSubscriptions() {
    return new Promise<void>((resolve) => {
      this.associationService.getSubscriptions().subscribe({
        next: (subscriptions) => {
          this.subscriptions = subscriptions;
          resolve();
        },
        error: (error) => {
          console.error('Error loading subscriptions:', error);
          this.subscriptions = [];
          resolve();
        }
      });
    });
  }

  updateStats() {
    this.stats.totalAdherents = this.adherents.length;
    this.stats.activeAdherents = this.adherents.filter(a => a.isActive).length;
    this.stats.totalNews = this.news.length;
    this.stats.totalEvents = this.events.length;
    this.stats.pendingMessages = this.messages.filter(m => m.status === 'new').length;
    this.stats.pendingReports = this.reports.filter(r => r.status === 'pending').length;
    this.stats.totalSubscriptions = this.subscriptions.length;
    this.stats.monthlyRevenue = this.subscriptions
      .filter(s => s.status === 'paid' && this.isThisMonth(s.paymentDate))
      .reduce((sum, s) => sum + s.amount, 0);
  }

  isThisMonth(date: Date): boolean {
    const now = new Date();
    const checkDate = new Date(date);
    return now.getMonth() === checkDate.getMonth() && now.getFullYear() === checkDate.getFullYear();
  }

  initializeAnimations() {
    if (typeof (window as any).AOS !== 'undefined') {
      (window as any).AOS.init({
        duration: 1000,
        easing: 'ease-in-out',
        once: true,
        mirror: false
      });
    }
  }

  setActiveTab(tab: string) {
    this.activeTab = tab;
    this.currentPage = 1;
  }

  onAdherentSubmit() {
    if (this.adherentForm.valid) {
      this.isLoading = true;
      
      // This would be an API call to create/update adherent
      console.log('Creating adherent:', this.adherentForm.value);
      
      setTimeout(() => {
        this.isLoading = false;
        this.adherentForm.reset();
        this.loadData();
        alert('Adhérent créé avec succès !');
      }, 1000);
    } else {
      this.markFormGroupTouched(this.adherentForm);
    }
  }

  onNewsSubmit() {
    if (this.newsForm.valid) {
      this.isLoading = true;
      
      this.associationService.createNews(this.newsForm.value).subscribe({
        next: (response) => {
          this.isLoading = false;
          if (response.success) {
            this.newsForm.reset();
            this.loadData();
            alert('Actualité créée avec succès !');
          } else {
            alert('Erreur lors de la création de l\'actualité');
          }
        },
        error: (error) => {
          this.isLoading = false;
          alert('Erreur lors de la création de l\'actualité');
          console.error('News creation error:', error);
        }
      });
    } else {
      this.markFormGroupTouched(this.newsForm);
    }
  }

  onEventSubmit() {
    if (this.eventForm.valid) {
      this.isLoading = true;
      
      this.associationService.createEvent(this.eventForm.value).subscribe({
        next: (response) => {
          this.isLoading = false;
          if (response.success) {
            this.eventForm.reset();
            this.loadData();
            alert('Événement créé avec succès !');
          } else {
            alert('Erreur lors de la création de l\'événement');
          }
        },
        error: (error) => {
          this.isLoading = false;
          alert('Erreur lors de la création de l\'événement');
          console.error('Event creation error:', error);
        }
      });
    } else {
      this.markFormGroupTouched(this.eventForm);
    }
  }

  updateMessageStatus(messageId: number, status: string, reply?: string) {
    this.associationService.updateMessageStatus(messageId, status, reply).subscribe({
      next: (response) => {
        if (response.success) {
          this.loadData();
          alert('Message mis à jour avec succès !');
        } else {
          alert('Erreur lors de la mise à jour du message');
        }
      },
      error: (error) => {
        alert('Erreur lors de la mise à jour du message');
        console.error('Message update error:', error);
      }
    });
  }

  updateReportStatus(reportId: number, status: 'pending' | 'in_progress' | 'resolved' | 'closed', resolution?: string) {
    this.associationService.updateReport(reportId, { status, resolution }).subscribe({
      next: (response) => {
        if (response.success) {
          this.loadData();
          alert('Signalement mis à jour avec succès !');
        } else {
          alert('Erreur lors de la mise à jour du signalement');
        }
      },
      error: (error) => {
        alert('Erreur lors de la mise à jour du signalement');
        console.error('Report update error:', error);
      }
    });
  }

  deleteItem(type: string, id: number) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cet élément ?')) {
      let serviceCall;
      
      switch (type) {
        case 'news':
          serviceCall = this.associationService.deleteNews(id);
          break;
        case 'event':
          serviceCall = this.associationService.deleteEvent(id);
          break;
        default:
          return;
      }
      
      serviceCall.subscribe({
        next: (response) => {
          if (response.success) {
            this.loadData();
            alert('Élément supprimé avec succès !');
          } else {
            alert('Erreur lors de la suppression');
          }
        },
        error: (error) => {
          alert('Erreur lors de la suppression');
          console.error('Delete error:', error);
        }
      });
    }
  }

  markFormGroupTouched(form: FormGroup) {
    Object.keys(form.controls).forEach(key => {
      const control = form.get(key);
      control?.markAsTouched();
    });
  }

  getFieldError(form: FormGroup, fieldName: string): string {
    const field = form.get(fieldName);
    if (field?.errors && field.touched) {
      if (field.errors['required']) {
        return 'Ce champ est obligatoire';
      }
      if (field.errors['email']) {
        return 'Veuillez entrer une adresse email valide';
      }
      if (field.errors['minlength']) {
        return `Ce champ doit contenir au moins ${field.errors['minlength'].requiredLength} caractères`;
      }
      if (field.errors['pattern']) {
        if (fieldName === 'phone') {
          return 'Format de numéro de téléphone invalide';
        }
        if (fieldName === 'postalCode') {
          return 'Code postal invalide (5 chiffres)';
        }
      }
    }
    return '';
  }

  isFieldInvalid(form: FormGroup, fieldName: string): boolean {
    const field = form.get(fieldName);
    return !!(field?.invalid && field.touched);
  }

  formatDate(date: Date | undefined): string {
    if (!date) return 'Non défini';
    return new Date(date).toLocaleDateString('fr-FR', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    });
  }

  getStatusClass(status: string): string {
    switch (status) {
      case 'active': return 'status-active';
      case 'inactive': return 'status-inactive';
      case 'pending': return 'status-pending';
      case 'resolved': return 'status-resolved';
      case 'closed': return 'status-closed';
      case 'new': return 'status-new';
      case 'read': return 'status-read';
      case 'replied': return 'status-replied';
      default: return 'status-unknown';
    }
  }

  getFilteredData(): any[] {
    let data: any[] = [];
    
    switch (this.activeTab) {
      case 'adherents':
        data = this.adherents;
        break;
      case 'news':
        data = this.news;
        break;
      case 'events':
        data = this.events;
        break;
      case 'messages':
        data = this.messages;
        break;
      case 'reports':
        data = this.reports;
        break;
      case 'subscriptions':
        data = this.subscriptions;
        break;
    }
    
    // Apply search filter
    if (this.searchTerm) {
      data = data.filter((item: any) => 
        Object.values(item).some(value => 
          String(value).toLowerCase().includes(this.searchTerm.toLowerCase())
        )
      );
    }
    
    // Apply status filter
    if (this.statusFilter !== 'all') {
      data = data.filter((item: any) => item.status === this.statusFilter);
    }
    
    this.totalItems = data.length;
    return data;
  }

  getPaginatedData() {
    const filteredData = this.getFilteredData();
    const startIndex = (this.currentPage - 1) * this.itemsPerPage;
    const endIndex = startIndex + this.itemsPerPage;
    return filteredData.slice(startIndex, endIndex);
  }

  onPageChange(page: number) {
    this.currentPage = page;
  }

  getTotalPages(): number {
    return Math.ceil(this.totalItems / this.itemsPerPage);
  }
}
