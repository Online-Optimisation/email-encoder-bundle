<?php

namespace OnlineOptimisation\EmailEncoderBundle\Admin;

use OnlineOptimisation\EmailEncoderBundle\Traits\PluginHelper;

class Admin
{
    use PluginHelper;

	public static array $display_notices = [];


    public function boot(): void {

        ( new AdminEnqueue() )->boot();
        ( new AdminMenu() )->boot(); // AdminMetaBox & AdminHelp are added here
        ( new PluginActionLinks() )->boot();

        add_action( 'init', [ $this, 'register_hooks' ] );
    }

	# ADMIN METHODS ============================================================

	public function register_hooks() {

        add_action( 'admin_init', [ $this, 'save_settings_admin' ] );
	}





	public function save_settings_admin() {

        $this->log( __METHOD__ );
		if ( !isset( $_POST[ $this->getPageName() . '_nonce' ] ) ) {
            return;
        };

        if ( ! wp_verify_nonce( $_POST[ $this->getPageName() . '_nonce' ], $this->getPageName() ) ) {
            wp_die( __( 'You don\'t have permission to update these settings.', 'email-encoder-bundle' ) );
        }

        if ( ! current_user_can( $this->getAdminCap( 'admin-update-settings' ) ) ) {
            wp_die( __( 'You don\'t have permission to update these settings.', 'email-encoder-bundle' ) );
        }

        $raw = wp_unslash( $_POST );

        if ( isset( $raw[ $this->getSettingsKey() ] ) && is_array( $raw[ $this->getSettingsKey() ] ) ) {

            //Strip duplicate slashes before saving
            foreach( $raw[ $this->getSettingsKey() ] as $k => $v ) {
                if ( is_string( $v ) ) {
                    $raw[ $this->getSettingsKey() ][ $k ] = $this->sanitise( $v, $k );
                    $this->log( $raw[ $this->getSettingsKey() ][ $k ] );
                }
            }

            $this->log( $this->getSettingsKey() );
            $check = update_option( $this->getSettingsKey(), $raw[ $this->getSettingsKey() ] );

            if ( $check ) {
                $this->reloadSettings();
                $update_notice = $this->helper()->create_admin_notice( 'Settings successfully saved.', 'success', true );
                self::$display_notices[] = $update_notice;
            }
            else {
                $update_notice = $this->helper()->create_admin_notice( 'No changes were made to your settings with your last save.', 'info', true );
                self::$display_notices[] = $update_notice;
            }
        }

	}

    protected function sanitise( string $value, ?string $key = null ): string
    {
        // if ( $key == 'protection_text' ) {
            // $this->log( [
            //     'k' => $key,
            //     'v' => $value,
            //     // 'config' => $this->getSetting( $key ),
            // ] );
        // }

        return sanitize_text_field( $value );
    }

}
