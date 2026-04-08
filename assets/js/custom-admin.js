/* Email Encoder Admin */
jQuery(function ($) {

    'use strict';

    // add form-table class to Encoder Form tables
    $('.eeb-form table').addClass('form-table');

    // Copy support info to clipboard
    var $btn = $('#eeb-copy-support-info');
    if ($btn.length) {
        var original = $btn.html();
        var copying = false;

        $btn.on('click', function (e) {
            e.preventDefault();
            if (copying) return;
            copying = true;

            var text = $btn.data('support-text');

            var onCopied = function () {
                $btn.text('Copied!');
                setTimeout(function () {
                    $btn.html(original);
                    copying = false;
                }, 2000);
            };

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(onCopied).catch(function () {
                    prompt('Copy this text:', text);
                    copying = false;
                });
            } else {
                prompt('Copy this text:', text);
                copying = false;
            }
        });
    }

});
