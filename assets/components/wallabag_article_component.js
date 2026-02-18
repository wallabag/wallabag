import './wallabag_article_component.scss';
import 'highlight.js/styles/atom-one-light.css';

export default class WallabagArticle extends HTMLElement {
  constructor() {
    super();
    this.attachShadow({ mode: 'open' });
  }

  static get observedAttributes() {
    return ['stylesheet-urls', 'content'];
  }

  attributeChangedCallback(attribute, oldValue, newValue) {
    if (!WallabagArticle.observedAttributes.includes(attribute) || oldValue === newValue) {
      return;
    }

    this.render();
  }

  connectedCallback() {
    this.render();
  }

  render() {
    const stylesheetsHTML = Object.values(JSON.parse(this.getAttribute('stylesheet-urls')))
      .map((url) => {
        const linkElement = document.createElement('link');
        linkElement.setAttribute('rel', 'stylesheet');
        linkElement.setAttribute('href', url);
        return linkElement.outerHTML;
      })
      .join('\n');

    const contentElement = document.createElement('div');
    contentElement.classList.add('wallabag_article_component');
    contentElement.innerHTML = this.getAttribute('content');

    // Clone data attributes inside our shadow DOM
    this.getAttributeNames().forEach((attributeName) => {
      if (!attributeName.startsWith('data-')) {
        return;
      }

      contentElement.setAttribute(attributeName, this.getAttribute(attributeName));
    });

    this.shadowRoot.innerHTML = `
      ${stylesheetsHTML}
      ${contentElement.outerHTML}
    `;
  }

  /**
   * Return a promise that will be fulfilled once the shadowRoot is ready.
   * Once fulfilled, pass the .wallabag_article_component HTMLElement.
   * @returns {Promise<HTMLElement>}
   */
  getOnLoadedPromise() {
    return new Promise((resolve) => {
      const interval = setInterval(() => {
        if (!(this.shadowRoot instanceof ShadowRoot)) {
          return;
        }

        const element = this.shadowRoot.querySelector('.wallabag_article_component');
        if (!(element instanceof HTMLElement)) {
          return;
        }

        clearInterval(interval);
        resolve(element);
      }, 50);
    });
  }
}

customElements.define('wallabag-article', WallabagArticle);
