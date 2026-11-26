import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  static targets = ['toggle'];

  toggle() {
    this.#set(!document.body.classList.contains('wb-drawer-open'));
  }

  close() {
    this.#set(false);
  }

  disconnect() {
    this.#set(false);
  }

  #set(open) {
    document.body.classList.toggle('wb-drawer-open', open);
    for (const toggle of this.toggleTargets) {
      toggle.setAttribute('aria-expanded', String(open));
    }
  }
}
