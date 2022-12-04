<?php

namespace CC\Api;

/** Register error handlers */

register_shutdown_function( function( ){ return Error::fatal(); } );
set_exception_handler( function( $e ){ return Error::error( $e -> getCode(), $e -> getMessage(), $e -> getFile(), $e -> getLine() ); } );
set_error_handler( function( ){ return Error::error(); } );

/** Merge dotenv options */

if( file_exists( $_SERVER[ 'DOCUMENT_ROOT' ] . '/.env' ) ){

    $dotenv = \Dotenv\Dotenv::createImmutable( $_SERVER[ 'DOCUMENT_ROOT' ] );
    $dotenv -> load();

    foreach($_ENV as $key => $value){
        
        Options::set( $key, $value ); } }

/** Initialize HTTP request */

new Request();

/** Set locale */

date_default_timezone_set( Request::get( 'contentTimezone' ) ?? Options::get( 'api.timezone' ) ?? 'Europe/Amsterdam' );

$locale = ( Request::get( 'contentLanguage' ) ?? Options::get( 'api.locale' ) ?? 'nl_NL' ) . '.utf8';

// Linux

putenv( 'LANG=' . $locale ); if( defined( 'LC_MESSAGES' ) ){

    setlocale( LC_MESSAGES, $locale );
    bindtextdomain( 'messages', Options::get( 'root' ) . '/locale' ); }

// Windows

 else {

    bindtextdomain( 'messages', Options::get( 'root' ) . '\locale' ); }

// Both

bind_textdomain_codeset( 'messages', 'UTF-8' );
textdomain( 'messages' );

/** Parse OPTIONS request */

if( Request::$method === 'OPTIONS' ){

    Response::applyHeaders(); die(); }

/**
 * Set security headers
 */

Response::setHeaders([ 'Content-Security-Policy' =>

    'default-src \'self\'; font-src *;img-src * data:; script-src * \'nonce-' . Request::nonce() . '\'; style-src * \'nonce-' . Request::nonce() . '\'' ]);

/** 
 * 
 * Add DEV routes 
 * 
 */

if( Options::get( 'api.dev' ) ){
        
    Router::add([

        /** REST debug client */

        'GET/' => [ 'view' => function(){ return __DIR__ . '/app/Views/Client.php'; } ],

        /** REST API documentation */

        'GET/docs' => [ 'view' => function(){ return __DIR__ . '/app/Views/Docs.php'; } ],
        'GET/docs/:doc' => [ 'view' => function( $doc ){ return __DIR__ . '/app/Views/Docs/' . $doc . '.yaml'; } ],

        /** Authentication FORM */

        'GET/login' => [ 'run' => function(){ require __DIR__ . '/app/Views/Login.php'; } ],
        'POST/login' => [ 'run' => function(){ require __DIR__ . '/app/Views/Login.php'; } ],
        'GET/logoff' => [ 'run' => function(){ require __DIR__ . '/app/Views/Login.php'; } ],
        'POST/logoff' => [ 'run' => function(){ require __DIR__ . '/app/Views/Login.php'; } ] ]); }

/** 
 * 
 * Add standard routes 
 * 
 */

Router::add([

    /** Special actions/interfaces */

    'GET/download/:token' => [ 'attachment' => function( $token ){ return ( new Models\Files ) -> download( $token ); }],

    /** Content delivery route */

    'GET/cdn/:fileName' => [ 'attachment' => function( $fileName ){ return __DIR__ . '/app/Cdn/' . $fileName; }],

    /** Authentication JWT */

    'POST/auth' => [ 'write' => function(){ return Session::start( ( new Models\Users ) -> authenticate( Request::data( 'username' ), Request::data( 'password' ) ) ); }],

    /** Whoami */

    'GET/me' => [ 'roles' => 'user', 'write' => function(){ return ( new Models\Users ) -> getAccount( 'me' ); }],
    'POST/me/password' => [ 'write' => function( ){ return ( new Models\Users ) -> sendPasswordReset( Request::data( 'username' ) ?? 'null' ); }],
    'GET/me/password/:token' => [ 'run' => function( $token ){ return ( new Models\Users ) -> passwordReset( $token ); }],
    'POST/me/verify' => [ 'write' => function( ){ return ( new Models\Users ) -> sendAccountVerification( Request::data( 'username' ) ?? 'null' ); }],
    'GET/me/verify/:token' => [ 'run' => function( $token ){ return ( new Models\Users ) -> accountVerification( $token ); }]

]);