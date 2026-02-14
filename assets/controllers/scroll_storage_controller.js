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

    // 2-second debounce: saves progress soon after the user stops scrolling while
    // avoiding excessive requests. Below the server-side 5-second rate limit, so
    // at most one request per scroll pause reaches the server.
    clearTimeout(this.syncTimeout);
    this.syncTimeout = setTimeout(() => {
      this.syncWithRetry(progress, now);
    }, 2000);
  }

  async syncWithRetry(progress, timestamp, attempt = 0) {
    const maxRetries = 3;
    try {
      const response = await fetch(`/reading-progress/${this.entryIdValue}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `reading_progress=${progress}&reading_progress_updated_at=${timestamp}&token=${encodeURIComponent(this.csrfTokenValue)}`,
      });

      // Don't retry on 4xx (client errors like CSRF failure or 429 rate limit)
      if (response.ok || (response.status >= 400 && response.status < 500)) {
        return;
      }

      // Retry on 5xx server errors
      if (attempt < maxRetries) {
        const delay = 5000 * (2 ** attempt); // 5s, 10s, 20s
        setTimeout(() => this.syncWithRetry(progress, timestamp, attempt + 1), delay);
      }
    } catch {
      // Retry on network errors
      if (attempt < maxRetries) {
        const delay = 5000 * (2 ** attempt);
        setTimeout(() => this.syncWithRetry(progress, timestamp, attempt + 1), delay);
      }
    }
  }
}
