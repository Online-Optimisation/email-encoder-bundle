<?php

namespace OnlineOptimisation\EmailEncoderBundle\Admin;

use OnlineOptimisation\EmailEncoderBundle\Traits\PluginHelper;

class AdminMenu
{
    use PluginHelper;

    private AdminHelp $help;
    private AdminMetaBox $metabox;


    public function boot(): void
    {
        $this->help = new AdminHelp();
        $this->metabox = new AdminMetaBox();

        add_action( 'admin_menu', [ $this, 'register_menu' ], 150 );
    }


    public function register_menu(): void
    {
        if ( (string) $this->getSetting( 'own_admin_menu', true ) !== '1' ) {
            $pagehook = add_submenu_page(
                'options-general.php',
                __( $this->getPageTitle(), 'email-encoder-bundle' ),
                __( $this->getPageTitle(), 'email-encoder-bundle' ),
                $this->getAdminCap( 'admin-add-submenu-page-item' ),
                $this->getPageName(),
                [ $this, 'render_admin_menu_page' ]
            );
        } else {
            $pagehook = add_menu_page(
                __( $this->getPageTitle(), 'email-encoder-bundle' ),
                __( $this->getPageTitle(), 'email-encoder-bundle' ),
                $this->getAdminCap( 'admin-add-menu-page-item' ),
                $this->getPageName(),
                [ $this, 'render_admin_menu_page' ],
                plugins_url( 'assets/img/icon-email-encoder-bundle.png', EEB_PLUGIN_FILE )
            );
        }

        add_action( 'load-' . $pagehook, [ $this->help, 'add_help_tabs' ] );
        add_action( 'load-' . $pagehook, [ $this->metabox, 'add_meta_box' ] );
    }



    public function render_admin_menu_page(): void
    {
        if ( ! current_user_can( $this->getAdminCap('admin-menu-page') ) ) {
            wp_die( 'Insufficinet permissions.' );
            // wp_die( __( $this->settings()->get_default_string( 'insufficient-permissions' ), 'email-encoder-bundle' ) );
        }


        $display_notices = Admin::$display_notices;

        include EEB_PLUGIN_DIR . 'templates/eeb-page-display.php';
    }
}
