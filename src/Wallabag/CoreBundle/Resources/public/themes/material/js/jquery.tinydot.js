/*
 *	jQuery tinydot 0.1
 *
 *	Copyright (c) Alexander Danilov
 *	www.modos189.ru
 *
 *	Plugin repository:
 *	https://gitlab.com/modos189/tinydot
 *
 *	Licensed under the MIT license.
 *	http://en.wikipedia.org/wiki/MIT_License
 */

(function( $, undef )
{
    if ( $.fn.tinydot )
    {
            return;
    }

    $.fn.tinydot = function( o ) {
        var p=$(this).children('a');
        var divh=$(this).height();
        while ($(p).outerHeight()>divh) {
            $(p).text(function (index, text) {
                return text.replace(/\W*\s(\S)*$/, '...');
            });
        }
    }
})( jQuery );

jQuery(document).ready(function($) {
    //We only invoke jQuery.tinydot on elements that have dot-ellipsis class
    $(".dot-ellipsis").each(function(){
            var x = new Object();
            $(this).tinydot(x);
    });
            
});
