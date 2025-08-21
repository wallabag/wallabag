import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  static targets = ['previewArticle', 'previewContent', 'font', 'fontSize', 'lineHeight', 'maxWidth'];

  connect() {
    this.updatePreview();
  }

  updatePreview() {
    this.previewArticleTarget.style.maxWidth = `${this.maxWidthTarget.value}em`;
    this.previewContentTarget.style.fontFamily = this.fontTarget.value;
    this.previewContentTarget.style.fontSize = `${this.fontSizeTarget.value}em`;
    this.previewContentTarget.style.lineHeight = `${this.lineHeightTarget.value}em`;
  }
}
