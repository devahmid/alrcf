import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { AuthService } from '../../services/auth.service';
import { AssociationService } from '../../services/association.service';
import { Adherent } from '../../models/user.model';
import { Subscription, Report, ContactMessage } from '../../models/association.model';

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
  
  // Data arrays
  subscriptions: Subscription[] = [];
  reports: Report[] = [];
  messages: ContactMessage[] = [];
  
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
    private associationService: AssociationService
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
  }

  ngOnInit() {
    this.loadUserData();
    this.loadData();
    this.initializeAnimations();
  }

  loadUserData() {
    const user = this.authService.getCurrentUser();
    if (user && user.role === 'adherent') {
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
        this.isLoading = false;
      },
      error: (error) => {
        console.error('Error loading messages:', error);
        this.isLoading = false;
      }
    });
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
              this.authService.currentUserSubject$.next(this.currentUser);
            }
            alert('Profil mis à jour avec succès !');
          } else {
            alert('Erreur lors de la mise à jour du profil');
          }
        },
        error: (error) => {
          this.isLoading = false;
          alert('Erreur lors de la mise à jour du profil');
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
            alert('Signalement envoyé avec succès !');
          } else {
            alert('Erreur lors de l\'envoi du signalement');
          }
        },
        error: (error) => {
          this.isLoading = false;
          alert('Erreur lors de l\'envoi du signalement');
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
            alert('Message envoyé avec succès !');
          } else {
            alert('Erreur lors de l\'envoi du message');
          }
        },
        error: (error) => {
          this.isLoading = false;
          alert('Erreur lors de l\'envoi du message');
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
}
