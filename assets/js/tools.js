import $ from 'jquery';

function supportsLocalStorage() {
  try {
    return 'localStorage' in window && window.localStorage !== null;
  } catch (e) {
    return false;
  }
}

function savePercent(id, percent) {
  if (!supportsLocalStorage()) { return false; }
  localStorage[`wallabag.article.${id}.percent`] = percent;
  return true;
}

function retrievePercent(id, resized) {
  if (!supportsLocalStorage()) { return false; }

  const bheight = $(document).height();
  const percent = localStorage[`wallabag.article.${id}.percent`];
  const scroll = bheight * percent;

  if (!resized) {
    window.scrollTo({
      top: scroll,
      behavior: 'smooth',
    });
  }

  return true;
}

function initFilters() {
  // no display if filters not available
  if ($('div').is('#filters')) {
    $('#filters.sidenav').sidenav({ edge: 'right' });
    $('#clear_form_filters').on('click', () => {
      $('#filters input').val('');
      $('#filters :checked').removeAttr('checked');

      return false;
    });
  }
}

function initExport() {
  // no display if export not available
  if ($('div').is('#export')) {
    $('#export.sidenav').sidenav({ edge: 'right' });
  }
}

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
  savePercent,
  retrievePercent,
  initExport,
  initFilters,
  initPreviewText,
};
