import { Controller } from '@hotwired/stimulus';
import $ from 'jquery';

const mobileMaxWidth = 993;

export default class extends Controller {
  static values = {
    edge: { type: String, default: 'left' },
  };

  connect() {
    $(this.element).sidenav({ edge: this.edgeValue });
  }

  close() {
    if (window.innerWidth < mobileMaxWidth) {
      $(this.element).sidenav('close')
    }
  }

  disconnect() {
    $(this.element).sidenav('destroy');
  }
}
