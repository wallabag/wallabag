import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  static targets = ['addUrl', 'addUrlInput', 'addUrlTrigger', 'search', 'searchInput', 'searchTrigger', 'actions'];

  showAddUrl({ currentTarget } = {}) {
    this.modeTrigger = currentTarget ?? this.addUrlTriggerTarget;
    this.actionsTarget.style.display = 'none';
    this.addUrlTarget.style.display = 'flex';
    this.searchTarget.style.display = 'none';
    this.#setExpanded(true, false);
    this.addUrlInputTarget.focus();
  }

  submittingUrl(e) {
    e.currentTarget.disabled = true;
    this.addUrlInputTarget.readOnly = true;
    this.addUrlInputTarget.blur();
  }

  showSearch({ currentTarget } = {}) {
    this.modeTrigger = currentTarget ?? this.searchTriggerTarget;
    this.actionsTarget.style.display = 'none';
    this.addUrlTarget.style.display = 'none';
    this.searchTarget.style.display = 'flex';
    this.#setExpanded(false, true);
    this.searchInputTarget.focus();
  }

  showActions() {
    this.actionsTarget.style.display = 'flex';
    this.addUrlTarget.style.display = 'none';
    this.searchTarget.style.display = 'none';
    this.#setExpanded(false, false);
    this.modeTrigger?.focus({ preventScroll: true });
    this.modeTrigger = undefined;
  }

  #setExpanded(addUrl, search) {
    this.addUrlTriggerTarget.setAttribute('aria-expanded', addUrl.toString());
    this.searchTriggerTarget.setAttribute('aria-expanded', search.toString());
  }
}
