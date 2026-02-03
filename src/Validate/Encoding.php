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
                $p = rand(0, strlen($mail_letters));
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

        $element_id = 'eeb-' . mt_rand( 0, 1000000 ) . '-' . mt_rand(0, 1000000);

        return '<span id="' . $element_id . '"></span>'
                . '<script type="text/javascript">'
                . '(function() {'
                . 'var ml="' . $mail_letters_enc . '",mi="' . $mail_indices . '",o="";'
                . 'for(var j=0,l=mi.length;j<l;j++) {'
                . 'o+=ml.charAt(mi.charCodeAt(j)-48);'
                . '}document.getElementById("' . $element_id . '").innerHTML = decodeURIComponent(o);' // decode at the end, this way special chars can be supported
                . '}());'
                . '</script><noscript>'
                . $protection_text
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
        $element_id = 'eeb-' . mt_rand( 0, 1000000 ) . '-' . mt_rand( 0, 1000000 );
        $string = '\'' . $value . '\'';

        //Validate escape sequences
        $string = preg_replace('/\s+/S', " ", $string) ?? '';

        // break string into array of characters, we can't use string_split because its php5 only
        $split = preg_split( '||', $string );
        $out = '<span id="' . $element_id . '"></span>'
            . '<script type="text/javascript">' . 'document.getElementById("' . $element_id . '").innerHTML = ev' . 'al(decodeURIComponent("';

        if ( is_array( $split ) )
        foreach ( $split as $c ) {
            // preg split will return empty first and last characters, check for them and ignore
            if ( ! empty( $c ) || $c === '0' ) {
                $out .= '%' . dechex( ord( $c ) );
            }
        }

        $out .= '"))' . '</script><noscript>'
             . $protection_text
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
        $inputWithDataAttr .= ' data-enc-email="' . $this->get_encoded_email( $email ) . '"';
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

        $html = '<i class="eeb-encoded dashicons-before dashicons-lock" title="' . __( $text, 'email-encoder-bundle' ) . '"></i>';

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
        $show_encoded_check = (string) $this->getSetting( 'show_encoded_check', true );

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
                    // get email from href
                    $email = substr($value, 7);

                    $encoded_email = $this->get_encoded_email( $email );

                    // set attrs
                    $link .= 'href="javascript:;" ';
                    $link .= 'data-enc-email="' . $encoded_email . '" ';
                }

            } else {
                $link .= $key . '="' . $value . '" ';
            }
        }

        // remove last space
        $link = substr( $link, 0, -1 );

        $link .= '>';

        $link .= ( preg_match( $this->settings()->get_email_regex(), $display) > 0 ) ? $this->get_protected_display( $display, $protection_method ) : $display;

        $link .= '</a>';

        // filter
        $link = apply_filters( 'eeb_mailto', $link, $display, $email, $attrs );

        // just in case there are still email addresses f.e. within title-tag
        $link = $this->filterPlainEmails( $link, null, 'char_encode' );

        // mark link as successfullly encoded (for admin users)
        if ( current_user_can( $this->getAdminCap( 'frontend-display-security-check' ) ) && $show_encoded_check !== '' ) {
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
        $show_encoded_check = (string) $this->getSetting( 'show_encoded_check', true );

        // set user-defined class
        if ( $custom_class !== '' && strpos( $class_ori, $custom_class ) === false ) {
            $attrs['class'] = ( empty( $attrs['class'] ) ) ? $custom_class : $attrs['class'] . ' ' . $custom_class;
        }

        // check title for email address
        if ( ! empty( $attrs['title'] ) ) {
            $attrs['title'] = antispambot( $attrs['title'] );
        }

        // set ignore to data-attribute to prevent being processed by WPEL plugin
        $attrs['data-wpel-link'] = 'ignore';

        // create element code
        $link = '<a ';

        foreach ( $attrs as $key => $value ) {
            if ( strtolower( $key ) === 'href' ) {
                $link .= $key . '="' . antispambot( $value ) . '" ';
            } else {
                $link .= $key . '="' . $value . '" ';
            }
        }

        // remove last space
        $link = substr( $link, 0, -1 );

        $link .= '>';

        $link .= $this->get_protected_display( $display, $protection_method );

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
        $protection_text = __( $this->getSetting( 'protection_text', true ), 'email-encoder-bundle' );
        $raw_display = $display;

        // get display out of array (result of preg callback)
        if ( is_array( $display ) ) {
            $display = $display[0];
        }

        if ( $convert_plain_to_image ) {
            $display = '<img src="' . $this->generate_email_image_url( $display ) . '" />';
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
        $rand = apply_filters( 'eeb/validate/random_encoding', rand( 0, 2 ), $email, $protection_text );

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
        $stripped_display = strip_tags( $display );
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

            // setup dummy content
            $protected .= '<span class="eeb-nodis">' . $dummy_data . '</span>';
            $offset += $interval;
        }

        $protected = '<span class="' . $protection_classes . '">' . $protected . '</span>';

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
