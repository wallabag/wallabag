import { Controller } from '@hotwired/stimulus';
import $ from 'jquery';

export default class extends Controller {
  connect() {
    $(this.element.querySelector('select')).formSelect();
  }

  disconnect() {
    $(this.element.querySelector('select')).formSelect('destroy');
  }
}
