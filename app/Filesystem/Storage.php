<?php

/**
 * 
 */

namespace CC\Api\Filesystem;

    use CC\Api\Error;

class Storage {

    /**
     * Settings
     */
    
    public static $adapterType = 'local';
    public static $adapterProperties = [ 'root' => __DIR__ ];

    /**
     * Handles
     */
    
    private $adapter = null;

    /**
     * Create instance
     */
    
    public function __construct(){

        /** Return connection if exists */

        if( $this -> adapter ){

            return $this -> adapter; }

        /** Build connection */

        switch( strtolower( self::$adapterType ) ){

            case 'ftp':

                $this -> adapter = new \League\Flysystem\Ftp\FtpAdapter( \League\Flysystem\Ftp\FtpConnectionOptions::fromArray( self::$adapterProperties ) ); break;

            default:

                $this -> adapter = new \League\Flysystem\Local\LocalFilesystemAdapter( self::$adapterProperties[ 'root' ] ?? sys_get_temp_dir() ); break; }

        /** Return */

        return $this; }

    /**
     * Get mimetype by filename or extension
     * @param  string $fileName File name or extension
     * @return string           MIME
     */
    
    public function list( ){

        $rsp = []; foreach( $this -> adapter -> listContents( '/', false ) as $item ){

            /** Detect files */

            if( $item instanceof \League\Flysystem\FileAttributes) {

                $rsp[] = $item -> path(); } }

        /** Return */

        return $rsp; }

    /**
     * Get mimetype by filename or extension
     * @param  string $fileName File name or extension
     * @return string           MIME
     */
    
    public function get( string $file, $softFail = true ){

        try {

            return $this -> adapter -> read( $file );

        } catch( \League\Flysystem\FilesystemError | \League\Flysystem\UnableToReadFile $exception ){

            return $softFail ? false : new Error( $exception -> getMessage(), 404 ); }}

    /**
     * Get mimetype by filename or extension
     * @param  string $fileName File name or extension
     * @return string           MIME
     */
    
    public function write( string $name, $data, $options = [], $softFail = true ){

        try {

            return $this -> adapter -> write( $name, $data, new \League\Flysystem\Config( $options ) );

        } catch( \League\Flysystem\FilesystemError | \League\Flysystem\UnableToWriteFile $exception ){

            return $softFail ? false : new Error( $exception -> getMessage(), 404 ); }}

    /**
     * Get mimetype by filename or extension
     * @param  string $fileName File name or extension
     * @return string           MIME
     */
    
    public function delete( string $name, $softFail = true ){

        try {

            return $this -> adapter -> delete( $name );

        } catch( \League\Flysystem\FilesystemError | \League\Flysystem\UnableToDeleteFile $exception ){

            return $softFail ? false : new Error( $exception -> getMessage(), 404 ); }}

    /**
     * Get mimetype by filename or extension
     * @param  string $fileName File name or extension
     * @return string           MIME
     */
    
    public function fileExists( string $file, $softFail = true ){

        try {

            return $this -> adapter -> fileExists( $file );

        } catch( \League\Flysystem\FilesystemError | \League\Flysystem\UnableToReadFile $exception ){

            return $softFail ? false : new Error( $exception -> getMessage(), 404 ); }}

    /**
     * Get mimetype by filename or extension
     * @param  string $fileName File name or extension
     * @return string           MIME
     */
    
    public function fileSize( string $file, $softFail = true ){

        try {

            return $this -> adapter -> fileSize( $file )[ 'file_size' ] ?? 0;

        } catch( \League\Flysystem\FilesystemError | \League\Flysystem\UnableToReadFile $exception ){

            return $softFail ? false : new Error( $exception -> getMessage(), 404 ); }}

    /**
     * Get mimetype by filename or extension
     * @param  string $fileName File name or extension
     * @return string           MIME
     */
    
    public function getProperty( string $file, string $property = 'fileExists', $softFail = true ){

        /** Try get property */

        try {

            switch( $property ){

                case 'fileExists':

                    return $this -> adapter -> fileExists( $file ); break;

                case 'lastModified':

                    return $this -> adapter -> lastModified( $file ); break;

                case 'mimeType':

                    return $this -> adapter -> mimeType( $file ); break;

                case 'fileSize':

                    return $this -> adapter -> fileSize( $file ); break; }

        /** Exception */

        } catch( \League\Flysystem\FilesystemError | \League\Flysystem\UnableToReadFile $exception ){

            return $softFail ? false : new Error( $exception -> getMessage(), 404 );
        
        }}

}

