import { Controller } from '@hotwired/stimulus';
import ClipboardJS from 'clipboard';

export default class extends Controller {
  connect() {
    this.clipboard = new ClipboardJS(this.element);

    this.clipboard.on('success', (e) => {
      e.clearSelection();
    });
  }

  disconnect() {
    this.clipboard.destroy();
  }
}
