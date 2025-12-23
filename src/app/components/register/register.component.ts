import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { AuthService } from '../../services/auth.service';

@Component({
    selector: 'app-register',
    templateUrl: './register.component.html'
})
export class RegisterComponent implements OnInit {
    registerForm: FormGroup;
    isSubmitting = false;
    errorMessage = '';
    showPassword = false;

    constructor(
        private formBuilder: FormBuilder,
        private authService: AuthService,
        private router: Router
    ) {
        this.registerForm = this.formBuilder.group({
            firstName: ['', [Validators.required]],
            lastName: ['', [Validators.required]],
            email: ['', [Validators.required, Validators.email]],
            phone: ['', [Validators.pattern('^[0-9+ ]*$')]],
            address: [''],
            password: ['', [Validators.required, Validators.minLength(6)]],
            confirmPassword: ['', [Validators.required]]
        }, {
            validator: this.mustMatch('password', 'confirmPassword')
        });
    }

    ngOnInit(): void {
        if (this.authService.isLoggedIn()) {
            this.router.navigate(['/espace-adherent']);
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

    isFieldInvalid(fieldName: string): boolean {
        const field = this.registerForm.get(fieldName);
        return field ? (field.invalid && (field.dirty || field.touched)) : false;
    }

    getFieldError(fieldName: string): string {
        const field = this.registerForm.get(fieldName);
        if (field?.hasError('required')) return 'Ce champ est requis';
        if (field?.hasError('email')) return 'Email invalide';
        if (field?.hasError('minlength')) return 'Le mot de passe doit contenir au moins 6 caractères';
        if (field?.hasError('mustMatch')) return 'Les mots de passe ne correspondent pas';
        if (field?.hasError('pattern')) return 'Format invalide';
        return '';
    }

    togglePasswordVisibility(): void {
        this.showPassword = !this.showPassword;
    }

    onSubmit(): void {
        if (this.registerForm.invalid) {
            Object.keys(this.registerForm.controls).forEach(key => {
                const control = this.registerForm.get(key);
                control?.markAsTouched();
            });
            return;
        }

        this.isSubmitting = true;
        this.errorMessage = '';

        this.authService.register(this.registerForm.value).subscribe({
            next: (response) => {
                if (response.success) {
                    this.router.navigate(['/espace-adherent']);
                } else {
                    this.errorMessage = response.message || 'Une erreur est survenue';
                    this.isSubmitting = false;
                }
            },
            error: (error) => {
                console.error('Registration error:', error);
                this.errorMessage = 'Erreur de connexion au serveur';
                this.isSubmitting = false;
            }
        });
    }

    goToLogin(): void {
        this.router.navigate(['/connexion']);
    }
}
