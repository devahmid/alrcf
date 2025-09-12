import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { AssociationService } from '../../services/association.service';

@Component({
  selector: 'app-contact',
  templateUrl: './contact.component.html',
  styleUrls: ['./contact.component.scss']
})
export class ContactComponent implements OnInit {
  contactForm: FormGroup;
  isSubmitting = false;
  submitMessage = '';
  submitSuccess = false;

  // Contact information
  contactInfo = {
    address: '123 Rue de la République, 75001 Paris',
    phone: '+33 1 23 45 67 89',
    email: 'contact@alrcf.fr',
    hours: 'Lundi - Vendredi: 9h00 - 18h00'
  };

  // Office locations
  offices = [
    {
      city: 'Paris',
      address: '123 Rue de la République, 75001 Paris',
      phone: '+33 1 23 45 67 89',
      email: 'paris@alrcf.fr',
      hours: 'Lun-Ven: 9h-18h'
    },
    {
      city: 'Lyon',
      address: '456 Avenue des Champs, 69000 Lyon',
      phone: '+33 4 12 34 56 78',
      email: 'lyon@alrcf.fr',
      hours: 'Lun-Ven: 9h-18h'
    },
    {
      city: 'Marseille',
      address: '789 Boulevard du Port, 13000 Marseille',
      phone: '+33 4 91 23 45 67',
      email: 'marseille@alrcf.fr',
      hours: 'Lun-Ven: 9h-18h'
    }
  ];

  // FAQ data
  faqs = [
    {
      question: 'Comment devenir membre de l\'ALRCF ?',
      answer: 'Pour devenir membre, vous pouvez remplir le formulaire d\'adhésion en ligne ou nous contacter directement. L\'adhésion est ouverte à tous les citoyens partageant nos valeurs.',
      icon: 'fas fa-user-plus'
    },
    {
      question: 'Quels sont les avantages d\'être membre ?',
      answer: 'En tant que membre, vous bénéficiez d\'un accès à notre espace adhérent, de réductions sur nos événements, de publications exclusives et de la possibilité de participer à nos décisions.',
      icon: 'fas fa-star'
    },
    {
      question: 'Comment puis-je participer aux activités ?',
      answer: 'Vous pouvez participer en vous inscrivant à nos événements via la plateforme, en rejoignant nos groupes de travail ou en proposant vos propres initiatives.',
      icon: 'fas fa-handshake'
    },
    {
      question: 'L\'association est-elle reconnue ?',
      answer: 'Oui, l\'ALRCF est une association loi 1901 reconnue d\'intérêt général. Nous sommes enregistrés auprès des autorités compétentes et nos comptes sont audités chaque année.',
      icon: 'fas fa-certificate'
    }
  ];

  constructor(
    private fb: FormBuilder,
    private associationService: AssociationService
  ) {
    this.contactForm = this.fb.group({
      name: ['', [Validators.required, Validators.minLength(2)]],
      email: ['', [Validators.required, Validators.email]],
      phone: ['', [Validators.pattern(/^[0-9+\-\s()]+$/)]],
      subject: ['', [Validators.required, Validators.minLength(5)]],
      message: ['', [Validators.required, Validators.minLength(20)]],
      consent: [false, Validators.requiredTrue]
    });
  }

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

  onSubmit() {
    if (this.contactForm.valid && !this.isSubmitting) {
      this.isSubmitting = true;
      this.submitMessage = '';
      
      const formData = this.contactForm.value;
      
      this.associationService.sendMessage(formData).subscribe({
        next: (response) => {
          this.isSubmitting = false;
          this.submitSuccess = true;
          this.submitMessage = 'Votre message a été envoyé avec succès ! Nous vous répondrons dans les plus brefs délais.';
          this.contactForm.reset();
        },
        error: (error) => {
          this.isSubmitting = false;
          this.submitSuccess = false;
          this.submitMessage = 'Une erreur est survenue lors de l\'envoi de votre message. Veuillez réessayer.';
          console.error('Error sending message:', error);
        }
      });
    } else {
      this.markFormGroupTouched();
    }
  }

  markFormGroupTouched() {
    Object.keys(this.contactForm.controls).forEach(key => {
      const control = this.contactForm.get(key);
      control?.markAsTouched();
    });
  }

  getFieldError(fieldName: string): string {
    const field = this.contactForm.get(fieldName);
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
        return 'Format de numéro de téléphone invalide';
      }
    }
    return '';
  }

  isFieldInvalid(fieldName: string): boolean {
    const field = this.contactForm.get(fieldName);
    return !!(field?.invalid && field.touched);
  }
}
