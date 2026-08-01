import { Controller } from '@hotwired/stimulus';
import M from '@materializecss/materialize';

const mobileMaxWidth = 993;

export default class extends Controller {
  static values = {
    edge: { type: String, default: 'left' },
  };

  connect() {
    this.triggers = [...document.querySelectorAll('.sidenav-trigger')]
      .filter((trigger) => trigger.dataset.target === this.element.id);
    this.instance = M.Sidenav.init(this.element, {
      edge: this.edgeValue,
      onOpenStart: () => this.#setExpanded(true),
      onCloseStart: () => this.#setExpanded(false),
    });
    this.#setExpanded(false);
    this.keydownHandler = this.#handleKeydown.bind(this);
    document.addEventListener('keydown', this.keydownHandler);
  }

  close() {
    if (window.innerWidth < mobileMaxWidth) {
      this.instance.close();
    }
  }

  disconnect() {
    document.removeEventListener('keydown', this.keydownHandler);
    this.instance.destroy();
  }

  #handleKeydown(event) {
    if (event.key !== 'Escape' || !this.instance.isOpen || (this.instance.isFixed && window.innerWidth >= mobileMaxWidth)) {
      return;
    }

    event.preventDefault();
    event.stopPropagation();
    this.instance.close();
  }

  #setExpanded(expanded) {
    this.triggers.forEach((trigger) => trigger.setAttribute('aria-expanded', expanded.toString()));
  }
}
