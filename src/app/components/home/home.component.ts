import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { AssociationService } from '../../services/association.service';
import { News, Event } from '../../models/association.model';

@Component({
  selector: 'app-home',
  templateUrl: './home.component.html',
  styleUrls: ['./home.component.scss']
})
export class HomeComponent implements OnInit {
  news: News[] = [];
  events: Event[] = [];
  isLoading = false;
  selectedNews: News | null = null;
  showDetailModal = false;
  selectedEvent: Event | null = null;
  showEventDetailModal = false;

  // Statistics
  stats = {
    totalMembers: 0,
    activeProjects: 0,
    upcomingEvents: 0,
    announcements: 0
  };

  constructor(
    private associationService: AssociationService,
    private router: Router
  ) { }

  ngOnInit() {
    this.loadData();
    this.loadStatistics();
    this.initializeAnimations();
  }

  loadStatistics() {
    // Load members count (would need a new endpoint or use existing users endpoint)
    // For now, we'll load projects, events, and announcements
    
    // Load projects
    this.associationService.getProjects().subscribe({
      next: (response) => {
        if (response.success && response.data) {
          this.stats.activeProjects = response.data.filter((p: any) => 
            p.status === 'in_progress' || p.status === 'planning'
          ).length;
        }
      },
      error: (error) => {
        console.error('Error loading projects:', error);
      }
    });

    // Load events
    this.associationService.getEvents().subscribe({
      next: (response) => {
        if (response.success && response.data) {
          const now = new Date();
          this.stats.upcomingEvents = response.data.filter((e: any) => {
            const eventDate = new Date(e.startDate);
            return eventDate >= now && e.isPublished;
          }).length;
        }
      },
      error: (error) => {
        console.error('Error loading events:', error);
      }
    });

    // Load announcements
    this.associationService.getAnnouncements().subscribe({
      next: (response) => {
        if (response.success && response.data) {
          this.stats.announcements = response.data.filter((a: any) => 
            a.status === 'approved' && a.isPublic
          ).length;
        }
      },
      error: (error) => {
        console.error('Error loading announcements:', error);
      }
    });
  }

  loadData() {
    this.isLoading = true;
    let newsLoaded = false;
    let eventsLoaded = false;

    const checkComplete = () => {
      if (newsLoaded && eventsLoaded) {
        this.isLoading = false;
      }
    };

    // Load news
    this.associationService.getNews().subscribe({
      next: (response) => {
        this.news = response.success && response.data ? response.data.slice(0, 3) : []; // Show only 3 latest news
        newsLoaded = true;
        checkComplete();
      },
      error: (error) => {
        console.error('Error loading news:', error);
        this.news = [];
        newsLoaded = true;
        checkComplete();
      }
    });

    // Load events
    this.associationService.getEvents().subscribe({
      next: (response) => {
        this.events = response.success && response.data ? response.data.slice(0, 3) : []; // Show only 3 upcoming events
        eventsLoaded = true;
        checkComplete();
      },
      error: (error) => {
        console.error('Error loading events:', error);
        this.events = [];
        eventsLoaded = true;
        checkComplete();
      }
    });
  }

  initializeAnimations() {
    // Initialize AOS animations
    if (typeof (window as any).AOS !== 'undefined') {
      (window as any).AOS.init({
        duration: 1000,
        easing: 'ease-in-out',
        once: true,
        mirror: false
      });
    }
  }

  getEventStatus(event: Event): string {
    const now = new Date();
    const eventDate = new Date(event.startDate);

    if (eventDate < now) {
      return 'past';
    } else if (eventDate.getTime() - now.getTime() < 7 * 24 * 60 * 60 * 1000) {
      return 'soon';
    } else {
      return 'upcoming';
    }
  }

  formatDate(date: Date | undefined): string {
    if (!date) return 'Non défini';
    return new Date(date).toLocaleDateString('fr-FR', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
  }

  scrollToSection(sectionId: string) {
    const element = document.getElementById(sectionId);
    if (element) {
      element.scrollIntoView({ behavior: 'smooth' });
    }
  }

  navigateTo(path: string) {
    this.router.navigate([path]);
  }

  openNewsDetail(news: News) {
    this.selectedNews = news;
    this.showDetailModal = true;
    // Empêcher le scroll du body quand la modal est ouverte
    document.body.style.overflow = 'hidden';
  }

  closeDetailModal() {
    this.showDetailModal = false;
    this.selectedNews = null;
    // Réactiver le scroll du body
    document.body.style.overflow = 'auto';
  }

  getCategoryLabel(category: string): string {
    const categories: { [key: string]: string } = {
      'general': 'Général',
      'event': 'Événement',
      'announcement': 'Annonce',
      'urgent': 'Urgent'
    };
    return categories[category] || category;
  }

  openEventDetail(event: Event) {
    this.selectedEvent = event;
    this.showEventDetailModal = true;
    document.body.style.overflow = 'hidden';
  }

  closeEventDetailModal() {
    this.showEventDetailModal = false;
    this.selectedEvent = null;
    document.body.style.overflow = 'auto';
  }
}