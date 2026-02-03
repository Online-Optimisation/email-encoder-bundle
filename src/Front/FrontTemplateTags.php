<?php

namespace OnlineOptimisation\EmailEncoderBundle\Front;

use OnlineOptimisation\EmailEncoderBundle\Traits\PluginHelper;

class FrontTemplateTags
{
    use PluginHelper;


    public function boot(): void
    {
        add_action( 'init', [ $this, 'add_custom_template_tags' ], 10 );
    }



    public function add_custom_template_tags(): void
    {
        $template_tags = $this->getTemplateTags();

        foreach ( $template_tags as $hook => $callback ) {

            //Make sure we only call our own custom template tags
            if ( $hook !== '' && is_callable( array( $this, $callback ) ) ) {
                apply_filters( $hook, array( $this, $callback ), 10 );
            }

        }
    }

    /**
     * Filter for the eeb_filter template tag
     *
     * This function is called dynamically by add_custom_template_tags
     * using the $this->getTemplateTags() callback.
     *
     * @param string $content - the default content
     * @return string - the filtered content
     */
    public function template_tag_eeb_filter( string $content ): string
    {
        $protect_using = (string) $this->getSetting( 'protect_using', true );
        return $this->validate()->filters->filter_content( $content, $protect_using );
    }

    /**
     * Filter for the eeb_filter template tag
     *
     * This function is called dynamically by add_custom_template_tags
     * using the $this->getTemplateTags() callback.
     *
     * @param string $email
     * @param string|array< string > $display
     * @param array< string, string > $atts
     */
    public function template_tag_eeb_mailto( $email, $display = '', $atts = [] ): string
    {
        if ( is_array( $display ) ) {
            // backwards compatibility (old params: $display, $attrs = array())
            $atts   = $display;
            $display = $email;
        } else {
            $atts['href'] = 'mailto:' . $email;
        }

        return $this->validate()->encoding->create_protected_mailto( $display, $atts );
    }
}
