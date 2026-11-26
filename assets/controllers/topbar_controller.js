import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  static targets = ['addUrl', 'addUrlInput', 'search', 'searchInput'];

  showAddUrl() {
    this.showActions();
    this.addUrlTarget.showModal();
    this.addUrlInputTarget.focus();
  }

  submittingUrl(e) {
    const button = e.currentTarget.querySelector('button[type="submit"]');
    if (button) {
      button.disabled = true;
    }
    this.addUrlInputTarget.readOnly = true;
    this.addUrlInputTarget.blur();
  }

  showSearch() {
    this.showActions();
    this.searchTarget.showModal();
    this.searchInputTarget.focus();
  }

  showActions() {
    for (const dialog of document.querySelectorAll('dialog[open]')) {
      dialog.close();
    }
  }

  openPanel({ params: { panel } }) {
    document.getElementById(panel)?.showModal();
  }

  closeOnBackdrop(e) {
    if (e.target === e.currentTarget) {
      e.currentTarget.close();
    }
  }
}
