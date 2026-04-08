<?php

declare( strict_types=1 );

use PHPUnit\Framework\TestCase;
use Legacy\EmailEncoderBundle\Email_Encoder_Settings;

/**
 * Verify that get_setting($slug, true) returns correct values
 * from the lightweight values path (before load_settings runs),
 * matching the fully-booted instance that has loaded field definitions.
 *
 * This guards against the _load_textdomain_just_in_time regression:
 * if the early path ever falls back to load_settings(), translations
 * will trigger too early on WP 6.7+.
 */
final class SettingsTimingTest extends TestCase
{
    private Email_Encoder_Settings $settings;

    protected function setUp(): void
    {
        $this->settings = EEB()->settings;
    }

    public function testEarlyAccessSimpleSettingMatchesBootedInstance(): void
    {
        $expected = $this->settings->get_setting( 'protect', true );

        // Fresh instance — load_settings() has NOT run, only load_values() path
        $fresh = new Email_Encoder_Settings();
        $actual = $fresh->get_setting( 'protect', true );

        $this->assertEquals( $expected, $actual );
    }

    public function testEarlyAccessGroupedSettingMatchesBootedInstance(): void
    {
        $expected = $this->settings->get_setting( 'filter_rss', true, 'filter_body' );

        $fresh = new Email_Encoder_Settings();
        $actual = $fresh->get_setting( 'filter_rss', true, 'filter_body' );

        $this->assertEquals( $expected, $actual );
    }
}
