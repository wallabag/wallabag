import { Controller } from '@hotwired/stimulus';
import $ from 'jquery';

export default class extends Controller {
  static values = {
    edge: { type: String, default: 'left' },
  };

  connect() {
    $(this.element).floatingActionButton();
  }

  autoDisplay() {
    const scrolled = (window.innerHeight + window.scrollY) >= document.body.offsetHeight;

    if (scrolled) {
      this.toggleScroll = true;
      $(this.element).floatingActionButton('open');
    } else if (this.toggleScroll === true) {
      this.toggleScroll = false;
      $(this.element).floatingActionButton('close');
    }
  }

  disconnect() {
    $(this.element).floatingActionButton('destroy');
  }
}
