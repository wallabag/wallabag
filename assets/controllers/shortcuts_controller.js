import { Controller } from '@hotwired/stimulus';
import Mousetrap from 'mousetrap';

export default class extends Controller {
  static targets = ['openOriginal', 'markAsFavorite', 'markAsRead', 'deleteEntry', 'showAddUrl', 'showSearch', 'showActions'];

  static outlets = ['entries-navigation'];

  connect() {
    this.boundShortcuts = [];
    this.originalStopCallback = Mousetrap.prototype.stopCallback;

    /* Go to */
    this.bindShortcut('g u', () => {
      window.location.href = Routing.generate('homepage');
    });
    this.bindShortcut('g s', () => {
      window.location.href = Routing.generate('starred');
    });
    this.bindShortcut('g r', () => {
      window.location.href = Routing.generate('archive');
    });
    this.bindShortcut('g a', () => {
      window.location.href = Routing.generate('all');
    });
    this.bindShortcut('g t', () => {
      window.location.href = Routing.generate('tag');
    });
    this.bindShortcut('g c', () => {
      window.location.href = Routing.generate('config');
    });
    this.bindShortcut('g i', () => {
      window.location.href = Routing.generate('import');
    });
    this.bindShortcut('g d', () => {
      window.location.href = Routing.generate('developer');
    });
    this.bindShortcut('?', () => {
      window.location.href = Routing.generate('howto');
    });
    this.bindShortcut('g l', () => {
      window.location.href = Routing.generate('fos_user_security_logout');
    });

    /* open original article */
    this.bindShortcut('o', () => {
      if (!this.hasOpenOriginalTarget) {
        return;
      }

      this.openOriginalTarget.click();
    });

    /* mark as favorite */
    this.bindShortcut('f', () => {
      if (!this.hasMarkAsFavoriteTarget) {
        return;
      }

      this.markAsFavoriteTarget.click();
    });

    /* mark as read */
    this.bindShortcut('a', () => {
      if (!this.hasMarkAsReadTarget) {
        return;
      }

      this.markAsReadTarget.click();
    });

    /* delete */
    this.bindShortcut('del', () => {
      if (!this.hasDeleteEntryTarget) {
        return;
      }

      this.deleteEntryTarget.click();
    });

    /* Actions */
    this.bindShortcut('g n', (e) => {
      if (!this.hasShowAddUrlTarget) {
        return;
      }

      e.preventDefault();
      this.showAddUrlTarget.click();
    });

    this.bindShortcut('s', (e) => {
      if (!this.hasShowSearchTarget) {
        return;
      }

      e.preventDefault();
      this.showSearchTarget.click();
    });

    this.bindShortcut('esc', (e) => {
      if (!this.hasShowActionsTarget) {
        return;
      }

      e.preventDefault();
      this.showActionsTarget.click();
    });

    this.stopCallback = (e, element, combo) => {
      // allow esc key to be used in input fields of topbar
      if (combo === 'esc' && element.dataset.topbarTarget !== undefined) {
        return false;
      }

      return this.originalStopCallback(e, element);
    };
    Mousetrap.prototype.stopCallback = this.stopCallback;

    this.bindShortcut('right', () => {
      if (!this.hasEntriesNavigationOutlet) {
        return;
      }

      this.entriesNavigationOutlet.selectRightCard();
    });

    this.bindShortcut('left', () => {
      if (!this.hasEntriesNavigationOutlet) {
        return;
      }

      this.entriesNavigationOutlet.selectLeftCard();
    });

    this.bindShortcut('enter', () => {
      if (!this.hasEntriesNavigationOutlet) {
        return;
      }

      this.entriesNavigationOutlet.selectCurrentCard();
    });
  }

  disconnect() {
    this.boundShortcuts.forEach((combo) => {
      Mousetrap.unbind(combo);
    });
    this.boundShortcuts = [];

    if (Mousetrap.prototype.stopCallback === this.stopCallback) {
      Mousetrap.prototype.stopCallback = this.originalStopCallback;
    }
  }

  bindShortcut(combo, callback) {
    Mousetrap.bind(combo, callback);
    this.boundShortcuts.push(combo);
  }
}
