import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  toggleAddTagForm() {
    this.dispatch('toggleAddTagForm');
  }
}
