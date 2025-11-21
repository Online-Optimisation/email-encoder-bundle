<?php

namespace Legacy\EmailEncoderBundle\Integration;

class EventsCalendar {

    public function boot(): void {
        add_filter( 'tribe_get_organizer_email', [ $this, 'deactivate_logic' ], 100, 2 );
    }


    public function deactivate_logic( $filtered_email, $unfiltered_email ) {
        return $unfiltered_email;
    }

}

