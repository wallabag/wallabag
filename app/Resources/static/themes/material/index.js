import $ from 'jquery';

/* Materialize imports */
import 'materialize-css/dist/css/materialize.css';
import 'materialize-css/dist/js/materialize';

/* Global imports */
import '../_global/index';

/* Tools */
import { initExport, initFilters, initNotifications } from './js/tools';

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
  initNotifications();

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
    $('#search_entry_term').focus();
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
});
