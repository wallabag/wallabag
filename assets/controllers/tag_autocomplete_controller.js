import { Controller } from '@hotwired/stimulus';

const MAX_SUGGESTIONS = 20;

let datalistCounter = 0;

/*
 * Suggests the existing tags of the user while typing in a comma-separated tags input.
 *
 * Suggestions are provided through a native <datalist>: each option holds the whole input value
 * with the tag being typed completed, so picking one keeps the tags typed before it.
 */
export default class extends Controller {
  static values = { url: String };

  connect() {
    datalistCounter += 1;

    this.datalist = document.createElement('datalist');
    this.datalist.id = `tag-autocomplete-${datalistCounter}`;
    this.element.after(this.datalist);
    this.element.setAttribute('list', this.datalist.id);
  }

  disconnect() {
    this.element.removeAttribute('list');
    this.datalist.remove();
  }

  load() {
    if (!this.labelsRequest) {
      this.labelsRequest = fetch(this.urlValue, {
        credentials: 'same-origin',
        headers: { Accept: 'application/json' },
      })
        .then((response) => (response.ok ? response.json() : []))
        .catch(() => [])
        .then((labels) => {
          this.labels = Array.isArray(labels) ? labels : [];
          this.suggest();
        });
    }
  }

  suggest() {
    if (!this.labels) {
      this.load();

      return;
    }

    const { value } = this.element;
    const separatorPosition = value.lastIndexOf(',');
    const previousTags = value.slice(0, separatorPosition + 1);
    const currentTag = value.slice(separatorPosition + 1);
    const term = currentTag.trim().toLowerCase();

    if (term === '') {
      this.datalist.replaceChildren();

      return;
    }

    const typedLabels = previousTags.split(',').map((label) => label.trim().toLowerCase());
    // keep what was typed so far untouched, the browser only shows the options matching it
    const optionPrefix = value.slice(0, value.length - currentTag.trimStart().length);

    const options = this.labels
      .filter((label) => label.startsWith(term) && label !== term && !typedLabels.includes(label))
      .slice(0, MAX_SUGGESTIONS)
      .map((label) => {
        const option = document.createElement('option');
        option.value = optionPrefix + label;

        return option;
      });

    this.datalist.replaceChildren(...options);
  }
}
