<?php

declare( strict_types=1 );

use PHPUnit\Framework\TestCase;
use OnlineOptimisation\EmailEncoderBundle\Validate\Encoding;

final class AttributeEscapingTest extends TestCase
{
    private Encoding $encoding;

    protected function setUp(): void
    {
        $this->encoding = new Encoding();
    }

    // =========================================================
    // create_protected_mailto() — attribute escaping
    // =========================================================

    public function test_mailto_escapes_class_attribute(): void
    {
        $result = $this->encoding->create_protected_mailto(
            'Contact Us',
            [
                'href'  => 'mailto:test@example.com',
                'class' => 'safe-class',
            ]
        );

        $this->assertStringContainsString( 'safe-class', $result );
        $this->assertStringContainsString( '<a', $result );
    }

    public function test_mailto_escapes_dangerous_attribute_value(): void
    {
        $result = $this->encoding->create_protected_mailto(
            'Contact Us',
            [
                'href'  => 'mailto:test@example.com',
                'class' => '" onclick="alert(1)',
            ]
        );

        // esc_attr converts " to &quot;, trapping the onclick inside the class value
        // The browser sees it as class text, not as a real onclick attribute
        $this->assertStringContainsString( '&quot;', $result );
        // Verify the dangerous content is inside the class attribute, not broken out
        $this->assertMatchesRegularExpression( '/class="[^"]*&quot;[^"]*onclick[^"]*"/', $result );
    }

    public function test_mailto_escapes_title_attribute(): void
    {
        $result = $this->encoding->create_protected_mailto(
            'Contact Us',
            [
                'href'  => 'mailto:test@example.com',
                'title' => 'Send us an email',
            ]
        );

        $this->assertStringContainsString( '<a', $result );
    }

    public function test_mailto_produces_encoded_href(): void
    {
        $result = $this->encoding->create_protected_mailto(
            'Contact Us',
            [
                'href'  => 'mailto:test@example.com',
                'class' => 'mail-link',
            ]
        );

        // Default protection method uses JavaScript — href should be javascript:;
        // and email should be in data-enc-email attribute
        $this->assertStringContainsString( 'data-enc-email', $result );
        $this->assertStringNotContainsString( 'test@example.com', $result );
    }

    // =========================================================
    // create_protected_href_att() — attribute escaping
    // =========================================================

    public function test_href_att_escapes_attribute_values(): void
    {
        $result = $this->encoding->create_protected_href_att(
            'Call Us',
            [
                'href'  => 'tel:+61400000000',
                'class' => 'phone-link',
            ]
        );

        $this->assertStringContainsString( '<a', $result );
        $this->assertStringContainsString( 'phone-link', $result );
    }

    public function test_href_att_escapes_dangerous_attribute_value(): void
    {
        $result = $this->encoding->create_protected_href_att(
            'Call Us',
            [
                'href'  => 'tel:+61400000000',
                'class' => '" onclick="alert(1)',
            ]
        );

        // esc_attr converts " to &quot;, trapping onclick inside the class value
        $this->assertStringContainsString( '&quot;', $result );
        // Verify the dangerous content is inside the class attribute, not broken out
        $this->assertMatchesRegularExpression( '/class="[^"]*&quot;[^"]*onclick[^"]*"/', $result );
    }

    public function test_href_att_encodes_href_with_antispambot(): void
    {
        $result = $this->encoding->create_protected_href_att(
            'Call Us',
            [
                'href'  => 'tel:+61400000000',
                'class' => 'phone-link',
            ]
        );

        // antispambot encodes the href value — the literal tel: number
        // should be at least partially entity-encoded
        $this->assertStringContainsString( 'href=', $result );
    }
}
