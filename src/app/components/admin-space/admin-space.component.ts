import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { AuthService } from '../../services/auth.service';
import { AssociationService } from '../../services/association.service';
import { ModalService } from '../../services/modal.service';
import { Adherent, Admin } from '../../models/user.model';
import { News, Event, ContactMessage, Report, Subscription, Announcement, Project } from '../../models/association.model';
import { take } from 'rxjs/operators';

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
  announcements: Announcement[] = [];
  filteredAnnouncements: Announcement[] = [];
  projects: Project[] = [];
  
  // Announcement management
  announcementStatusFilter: string = 'all';
  showRejectModal: boolean = false;
  rejectionReason: string = '';
  announcementToReject: Announcement | null = null;

  // Message reply management
  showReplyModal: boolean = false;
  messageToReply: ContactMessage | null = null;
  replyText: string = '';

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
  projectForm: FormGroup;
  
  // News image management
  newsImageFile: File | null = null;
  newsImageUrl: string | null = null;
  editingNews: News | null = null;
  
  // Event management
  editingEvent: Event | null = null;
  
  // Project management
  editingProject: Project | null = null;

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
    private associationService: AssociationService,
    private modalService: ModalService
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
      imageUrl: [''],
      videoUrl: [''],
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

    this.projectForm = this.fb.group({
      title: ['', [Validators.required, Validators.minLength(5)]],
      description: ['', [Validators.required, Validators.minLength(20)]],
      category: ['autre', Validators.required],
      status: ['planning', Validators.required],
      priority: ['medium', Validators.required],
      startDate: [''],
      endDate: [''],
      budget: [''],
      imageUrl: [''],
      assignedTo: [''],
      progress: [0, [Validators.min(0), Validators.max(100)]],
      isPublic: [true]
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
      this.loadSubscriptions(),
      this.loadAnnouncements(),
      this.loadProjects()
    ]).finally(() => {
      this.isLoading = false;
      this.updateStats();
    });
  }

  loadAdherents() {
    return new Promise<void>((resolve) => {
      // Use AssociationService to get users (we need to add this method or use HttpClient directly if we inject it)
      // Since we don't have HttpClient injected and AssociationService is where data logic lives:
      this.associationService.getUsers().subscribe({
        next: (response: any) => {
          this.adherents = response.success ? response.data : [];
          this.totalItems = this.adherents.length;
          resolve();
        },
        error: (error: any) => {
          console.error('Error loading adherents:', error);
          this.adherents = [];
          resolve();
        }
      });
    });
  }

  loadNews() {
    return new Promise<void>((resolve) => {
      this.associationService.getNews().subscribe({
        next: (response: any) => {
          this.news = response.success ? response.data : [];
          resolve();
        },
        error: (error: any) => {
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
        next: (response: any) => {
          this.events = response.success ? response.data : [];
          resolve();
        },
        error: (error: any) => {
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
        next: (response: any) => {
          this.messages = response.success ? response.data : [];
          resolve();
        },
        error: (error: any) => {
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
        next: (response: any) => {
          this.reports = response.success ? response.data : [];
          resolve();
        },
        error: (error: any) => {
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
        next: (response: any) => {
          this.subscriptions = response.success ? response.data : [];
          resolve();
        },
        error: (error: any) => {
          console.error('Error loading subscriptions:', error);
          this.subscriptions = [];
          resolve();
        }
      });
    });
  }

  loadAnnouncements() {
    return new Promise<void>((resolve) => {
      this.associationService.getAnnouncements().subscribe({
        next: (response: any) => {
          this.announcements = response.success ? response.data : [];
          this.applyAnnouncementFilter();
          resolve();
        },
        error: (error: any) => {
          console.error('Error loading announcements:', error);
          this.announcements = [];
          this.filteredAnnouncements = [];
          resolve();
        }
      });
    });
  }

  loadProjects() {
    return new Promise<void>((resolve) => {
      this.associationService.getProjects().subscribe({
        next: (response: any) => {
          this.projects = response.success ? response.data : [];
          resolve();
        },
        error: (error: any) => {
          console.error('Error loading projects:', error);
          this.projects = [];
          resolve();
        }
      });
    });
  }

  applyAnnouncementFilter() {
    if (this.announcementStatusFilter === 'all') {
      this.filteredAnnouncements = this.announcements;
    } else {
      this.filteredAnnouncements = this.announcements.filter(a => a.status === this.announcementStatusFilter);
    }
  }

  getPendingAnnouncementsCount(): number {
    return this.announcements.filter(a => a.status === 'pending').length;
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
        this.modalService.success('Adhérent créé avec succès !').pipe(take(1)).subscribe();
      }, 1000);
    } else {
      this.markFormGroupTouched(this.adherentForm);
    }
  }

  onNewsImageSelected(event: any) {
    const file = event.target.files[0];
    if (file) {
      // Vérifier le type de fichier
      const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
      if (!allowedTypes.includes(file.type)) {
        this.modalService.error('Type de fichier non autorisé. Formats acceptés: JPG, PNG, GIF, WEBP').pipe(take(1)).subscribe();
        return;
      }
      
      // Vérifier la taille (5MB max)
      if (file.size > 5 * 1024 * 1024) {
        this.modalService.error('Fichier trop volumineux. Taille maximale: 5MB').pipe(take(1)).subscribe();
        return;
      }
      
      this.newsImageFile = file;
      
      // Afficher un aperçu
      const reader = new FileReader();
      reader.onload = (e: any) => {
        this.newsImageUrl = e.target.result;
      };
      reader.readAsDataURL(file);
    }
  }

  removeNewsImage() {
    this.newsImageFile = null;
    this.newsImageUrl = null;
    this.newsForm.patchValue({ imageUrl: '' });
  }

  onNewsSubmit() {
    if (this.newsForm.valid) {
      this.isLoading = true;

      // Si une image a été sélectionnée, l'uploader d'abord
      if (this.newsImageFile) {
        this.associationService.uploadNewsImage(this.newsImageFile).subscribe({
          next: (uploadResponse: any) => {
            if (uploadResponse.success) {
              // Ajouter l'URL de l'image au formulaire
              this.newsForm.patchValue({ imageUrl: uploadResponse.imageUrl });
              
              // Créer l'actualité avec l'image
              this.createNewsArticle();
            } else {
              this.isLoading = false;
              this.modalService.error('Erreur lors de l\'upload de l\'image').pipe(take(1)).subscribe();
            }
          },
          error: (error: any) => {
            this.isLoading = false;
            this.modalService.error('Erreur lors de l\'upload de l\'image').pipe(take(1)).subscribe();
            console.error('Image upload error:', error);
          }
        });
      } else {
        // Pas d'image, créer directement l'actualité
        this.createNewsArticle();
      }
    } else {
      this.markFormGroupTouched(this.newsForm);
    }
  }

  editNews(news: News) {
    this.editingNews = news;
    this.newsForm.patchValue({
      title: news.title,
      content: news.content,
      category: news.category,
      imageUrl: news.imageUrl || '',
      videoUrl: news.videoUrl || '',
      isPublished: news.isPublished
    });
    
    // Charger l'image existante si disponible
    if (news.imageUrl) {
      this.newsImageUrl = news.imageUrl;
      this.newsImageFile = null; // Pas de nouveau fichier sélectionné
    } else {
      this.newsImageUrl = null;
      this.newsImageFile = null;
    }
    
    // Scroll vers le formulaire
    const formElement = document.querySelector('.lg\\:col-span-1');
    if (formElement) {
      formElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  }

  cancelNewsEdit() {
    this.editingNews = null;
    this.newsForm.reset();
    this.newsImageFile = null;
    this.newsImageUrl = null;
    this.newsForm.patchValue({
      category: 'general',
      isPublished: true
    });
  }

  private createNewsArticle() {
    const newsData = {
      ...this.newsForm.value,
      author: this.currentUser?.firstName + ' ' + this.currentUser?.lastName || 'Administrateur'
    };

    if (this.editingNews) {
      // Mode modification
      this.associationService.updateNews(this.editingNews.id, newsData).subscribe({
        next: (response: any) => {
          this.isLoading = false;
          if (response.success) {
            this.cancelNewsEdit();
            this.loadData();
            this.modalService.success('Actualité modifiée avec succès !').pipe(take(1)).subscribe();
          } else {
            this.modalService.error('Erreur lors de la modification de l\'actualité').pipe(take(1)).subscribe();
          }
        },
        error: (error: any) => {
          this.isLoading = false;
          this.modalService.error('Erreur lors de la modification de l\'actualité').pipe(take(1)).subscribe();
          console.error('News update error:', error);
        }
      });
    } else {
      // Mode création
      this.associationService.createNews(newsData).subscribe({
        next: (response: any) => {
          this.isLoading = false;
          if (response.success) {
            this.cancelNewsEdit();
            this.loadData();
            this.modalService.success('Actualité créée avec succès !').pipe(take(1)).subscribe();
          } else {
            this.modalService.error('Erreur lors de la création de l\'actualité').pipe(take(1)).subscribe();
          }
        },
        error: (error: any) => {
          this.isLoading = false;
          this.modalService.error('Erreur lors de la création de l\'actualité').pipe(take(1)).subscribe();
          console.error('News creation error:', error);
        }
      });
    }
  }

  editEvent(event: Event) {
    this.editingEvent = event;
    this.eventForm.patchValue({
      title: event.title,
      description: event.description,
      startDate: event.startDate ? new Date(event.startDate).toISOString().slice(0, 16) : '',
      endDate: event.endDate ? new Date(event.endDate).toISOString().slice(0, 16) : '',
      location: event.location,
      maxParticipants: event.maxParticipants || null,
      registrationRequired: event.registrationRequired || false,
      registrationDeadline: event.registrationDeadline ? new Date(event.registrationDeadline).toISOString().slice(0, 16) : '',
      isPublished: event.isPublished !== undefined ? event.isPublished : true
    });
    
    // Scroll vers le formulaire
    const formElement = document.querySelector('.lg\\:col-span-1');
    if (formElement) {
      formElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  }

  cancelEventEdit() {
    this.editingEvent = null;
    this.eventForm.reset();
    this.eventForm.patchValue({
      registrationRequired: false,
      isPublished: true
    });
  }

  onEventSubmit() {
    if (this.eventForm.valid) {
      this.isLoading = true;

      const eventData = { ...this.eventForm.value };

      if (this.editingEvent) {
        // Mode modification
        this.associationService.updateEvent(this.editingEvent.id, eventData).subscribe({
          next: (response: any) => {
            this.isLoading = false;
            if (response.success) {
              this.cancelEventEdit();
              this.loadData();
              this.modalService.success('Événement modifié avec succès !').pipe(take(1)).subscribe();
            } else {
              this.modalService.error('Erreur lors de la modification de l\'événement').pipe(take(1)).subscribe();
            }
          },
          error: (error: any) => {
            this.isLoading = false;
            this.modalService.error('Erreur lors de la modification de l\'événement').pipe(take(1)).subscribe();
            console.error('Event update error:', error);
          }
        });
      } else {
        // Mode création
        this.associationService.createEvent(eventData).subscribe({
          next: (response: any) => {
            this.isLoading = false;
            if (response.success) {
              this.cancelEventEdit();
              this.loadData();
              this.modalService.success('Événement créé avec succès !').pipe(take(1)).subscribe();
            } else {
              this.modalService.error('Erreur lors de la création de l\'événement').pipe(take(1)).subscribe();
            }
          },
          error: (error: any) => {
            this.isLoading = false;
            this.modalService.error('Erreur lors de la création de l\'événement').pipe(take(1)).subscribe();
            console.error('Event creation error:', error);
          }
        });
      }
    } else {
      this.markFormGroupTouched(this.eventForm);
    }
  }

  updateMessageStatus(messageId: number, status: string, reply?: string) {
    this.associationService.updateMessageStatus(messageId, status, reply).subscribe({
      next: (response: any) => {
        if (response.success) {
          this.loadData();
          this.modalService.success('Message mis à jour avec succès !').pipe(take(1)).subscribe();
        } else {
          this.modalService.error('Erreur lors de la mise à jour du message').pipe(take(1)).subscribe();
        }
      },
      error: (error: any) => {
        this.modalService.error('Erreur lors de la mise à jour du message').pipe(take(1)).subscribe();
        console.error('Message update error:', error);
      }
    });
  }

  updateReportStatus(reportId: number, status: 'pending' | 'in_progress' | 'resolved' | 'closed', resolution?: string) {
    this.associationService.updateReport(reportId, { status, resolution }).subscribe({
      next: (response: any) => {
        if (response.success) {
          this.loadData();
          this.modalService.success('Signalement mis à jour avec succès !').pipe(take(1)).subscribe();
        } else {
          this.modalService.error('Erreur lors de la mise à jour du signalement').pipe(take(1)).subscribe();
        }
      },
      error: (error: any) => {
        this.modalService.error('Erreur lors de la mise à jour du signalement').pipe(take(1)).subscribe();
        console.error('Report update error:', error);
      }
    });
  }

  deleteItem(type: string, id: number) {
    this.modalService.confirmAction('Êtes-vous sûr de vouloir supprimer cet élément ?', 'Confirmation de suppression').pipe(take(1)).subscribe((confirmed) => {
      if (confirmed) {
        let serviceCall;

        switch (type) {
          case 'news':
            serviceCall = this.associationService.deleteNews(id);
            break;
          case 'event':
            serviceCall = this.associationService.deleteEvent(id);
            break;
          case 'project':
            serviceCall = this.associationService.deleteProject(id);
            break;
          case 'adherent':
            serviceCall = this.associationService.deleteUser(id);
            break;
          case 'message':
            serviceCall = this.associationService.deleteMessage(id);
            break;
          default:
            return;
        }

        serviceCall.subscribe({
          next: (response: any) => {
            if (response.success) {
              this.loadData();
              this.modalService.success('Élément supprimé avec succès !').pipe(take(1)).subscribe();
            } else {
              this.modalService.error('Erreur lors de la suppression').pipe(take(1)).subscribe();
            }
          },
          error: (error: any) => {
            this.modalService.error('Erreur lors de la suppression').pipe(take(1)).subscribe();
            console.error('Delete error:', error);
          }
        });
      }
    });
  }

  openReplyModal(message: ContactMessage) {
    this.messageToReply = message;
    this.replyText = '';
    this.showReplyModal = true;
  }

  closeReplyModal() {
    this.showReplyModal = false;
    this.messageToReply = null;
    this.replyText = '';
  }

  sendReply() {
    if (!this.messageToReply || !this.replyText || this.replyText.trim().length === 0) {
      this.modalService.error('Veuillez saisir une réponse').pipe(take(1)).subscribe();
      return;
    }

    this.isLoading = true;
    this.associationService.updateMessageStatus(
      this.messageToReply.id,
      'replied',
      this.replyText.trim()
    ).subscribe({
      next: (response: any) => {
        this.isLoading = false;
        if (response.success) {
          this.closeReplyModal();
          this.loadData();
          
          // Afficher un message selon le résultat de l'envoi d'email
          if (response.email_sent) {
            this.modalService.success('Réponse envoyée avec succès ! Un email de notification a été envoyé à l\'utilisateur.').pipe(take(1)).subscribe();
          } else if (response.email_error) {
            this.modalService.warning('Réponse enregistrée mais l\'email n\'a pas pu être envoyé : ' + response.email_error).pipe(take(1)).subscribe();
          } else {
            this.modalService.success('Réponse envoyée avec succès !').pipe(take(1)).subscribe();
          }
        } else {
          this.modalService.error('Erreur lors de l\'envoi de la réponse').pipe(take(1)).subscribe();
        }
      },
      error: (error: any) => {
        this.isLoading = false;
        this.modalService.error('Erreur lors de l\'envoi de la réponse').pipe(take(1)).subscribe();
        console.error('Reply error:', error);
      }
    });
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

  // Announcement methods
  validateAnnouncement(id: number, action: 'approve' | 'reject') {
    if (action === 'approve') {
      this.associationService.validateAnnouncement(id, 'approve').subscribe({
        next: (response: any) => {
        if (response.success) {
          this.loadAnnouncements();
          this.modalService.success('Annonce approuvée avec succès !').pipe(take(1)).subscribe();
        } else {
          this.modalService.error('Erreur lors de l\'approbation de l\'annonce').pipe(take(1)).subscribe();
        }
      },
      error: (error: any) => {
        this.modalService.error('Erreur lors de l\'approbation de l\'annonce').pipe(take(1)).subscribe();
        console.error('Announcement validation error:', error);
      }
      });
    }
  }

  showRejectDialog(announcement: Announcement) {
    this.announcementToReject = announcement;
    this.rejectionReason = '';
    this.showRejectModal = true;
  }

  cancelReject() {
    this.showRejectModal = false;
    this.announcementToReject = null;
    this.rejectionReason = '';
  }

  confirmReject() {
    if (this.announcementToReject && this.rejectionReason.trim()) {
      this.associationService.validateAnnouncement(
        this.announcementToReject.id, 
        'reject', 
        this.rejectionReason.trim()
      ).subscribe({
        next: (response: any) => {
        if (response.success) {
          this.loadAnnouncements();
          this.cancelReject();
          this.modalService.success('Annonce rejetée avec succès !').pipe(take(1)).subscribe();
        } else {
          this.modalService.error('Erreur lors du rejet de l\'annonce').pipe(take(1)).subscribe();
        }
      },
      error: (error: any) => {
        this.modalService.error('Erreur lors du rejet de l\'annonce').pipe(take(1)).subscribe();
        console.error('Announcement rejection error:', error);
      }
      });
    }
  }

  deleteAnnouncement(id: number) {
    this.modalService.confirmAction('Êtes-vous sûr de vouloir supprimer cette annonce ?', 'Confirmation de suppression').pipe(take(1)).subscribe((confirmed) => {
      if (confirmed) {
        this.associationService.deleteAnnouncement(id).subscribe({
          next: (response: any) => {
            if (response.success) {
              this.loadAnnouncements();
              this.modalService.success('Annonce supprimée avec succès !').pipe(take(1)).subscribe();
            } else {
              this.modalService.error('Erreur lors de la suppression de l\'annonce').pipe(take(1)).subscribe();
            }
          },
          error: (error: any) => {
            this.modalService.error('Erreur lors de la suppression de l\'annonce').pipe(take(1)).subscribe();
            console.error('Announcement deletion error:', error);
          }
        });
      }
    });
  }

  viewAnnouncementDetails(announcement: Announcement) {
    // Afficher les détails dans une modale
    const details = `
<strong>Titre:</strong> ${announcement.title}<br>
<strong>Description:</strong> ${announcement.description}<br>
<strong>Catégorie:</strong> ${this.getCategoryLabel(announcement.category)}<br>
<strong>Prix:</strong> ${announcement.price ? this.formatPrice(announcement.price) : 'N/A'}<br>
<strong>Contact:</strong> ${announcement.contactPhone || announcement.contactEmail || 'N/A'}<br>
<strong>Statut:</strong> ${this.getStatusLabel(announcement.status)}<br>
<strong>Date de création:</strong> ${this.formatDate(announcement.createdAt)}<br>
${announcement.rejectionReason ? `<strong>Raison du rejet:</strong> ${announcement.rejectionReason}` : ''}
    `;
    this.modalService.show({
      title: 'Détails de l\'annonce',
      message: details,
      type: 'info',
      confirmText: 'Fermer',
      showCancel: false
    }).pipe(take(1)).subscribe();
  }

  getCategoryLabel(category: string): string {
    const labels: { [key: string]: string } = {
      'service': 'Service',
      'emploi': 'Emploi',
      'vente': 'Vente',
      'location': 'Location',
      'autre': 'Autre'
    };
    return labels[category] || category;
  }

  getStatusLabel(status: string): string {
    const labels: { [key: string]: string } = {
      'pending': 'En attente',
      'approved': 'Approuvée',
      'rejected': 'Rejetée',
      'expired': 'Expirée'
    };
    return labels[status] || status;
  }

  formatPrice(price?: number): string {
    if (!price) return 'N/A';
    return new Intl.NumberFormat('fr-FR', {
      style: 'currency',
      currency: 'EUR'
    }).format(price);
  }

  // User management methods
  updateUserRole(userId: number, event: any) {
    const newRole = event.target.value;
    const user = this.adherents.find(u => u.id === userId);
    
    if (!user) return;
    
    // Empêcher de retirer le rôle admin si c'est le dernier admin
    if (user.role === 'admin' && newRole === 'adherent') {
      const adminCount = this.adherents.filter(u => u.role === 'admin' && u.id !== userId).length;
      if (adminCount === 0) {
        this.modalService.warning('Impossible de retirer le rôle administrateur : il doit y avoir au moins un administrateur.').pipe(take(1)).subscribe();
        event.target.value = user.role; // Réinitialiser la valeur
        return;
      }
    }
    
    this.modalService.confirmAction(
      `Êtes-vous sûr de vouloir changer le rôle de ${user.firstName} ${user.lastName} en ${newRole === 'admin' ? 'Administrateur' : 'Adhérent'} ?`,
      'Changement de rôle'
    ).pipe(take(1)).subscribe((confirmed) => {
      if (!confirmed) {
        event.target.value = user.role; // Réinitialiser si annulé
        return;
      }
      this.isLoading = true;
      
      this.associationService.updateUser({
        id: userId,
        role: newRole
      }).subscribe({
        next: (response: any) => {
          this.isLoading = false;
          if (response.success) {
            // Mettre à jour localement
            user.role = newRole;
            this.loadData(); // Recharger pour s'assurer de la cohérence
            this.modalService.success('Rôle mis à jour avec succès !').pipe(take(1)).subscribe();
          } else {
            this.modalService.error('Erreur lors de la mise à jour du rôle').pipe(take(1)).subscribe();
            event.target.value = user.role; // Réinitialiser en cas d'erreur
          }
        },
        error: (error: any) => {
          this.isLoading = false;
          this.modalService.error('Erreur lors de la mise à jour du rôle').pipe(take(1)).subscribe();
          console.error('Role update error:', error);
          event.target.value = user.role; // Réinitialiser en cas d'erreur
        }
      });
    });
  }

  updateUserStatus(userId: number, event: any) {
    const newStatus = event.target.checked;
    const user = this.adherents.find(u => u.id === userId);
    
    if (!user) return;
    
    // Empêcher de désactiver le dernier admin
    if (!newStatus && user.role === 'admin') {
      const activeAdminCount = this.adherents.filter(u => u.role === 'admin' && u.isActive && u.id !== userId).length;
      if (activeAdminCount === 0) {
        this.modalService.warning('Impossible de désactiver : il doit y avoir au moins un administrateur actif.').pipe(take(1)).subscribe();
        event.target.checked = true; // Réinitialiser
        return;
      }
    }
    
    this.isLoading = true;
    
    this.associationService.updateUser({
      id: userId,
      isActive: newStatus
    }).subscribe({
      next: (response: any) => {
        this.isLoading = false;
        if (response.success) {
          // Mettre à jour localement
          user.isActive = newStatus;
          this.updateStats();
          this.modalService.success(`Utilisateur ${newStatus ? 'activé' : 'désactivé'} avec succès !`).pipe(take(1)).subscribe();
        } else {
          this.modalService.error('Erreur lors de la mise à jour du statut').pipe(take(1)).subscribe();
          event.target.checked = !newStatus; // Réinitialiser en cas d'erreur
        }
      },
      error: (error: any) => {
        this.isLoading = false;
        this.modalService.error('Erreur lors de la mise à jour du statut').pipe(take(1)).subscribe();
        console.error('Status update error:', error);
        event.target.checked = !newStatus; // Réinitialiser en cas d'erreur
      }
    });
  }

  // Project management methods
  editProject(project: Project) {
    this.editingProject = project;
    this.projectForm.patchValue({
      title: project.title,
      description: project.description,
      category: project.category,
      status: project.status,
      priority: project.priority,
      startDate: project.startDate ? new Date(project.startDate).toISOString().slice(0, 10) : '',
      endDate: project.endDate ? new Date(project.endDate).toISOString().slice(0, 10) : '',
      budget: project.budget || null,
      imageUrl: project.imageUrl || '',
      assignedTo: project.assignedTo || null,
      progress: project.progress || 0,
      isPublic: project.isPublic !== false
    });
  }

  cancelProjectEdit() {
    this.editingProject = null;
    this.projectForm.reset();
    this.projectForm.patchValue({
      category: 'autre',
      status: 'planning',
      priority: 'medium',
      progress: 0,
      isPublic: true
    });
  }

  onProjectSubmit() {
    if (this.projectForm.valid) {
      this.isLoading = true;

      const projectData = { ...this.projectForm.value };
      if (projectData.assignedTo === '') {
        projectData.assignedTo = null;
      }

      if (this.editingProject) {
        // Mode modification
        this.associationService.updateProject(this.editingProject.id, projectData).subscribe({
          next: (response: any) => {
            this.isLoading = false;
            if (response.success) {
              this.cancelProjectEdit();
              this.loadData();
              this.modalService.success('Projet modifié avec succès !').pipe(take(1)).subscribe();
            } else {
              this.modalService.error('Erreur lors de la modification du projet').pipe(take(1)).subscribe();
            }
          },
          error: (error: any) => {
            this.isLoading = false;
            this.modalService.error('Erreur lors de la modification du projet').pipe(take(1)).subscribe();
            console.error('Project update error:', error);
          }
        });
      } else {
        // Mode création
        this.associationService.createProject(projectData).subscribe({
          next: (response: any) => {
            this.isLoading = false;
            if (response.success) {
              this.cancelProjectEdit();
              this.loadData();
              this.modalService.success('Projet créé avec succès !').pipe(take(1)).subscribe();
            } else {
              this.modalService.error('Erreur lors de la création du projet').pipe(take(1)).subscribe();
            }
          },
          error: (error: any) => {
            this.isLoading = false;
            this.modalService.error('Erreur lors de la création du projet').pipe(take(1)).subscribe();
            console.error('Project creation error:', error);
          }
        });
      }
    } else {
      this.markFormGroupTouched(this.projectForm);
    }
  }
}
