import { Component, OnInit, effect } from '@angular/core';
import { AssociationService } from '../../services/association.service';
import { AuthService } from '../../services/auth.service';
import { Announcement } from '../../models/association.model';

@Component({
  selector: 'app-announcements',
  templateUrl: './announcements.component.html',
  styleUrls: ['./announcements.component.scss']
})
export class AnnouncementsComponent implements OnInit {
  announcements: Announcement[] = [];
  filteredAnnouncements: Announcement[] = [];
  isLoading = false;
  selectedAnnouncement: Announcement | null = null;
  showDetailModal = false;
  isLoggedIn = false;
  
  // Filtres
  selectedCategory: string = 'all';
  searchTerm: string = '';
  
  categories = [
    { value: 'all', label: 'Toutes les catégories' },
    { value: 'service', label: 'Services' },
    { value: 'emploi', label: 'Emploi' },
    { value: 'vente', label: 'Vente' },
    { value: 'location', label: 'Location' },
    { value: 'autre', label: 'Autre' }
  ];

  constructor(
    private associationService: AssociationService,
    private authService: AuthService
  ) {
    // Utiliser effect() pour réagir aux changements du signal
    effect(() => {
      this.isLoggedIn = this.authService.isLoggedIn();
    });
  }

  ngOnInit() {
    this.isLoggedIn = this.authService.isLoggedIn();
    this.loadAnnouncements();
    this.initializeAnimations();
  }

  openAnnouncementDetail(announcement: Announcement) {
    this.selectedAnnouncement = announcement;
    this.showDetailModal = true;
  }

  closeDetailModal() {
    this.showDetailModal = false;
    this.selectedAnnouncement = null;
  }

  loadAnnouncements() {
    this.isLoading = true;
    
    this.associationService.getAnnouncements().subscribe({
      next: (response) => {
        if (response.success) {
          this.announcements = response.data || [];
          this.applyFilters();
        } else {
          this.announcements = [];
        }
        this.isLoading = false;
      },
      error: (error) => {
        console.error('Error loading announcements:', error);
        this.announcements = [];
        this.isLoading = false;
      }
    });
  }

  applyFilters() {
    this.filteredAnnouncements = this.announcements.filter(announcement => {
      // Filtre par catégorie
      const categoryMatch = this.selectedCategory === 'all' || announcement.category === this.selectedCategory;
      
      // Filtre par recherche
      const searchMatch = !this.searchTerm || 
        announcement.title.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
        announcement.description.toLowerCase().includes(this.searchTerm.toLowerCase());
      
      return categoryMatch && searchMatch;
    });
  }

  onCategoryChange() {
    this.applyFilters();
  }

  onSearchChange() {
    this.applyFilters();
  }

  getCategoryLabel(category: string): string {
    const cat = this.categories.find(c => c.value === category);
    return cat ? cat.label : category;
  }

  getCategoryIcon(category: string): string {
    switch (category) {
      case 'service':
        return 'fa-tools';
      case 'emploi':
        return 'fa-briefcase';
      case 'vente':
        return 'fa-shopping-cart';
      case 'location':
        return 'fa-home';
      default:
        return 'fa-tag';
    }
  }

  formatPrice(price?: number): string {
    if (!price) return 'Prix à convenir';
    return new Intl.NumberFormat('fr-FR', {
      style: 'currency',
      currency: 'EUR'
    }).format(price);
  }

  formatDate(date: Date | string | undefined): string {
    if (!date) return '';
    return new Date(date).toLocaleDateString('fr-FR', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
  }

  getContactInfo(announcement: Announcement): string {
    if (!this.isLoggedIn) {
      return 'Connectez-vous pour voir les coordonnées';
    }
    if (announcement.contactPhone) {
      return `Tél: ${announcement.contactPhone}`;
    }
    if (announcement.contactEmail) {
      return `Email: ${announcement.contactEmail}`;
    }
    return 'Contact via le site';
  }

  hasContactInfo(announcement: Announcement): boolean {
    return !!(announcement.contactPhone || announcement.contactEmail);
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
}
