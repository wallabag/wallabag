var Gritter = function () {

    $('#add-sticky').click(function(){

        var unique_id = $.gritter.add({
            // (string | mandatory) the heading of the notification
            title: 'This is a Sticky Notice!',
            // (string | mandatory) the text inside the notification
            text: 'Hover me to enable the Close Button. This note also contains a link example. Thank you so much to try Dashgum. Developed by <a href="#" style="color:#FFD777">Alvarez.is</a>.',
            // (string | optional) the image to display on the left
            image: 'assets/img/ui-sam.jpg',
            // (bool | optional) if you want it to fade out on its own or just sit there
            sticky: true,
            // (int | optional) the time you want it to be alive for before fading out
            time: '',
            // (string | optional) the class name you want to apply to that specific message
            class_name: 'my-sticky-class'
        });

        // You can have it return a unique id, this can be used to manually remove it later using
        /*
         setTimeout(function(){

         $.gritter.remove(unique_id, {
         fade: true,
         speed: 'slow'
         });

         }, 6000)
         */

        return false;

    });

    $('#add-regular').click(function(){

        $.gritter.add({
            // (string | mandatory) the heading of the notification
            title: 'This is a Regular Notice!',
            // (string | mandatory) the text inside the notification
            text: 'This will fade out after a certain amount of time. This note also contains a link example. Thank you so much to try Dashgum. Developed by <a href="#" style="color:#FFD777">Alvarez.is</a>.',
            // (string | optional) the image to display on the left
            image: 'assets/img/ui-sam.jpg',
            // (bool | optional) if you want it to fade out on its own or just sit there
            sticky: false,
            // (int | optional) the time you want it to be alive for before fading out
            time: ''
        });

        return false;

    });

    $('#add-max').click(function(){

        $.gritter.add({
            // (string | mandatory) the heading of the notification
            title: 'This is a notice with a max of 3 on screen at one time!',
            // (string | mandatory) the text inside the notification
            text: 'This will fade out after a certain amount of time. This note also contains a link example. Thank you so much to try Dashgum. Developed by <a href="#" style="color:#FFD777">Alvarez.is</a>.',
            // (string | optional) the image to display on the left
            image: 'assets/img/ui-sam.jpg',
            // (bool | optional) if you want it to fade out on its own or just sit there
            sticky: false,
            // (function) before the gritter notice is opened
            before_open: function(){
                if($('.gritter-item-wrapper').length == 3)
                {
                    // Returning false prevents a new gritter from opening
                    return false;
                }
            }
        });

        return false;

    });

    $('#add-without-image').click(function(){

        $.gritter.add({
            // (string | mandatory) the heading of the notification
            title: 'This is a Notice Without an Image!',
            // (string | mandatory) the text inside the notification
            text: 'This will fade out after a certain amount of time. This note also contains a link example. Thank you so much to try Dashgum. Developed by <a href="#" style="color:#FFD777">Alvarez.is</a>.'
        });

        return false;
    });

    $('#add-gritter-light').click(function(){

        $.gritter.add({
            // (string | mandatory) the heading of the notification
            title: 'This is a Light Notification',
            // (string | mandatory) the text inside the notification
            text: 'Just add a "gritter-light" class_name to your $.gritter.add or globally to $.gritter.options.class_name',
            class_name: 'gritter-light'
        });

        return false;
    });

    $("#remove-all").click(function(){

        $.gritter.removeAll();
        return false;

    });



}();