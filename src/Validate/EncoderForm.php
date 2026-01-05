<?php

namespace OnlineOptimisation\EmailEncoderBundle\Validate;

use OnlineOptimisation\EmailEncoderBundle\Traits\PluginHelper;

class EncoderForm
{
    use PluginHelper;

    public function boot(): void
    {
    }


    /**
     * ######################
     * ###
     * #### ENCODER FORM
     * ###
     * ######################
     */

    /**
     * Get the encoder form (to use as a demo, like on the options page)
     * @return string
     */
    public function get_encoder_form()
    {
        $powered_by_setting = (bool) $this->getSetting( 'powered_by', true, 'encoder_form' );

        //shorten circle
        if (
            ! $this->helper()->is_page( $this->getPageName() )
            && ! (bool) $this->getSetting( 'encoder_form_frontend', true, 'encoder_form' )
        ) {
            return apply_filters('eeb_form_content_inactive', '' );
        }

        $powered_by = '';
        if ($powered_by_setting) {
            $powered_by .= '<p class="powered-by">' . __('Powered by free', 'email-encoder-bundle') . ' <a rel="external" href="https://wordpress.org/plugins/email-encoder-bundle/">Email Encoder</a></p>';
        }

        $smethods = array(
            'rot13' => __( 'Rot13 (Javascript)', 'email-encoder-bundle' ),
            'escape' => __( 'Escape (Javascript)', 'email-encoder-bundle' ),
            'encode' => __( 'Encode (HTML)', 'email-encoder-bundle' ),
        );
        $method_options = '';
        $selected = false;
        foreach ( $smethods as $method_name => $name ) {
            $method_options .= '<option value="' . $method_name . '"' . ( ($selected === false ) ? ' selected="selected"' : '') . '>' . $name . '</option>';
            $selected = true;
        }

        $labels = array(
            'email' => __( 'Email Address:', 'email-encoder-bundle' ),
            'display' => __( 'Display Text:', 'email-encoder-bundle' ),
            'mailto' => __( 'Mailto Link:', 'email-encoder-bundle' ),
            'method' => __( 'Encoding Method:', 'email-encoder-bundle' ),
            'create_link' => __( 'Create Protected Mail Link &gt;&gt;', 'email-encoder-bundle' ),
            'output' => __( 'Protected Mail Link (code):', 'email-encoder-bundle' ),
            'powered_by' => $powered_by,
        );

        extract( $labels );

        $form = <<<FORM
<div class="eeb-form">
    <form>
        <fieldset>
            <div class="input">
                <table>
                <tbody>
                    <tr>
                        <th><label for="eeb-email">{$email}</label></th>
                        <td><input type="text" class="regular-text" id="eeb-email" name="eeb-email" /></td>
                    </tr>
                    <tr>
                        <th><label for="eeb-display">{$display}</label></th>
                        <td><input type="text" class="regular-text" id="eeb-display" name="eeb-display" /></td>
                    </tr>
                    <tr>
                        <th>{$mailto}</th>
                        <td><span class="eeb-example"></span></td>
                    </tr>
                    <tr>
                        <th><label for="eeb-encode-method">{$method}</label></th>
                        <td><select id="eeb-encode-method" name="eeb-encode-method" class="postform">
                                {$method_options}
                            </select>
                            <input type="button" id="eeb-ajax-encode" name="eeb-ajax-encode" value="{$create_link}" />
                        </td>
                    </tr>
                </tbody>
                </table>
            </div>
            <div class="eeb-output">
                <table>
                <tbody>
                    <tr>
                        <th><label for="eeb-encoded-output">{$output}</label></th>
                        <td><textarea class="large-text node" id="eeb-encoded-output" name="eeb-encoded-output" cols="50" rows="4"></textarea></td>
                    </tr>
                </tbody>
                </table>
            </div>
            {$powered_by}
        </fieldset>
    </form>
</div>
FORM;

         // apply filters
        $form = apply_filters('eeb_form_content', $form, $labels, $powered_by_setting );

        return $form;
    }


    public function is_post_excluded( $post_id = null )
    {

        $return = false;
        $skip_posts = (string) $this->getSetting( 'skip_posts', true );
        if ( ! empty( $skip_posts ) ) {

            if ( empty( $post_id ) ) {
                global $post;
                if ( ! empty( $post ) ) {
                    $post_id = $post->ID;
                }
            } else {
                $post_id = intval( $post_id );
            }

            $exclude_pages = explode( ',', $skip_posts );

            if ( is_array( $exclude_pages ) ) {
                $exclude_pages_validated = array();

                foreach ( $exclude_pages as $spost_id ) {
                    $spost_id = trim($spost_id);
                    if ( is_numeric( $spost_id ) ) {
                        $exclude_pages_validated[] = intval( $spost_id );
                    }
                }

                if ( in_array( $post_id, $exclude_pages_validated ) ) {
                    $return = true;
                }
            }

        }

        return apply_filters( 'eeb/validate/is_post_excluded', $return, $post_id, $skip_posts );
    }

    /**
     * Filter if to exclude specific URL parameters from filtering
     *
     * @since 2.2.0
     * @param array $parameters
     * @return boolean
     */
    public function is_query_parameter_excluded( $parameters = null )
    {

        if ( $parameters === null ) {
            $parameters = $_GET;
        }

        $return = false;
        $skip_query_parameters = (string) $this->getSetting( 'skip_query_parameters', true );
        if ( ! empty( $skip_query_parameters ) && ! empty( $parameters ) ) {

            $excluded_parameters = explode( ',', $skip_query_parameters );

            if ( is_array( $excluded_parameters ) ) {

                foreach ( $excluded_parameters as $param ) {
                    $param = trim($param);

                    if ( isset( $parameters[ $param ] ) ) {
                        $return = true;
                        break;
                    }
                }

            }

        }

        return apply_filters( 'eeb/validate/is_query_parameter_excluded', $return, $parameters );
    }
}
