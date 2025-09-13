import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { News, Event, ContactMessage, Report, Subscription } from '../models/association.model';
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
}
