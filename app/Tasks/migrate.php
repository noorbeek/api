<?php

namespace CC\Api;

/** Log */

Log::title( 'API database migrations' );

/** Get all done migrations in array -> ID format */

$doneMigrations = []; foreach( ( new Models\ApiDatabaseMigrations ) -> get() as $migration ){
    
    $doneMigrations[] = strtolower( $migration[ 'id' ] ); }

/** Get pending migrations */

$count = 0; $log = []; foreach( array_merge( [ __DIR__ . '/../../database' ], Options::get( 'sql.migrations.directories' ) ?? [] ) as $directory ){

    foreach( scandir( $directory ) as $migration ){

    /** Check is file is valid */

    if( preg_match( '/^([\d]+\-[^\.]+)\.sql$/', $migration, $match ) && ! in_array( strtolower( $match[ 1 ] ), $doneMigrations ) ){

        /** Execute SQL statement & register migration */

        $sql = ( new Sql( 'main' ) ) -> raw( $directory . '/' . $migration );
        $register = ( new Sql( 'main' ) ) -> insert( 'apiDatabaseMigrations', [ 'id' => $match[ 1 ] ]) -> execute( );

        /** SQL file path */

        $count++; Log::info( 'File: ' . $migration . ' executed successfully' ); } } }

/** Ready */

Log::success( PHP_EOL . 'Database up-to-date (' . $count . ' migrations executed)' );