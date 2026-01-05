<?php

namespace OnlineOptimisation\EmailEncoderBundle\Admin;

use OnlineOptimisation\EmailEncoderBundle\Traits\PluginHelper;

class AdminHelp
{
    use PluginHelper;


    // public function boot(): void {
    // }


    public function add_help_tabs(): void
    {
        $screen = get_current_screen();

        $defaults = [
            'content'   => '',
            'callback'  => [ $this, 'load_help_tabs' ],
        ];

        $tabs = [
            [ 'id' => 'general',       'title' => 'General'       ],
            [ 'id' => 'shortcodes',    'title' => 'Shortcodes'    ],
            [ 'id' => 'template-tags', 'title' => 'Template Tags' ],
        ];

        foreach ( $tabs as $tab ) {
            $screen->add_help_tab( wp_parse_args( [
                'id'        => $tab['id'],
                'title'     => __( $tab['title'], 'email-encoder-bundle' ),
            ], $defaults ) );
        }
    }


    public function load_help_tabs( \WP_Screen $screen, array $args ): void
    {
        if ( empty( $args['id'] ) ) {
            return;
        }

        $allowed_attr_html = $this->getSafeHtmlAttr();

        include EEB_PLUGIN_DIR . 'templates/help-tabs/' . $args['id'] . '.php';
    }


}
