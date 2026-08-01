import { Controller } from '@hotwired/stimulus';
import M from '@materializecss/materialize';

export default class extends Controller {
  connect() {
    this.instance = M.Dropdown.init(this.element, {
      hover: false,
      coverTrigger: false,
      constrainWidth: false,
      onOpenStart: () => this.element.setAttribute('aria-expanded', 'true'),
      onOpenEnd: () => this.#focusFirstAction(),
      onCloseStart: () => this.element.setAttribute('aria-expanded', 'false'),
    });
    this.element.setAttribute('aria-expanded', 'false');
  }

  disconnect() {
    this.instance.destroy();
  }

  #focusFirstAction() {
    this.instance.dropdownEl
      .querySelector('li:not(.divider) > a, li:not(.divider) > button:not([disabled])')
      ?.focus({ preventScroll: true });
  }
}
