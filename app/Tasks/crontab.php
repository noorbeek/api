<?php

namespace CC\Api;

/** Log */

Log::title( 'Crontab' );

/** Execute database migration */

$cronTab = new Crontab;
$cronJobs = ( new Models\ApiCronjobs ) -> get();

/** Get cronjobs */

$count = 0; foreach( $cronJobs as $cronJob ){

    /** Check if cronJob is active */

    if( $cronTab -> scheduleIsCurrentMoment( $cronJob[ 'schedule' ] ) ){

        /** Execute cronjob async */

        Log::section( 'Cronjob: ' . $cronJob[ 'task' ] . ' @ ' . $cronJob[ 'schedule' ] );
        Log::info( pclose( popen( 'php ' . __DIR__ . '/../../run ' . $cronJob[ 'task' ], 'w' ) ) ); $count++; } }

/** Log no cronjobs */

Log::success( PHP_EOL . 'Crontab ready: ' . $count . ' jobs executed' );