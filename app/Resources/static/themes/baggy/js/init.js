/* jQuery */
import $ from 'jquery';

/* eslint-disable no-unused-vars */
/* jquery has default scope */
import cookie from 'jquery.cookie';
import ui from 'jquery-ui-browserify';
/* eslint-enable no-unused-vars */

/* Annotations */
import annotator from 'annotator';

/* Shortcuts */
import './shortcuts/main';
import './shortcuts/entry';
import '../../_global/js/shortcuts/main';
import '../../_global/js/shortcuts/entry';

/* Tools */
import { savePercent, retrievePercent } from '../../_global/js/tools';
import toggleSaveLinkForm from './uiTools';

global.jquery = $;

$.fn.ready(() => {
  const $listmode = $('#listmode');
  const $listentries = $('#list-entries');

  /* ==========================================================================
     Menu
     ========================================================================== */

  $('#menu').click(() => {
    $('#links').toggleClass('menu--open');
    const content = $('#content');
    if (content.hasClass('opacity03')) {
      content.removeClass('opacity03');
    }
  });

  /* ==========================================================================
     List mode or Table Mode
     ========================================================================== */

  $listmode.click(() => {
    if ($.cookie('listmode') === 1) {
      // Cookie
      $.removeCookie('listmode');

      $listentries.removeClass('listmode');
      $listmode.removeClass('tablemode');
      $listmode.addClass('listmode');
    } else {
      // Cookie
      $.cookie('listmode', 1, { expires: 365 });

      $listentries.addClass('listmode');
      $listmode.removeClass('listmode');
      $listmode.addClass('tablemode');
    }
  });

  /* ==========================================================================
     Cookie listmode
     ========================================================================== */

  if ($.cookie('listmode') === 1) {
    $listentries.addClass('listmode');
    $listmode.removeClass('listmode');
    $listmode.addClass('tablemode');
  }

  /* ==========================================================================
     Add tag panel
     ========================================================================== */


  $('#nav-btn-add-tag').on('click', () => {
    $('.nav-panel-add-tag').toggle(100);
    $('.nav-panel-menu').addClass('hidden');
    $('#tag_label').focus();
    return false;
  });

  /**
   * Filters & Export
   */
  // no display if filters not available
  if ($('div').is('#filters')) {
    $('#button_filters').show();
    $('#clear_form_filters').on('click', () => {
      $('#filters input').val('');
      $('#filters :checked').removeAttr('checked');
      return false;
    });
  }

  /* ==========================================================================
     Annotations & Remember position
     ========================================================================== */

  if ($('article').length) {
    const app = new annotator.App();

    app.include(annotator.ui.main, {
      element: document.querySelector('article'),
    });

    const x = JSON.parse($('#annotationroutes').html());
    app.include(annotator.storage.http, x);

    app.start().then(() => {
      app.annotations.load({ entry: x.entryId });
    });

    $(window).scroll(() => {
      const scrollTop = $(window).scrollTop();
      const docHeight = $(document).height();
      const scrollPercent = (scrollTop) / (docHeight);
      const scrollPercentRounded = Math.round(scrollPercent * 100) / 100;
      savePercent(x.entryId, scrollPercentRounded);
    });

    retrievePercent(x.entryId);

    $(window).resize(() => {
      retrievePercent(x.entryId);
    });
  }

  /**
   * Close window after adding entry if popup
   */
  const currentUrl = window.location.href;
  if (currentUrl.match('&closewin=true')) {
    window.close();
  }

  /**
   * Tags autocomplete
   */
  /**
   * Not working on v2
   *

  $('#value').bind('keydown', (event) => {
    if (event.keyCode === $.ui.keyCode.TAB && $(this).data('ui-autocomplete').menu.active) {
      event.preventDefault();
    }
  }).autocomplete({
    source: function source(request, response) {
      $.getJSON('./?view=tags', {
        term: extractLast(request.term),
        //id: $(':hidden#entry_id').val()
      }, response);
    },
    search: function search() {
      // custom minLength
      const term = extractLast(this.value);
      return term.length >= 1;
    },
    focus: function focus() {
      // prevent value inserted on focus
      return false;
    },
    select: function select(event, ui) {
      const terms = split(this.value);
      // remove the current input
      terms.pop();
      // add the selected item
      terms.push(ui.item.value);
      // add placeholder to get the comma-and-space at the end
      terms.push('');
      this.value = terms.join(', ');
      return false;
    },
  });
  */

  //---------------------------------------------------------------------------
  // Close the message box when the user clicks the close icon
  //---------------------------------------------------------------------------
  $('a.closeMessage').on('click', () => {
    $(this).parents('div.messages').slideUp(300, () => { $(this).remove(); });
    return false;
  });

  $('#search-form').hide();
  $('#bagit-form').hide();
  $('#filters').hide();
  $('#download-form').hide();

  //---------------------------------------------------------------------------
  // Toggle the 'Search' popup in the sidebar
  //---------------------------------------------------------------------------
  function toggleSearch() {
    $('#search-form').toggle();
    $('#search').toggleClass('current');
    $('#search').toggleClass('active-current');
    $('#search-arrow').toggleClass('arrow-down');
    if ($('#search').hasClass('current')) {
      $('#content').addClass('opacity03');
    } else {
      $('#content').removeClass('opacity03');
    }
  }

  //---------------------------------------------------------------------------
  // Toggle the 'Filter' popup on entries list
  //---------------------------------------------------------------------------
  function toggleFilter() {
    $('#filters').toggle();
  }

  //---------------------------------------------------------------------------
  // Toggle the 'Download' popup on entries list
  //---------------------------------------------------------------------------
  function toggleDownload() {
    $('#download-form').toggle();
  }

  //---------------------------------------------------------------------------
  // Toggle the 'Save a Link' popup in the sidebar
  //---------------------------------------------------------------------------
  function toggleBagit() {
    $('#bagit-form').toggle();
    $('#bagit').toggleClass('current');
    $('#bagit').toggleClass('active-current');
    $('#bagit-arrow').toggleClass('arrow-down');
    if ($('#bagit').hasClass('current')) {
      $('#content').addClass('opacity03');
    } else {
      $('#content').removeClass('opacity03');
    }
  }

  //---------------------------------------------------------------------------
  // Close all #links popups in the sidebar
  //---------------------------------------------------------------------------
  function closePopups() {
    $('#links .messages').hide();
    $('#links > li > a').removeClass('active-current');
    $('#links > li > a').removeClass('current');
    $('[id$=-arrow]').removeClass('arrow-down');
    $('#content').removeClass('opacity03');
  }

  $('#search').click(() => {
    closePopups();
    toggleSearch();
    $('#searchfield').focus();
  });

  $('.filter-btn').click(() => {
    closePopups();
    toggleFilter();
  });

  $('.download-btn').click(() => {
    closePopups();
    toggleDownload();
  });

  $('#bagit').click(() => {
    closePopups();
    toggleBagit();
    $('#plainurl').focus();
  });

  $('#search-form-close').click(() => {
    toggleSearch();
  });

  $('#filter-form-close').click(() => {
    toggleFilter();
  });

  $('#download-form-close').click(() => {
    toggleDownload();
  });

  $('#bagit-form-close').click(() => {
    toggleBagit();
  });

  const $bagitFormForm = $('#bagit-form-form');

  /* ==========================================================================
   bag it link and close button
   ========================================================================== */

  // send 'bag it link' form request via ajax
  $bagitFormForm.submit((event) => {
    $('body').css('cursor', 'wait');
    $('#add-link-result').empty();

    $.ajax({
      type: $bagitFormForm.attr('method'),
      url: $bagitFormForm.attr('action'),
      data: $bagitFormForm.serialize(),
      success: function success() {
        $('#add-link-result').html('Done!');
        $('#plainurl').val('');
        $('#plainurl').blur('');
        $('body').css('cursor', 'auto');
      },
      error: function error() {
        $('#add-link-result').html('Failed!');
        $('body').css('cursor', 'auto');
      },
    });

    event.preventDefault();
  });

  /* ==========================================================================
   Process all links inside an article
   ========================================================================== */

  $('article a[href^="http"]').after(
      () => `<a href="${$(this).attr('href')}" class="add-to-wallabag-link-after" ` +
      'alt="add to wallabag" title="add to wallabag"></a>'
  );

  $('.add-to-wallabag-link-after').click((event) => {
    toggleSaveLinkForm($(this).attr('href'), event);
    event.preventDefault();
  });
});
