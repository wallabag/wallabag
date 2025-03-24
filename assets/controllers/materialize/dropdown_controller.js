import { Controller } from '@hotwired/stimulus';
import M from '@materializecss/materialize';

export default class extends Controller {
  connect() {
    this.instance = M.Dropdown.init(this.element, {
      hover: false,
      coverTrigger: false,
      constrainWidth: false,
    });
  }

  disconnect() {
    this.instance.destroy();
  }
}
