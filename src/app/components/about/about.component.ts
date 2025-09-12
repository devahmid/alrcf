import { Component, OnInit } from '@angular/core';

@Component({
  selector: 'app-about',
  templateUrl: './about.component.html',
  styleUrls: ['./about.component.scss']
})
export class AboutComponent implements OnInit {

  // Mission and vision data
  missionData = {
    title: 'Notre Mission',
    description: 'L\'ALRCF s\'engage à défendre les valeurs fondamentales de la République française : Liberté, Égalité, Fraternité. Nous œuvrons pour une société plus juste, plus solidaire et plus respectueuse des droits de chacun.',
    image: 'assets/images/mission.jpg'
  };

  visionData = {
    title: 'Notre Vision',
    description: 'Nous aspirons à un monde où chaque citoyen peut exercer pleinement ses droits et libertés, dans le respect de l\'autre et de l\'environnement. Un monde où la solidarité et l\'entraide sont les piliers de notre société.',
    image: 'assets/images/vision.jpg'
  };

  // Values data
  values = [
    {
      icon: 'fas fa-balance-scale',
      title: 'Justice',
      description: 'Nous défendons l\'égalité des droits et l\'accès à la justice pour tous, sans discrimination.',
      color: 'primary'
    },
    {
      icon: 'fas fa-heart',
      title: 'Solidarité',
      description: 'Nous croyons en la force du collectif et de l\'entraide pour construire une société meilleure.',
      color: 'success'
    },
    {
      icon: 'fas fa-shield-alt',
      title: 'Protection',
      description: 'Nous protégeons les droits des plus vulnérables et défendons les libertés individuelles.',
      color: 'warning'
    },
    {
      icon: 'fas fa-leaf',
      title: 'Durabilité',
      description: 'Nous nous engageons pour un avenir durable et respectueux de l\'environnement.',
      color: 'info'
    }
  ];

  // Team data
  teamMembers = [
    {
      name: 'Marie Dubois',
      position: 'Présidente',
      image: 'assets/images/team/marie-dubois.jpg',
      description: 'Avocate de formation, Marie s\'engage depuis 10 ans pour la défense des droits civiques.',
      social: {
        linkedin: '#',
        twitter: '#'
      }
    },
    {
      name: 'Jean Martin',
      position: 'Vice-Président',
      image: 'assets/images/team/jean-martin.jpg',
      description: 'Ancien fonctionnaire, Jean apporte son expertise en administration publique.',
      social: {
        linkedin: '#',
        twitter: '#'
      }
    },
    {
      name: 'Sophie Leroy',
      position: 'Secrétaire Générale',
      image: 'assets/images/team/sophie-leroy.jpg',
      description: 'Enseignante et militante, Sophie coordonne nos actions éducatives.',
      social: {
        linkedin: '#',
        twitter: '#'
      }
    },
    {
      name: 'Pierre Moreau',
      position: 'Trésorier',
      image: 'assets/images/team/pierre-moreau.jpg',
      description: 'Expert-comptable, Pierre gère les finances de l\'association avec rigueur.',
      social: {
        linkedin: '#',
        twitter: '#'
      }
    }
  ];

  // History timeline
  history = [
    {
      year: '2009',
      title: 'Fondation de l\'ALRCF',
      description: 'Création de l\'association par un groupe de citoyens engagés pour défendre les valeurs républicaines.',
      icon: 'fas fa-seedling'
    },
    {
      year: '2012',
      title: 'Première grande mobilisation',
      description: 'Organisation d\'une manifestation de 5000 personnes pour défendre les droits des travailleurs.',
      icon: 'fas fa-users'
    },
    {
      year: '2015',
      title: 'Expansion nationale',
      description: 'Ouverture de 15 antennes locales dans toute la France pour mieux servir nos adhérents.',
      icon: 'fas fa-map-marker-alt'
    },
    {
      year: '2018',
      title: 'Programme éducatif',
      description: 'Lancement d\'un programme d\'éducation civique dans les écoles et universités.',
      icon: 'fas fa-graduation-cap'
    },
    {
      year: '2021',
      title: 'Transition numérique',
      description: 'Modernisation de nos outils et création de cette plateforme numérique.',
      icon: 'fas fa-laptop'
    },
    {
      year: '2024',
      title: 'Aujourd\'hui',
      description: 'Plus de 1000 adhérents actifs et des projets ambitieux pour l\'avenir.',
      icon: 'fas fa-star'
    }
  ];

  // Statistics
  stats = [
    { number: '1000+', label: 'Adhérents', icon: 'fas fa-users' },
    { number: '15', label: 'Antennes locales', icon: 'fas fa-map-marker-alt' },
    { number: '50+', label: 'Actions par an', icon: 'fas fa-handshake' },
    { number: '15', label: 'Années d\'existence', icon: 'fas fa-history' }
  ];

  constructor() { }

  ngOnInit() {
    this.initializeAnimations();
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
}
