import { Injectable } from '@angular/core';
import { Subject, Observable } from 'rxjs';

export interface ModalConfig {
  title: string;
  message: string;
  type?: 'info' | 'success' | 'warning' | 'error' | 'confirm';
  confirmText?: string;
  cancelText?: string;
  showCancel?: boolean;
}

@Injectable({
  providedIn: 'root'
})
export class ModalService {
  private modalSubject = new Subject<ModalConfig | null>();
  private responseSubject = new Subject<boolean>();

  constructor() { }

  show(config: ModalConfig): Observable<boolean> {
    this.modalSubject.next(config);
    return this.responseSubject.asObservable();
  }

  getModal(): Observable<ModalConfig | null> {
    return this.modalSubject.asObservable();
  }

  confirm(): void {
    this.responseSubject.next(true);
    this.close();
  }

  cancel(): void {
    this.responseSubject.next(false);
    this.close();
  }

  close(): void {
    this.modalSubject.next(null);
  }

  // Helper methods for common use cases
  success(message: string, title: string = 'Succès'): Observable<boolean> {
    return this.show({
      title,
      message,
      type: 'success',
      confirmText: 'OK',
      showCancel: false
    });
  }

  error(message: string, title: string = 'Erreur'): Observable<boolean> {
    return this.show({
      title,
      message,
      type: 'error',
      confirmText: 'OK',
      showCancel: false
    });
  }

  info(message: string, title: string = 'Information'): Observable<boolean> {
    return this.show({
      title,
      message,
      type: 'info',
      confirmText: 'OK',
      showCancel: false
    });
  }

  warning(message: string, title: string = 'Attention'): Observable<boolean> {
    return this.show({
      title,
      message,
      type: 'warning',
      confirmText: 'OK',
      showCancel: false
    });
  }

  confirmAction(message: string, title: string = 'Confirmation'): Observable<boolean> {
    return this.show({
      title,
      message,
      type: 'confirm',
      confirmText: 'Confirmer',
      cancelText: 'Annuler',
      showCancel: true
    });
  }
}
