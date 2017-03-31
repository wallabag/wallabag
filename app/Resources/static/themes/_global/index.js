/* jQuery */
import $ from 'jquery';

/* Annotations */
import annotator from 'annotator';

/* Fonts */
import 'material-design-icons-iconfont/dist/material-design-icons.css';
import 'lato-font/css/lato-font.css';
import './global.scss';

/* Shortcuts*/
import './js/shortcuts/entry';
import './js/shortcuts/main';

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
});
