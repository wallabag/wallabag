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
    this.spaceHandler = this.constructor.#activateWithSpace;
    this.instance.dropdownEl.addEventListener('keydown', this.spaceHandler);
  }

  disconnect() {
    this.instance.dropdownEl.removeEventListener('keydown', this.spaceHandler);
    this.instance.destroy();
  }

  #focusFirstAction() {
    this.instance.dropdownEl
      .querySelector('li:not(.divider) > a, li:not(.divider) > button:not([disabled])')
      ?.focus({ preventScroll: true });
  }

  static #activateWithSpace(event) {
    if (event.key !== ' ') {
      return;
    }

    const action = event.target.closest('a, button')
      ?? event.target.closest('li')?.querySelector('a, button');

    if (!action) {
      return;
    }

    event.preventDefault();
    action.click();
  }
}
