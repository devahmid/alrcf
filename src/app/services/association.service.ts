import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { News, Event, ContactMessage, Report, Subscription, Announcement } from '../models/association.model';
import { environment } from '../../environments/environment';

@Injectable({
  providedIn: 'root'
})
export class AssociationService {
  private apiUrl = environment.apiUrl;

  constructor(private http: HttpClient) { }

  // News methods
  getNews(): Observable<any> {
    return this.http.get<any>(`${this.apiUrl}news/get.php`);
  }

  getNewsById(id: number): Observable<any> {
    return this.http.get<any>(`${this.apiUrl}news/get.php?id=${id}`);
  }

  createNews(news: Partial<News>): Observable<any> {
    return this.http.post(`${this.apiUrl}news/create.php`, news);
  }

  updateNews(id: number, news: Partial<News>): Observable<any> {
    return this.http.put(`${this.apiUrl}news/update.php`, { id, ...news });
  }

  uploadNewsImage(file: File): Observable<any> {
    const formData = new FormData();
    formData.append('image', file);
    return this.http.post(`${this.apiUrl}news/upload.php`, formData);
  }

  deleteNews(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}news/delete.php?id=${id}`);
  }

  // Events methods
  getEvents(): Observable<any> {
    return this.http.get<any>(`${this.apiUrl}events/get.php`);
  }

  getEventById(id: number): Observable<any> {
    return this.http.get<any>(`${this.apiUrl}events/get.php?id=${id}`);
  }

  createEvent(event: Partial<Event>): Observable<any> {
    return this.http.post(`${this.apiUrl}events/create.php`, event);
  }

  updateEvent(id: number, event: Partial<Event>): Observable<any> {
    return this.http.put(`${this.apiUrl}events/update.php`, { id, ...event });
  }

  deleteEvent(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}events/delete.php?id=${id}`);
  }

  registerForEvent(eventId: number, adherentId: number): Observable<any> {
    return this.http.post(`${this.apiUrl}events/register.php`, { eventId, adherentId });
  }

  // Contact messages methods
  sendMessage(message: Partial<ContactMessage>): Observable<any> {
    return this.http.post(`${this.apiUrl}contact/send.php`, message);
  }

  getMessages(): Observable<any> {
    return this.http.get<any>(`${this.apiUrl}contact/get.php`);
  }

  updateMessageStatus(id: number, status: string, reply?: string): Observable<any> {
    return this.http.put(`${this.apiUrl}contact/update.php`, { id, status, reply });
  }

  deleteMessage(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}contact/delete.php?id=${id}`);
  }

  // Reports methods
  createReport(report: Partial<Report>): Observable<any> {
    return this.http.post(`${this.apiUrl}reports/create.php`, report);
  }

  getReports(adherentId?: number): Observable<any> {
    const url = adherentId
      ? `${this.apiUrl}reports/get.php?adherentId=${adherentId}`
      : `${this.apiUrl}reports/get.php`;
    return this.http.get<any>(url);
  }

  updateReport(id: number, report: Partial<Report>): Observable<any> {
    return this.http.put(`${this.apiUrl}reports/update.php`, { id, ...report });
  }

  // Subscriptions methods
  getSubscriptions(adherentId?: number): Observable<any> {
    const url = adherentId
      ? `${this.apiUrl}subscriptions/get.php?adherentId=${adherentId}`
      : `${this.apiUrl}subscriptions/get.php`;
    return this.http.get<any>(url);
  }

  createSubscription(subscription: Partial<Subscription>): Observable<any> {
    return this.http.post(`${this.apiUrl}subscriptions/create.php`, subscription);
  }

  updateSubscription(id: number, subscription: Partial<Subscription>): Observable<any> {
    return this.http.put(`${this.apiUrl}subscriptions/update.php`, { id, ...subscription });
  }

  // Statistics methods
  getStatistics(): Observable<any> {
    return this.http.get(`${this.apiUrl}statistics/get.php`);
  }
  // Admin User Management
  getUsers(): Observable<any> {
    // Admin user management
    return this.http.get<any>(`${this.apiUrl}admin/users`);
  }

  updateUser(user: Partial<any>): Observable<any> {
    return this.http.put(`${this.apiUrl}admin/users`, user);
  }

  deleteUser(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}admin/users?id=${id}`);
  }

  // Announcements methods
  getAnnouncements(params?: { id?: number; userId?: number; category?: string; status?: string }): Observable<any> {
    let url = `${this.apiUrl}announcements/get.php`;
    const queryParams: string[] = [];
    
    if (params?.id) queryParams.push(`id=${params.id}`);
    if (params?.userId) queryParams.push(`userId=${params.userId}`);
    if (params?.category) queryParams.push(`category=${params.category}`);
    if (params?.status) queryParams.push(`status=${params.status}`);
    
    if (queryParams.length > 0) {
      url += '?' + queryParams.join('&');
    }
    
    return this.http.get<any>(url);
  }

  createAnnouncement(announcement: Partial<Announcement>): Observable<any> {
    return this.http.post(`${this.apiUrl}announcements/create.php`, announcement);
  }

  updateAnnouncement(id: number, announcement: Partial<Announcement>): Observable<any> {
    return this.http.put(`${this.apiUrl}announcements/update.php`, { id, ...announcement });
  }

  deleteAnnouncement(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}announcements/delete.php?id=${id}`);
  }

  validateAnnouncement(id: number, action: 'approve' | 'reject', rejectionReason?: string): Observable<any> {
    return this.http.post(`${this.apiUrl}announcements/validate.php`, { 
      id, 
      action, 
      rejectionReason 
    });
  }

  uploadAnnouncementImage(file: File): Observable<any> {
    const formData = new FormData();
    formData.append('image', file);
    return this.http.post<any>(`${this.apiUrl}announcements/upload.php`, formData);
  }

  // Projects
  getProjects(): Observable<any> {
    return this.http.get<any>(`${this.apiUrl}projects/get.php`);
  }

  getProjectById(id: number): Observable<any> {
    return this.http.get<any>(`${this.apiUrl}projects/get.php?id=${id}`);
  }

  createProject(project: Partial<any>): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}projects/create.php`, project);
  }

  updateProject(id: number, project: Partial<any>): Observable<any> {
    return this.http.put<any>(`${this.apiUrl}projects/update.php`, { id, ...project });
  }

  deleteProject(id: number): Observable<any> {
    return this.http.delete<any>(`${this.apiUrl}projects/delete.php?id=${id}`);
  }
}
