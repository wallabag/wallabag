import { Controller } from '@hotwired/stimulus';
import M from '@materializecss/materialize';

export default class extends Controller {
  connect() {
    this.instance = M.Range.init(this.element.querySelector('input[type=range]'));
  }

  disconnect() {
    if (this.instance) {
      this.instance.destroy();
    }
  }
}
