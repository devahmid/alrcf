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
  isLoading = true;

  // Hero section data
  heroData = {
    title: 'Bienvenue à l\'ALRCF',
    subtitle: 'Association pour la Liberté et la Responsabilité Civique Française',
    description: 'Rejoignez notre communauté engagée pour défendre les valeurs de liberté, d\'égalité et de fraternité.',
    image: 'assets/images/hero-bg.jpg'
  };

  // Features data
  features = [
    {
      icon: 'fas fa-users',
      title: 'Communauté Active',
      description: 'Plus de 1000 membres engagés dans la défense de nos valeurs',
      color: 'primary'
    },
    {
      icon: 'fas fa-handshake',
      title: 'Solidarité',
      description: 'Entraide et soutien mutuel entre tous nos adhérents',
      color: 'success'
    },
    {
      icon: 'fas fa-balance-scale',
      title: 'Justice',
      description: 'Défense des droits civiques et de la justice sociale',
      color: 'warning'
    },
    {
      icon: 'fas fa-heart',
      title: 'Engagement',
      description: 'Actions concrètes pour un monde plus juste et libre',
      color: 'danger'
    }
  ];

  // Statistics
  stats = [
    { number: '1000+', label: 'Membres', icon: 'fas fa-users' },
    { number: '50+', label: 'Événements', icon: 'fas fa-calendar' },
    { number: '15', label: 'Années d\'existence', icon: 'fas fa-history' },
    { number: '100%', label: 'Engagement', icon: 'fas fa-heart' }
  ];

  constructor(private associationService: AssociationService) { }

  ngOnInit() {
    this.loadData();
    this.initializeAnimations();
  }

  loadData() {
    this.isLoading = true;
    
    // Load news
    this.associationService.getNews().subscribe({
      next: (news) => {
        this.news = news.slice(0, 3); // Show only 3 latest news
      },
      error: (error) => {
        console.error('Error loading news:', error);
        this.news = [];
      }
    });

    // Load events
    this.associationService.getEvents().subscribe({
      next: (events) => {
        this.events = events.slice(0, 3); // Show only 3 upcoming events
        this.isLoading = false;
      },
      error: (error) => {
        console.error('Error loading events:', error);
        this.events = [];
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
}
