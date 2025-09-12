import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { AuthService } from '../../services/auth.service';

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.scss']
})
export class LoginComponent implements OnInit {
  loginForm: FormGroup;
  isSubmitting = false;
  errorMessage = '';
  showPassword = false;

  constructor(
    private fb: FormBuilder,
    private authService: AuthService,
    private router: Router
  ) {
    this.loginForm = this.fb.group({
      email: ['', [Validators.required, Validators.email]],
      password: ['', [Validators.required, Validators.minLength(6)]],
      rememberMe: [false]
    });
  }

  ngOnInit() {
    // Check if user is already logged in
    if (this.authService.isLoggedIn()) {
      this.redirectToUserSpace();
    }
    
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
    if (this.loginForm.valid && !this.isSubmitting) {
      this.isSubmitting = true;
      this.errorMessage = '';
      
      const { email, password, rememberMe } = this.loginForm.value;
      
      this.authService.login(email, password).subscribe({
        next: (response) => {
          this.isSubmitting = false;
          if (response.success) {
            if (rememberMe) {
              // Store login info for remember me functionality
              localStorage.setItem('rememberMe', 'true');
              localStorage.setItem('rememberedEmail', email);
            } else {
              localStorage.removeItem('rememberMe');
              localStorage.removeItem('rememberedEmail');
            }
            
            this.redirectToUserSpace();
          } else {
            this.errorMessage = response.message || 'Erreur de connexion';
          }
        },
        error: (error) => {
          this.isSubmitting = false;
          this.errorMessage = 'Une erreur est survenue. Veuillez réessayer.';
          console.error('Login error:', error);
        }
      });
    } else {
      this.markFormGroupTouched();
    }
  }

  redirectToUserSpace() {
    const user = this.authService.getCurrentUser();
    if (user) {
      if (user.role === 'admin') {
        this.router.navigate(['/admin']);
      } else {
        this.router.navigate(['/espace-adherent']);
      }
    } else {
      this.router.navigate(['/']);
    }
  }

  markFormGroupTouched() {
    Object.keys(this.loginForm.controls).forEach(key => {
      const control = this.loginForm.get(key);
      control?.markAsTouched();
    });
  }

  getFieldError(fieldName: string): string {
    const field = this.loginForm.get(fieldName);
    if (field?.errors && field.touched) {
      if (field.errors['required']) {
        return 'Ce champ est obligatoire';
      }
      if (field.errors['email']) {
        return 'Veuillez entrer une adresse email valide';
      }
      if (field.errors['minlength']) {
        return `Le mot de passe doit contenir au moins ${field.errors['minlength'].requiredLength} caractères`;
      }
    }
    return '';
  }

  isFieldInvalid(fieldName: string): boolean {
    const field = this.loginForm.get(fieldName);
    return !!(field?.invalid && field.touched);
  }

  togglePasswordVisibility() {
    this.showPassword = !this.showPassword;
  }

  goToHome() {
    this.router.navigate(['/']);
  }
}
