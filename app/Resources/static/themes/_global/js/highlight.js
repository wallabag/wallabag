import 'highlight.js/styles/atom-one-light.css';
import * as hljs from 'highlight.js';

window.addEventListener('load', () => {
  document.querySelectorAll('pre').forEach((node) => {
    hljs.highlightBlock(node);
  });
});
