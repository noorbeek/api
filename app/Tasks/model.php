<?php

namespace CC\Api;

/** Log */

Log::title( 'Create model' );

/** Modelname */

$modelName = null; while( $modelName === null ){

    Log::highlight( PHP_EOL . 'Enter model name (i.e. Cars or Organizations):' . PHP_EOL ); $modelName = readline( '> ' );

    /** Validate name */

    if( ! preg_match( '/^[A-Z]{1}[a-z]{1}[a-zA-Z]*$/', $modelName ) ){
    
        Log::error( PHP_EOL . 'Invalid name "' . $modelName . '" (no PascalCase)!' ); $modelName = null; } }

/** Modelname (single) */

$modelNameSingle = null; while( $modelNameSingle === null ){

    Log::highlight( PHP_EOL . 'Enter model name of a single item (i.e. Car or Organization):' . PHP_EOL ); $modelNameSingle = readline( '> ' );

    /** Validate name */

    if( ! preg_match( '/^[A-Z]{1}[a-z]{1}[a-zA-Z]*$/', $modelNameSingle ) ){
    
        Log::error( PHP_EOL . 'Invalid name "' . $modelNameSingle . '" (no PascalCase)!' ); $modelNameSingle = null; } }

/** Database */

$database = null; while( $database === null ){

    Log::highlight( PHP_EOL . 'Enter SQL connection name (from options.php):' . PHP_EOL );

    /** List connection names */
    
    foreach( Sql\Connections::$connections as $connection => $properties ){

        Log::info( $connection ); } $database = readline( PHP_EOL . '> ' );

    /** Validate name */

    if( ! preg_match( '/^[a-z\-_]+$/i', $database ) ){
    
        Log::error( PHP_EOL . 'Invalid name "' . $database . '"!' ); $database = null; }

    /** Check if exists */

    if( ! isset( Sql\Connections::$connections[ $database ] ) ){
    
        Log::error( PHP_EOL . 'Database "' . $database . '" is NOT defined in options.php!' ); $database = null; } }

/** Table name */

$table = null; while( $table === null ){

    Log::highlight( PHP_EOL . 'Enter database table name:' . PHP_EOL ); $table = readline( '> ' );

    /** Validate name */

    if( ! preg_match( '/^[a-z0-9\-_]+$/i', $table ) ){
    
        Log::error( PHP_EOL . 'Invalid name "' . $table . '"!' ); $table = null; } }

/** Key name */

$key = null; while( $key === null ){

    Log::highlight( PHP_EOL . 'Enter database KEY name (i.e. id):' . PHP_EOL ); $key = readline( '> ' );

    /** Validate name */

    if( ! preg_match( '/^[a-z\-_]+$/i', $key ) ){
    
        Log::error( PHP_EOL . 'Invalid name "' . $key . '"!' ); $key = null; } }

/** Parent key name */

$parent = null; while( $parent === null ){

    Log::highlight( PHP_EOL . 'Enter database PARENT key name (optional, used for recursion when a record has a parent):' . PHP_EOL ); $parent = readline( '> ' );

    /** Validate name */

    if( $parent && ! preg_match( '/^[a-z\-_]+$/i', $parent ) ){
    
        Log::error( PHP_EOL . 'Invalid name "' . $parent . '"!' ); $parent = null; } }

/** Verify */

$continue = null; while( $continue === null ){

    Log::highlight( PHP_EOL . 'Model:' . PHP_EOL );
    
        Log::info( 'Name: ' . $modelName );
        Log::info( 'Name (single): ' . $modelNameSingle );
        Log::info( 'Connection: ' . $database );
        Log::info( 'Table: ' . $table );
        Log::info( 'Key: ' . $key );
        Log::info( 'Parent key: ' . ( $parent ? $parent : 'none' ) );

    Log::highlight( PHP_EOL . 'Do you want to create this model (y/n)?' . PHP_EOL ); $continue = strtolower( readline( '> ' ) );

    /** Validate */

    if( ! preg_match( '/^(y|n)$/i', $continue ) ){
    
        $continue = null; } 

    /** Stop */
        
    else if( $continue === 'n' ){

        Log::error( PHP_EOL . 'Model "' . $modelName . '" not created.' ); }

    /** Create */
        
    else if( $continue === 'y' ){

        /** Select target folder */

        Log::highlight( PHP_EOL . 'Select folder/namespace location:' . PHP_EOL );

        $target = null; while( $target === null ){

            /** List model sources */

            $targets = []; $n = 0; foreach( Models::sources() as $source => $namespace ){

                $targets[] = [ $source . '/' . $modelName . '.php', $source, $namespace ];
                Log::info( '[' . $n . '] ' . $targets[ $n ][ 0 ] . ' (namespace ' . $targets[ $n ][ 2 ] . ')' ); $n++; }

            $target = readline( PHP_EOL . '> ' );

            /** Match */

            if( $target !== null && isset( $targets[ intval( $target ) ] ) ){

                $target = $targets[ intval( $target ) ]; }

            else { $target = null; } }

        /** Generate file */

        $file = ( new \Twig\Environment( new \Twig\Loader\FilesystemLoader( __DIR__ . '/../Templates/Tasks' ) ) )

			-> render( 'Model.php', [
            
                'namespace' => preg_replace( '/(^[\\\]+|[\\\]+$)/', '', $target[ 2 ] ),
                'modelName' => $modelName,
                'modelNameSingle' => $modelNameSingle,
                'database' => $database,
                'table' => $table,
                'key' => $key,
                'parent' => $parent ]);

        /** Save file */

        if( file_exists( $target[ 0 ] ) ){

            Log::error(  PHP_EOL . 'File "' . $target[ 0 ] . '" already exists!' ); die(); }

        file_put_contents( $target[ 0 ], $file );
    
        /** Ready */

        Log::success( PHP_EOL . 'Model "' . $modelName . '" created successfully!' );
    
    }}