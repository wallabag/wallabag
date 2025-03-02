import $ from 'jquery';

function initPreviewText() {
  // no display if preview_text not available
  if ($('div').is('#preview-article')) {
    const defaultFontFamily = $('#config_font').val();
    const defaultFontSize = $('#config_fontsize').val();
    const defaultLineHeight = $('#config_lineHeight').val();
    const defaultMaxWidth = $('#config_maxWidth').val();
    const previewContent = $('#preview-content');

    previewContent.css('font-family', defaultFontFamily);
    previewContent.css('font-size', `${defaultFontSize}em`);
    previewContent.css('line-height', `${defaultLineHeight}em`);
    $('#preview-article').css('max-width', `${defaultMaxWidth}em`);
  }
}

export {
  initPreviewText,
};
