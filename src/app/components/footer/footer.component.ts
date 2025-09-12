import { Component, OnInit } from '@angular/core';

@Component({
  selector: 'app-footer',
  templateUrl: './footer.component.html',
  styleUrls: ['./footer.component.scss']
})
export class FooterComponent implements OnInit {

  // Footer data
  footerData = {
    association: {
      name: 'ALRCF',
      description: 'Association pour la Liberté et la Responsabilité Civique Française',
      address: '123 Rue de la République, 75001 Paris',
      phone: '+33 1 23 45 67 89',
      email: 'contact@alrcf.fr'
    },
    quickLinks: [
      { name: 'Accueil', route: '/' },
      { name: 'À propos', route: '/a-propos' },
      { name: 'Contact', route: '/contact' },
      { name: 'Connexion', route: '/connexion' }
    ],
    legal: [
      { name: 'Mentions légales', route: '/mentions-legales' },
      { name: 'Politique de confidentialité', route: '/confidentialite' },
      { name: 'CGU', route: '/cgu' },
      { name: 'Statuts', route: '/statuts' }
    ],
    social: [
      { name: 'Facebook', icon: 'fab fa-facebook-f', url: '#' },
      { name: 'Twitter', icon: 'fab fa-twitter', url: '#' },
      { name: 'LinkedIn', icon: 'fab fa-linkedin-in', url: '#' },
      { name: 'Instagram', icon: 'fab fa-instagram', url: '#' }
    ]
  };

  currentYear = new Date().getFullYear();

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

  scrollToTop() {
    window.scrollTo({
      top: 0,
      behavior: 'smooth'
    });
  }
}
