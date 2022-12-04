<?php

/**
 * 
 */

namespace CC\Api;

    use \Hashids\Hashids;

class Cryptography {

    /** Constants */

    public static $cipher = 'AES-256-CBC';
    public static $key = 'Mn-HC48ZmEn_a]3r';
    public static $iv = '7H)p/dC=f-4nGYxc';
    public static $salt = 'f}49Up_J.X$v4u~K';
    public static $padding = 10;
    public static $alphabet = '0123456789abcdefghijklmnopqrstuvwxyz';
    
    /**
     * Encrypt plaintext
     * @param  string $plainText Input
     * @return string            Output
     */
    
    public static function encrypt( $plainText ){

        return $plainText ? base64_encode( openssl_encrypt( $plainText, self::$cipher, hash( 'sha256', self::$key ), 0, substr( hash( 'sha256', self::$iv ), 0, 16 ) ) ) : null; }
    
    /**
     * Decrypt plaintext
     * @param  string $encryptedText Input
     * @return string                Output
     */

    public static function decrypt( $encryptedText ){
        
        return $encryptedText ? openssl_decrypt( base64_decode( $encryptedText ), self::$cipher, hash( 'sha256', self::$key ), 0, substr( hash( 'sha256', self::$iv ), 0, 16 ) ) : null; }
    
    /**
     * Password hash/verification
     * @param  string      $password encrypted/unencrypted text
     * @param  string|null $key      Password for verification (optional)
     * @return mixed                 Encrypted password or true/false (verification)
     */
    
    public static function password( string $password, string $key = null ){

        return $key ? password_verify( $key, $password ) : password_hash( $password, PASSWORD_BCRYPT ); }

    /**
     * Hash input
     * @param  mixed   $input    Input
     * @return string            Hashed string
     */
    
    public static function hash( $arguments ){

        return ( new \Hashids\Hashids( self::$salt, self::$padding, self::$alphabet ) ) -> encode( func_get_args() ); }

    /**
     * Unhash input
     * @param  mixed   $input    Input
     * @return string            Unhashed string
     */
    
    public static function unhash( string $hash ){

        return ( new \Hashids\Hashids( self::$salt, self::$padding, self::$alphabet ) ) -> decode( $hash )[ 0 ] ?? 0; }

    /**
     * Create UUID
     * @return string            UUID
     */
    
    public static function uuid( $version = 4 ){

        /** Switch version */

        switch( $version ){

            case 1: $uuid = \Ramsey\Uuid\Uuid::uuid1(); break;
            case 2: $uuid = \Ramsey\Uuid\Uuid::uuid2(); break;
            case 3: $uuid = \Ramsey\Uuid\Uuid::uuid3(); break;
            case 5: $uuid = \Ramsey\Uuid\Uuid::uuid5(); break;
            case 6: $uuid = \Ramsey\Uuid\Uuid::uuid6(); break;
            default: $uuid = \Ramsey\Uuid\Uuid::uuid4(); break; }

        /** Return */

        return $uuid -> toString(); }

    /**
     * Generate password
     *
     * @param integer $length
     * @param boolean $lowercase
     * @param boolean $uppercase
     * @param boolean $numeric
     * @param boolean $special
     * @return string
     */

    public static function passwordGenerate( $length = 8, $lowercase = true, $uppercase = true, $numeric = true, $special = true ): string {

        /** Count requested types */

        $typeCount = $lowercase ? 1 : 0;
        $typeCount += $uppercase ? 1 : 0;
        $typeCount += $numeric ? 1 : 0;
        $typeCount += $special ? 1 : 0;

        /** Calculate amount of characters per type */

        $lengthPerType = ceil( $length / $typeCount ); $password = ''; foreach([
        
            'abcdefghijklmnopqrstuvwxyz' => $lowercase ? $lengthPerType : 0,
            'ABCDEFGHIJKLMNOPQRSTUVWXYZ' => $uppercase ? $lengthPerType : 0,
            '0123456789' => $numeric ? $lengthPerType : 0,
            '!@#$%&*_-=+' => $special ? $lengthPerType : 0
            
        ] as $characters => $amount ){

            $password = str_shuffle( $password . substr( str_shuffle( $characters ), 0, $amount ) ); }

        /** Return exact length */

        return substr( $password, 0, $length ); }

}
