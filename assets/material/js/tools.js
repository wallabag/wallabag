import $ from 'jquery';

function initFilters() {
  // no display if filters not available
  if ($('div').is('#filters')) {
    $('#button_filters').show();
    $('.js-filters-action').sideNav({ edge: 'right' });
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
    $('#button_export').show();
    $('.js-export-action').sideNav({ edge: 'right' });
  }
}

function initRandom() {
  // no display if export (ie: entries) not available
  if ($('div').is('#export')) {
    $('#button_random').show();
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
  initExport,
  initFilters,
  initRandom,
  initPreviewText,
};
