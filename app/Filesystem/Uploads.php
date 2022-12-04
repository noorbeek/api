<?php

/**
 * 
 */

namespace CC\Api\Filesystem;

    use CC\Api\Options;
    use CC\Api\Error;
    use CC\Api\Response;

    use \claviska\SimpleImage;

class Uploads {

    /**
     * Cache
     */
    
    private static $uploads = null;

    /**
     * Create instance
     */
    
    public function __construct( $options = [], $files = null ){

        /** Return if already set */

        if( self::$uploads ){

            return $this; }

        /** Set options */

        $options = array_merge([

            'exception' => true,
            'convertTo' => false,
            'extensions' => [],
            'maxFiles' => Options::get( 'filesystem.maxUploadFiles' ),
            'maxFileSize' => Options::get( 'filesystem.maxUploadFileSize' ),
            'maxImageWidth' => 1000,
            'maxImageHeight' => 1000,
            'maxImageQuality' => 80 ], $options );

        /** Retrieve uploaded files */

        self::$uploads = $files = $files ? $files : ( isset( $_FILES ) && count( $_FILES ) ? array_values( $_FILES ) : [ ] );

        /** Check for upload errors */

        if( count( $files ) === 0 ){ return $options[ 'exception' ] ? new Error( _( 'Nothing uploaded.' ), 400 ) : []; }
        if( count( $files ) > $options[ 'maxFiles' ] ){ return $options[ 'exception' ] ? new Error( _( 'Amount of uploaded files invalid.' ), 400 ) : []; }

        /** Validate each file */

        foreach( $files as &$file ){ if( ( $file[ 'error' ] ?? 0 ) > 0 ){ $httpCode = 500; switch( $file[ 'error' ] ){

            /** Check for errors */

                case 1: $code = 'UPLOAD_ERR_INI_SIZE'; $error = _( 'The uploaded file exceeds the upload_max_filesize directive in php.ini.' ); break;
                case 2: $code = 'UPLOAD_ERR_FORM_SIZE'; $error = _( 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.' ); break;
                case 3: $code = 'UPLOAD_ERR_PARTIAL'; $error = _( 'The uploaded file was only partially uploaded.' ); $httpCode = 400; break;
                case 4: $code = 'UPLOAD_ERR_NO_FILE'; $error = _( 'No file was uploaded.' ); $httpCode = 400; break;
                case 6: $code = 'UPLOAD_ERR_NO_TMP_DIR'; $error = _( 'missing a temporary folder.' ); break;
                case 7: $code = 'UPLOAD_ERR_CANT_WRITE'; $error = _( 'Failed to write file to disk.' ); break;
                case 8: $code = 'UPLOAD_ERR_EXTENSION'; $error = _( 'A PHP extension stopped the file upload.' ); break;

            } return $options[ 'exception' ] ? new Error( $error, $httpCode ) : []; }

            /** Check for allowed extensions */

            $extensionCorrect = count( $options[ 'extensions' ] ) === 0 ? true : false; foreach( $options[ 'extensions' ] as $extension ){

                if( preg_match( '/\.' . strtolower( $extension ). '$/', strtolower( $file[ 'name' ] ) ) ){ $extensionCorrect = true; break; } }

            if( ! $extensionCorrect ){

                return $options[ 'exception' ] ? new Error( _( 'Invalid file type uploaded.' ), 400 ) : []; }

            /** Check for allowed maximum filesize */

            if( $file[ 'size' ] > $options[ 'maxFileSize' ] ){

                return $options[ 'exception' ] ? new Error( _( 'Filesize exceeds maximum allowed.' ), 400 ) : []; }

            /** If file is image */

            $extension = preg_match( '/\.([0-9a-z]+)$/i', $file[ 'name' ], $M ) ? strtolower( $M[ 1 ] ) : '.tmp';
            $filename = preg_replace( '/\.[0-9a-z]+$/i', '', $file[ 'tmp_name' ] ) . '.' . $extension;

            if( preg_match( '/^(png|gif|jpg|jpeg)$/i', $extension ) ){

                /** Duplicate file */

                rename( $file[ 'tmp_name' ], $filename );

                /** Create SimpleImage */

                $image = ( new SimpleImage( $filename ) );

                /** Create new filename */

                $file[ 'tmp_name' ] = $options[ 'convertTo' ] ? preg_replace( '/[^\.]+$/', $options[ 'convertTo' ], $filename ) : $filename;

                /** Auto rotate */

                $image -> autoOrient( );

                /** Resize image */

                if( $image -> getHeight( ) > $options[ 'maxImageHeight' ] || $image -> getWidth( ) > $options[ 'maxImageWidth' ] ){

                    $image -> bestFit( $options[ 'maxImageWidth' ], $options[ 'maxImageHeight' ] ); }

                /** Save file */

                $image -> toFile( $file[ 'tmp_name' ], null, $options[ 'maxImageQuality' ] );

                /** Overwrite file properties */

                if( $options[ 'convertTo' ] ){

                    $file[ 'type' ] = Mimetypes::get( $options[ 'convertTo' ] ); }

            $file[ 'size' ] = filesize( $file[ 'tmp_name' ] ); } }

        /** Overwrite files */

        self::$uploads = $files;

        /** Return */

        return $this; }

    /**
     * Get uploaded files
     * @param array $options Settings
     */
    
    public function get( $file = null ){

        return self::$uploads; }

}

?>