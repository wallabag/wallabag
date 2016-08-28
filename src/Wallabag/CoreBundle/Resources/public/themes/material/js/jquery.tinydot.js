/*
 *    jQuery tinydot 0.2.0
 *
 *    Copyright (c) Alexander Danilov
 *    modos189.ru
 *
 *    Plugin website:
 *    tinydot.modos189.ru
 *
 *    Licensed under the MIT license.
 *    http://en.wikipedia.org/wiki/MIT_License
 */

(function( $, undef )
{
    if ( $.fn.tinydot )
    {
            return;
    }

    $.fn.tinydot = function( o ) {
        
        var $dot = this;
        $dot.child = getChildOrDie($dot);
        $dot.orgContent = $($dot.child).html();
        ellipsis( $dot );

        $dot.watch = function()
        {
            $(window).on('resize', function(){
                if ( watchInt )
                {
                    clearInterval( watchInt );
                }
                watchInt = setTimeout(
                    function()
                    {
                        reinitialize($dot);
                    }, 100
                );
            });
            
            return $dot;
        };
    
        var opts = $.extend( true, {}, $.fn.tinydot.defaults, o ),
            watchInt = null;

        if ( opts.watch )
        {
            $dot.watch();
        }
    }
    
    // public
    $.fn.tinydot.defaults = {
        'watch'               : false
    };
    
    function getChildOrDie( $elem )
    {
        var childrens = $elem.children();
        if (childrens.length == 0) {
            // create children
            var data = $($elem).html();
            $elem.html('');
            $elem.append('<span />');
            return $elem.children('span').html(data);
        } else {
            return childrens[0];
        }
    }
    
    function reinitialize( $elem )
    {
        $($elem.child).html($elem.orgContent);
        ellipsis( $elem );
    }
    
    function ellipsis( $elem ) {
        var divh=$($elem).height();
        while ($($elem.child).outerHeight()>divh) {
            $($elem.child).html(function (index, html) {
                return html.replace(/\W*\s(\S)*$/, '...');
            });
        }
    }
    
})( jQuery );

jQuery(document).ready(function($) {
    //We only invoke jQuery.tinydot on elements that have dot-ellipsis class
    $(".dot-ellipsis").each(function(){
        //Checking if update on window resize required
        var watch_window=$(this).hasClass("dot-resize-update");

        //Invoking jQuery.tinydot
        var x = new Object();
        if (watch_window)
                x.watch='window';
        $(this).tinydot(x);
    });
});
