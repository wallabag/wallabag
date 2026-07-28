import { Controller } from '@hotwired/stimulus';
import M from '@materializecss/materialize';

export default class extends Controller {
  static values = {
    accordion: { type: Boolean, default: true },
  };

  connect() {
    this.instance = M.Collapsible.init(this.element, {
      accordion: this.accordionValue,
      onOpenStart: (listItem) => this.#setExpanded(listItem, true),
      onCloseStart: (listItem) => this.#setExpanded(listItem, false),
    });
    this.buttonHeaders = [...this.element.querySelectorAll('button.collapsible-header')];
    const keydownHandler = this.instance['_handleCollapsibleKeydownBound'];

    this.buttonHeaders.forEach((header) => {
      header.removeEventListener('keydown', keydownHandler);
    });
  }

  disconnect() {
    this.instance.destroy();
  }

  #setExpanded(listItem, expanded) {
    const header = listItem.querySelector(':scope > .collapsible-header[aria-expanded]');

    header?.setAttribute('aria-expanded', expanded.toString());
  }
}
