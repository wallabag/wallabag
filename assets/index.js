import './bootstrap';

import $ from 'jquery';

/* Materialize imports */
import '@materializecss/materialize/dist/css/materialize.css';
import '@materializecss/materialize/dist/js/materialize';

import 'mathjax/es5/tex-svg';

/* Fonts */
import 'material-design-icons-iconfont/dist/material-design-icons.css';
import 'lato-font/css/lato-font.css';
import 'open-dyslexic/open-dyslexic-regular.css';
import '@fontsource/atkinson-hyperlegible';
import '@fontsource/eb-garamond';
import '@fontsource/montserrat';
import '@fontsource/oswald';

/* Import shortcuts */
import './js/shortcuts/main';
import './js/shortcuts/entry';

/* Theme style */
import './scss/index.scss';

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

$(document).ready(() => {
  $('#nav-btn-add-tag').on('click', () => {
    $('.nav-panel-add-tag').toggle();
    $('.nav-panel-menu').addClass('hidden');
    $('#tag_label').focus();
    return false;
  });
});
