import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
  // eslint-disable-next-line class-methods-use-this
  async connect() {
    await import(/* webpackChunkName: "mathjax" */ 'mathjax/es5/tex-svg');
  }
}
