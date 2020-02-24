/* jQuery */
import $ from 'jquery';

/* Annotations */
import annotator from 'annotator';

import ClipboardJS from 'clipboard';
import 'mathjax/es5/tex-svg';

/* Fonts */
import 'material-design-icons-iconfont/dist/material-design-icons.css';
import 'lato-font/css/lato-font.css';
import './global.scss';

/* Shortcuts */
import './js/shortcuts/entry';
import './js/shortcuts/main';

/* Hightlight */
import './js/highlight';

import { savePercent, retrievePercent } from './js/tools';

/* ==========================================================================
 Annotations & Remember position
 ========================================================================== */

$(document).ready(() => {
  if ($('article').length) {
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
