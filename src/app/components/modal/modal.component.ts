import { Component, OnInit, OnDestroy } from '@angular/core';
import { Subscription } from 'rxjs';
import { ModalService, ModalConfig } from '../../services/modal.service';

@Component({
  selector: 'app-modal',
  templateUrl: './modal.component.html',
  styleUrls: ['./modal.component.scss']
})
export class ModalComponent implements OnInit, OnDestroy {
  isVisible = false;
  config: ModalConfig | null = null;
  private subscription: Subscription = new Subscription();

  constructor(private modalService: ModalService) { }

  ngOnInit() {
    this.subscription.add(
      this.modalService.getModal().subscribe(config => {
        this.config = config;
        this.isVisible = config !== null;
        if (this.isVisible) {
          document.body.style.overflow = 'hidden'; // Prevent background scrolling
        } else {
          document.body.style.overflow = '';
        }
      })
    );
  }

  ngOnDestroy() {
    this.subscription.unsubscribe();
    document.body.style.overflow = '';
  }

  onConfirm() {
    this.modalService.confirm();
  }

  onCancel() {
    this.modalService.cancel();
  }

  onBackdropClick(event: Event) {
    if (event.target === event.currentTarget) {
      if (this.config?.showCancel) {
        this.onCancel();
      }
    }
  }

  getIconClass(): string {
    if (!this.config) return '';
    switch (this.config.type) {
      case 'success':
        return 'fa-check-circle';
      case 'error':
        return 'fa-exclamation-circle';
      case 'warning':
        return 'fa-exclamation-triangle';
      case 'confirm':
        return 'fa-question-circle';
      default:
        return 'fa-info-circle';
    }
  }

  getIconColorClass(): string {
    if (!this.config) return '';
    switch (this.config.type) {
      case 'success':
        return 'text-green-500';
      case 'error':
        return 'text-red-500';
      case 'warning':
        return 'text-yellow-500';
      case 'confirm':
        return 'text-blue-500';
      default:
        return 'text-blue-500';
    }
  }

  getButtonClass(): string {
    if (!this.config) return '';
    switch (this.config.type) {
      case 'success':
        return 'bg-green-500 hover:bg-green-600';
      case 'error':
        return 'bg-red-500 hover:bg-red-600';
      case 'warning':
        return 'bg-yellow-500 hover:bg-yellow-600';
      case 'confirm':
        return 'bg-blue-500 hover:bg-blue-600';
      default:
        return 'bg-blue-500 hover:bg-blue-600';
    }
  }
}
