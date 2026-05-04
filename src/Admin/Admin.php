<?php

namespace OnlineOptimisation\EmailEncoderBundle\Admin;

if ( ! defined( 'ABSPATH' ) ) exit;

use OnlineOptimisation\EmailEncoderBundle\Traits\PluginHelper;

class Admin
{
    use PluginHelper;

    /** @var array< string > $display_notices */
    public static array $display_notices = [];


    public function boot(): void
    {
        ( new AdminEnqueue() )->boot();
        ( new AdminMenu() )->boot(); // AdminHelp is added here
        ( new PluginActionLinks() )->boot();

        add_action( 'init', [ $this, 'register_hooks' ] );
    }

    # ADMIN METHODS ============================================================

    public function register_hooks(): void
    {
        add_action( 'admin_init', [ $this, 'save_settings_admin' ] );
    }





    public function save_settings_admin(): void
    {
        // $this->log( __METHOD__ );
        if ( !isset( $_POST[ $this->getPageName() . '_nonce' ] ) ) {
            return;
        };

        if ( ! wp_verify_nonce( $_POST[ $this->getPageName() . '_nonce' ], $this->getPageName() ) ) {
            wp_die( esc_html__( 'You don\'t have permission to update these settings.', 'email-encoder-bundle' ) );
        }

        if ( ! current_user_can( $this->getAdminCap( 'admin-update-settings' ) ) ) {
            wp_die( esc_html__( 'You don\'t have permission to update these settings.', 'email-encoder-bundle' ) );
        }

        $raw = wp_unslash( $_POST );

        if ( isset( $raw[ $this->getSettingsKey() ] ) && is_array( $raw[ $this->getSettingsKey() ] ) ) {

            //Strip duplicate slashes before saving
            foreach ( $raw[ $this->getSettingsKey() ] as $k => $v ) {
                if ( is_string( $v ) ) {
                    $raw[ $this->getSettingsKey() ][ $k ] = $this->sanitise( $v, $k );
                    // $this->log( $raw[ $this->getSettingsKey() ][ $k ] );
                }
            }

            // $this->log( $this->getSettingsKey() );
            $check = update_option( $this->getSettingsKey(), $raw[ $this->getSettingsKey() ] );

            if ( $check ) {
                $this->reloadSettings();
                $update_notice = $this->helper()->create_admin_notice( 'Settings successfully saved.', 'success', true );
            } else {
                $update_notice = $this->helper()->create_admin_notice( 'No changes were made to your settings with your last save.', 'info', true );
            }

            // PRG redirect: toggling `own_admin_menu` moves the page between
            // `options-general.php` and `admin.php`, so the POST URL can no
            // longer match a registered menu — leaving WP's `$title` null and
            // tripping a strip_tags() deprecation in admin-header.php. Stash
            // the notice in a per-user transient and bounce to the canonical URL.
            set_transient( $this->getNoticeTransientKey(), $update_notice, 30 );
            wp_safe_redirect( $this->getCanonicalAdminPageUrl() );
            exit;
        }

    }


    public function maybe_consume_redirect_notice(): void
    {
        $key = $this->getNoticeTransientKey();
        $notice = get_transient( $key );
        if ( $notice ) {
            delete_transient( $key );
            self::$display_notices[] = (string) $notice;
        }
    }


    private function getNoticeTransientKey(): string
    {
        return 'eeb_admin_notice_' . get_current_user_id();
    }


    private function getCanonicalAdminPageUrl(): string
    {
        $is_top_level = (string) $this->getSetting( 'own_admin_menu', true ) === '1';
        $base = $is_top_level ? 'admin.php' : 'options-general.php';
        return admin_url( $base . '?page=' . $this->getPageName() );
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
