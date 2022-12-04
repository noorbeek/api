<?php

namespace CC\Api;

/** Log */

Log::title( 'Install API instance' );

/** Fields */

$fields = [

    'name' => [
        'type' => 'string',
        'description' => 'App name',
        'validate' => '/^[a-z0-9\s\-\_]+$/i' ],
    'namespace' => [
        'type' => 'string',
        'description' => 'App namespace (i.e. MyApp)',
        'validate' => '/^[A-Z]{1}[a-zA-Z]+$/' ],
    'development' => [
        'type' => 'bool',
        'description' => 'App DEVELOPMENT mode (y,n)',
        'y' => 'true',
        'n' => 'false' ],
    'port' => [
        'type' => 'string',
        'description' => 'App HTTP port',
        'validate' => '/^[0-9]{2,5}$/i' ],
    'dbHost' => [
        'type' => 'string',
        'description' => 'Database hostname',
        'validate' => '/^[a-z0-9\s\-\_\.]+$/i' ],
    'dbPort' => [
        'type' => 'string',
        'description' => 'Database port',
        'validate' => '/^[0-9]{2,5}$/i' ],
    'dbName' => [
        'type' => 'string',
        'description' => 'Database name',
        'validate' => '/^[a-z0-9\s\-\_]+$/i' ],
    'dbUsername' => [
        'type' => 'string',
        'description' => 'Database username',
        'validate' => '/^[a-z0-9\s\-\_]+$/i' ],
    'dbPassword' => [
        'type' => 'string',
        'description' => 'Database password',
        'validate' => '/^.+$/i' ] ];

/** Enumerate fields */

$app = []; foreach( $fields as $field => $properties ){

    $value = null; while( $value === null ){

        Log::highlight( PHP_EOL . $properties[ 'description' ] . PHP_EOL ); $value = readline( '> ' );

        /** Validate string */

        if( $properties[ 'type' ] === 'string' && ! preg_match( $properties[ 'validate' ], $value ) ){
        
            Log::error( PHP_EOL . 'Invalid value "' . $value . '" (' . $properties[ 'validate' ] . ')!' ); $value = null; }

        /** Validate bool */

        if( $properties[ 'type' ] === 'bool' && ! preg_match( '/^(y|n)$/i', $value ) ){
        
            Log::error( PHP_EOL . 'Invalid value "' . $value . '" (Y,N)!' ); $value = null; }

        else if( $properties[ 'type' ] === 'bool' ){
        
            $value = preg_match( '/^y$/i', $value ) ? 'true' : 'false'; }

        /** Set value */

        $fields[ $field ][ 'value' ] = $app[ $field ] = $value; } }

/** Generate required */

$app[ 'webRoot' ] = __DIR__;
$app[ 'key' ] = Cryptography::passwordGenerate( 16 );
$app[ 'iv' ] = Cryptography::passwordGenerate( 16 );
$app[ 'salt' ] = Cryptography::passwordGenerate( 16 );

/** Verify */

$continue = null; while( $continue === null ){

    Log::highlight( PHP_EOL . 'App:' . PHP_EOL ); foreach( $app as $key => $value ){
    
        Log::info( $key . ': ' . $value ); }

    Log::highlight( PHP_EOL . 'Do you want to create this app (y/n)?' . PHP_EOL ); $continue = strtolower( readline( '> ' ) );

    /** Validate */

    if( ! preg_match( '/^(y|n)$/i', $continue ) ){
    
        $continue = null; } 

    /** Stop */
        
    else if( $continue === 'n' ){

        Log::error( PHP_EOL . 'App "' . $app[ 'name' ] . '" not created.' ); }

    /** Create */
        
    else if( $continue === 'y' ){

        /** Create folders */

        foreach([ '/app', '/app/Models', '/app/Tasks', '/storage' ] as $directory ){
        
            if( ! is_dir( __DIR__ . $directory ) ){

                mkdir( __DIR__ . $directory ); } }

        /** Generate files */

        $templates = new \Twig\Loader\FilesystemLoader( __DIR__ . '/../Templates/Tasks/install' );

        /** Generate files */

        foreach([
        
            'options.php' => 'options.php',
            'index.php' => 'index.php',
            'compose.yaml' => 'compose.yaml',
            'composer.json' => 'composer.json',
            '.gitignore' => '.gitignore',
            '.env' => '.env',
            '.htaccess' => '.htaccess',
            'run' => 'run'
            
        ] as $template => $targetFile ){

            $fileContents = ( new \Twig\Environment( $templates ) ) -> render( $template, $app );

            Log::info( 'Writing' . PHP_EOL . $fileContents ); if( file_exists( $targetFile ) ){

                Log::error(  'File "' . $targetFile . '" already exists!' ); }

            else { file_put_contents( $targetFile, $fileContents ); } }

        /** Copy docker files */

        $copyCommand = 'Xcopy /E /I';

        Log::section( 'Creating docker folders' ); if( true || ! is_dir( __DIR__ . '/docker' ) ){ 
        
            shell_exec( 'cp docker ' . __DIR__ . '/dockerTest' ); }
    
        /** Ready */

        Log::success( PHP_EOL . 'App "' . $app[ 'name' ] . '" created successfully!' );
        Log::success( 'Execute "php run serve" to start the API or "php run" to view availabe commands.' . PHP_EOL );
    
    }}