import { browser, by, element } from 'protractor';

describe('ALRCF Association App', () => {
  beforeEach(() => {
    browser.get('/');
  });

  it('should display welcome message', () => {
    expect(element(by.css('h1')).getText()).toContain('Bienvenue à l\'ALRCF');
  });

  it('should navigate to about page', () => {
    element(by.css('a[routerLink="/a-propos"]')).click();
    expect(browser.getCurrentUrl()).toContain('/a-propos');
  });

  it('should navigate to contact page', () => {
    element(by.css('a[routerLink="/contact"]')).click();
    expect(browser.getCurrentUrl()).toContain('/contact');
  });

  it('should navigate to login page', () => {
    element(by.css('a[routerLink="/connexion"]')).click();
    expect(browser.getCurrentUrl()).toContain('/connexion');
  });
});
