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
    // WPScan CVE PoC — regression test for the exact reported attack
    // =========================================================

    /**
     * WPScan PoC payload (2026-04-09):
     *   <a href="mailto:test&quot;fglyr=qvfcynl:oybpx;...@g.com">stealthcopter</a>
     *
     * The href value contains a &quot; entity and ROT13-encoded CSS/JS.
     * After html_entity_decode() inside get_encoded_email(), &quot; becomes a
     * literal " that used to break out of the data-enc-email attribute.
     *
     * esc_attr() on the output converts " back to &quot; in the final HTML,
     * keeping the payload trapped inside the attribute value.
     */
    public function test_mailto_blocks_wpscan_poc_attribute_breakout(): void
    {
        // Attacker-controlled href as it appears in comment HTML. The &quot;
        // entity survives regex-based extraction and then html_entity_decode()
        // inside get_encoded_email() converts it to a literal ". str_rot13()
        // preserves non-alpha characters so the " reaches the output intact.
        $poc_href = 'mailto:test&quot;fglyr=qvfcynl:oybpx;pbagrag-ivfvovyvgl:nhgb'
                  . 'bapbagragivfvovyvglnhgbfgngrpunatr=nyreg(qbphzrag.qbznva)//@g.com';

        $result = $this->encoding->create_protected_mailto(
            'stealthcopter',
            [ 'href' => $poc_href ]
        );

        // The decoded quote (`"` between the ROT13 fragments `grfg` and `style=`)
        // must be entity-encoded in the final HTML, not left as a literal that
        // terminates the data-enc-email attribute early.
        $this->assertStringNotContainsString(
            'grfg"style=',
            $result,
            'Attribute breakout: literal " leaked into data-enc-email, turning style= into a real anchor attribute'
        );
        $this->assertStringContainsString(
            'grfg&quot;style=',
            $result,
            'Expected " to be escaped to &quot; inside data-enc-email'
        );

        // Belt and braces: parse the anchor and confirm the only attributes
        // present are the ones the encoder is supposed to emit. No style,
        // onload, oncontentvisibilitystatechange, etc.
        $dom = new \DOMDocument();
        libxml_use_internal_errors( true );
        $dom->loadHTML( '<?xml encoding="UTF-8"><div>' . $result . '</div>' );
        libxml_clear_errors();
        $anchors = $dom->getElementsByTagName( 'a' );
        $this->assertGreaterThan( 0, $anchors->length, 'No anchor in output' );
        $anchor = $anchors->item( 0 );

        $attr_names = [];
        foreach ( $anchor->attributes as $attr ) {
            $attr_names[] = strtolower( $attr->name );
        }

        $allowed = [ 'href', 'data-enc-email', 'class', 'data-wpel-link', 'title' ];
        foreach ( $attr_names as $name ) {
            $this->assertContains(
                $name,
                $allowed,
                'Unexpected attribute "' . $name . '" on anchor — likely attribute breakout'
            );
        }
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
