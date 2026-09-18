<?php

declare( strict_types=1 );

use PHPUnit\Framework\TestCase;

/**
 * Regression test for the admin "Security Check" padlock showing even when the
 * setting is disabled. WP.org "Encoded email padlock shows with setting disabled"
 * (xabster, Asana 1217034030697746) — reported on 2.5.2, regression since 2.5.0.
 *
 * The 2.5.0 settings form saves an unticked checkbox as the string '0' (a hidden
 * <input value="0"> submits when the box is unchecked). create_protected_mailto()
 * gated the admin padlock with `$show_encoded_check !== ''`, and '0' !== '' is true,
 * so the padlock kept showing even with the setting off. (2.4.8 stored the unticked
 * box as absent/'', which that check happened to handle.) The fix reads the value
 * via getSettingBool(), which correctly treats '0' as false.
 */
final class SecurityCheckPadlockTest extends TestCase
{
    /** @var int|\WP_Error */
    private $admin_id;

    protected function setUp(): void
    {
        // The padlock is admin-only (current_user_can('manage_options')). Without a
        // real admin the disabled-case assertion would pass vacuously (the icon never
        // renders for non-admins), so create one and log in as it.
        $this->admin_id = wp_insert_user( [
            'user_login' => 'eeb_padlock_admin',
            'user_pass'  => 'pw',
            'role'       => 'administrator',
        ] );

        if ( is_wp_error( $this->admin_id ) ) {
            $existing       = get_user_by( 'login', 'eeb_padlock_admin' );
            $this->admin_id = $existing ? $existing->ID : 0;
        }

        wp_set_current_user( (int) $this->admin_id );
    }

    protected function tearDown(): void
    {
        wp_set_current_user( 0 );

        if ( is_int( $this->admin_id ) && $this->admin_id > 0 ) {
            require_once ABSPATH . 'wp-admin/includes/user.php';
            wp_delete_user( $this->admin_id );
        }

        $this->setSecurityCheck( '' );
    }

    private function setSecurityCheck( $value ): void
    {
        $opts                        = get_option( 'WP_Email_Encoder_Bundle_options', [] );
        $opts['show_encoded_check']  = $value;
        update_option( 'WP_Email_Encoder_Bundle_options', $opts );
        // `init` never fires under the PHPUnit bootstrap, so force a fresh settings read.
        EEB()->settings->reload_settings();
    }

    private function mailto(): string
    {
        return EEB()->validate->encoding->create_protected_mailto(
            'x@example.com',
            [ 'href' => 'mailto:x@example.com' ],
            'with_javascript'
        );
    }

    public function test_disabled_checkbox_stored_as_zero_hides_padlock(): void
    {
        // Exactly what the 2.5.0 form saves for an unticked "Security Check" box.
        $this->setSecurityCheck( '0' );

        $this->assertStringNotContainsString(
            'eeb-encoded',
            $this->mailto(),
            'Padlock must NOT show when Security Check is disabled (stored as "0")'
        );
    }

    public function test_enabled_shows_padlock_for_admin(): void
    {
        // Guardrail: when actually enabled, an admin must still see the padlock —
        // proves the assertion above fails because the setting is off, not because
        // the icon never renders.
        $this->setSecurityCheck( '1' );

        $this->assertStringContainsString(
            'eeb-encoded',
            $this->mailto(),
            'Padlock SHOULD show for an admin when Security Check is enabled'
        );
    }
}
