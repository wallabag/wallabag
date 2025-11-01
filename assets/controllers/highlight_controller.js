import { Controller } from '@hotwired/stimulus';
import hljs from 'highlight.js';
import WallabagArticle from '../components/wallabag_article_component';

export default class extends Controller {
  connect() {
    // Prevent ESLint from complaining that this.setup() doesn't use this
    this.hljs = hljs;

    if (this.element instanceof WallabagArticle) {
      this.element.getOnLoadedPromise().then((element) => this.setup(element));
      return;
    }

    this.setup(this.element);
  }

  setup(element) {
    element.querySelectorAll('pre code').forEach((el) => this.hljs.highlightElement(el));
  }
}
