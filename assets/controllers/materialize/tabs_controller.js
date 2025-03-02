import { Controller } from '@hotwired/stimulus';
import $ from 'jquery';

export default class extends Controller {
  connect() {
    $(this.element).tabs();
  }

  disconnect() {
    $(this.element).tabs('destroy');
  }
}
