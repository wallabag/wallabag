import { Controller } from '@hotwired/stimulus';
import M from '@materializecss/materialize';

export default class extends Controller {
  static values = {
    accordion: { type: Boolean, default: true },
  };

  connect() {
    this.instance = M.Collapsible.init(this.element, { accordion: this.accordionValue });
    this.boundHandleKeydown = this.handleKeydown.bind(this);
    this.element.addEventListener('keydown', this.boundHandleKeydown, true);
  }

  handleKeydown(event) {
    if (event.key !== 'Enter' || !(event.target instanceof Element)) {
      return;
    }

    const buttonHeader = event.target.closest('button.collapsible-header');

    if (!buttonHeader || buttonHeader.closest('.collapsible') !== this.element) {
      return;
    }

    event.stopPropagation();
  }

  disconnect() {
    this.element.removeEventListener('keydown', this.boundHandleKeydown, true);
    this.instance.destroy();
  }
}
