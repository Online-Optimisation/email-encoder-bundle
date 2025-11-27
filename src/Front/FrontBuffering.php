<?php

namespace OnlineOptimisation\EmailEncoderBundle\Front;

use OnlineOptimisation\EmailEncoderBundle\Traits\PluginHelper;

class FrontBuffering
{
    use PluginHelper;


    public function boot(): void {

        add_action( 'init', [ $this, 'buffer_final_output' ], 1000 );
    }



	public function buffer_final_output() {

		if ( defined( 'WP_CLI' ) || defined( 'DOING_CRON' ) ) {
			return;
		}

		if ( wp_doing_ajax() ) {
			//Maybe allow filtering for ajax requests
			$filter_ajax_requests = (int) $this->getSetting( 'ajax_requests', true, 'filter_body' );
			if ( $filter_ajax_requests !== 1 ) {
				return;
			}

		}

		if ( is_admin() ) {

			//Maybe allow filtering for admin requests
			$filter_admin_requests = (int) $this->getSetting( 'admin_requests', true, 'filter_body' );
			if ( $filter_admin_requests !== 1 ) {
				return;
			}

		}

		ob_start( [ $this, 'apply_content_filter' ] );
	}

	 /**
	 * Apply the callabla function for ob_start()
	 *
	 * @param string $content
	 * @return string - the filtered content
	 */
	public function apply_content_filter( $content ) {
		$filteredContent = apply_filters( $this->getFinalOutputBufferHook(), $content );

		// remove filters after applying to prevent multiple applies
		remove_all_filters( $this->getFinalOutputBufferHook() );

		return $filteredContent;
	}
}