import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  connect() {
    this.#choose();

    this.mql = window.matchMedia('(prefers-color-scheme: dark)');
    this.mql.addEventListener('change', this.#choose.bind(this));
  }

  useLight() {
    this.element.classList.remove('dark-theme');
    document.cookie = 'theme=light;samesite=Lax;path=/;max-age=31536000';
  }

  useDark() {
    this.element.classList.add('dark-theme');
    document.cookie = 'theme=dark;samesite=Lax;path=/;max-age=31536000';
  }

  useAuto() {
    document.cookie = 'theme=auto;samesite=Lax;path=/;max-age=0';
    this.#choose();
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
}
