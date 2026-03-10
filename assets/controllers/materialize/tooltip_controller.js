import { Controller } from '@hotwired/stimulus';
import M from '@materializecss/materialize';

export default class extends Controller {
  static values = {
    position: String,
    enterDelay: Number,
  };

  connect() {
    const options = {};

    if (this.hasPositionValue) {
      options.position = this.positionValue;
    }

    if (this.hasEnterDelayValue) {
      options.enterDelay = this.enterDelayValue;
    }

    this.instance = M.Tooltip.init(this.element, options);
  }

  disconnect() {
    this.instance.destroy();
  }
}
