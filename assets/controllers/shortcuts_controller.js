import { Controller } from '@hotwired/stimulus';
import Mousetrap from 'mousetrap';

export default class extends Controller {
  static targets = ['openOriginal', 'markAsFavorite', 'markAsRead', 'deleteEntry', 'showAddUrl', 'showSearch', 'showActions'];

  static outlets = ['entries-navigation'];

  connect() {
    /* Go to */
    Mousetrap.bind('g u', () => {
      window.location.href = Routing.generate('homepage');
    });
    Mousetrap.bind('g s', () => {
      window.location.href = Routing.generate('starred');
    });
    Mousetrap.bind('g r', () => {
      window.location.href = Routing.generate('archive');
    });
    Mousetrap.bind('g a', () => {
      window.location.href = Routing.generate('all');
    });
    Mousetrap.bind('g t', () => {
      window.location.href = Routing.generate('tag');
    });
    Mousetrap.bind('g c', () => {
      window.location.href = Routing.generate('config');
    });
    Mousetrap.bind('g i', () => {
      window.location.href = Routing.generate('import');
    });
    Mousetrap.bind('g d', () => {
      window.location.href = Routing.generate('developer');
    });
    Mousetrap.bind('?', () => {
      window.location.href = Routing.generate('howto');
    });
    Mousetrap.bind('g l', () => {
      window.location.href = Routing.generate('fos_user_security_logout');
    });

    /* open original article */
    Mousetrap.bind('o', () => {
      if (!this.hasOpenOriginalTarget) {
        return;
      }

      this.openOriginalTarget.click();
    });

    /* mark as favorite */
    Mousetrap.bind('f', () => {
      if (!this.hasMarkAsFavoriteTarget) {
        return;
      }

      this.markAsFavoriteTarget.click();
    });

    /* mark as read */
    Mousetrap.bind('a', () => {
      if (!this.hasMarkAsReadTarget) {
        return;
      }

      this.markAsReadTarget.click();
    });

    /* delete */
    Mousetrap.bind('del', () => {
      if (!this.hasDeleteEntryTarget) {
        return;
      }

      this.deleteEntryTarget.click();
    });

    /* Actions */
    Mousetrap.bind('g n', (e) => {
      if (!this.hasShowAddUrlTarget) {
        return;
      }

      e.preventDefault();
      this.showAddUrlTarget.click();
    });

    Mousetrap.bind('s', (e) => {
      if (!this.hasShowSearchTarget) {
        return;
      }

      e.preventDefault();
      this.showSearchTarget.click();
    });

    Mousetrap.bind('esc', (e) => {
      if (!this.hasShowActionsTarget) {
        return;
      }

      e.preventDefault();
      this.showActionsTarget.click();
    });

    const originalStopCallback = Mousetrap.prototype.stopCallback;

    Mousetrap.prototype.stopCallback = (e, element, combo) => {
      // allow esc key to be used in input fields of topbar
      if (combo === 'esc' && element.dataset.topbarTarget !== undefined) {
        return false;
      }

      return originalStopCallback(e, element);
    };

    Mousetrap.bind('right', () => {
      if (!this.hasEntriesNavigationOutlet) {
        return;
      }

      this.entriesNavigationOutlet.selectRightCard();
    });

    Mousetrap.bind('left', () => {
      if (!this.hasEntriesNavigationOutlet) {
        return;
      }

      this.entriesNavigationOutlet.selectLeftCard();
    });

    Mousetrap.bind('enter', () => {
      if (!this.hasEntriesNavigationOutlet) {
        return;
      }

      this.entriesNavigationOutlet.selectCurrentCard();
    });
  }
}
