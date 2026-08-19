import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  static targets = ['light', 'dark', 'auto'];

  connect() {
    this.mql = window.matchMedia('(prefers-color-scheme: dark)');
    this.handleThemeChange = this.#choose.bind(this);
    this.mql.addEventListener('change', this.handleThemeChange);

    this.#choose();
    this.#select(this.#currentPreference());
  }

  disconnect() {
    this.mql.removeEventListener('change', this.handleThemeChange);
  }

  useLight() {
    this.element.classList.remove('dark-theme');
    document.cookie = 'theme=light;samesite=Lax;path=/;max-age=31536000';
    this.#select('light');
  }

  useDark() {
    this.element.classList.add('dark-theme');
    document.cookie = 'theme=dark;samesite=Lax;path=/;max-age=31536000';
    this.#select('dark');
  }

  useAuto() {
    document.cookie = 'theme=auto;samesite=Lax;path=/;max-age=0';
    this.#choose();
    this.#select('auto');
  }

  #choose() {
    const themeCookieExists = document.cookie.split(';').some((cookie) => cookie.trim().startsWith('theme='));

    if (themeCookieExists) {
      return;
    }

    if (this.mql.matches) {
      this.element.classList.add('dark-theme');
    } else {
      this.element.classList.remove('dark-theme');
    }
  }

  #currentPreference() {
    const themeCookie = this.element.ownerDocument.cookie
      .split(';')
      .map((cookie) => cookie.trim())
      .find((cookie) => cookie.startsWith('theme='));

    return themeCookie?.split('=')[1] ?? 'auto';
  }

  #select(preference) {
    this.lightTargets.forEach((target) => target.setAttribute('aria-pressed', (preference === 'light').toString()));
    this.darkTargets.forEach((target) => target.setAttribute('aria-pressed', (preference === 'dark').toString()));
    this.autoTargets.forEach((target) => target.setAttribute('aria-pressed', (preference === 'auto').toString()));
  }
}
