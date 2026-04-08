<?php

namespace OnlineOptimisation\EmailEncoderBundle\Admin;

if ( ! defined( 'ABSPATH' ) ) exit;

use OnlineOptimisation\EmailEncoderBundle\Traits\PluginHelper;

class SupportExport
{
    use PluginHelper;


    public function generate(): string
    {
        $saved = $this->plugin()->settings->get_saved();
        $theme = wp_get_theme();

        $lines = [];

        // Header
        $lines[] = '=== Email Encoder Support Info ===';
        $lines[] = 'Plugin Version: ' . EEB_VERSION;
        $lines[] = 'PHP Version: ' . phpversion();
        $lines[] = 'WordPress Version: ' . get_bloginfo( 'version' );
        $lines[] = 'Theme: ' . $theme->get( 'Name' ) . ' (' . $theme->get( 'Version' ) . ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ? ', block theme' : ', classic theme' ) . ')';
        $lines[] = 'Multisite: ' . ( is_multisite() ? 'Yes' : 'No' );
        $lines[] = 'GD Library: ' . ( extension_loaded( 'gd' ) ? 'Yes' : 'No' );
        $lines[] = 'Memory Limit: ' . ( ini_get( 'memory_limit' ) ?: 'Unknown' );

        // Settings
        $lines[] = '';
        $lines[] = '=== Settings ===';
        $lines[] = 'Protection: ' . $this->format_protection_mode( $saved['protect'] ?? '' );
        $lines[] = 'Method: ' . $this->format_method( $saved['protect_using'] ?? '' );
        $lines[] = 'RSS Protection: ' . $this->on_off( $saved['filter_rss'] ?? '' );
        $lines[] = 'AJAX Protection: ' . $this->on_off( $saved['ajax_requests'] ?? '' );
        $lines[] = 'Admin Protection: ' . $this->on_off( $saved['admin_requests'] ?? '' );
        $lines[] = 'Convert to mailto: ' . $this->on_off( $saved['encode_mailtos'] ?? '' );
        $lines[] = 'Convert to images: ' . $this->on_off( $saved['convert_plain_to_image'] ?? '' );
        $lines[] = 'Input field protection: ' . $this->on_off( $saved['input_strong_protection'] ?? '' );
        $lines[] = 'Protection Text: ' . ( $saved['protection_text'] ?? '*protected email*' );
        $lines[] = 'Custom Classes: ' . ( ! empty( $saved['class_name'] ) ? $saved['class_name'] : '(none)' );
        $lines[] = 'Custom Href Attrs: ' . ( ! empty( $saved['custom_href_attr'] ) ? $saved['custom_href_attr'] : '(none)' );
        $lines[] = 'Skip Posts: ' . ( ! empty( $saved['skip_posts'] ) ? $saved['skip_posts'] : '(none)' );
        $lines[] = 'Skip Query Params: ' . ( ! empty( $saved['skip_query_parameters'] ) ? $saved['skip_query_parameters'] : '(none)' );
        $lines[] = 'Footer Scripts: ' . $this->on_off( $saved['footer_scripts'] ?? '' );
        $lines[] = 'Security Check: ' . $this->on_off( $saved['show_encoded_check'] ?? '' );

        // Active plugins
        if ( ! function_exists( 'get_plugins' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $plugins = get_plugins();
        $active = (array) get_option( 'active_plugins', [] );
        $active_plugins = [];

        foreach ( $active as $plugin_path ) {
            if ( isset( $plugins[ $plugin_path ] ) ) {
                $active_plugins[] = $plugins[ $plugin_path ]['Name'] . ' (' . $plugins[ $plugin_path ]['Version'] . ')';
            }
        }

        if ( is_multisite() ) {
            $network_plugins = get_site_option( 'active_sitewide_plugins', [] );
            foreach ( array_keys( (array) $network_plugins ) as $plugin_path ) {
                if ( isset( $plugins[ $plugin_path ] ) ) {
                    $active_plugins[] = $plugins[ $plugin_path ]['Name'] . ' (' . $plugins[ $plugin_path ]['Version'] . ') [network]';
                }
            }
        }

        $lines[] = '';
        $lines[] = '=== Active Plugins (' . count( $active_plugins ) . ') ===';
        foreach ( $active_plugins as $plugin_name ) {
            $lines[] = $plugin_name;
        }

        return implode( "\n", $lines );
    }


    /**
     * @param mixed $value
     */
    private function format_protection_mode( $value ): string
    {
        $modes = [
            '1' => 'Full-page scan',
            '2' => 'WordPress filters',
            '3' => 'Disabled',
        ];

        return $modes[ (string) $value ] ?? 'Unknown (' . $value . ')';
    }


    /**
     * @param mixed $value
     */
    private function format_method( $value ): string
    {
        $methods = [
            'with_javascript'    => 'With JavaScript (auto best)',
            'without_javascript' => 'Without JavaScript (CSS)',
            'strong_method'      => 'Strong method',
            'char_encode'        => 'Character encoding',
        ];

        return $methods[ (string) $value ] ?? 'Unknown (' . $value . ')';
    }


    /**
     * @param mixed $value
     */
    private function on_off( $value ): string
    {
        return ( $value === 1 || $value === '1' ) ? 'On' : 'Off';
    }
}
