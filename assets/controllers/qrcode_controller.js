import { Controller } from '@hotwired/stimulus';
import jrQrcode from 'jr-qrcode';

export default class extends Controller {
  static values = { url: String };

  connect() {
    this.element.setAttribute('src', jrQrcode.getQrBase64(this.urlValue));
  }
}
