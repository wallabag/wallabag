import { Controller } from '@hotwired/stimulus';
import M from '@materializecss/materialize';

const mobileMaxWidth = 993;
const focusableSelector = [
  'a[href]',
  'button:not([disabled])',
  'input:not([disabled])',
  'select:not([disabled])',
  'textarea:not([disabled])',
  '[tabindex]:not([tabindex="-1"])',
].join(', ');

export default class extends Controller {
  static values = {
    edge: { type: String, default: 'left' },
  };

  connect() {
    this.triggers = [...document.querySelectorAll('.sidenav-trigger')]
      .filter((trigger) => trigger.dataset.target === this.element.id);
    this.instance = M.Sidenav.init(this.element, {
      edge: this.edgeValue,
      onOpenStart: () => {
        this.#setExpanded(true);
        this.#rememberInvokingTrigger();
      },
      onOpenEnd: () => this.#focusFirstControl(),
      onCloseStart: () => this.#setExpanded(false),
      onCloseEnd: () => this.#restoreInvokingTrigger(),
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
    if (event.key !== 'Escape' || !this.instance.isOpen || this.#isFixed()) {
      return;
    }

    event.preventDefault();
    event.stopPropagation();
    this.instance.close();
  }

  #rememberInvokingTrigger() {
    this.invokingTrigger = this.triggers.find(
      (trigger) => trigger === document.activeElement,
    ) ?? this.triggers[0];
  }

  #focusFirstControl() {
    if (this.#isFixed()) {
      return;
    }

    const control = [...this.element.querySelectorAll(focusableSelector)]
      .find((element) => element.getClientRects().length > 0);

    control?.focus({ preventScroll: true });
  }

  #restoreInvokingTrigger() {
    if (!this.#isFixed() && this.invokingTrigger?.isConnected) {
      this.invokingTrigger.focus({ preventScroll: true });
    }
  }

  #isFixed() {
    return this.element.classList.contains('sidenav-fixed') && window.innerWidth >= mobileMaxWidth;
  }

  #setExpanded(expanded) {
    this.triggers.forEach((trigger) => trigger.setAttribute('aria-expanded', expanded.toString()));
  }
}
