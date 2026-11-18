import { Controller } from '@hotwired/stimulus';
import M from '@materializecss/materialize';

export default class extends Controller {
  connect() {
    this.instance = M.toast({ text: this.element.textContent });
  }

  disconnect() {
    this.instance.dismiss();
  }
}
