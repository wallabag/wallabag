import 'highlight.js/styles/atom-one-light.css';
import hljs from 'highlight.js';

window.addEventListener('load', () => {
  document.querySelectorAll('pre').forEach((element) => {
    hljs.highlightElement(element);
  });
});
