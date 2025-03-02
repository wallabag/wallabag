import { Controller } from '@hotwired/stimulus';
import $ from 'jquery';

export default class extends Controller {
  static values = {
    accordion: { type: Boolean, default: true },
  };

  connect() {
    $(this.element).collapsible({ accordion: this.accordionValue });
  }

  disconnect() {
    $(this.element).collapsible('destroy');
  }
}
