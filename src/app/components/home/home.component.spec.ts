import { ComponentFixture, TestBed } from '@angular/core/testing';
import { HttpClientTestingModule } from '@angular/common/http/testing';
import { HomeComponent } from './home.component';
import { AssociationService } from '../../services/association.service';

describe('HomeComponent', () => {
  let component: HomeComponent;
  let fixture: ComponentFixture<HomeComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ HomeComponent ],
      imports: [ HttpClientTestingModule ],
      providers: [ AssociationService ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(HomeComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });

  it('should have hero data', () => {
    expect(component.heroData).toBeDefined();
    expect(component.heroData.title).toBe('Bienvenue à l\'ALRCF');
  });

  it('should have features data', () => {
    expect(component.features).toBeDefined();
    expect(component.features.length).toBeGreaterThan(0);
  });

  it('should have stats data', () => {
    expect(component.stats).toBeDefined();
    expect(component.stats.length).toBeGreaterThan(0);
  });
});
