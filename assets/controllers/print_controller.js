import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  // eslint-disable-next-line class-methods-use-this
  print() {
    window.print();
  }
}
