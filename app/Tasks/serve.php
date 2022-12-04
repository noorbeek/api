<?php

namespace CC\Api;

/** Log */

Log::title( 'Serving API' );

/** Update, migrate and start docker */

Log::section( 'Composer' ); shell_exec( 'composer update' );
Log::section( 'Docker' ); shell_exec( 'docker-compose up -d --build' );
Log::section( 'Migrate database' ); shell_exec( 'php run migrate' );

/** Start browser */

$cmd = preg_match( '/winnt/i', PHP_OS ) ? 'start' : (  preg_match( '/linux/i', PHP_OS ) ? 'xdg-open' : 'open' );
$url = 'http://localhost:' . ( Options::get( 'http.port' ) ?? 80 ) ;

Log::section( 'Opening ' . $url . PHP_EOL ); shell_exec( $cmd . ' ' . $url );
