import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { AuthService } from '../../services/auth.service';
import { AssociationService } from '../../services/association.service';
import { ModalService } from '../../services/modal.service';
import { Adherent } from '../../models/user.model';
import { Subscription, Report, ContactMessage, Announcement } from '../../models/association.model';
import { take } from 'rxjs/operators';

@Component({
  selector: 'app-adherent-space',
  templateUrl: './adherent-space.component.html',
  styleUrls: ['./adherent-space.component.scss']
})
export class AdherentSpaceComponent implements OnInit {
  currentUser: Adherent | null = null;
  activeTab = 'profile';
  isLoading = false;

  // Profile form
  profileForm: FormGroup;

  // Report form
  reportForm: FormGroup;

  // Message form
  messageForm: FormGroup;

  // Announcement form
  announcementForm: FormGroup;
  editingAnnouncement: Announcement | null = null;
  announcementImageUrl: string | null = null;
  announcementImageFile: File | null = null;

  // Data arrays
  subscriptions: Subscription[] = [];
  reports: Report[] = [];
  messages: ContactMessage[] = [];
  announcements: Announcement[] = [];

  // Statistics
  stats = {
    totalSubscriptions: 0,
    activeSubscriptions: 0,
    pendingReports: 0,
    unreadMessages: 0
  };

  constructor(
    private fb: FormBuilder,
    private authService: AuthService,
    private associationService: AssociationService,
    private modalService: ModalService
  ) {
    this.profileForm = this.fb.group({
      firstName: ['', [Validators.required, Validators.minLength(2)]],
      lastName: ['', [Validators.required, Validators.minLength(2)]],
      email: ['', [Validators.required, Validators.email]],
      phone: ['', [Validators.pattern(/^[0-9+\-\s()]+$/)]],
      address: [''],
      city: [''],
      postalCode: ['', [Validators.pattern(/^[0-9]{5}$/)]],
      emergencyContact: [''],
      emergencyPhone: ['', [Validators.pattern(/^[0-9+\-\s()]+$/)]]
    });

    this.reportForm = this.fb.group({
      title: ['', [Validators.required, Validators.minLength(5)]],
      description: ['', [Validators.required, Validators.minLength(20)]],
      category: ['', Validators.required],
      priority: ['medium', Validators.required]
    });

    this.messageForm = this.fb.group({
      subject: ['', [Validators.required, Validators.minLength(5)]],
      message: ['', [Validators.required, Validators.minLength(20)]]
    });

    this.announcementForm = this.fb.group({
      title: ['', [Validators.required, Validators.minLength(5)]],
      description: ['', [Validators.required, Validators.minLength(20)]],
      category: ['', Validators.required],
      price: [''],
      contactPhone: [''],
      contactEmail: ['']
    });
  }

  ngOnInit() {
    this.loadUserData();
    this.loadData();
    this.initializeAnimations();
  }

  loadUserData() {
    const user = this.authService.getCurrentUser();
    if (user && (user.role === 'adherent' || user.role === 'admin')) {
      this.currentUser = user as Adherent;
      this.populateProfileForm();
    }
  }

  populateProfileForm() {
    if (this.currentUser) {
      this.profileForm.patchValue({
        firstName: this.currentUser.firstName,
        lastName: this.currentUser.lastName,
        email: this.currentUser.email,
        phone: this.currentUser.phone,
        address: this.currentUser.address,
        city: this.currentUser.city,
        postalCode: this.currentUser.postalCode,
        emergencyContact: this.currentUser.emergencyContact,
        emergencyPhone: this.currentUser.emergencyPhone
      });
    }
  }

  loadData() {
    this.isLoading = true;

    // Load subscriptions
    this.associationService.getSubscriptions(this.currentUser?.id).subscribe({
      next: (response) => {
        this.subscriptions = response.success ? response.data : [];
        this.updateStats();
      },
      error: (error) => {
        console.error('Error loading subscriptions:', error);
      }
    });

    // Load reports
    this.associationService.getReports(this.currentUser?.id).subscribe({
      next: (response) => {
        this.reports = response.success ? response.data : [];
        this.updateStats();
      },
      error: (error) => {
        console.error('Error loading reports:', error);
      }
    });

    // Load messages
    this.associationService.getMessages().subscribe({
      next: (response) => {
        this.messages = response.success ? response.data : [];
        this.updateStats();
      },
      error: (error) => {
        console.error('Error loading messages:', error);
      }
    });

    // Load announcements
    if (this.currentUser?.id) {
      this.associationService.getAnnouncements({ userId: this.currentUser.id }).subscribe({
        next: (response) => {
          this.announcements = response.success ? response.data : [];
          this.updateStats();
          this.isLoading = false;
        },
        error: (error) => {
          console.error('Error loading announcements:', error);
          this.announcements = [];
          this.isLoading = false;
        }
      });
    } else {
      this.isLoading = false;
    }
  }

  updateStats() {
    this.stats.totalSubscriptions = this.subscriptions.length;
    this.stats.activeSubscriptions = this.subscriptions.filter(s => s.status === 'paid').length;
    this.stats.pendingReports = this.reports.filter(r => r.status === 'pending').length;
    this.stats.unreadMessages = this.messages.filter(m => m.status === 'new').length;
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
  }

  onProfileSubmit() {
    if (this.profileForm.valid && this.currentUser) {
      this.isLoading = true;

      this.authService.updateProfile(this.profileForm.value).subscribe({
        next: (response) => {
          this.isLoading = false;
          if (response.success) {
            // Update current user data
            if (this.currentUser) {
              Object.assign(this.currentUser, this.profileForm.value);
              this.authService.setCurrentUser(this.currentUser);
            }
            this.modalService.success('Profil mis à jour avec succès !').pipe(take(1)).subscribe();
          } else {
            this.modalService.error('Erreur lors de la mise à jour du profil').pipe(take(1)).subscribe();
          }
        },
        error: (error) => {
          this.isLoading = false;
          this.modalService.error('Erreur lors de la mise à jour du profil').pipe(take(1)).subscribe();
          console.error('Profile update error:', error);
        }
      });
    } else {
      this.markFormGroupTouched(this.profileForm);
    }
  }

  onReportSubmit() {
    if (this.reportForm.valid && this.currentUser) {
      this.isLoading = true;

      const reportData = {
        ...this.reportForm.value,
        adherentId: this.currentUser.id
      };

      this.associationService.createReport(reportData).subscribe({
        next: (response) => {
          this.isLoading = false;
          if (response.success) {
            this.reportForm.reset();
            this.loadData(); // Reload data
            this.modalService.success('Signalement envoyé avec succès !').pipe(take(1)).subscribe();
          } else {
            this.modalService.error('Erreur lors de l\'envoi du signalement').pipe(take(1)).subscribe();
          }
        },
        error: (error) => {
          this.isLoading = false;
          this.modalService.error('Erreur lors de l\'envoi du signalement').pipe(take(1)).subscribe();
          console.error('Report creation error:', error);
        }
      });
    } else {
      this.markFormGroupTouched(this.reportForm);
    }
  }

  onMessageSubmit() {
    if (this.messageForm.valid && this.currentUser) {
      this.isLoading = true;

      const messageData = {
        ...this.messageForm.value,
        name: `${this.currentUser.firstName} ${this.currentUser.lastName}`,
        email: this.currentUser.email,
        phone: this.currentUser.phone
      };

      this.associationService.sendMessage(messageData).subscribe({
        next: (response) => {
          this.isLoading = false;
          if (response.success) {
            this.messageForm.reset();
            this.loadData(); // Reload data
            this.modalService.success('Message envoyé avec succès !').pipe(take(1)).subscribe();
          } else {
            this.modalService.error('Erreur lors de l\'envoi du message').pipe(take(1)).subscribe();
          }
        },
        error: (error) => {
          this.isLoading = false;
          this.modalService.error('Erreur lors de l\'envoi du message').pipe(take(1)).subscribe();
          console.error('Message sending error:', error);
        }
      });
    } else {
      this.markFormGroupTouched(this.messageForm);
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
        if (fieldName === 'phone' || fieldName === 'emergencyPhone') {
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


  getSubscriptionStatusClass(status: string): string {
    switch (status) {
      case 'paid': return 'status-paid';
      case 'pending': return 'status-pending';
      case 'overdue': return 'status-overdue';
      default: return 'status-unknown';
    }
  }

  getReportStatusClass(status: string): string {
    switch (status) {
      case 'pending': return 'status-pending';
      case 'in_progress': return 'status-progress';
      case 'resolved': return 'status-resolved';
      case 'closed': return 'status-closed';
      default: return 'status-unknown';
    }
  }

  getPriorityClass(priority: string): string {
    switch (priority) {
      case 'low': return 'priority-low';
      case 'medium': return 'priority-medium';
      case 'high': return 'priority-high';
      case 'urgent': return 'priority-urgent';
      default: return 'priority-medium';
    }
  }

  formatDate(date: Date | undefined): string {
    if (!date) return 'Non défini';
    return new Date(date).toLocaleDateString('fr-FR', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    });
  }

  // Announcement methods
  onAnnouncementImageSelected(event: any) {
    const file = event.target.files[0];
    if (file) {
      // Vérifier la taille (max 5MB)
      if (file.size > 5 * 1024 * 1024) {
        this.modalService.error('Le fichier est trop volumineux (max 5MB)').pipe(take(1)).subscribe();
        return;
      }

      // Vérifier le type
      const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
      if (!allowedTypes.includes(file.type)) {
        this.modalService.error('Type de fichier non autorisé. Formats acceptés: JPG, PNG, GIF, WEBP').pipe(take(1)).subscribe();
        return;
      }

      this.announcementImageFile = file;
      
      // Afficher un aperçu
      const reader = new FileReader();
      reader.onload = (e: any) => {
        this.announcementImageUrl = e.target.result;
      };
      reader.readAsDataURL(file);
    }
  }

  removeAnnouncementImage() {
    this.announcementImageUrl = null;
    this.announcementImageFile = null;
    const fileInput = document.getElementById('announcementImage') as HTMLInputElement;
    if (fileInput) {
      fileInput.value = '';
    }
  }

  onAnnouncementSubmit() {
    if (this.announcementForm.valid && this.currentUser) {
      this.isLoading = true;

      // Si une nouvelle image est sélectionnée, l'uploader d'abord
      if (this.announcementImageFile) {
        this.associationService.uploadAnnouncementImage(this.announcementImageFile).subscribe({
          next: (uploadResponse) => {
            if (uploadResponse.success) {
              // Créer/mettre à jour l'annonce avec l'URL de l'image
              this.submitAnnouncement(uploadResponse.url);
            } else {
              this.isLoading = false;
              this.modalService.error('Erreur lors de l\'upload de l\'image').pipe(take(1)).subscribe();
            }
          },
          error: (error) => {
            this.isLoading = false;
            this.modalService.error('Erreur lors de l\'upload de l\'image').pipe(take(1)).subscribe();
            console.error('Image upload error:', error);
          }
        });
      } else {
        // Pas d'image à uploader, soumettre directement
        this.submitAnnouncement(this.editingAnnouncement?.imageUrl || null);
      }
    } else {
      this.markFormGroupTouched(this.announcementForm);
    }
  }

  private submitAnnouncement(imageUrl: string | null) {
    const announcementData = {
      ...this.announcementForm.value,
      price: this.announcementForm.value.price ? parseFloat(this.announcementForm.value.price) : null,
      imageUrl: imageUrl
    };

    if (this.editingAnnouncement) {
      // Update existing announcement
      this.associationService.updateAnnouncement(this.editingAnnouncement.id, announcementData).subscribe({
        next: (response) => {
          this.isLoading = false;
          if (response.success) {
            this.announcementForm.reset();
            this.editingAnnouncement = null;
            this.announcementImageUrl = null;
            this.announcementImageFile = null;
            this.loadData();
            this.modalService.success('Annonce mise à jour avec succès ! Elle sera à nouveau validée par l\'administrateur.').pipe(take(1)).subscribe();
          } else {
            this.modalService.error('Erreur lors de la mise à jour de l\'annonce').pipe(take(1)).subscribe();
          }
        },
        error: (error) => {
          this.isLoading = false;
          this.modalService.error('Erreur lors de la mise à jour de l\'annonce').pipe(take(1)).subscribe();
          console.error('Announcement update error:', error);
        }
      });
    } else {
      // Create new announcement
      this.associationService.createAnnouncement(announcementData).subscribe({
        next: (response) => {
          this.isLoading = false;
          if (response.success) {
            this.announcementForm.reset();
            this.announcementImageUrl = null;
            this.announcementImageFile = null;
            this.loadData();
            this.modalService.success('Annonce créée avec succès ! Elle sera validée par l\'administrateur avant publication.').pipe(take(1)).subscribe();
          } else {
            this.modalService.error('Erreur lors de la création de l\'annonce').pipe(take(1)).subscribe();
          }
        },
        error: (error) => {
          this.isLoading = false;
          this.modalService.error('Erreur lors de la création de l\'annonce').pipe(take(1)).subscribe();
          console.error('Announcement creation error:', error);
        }
      });
    }
  }

  editAnnouncement(announcement: Announcement) {
    this.editingAnnouncement = announcement;
    this.announcementForm.patchValue({
      title: announcement.title,
      description: announcement.description,
      category: announcement.category,
      price: announcement.price || '',
      contactPhone: announcement.contactPhone || '',
      contactEmail: announcement.contactEmail || ''
    });
    this.announcementImageUrl = announcement.imageUrl || null;
    this.announcementImageFile = null;
    // Scroll to form
    setTimeout(() => {
      const formElement = document.querySelector('.announcement-form-container');
      if (formElement) {
        formElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    }, 100);
  }

  cancelEdit() {
    this.editingAnnouncement = null;
    this.announcementForm.reset();
    this.announcementImageUrl = null;
    this.announcementImageFile = null;
  }

  deleteAnnouncement(id: number) {
    this.modalService.confirmAction('Êtes-vous sûr de vouloir supprimer cette annonce ?', 'Confirmation de suppression').pipe(take(1)).subscribe((confirmed) => {
      if (confirmed) {
        this.isLoading = true;
        this.associationService.deleteAnnouncement(id).subscribe({
          next: (response) => {
            this.isLoading = false;
            if (response.success) {
              this.loadData();
              this.modalService.success('Annonce supprimée avec succès !').pipe(take(1)).subscribe();
            } else {
              this.modalService.error('Erreur lors de la suppression de l\'annonce').pipe(take(1)).subscribe();
            }
          },
          error: (error) => {
            this.isLoading = false;
            this.modalService.error('Erreur lors de la suppression de l\'annonce').pipe(take(1)).subscribe();
            console.error('Announcement deletion error:', error);
          }
        });
      }
    });
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
    if (!price) return 'Prix à convenir';
    return new Intl.NumberFormat('fr-FR', {
      style: 'currency',
      currency: 'EUR'
    }).format(price);
  }
}
