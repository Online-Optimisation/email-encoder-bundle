<?php

namespace OnlineOptimisation\EmailEncoderBundle;


final class Email_Encoder {

	private static ?Email_Encoder $instance = null;
	public Email_Encoder_Settings $settings;
	public Email_Encoder_Helpers $helpers;
	public Email_Encoder_Validate $validate;
	public Email_Encoder_Ajax $ajax;

    private array $integrations = [
        'avada_builder'       => Integration\AvadaBuilder::class,
        'bricks_builder'      => Integration\BricksBuilder::class,
        'maintenance'         => Integration\Maintenance::class,
        'divi_theme'          => Integration\DiviTheme::class,
        'google_site_kit'     => Integration\GoogleSiteKit::class,
        'oxygen_builder'      => Integration\OxygenBuilder::class,
        'the_events_calendar' => Integration\EventsCalendar::class,
        'wpml'                => Integration\Wpml::class,
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

		$this->ajax = new Email_Encoder_Ajax();
		$this->ajax->boot();

		$this->integrate3rdParty();
		new Email_Encoder_Run();

		do_action( 'eeb_plugin_loaded', $this );
	}


	private function integrate3rdParty(): void {

		foreach ( $this->integrations as $plugin_id => $class ) {

			if ( true !== apply_filters( 'eeb/integrations/' . $plugin_id, true ) ) {
				continue;
			}

			$instance = new $class();
			$instance->boot();
		}
	}


	/**
	 * Protection.
	 * Cloning instances and unserializing of the class is forbidden.
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__,
			__( 'Cheatin&#8217; huh?', 'email-encoder-bundle' ),
			'2.0.0'
		);
	}

	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__,
			__( 'Cheatin&#8217; huh?', 'email-encoder-bundle' ),
			'2.0.0'
		);
	}
}
