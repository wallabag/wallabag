/* jQuery */
import $ from 'jquery';

/* Annotations */
import annotator from 'annotator';

/* Tools */
import { savePercent, retrievePercent, initFilters, initExport } from '../../_global/js/tools';

/* Import shortcuts */
import './shortcuts/main';
import './shortcuts/entry';
import '../../_global/js/shortcuts/main';
import '../../_global/js/shortcuts/entry';

require('materialize'); // eslint-disable-line

global.jQuery = $;

$(document).ready(() => {
  // sideNav
  $('.button-collapse').sideNav();
  $('select').material_select();
  $('.collapsible').collapsible({
    accordion: false,
  });
  $('.datepicker').pickadate({
    selectMonths: true,
    selectYears: 15,
    formatSubmit: 'dd/mm/yyyy',
    hiddenName: true,
    format: 'dd/mm/yyyy',
  });
  initFilters();
  initExport();

  $('#nav-btn-add-tag').on('click', () => {
    $('.nav-panel-add-tag').toggle(100);
    $('.nav-panel-menu').addClass('hidden');
    $('#tag_label').focus();
    return false;
  });
  $('#nav-btn-add').on('click', () => {
    $('.nav-panel-buttom').hide(100);
    $('.nav-panel-add').show(100);
    $('.nav-panels .action').hide(100);
    $('.nav-panel-menu').addClass('hidden');
    $('.nav-panels').css('background', 'white');
    $('#entry_url').focus();
    return false;
  });
  $('#nav-btn-search').on('click', () => {
    $('.nav-panel-buttom').hide(100);
    $('.nav-panel-search').show(100);
    $('.nav-panels .action').hide(100);
    $('.nav-panel-menu').addClass('hidden');
    $('.nav-panels').css('background', 'white');
    $('#searchfield').focus();
    return false;
  });
  $('.close').on('click', () => {
    $('.nav-panel-add').hide(100);
    $('.nav-panel-search').hide(100);
    $('.nav-panel-buttom').show(100);
    $('.nav-panels .action').show(100);
    $('.nav-panel-menu').removeClass('hidden');
    $('.nav-panels').css('background', 'transparent');
    return false;
  });
  $(window).scroll(() => {
    const s = $(window).scrollTop();
    const d = $(document).height();
    const c = $(window).height();
    const scrollPercent = (s / (d - c)) * 100;
    $('.progress .determinate').css('width', `${scrollPercent}%`);
  });

/* ==========================================================================
   Annotations & Remember position
   ========================================================================== */

  if ($('article').length) {
    const app = new annotator.App();
    const x = JSON.parse($('#annotationroutes').html());

    app.include(annotator.ui.main, {
      element: document.querySelector('article'),
    });

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
});
