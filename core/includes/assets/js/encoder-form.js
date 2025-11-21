jQuery( document ).ready( function( $ ) {

    'use strict';

    var $wrap = $('.eeb-form');
    var $email = $wrap.find('#eeb-email');
    var $display = $wrap.find('#eeb-display');
    var prevEmail;

    // get encoded email ( ajax call )
    var getEncoded = function () {
        // stop when email field is empty
        if (!$email.val()) {
            return;
        }

        // empty the output field
        $wrap.find('#eeb-encoded-output').val('');

        // get the encoded email link
        $.post( eeb_ef.ajaxurl, {
            action: 'eeb_get_email_form_output',
            eebsec : eeb_ef.security,
            eebEmail: $email.val(),
            eebDisplay: $display.val() || $email.val(),
            eebMethod: $wrap.find('#eeb-encode-method').val()
        }, function ( response ) {
            $wrap.find('#eeb-encoded-output').val( response );
            $wrap.find('.eeb-output').fadeIn();
        });

    };

    // hide output
    $wrap.find('.eeb-output').hide();

    // auto-set display field
    $email.keyup(function () {
        var email = $email.val();
        var display = $display.val();

        if (!display || display === prevEmail) {
            $display.val(email);
        }

        prevEmail = email;
    });

    // get encoded link on these events
    $wrap.find('#eeb-email, #eeb-display')
        .keyup(function () {
            if ($display.val().length > 0) {
                // show example how it will appear on the page
                $wrap.find('.eeb-example')
                    .html('<a href="mailto:' + $email.val() + '">' + $display.val() + '</a>')
                    .parents('tr').fadeIn();
            } else {
                $wrap.find('.eeb-example').parents('tr').fadeOut();
            }

            // clear code field
            $wrap.find('.eeb-output').fadeOut();
            $wrap.find('#eeb-encoded-output').val('');
        })
        .keyup();

    $wrap.find('#eeb-encode-method').bind('change keyup', function () {
        getEncoded();
    });

    $wrap.find('#eeb-ajax-encode').click(function () {
        getEncoded();
    });

  });