import { Controller } from '@hotwired/stimulus';
import M from '@materializecss/materialize';

const mobileMaxWidth = 993;

export default class extends Controller {
  static values = {
    edge: { type: String, default: 'left' },
  };

  connect() {
    this.instance = M.Sidenav.init(this.element, { edge: this.edgeValue });
  }

  close() {
    if (window.innerWidth < mobileMaxWidth) {
      this.instance.close();
    }
  }

  disconnect() {
    this.instance.destroy();
  }
}
