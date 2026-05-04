<?php

namespace OnlineOptimisation\EmailEncoderBundle\Admin;

use OnlineOptimisation\EmailEncoderBundle\Traits\PluginHelper;
use WP_Screen;

class AdminHelp
{
    use PluginHelper;


    // public function boot(): void {
    // }


    public function add_help_tabs(): void
    {
        // Help content is now rendered inline as the 'Help' panel inside our
        // custom settings page. WP's top-right Help dropdown is removed to
        // avoid duplication and to keep all plugin help in one place.
        $screen = get_current_screen();
        if ( $screen === null ) {
            return;
        }

        // Remove the WP-default tabs that may have been registered by other
        // sources (e.g. core's "Overview" tab on settings screens) so the
        // Help dropdown disappears from the corner of the page.
        foreach ( $screen->get_help_tabs() as $tab ) {
            $screen->remove_help_tab( $tab['id'] );
        }
    }


    /**
     * @param WP_Screen $screen
     * @param array< string, string > $args
     * @return void
     */
    public function load_help_tabs( WP_Screen $screen, array $args ): void
    {
        if ( empty( $args['id'] ) ) {
            return;
        }

        $allowed_attr_html = $this->getSafeHtmlAttr();

        include \EEB_PLUGIN_DIR . 'templates/help-tabs/' . $args['id'] . '.php';
    }


}
