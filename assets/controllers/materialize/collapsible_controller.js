import { Controller } from '@hotwired/stimulus';
import M from '@materializecss/materialize';

export default class extends Controller {
  static values = {
    accordion: { type: Boolean, default: true },
  };

  connect() {
    this.instance = M.Collapsible.init(this.element, { accordion: this.accordionValue });
    this.buttonHeaders = [...this.element.querySelectorAll('button.collapsible-header')];
    // eslint-disable-next-line dot-notation
    const keydownHandler = this.instance['_handleCollapsibleKeydownBound'];

    this.buttonHeaders.forEach((header) => {
      header.removeEventListener('keydown', keydownHandler);
    });
  }

  disconnect() {
    this.instance.destroy();
  }
}
