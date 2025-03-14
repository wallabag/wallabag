import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  static targets = ['addUrl', 'addUrlInput', 'search', 'searchInput', 'actions'];

  showAddUrl() {
    this.actionsTarget.style.display = 'none';
    this.addUrlTarget.style.display = 'flex';
    this.searchTarget.style.display = 'none';
    this.addUrlInputTarget.focus();
  }

  submittingUrl(e) {
    e.currentTarget.disabled = true;
    this.addUrlInputTarget.readOnly = true;
    this.addUrlInputTarget.blur();
  }

  showSearch() {
    this.actionsTarget.style.display = 'none';
    this.addUrlTarget.style.display = 'none';
    this.searchTarget.style.display = 'flex';
    this.searchInputTarget.focus();
  }

  showActions() {
    this.actionsTarget.style.display = 'flex';
    this.addUrlTarget.style.display = 'none';
    this.searchTarget.style.display = 'none';
  }
}
