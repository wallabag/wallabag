import $ from 'jquery';

/* Materialize imports */
import '@materializecss/materialize/dist/css/materialize.css';
import '@materializecss/materialize/dist/js/materialize';

/* Annotations */
import annotator from 'annotator';

import ClipboardJS from 'clipboard';
import 'mathjax/es5/tex-svg';

/* jrQrcode */
import jrQrcode from 'jr-qrcode';

/* Fonts */
import 'material-design-icons-iconfont/dist/material-design-icons.css';
import 'lato-font/css/lato-font.css';
import 'open-dyslexic/open-dyslexic-regular.css';
import '@fontsource/atkinson-hyperlegible';
import '@fontsource/eb-garamond';
import '@fontsource/montserrat';
import '@fontsource/oswald';

/* Highlight */
import './js/highlight';

/* Tools */
import {
  savePercent, retrievePercent, initExport, initFilters, initRandom, initPreviewText,
} from './js/tools';

/* Import shortcuts */
import './js/shortcuts/main';
import './js/shortcuts/entry';

/* Theme style */
import './scss/index.scss';

const mobileMaxWidth = 993;

/* ==========================================================================
 Annotations & Remember position
 ========================================================================== */

$(document).ready(() => {
  if ($('#article').length) {
    const app = new annotator.App();

    app.include(annotator.ui.main, {
      element: document.querySelector('article'),
    });

    const authorization = {
      permits() { return true; },
    };
    app.registry.registerUtility(authorization, 'authorizationPolicy');

    const x = JSON.parse($('#annotationroutes').html());
    app.include(annotator.storage.http, $.extend({}, x, {
      onError(msg, xhr) {
        if (!Object.prototype.hasOwnProperty.call(xhr, 'responseJSON')) {
          annotator.notification.banner('An error occurred', 'error');
          return;
        }
        $.each(xhr.responseJSON.children, (k, v) => {
          if (v.errors) {
            $.each(v.errors, (n, errorText) => {
              annotator.notification.banner(errorText, 'error');
            });
          }
        });
      },
    }));

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
      retrievePercent(x.entryId, true);
    });
  }

  document.querySelectorAll('[data-handler=tag-rename]').forEach((item) => {
    const current = item;
    current.wallabag_edit_mode = false;
    current.onclick = (event) => {
      const target = event.currentTarget;

      if (target.wallabag_edit_mode === false) {
        $(target.parentNode.querySelector('[data-handle=tag-link]')).addClass('hidden');
        $(target.parentNode.querySelector('[data-handle=tag-rename-form]')).removeClass('hidden');
        target.parentNode.querySelector('[data-handle=tag-rename-form] input').focus();
        target.querySelector('.material-icons').innerHTML = 'done';

        target.wallabag_edit_mode = true;
      } else {
        target.parentNode.querySelector('[data-handle=tag-rename-form]').submit();
      }
    };
  });

  // mimic radio button because emailTwoFactor is a boolean
  $('#update_user_googleTwoFactor').on('change', () => {
    $('#update_user_emailTwoFactor').prop('checked', false);
  });

  $('#update_user_emailTwoFactor').on('change', () => {
    $('#update_user_googleTwoFactor').prop('checked', false);
  });

  // same mimic for super admin
  $('#user_googleTwoFactor').on('change', () => {
    $('#user_emailTwoFactor').prop('checked', false);
  });

  $('#user_emailTwoFactor').on('change', () => {
    $('#user_googleTwoFactor').prop('checked', false);
  });

  // handle copy to clipboard for developer stuff
  const clipboard = new ClipboardJS('.btn');
  clipboard.on('success', (e) => {
    e.clearSelection();
  });
});

(function darkTheme() {
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

    removeCookie() {
      document.cookie = `${this.name}=auto;samesite=Lax;path=/;max-age=0`;
    },

    exists() {
      return document.cookie.split(';').some((cookie) => cookie.trim().startsWith(`${this.name}=`));
    },
  };
  const preferedColorScheme = {
    choose() {
      const themeCookieExists = themeCookie.exists();
      if (this.isAvailable() && !themeCookieExists) {
        const isPreferedColorSchemeDark = window.matchMedia('(prefers-color-scheme: dark)').matches === true;
        if (!themeCookieExists) {
          themeDom[isPreferedColorSchemeDark ? 'addClass' : 'removeClass'](rootEl);
        }
      }
    },

    isAvailable() {
      return typeof window.matchMedia === 'function';
    },

    init() {
      if (!this.isAvailable()) {
        return false;
      }
      this.choose();
      window.matchMedia('(prefers-color-scheme: dark)').addListener(() => {
        this.choose();
      });
      return true;
    },
  };

  const addDarkThemeListeners = () => {
    $(document).ready(() => {
      const lightThemeButtons = document.querySelectorAll('.js-theme-toggle[data-theme="light"]');
      [...lightThemeButtons].map((lightThemeButton) => {
        lightThemeButton.addEventListener('click', (e) => {
          e.preventDefault();
          themeDom.removeClass(rootEl);
          themeCookie.setCookie(false);
        });
        return true;
      });
      const darkThemeButtons = document.querySelectorAll('.js-theme-toggle[data-theme="dark"]');
      [...darkThemeButtons].map((darkThemeButton) => {
        darkThemeButton.addEventListener('click', (e) => {
          e.preventDefault();
          themeDom.addClass(rootEl);
          themeCookie.setCookie(true);
        });
        return true;
      });
      const autoThemeButtons = document.querySelectorAll('.js-theme-toggle[data-theme="auto"]');
      [...autoThemeButtons].map((autoThemeButton) => {
        autoThemeButton.addEventListener('click', (e) => {
          e.preventDefault();
          themeCookie.removeCookie();
          preferedColorScheme.choose();
        });
        return true;
      });
    });
  };

  preferedColorScheme.init();
  addDarkThemeListeners();
}());

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
  // sidenav
  $('.sidenav').sidenav();
  $('select').formSelect();
  $('.collapsible[data-collapsible="accordion"]').collapsible();
  $('.collapsible[data-collapsible="expandable"]').collapsible({
    accordion: false,
  });

  $('.dropdown-trigger').dropdown({ hover: false });
  $('.dropdown-trigger[data-covertrigger="false"][data-constrainwidth="false"]').dropdown({
    hover: false,
    coverTrigger: false,
    constrainWidth: false,
  });

  $('.tabs').tabs();
  $('.tooltipped').tooltip();

  initFilters();
  initExport();
  initRandom();
  stickyNav();
  articleScroll();
  initPreviewText();

  const toggleNav = (toShow, toFocus) => {
    $('.nav-panel-actions').hide(100);
    $(toShow).show(100);
    $(toFocus).focus();
  };

  $('#nav-btn-add-tag').on('click', () => {
    $('.nav-panel-add-tag').toggle(100);
    $('.nav-panel-menu').addClass('hidden');
    if (window.innerWidth < mobileMaxWidth) {
      $('.sidenav').sidenav('close');
    }
    $('#tag_label').focus();
    return false;
  });

  $('#nav-btn-add').on('click', () => {
    toggleNav('.nav-panel-add', '#entry_url');
    return false;
  });

  $('#config_fontsize').on('input', () => {
    const value = $('#config_fontsize').val();
    const css = `${value}em`;
    $('#preview-content').css('font-size', css);
  });

  $('#config_font').on('change', () => {
    const value = $('#config_font').val();
    $('#preview-content').css('font-family', value);
  });

  $('#config_lineHeight').on('input', () => {
    const value = $('#config_lineHeight').val();
    const css = `${value}em`;
    $('#preview-content').css('line-height', css);
  });

  $('#config_maxWidth').on('input', () => {
    const value = $('#config_maxWidth').val();
    const css = `${value}em`;
    $('#preview-article').css('max-width', css);
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
  $('form[name="form_mass_action"] input[name="tags"]').on('keydown', (e) => {
    if (e.key === 'Enter') {
      e.preventDefault();
      $('form[name="form_mass_action"] button[name="tag"]').trigger('click');
    }
  });

  document.querySelectorAll('img.jr-qrcode').forEach((qrcode) => {
    const src = jrQrcode.getQrBase64(qrcode.getAttribute('data-url'));

    qrcode.setAttribute('src', src);
  });
});
