import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  updateWidth() {
    const referenceHeight = document.body.offsetHeight - window.innerHeight;
    const scrollPercent = (window.scrollY / referenceHeight) * 100;

    this.element.style.width = `${scrollPercent}%`;
  }
}
