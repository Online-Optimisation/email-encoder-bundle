<?php

namespace OnlineOptimisation\EmailEncoderBundle\Validate;

use OnlineOptimisation\EmailEncoderBundle\Traits\PluginHelper;

class Encoding
{
    use PluginHelper;

    private string $at_identifier;

    public function boot(): void
    {
        $this->at_identifier = $this->settings()->get_at_identifier();
    }


    /**
     * ######################
     * ###
     * #### ENCODINGS
     * ###
     * ######################
     */

    /**
     * @param string $content
     * @param bool $decode
     * @return string
     */
    public function temp_encode_at_symbol( string $content, bool $decode = false )
    {
        if ( $decode ) {
            return str_replace( $this->at_identifier, '@', $content );
        }

        return str_replace( '@', $this->at_identifier, $content );
    }

    /**
     * ASCII method
     *
     * @param string $value
     * @param string $protection_text
     * @return string
     */
    public function encode_ascii( $value, $protection_text )
    {
        $mail_link = $value;

        // first encode, so special chars can be supported
        $mail_link = $this->helper()->encode_uri_components( $mail_link );

        $mail_letters = '';

        for ( $i = 0; $i < strlen( $mail_link ); $i++ ) {
            $l = substr($mail_link, $i, 1);

            if (strpos($mail_letters, $l) === false) {
                $p = wp_rand(0, strlen($mail_letters));
                $mail_letters = substr($mail_letters, 0, $p) .
                $l . substr($mail_letters, $p, strlen($mail_letters));
            }
        }

        $mail_letters_enc = str_replace( "\\", "\\\\", $mail_letters );
        $mail_letters_enc = str_replace( "\"", "\\\"", $mail_letters_enc );

        $mail_indices = '';
        for ( $i = 0; $i < strlen( $mail_link ); $i++ ) {
            $index = strpos( $mail_letters, substr( $mail_link, $i, 1 ) );
            $index += 48;
            $mail_indices .= chr( $index );
        }

        $mail_indices = str_replace("\\", "\\\\", $mail_indices);
        $mail_indices = str_replace("\"", "\\\"", $mail_indices);

        $element_id = 'eeb-' . wp_rand( 0, 1000000 ) . '-' . wp_rand(0, 1000000);

        return '<span id="' . $element_id . '"></span>'
                . '<script type="text/javascript">'
                . '(function() {'
                . 'var ml="' . $mail_letters_enc . '",mi="' . $mail_indices . '",o="";'
                . 'for(var j=0,l=mi.length;j<l;j++) {'
                . 'o+=ml.charAt(mi.charCodeAt(j)-48);'
                . '}document.getElementById("' . $element_id . '").innerHTML = decodeURIComponent(o);' // decode at the end, this way special chars can be supported
                . '}());'
                . '</script><noscript>'
                . esc_html( $protection_text )
                . '</noscript>'
        ;
    }

    /**
     * Escape encoding method
     *
     * @param string $value
     * @param string $protection_text
     * @return string
     */
    public function encode_escape( $value, $protection_text )
    {
        $element_id = 'eeb-' . wp_rand( 0, 1000000 ) . '-' . wp_rand( 0, 1000000 );

        //Validate escape sequences
        $string = preg_replace('/\s+/S', " ", $value) ?? '';

        // break string into array of characters, we can't use string_split because its php5 only
        $split = preg_split( '||', $string );
        $out = '<span id="' . esc_attr( $element_id ) . '"></span>'
            . '<script type="text/javascript">'
            . 'document.getElementById("' . $element_id . '").innerHTML = decodeURIComponent("';

        if ( is_array( $split ) )
        foreach ( $split as $c ) {
            // preg split will return empty first and last characters, check for them and ignore
            if ( ! empty( $c ) || $c === '0' ) {
                $out .= '%' . dechex( ord( $c ) );
            }
        }

        $out .= '");'
             . '</script><noscript>'
             . esc_html( $protection_text )
             . '</noscript>';

        return $out;
    }

    /**
     * Encode email in input field
     * @param string $input
     * @param string $email
     * @param bool $strongEncoding
     * @return string
     */
    public function encode_input_field( $input, $email, $strongEncoding = false )
    {

        $show_encoded_check = (bool) $this->getSetting( 'show_encoded_check', true );

        if ( $strongEncoding === false ) {
            // encode email with entities (default wp method)
            $sub_return = str_replace( $email, antispambot( $email ), $input );

            if ( current_user_can( $this->getAdminCap( 'frontend-display-security-check' ) ) && $show_encoded_check ) {
                $sub_return .= $this->get_encoded_email_icon();
            }

            return $sub_return;
        }

        // add data-enc-email after "<input"
        $inputWithDataAttr = substr( $input, 0, 6 );
        $inputWithDataAttr .= ' data-enc-email="' . esc_attr( $this->get_encoded_email( $email ) ) . '"';
        $inputWithDataAttr .= substr( $input, 6 );

        // mark link as successfullly encoded (for admin users)
        if ( current_user_can( $this->getAdminCap( 'frontend-display-security-check' ) ) && $show_encoded_check ) {
            $inputWithDataAttr .= $this->get_encoded_email_icon();
        }

        // remove email from value attribute
        $encInput = str_replace( $email, '', $inputWithDataAttr );

        return $encInput;
    }

    /**
     * Get encoded email, used for data-attribute (translate by javascript)
     *
     * @param string $email
     * @return string
     */
    public function get_encoded_email( $email )
    {
        $encEmail = $email;

        // decode entities
        $encEmail = html_entity_decode( $encEmail );

        // rot13 encoding
        // phpcs:ignore Generic.PHP.ForbiddenFunctions.Found -- str_rot13 is intentional for email obfuscation
        $encEmail = str_rot13( $encEmail );

        // replace @
        $encEmail = str_replace( '@', '[at]', $encEmail );

        return $encEmail;
    }

    /**
     * Get the ebcoded email icon
     *
     * @param string $text
     * @return string
     */
    public function get_encoded_email_icon( $text = 'Email encoded successfully!' )
    {

        $html = '<i class="eeb-encoded dashicons-before dashicons-lock" title="' . esc_attr( $text ) . '"></i>';

        return apply_filters( 'eeb/validate/get_encoded_email_icon', $html, $text );
    }

    /**
     * Create a protected email
     *
     * @param string $display
     * @param array< string, string > $attrs
     * @param string $protection_method
     * @return string
     */
    public function create_protected_mailto( $display, $attrs = [], $protection_method = null )
    {
        $email     = '';
        $class_ori = ( empty( $attrs['class'] ) ) ? '' : $attrs['class'];
        $custom_class = (string) $this->getSetting( 'class_name', true );
        $show_encoded_check = $this->getSettingBool( 'show_encoded_check', true );

        if ( ! empty( $attrs['href'] ) && stripos( $attrs['href'], 'mailto:' ) === 0 ) {
            $email = substr( $attrs['href'], 7 );
        }

        // set user-defined class
        if ( $custom_class !== '' && strpos( $class_ori, $custom_class ) === false ) {
            $attrs['class'] = ( empty( $attrs['class'] ) ) ? $custom_class : $attrs['class'] . ' ' . $custom_class;
        }

        // check title for email address
        if ( ! empty( $attrs['title'] ) ) {
            $attrs['title'] = $this->filterPlainEmails( $attrs['title'], '{{email}}' ); // {{email}} will be replaced in javascript
        }

        // set ignore to data-attribute to prevent being processed by WPEL plugin
        $attrs['data-wpel-link'] = 'ignore';

        // create element code
        $link = '<a ';

        foreach ( $attrs as $key => $value ) {
            if ( strtolower( $key ) === 'href' ) {
                if ( $protection_method === 'without_javascript' ) {
                    $link .= $key . '="' . antispambot( $value ) . '" ';
                } else {
                    $encoded_email = $this->get_encoded_email( $email );

                    // set attrs
                    $link .= 'href="javascript:;" ';
                    $link .= 'data-enc-email="' . esc_attr( $encoded_email ) . '" ';
                }

            } else {
                $link .= esc_attr( $key ) . '="' . esc_attr( $value ) . '" ';
            }
        }

        // remove last space
        $link = substr( $link, 0, -1 );

        $link .= '>';

        // Only scramble the display when it IS the email (classic <a href="mailto:x">x</a> shape).
        // When the display holds richer content (e.g. a builder-wrapped <span>Email us at x</span>),
        // keep the markup intact — the final filterPlainEmails pass below entity-encodes any emails
        // still embedded in it so bots can't harvest them.
        $display_is_just_email = ( $email !== '' && trim( strip_tags( (string) $display ) ) === $email );

        if ( $display_is_just_email && trim( (string) $display ) !== $email ) {
            // The email is wrapped in markup, e.g. Elementor's icon list:
            // <span class="icon"><svg/></span><span class="text">x</span>. Scramble only the
            // email text and leave the wrapping elements as real DOM. Passing the whole display
            // through get_protected_display() re-injects it inside one extra <span>, so the icon
            // and text stop being direct (flex) children of the anchor and the email drops onto
            // its own row; the CSS method would strip the icon altogether.
            $self = $this;
            // (?![^<]*>) skips occurrences inside a tag, i.e. attribute values.
            $link .= (string) preg_replace_callback(
                '/' . preg_quote( $email, '/' ) . '(?![^<]*>)/',
                function ( $match ) use ( $self, $protection_method ) {
                    return $self->get_protected_display( $match[0], $protection_method );
                },
                (string) $display,
                1
            );
        } elseif ( $display_is_just_email ) {
            $link .= $this->get_protected_display( $display, $protection_method );
        } else {
            $link .= $display;
        }

        $link .= '</a>';

        // filter
        $link = apply_filters( 'eeb_mailto', $link, $display, $email, $attrs );

        // just in case there are still email addresses f.e. within title-tag
        $link = $this->filterPlainEmails( $link, null, 'char_encode' );

        // mark link as successfullly encoded (for admin users)
        if ( current_user_can( $this->getAdminCap( 'frontend-display-security-check' ) ) && $show_encoded_check ) {
            $link .= $this->get_encoded_email_icon();
        }


        return $link;
    }

    /**
     * Create a protected custom attribute
     *
     * @param string $display
     * @param array< string, string > $attrs Optional
     * @param string $protection_method
     * @return string
     */
    public function create_protected_href_att( $display, $attrs = [], $protection_method = null )
    {
        $email     = '';
        $class_ori = ( empty( $attrs['class'] ) ) ? '' : $attrs['class'];
        $custom_class = (string) $this->getSetting( 'class_name', true );
        $show_encoded_check = $this->getSettingBool( 'show_encoded_check', true );

        // set user-defined class
        if ( $custom_class !== '' && strpos( $class_ori, $custom_class ) === false ) {
            $attrs['class'] = ( empty( $attrs['class'] ) ) ? $custom_class : $attrs['class'] . ' ' . $custom_class;
        }

        // Entity-encode attributes that repeat the protected value. Elementor's Icon Box copies
        // the title (the phone number) into aria-label on its icon link.
        foreach ( [ 'title', 'aria-label' ] as $text_attr ) {
            if ( ! empty( $attrs[ $text_attr ] ) ) {
                $attrs[ $text_attr ] = antispambot( $attrs[ $text_attr ] );
            }
        }

        // set ignore to data-attribute to prevent being processed by WPEL plugin
        $attrs['data-wpel-link'] = 'ignore';

        // create element code
        $link = '<a ';

        foreach ( $attrs as $key => $value ) {
            if ( strtolower( $key ) === 'href' ) {
                $link .= $key . '="' . antispambot( $value ) . '" ';
            } else {
                $link .= esc_attr( $key ) . '="' . esc_attr( $value ) . '" ';
            }
        }

        // remove last space
        $link = substr( $link, 0, -1 );

        $link .= '>';

        // Plain text display (classic <a href="tel:x">x</a>) is scrambled whole. When the display
        // holds markup — a builder icon (<img>/<svg>), or Elementor's icon + text spans — the
        // elements must stay real DOM: scrambling the whole thing strips the icon (CSS method) or
        // re-injects it inside an extra <span> that breaks the builder's flex layout (JS method).
        // So only the text nodes are scrambled. Mirrors create_protected_mailto().
        $display_is_plain_text = ( trim( (string) $display ) === trim( wp_strip_all_tags( (string) $display ) ) );

        if ( $display_is_plain_text ) {
            $link .= $this->get_protected_display( $display, $protection_method );
        } else {
            $link .= $this->protect_display_text_nodes( (string) $display, $protection_method );
        }

        $link .= '</a>';

        // filter
        $link = apply_filters( 'eeb_custom_href', $link, $display, $email, $attrs );

        // mark link as successfullly encoded (for admin users)
        if ( current_user_can( $this->getAdminCap( 'frontend-display-security-check' ) ) && $show_encoded_check ) {
            $link .= $this->get_encoded_email_icon( 'Custom attribute encoded successfully!' );
        }


        return $link;
    }

    /**
     * Scramble the text nodes of an HTML fragment while leaving its elements in place.
     * Inline <svg>, <script> and <style> blocks are passed through untouched.
     *
     * @param string $display
     * @param string|null $protection_method
     * @return string
     */
    private function protect_display_text_nodes( string $display, $protection_method = null ): string
    {
        $parts = preg_split(
            '#(<svg\b.*?</svg\s*>|<script\b.*?</script\s*>|<style\b.*?</style\s*>|<[^>]+>)#is',
            $display,
            -1,
            PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
        );

        if ( ! is_array( $parts ) ) {
            return $display;
        }

        $out = '';

        foreach ( $parts as $part ) {
            if ( $part[0] === '<' || trim( $part ) === '' ) {
                $out .= $part;
            } else {
                $out .= $this->get_protected_display( $part, $protection_method );
            }
        }

        return $out;
    }

    /**
     * Create protected display combining these 3 methods:
     * - reversing string
     * - adding no-display spans with dummy values
     * - using the wp antispambot function
     *
     * @param string|array<string> $display
     * @param string $protection_method
     * @return string Protected display
     */
    public function get_protected_display( $display, $protection_method = null )
    {

        $convert_plain_to_image = (bool) $this->getSetting( 'convert_plain_to_image', true, 'filter_body' );
        $protection_text = (string) $this->getSetting( 'protection_text', true );
        $raw_display = $display;

        // get display out of array (result of preg callback)
        if ( is_array( $display ) ) {
            $display = $display[0];
        }

        // generate_email_image_url() requires a bare email address (it runs is_email()
        // on its input and returns false otherwise). Page builders like WPForms, Divi,
        // and Elementor commonly wrap the email in HTML (<span>x</span>) or surround
        // it with copy ("Contact us at x"), which previously produced <img src="">
        // — the broken image rendered invisibly on the frontend.
        $email_for_image = '';
        if ( $convert_plain_to_image ) {
            if ( is_email( (string) $display ) ) {
                $email_for_image = (string) $display;
            } else {
                $stripped = wp_strip_all_tags( (string) $display );
                $email_match = [];
                if ( preg_match( $this->settings()->get_email_regex(), $stripped, $email_match ) ) {
                    $email_for_image = $email_match[0];
                }
            }
        }

        // Image mode can only draw emails. A display with no email in it (a phone number from
        // a protected tel: link, any custom href text) falls through to the JS/CSS methods
        // instead of emitting a broken <img src="">.
        if ( $email_for_image !== '' ) {
            $display = '<img src="' . $this->generate_email_image_url( $email_for_image ) . '" />';
        } elseif ( $protection_method !== 'without_javascript' ) {
            $display = $this->dynamic_js_email_encoding( $display, $protection_text );
        } else {
            $display = $this->encode_email_css( $display );
        }

        return apply_filters( 'eeb/validate/get_protected_display', $display, $raw_display, $protection_method, $protection_text );
    }

    /**
     * Dynamic email encoding with certain javascript methods
     *
     * @param string $email
     * @param string $protection_text
     * @return string the encoded email
     */
    public function dynamic_js_email_encoding( $email, $protection_text = '' )
    {
        $return = $email;
        $rand = apply_filters( 'eeb/validate/random_encoding', wp_rand( 0, 2 ), $email, $protection_text );

        switch ( $rand ) {
            case 2:
                $return = $this->encode_escape( $return, $protection_text );
                break;
            case 1:
                $return = $this->encode_ascii( $return, $protection_text );
                break;
            default:
                $return = $this->encode_ascii( $return, $protection_text );
                break;
        }

        return $return;
    }

    /**
     * @param string $display
     * @return string
     */
    public function encode_email_css( $display )
    {
        $deactivate_rtl = (bool) $this->getSetting( 'deactivate_rtl', true, 'filter_body' );

        // $this->log( 'display: ' . $display );
        $stripped_display = wp_strip_all_tags( $display );
        $stripped_display = html_entity_decode( $stripped_display );

        $length = strlen( $stripped_display );
        $interval = (int) ceil( min( 5, $length / 2 ) );
        $offset = 0;
        $dummy_data = time();
        $protected = '';
        $protection_classes = 'eeb';

        if ( $deactivate_rtl ) {
            $rev = $stripped_display;
            $protection_classes .= ' eeb-nrtl';
        } else {
            // reverse string ( will be corrected with CSS )
            $rev = strrev( $stripped_display );
            $protection_classes .= ' eeb-rtl';
        }


        while ( $offset < $length ) {
            $protected .= '<span class="eeb-sd">' . antispambot( substr( $rev, $offset, $interval ) ) . '</span>';

            // Dummy content between real segments confuses scrapers. It's kept hidden from
            // humans via CSS, but we also inline display:none so it never leaks as visible
            // text if the plugin's stylesheet fails to load (page builders that defer/strip
            // CSS, caching layers, or other plugins dropping the eeb-css-frontend handle).
            $protected .= '<span class="eeb-nodis" style="display:none">' . $dummy_data . '</span>';
            $offset += $interval;
        }

        // Inline the bidi-override / word-break styles for the same reason the dummy spans
        // inline display:none — the email must still render correctly (forward, not reversed)
        // when the stylesheet isn't loaded.
        $wrapper_style = $deactivate_rtl
            ? 'word-break:break-all'
            : 'unicode-bidi:bidi-override;direction:rtl';

        $protected = '<span class="' . $protection_classes . '" style="' . $wrapper_style . '">' . $protected . '</span>';

        return $protected;
    }


    /**
     * @return string
     */
    public function email_to_image( string $email, string $image_string_color = 'default', string $image_background_color = 'default', int $alpha_string = 0, int $alpha_fill = 127, int $font_size = 4 )
    {

        $setting_image_string_color = (string) $this->getSetting( 'image_color', true, 'image_settings' );
        $setting_image_background_color = (string) $this->getSetting( 'image_background_color', true, 'image_settings' );
        $image_text_opacity = (int) $this->getSetting( 'image_text_opacity', true, 'image_settings' );
        $image_background_opacity = (int) $this->getSetting( 'image_background_opacity', true, 'image_settings' );
        $image_font_size = (int) $this->getSetting( 'image_font_size', true, 'image_settings' );
        $border_height = (int) $this->getSetting( 'image_underline', true, 'image_settings' );
        $border_padding = 0;
        $border_offset = 2;

        if ( $image_background_color === 'default' ) {
            $image_background_color = $setting_image_background_color;
        } else {
            $image_background_color = '0,0,0';
        }

        $colors = explode( ',', $image_background_color );
        $bg_red = max( 0, min( 255, (int) $colors[0] ) );
        $bg_green = max( 0, min( 255, (int) $colors[1] ) );
        $bg_blue = max( 0, min( 255, (int) $colors[2] ) );

        if ( $image_string_color === 'default' ) {
            $image_string_color = $setting_image_string_color;
        } else {
            $image_string_color = '0,0,0';
        }

        $colors = explode( ',', $image_string_color );
        $string_red = max( 0, min( 255, (int) $colors[0] ) );
        $string_green = max( 0, min( 255, (int) $colors[1] ) );
        $string_blue = max( 0, min( 255, (int) $colors[2] ) );

        if (
            ! empty( $image_text_opacity )
            && $image_text_opacity >= 0
            && $image_text_opacity <= 127
        ) {
            $alpha_string = intval( $image_text_opacity );
        }
        $alpha_string = max( 0, min( 127, $alpha_string ) );

        if (
            ! empty( $image_background_opacity )
            && $image_background_opacity >= 0
            && $image_background_opacity <= 127
        ) {
            $alpha_fill = intval( $image_background_opacity );
        }
        $alpha_fill = max( 0, min( 127, $alpha_fill ) );

        if ( ! empty( $image_font_size ) && $image_font_size >= 1 && $image_font_size <= 5 ) {
            $font_size = intval( $image_font_size );
        }

        $img_width = max( 1, imagefontwidth( $font_size ) * strlen( $email ) );
        $img_height = imagefontheight( $font_size );

        if ( ! empty( $border_height ) ) {
            $img_real_height = max( 1, $img_height + $border_offset + $border_height );
        } else {
            $img_real_height = max( 1, $img_height );
        }

        $img = imagecreatetruecolor( $img_width, $img_real_height );
        imagesavealpha( $img, true );
        imagefill( $img, 0, 0, max( 0, imagecolorallocatealpha($img, $bg_red, $bg_green, $bg_blue, $alpha_fill ) ) );
        imagestring(
            $img,
            $font_size,
            0,
            0,
            $email,
            max( 0, imagecolorallocatealpha( $img, $string_red, $string_green, $string_blue, $alpha_string ) )
        );


        if ( ! empty( $border_height ) ) {
            $border_fill = imagecolorallocatealpha ($img, $string_red, $string_green, $string_blue, $alpha_string );
            imagefilledrectangle(
                $img,
                0,
                $border_offset + $img_height + $border_height - 1,
                $border_padding + $img_width,
                $border_offset + $img_height,
                max( 0, $border_fill )
            );
        }

        ob_start();
        imagepng( $img );
        imagedestroy( $img );

        return (string) ob_get_clean();
    }


    /**
     * @param string $email
     * @param string $secret
     * @return string|bool
     */
    public function generate_email_signature( string $email, string $secret )
    {

        if ( ! $secret ) {
            return false;
        }

        $hash_signature = apply_filters( 'eeb/validate/email_signature', 'sha256', $email );

        return base64_encode( hash_hmac( $hash_signature, $email, $secret, true ) );
    }

    /**
     * @param string $email
     * @return string|bool
     */
    public function generate_email_image_url( ?string $email )
    {
        if ( ! function_exists( 'imagefontwidth' ) || empty( $email ) || ! is_email( $email ) ) {
            return false;
        }

        $secret = $this->settings()->get_email_image_secret();
        $signature = (string) $this->generate_email_signature( $email, $secret );
        $url = home_url();
        $url .= '?eeb_mail=' . urlencode( base64_encode( $email ) );
        $url .= '&eeb_hash=' . urlencode( $signature );

        $url = apply_filters( 'eeb/validate/generate_email_image_url', $url, $email );

        return $url;
    }

}
