<?php

namespace OnlineOptimisation\EmailEncoderBundle;


class EEB_Integrations_Loader {

    private static array $integrations = [
        // 'avada_builder'       => 'avada_builder.php',
        // 'bricks_builder'      => 'bricks_builder.php',
        // 'maintenance'         => 'maintenance.php',
        // 'divi_theme'          => 'divi_theme.php',
        // 'google_site_kit'     => 'google_site_kit.php',
        // 'oxygen_builder'      => 'oxygen_builder.php',
        // 'the_events_calendar' => 'the_events_calendar.php',
        // 'wpml'                => 'wpml.php',
        'hive_press'          => Integration\HivePress::class,
    ];

    // function __construct() {
    //     $this->boot();
    // }

    public static function boot(): void {

        foreach ( self::$integrations as $plugin_id => $class ) {

            if ( true !== apply_filters( 'eeb/integrations' . $plugin_id, true ) ) {
                continue;
            }

            $instance = new $class();
            $instance->boot();
        }
    }

    // public function load_integrations() {

    //     $plugins = array(
    //         'avada_builder' => 'avada_builder.php',
    //         'bricks_builder' => 'bricks_builder.php',
    //         'maintenance' => 'maintenance.php',
    //         'divi_theme' => 'divi_theme.php',
    //         'google_site_kit' => 'google_site_kit.php',
    //         'oxygen_builder' => 'oxygen_builder.php',
    //         'the_events_calendar' => 'the_events_calendar.php',
    //         'wpml' => 'wpml.php',
    //         'hive_press' => 'hive_press.php',
    //     );

    //     $services = array(
    //         //'foggy_email' => 'foggy_email.php' //Got discontinued
    //     );

    //     $integrations = array_merge( $plugins, $services );

    //     foreach ( $integrations as $plugin_id => $plugin_file ) :

    //         $plugin_file = 'classes/' . $plugin_file;
    //         $full_path = EEB_PLUGIN_DIR . 'core/includes/integrations/' . $plugin_file;

    //         if ( TRUE === apply_filters( 'eeb/integrations/' . $plugin_id, true ) ) {
    //             if ( file_exists( $full_path ) ) {
    //                 include $plugin_file;
    //             }
    //         }

    //     endforeach;

    // }

}