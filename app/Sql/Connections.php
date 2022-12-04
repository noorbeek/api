<?php

/**
 * 
 */

namespace CC\Api\Sql;

use CC\Api\Options;
use CC\Api\Error;

class Connections {

    /** Current */

    public static $connections = [];

    /**
     * Get database connection
     * @param  string $database Name
     * @return object           Database
     */
    
    public static function get( string $database ){

        /** Set options */

        $database = strtolower( $database );

        /** Return */

        return self::$connections[ $database ] ?? new Error( sprintf( _( 'Connection does not exist: "%s"' ), $database ), 500 ); }

    /**
     * set database connection
     * @param  string $database Name
     * @return object           Database
     */
    
    public static function set( string $database, array $options ){

        /** Set options */

        $database = strtolower( $database );

        /** Set options */

        self::$connections[ $database ] = array_merge( self::$connections[ $database ] ?? [], $options );

        /** Return */

        return self::$connections[ $database ]; }

    /**
     * Get database connection
     * @param  string $database Name
     * @return object           Database
     */
    
    public static function connect( string $database ){

        /** Set options */

        $connection = self::get( $database );

        /** Return connection if exists */

        if( isset( $connection[ 'pdo' ] ) ){

            return $connection[ 'pdo' ]; }

        /** Build connection string */

        $connectionString = ( $connection[ 'dsn' ] ?? 'mysql' ) . ':'; switch( $connection[ 'dsn' ] ){

            case 'mysql':

                $connectionString .= 'host=' . ( isset( $connection[ 'host' ] ) ? $connection[ 'host' ] : 'localhost' ) . ';';
                $connectionString .= 'port=' . ( isset( $connection[ 'port' ] ) ? $connection[ 'port' ] : 3306 ) . ';';
                $connectionString .= ( isset( $connection[ 'database' ] ) ? 'dbname=' . $connection[ 'database' ] . ';' : '' );
                $connectionString .= ( isset( $connection[ 'charset' ] ) ? 'charset=' . $connection[ 'charset' ] . ';' : '' ); break;

            case 'dblib':

                $connectionString .= 'host=' . ( isset( $connection[ 'host' ] ) ? $connection[ 'host' ] : 'localhost' ) . ';';
                $connectionString .= 'port=' . ( isset( $connection[ 'port' ] ) ? $connection[ 'port' ] : 1433 ) . ';';
                $connectionString .= ( isset( $connection[ 'database' ] ) ? 'dbname=' . $connection[ 'database' ] . ';' : '' );
                $connectionString .= ( isset( $connection[ 'charset' ] ) ? 'charset=' . $connection[ 'charset' ] . ';' : '' ); break;

            case 'sqlsrv':

                $connectionString .= 'Server=' . ( isset( $connection[ 'Server' ] ) ? $connection[ 'Server' ] : 'localhost' );
                $connectionString .= ',' . ( isset( $connection[ 'Port' ] ) ? $connection[ 'Port' ] : 1433 ) . ';';
                $connectionString .= ( isset( $connection[ 'Database' ] ) ? 'Database=' . $connection[ 'Database' ] . ';' : '' );
                $connectionString .= ( isset( $connection[ 'ConnectionPooling' ] ) ? 'ConnectionPooling=' . $connection[ 'ConnectionPooling' ] . ';' : '' );
                $connectionString .= ( isset( $connection[ 'APP' ] ) ? 'APP=' . $connection[ 'APP' ] . ';' : '' );
                $connectionString .= ( isset( $connection[ 'Encrypt' ] ) ? 'Encrypt=' . $connection[ 'Encrypt' ] . ';' : '' );
                $connectionString .= ( isset( $connection[ 'LoginTimeout' ] ) ? 'LoginTimeout=' . $connection[ 'LoginTimeout' ] . ';' : '' );
                $connectionString .= ( isset( $connection[ 'Failover_Partner' ] ) ? 'Failover_Partner=' . $connection[ 'Failover_Partner' ] . ';' : '' );
                $connectionString .= ( isset( $connection[ 'MultipleActiveResultSets' ] ) ? 'MultipleActiveResultSets=' . $connection[ 'MultipleActiveResultSets' ] . ';' : '' );
                $connectionString .= ( isset( $connection[ 'QuotedId' ] ) ? 'QuotedId=' . $connection[ 'QuotedId' ] . ';' : '' );
                $connectionString .= ( isset( $connection[ 'Server' ] ) ? 'Server=' . $connection[ 'Server' ] . ';' : '' );
                $connectionString .= ( isset( $connection[ 'TraceFile' ] ) ? 'TraceFile=' . $connection[ 'TraceFile' ] . ';' : '' );
                $connectionString .= ( isset( $connection[ 'TraceOn' ] ) ? 'TraceOn=' . $connection[ 'TraceOn' ] . ';' : '' );
                $connectionString .= ( isset( $connection[ 'TransactionIsolation' ] ) ? 'TransactionIsolation=' . $connection[ 'TransactionIsolation' ] . ';' : '' );
                $connectionString .= ( isset( $connection[ 'TrustServerCertificate' ] ) ? 'TrustServerCertificate=' . $connection[ 'TrustServerCertificate' ] . ';' : '' );
                $connectionString .= ( isset( $connection[ 'WSID' ] ) ? 'WSID=' . $connection[ 'WSID' ] . ';' : '' ); break;

        }

        /** Build PDO */

        try {

            $pdo = new \PDO( $connectionString, $connection[ 'username' ] ?? null, $connection[ 'password' ] ?? null, [

                \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET sql_mode="' . ( $connection[ 'mode' ] ?? 'STRICT_TRANS_TABLES' ) . '"' ]);

        } catch( \PDOException $error ){

            new Error( $error -> getMessage( ), 500 ); }

        /** Set PDO attributes */

        $pdo -> setAttribute( \PDO::ATTR_ERRMODE, Options::get( 'api.debug' ) ? \PDO::ERRMODE_EXCEPTION : \PDO::ERRMODE_SILENT );
        $pdo -> setAttribute( \PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC );
        $pdo -> setAttribute( \PDO::ATTR_EMULATE_PREPARES, false );

        /** Register connection */

        self::set( $database, [ 'pdo' => $pdo, 'connectionString' => $connectionString ]);

        /** Return */

        return $pdo; }

    /**
     * Check if database is connected
     * @param  string  $database Name
     * @return boolean           Is connected?
     */
    
    public static function isConnected( string $database ){

        /** Set options */

        $connection = self::get( $database );

        /** Return connection if exists */

        return isset( $connection[ 'options' ][ 'pdo' ] ) ? $connection[ 'options' ][ 'pdo' ] : false; }

}

?>