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

const mobileMaxWidth = 993;

function darkTheme() {
  const rootEl = document.querySelector('html');
  const themeDom = {
    darkClass: 'dark-theme',

    toggleClass(el) {
      return el.classList.toggle(this.darkClass);
    },

    addClass(el) {
      return el.classList.add(this.darkClass);
    },

    removeClass(el) {
      return el.classList.remove(this.darkClass);
    },
  };
  const themeCookie = {
    values: {
      light: 'light',
      dark: 'dark',
    },

    name: 'theme',

    getValue(isDarkTheme) {
      return isDarkTheme ? this.values.dark : this.values.light;
    },

    setCookie(isDarkTheme) {
      const value = this.getValue(isDarkTheme);
      document.cookie = `${this.name}=${value};samesite=Lax;path=/;max-age=31536000`;
    },

    exists() {
      return document.cookie.split(';').map((cookie) => cookie.split('=')).filter((cookie) => cookie[0] === 'theme').length;
    },
  };
  if (themeCookie.exists() === 0 && typeof window.matchMedia === 'function') {
    const isPreferedColorSchemeDark = window.matchMedia('(prefers-color-scheme: dark)').matches === true;
    if (isPreferedColorSchemeDark) {
      themeDom.addClass(rootEl);
    }
    window.matchMedia('(prefers-color-scheme: dark)').addListener(
      (e) => themeDom[e.matches ? 'addClass' : 'removeClass'](rootEl),
    );
  }
  const themeButton = document.querySelector('.js-theme-toggle');
  if (themeButton) {
    themeButton.addEventListener('click', () => {
      const isDarkTheme = themeDom.toggleClass(rootEl);
      themeCookie.setCookie(isDarkTheme);
    });
  }
}

const stickyNav = () => {
  const nav = $('.js-entry-nav-top');
  $('[data-toggle="actions"]').click(() => {
    nav.toggleClass('entry-nav-top--sticky');
  });
};

const articleScroll = () => {
  const articleEl = $('#article');
  if (articleEl.length > 0) {
    $(window).scroll(() => {
      const s = $(window).scrollTop();
      const d = $(document).height();
      const c = $(window).height();
      const articleElBottom = articleEl.offset().top + articleEl.height();
      const scrollPercent = (s / (d - c)) * 100;
      $('.progress .determinate').css('width', `${scrollPercent}%`);
      const fixedActionBtn = $('.js-fixed-action-btn');
      const toggleScrollDataName = 'toggle-auto';
      if ((s + c) > articleElBottom) {
        fixedActionBtn.data(toggleScrollDataName, true);
        fixedActionBtn.openFAB();
      } else if (fixedActionBtn.data(toggleScrollDataName) === true) {
        fixedActionBtn.data(toggleScrollDataName, false);
        fixedActionBtn.closeFAB();
      }
    });
  }
};

$(document).ready(() => {
  // sideNav
  $('.button-collapse').sideNav();
  darkTheme();
  $('select').material_select();
  $('.collapsible').collapsible({
    accordion: false,
  });
  $('.datepicker').pickadate({
    selectMonths: true,
    selectYears: 15,
    formatSubmit: 'yyyy-mm-dd',
    hiddenName: false,
    format: 'yyyy-mm-dd',
    container: 'body',
  });

  $('.dropdown-trigger').dropdown({ hover: false });

  initFilters();
  initExport();
  initRandom();
  stickyNav();
  articleScroll();

  const toggleNav = (toShow, toFocus) => {
    $('.nav-panel-actions').hide(100);
    $(toShow).show(100);
    $(toFocus).focus();
  };

  $('#nav-btn-add-tag').on('click', () => {
    $('.nav-panel-add-tag').toggle(100);
    $('.nav-panel-menu').addClass('hidden');
    if (window.innerWidth < mobileMaxWidth) {
      $('.side-nav').sideNav('hide');
    }
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
    return false;
  });

  const mainCheckboxes = document.querySelectorAll('[data-js="checkboxes-toggle"]');
  if (mainCheckboxes.length) {
    [...mainCheckboxes].forEach((el) => {
      el.addEventListener('click', () => {
        const checkboxes = document.querySelectorAll(el.dataset.toggle);
        [...checkboxes].forEach((checkbox) => {
          const checkboxClone = checkbox;
          checkboxClone.checked = el.checked;
        });
      });
    });
  }
});
