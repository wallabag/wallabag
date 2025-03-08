import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  static targets = ['emailTwoFactor', 'googleTwoFactor'];

  uncheckGoogle() {
    this.googleTwoFactorTarget.checked = false;
  }

  uncheckEmail() {
    this.emailTwoFactorTarget.checked = false;
  }
}
