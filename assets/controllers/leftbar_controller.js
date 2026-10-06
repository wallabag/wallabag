import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  toggleAddTagForm({ currentTarget }) {
    const expanded = currentTarget.getAttribute('aria-expanded') !== 'true';

    currentTarget.setAttribute('aria-expanded', expanded.toString());
    this.dispatch('toggleAddTagForm');
  }
}
