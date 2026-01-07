import { Controller } from '@hotwired/stimulus';
import annotator from 'annotator';

export default class extends Controller {
  static values = {
    entryId: Number,
    createUrl: String,
    updateUrl: String,
    destroyUrl: String,
    searchUrl: String,
  };

  connect() {
    this.app = new annotator.App();

    this.app.include(annotator.ui.main, {
      element: this.element,
    });

    const authorization = {
      permits() { return true; },
    };
    this.app.registry.registerUtility(authorization, 'authorizationPolicy');

    this.app.include(annotator.storage.http, {
      prefix: '',
      urls: {
        create: this.createUrlValue,
        update: this.updateUrlValue,
        destroy: this.destroyUrlValue,
        search: this.searchUrlValue,
      },
      entryId: this.entryIdValue,
      onError(msg, xhr) {
        if (!Object.prototype.hasOwnProperty.call(xhr, 'responseJSON')) {
          annotator.notification.banner('An error occurred', 'error');
          return;
        }
        Object.values(xhr.responseJSON.children).forEach((v) => {
          if (v.errors) {
            Object.values(v.errors).forEach((errorText) => {
              annotator.notification.banner(errorText, 'error');
            });
          }
        });
      },
    });

    this.app.start().then(() => {
      this.app.annotations.load({ entry: this.entryIdValue });
    });
  }

  disconnect() {
    this.app.destroy();
  }
}
