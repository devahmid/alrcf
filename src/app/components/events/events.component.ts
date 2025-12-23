import { Component, OnInit } from '@angular/core';
import { AssociationService } from '../../services/association.service';
import { Event } from '../../models/association.model';

@Component({
  selector: 'app-events',
  standalone: false,
  templateUrl: './events.component.html',
  styleUrls: ['./events.component.scss']
})
export class EventsComponent implements OnInit {
  events: Event[] = [];
  isLoading = false;
  filteredEvents: Event[] = [];
  selectedEvent: Event | null = null;
  showDetailModal = false;

  constructor(private associationService: AssociationService) { }

  ngOnInit() {
    this.loadEvents();
    this.initializeAnimations();
  }

  loadEvents() {
    this.isLoading = true;
    this.associationService.getEvents().subscribe({
      next: (response) => {
        console.log('API Response:', response);
        if (response.success && response.data && Array.isArray(response.data)) {
          // Mapper les données pour s'assurer que les types sont corrects
          this.events = response.data.map((event: any) => ({
            ...event,
            startDate: event.startDate ? new Date(event.startDate) : null,
            endDate: event.endDate && event.endDate !== '0000-00-00 00:00:00' ? new Date(event.endDate) : null,
            isPublished: event.isPublished === 1 || event.isPublished === true,
            registrationRequired: event.registrationRequired === 1 || event.registrationRequired === true,
            currentParticipants: event.currentParticipants || 0,
            maxParticipants: event.maxParticipants || null
          }));
          
          // Filtrer uniquement les événements publiés
          this.filteredEvents = this.events
            .filter(event => event.isPublished)
            .sort((a, b) => {
              if (!a.startDate || !b.startDate) return 0;
              const dateA = new Date(a.startDate).getTime();
              const dateB = new Date(b.startDate).getTime();
              return dateA - dateB;
            });
          
          console.log('Events loaded:', this.events);
          console.log('Filtered events:', this.filteredEvents);
        } else {
          console.warn('No events data or invalid response:', response);
          this.events = [];
          this.filteredEvents = [];
        }
        this.isLoading = false;
      },
      error: (error) => {
        console.error('Error loading events:', error);
        this.events = [];
        this.filteredEvents = [];
        this.isLoading = false;
      }
    });
  }

  openEventDetail(event: Event) {
    this.selectedEvent = event;
    this.showDetailModal = true;
    document.body.style.overflow = 'hidden';
  }

  closeDetailModal() {
    this.showDetailModal = false;
    this.selectedEvent = null;
    document.body.style.overflow = 'auto';
  }

  formatDate(date: Date | string | undefined): string {
    if (!date) return 'Non défini';
    const dateObj = typeof date === 'string' ? new Date(date) : date;
    return dateObj.toLocaleDateString('fr-FR', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  }

  formatDateShort(date: Date | string | undefined): string {
    if (!date) return 'Non défini';
    const dateObj = typeof date === 'string' ? new Date(date) : date;
    return dateObj.toLocaleDateString('fr-FR', {
      day: 'numeric',
      month: 'short'
    });
  }

  getEventStatus(event: Event): string {
    const now = new Date();
    const eventDate = new Date(event.startDate);
    const daysUntil = Math.ceil((eventDate.getTime() - now.getTime()) / (1000 * 60 * 60 * 24));
    
    if (daysUntil < 0) return 'past';
    if (daysUntil <= 7) return 'soon';
    return 'upcoming';
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

