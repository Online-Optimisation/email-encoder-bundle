<?php

namespace OnlineOptimisation\EmailEncoderBundle\Validate;

use DOMDocument;
use OnlineOptimisation\EmailEncoderBundle\Traits\PluginHelper;

class Filters {

    use PluginHelper;

    public function boot(): void {
    }


	/**
	 * ######################
	 * ###
	 * #### FILTERS
	 * ###
	 * ######################
	 */

     /**
      * The main page filter function
      *
      * @param string $content - the content that needs to be filtered
      * @param bool $convertPlainEmails - wether plain emails should be preserved or not
      * @return string - The filtered content
      */
    public function filter_page( $content, $protect_using ) {

        //Added in 2.0.6
        $content = apply_filters( 'eeb/validate/filter_page_content', $content, $protect_using );

        $content = $this->filter_soft_dom_attributes( $content, 'char_encode' );

        $htmlSplit = preg_split( '/(<body(([^>]*)>))/is', $content, -1, PREG_SPLIT_DELIM_CAPTURE );

        if ( count( $htmlSplit ) < 4 ) {
            return $content;
        }

        switch( $protect_using ) {
            case 'with_javascript':
            case 'without_javascript':
            case 'char_encode':
                $head_encoding_method = 'char_encode';
                break;
            default:
                $head_encoding_method = 'default';
                break;
        }

        //Filter head area
        $filtered_head = $this->filter_plain_emails( $htmlSplit[0], null, $head_encoding_method );

        //Filter body
        //Soft attributes always need to be protected using only the char encode method since otherwise the logic breaks
        $filtered_body = $this->filter_soft_attributes( $htmlSplit[4], 'char_encode' );
        $filtered_body = $this->filter_content( $filtered_body, $protect_using );

        $filtered_content = $filtered_head . $htmlSplit[1] . $filtered_body;

        //Revalidate filtered emails that should not bbe encoded
        $filtered_content = $this->tempEncodeAtSymbol( $filtered_content, true );

        return $filtered_content;
    }

    /**
     * Filter content
     *
     * @param string  $content
     * @param integer $protect_using
     * @return string
     */
    public function filter_content( $content, $protect_using ) {
        $filtered = $content;
        $self = $this;
        $encode_mailtos = (bool) $this->getSetting( 'encode_mailtos', true, 'filter_body' );
        $convert_plain_to_image = (bool) $this->getSetting( 'convert_plain_to_image', true, 'filter_body' );

        //Added in 2.0.6
        $filtered = apply_filters( 'eeb/validate/filter_content_content', $filtered, $protect_using );

        //Soft attributes always need to be protected using only the char encode method since otherwise the logic breaks
        $filtered = $this->filter_soft_attributes( $filtered, 'char_encode' );

        switch( $protect_using ) {
            case 'char_encode':
                $filtered = $this->filter_plain_emails( $filtered, null, 'char_encode' );
                break;
            case 'strong_method':
                $filtered = $this->filter_plain_emails( $filtered );
                break;
            case 'without_javascript':
                $filtered = $this->filter_input_fields( $filtered, $protect_using );
                $filtered = $this->filter_mailto_links( $filtered, 'without_javascript' );
                $filtered = $this->filter_custom_links( $filtered, 'without_javascript' );

                if ( $convert_plain_to_image ) {
                    $replace_by = 'convert_image';
                } else {
                    $replace_by = 'use_css';
                }

                if ( $encode_mailtos ) {
                    if ( ! ( function_exists( 'et_fb_enabled' ) && et_fb_enabled() ) ) {
                        $filtered = $this->filter_plain_emails( $filtered, function ( $match ) use ( $self ) {
                            return $self->createProtectedMailto( $match[0], array( 'href' => 'mailto:' . $match[0] ), 'without_javascript' );
                        }, $replace_by);
                    } else {
                        $filtered = $this->filter_plain_emails( $filtered, null, $replace_by );
                    }
                } else {
                    $filtered = $this->filter_plain_emails( $filtered, null, $replace_by );
                }

                break;
            case 'with_javascript':
                $filtered = $this->filter_input_fields( $filtered, $protect_using );
                $filtered = $this->filter_mailto_links( $filtered );
                $filtered = $this->filter_custom_links( $filtered );

                if ( $convert_plain_to_image ) {
                    $replace_by = 'convert_image';
                } else {
                    $replace_by = 'use_javascript';
                }

                if ( $encode_mailtos ) {
                    if ( ! ( function_exists( 'et_fb_enabled' ) && et_fb_enabled() ) ) {
                        $filtered = $this->filter_plain_emails( $filtered, function ( $match ) use ( $self ) {
                            return $self->createProtectedMailto( $match[0], array( 'href' => 'mailto:' . $match[0] ), 'with_javascript' );
                        }, $replace_by);
                    } else {
                        $filtered = $this->filter_plain_emails( $filtered, null, $replace_by );
                    }
                } else {
                    $filtered = $this->filter_plain_emails( $filtered, null, $replace_by );
                }

                break;
        }

        //Revalidate filtered emails that should not be encoded
        $filtered = $this->tempEncodeAtSymbol( $filtered, true );

        return $filtered;
    }

    /**
     * Emails will be replaced by '*protected email*'
     * @param string           $content
     * @param string|callable  $replace_by  Optional
     * @param string           $protection_method  Optional
     * @param mixed            $show_encoded_check  Optional
     * @return string
     */
    public function filter_plain_emails( $content, $replace_by = null, $protection_method = 'default', $show_encoded_check = 'default' ) {

        if ( $show_encoded_check === 'default' ) {
            $show_encoded_check = (bool) $this->getSetting( 'show_encoded_check', true );
        }

        if ( $replace_by === null ) {
            $replace_by = __( $this->getSetting( 'protection_text', true ), 'email-encoder-bundle' );
        }

        $self = $this;

        return preg_replace_callback( $this->settings()->get_email_regex(), function ( $matches ) use ( $replace_by, $protection_method, $show_encoded_check, $self ) {
            // workaround to skip responsive image names containing @
            $extention = strtolower( $matches[4] );
            $excludedList = array(
                '.jpg',
                '.jpeg',
                '.png',
                '.gif',
                '.svg',
                '.webp',
                '.bmp',
                '.tiff',
                '.avif',
            );

            //Added in 2.1.1
            $excludedList = apply_filters( 'eeb/validate/excluded_image_urls', $excludedList );

            if ( in_array( $extention, $excludedList ) ) {
                return $matches[0];
            }

            if ( is_callable( $replace_by ) ) {
                return call_user_func( $replace_by, $matches, $protection_method );
            }

            if ( $protection_method === 'char_encode' ) {
                $protected_return = antispambot( $matches[0] );
            } elseif ( $protection_method === 'convert_image' ) {

                $image_link = $self->generateEmailImageUrl( $matches[0] );
                if ( ! empty( $image_link ) ) {
                    $protected_return = '<img src="' . $image_link . '" />';
                } else {
                    $protected_return = antispambot( $matches[0] );
                }

            } elseif ( $protection_method === 'use_javascript' ) {
                $protection_text = __( $this->getSetting( 'protection_text', true ), 'email-encoder-bundle' );
                $protected_return = $this->dynamicJsEmailEncoding( $matches[0], $protection_text );
            } elseif ( $protection_method === 'use_css' ) {
                $protection_text = __( $this->getSetting( 'protection_text', true ), 'email-encoder-bundle' );
                $protected_return = $this->encode_email_css( $matches[0], $protection_text );
            } elseif ( $protection_method === 'no_encoding' ) {
                $protected_return = $matches[0];
            } else {
                $protected_return = $replace_by;
            }

            // mark link as successfully encoded (for admin users)
            if ( current_user_can( $this->getAdminCap( 'frontend-display-security-check' ) ) && $show_encoded_check ) {
                $protected_return .= $this->getEncodedEmailIcon();
            }

            return $protected_return;

        }, $content );
    }

    /**
     * Filter passed input fields
     *
     * @param string $content
     * @return string
     */
    public function filter_input_fields( $content, $encoding_method = 'default' ) {
        $self = $this;
        $strong_encoding = (bool) $this->getSetting( 'input_strong_protection', true, 'filter_body' );

        $callback_encode_input_fields = function ( $match ) use ( $self, $encoding_method, $strong_encoding ) {
            $input = $match[0];
            $email = $match[2];

            //Only allow strong encoding if javascript is supported
            if ( $encoding_method === 'without_javascript' ) {
                $strong_encoding = false;
            }

            return $self->encode_input_field( $input, $email, $strong_encoding );
        };

        $regexpInputField = '/<input([^>]*)value=["\'][\s+]*' . $this->settings()->get_email_regex( true ) . '[\s+]*["\']([^>]*)>/is';

        return preg_replace_callback( $regexpInputField, $callback_encode_input_fields, $content );
    }

    /**
     * @param string $content
     * @return string
     */
    public function filter_mailto_links( $content, $protection_method = null ) {
        $self = $this;

        $callbackEncodeMailtoLinks = function ( $match ) use ( $self, $protection_method ) {
            $attrs = $this->helper()->parse_html_attributes( $match[1] );
            return $self->createProtectedMailto( $match[4], $attrs, $protection_method );
        };

        $regexpMailtoLink = '/<a[\s+]*(([^>]*)href=["\']mailto\:([^>]*)["\' ])>(.*?)<\/a[\s+]*>/is';

        return preg_replace_callback( $regexpMailtoLink, $callbackEncodeMailtoLinks, $content );
    }

    /**
     * @param string $content
     * @return string
     */
    public function filter_custom_links( $content, $protection_method = null ) {
        $self = $this;
        $custom_href_attr = (string) $this->getSetting( 'custom_href_attr', true );

        if ( ! empty( $custom_href_attr ) ) {
            $custom_attr_list = explode( ',', $custom_href_attr );
            foreach( $custom_attr_list as $s_attr ) {
                $attr_name = trim( $s_attr );

                $callbackEncodeCustomLinks = function ( $match ) use ( $self, $protection_method ) {
                    $attrs = shortcode_parse_atts( $match[1] );
                    return $self->createProtectedHrefAtt( $match[4], $attrs, $protection_method );
                };

                $regexpMailtoLink = '/<a[\s+]*(([^>]*)href=["\']' . addslashes( $attr_name ) . '\:([^>]*)["\' ])>(.*?)<\/a[\s+]*>/is';

                $content = preg_replace_callback( $regexpMailtoLink, $callbackEncodeCustomLinks, $content );
            }
        }

        return $content;
    }

    /**
     * Emails will be replaced by '*protected email*'
     *
     * @param string $content
     * @return string
     */
    public function filter_rss( $content, $protection_type ) {

        if ( $protection_type === 'strong_method' ) {
            $filtered = $this->filter_plain_emails( $content );
        } else {
            $filtered = $this->filter_plain_emails( $content, null, 'char_encode' );
        }

        return $filtered;
    }

    /**
     * Filter plain emails using soft attributes
     *
     * @param string $content - the content that should be soft filtered
     * @param string $protection_method - The method (E.g. char_encode)
     * @return string
     */
    public function filter_soft_attributes( $content, $protection_method ) {
        $soft_attributes = $this->settings()->get_soft_attribute_regex();

        foreach( $soft_attributes as $ident => $regex ) {

            $attributes = array();
            preg_match_all( $regex, $content, $attributes );

            if ( is_array( $attributes ) && isset( $attributes[0] ) ) {
                foreach( $attributes[0] as $single ) {

                    if ( empty( $single ) ) {
                        continue;
                    }

                    $content = str_replace( $single, $this->filter_plain_emails( $single, null, $protection_method, false ), $content );
                }
            }

        }

        return $content;
    }

    /**
     * Filter plain emails using soft dom attributes
     *
     * @param string $content - the content that should be soft filtered
     * @param string $protection_method - The method (E.g. char_encode)
     * @return string
     */
    public function filter_soft_dom_attributes( $content, $protection_method ) {

        $no_script_tags = (bool) $this->getSetting( 'no_script_tags', true, 'filter_body' );
        $no_attribute_validation = (bool) $this->getSetting( 'no_attribute_validation', true, 'filter_body' );

        if ( ! empty( $content ) && is_string( $content ) ) {

            if ( class_exists( 'DOMDocument' ) ) {
                $dom = new DOMDocument();
                @$dom->loadHTML($content);

                //Filter html attributes
                if ( ! $no_attribute_validation ) {
                    $allNodes = $dom->getElementsByTagName('*');
                    foreach( $allNodes as $snote ) {
                        if ( $snote->hasAttributes() ) {
                            foreach( $snote->attributes as $attr ) {
                                if ( $attr->nodeName == 'href' || $attr->nodeName == 'src' ) {
                                    continue;
                                }

                                if ( strpos( $attr->nodeValue, '@' ) !== FALSE ) {
                                    $single_tags = array();
                                    preg_match_all( '/' . $attr->nodeName . '=["\']([^"]*)["\']/i', $content, $single_tags );

                                    if ( is_array( $single_tags ) && isset( $single_tags[0] ) ) {
                                        foreach( $single_tags[0] as $single ) {

                                            if ( empty( $single ) ) {
                                                continue;
                                            }

                                            $content = str_replace( $single, $this->filter_plain_emails( $single, null, $protection_method, false ), $content );
                                        }
                                    }

                                }
                            }
                        }
                    }
                }

                //Keep for now
                //Soft-encode scripts
                // $script = $dom->getElementsByTagName('script');
                // if ( ! empty( $script ) ) {
                //     $scripts_encoded = true;

                //     if ( ! $no_script_tags ) {
                //         foreach( $script as $item ) {
                //             $content = str_replace( $item->nodeValue, $this->filter_plain_emails( $item->nodeValue, null, $protection_method, false ), $content );
                //         }
                //     } else {
                //         foreach( $script as $item ) {
                //             $content = str_replace( $item->nodeValue, $this->temp_encode_at_symbol( $item->nodeValue ), $content );
                //         }
                //     }
                // }

            }

            //Validate script tags for better encoding
            $pattern = '/<script\b[^>]*>(.*?)<\/script>/is';

            preg_match_all($pattern, $content, $matches);
            if (
                isset( $matches[1] )
                && ! empty( $matches[1] )
                ) {
                if ( ! $no_script_tags ) {
                    foreach( $matches[1] as $key => $item ) {

                        //Don't do anything if something doesn't add up
                        if ( ! isset( $matches[0][ $key ] ) ) {
                            continue;
                        }

                        $org_script = $matches[0][ $key ];

                        //Only encode emails when a CDATA is given to not cause any break within the scripts
                        if ( strpos( $item, '<![CDATA' ) !== false ) {
                            $validated_script = str_replace( $item, $this->filter_plain_emails( $item, null, $protection_method, false ), $org_script );
                        } else {
                            $validated_script = str_replace( $item, $this->tempEncodeAtSymbol( $item ), $org_script );
                        }

                        $content = str_replace( $org_script, $validated_script, $content );
                    }
                } else {
                    foreach( $matches[1] as $key => $item ) {

                        //Don't do anything if something doesn't add up
                        if ( ! isset( $matches[0][ $key ] ) ) {
                            continue;
                        }

                        $org_script = $matches[0][ $key ];
                        $validated_script = str_replace( $item, $this->tempEncodeAtSymbol( $item  ), $org_script );

                        $content = str_replace( $org_script, $validated_script, $content );
                    }
                }
            }

        }


        return $content;
    }
}
