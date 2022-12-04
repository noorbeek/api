<?php

namespace CC\Api;

/** App */

Options::set( 'api.name', 'Api/API' );
Options::set( 'api.color', '#4a148c' );

/** Development */

Options::set( 'api.dev', false ); // DEV mode: allow options override in URL (i.e. ...?options=debug,log )
Options::set( 'api.debug', false );
Options::set( 'api.log', false );

/** Webserver */

Options::set( 'http.port', 443 );

/** Storage */

Options::set( 'root', __DIR__ );
Options::set( 'filesystem.maxUploadFiles', 20 );
Options::set( 'filesystem.maxUploadFileSize', 128 * 1024 * 1024 );

Filesystem\Storage::$adapterType = 'local';
Filesystem\Storage::$adapterProperties = [ 'root' => __DIR__ . '/storage/' ];

/** Encryption */

Cryptography::$key = '16-character-password';
Cryptography::$iv = '16-character-password';
Cryptography::$salt = '16-character-password';

/** 
 * 
 * Extend API with APP (optional)
 * 
 */

Options::set( 'models.directories', [ __DIR__ . '/app/Models' => '\My\App\Models\\' ]);
Options::set( 'tasks.directories', [ '../my/app/tasks' ]);
Options::set( 'sql.migrations.directories', [ '../my/database/migrations' ]);

/** 
 * Sql Server
 * $argv = if CLI > 127.0.0.1:13306 else if HTTP (docker) > database_image_name:3306
 */

Sql\Connections :: set( 'main', [

    'dsn' => 'mysql',
    'host' => isset( $argv ) ? '127.0.0.1' : 'hostname.nl',
    'port' => isset( $argv ) ? 13306 : 3306,
    'database' => 'database',
    'username' => 'username',
    'password' => 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
    'charset' => 'utf8mb4' ]);

/**
 *
 * Mailboxes
 * 
 */

Options::set( 'mailbox', [

    'outType' => 'SMTP',
    'outHost' => 'smtp.eu.mailgun.org',
    'outPort' => 587,
    'outSsl' => 'tls',
    'outUsername' => 'noreply@actiehouden.nl',
    'outPassword' => 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
    'outSendFrom' => 'noreply@actiehouden.nl' ]);

/** End */