import { Controller } from '@hotwired/stimulus';
import $ from 'jquery';

export default class extends Controller {
  connect() {
    $(this.element).tooltip();
  }

  disconnect() {
    $(this.element).tooltip('destroy');
  }
}
