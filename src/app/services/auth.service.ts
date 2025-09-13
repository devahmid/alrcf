import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject, Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { User, Adherent, Admin } from '../models/user.model';
import { environment } from '../../environments/environment';

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private currentUserSubject = new BehaviorSubject<User | null>(null);
  public currentUser$ = this.currentUserSubject.asObservable();
  
  // Exposer le BehaviorSubject pour les mises à jour directes
  get currentUserSubject$() {
    return this.currentUserSubject;
  }
  
  private apiUrl = environment.apiUrl;

  constructor(private http: HttpClient) {
    // Check if user is logged in on service initialization
    this.checkAuthStatus();
  }

  login(email: string, password: string): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}auth/login.php`, { email, password })
      .pipe(
        map(response => {
          if (response.success && response.user) {
            localStorage.setItem('currentUser', JSON.stringify(response.user));
            localStorage.setItem('token', response.token);
            this.currentUserSubject.next(response.user);
          }
          return response;
        })
      );
  }

  logout(): void {
    localStorage.removeItem('currentUser');
    localStorage.removeItem('token');
    this.currentUserSubject.next(null);
  }

  getCurrentUser(): User | null {
    return this.currentUserSubject.value;
  }

  isLoggedIn(): boolean {
    return this.currentUserSubject.value !== null;
  }

  isAdmin(): boolean {
    const user = this.getCurrentUser();
    return user ? user.role === 'admin' : false;
  }

  isAdherent(): boolean {
    const user = this.getCurrentUser();
    return user ? user.role === 'adherent' : false;
  }

  private checkAuthStatus(): void {
    const user = localStorage.getItem('currentUser');
    if (user) {
      try {
        const parsedUser = JSON.parse(user);
        this.currentUserSubject.next(parsedUser);
      } catch (error) {
        console.error('Error parsing stored user data:', error);
        this.logout();
      }
    }
  }

  updateProfile(userData: Partial<User>): Observable<any> {
    const user = this.getCurrentUser();
    if (!user) {
      throw new Error('No user logged in');
    }
    
    return this.http.put(`${this.apiUrl}auth/profile.php`, {
      id: user.id,
      ...userData
    });
  }

  changePassword(currentPassword: string, newPassword: string): Observable<any> {
    const user = this.getCurrentUser();
    if (!user) {
      throw new Error('No user logged in');
    }
    
    return this.http.put(`${this.apiUrl}auth/password.php`, {
      id: user.id,
      currentPassword,
      newPassword
    });
  }
}
