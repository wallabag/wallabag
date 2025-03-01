import { Controller } from '@hotwired/stimulus';
import M from '@materializecss/materialize';

export default class extends Controller {
  connect() {
    this.instance = M.FormSelect.init(this.element.querySelector('select'));
  }

  disconnect() {
    this.instance.destroy();
  }
}
