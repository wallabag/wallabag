import { Controller } from '@hotwired/stimulus';
import $ from 'jquery';

export default class extends Controller {
  connect() {
    $(this.element).dropdown({
      hover: false,
      coverTrigger: false,
      constrainWidth: false,
    })
  }

  disconnect() {
    $(this.element).dropdown('destroy');
  }
}
