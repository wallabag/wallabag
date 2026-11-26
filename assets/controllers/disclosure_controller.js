import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  closeOutside(e) {
    if (!this.element.contains(e.target)) {
      this.close();
    }
  }

  close() {
    this.element.removeAttribute('open');
  }
}
