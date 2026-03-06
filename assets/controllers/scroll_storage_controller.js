import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  static values = {
    entryId: Number,
    serverProgress: { type: Number, default: 0 },
    serverProgressTimestamp: { type: Number, default: 0 },
    csrfToken: String,
  };

  connect() {
    const localPercent = parseFloat(localStorage[`wallabag.article.${this.entryIdValue}.percent`]) || 0;
    const localTimestamp = parseInt(localStorage[`wallabag.article.${this.entryIdValue}.timestamp`], 10) || 0;

    const serverProgress = this.serverProgressValue;
    const serverTimestamp = this.serverProgressTimestampValue;

    if (serverTimestamp > localTimestamp && serverProgress > 0) {
      const totalHeight = document.documentElement.scrollHeight - window.innerHeight;
      if (totalHeight > 0) {
        window.scrollTo({ top: totalHeight * (serverProgress / 100), behavior: 'smooth' });
        localStorage[`wallabag.article.${this.entryIdValue}.percent`] = serverProgress / 100;
        localStorage[`wallabag.article.${this.entryIdValue}.timestamp`] = serverTimestamp;
      }
    } else if (localPercent > 0) {
      window.scrollTo({ top: window.innerHeight * localPercent, behavior: 'smooth' });
    }
  }

  disconnect() {
    clearTimeout(this.syncTimeout);
  }

  saveScroll() {
    const now = Math.floor(Date.now() / 1000);

    const scrollPercent = Math.round((window.scrollY / window.innerHeight) * 100) / 100;
    localStorage[`wallabag.article.${this.entryIdValue}.percent`] = scrollPercent;
    localStorage[`wallabag.article.${this.entryIdValue}.timestamp`] = now;

    const totalHeight = document.documentElement.scrollHeight - window.innerHeight;
    const progress = totalHeight > 0
      ? Math.min(100, Math.max(0, Math.round((window.scrollY / totalHeight) * 100)))
      : 0;

    clearTimeout(this.syncTimeout);
    this.syncTimeout = setTimeout(() => {
      this.syncProgress(progress, now);
    }, 2000);
  }

  async syncProgress(progress, timestamp) {
    await fetch(`/reading-progress/${this.entryIdValue}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `reading_progress=${progress}&reading_progress_updated_at=${timestamp}&token=${encodeURIComponent(this.csrfTokenValue)}`,
    });
  }
}
