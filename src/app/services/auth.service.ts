import { Injectable, signal, WritableSignal, computed } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { User } from '../models/user.model';
import { environment } from '../../environments/environment';

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  // Signal de l'utilisateur courant (privé en écriture)
  private currentUserSig: WritableSignal<User | null> = signal<User | null>(null);

  // Signal public en lecture seule
  public readonly currentUser = this.currentUserSig.asReadonly();

  // Signaux calculés (Computed Signals)
  public readonly isLoggedIn = computed(() => !!this.currentUserSig());
  public readonly isAdmin = computed(() => this.currentUserSig()?.role === 'admin');
  public readonly isAdherent = computed(() => this.currentUserSig()?.role === 'adherent');

  private apiUrl = environment.apiUrl;

  constructor(private http: HttpClient) {
    // Check if user is logged in on service initialization
    this.checkAuthStatus();
  }

  // Helper pour compatibilité temporaire si nécessaire (à éviter si possible)
  getCurrentUser(): User | null {
    return this.currentUserSig();
  }

  login(email: string, password: string): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}auth/login.php`, { email, password })
      .pipe(
        map(response => {
          if (response.success && response.user) {
            localStorage.setItem('currentUser', JSON.stringify(response.user));
            localStorage.setItem('token', response.token);
            // Mise à jour du signal
            this.currentUserSig.set(response.user);
          }
          return response;
        })
      );
  }

  register(userData: any): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}auth/register.php`, userData)
      .pipe(
        map(response => {
          if (response.success && response.user) {
            localStorage.setItem('currentUser', JSON.stringify(response.user));
            localStorage.setItem('token', response.token);
            // Mise à jour du signal
            this.currentUserSig.set(response.user);
          }
          return response;
        })
      );
  }

  logout(): void {
    localStorage.removeItem('currentUser');
    localStorage.removeItem('token');
    // Mise à jour du signal
    this.currentUserSig.set(null);
  }

  private checkAuthStatus(): void {
    const user = localStorage.getItem('currentUser');
    if (user) {
      try {
        const parsedUser = JSON.parse(user);
        this.currentUserSig.set(parsedUser);
      } catch (error) {
        console.error('Error parsing stored user data:', error);
        this.logout();
      }
    }
  }

  updateProfile(userData: Partial<User>): Observable<any> {
    const user = this.currentUserSig();
    if (!user) {
      throw new Error('No user logged in');
    }

    return this.http.put(`${this.apiUrl}auth/profile.php`, {
      id: user.id,
      ...userData
    });
  }

  changePassword(currentPassword: string, newPassword: string): Observable<any> {
    const user = this.currentUserSig();
    if (!user) {
      throw new Error('No user logged in');
    }

    return this.http.put(`${this.apiUrl}auth/password.php`, {
      id: user.id,
      currentPassword,
      newPassword
    });
  }

  // Method to manually update the current user state (e.g. after profile update)
  setCurrentUser(user: User): void {
    this.currentUserSig.set(user);
    localStorage.setItem('currentUser', JSON.stringify(user));
  }
}
