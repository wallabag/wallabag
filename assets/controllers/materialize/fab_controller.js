import { Controller } from '@hotwired/stimulus';
import M from '@materializecss/materialize';

export default class extends Controller {
  connect() {
    this.trigger = this.element.querySelector('[data-toggle="actions"]');
    this.instance = M.FloatingActionButton.init(this.element, {
      direction: 'left',
      hoverEnabled: false,
    });
    this.#syncExpanded();
  }

  autoDisplay() {
    const scrolled = (window.innerHeight + window.scrollY) >= document.body.offsetHeight;

    if (scrolled) {
      this.toggleScroll = true;
      this.instance.open();
    } else if (this.toggleScroll === true) {
      this.toggleScroll = false;
      this.instance.close();
    }

    this.#syncExpanded();
  }

  click() {
    this.dispatch('click');
    requestAnimationFrame(() => this.#syncExpanded());
  }

  disconnect() {
    this.instance.destroy();
  }

  #syncExpanded() {
    this.trigger.setAttribute('aria-expanded', this.instance.isOpen.toString());
  }
}
