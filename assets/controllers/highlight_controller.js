import { Controller } from '@hotwired/stimulus';
import 'highlight.js/styles/atom-one-light.css';
import hljs from 'highlight.js';

export default class extends Controller {
  connect() {
    this.element.querySelectorAll('pre code').forEach((element) => {
      hljs.highlightElement(element);
    });
  }
}
