import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  static targets = ['input'];

  toggle() {
    this.element.classList.toggle('hidden');

    if (!this.element.classList.contains('hidden')) {
      this.inputTarget.focus();
    }
  }
}
