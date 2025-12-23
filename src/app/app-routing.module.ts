import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { AuthGuard } from './guards/auth.guard';
import { AdminGuard } from './guards/admin.guard';
import { HomeComponent } from './components/home/home.component';
import { AboutComponent } from './components/about/about.component';
import { ContactComponent } from './components/contact/contact.component';
import { LoginComponent } from './components/login/login.component';
import { RegisterComponent } from './components/register/register.component';
import { AdherentSpaceComponent } from './components/adherent-space/adherent-space.component';
import { AdminSpaceComponent } from './components/admin-space/admin-space.component';
import { AnnouncementsComponent } from './components/announcements/announcements.component';
import { NewsComponent } from './components/news/news.component';
import { EventsComponent } from './components/events/events.component';
import { ProjectsComponent } from './components/projects/projects.component';

const routes: Routes = [
  { path: '', component: HomeComponent },
  { path: 'accueil', component: HomeComponent },
  { path: 'a-propos', component: AboutComponent },
  { path: 'contact', component: ContactComponent },
  { path: 'annonces', component: AnnouncementsComponent },
  { path: 'actualites', component: NewsComponent },
  { path: 'evenements', component: EventsComponent },
  { path: 'projets', component: ProjectsComponent },
  { path: 'connexion', component: LoginComponent },
  { path: 'inscription', component: RegisterComponent },
  {
    path: 'espace-adherent',
    component: AdherentSpaceComponent,
    canActivate: [AuthGuard]
  },
  {
    path: 'admin',
    component: AdminSpaceComponent,
    canActivate: [AdminGuard]
  },
  { path: '**', redirectTo: '' }
];

@NgModule({
  imports: [RouterModule.forRoot(routes, { scrollPositionRestoration: 'top' })],
  exports: [RouterModule]
})
export class AppRoutingModule { }
