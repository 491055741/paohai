/**
 * @preserve Copyright 2011 Syd Lawrence ( www.sydlawrence.com ).
 * Version: 0.1
 *
 * Licensed under MIT and GPLv2.
 *
 * Adapted from Nicholas Gallagher's CSS drop shadows without images demo
 * http://nicolasgallagher.com/css-drop-shadows-without-images/demo/
 *
 * Usage: $('.box').shadow(options);
 *
 */

(function( $ ){

	$.fn.shadow = function(options) {  

		// options could just be the type
		if (typeof options !== "object")
			options = {type:options};

		// set up the options using the defaults
		options = $.extend({}, $.fn.shadow.defaults, options);

		var els = this;

		// add the necessary css classes
		els.addClass('jquery-shadow');
		els.addClass('jquery-shadow-'+options.type);	

		// for good measure :)
		$.fn.shadow.borderRadius(this,options.radius);		

		return this;
	};

	// border-radius goodness..
	$.fn.shadow.borderRadius = function(els,radius) {
		els.css('-moz-border-radius',radius)
			.css('border-radius',radius);
	}

	// these are the defaults :)
	$.fn.shadow.defaults = {
		type:'standard', // AVAILABLE standard, lifted, perspective, raised, sides
		radius:4,
		sides:'', // used when type = "sides", AVAILABLE vt-1, vt-2, hz-1, hz-2
		rotate:'-3deg'
	};

})( jQuery );
