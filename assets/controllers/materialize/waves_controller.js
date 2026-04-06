import { Controller } from '@hotwired/stimulus';

let hasInitializedWaves = false;

export default class extends Controller {
  // eslint-disable-next-line class-methods-use-this
  connect() {
    if (window.Waves && !hasInitializedWaves) {
      window.Waves.init();
      hasInitializedWaves = true;
    }
  }
}
