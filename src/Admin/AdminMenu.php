<?php

namespace OnlineOptimisation\EmailEncoderBundle\Admin;

if ( ! defined( 'ABSPATH' ) ) exit;

use OnlineOptimisation\EmailEncoderBundle\Traits\PluginHelper;

class AdminMenu
{
    use PluginHelper;

    private AdminHelp $help;


    public function boot(): void
    {
        $this->help = new AdminHelp();

        add_action( 'admin_menu', [ $this, 'register_menu' ], 150 );
    }


    public function register_menu(): void
    {
        if ( (string) $this->getSetting( 'own_admin_menu', true ) !== '1' ) {
            $pagehook = add_submenu_page(
                'options-general.php',
                $this->getPageTitle(),
                $this->getPageTitle(),
                $this->getAdminCap( 'admin-add-submenu-page-item' ),
                $this->getPageName(),
                [ $this, 'render_admin_menu_page' ]
            );
        } else {
            $pagehook = add_menu_page(
                $this->getPageTitle(),
                $this->getPageTitle(),
                $this->getAdminCap( 'admin-add-menu-page-item' ),
                $this->getPageName(),
                [ $this, 'render_admin_menu_page' ],
                $this->getMenuIconUri()
            );
        }

        add_action( 'load-' . $pagehook, [ $this->help, 'add_help_tabs' ] );
    }


    private function getMenuIconUri(): string
    {
        $svg = (string) file_get_contents( EEB_PLUGIN_DIR . 'assets/img/icon-email-encoder-mono.svg' );
        return 'data:image/svg+xml;base64,' . base64_encode( $svg );
    }



    public function render_admin_menu_page(): void
    {
        if ( ! current_user_can( $this->getAdminCap('admin-menu-page') ) ) {
            wp_die( esc_html__( 'Insufficient permissions.', 'email-encoder-bundle' ) );
        }

        ( new Admin() )->maybe_consume_redirect_notice();

        $display_notices = Admin::$display_notices;

        include EEB_PLUGIN_DIR . 'templates/eeb-page-display.php';
    }
}
