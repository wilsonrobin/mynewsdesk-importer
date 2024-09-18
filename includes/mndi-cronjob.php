<?php

add_action( 'init', 'setup_cronjob' );
do_action( 'mndi_run_import' );

function setup_cronjob() {

    $recurrence = get_mndi_option('mndi_recurrence');

    if ( $recurrence !== 'none' && !wp_next_scheduled( 'mndi_run_import' ) ) {
        wp_schedule_event( time(), $recurrence, 'mndi_run_import' );
    }

}
