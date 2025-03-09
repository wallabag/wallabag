import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  static targets = ['card', 'paginationWrapper'];

  connect() {
    this.pagination = this.paginationWrapperTarget.querySelector('.pagination');

    this.cardIndex = 0;
    this.lastCardIndex = this.cardTargets.length - 1;

    /* If we come from next page */
    if (window.location.hash === '#prev') {
      this.cardIndex = this.lastCardIndex;
    }

    this.currentCard = this.cardTargets[this.cardIndex];

    this.currentCard.classList.add('z-depth-4');
  }

  selectRightCard() {
    if (this.cardIndex >= 0 && this.cardIndex < this.lastCardIndex) {
      this.currentCard.classList.remove('z-depth-4');
      this.cardIndex += 1;
      this.currentCard = this.cardTargets[this.cardIndex];
      this.currentCard.classList.add('z-depth-4');

      return;
    }

    if (this.pagination && this.pagination.querySelector('a[rel="next"]')) {
      window.location.href = this.pagination.querySelector('a[rel="next"]').href;
    }
  }

  selectLeftCard() {
    if (this.cardIndex > 0 && this.cardIndex <= this.lastCardIndex) {
      this.currentCard.classList.remove('z-depth-4');
      this.cardIndex -= 1;
      this.currentCard = this.cardTargets[this.cardIndex];
      this.currentCard.classList.add('z-depth-4');

      return;
    }

    if (this.pagination && this.pagination.querySelector('a[rel="prev"]')) {
      window.location.href = `${this.pagination.querySelector('a[rel="prev"]').href}#prev`;
    }
  }

  selectCurrentCard() {
    const url = this.currentCard.querySelector('a.card-title').href;
    if (url) {
      window.location.href = url;
    }
  }
}
