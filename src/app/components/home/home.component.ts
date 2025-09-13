import { Component, OnInit } from '@angular/core';
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

  // Statistics
  stats = {
    totalMembers: 248,
    activeProjects: 12,
    upcomingEvents: 3,
    announcements: 5
  };

  constructor(private associationService: AssociationService) { }

  ngOnInit() {
    this.loadData();
    this.initializeAnimations();
  }

  loadData() {
    this.isLoading = true;
    
    // Load news
    this.associationService.getNews().subscribe({
      next: (response) => {
        this.news = response.success ? response.data.slice(0, 3) : []; // Show only 3 latest news
      },
      error: (error) => {
        console.error('Error loading news:', error);
        this.news = [];
      }
    });

    // Load events
    this.associationService.getEvents().subscribe({
      next: (response) => {
        this.events = response.success ? response.data.slice(0, 3) : []; // Show only 3 upcoming events
      },
      error: (error) => {
        console.error('Error loading events:', error);
        this.events = [];
      },
      complete: () => {
        this.isLoading = false;
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
}