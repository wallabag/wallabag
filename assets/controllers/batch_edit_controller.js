import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  static targets = ['item', 'tagAction'];

  toggleSelection(e) {
    this.itemTargets.forEach((item) => {
      item.checked = e.currentTarget.checked; // eslint-disable-line no-param-reassign
    });
  }

  tagSelection() {
    this.element.requestSubmit(this.tagActionTarget);
  }
}
