import { Component, OnInit } from '@angular/core';
import { AssociationService } from '../../services/association.service';
import { News } from '../../models/association.model';

@Component({
  selector: 'app-news',
  templateUrl: './news.component.html',
  styleUrls: ['./news.component.scss']
})
export class NewsComponent implements OnInit {
  news: News[] = [];
  isLoading = false;
  filteredNews: News[] = [];
  selectedCategory: string = 'all';
  selectedNews: News | null = null;
  showDetailModal = false;

  categories = [
    { value: 'all', label: 'Toutes' },
    { value: 'general', label: 'Général' },
    { value: 'event', label: 'Événement' },
    { value: 'announcement', label: 'Annonce' },
    { value: 'urgent', label: 'Urgent' }
  ];

  constructor(private associationService: AssociationService) { }

  ngOnInit() {
    this.loadNews();
    this.initializeAnimations();
  }

  loadNews() {
    this.isLoading = true;
    this.associationService.getNews().subscribe({
      next: (response) => {
        if (response.success && response.data) {
          this.news = response.data;
          this.filteredNews = this.news;
        } else {
          this.news = [];
          this.filteredNews = [];
        }
        this.isLoading = false;
      },
      error: (error) => {
        console.error('Error loading news:', error);
        this.news = [];
        this.filteredNews = [];
        this.isLoading = false;
      }
    });
  }

  filterByCategory(category: string) {
    this.selectedCategory = category;
    if (category === 'all') {
      this.filteredNews = this.news;
    } else {
      this.filteredNews = this.news.filter(article => article.category === category);
    }
  }

  formatDate(date: Date | string | undefined): string {
    if (!date) return 'Non défini';
    const dateObj = typeof date === 'string' ? new Date(date) : date;
    return dateObj.toLocaleDateString('fr-FR', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
  }

  getCategoryLabel(category: string): string {
    const cat = this.categories.find(c => c.value === category);
    return cat ? cat.label : category;
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
