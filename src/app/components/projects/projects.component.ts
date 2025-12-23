import { Component, OnInit } from '@angular/core';
import { AssociationService } from '../../services/association.service';
import { Project } from '../../models/association.model';

@Component({
  selector: 'app-projects',
  standalone: false,
  templateUrl: './projects.component.html',
  styleUrls: ['./projects.component.scss']
})
export class ProjectsComponent implements OnInit {
  projects: Project[] = [];
  isLoading = false;
  filteredProjects: Project[] = [];
  selectedProject: Project | null = null;
  showDetailModal = false;
  statusFilter: string = 'all';
  categoryFilter: string = 'all';

  constructor(private associationService: AssociationService) { }

  ngOnInit() {
    this.loadProjects();
    this.initializeAnimations();
  }

  loadProjects() {
    this.isLoading = true;
    this.associationService.getProjects().subscribe({
      next: (response) => {
        console.log('API Response:', response);
        if (response.success && response.data) {
          // Mapper les données pour s'assurer que les types sont corrects
          this.projects = response.data.map((project: any) => ({
            ...project,
            startDate: project.startDate ? new Date(project.startDate) : null,
            endDate: project.endDate && project.endDate !== '0000-00-00' ? new Date(project.endDate) : null,
            isPublic: project.isPublic === 1 || project.isPublic === true,
            progress: project.progress || 0
          }));
          
          // Filtrer uniquement les projets publics
          this.applyFilters();
        } else {
          this.projects = [];
          this.filteredProjects = [];
        }
        this.isLoading = false;
      },
      error: (error) => {
        console.error('Error loading projects:', error);
        this.projects = [];
        this.filteredProjects = [];
        this.isLoading = false;
      }
    });
  }

  applyFilters() {
    this.filteredProjects = this.projects
      .filter(project => {
        // Filtrer par visibilité publique
        if (!project.isPublic) return false;
        
        // Filtrer par statut
        if (this.statusFilter !== 'all' && project.status !== this.statusFilter) {
          return false;
        }
        
        // Filtrer par catégorie
        if (this.categoryFilter !== 'all' && project.category !== this.categoryFilter) {
          return false;
        }
        
        return true;
      })
      .sort((a, b) => {
        // Trier par statut (en cours en premier, puis planifiés, puis terminés)
        const statusOrder: { [key: string]: number } = {
          'in_progress': 1,
          'planning': 2,
          'completed': 3,
          'cancelled': 4
        };
        return (statusOrder[a.status] || 99) - (statusOrder[b.status] || 99);
      });
  }

  onStatusFilterChange() {
    this.applyFilters();
  }

  onCategoryFilterChange() {
    this.applyFilters();
  }

  openProjectDetail(project: Project) {
    this.selectedProject = project;
    this.showDetailModal = true;
    document.body.style.overflow = 'hidden';
  }

  closeDetailModal() {
    this.showDetailModal = false;
    this.selectedProject = null;
    document.body.style.overflow = 'auto';
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

  getStatusLabel(status: string): string {
    const labels: { [key: string]: string } = {
      'planning': 'Planifié',
      'in_progress': 'En cours',
      'completed': 'Terminé',
      'cancelled': 'Annulé'
    };
    return labels[status] || status;
  }

  getCategoryLabel(category: string): string {
    const labels: { [key: string]: string } = {
      'culturel': 'Culturel',
      'sportif': 'Sportif',
      'social': 'Social',
      'environnement': 'Environnement',
      'autre': 'Autre'
    };
    return labels[category] || category;
  }

  getPriorityLabel(priority: string): string {
    const labels: { [key: string]: string } = {
      'low': 'Basse',
      'medium': 'Moyenne',
      'high': 'Haute',
      'urgent': 'Urgente'
    };
    return labels[priority] || priority;
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

