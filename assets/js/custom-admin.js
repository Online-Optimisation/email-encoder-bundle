/* Email Encoder Admin */
jQuery(function ($) {

    'use strict';

    // add form-table class to Encoder Form tables
    $('.eeb-form table').addClass('form-table');

    // Copy support info to clipboard
    $('#eeb-copy-support-info').on('click', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var text = $btn.data('support-text');
        var original = $btn.html();

        var onCopied = function () {
            $btn.text('Copied!');
            setTimeout(function () { $btn.html(original); }, 2000);
        };

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(onCopied).catch(function () {
                prompt('Copy this text:', text);
            });
        } else {
            prompt('Copy this text:', text);
        }
    });

});
