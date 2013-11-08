/*
 * jQuery PanZoom Plugin
 * Pan and zoom an image within a parent div.
 *
 * version: 0.9.0
 * @requires jQuery v1.4.2 or later (earlier probably work, but untested so far)
 *
 * Copyright (c) 2011 Ben Lumley
 * Examples and documentation at: https://github.com/benlumley/jQuery-PanZoom
 *
 * Dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
*/

(function( $ ){

  $.fn.panZoom = function(method) {

    if ( methods[method] ) {
      return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
    } else if ( typeof method === 'object' || ! method ) {
      return methods.init.apply( this, arguments );
    } else {
      $.error( 'Method ' +  method + ' does not exist' );
    }

  };

	$.fn.panZoom.defaults = {
      zoomIn   					: 	false,
      zoomOut 					: 	false,
		  panUp							:		false,
		  panDown						:		false,
		  panLeft						:		false,
		  panRight					:		false,
			fit								: 	false,
		  out_x1						:		false,
		  out_y1						:		false,
		  out_x2						:		false,
		  out_y2						:		false,
			min_width					:   20,
			min_height 				:   20,
			zoom_step					:   3,
			pan_step  				:   3,
			debug							: 	false,
			directedit				:   false,
      aspect    				:   true,
      factor    				:   1,
		  animate   				:   true,
			animate_duration	: 	200,
		 	animate_easing		: 	'linear',
			double_click	 		: 	true,
			mousewheel				: 	true,
			mousewheel_delta	: 	1,
			draggable					:   true,
			clickandhold			: 	true
  };

	var settings = {}

  var methods = {
		'init': function (options) {
			$.extend(settings, $.fn.panZoom.defaults, options);
  		setupCSS.apply(this);
			setupData.apply(this);
			setupBindings.apply(this);
			methods.readPosition.apply(this);
		},

		'destroy': function () {
			$(window).unbind('.panZoom');
			this.removeData('panZoom');
		},

		'loadImage': function () {
			var data = this.data('panZoom');
      loadTargetDimensions.apply(this);
			methods.updatePosition.apply(this);
			if (data.last_image != null && data.last_image != this.attr('src')) {
        methods.fit.apply(this);
			}
			data.last_image = this.attr('src');
			data.loaded = true;
		},

	  'readPosition': function () {
				var data = this.data('panZoom');
		 		if (settings.out_x1) { data.position.x1 = settings.out_x1.val()*settings.factor }
		 		if (settings.out_y1) { data.position.y1 = settings.out_y1.val()*settings.factor }
		 		if (settings.out_x2) { data.position.x2 = settings.out_x2.val()*settings.factor }
		 		if (settings.out_y2) { data.position.y2 = settings.out_y2.val()*settings.factor }
				methods.updatePosition.apply(this);
		 },

		'updatePosition': function() {
			validatePosition.apply(this);
			writePosition.apply(this);
			applyPosition.apply(this);
		},

	  'fit': function () {
			var data = this.data('panZoom');
			data.position.x1 = 0;
			data.position.y1 = 0;
			data.position.x2 = data.viewport_dimensions.x;
			data.position.y2 = data.viewport_dimensions.y;
			methods.updatePosition.apply(this);
		},

		'zoomIn': function (steps) {
			var data = this.data('panZoom');
			if (typeof(steps) == 'undefined') {
				var steps = getStepDimensions.apply(this);
			}
            console.debug(data.position);
			console.debug(data.viewport_dimensions);
			data.position.x1 = data.position.x1*1 - steps.zoom.x;
			data.position.x2 = data.position.x2*1 + steps.zoom.x;
			data.position.y1 = data.position.y1*1 - steps.zoom.y;
			data.position.y2 = data.position.y2*1 + steps.zoom.y;
			methods.updatePosition.apply(this);
		 },

		'zoomOut': function (steps) {
			var data = this.data('panZoom');
			if (typeof(steps) == 'undefined') {
				var steps = getStepDimensions.apply(this);
			}
			data.position.x1 = data.position.x1*1 + steps.zoom.x;
			data.position.x2 = data.position.x2*1 - steps.zoom.x;
			data.position.y1 = data.position.y1*1 + steps.zoom.y;
			data.position.y2 = data.position.y2*1 - steps.zoom.y;
			methods.updatePosition.apply(this);
		 },

		'panUp': function () {
			var data = this.data('panZoom');
			var steps = getStepDimensions.apply(this);
			data.position.y1 -= steps.pan.y;
			data.position.y2 -= steps.pan.y;
			methods.updatePosition.apply(this);
		},

		'panDown': function () {
			var data = this.data('panZoom');
			var steps = getStepDimensions.apply(this);
			data.position.y1 = data.position.y1*1 + steps.pan.y;
			data.position.y2 = data.position.y2*1 + steps.pan.y;
			methods.updatePosition.apply(this);
		},

		'panLeft': function () {
			var data = this.data('panZoom');
			var steps = getStepDimensions.apply(this);
			data.position.x1 -= steps.pan.x;
			data.position.x2 -= steps.pan.x;
			methods.updatePosition.apply(this);
		},

		'panRight': function () {
			var data = this.data('panZoom');
			var steps = getStepDimensions.apply(this);
			data.position.x1 = data.position.x1*1 + steps.pan.x;
			data.position.x2 = data.position.x2*1 + steps.pan.x;
			methods.updatePosition.apply(this);
		},

		'mouseWheel': function (delta) {
			// first calculate how much to zoom in/out
			var steps = getStepDimensions.apply(this);
			steps.zoom.x = steps.zoom.x * (Math.abs(delta) / settings.mousewheel_delta);
			steps.zoom.y = steps.zoom.y * (Math.abs(delta) / settings.mousewheel_delta);

			// then do it
			if (delta > 0) {
				methods.zoomIn.apply(this, [steps]);
			} else if (delta < 0) {
				methods.zoomOut.apply(this, [steps]);
			}
		},

		'dragComplete': function() {
			var data = this.data('panZoom');
			data.position.x1 = this.position().left;
			data.position.y1 = this.position().top;
			data.position.x2 = this.position().left*1 + this.width();
			data.position.y2 = this.position().top*1 + this.height();
			methods.updatePosition.apply(this);
		},

		'mouseDown': function (action) {
			methods[action].apply(this);

			if (settings.clickandhold) {
				var data = this.data('panZoom');
				methods.mouseUp.apply(this);
				data.mousedown_interval = window.setInterval(function (that, action) {
					that.panZoom(action);
				}, settings.animate_duration, this, action);
			}
		},

		'mouseUp': function() {
			var data = this.data('panZoom');
			window.clearInterval(data.mousedown_interval);
		}

  }

	function setupBindings() {

		eventData = { target: this }

		// bind up controls
		if (settings.zoomIn) {
			settings.zoomIn.bind('mousedown.panZoom', eventData, function(event) {
				event.preventDefault(); event.data.target.panZoom('mouseDown', 'zoomIn');
			}).bind('mouseleave.panZoom mouseup.panZoom', eventData, function(event) {
				event.preventDefault(); event.data.target.panZoom('mouseUp');
			});
		}

		if (settings.zoomOut) {
			settings.zoomOut.bind('mousedown.panZoom', eventData, function(event) {
				event.preventDefault(); event.data.target.panZoom('mouseDown', 'zoomOut');
			}).bind('mouseleave.panZoom mouseup.panZoom', eventData, function(event) {
				event.preventDefault(); event.data.target.panZoom('mouseUp');
			});
		}

		if (settings.panUp) {
			settings.panUp.bind('mousedown.panZoom', eventData, function(event) {
				event.preventDefault(); event.data.target.panZoom('mouseDown', 'panUp');
			}).bind('mouseleave.panZoom mouseup.panZoom', eventData, function(event) {
				event.preventDefault(); event.data.target.panZoom('mouseUp');
			});
		}

		if (settings.panDown) {
			settings.panDown.bind('mousedown.panZoom', eventData, function(event) {
				event.preventDefault(); event.data.target.panZoom('mouseDown', 'panDown');
			}).bind('mouseleave.panZoom mouseup.panZoom', eventData, function(event) {
				event.preventDefault(); event.data.target.panZoom('mouseUp');
			});
		}

		if (settings.panLeft) {
			settings.panLeft.bind('mousedown.panZoom', eventData, function(event) {
				event.preventDefault(); event.data.target.panZoom('mouseDown', 'panLeft');
			}).bind('mouseleave.panZoom mouseup.panZoom', eventData, function(event) {
				event.preventDefault(); event.data.target.panZoom('mouseUp');
			});
		}

		if (settings.panRight) {
			settings.panRight.bind('mousedown.panZoom', eventData, function(event) {
				event.preventDefault(); event.data.target.panZoom('mouseDown', 'panRight');
			}).bind('mouseleave.panZoom mouseup.panZoom', eventData, function(event) {
				event.preventDefault(); event.data.target.panZoom('mouseUp');
			});
		}

		if (settings.fit) { settings.fit.bind('click.panZoom', eventData, function(event) { event.preventDefault(); event.data.target.panZoom('fit'); } ); }

		// double click
		if (settings.double_click) {
			this.bind('dblclick.panZoom', eventData, function(event, delta) { event.data.target.panZoom('zoomIn') } );
		}

		// mousewheel
		if (settings.mousewheel && typeof(this.mousewheel) == 'function') {
			this.parent().mousewheel(function(event, delta) { event.preventDefault(); $(this).find('img').panZoom('mouseWheel', delta) } );
		} else if (settings.mousewheel) {
			alert('Mousewheel requires mousewheel from jQuery tools - please include jQuery tools or disable mousewheel to remove this warning.')
		}

		// direct form input
		if (settings.directedit) {
			$(settings.out_x1).add(settings.out_y1).add(settings.out_x2).add(settings.out_y2).bind('change.panZoom blur.panZoom', eventData, function(event) { event.data.target.panZoom('readPosition') } );
		}

		if (settings.draggable && typeof(this.draggable) == 'function') {
			this.draggable({
				stop: function () { $(this).panZoom('dragComplete');	}
			});
		}	else if (settings.draggable) {
				alert('Draggable requires jQuery UI - please include jQuery UI or disable draggable to remove this warning.')
		}

		// image load
		$(this).bind('load.panZoom', eventData, function (event) { event.data.target.panZoom('loadImage') })

	}

	function setupData() {
		this.data('panZoom', {
			target_element: this,
			target_dimensions: { x: null, y: null },
			viewport_element: this.parent(),
			viewport_dimensions: { x: this.parent().width(), y: this.parent().height() },
			position: { x1: null, y1: null, x2: null, y2: null },
			last_image: null,
			loaded: false,
			mousewheel_delta: 0,
			mousedown_interval: false
		});
		if (settings.debug) {
			console.log(this.data('panZoom'));
		}
	}

	function setupCSS() {
		if (this.parent().css('position') == 'static') {
			this.parent().css('position', 'relative');
		}
		this.css({
			'position': 'absolute',
			'top': 0,
			'left': 0
		});
		if (settings.draggable) {
			this.css({
				'cursor': 'move'
			});
		}
	}

	function validatePosition() {
		var data = this.data('panZoom');
		// if dimensions are too small...
		if ( data.position.x2 - data.position.x1 < settings.min_width/settings.factor || data.position.y2 - data.position.y1 < settings.min_height/settings.factor ) {
			// and second co-ords are zero (IE: no dims set), fit image
			if (data.position.x2 == 0 || data.position.y2 == 0) {
				methods.fit.apply(this);
			}
			// otherwise, backout a bit
			else {
				if (data.position.x2 - data.position.x1 < settings.min_width/settings.factor) {
					data.position.x2 = data.position.x1*1+settings.min_width/settings.factor;
				}
				if (data.position.y2 - data.position.y1 < settings.min_height/settings.factor) {
	  			data.position.y2 = data.position.y1*1+settings.min_height/settings.factor;
				}
			}
		}

		if (settings.aspect) {
			target = data.target_dimensions.ratio;
			current = getCurrentAspectRatio.apply(this)
			if (current > target) {
				new_width = getHeight.apply(this) * target;
				diff = getWidth.apply(this) - new_width;
				data.position.x1 = data.position.x1*1 + (diff/2);
				data.position.x2 = data.position.x2*1 - (diff/2);
			} else if (current < target) {
				new_height = getWidth.apply(this) / target;
				diff = getHeight.apply(this) - new_height;
				data.position.y1 = data.position.y1*1 + (diff/2);
				data.position.y2 = data.position.y2*1 - (diff/2);
			}
		}


	}

  function applyPosition() {
		var data = this.data('panZoom');

    width = getWidth.apply(this);
    height = getHeight.apply(this);
    left_offset = getLeftOffset.apply(this);
    top_offset = getTopOffset.apply(this);

		properties = {
			'top': Math.round(top_offset),
			'left': Math.round(left_offset),
			'width': Math.round(width),
			'height': Math.round(height)
		}

		if (data.loaded && settings.animate) {
			applyAnimate.apply(this, [ properties ]);
		} else {
			applyCSS.apply(this, [ properties ]);
		}

		if (settings.debug) {
			console.log('--');
			console.log('width:' + width);
			console.log('height:' + height);
			console.log('left:' + left_offset);
			console.log('top:' + top_offset);
		}
	}

	function applyCSS() {
		this.css(	properties );
	}

	function applyAnimate() {
		this.stop().animate(	properties , settings.animate_duration, settings.animate_easing);
	}

  function getWidth() {
		var data = this.data('panZoom');
    width = (data.position.x2 - data.position.x1);
    return width;
  }

  function getLeftOffset() {
		var data = this.data('panZoom');
    return data.position.x1;
  }

  function getHeight() {
		var data = this.data('panZoom');
		height = (data.position.y2 - data.position.y1);
    return height;
  }

  function getTopOffset() {
		var data = this.data('panZoom');
		top_offset = data.position.y1;
    return top_offset;
  }

	function getCurrentAspectRatio() {
		return (getWidth.apply(this) / getHeight.apply(this));
	}

	function writePosition() {
		var data = this.data('panZoom');
 		if (settings.out_x1) { settings.out_x1.val(Math.round(data.position.x1 / settings.factor)) }
 		if (settings.out_y1) { settings.out_y1.val(Math.round(data.position.y1 / settings.factor)) }
 		if (settings.out_x2) { settings.out_x2.val(Math.round(data.position.x2 / settings.factor)) }
 		if (settings.out_y2) { settings.out_y2.val(Math.round(data.position.y2 / settings.factor)) }
	}

	function getStepDimensions() {
		var data = this.data('panZoom');
		ret = {
			zoom: {
				x: (settings.zoom_step/100 * data.viewport_dimensions.x),
				y: (settings.zoom_step/100 * data.viewport_dimensions.y)
			},
			pan: {
				x: (settings.pan_step/100 * data.viewport_dimensions.x),
				y: (settings.pan_step/100 * data.viewport_dimensions.y)
			}
		}
		return ret;
	}

	function loadTargetDimensions() {
		var data = this.data('panZoom');
		var img = document.createElement('img');
    img.src = this.attr('src');
    img.id = "jqpz-temp";
    $('body').append(img);
    data.target_dimensions.x = $('#jqpz-temp').width();
		data.target_dimensions.y = $('#jqpz-temp').height();
    $('#jqpz-temp').remove();
    data.target_dimensions.ratio = data.target_dimensions.x / data.target_dimensions.y;
	}

})( jQuery );