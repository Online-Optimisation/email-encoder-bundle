<?php

namespace Legacy\EmailEncoderBundle\Integration;

use OnlineOptimisation\EmailEncoderBundle\Integrations\IntegrationInterface;

class EventsCalendar implements IntegrationInterface {

    public function boot(): void {
        add_filter( 'tribe_get_organizer_email', [ $this, 'deactivate_logic' ], 100, 2 );
    }


    public function deactivate_logic( string $filtered_email, string $unfiltered_email ): string {
        return $unfiltered_email;
    }

}

