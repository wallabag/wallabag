import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  static values = { entryId: Number };

  connect() {
    window.scrollTo({
      top: window.innerHeight * localStorage[`wallabag.article.${this.entryIdValue}.percent`],
      behavior: 'smooth',
    });
  }

  saveScroll() {
    const scrollPercent = window.scrollY / window.innerHeight;
    const scrollPercentRounded = Math.round(scrollPercent * 100) / 100;

    localStorage[`wallabag.article.${this.entryIdValue}.percent`] = scrollPercentRounded;
  }
}
