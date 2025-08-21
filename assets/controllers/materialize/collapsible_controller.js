import { Controller } from '@hotwired/stimulus';
import M from '@materializecss/materialize';

export default class extends Controller {
  static values = {
    accordion: { type: Boolean, default: true },
  };

  connect() {
    this.instance = M.Collapsible.init(this.element, { accordion: this.accordionValue });
  }

  disconnect() {
    this.instance.destroy();
  }
}
