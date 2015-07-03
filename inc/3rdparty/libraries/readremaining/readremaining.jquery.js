/*!
 * ReadRemaining.js 1.0.0
 * jQuery plugin to shows the reader how much time will need to get to the end of the article.
 * http://aerolab.github.io/readremaining.js/
 *
 * Copyright (c) 2015 Aerolab <info@aerolab.co>
 *
 * By http://twitter.com/pato_pitaluga for http://aerolab.co/
 *
 * Released under the MIT license
 * http://aerolab.github.io/readremaining.js/LICENSE.txt
 */
 
/*
 * Works much more precise when the content of the scrolling element is homogeneous. If the size of the text or complexity of images change in different parts of the scrolling element, then the accuracy will drop.
 * Has four parts or functions:
 * 1. The init: 1.1 set the variables, 1.2 measure and show the gauge for the first time if it was requested, 1.3 start the scroll listener, and 1.4 start the measurement interval.
 * 2. updateGauge: change the text in the gauge. Is called on start (if required), when the user stop scrolling after the delay.
 * 3. showGauge: is a function that make the gauge appear if the conditions are fulfilled. Called on start (if requested), or when stop scrolling.
 * 4. updateTime: a function called when the user is scrolling down to calculate the result for the getRemainingTime method.
 * 5. getRemainingTime: is a public method that returns the remaining time for reading an element with ReadRemaining.js initialized.
 */

;(function($, window, document, undefined) {

  var pluginName = "readRemaining",
    defaults = {
      showGaugeDelay   : 1000,           // Delay for the gauge, 0 if always visible.
      showGaugeOnStart : false,          // Show the gauge initially, before the user scroll.
      timeFormat       : '%mm %ss left',
      maxTimeToShow    : 20*60,          // Show remaining time only if is lower than x minutes (multiplied to seconds).
      minTimeToShow    : 10,             // Show remaining time only if is higher than x seconds (Who's that anxious? If is less than 10 seconds... just read).

      gaugeContainer   : '',             // if left '', the container will be the scrolling element.
      insertPosition   : 'prepend',      // 'append' or 'prepend' as required by style
      verboseMode      : false,          // Enable the console logs. For testing only.

      gaugeWrapper     : '',             // Optional, the element that define the visible scope for the gauge. If left "", the gauge will be visible all along. Can be multiple elements.
      topOffset        : 0,              // Distance between the top of the gaugeWrapper and the point where the gauge will start to appear. Some designs require this.
      bottomOffset     : 0               // Distance between bottom border where the box will appear and the bottom of the element.
    };

  function Plugin (element, options) { this.element = element; this.settings = $.extend({}, defaults, options); this._defaults = defaults; this._name = pluginName; this.init(); }

  $.extend(Plugin.prototype, {
    // 1.
    init: function() {
      // 1.1
      var instance = this; // required cause the "this" is lost on the .scroll(function()
      // Static settings that can be changed, but works better if left with these values.
      this.considerOnlyLast = 60; // for the measurement to be current, is set to consider only the average of the last x seconds. When this number is low, is more acurate, when is high, the difference between each calculation is more smooth.

      // Variables that need to be available in all the plugin functions
      this.pixelsMeasured = Array();
      this.timesMeasured = 0;
      this.endReached = false;
      this.updateWithNextMeasurement = false;

      this.scrollingElement = this.element;
      if ($(this.element).prop("tagName").toString().toLowerCase() == 'body') { this.scrollingElement = window; }

      this.currentScrollPos = $(this.scrollingElement).scrollTop();
      this.startScrollPos4measure = this.currentScrollPos;

      if (this.settings.gaugeContainer == '') this.settings.gaugeContainer = $(this.element); // The same element is the default gauge container if is not set otherwise via settings.

      var totalWordCount = $(this.element).text().split(' ').length;
      this.scrolleableContentHeight = $(this.element)[0].scrollHeight; // the full height is ok, not the remaining invisible, cause is used to measure with the full word count.

      // Don't worry, this average is used only for the first time when is set to appear at starting and the user haven't yet scrolled a bit. A second after user started scrolling, the speed will be personalized.
      var timeNeededForTextByWordCount = Math.ceil(totalWordCount/(200/60)); // When starting I'll asume a speed of 200 words per minute (in seconds) cause that's the average Jakob Nielsen is using for internet reading behavior studies: http://www.nngroup.com/articles/how-little-do-users-read/
      timeNeededForTextByWordCount += $(this.element).find('img').length*4;  // adds 4 seconds for every image. This should be improved, maybe can take in consideration the height of the image or can read a "data" value in which the user consider that each image will take to "read".
      var wordsPerScrollablePixel = totalWordCount/this.scrolleableContentHeight;
      var initialScrollingSpeedPxs = this.scrolleableContentHeight/timeNeededForTextByWordCount; // in pixels per second by simple Rule of Three
      this.averageScrollingSpeed = initialScrollingSpeedPxs;

      this.pixelsMeasured.push(initialScrollingSpeedPxs);
      for (var r = 2; r < this.considerOnlyLast; r++) { this.pixelsMeasured.push(initialScrollingSpeedPxs); } // Consider the initial speed as if the user has been reading constantly

      this.measureLimit = (650/60)/wordsPerScrollablePixel; // Ignore measures higher than x cause the user is not reading, but scrolling fast to get to a place. Measured in wpm (600 wpm from http://en.wikipedia.org/wiki/Speed_reading )

      var gaugeHtml = '<div class="readRemainingGauge hidden" style="visibility:hidden;display:block;"></div>';
      if ((this.settings.insertPosition == 'prepend') || (insertPosition == ''))
        this.settings.gaugeContainer.prepend(gaugeHtml);
      else
        this.settings.gaugeContainer.append(gaugeHtml);

      this.gauge = $(instance.settings.gaugeContainer).find('.readRemainingGauge');

      this.gaugeInitialAbsoluteTop = this.gauge.offset().top-this.currentScrollPos; // offset top takes in consideration even the margin positive or negative.
      this.gauge.attr('style', '').removeAttr('style');

      this.pixelsLeftToRead = Math.round(this.scrolleableContentHeight-this.currentScrollPos);

      if (this.settings.verboseMode) {
        console.log('Initial speed considering average words per minute: '+initialScrollingSpeedPxs.toFixed(2)+'px/s or '+(wordsPerScrollablePixel*initialScrollingSpeedPxs).toFixed(2)+'w/s ('+totalWordCount+' words in total)');
        var secondsTotal = Math.round(this.pixelsLeftToRead/initialScrollingSpeedPxs);
        console.log('Total of pixels to scroll: '+this.pixelsLeftToRead+'px at '+initialScrollingSpeedPxs.toFixed(2)+'px/s = '+Math.floor(secondsTotal/60)+'m '+(secondsTotal%60)+'s'+"\n\n");
      }

      // 1.2
      // On starting: Now that the speed is already measured, the gauge can appear if is set.
      this.updateTime();
      this.updateGauge();
      if (
        (this.settings.showGaugeOnStart) || 
        (this.currentScrollPos > 0)         // If is already scrolled then try to show de gauge even without the delay cause the user IS reading for sure.
      ) {
        this.showGauge();
      }

      // 1.3
      $(this.scrollingElement).scroll(function() {

        instance.currentScrollPos = $(this).scrollTop();
        instance.pixelsLeftToRead = Math.round(instance.scrolleableContentHeight-instance.currentScrollPos);

        if (!((typeof instance.lastScrollTop === 'undefined') && (instance.currentScrollPos > 0))) // when is not the case: page realoaded already scrolled
          instance.gauge.addClass('hidden');

        if (typeof instance.lastScrollTop === 'undefined')
          instance.lastScrollTop = instance.currentScrollPos; // to know when scrolling up or down inside the scroll event handler.

        if (instance.currentScrollPos+$(instance.scrollingElement).height() == $(instance.element)[0].scrollHeight) {
          instance.endReached = true;
          instance.gauge.addClass('hidden');
          if (instance.settings.verboseMode) { console.log('End of scroll reached. Estimated time to finish reading: '+instance.timeLeftMinutes+'m '+instance.timeLeftSeconds+'s. at '+instance.averageScrollingSpeed.toFixed(2)+'px/s'); }
          instance.finishedReadingTO = setTimeout(function() {
            if (instance.settings.verboseMode) { console.log('Estimated reading time reached.'); }
          }, instance.timeLeftTotalSeconds*1000);

        }

        clearTimeout(instance.readingTimeShowTO);
        if (instance.currentScrollPos > instance.lastScrollTop) {
          if (instance.settings.showGaugeDelay > 0) {
            instance.readingTimeShowTO = setTimeout(function() { instance.showGauge(); }, instance.settings.showGaugeDelay);
          } else {
            instance.updateGauge();
            instance.showGauge();
          }
        }
        if (instance.currentScrollPos == instance.lastScrollTop) {
          instance.updateWithNextMeasurement = true;
        }

        instance.lastScrollTop = instance.currentScrollPos;
      });

      // 1.4 Measurement interval
      this.measureScrollSpeedInterval = setInterval(function() {

        if (instance.currentScrollPos >= instance.startScrollPos4measure) { // if is scrolling down or still

          var docViewTop = $(window).scrollTop();
          var docViewBottom = docViewTop + $(window).height();

          var elemTop = $(instance.element).offset().top;
          var elemBottom = elemTop + $(instance.element).height();

          var pixelsScrolled = instance.currentScrollPos - instance.startScrollPos4measure;
          if (pixelsScrolled == 0) instance.scrollStill++; else instance.scrollStill = 0;

          if (
            ((elemTop >= docViewTop) && (elemTop <= docViewBottom)) ||
            ((elemTop <= docViewTop) && (elemBottom >= docViewBottom)) ||
            ((elemBottom >= docViewTop) && (elemBottom <= docViewBottom))
          ) {

            instance.pixelsMeasured.push(pixelsScrolled);
            instance.timesMeasured++;
            var pixelsMeasuredForAverage = 0; // Temporal variable used only in the next couple of lines for sum all measured pixels in the array
            $.each(instance.pixelsMeasured, function() { pixelsMeasuredForAverage += this; });
            instance.averageScrollingSpeed = pixelsMeasuredForAverage/instance.pixelsMeasured.length;
            if (instance.averageScrollingSpeed < 1) { instance.averageScrollingSpeed = 1; } // so it's never 0, it must be considered SOME movement to measure speed and remaining time. Division by zero is very dangerous, could destroy the space–time continuum.
            if (instance.averageScrollingSpeed > instance.measureLimit) { instance.averageScrollingSpeed = instance.measureLimit; }

            if (instance.settings.verboseMode) {
              var logDetail = ''; //logDetail = '['+instance.pixelsMeasured+'] '+pixelsMeasuredForAverage+'/'+instance.pixelsMeasured.length+' ';
              var secondsTotal = Math.round(instance.pixelsLeftToRead/instance.averageScrollingSpeed);
              //console.log('Measure '+('  '+instance.timesMeasured).substr(('  '+instance.timesMeasured).length-3)+': '+('   '+pixelsScrolled).substr(('   '+pixelsScrolled).length-3, 3)+'px/s. Measures: '+instance.pixelsMeasured.length+'. Average: '+instance.averageScrollingSpeed.toFixed(2)+'px/s. '+logDetail+'Pixels left to read: '+instance.pixelsLeftToRead+' = '+Math.floor(secondsTotal/60)+'m '+(secondsTotal%60)+'s');
            }

            if (instance.pixelsMeasured.length >= instance.considerOnlyLast) {
              instance.pixelsMeasured.shift();
              instance.pixelsMeasured.shift(); instance.pixelsMeasured.unshift(instance.averageScrollingSpeed); // This can be disabled to increase accuracy. Change the last measurement taked into account for the last average measured so the change between speeds will be smoother.
            }
            if (instance.updateWithNextMeasurement) {
              instance.updateGauge();
              instance.updateWithNextMeasurement = false;
            }
          }
          instance.startScrollPos4measure = $(instance.element).scrollTop();
        }

      }, 1000); // if this is changed, all the references to speed should be changed cause is measured in px/s.

    },
    // 2.
    updateGauge: function() {
      this.settings.gaugeContainer.find('.readRemainingGauge').html(this.getRemainingTime({ timeFormat : this.settings.timeFormat }));
    },
    // 3.
    showGauge: function() {
      var instance = this; // required cause the "this" is lost on the "each" function

      // these are the conditions that need to be fulfilled to make the gauge appear
      var isBetweenWrappers = false;
      if (this.settings.gaugeWrapper != '') {
        this.settings.gaugeWrapper.each(function() {
          if (!isBetweenWrappers) {
            var visibleHeightOfscrollingElement = $(this)[0].scrollHeight
            var gaugeWrapperTop = Math.round($(this).offset().top);

            isBetweenWrappers = ((instance.gaugeInitialAbsoluteTop+instance.currentScrollPos >= gaugeWrapperTop+instance.settings.topOffset) &&                                     // if the gauge is (or will be if visible) with current scroll position, below the top of the gaugeWrapper
            (instance.gaugeInitialAbsoluteTop+instance.currentScrollPos+instance.gauge.height() < gaugeWrapperTop+visibleHeightOfscrollingElement-instance.settings.bottomOffset)); // and above the end of the gaugeWrapper
          };
        });
        if (!isBetweenWrappers) { instance.gauge.addClass('hidden'); }
      } else {
        isBetweenWrappers = true;
      }

      this.updateTime();
      if ((isBetweenWrappers) &&                                      // if is between de wrapper
         (this.gauge.hasClass("hidden")) &&                           // and is not always visible or is currently not visible
         (!this.endReached) &&                                        // and the end is not reached yet
         (this.timeLeftTotalSeconds < this.settings.maxTimeToShow) && 
         (this.timeLeftTotalSeconds > this.settings.minTimeToShow)
      ) {
        this.updateGauge();
        this.gauge.removeClass('hidden');
      }

    },
    // 4.
    updateTime: function() {
      this.timeLeftTotalSeconds = Math.round(this.pixelsLeftToRead/this.averageScrollingSpeed);
      this.timeLeftMinutes = Math.floor(this.timeLeftTotalSeconds/60);

      this.timeLeftSeconds = this.timeLeftTotalSeconds-(this.timeLeftMinutes*60);
      this.timeLeftMinsFloat = (this.timeLeftMinutes+(this.timeLeftSeconds*0.0166667)).toFixed(1)*1;

      if (this.timeLeftTotalSeconds <= 0) this.gauge.addClass('hidden');

      $(this.element).data('timeLeftMinutes', this.timeLeftMinutes); // .data is needed cause this info can be required by the getRemainingTime method directly on the object
      $(this.element).data('timeLeftSeconds', this.timeLeftSeconds);
    },
    // 5.
    getRemainingTime: function(options) {
      // if vars are undefined, the method is required directly from the scrolling object
      var leftMinutes = (typeof this.timeLeftMinutes === 'undefined') ? $(this).data('timeLeftMinutes') : this.timeLeftMinutes;
      var leftSeconds = (typeof this.timeLeftSeconds === 'undefined') ? $(this).data('timeLeftSeconds') : this.timeLeftSeconds;

      var displayText = options.timeFormat.replace(/%m/g, leftMinutes);
      displayText = (' '+displayText).replace(' 0m ', ''); // Remove 0m and show only seconds, this can be disabled if desired
      displayText = displayText.replace(' 0s', '');
      displayText = displayText.replace(/%s/g, leftSeconds);
      displayText = $.trim(displayText);

      return displayText;
    }
  });

  /* jQuery Plugin Boilerplate: A really lightweight plugin wrapper around the constructor, preventing against multiple instantiations */  
  $.fn[pluginName]=function(options){if(typeof arguments[0]==="string"){var methodName=arguments[0];var args=Array.prototype.slice.call(arguments,1);var returnVal;this.each(function(){if($.data(this,"plugin_"+pluginName)&&typeof $.data(this,"plugin_"+pluginName)[methodName]==="function")returnVal=$.data(this,"plugin_"+pluginName)[methodName].apply(this,args);else throw new Error("Method "+methodName+" does not exist on jQuery."+pluginName);});if(returnVal!==undefined)return returnVal;else return this}else if(typeof options==="object"||!options)return this.each(function(){if(!$.data(this,"plugin_"+pluginName))$.data(this,"plugin_"+pluginName,new Plugin(this,options))});return this};

})(jQuery, window, document);
