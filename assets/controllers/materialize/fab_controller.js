import { Controller } from '@hotwired/stimulus';
import M from '@materializecss/materialize';

export default class extends Controller {
  static values = {
    edge: { type: String, default: 'left' },
  };

  connect() {
    this.instance = M.FloatingActionButton.init(this.element);
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
  }

  click() {
    this.dispatch('click');
  }

  disconnect() {
    this.instance.destroy();
  }
}
