import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  updateWidth() {
    const scrollPercent = (window.scrollY / (document.body.offsetHeight - window.innerHeight)) * 100;

    this.element.style.width = `${scrollPercent}%`
  }
}
