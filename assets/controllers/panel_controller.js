import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  close() {
    this.element.close();
  }

  closeOnBackdrop(e) {
    if (e.target === this.element) {
      this.close();
    }
  }
}
