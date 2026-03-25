<?php

namespace Legacy\EmailEncoderBundle\Integration;

if ( ! defined( 'ABSPATH' ) ) exit;

use OnlineOptimisation\EmailEncoderBundle\Integrations\IntegrationInterface;

class EventsCalendar implements IntegrationInterface {

    public function boot(): void {
        add_filter( 'tribe_get_organizer_email', [ $this, 'deactivate_logic' ], 100, 2 );
    }


    /**
     * @param string|null $filtered_email
     * @param string $unfiltered_email
     * @return string
     */
    public function deactivate_logic( $filtered_email, $unfiltered_email ): string {
        return $unfiltered_email;
    }

}

