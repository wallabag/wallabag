import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  static targets = ['link', 'edit', 'form', 'input'];

  showForm() {
    this.formTarget.classList.remove('hidden');
    this.editTarget.classList.add('hidden');
    this.linkTarget.classList.add('hidden');
    this.inputTarget.focus();
  }
}
