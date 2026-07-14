/* Email Encoder */
/*global jQuery, window*/
jQuery(function ($) {

    'use strict';

    // encoding method
    function rot13(s) {
        // source: http://jsfromhell.com/string/rot13
        return s.replace(/[a-zA-Z]/g, function (c) {
            return String.fromCharCode((c <= 'Z' ? 90 : 122) >= (c = c.charCodeAt(0) + 13) ? c : c - 26);
        });
    }

    /**
     * EMAIL RELATED LOGIC
     */

    // fetch email from data attribute
    function fetchEmail(el) {
        var email = el.getAttribute('data-enc-email');

        if (!email) {
            return null;
        }

        // replace [at] sign
        email = email.replace(/\[at\]/g, '@');

        // encode
        email = rot13(email);

        return email;
    }

    // replace email in title attribute
    function parseTitle(el) {
        var title = el.getAttribute('title');
        var email = fetchEmail(el);

        if (title && email) {
            title = title.replace('{{email}}', email);
            el.setAttribute('title', title);
        }
    }

    // set input value attribute
    function setInputValue(el) {
        var email = fetchEmail(el);

        if (email) {
            el.setAttribute('value', email);
        }
    }

    // open mailto link (fallback for links that were never hydrated)
    function mailto(el) {
        var email = fetchEmail(el);

        if (email) {
            window.location.href = 'mailto:' + email;
        }
    }

    // Build the real "mailto:" href from data-enc-email, lazily — only once the
    // visitor engages with the link (mousedown / touch / focus), which always
    // happens before a click, middle-click or "open in new tab". Doing it lazily
    // rather than on page load keeps the plaintext address out of the rendered
    // DOM for agents that run JS but never interact (search-engine renderers,
    // headless harvesters), while still giving real navigations a genuine link.
    // It also replaces the inert "javascript:;" placeholder before any navigation,
    // so iOS Safari never tries to open a javascript: URL in a new tab (which
    // throws "cannot run script").
    function hydrateMailto(el) {
        var email = fetchEmail(el);

        if (email && el.getAttribute('href') !== 'mailto:' + email) {
            el.setAttribute('href', 'mailto:' + email);
        }
    }

    // revert
    function revert(el, rtl) {
        var email = fetchEmail(el);

        if (email) {
           rtl.text(email);
           rtl.removeClass('eeb-rtl');
        }
    }

    // prepare for copying email
    document.addEventListener('copy', function(e){
        $('a[data-enc-email]').each(function () {
            var rtl = $(this).find('.eeb-rtl');

            if (rtl.text()) {
                revert(this, rtl);
            }
        });
        console.log('copy');
    });

    // hydrate the mailto href the moment the visitor engages with the link,
    // before any click / middle-click / "open in new tab" navigation resolves
    $('body').on('mousedown touchstart focusin', 'a[data-enc-email]', function () {
        hydrateMailto(this);
    });

    // set mailto click
    $('body').on('click', 'a[data-enc-email]', function () {
        // Already hydrated to a real mailto (on mousedown/touch/focus): let the
        // browser handle it natively. Otherwise open it ourselves.
        if ((this.getAttribute('href') || '').indexOf('mailto:') === 0) {
            return;
        }

        mailto(this);
    });

    // parse title attribute
    $('a[data-enc-email]').each(function () {
        parseTitle(this);
    });

    // parse input fields
    $('input[data-enc-email]').each(function () {
        setInputValue(this);
    });

});
