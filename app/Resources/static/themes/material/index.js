import $ from 'jquery';

/* Materialize imports */
import 'materialize-css/dist/css/materialize.css';
import 'materialize-css/dist/js/materialize';

/* Global imports */
import '../_global/index';

/* Tools */
import { initExport, initFilters, initRandom } from './js/tools';

/* Import shortcuts */
import './js/shortcuts/main';
import './js/shortcuts/entry';

/* Theme style */
import './css/index.scss';

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
    container: 'body',
  });

  initFilters();
  initExport();
  initRandom();

  const toggleNav = (toShow, toFocus) => {
    $('.nav-panel-actions').hide(100);
    $(toShow).show(100);
    $('.nav-panels').css('background', 'white');
    $(toFocus).focus();
  };

  $('#nav-btn-add-tag').on('click', () => {
    $('.nav-panel-add-tag').toggle(100);
    $('.nav-panel-menu').addClass('hidden');
    $('#tag_label').focus();
    return false;
  });

  $('#nav-btn-add').on('click', () => {
    toggleNav('.nav-panel-add', '#entry_url');
    return false;
  });

  const materialAddForm = $('.nav-panel-add');
  materialAddForm.on('submit', () => {
    materialAddForm.addClass('disabled');
    $('input#entry_url', materialAddForm).prop('readonly', true).trigger('blur');
  });

  $('#nav-btn-search').on('click', () => {
    toggleNav('.nav-panel-search', '#search_entry_term');
    return false;
  });

  $('.close').on('click', (e) => {
    $(e.target).parent('.nav-panel-item').hide(100);
    $('.nav-panel-actions').show(100);
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
});
