<?php

namespace OnlineOptimisation\EmailEncoderBundle\Admin;

use OnlineOptimisation\EmailEncoderBundle\Traits\PluginHelper;

class PluginActionLinks
{
    use PluginHelper;


    public function boot(): void
    {
        add_action( 'plugin_action_links_' . EEB_PLUGIN_BASE, [ $this, 'handle' ] );
    }


    public function handle( array $links ): array
    {
        $settings_link = sprintf( '<a href="%s">%s</a>', admin_url( 'options-general.php?page=' . $this->getPageName() ), __( 'Settings', 'email-encoder-bundle' ) );

        array_unshift( $links, $settings_link );

        $links['visit_us'] = sprintf(
            '<a href="%s" target="_blank" style="font-weight:700;color:#f1592a;">%s</a>',
            'https://wpemailencoder.com/?utm_source=email-encoder-bundle&utm_medium=plugin-overview-website-button&utm_campaign=WP%20Mailto%20Links',
            __('Visit us', 'email-encoder-bundle')
        );

        return $links;
    }

}
