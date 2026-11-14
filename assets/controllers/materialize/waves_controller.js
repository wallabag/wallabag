import { Controller } from '@hotwired/stimulus';

let hasInitializedWaves = false;

export default class extends Controller {
  connect() {
    if (window.Waves && !hasInitializedWaves) {
      window.Waves.init();
      hasInitializedWaves = true;
    }
  }
}
