<?php

namespace OnlineOptimisation\EmailEncoderBundle;


final class Email_Encoder {

	private static ?Email_Encoder $instance = null;
	public Email_Encoder_Settings $settings;
	public Email_Encoder_Helpers $helpers;
	public Email_Encoder_Validate $validate;

    private array $integrations = [
        'avada_builder'       => Integration\AvadaBuilder::class,
        // 'bricks_builder'      => 'bricks_builder.php',
        // 'maintenance'         => 'maintenance.php',
        // 'divi_theme'          => 'divi_theme.php',
        // 'google_site_kit'     => 'google_site_kit.php',
        // 'oxygen_builder'      => 'oxygen_builder.php',
        // 'the_events_calendar' => 'the_events_calendar.php',
        // 'wpml'                => 'wpml.php',
        'hive_press'          => Integration\HivePress::class,
    ];


	public static function instance(): self {
		if ( self::$instance === null ) {
			self::$instance = new self();
			self::$instance->boot();
		}

		return self::$instance;
	}


	private function boot(): void {

		$this->helpers  = new Email_Encoder_Helpers();
		$this->settings = new Email_Encoder_Settings();
		$this->validate = new Email_Encoder_Validate();

		new Email_Encoder_Ajax();
		$this->integrate3rdParty();
		new Email_Encoder_Run();

		do_action( 'eeb_plugin_loaded', $this );
	}

	private function integrate3rdParty(): void {

		foreach ( $this->integrations as $plugin_id => $class ) {

			if ( true !== apply_filters( 'eeb/integrations' . $plugin_id, true ) ) {
				continue;
			}

			error_log( 'integrate3rdParty: ' . $plugin_id );

			$instance = new $class();
			$instance->boot();
		}
	}


	/**
	 * Cloning instances of the class is forbidden.
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__,
			__( 'Cheatin&#8217; huh?', 'email-encoder-bundle' ),
			'2.0.0'
		);
	}

	/**
	 * Disable unserializing of the class.
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__,
			__( 'Cheatin&#8217; huh?', 'email-encoder-bundle' ),
			'2.0.0'
		);
	}
}
