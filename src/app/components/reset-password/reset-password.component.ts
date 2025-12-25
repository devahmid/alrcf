import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Router, ActivatedRoute } from '@angular/router';
import { AuthService } from '../../services/auth.service';

@Component({
  selector: 'app-reset-password',
  templateUrl: './reset-password.component.html',
  styleUrls: ['./reset-password.component.scss']
})
export class ResetPasswordComponent implements OnInit {
  resetPasswordForm: FormGroup;
  isSubmitting = false;
  isLoading = true;
  errorMessage = '';
  successMessage = '';
  token = '';
  tokenValid = false;
  showPassword = false;
  showConfirmPassword = false;

  constructor(
    private fb: FormBuilder,
    private authService: AuthService,
    private router: Router,
    private route: ActivatedRoute
  ) {
    this.resetPasswordForm = this.fb.group({
      password: ['', [Validators.required, Validators.minLength(6)]],
      confirmPassword: ['', [Validators.required]]
    }, {
      validator: this.mustMatch('password', 'confirmPassword')
    });
  }

  ngOnInit() {
    // Récupérer le token depuis l'URL
    this.route.queryParams.subscribe(params => {
      this.token = params['token'] || '';
      if (this.token) {
        this.verifyToken();
      } else {
        this.isLoading = false;
        this.errorMessage = 'Token manquant. Veuillez utiliser le lien reçu par email.';
      }
    });
    this.initializeAnimations();
  }

  verifyToken() {
    this.authService.verifyResetToken(this.token).subscribe({
      next: (response) => {
        this.isLoading = false;
        if (response.success) {
          this.tokenValid = true;
        } else {
          this.tokenValid = false;
          this.errorMessage = response.message || 'Token invalide ou expiré. Veuillez demander un nouveau lien.';
        }
      },
      error: (error) => {
        this.isLoading = false;
        this.tokenValid = false;
        this.errorMessage = 'Erreur lors de la vérification du token. Veuillez réessayer.';
        console.error('Token verification error:', error);
      }
    });
  }

  onSubmit() {
    if (this.resetPasswordForm.valid && !this.isSubmitting && this.tokenValid) {
      this.isSubmitting = true;
      this.errorMessage = '';
      this.successMessage = '';

      const { password } = this.resetPasswordForm.value;

      this.authService.resetPassword(this.token, password).subscribe({
        next: (response) => {
          this.isSubmitting = false;
          if (response.success) {
            this.successMessage = response.message || 'Mot de passe réinitialisé avec succès.';
            // Rediriger vers la page de connexion après 3 secondes
            setTimeout(() => {
              this.router.navigate(['/connexion']);
            }, 3000);
          } else {
            this.errorMessage = response.message || 'Une erreur est survenue. Veuillez réessayer.';
          }
        },
        error: (error) => {
          this.isSubmitting = false;
          this.errorMessage = 'Une erreur est survenue. Veuillez réessayer.';
          console.error('Reset password error:', error);
        }
      });
    } else {
      this.markFormGroupTouched();
    }
  }

  mustMatch(controlName: string, matchingControlName: string) {
    return (formGroup: FormGroup) => {
      const control = formGroup.controls[controlName];
      const matchingControl = formGroup.controls[matchingControlName];

      if (matchingControl.errors && !matchingControl.errors['mustMatch']) {
        return;
      }

      if (control.value !== matchingControl.value) {
        matchingControl.setErrors({ mustMatch: true });
      } else {
        matchingControl.setErrors(null);
      }
    };
  }

  markFormGroupTouched() {
    Object.keys(this.resetPasswordForm.controls).forEach(key => {
      const control = this.resetPasswordForm.get(key);
      control?.markAsTouched();
    });
  }

  getFieldError(fieldName: string): string {
    const field = this.resetPasswordForm.get(fieldName);
    if (field?.errors && field.touched) {
      if (field.errors['required']) {
        return 'Ce champ est obligatoire';
      }
      if (field.errors['minlength']) {
        return `Le mot de passe doit contenir au moins ${field.errors['minlength'].requiredLength} caractères`;
      }
      if (field.errors['mustMatch']) {
        return 'Les mots de passe ne correspondent pas';
      }
    }
    return '';
  }

  isFieldInvalid(fieldName: string): boolean {
    const field = this.resetPasswordForm.get(fieldName);
    return !!(field?.invalid && field.touched);
  }

  togglePasswordVisibility() {
    this.showPassword = !this.showPassword;
  }

  toggleConfirmPasswordVisibility() {
    this.showConfirmPassword = !this.showConfirmPassword;
  }

  goToLogin(): void {
    this.router.navigate(['/connexion']);
  }

  goToForgotPassword(): void {
    this.router.navigate(['/forgot-password']);
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

