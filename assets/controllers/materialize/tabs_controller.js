import { Controller } from '@hotwired/stimulus';
import M from '@materializecss/materialize';

export default class extends Controller {
  connect() {
    this.instance = M.Tabs.init(this.element);
  }

  disconnect() {
    this.instance.destroy();
  }
}
