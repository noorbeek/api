<?php

namespace CC\Api\Filesystem;

class Mimetypes {

    /**
     * Mimetypes
     * @var List
     */
    
    public static $mimetypes = [

        '3g2'   => 'video/3gpp2',
        '3gp'   => 'video/3gp',
        '7zip'  => 'application/x-7z-compressed',
        'aac'   => 'audio/x-acc',
        'ac3'   => 'audio/ac3',
        'ai'    => 'application/pdf',
        'aif'   => 'audio/x-aiff',
        'aifc'  => 'audio/x-aiff',
        'aiff'  => 'audio/x-aiff',
        'au'    => 'audio/x-au',
        'avi'   => 'video/x-msvideo',
        'bmp'   => 'image/bmp',
        'cdr'   => 'application/cdr',
        'cer'   => 'application/pkix-cert',
        'cpt'   => 'application/mac-compactpro',
        'crl'   => 'application/pkix-crl',
        'crt'   => 'application/x-x509-ca-cert',
        'css'   => 'text/css',
        'csv'   => 'text/x-comma-separated-values',
        'dcr'   => 'application/x-director',
        'der'   => 'application/x-x509-ca-cert',
        'dir'   => 'application/x-director',
        'doc'   => 'application/msword',
        'docx'  => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'dot'   => 'application/msword',
        'dotx'  => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'dvi'   => 'application/x-dvi',
        'dxr'   => 'application/x-director',
        'eml'   => 'message/rfc822',
        'eps'   => 'application/postscript',
        'f4v'   => 'video/mp4',
        'flac'  => 'audio/x-flac',
        'gif'   => 'image/gif',
        'gpg'   => 'application/gpg-keys',
        'gtar'  => 'application/x-gtar',
        'gz'    => 'application/x-gzip',
        'gzip'  => 'application/x-gzip',
        'hqx'   => 'application/mac-binhex40',
        'htm'   => 'text/html',
        'html'  => 'text/html',
        'ics'   => 'text/calendar',
        'jar'   => 'application/java-archive',
        'jpe'   => 'image/jpeg',
        'jpeg'  => 'image/jpeg',
        'jpg'   => 'image/jpeg',
        'js'    => 'application/javascript',
        'json'  => 'application/json',
        'kml'   => 'application/vnd.google-earth.kml+xml',
        'kmz'   => 'application/vnd.google-earth.kmz',
        'log'   => 'text/plain',
        'm3u'   => 'text/plain',
        'm4a'   => 'audio/x-m4a',
        'm4u'   => 'application/vnd.mpegurl',
        'mid'   => 'audio/midi',
        'midi'  => 'audio/midi',
        'mif'   => 'application/vnd.mif',
        'mov'   => 'video/quicktime',
        'movie' => 'video/x-sgi-movie',
        'mp2'   => 'audio/mpeg',
        'mp3'   => 'audio/mpeg',
        'mp4'   => 'video/mp4',
        'mpe'   => 'video/mpeg',
        'mpeg'  => 'video/mpeg',
        'mpg'   => 'video/mpeg',
        'mpga'  => 'audio/mpeg',
        'oda'   => 'application/oda',
        'ogg'   => 'audio/ogg',
        'p10'   => 'application/x-pkcs10',
        'p12'   => 'application/x-pkcs12',
        'p7a'   => 'application/x-pkcs7-signature',
        'p7c'   => 'application/pkcs7-mime',
        'p7m'   => 'application/pkcs7-mime',
        'p7r'   => 'application/x-pkcs7-certreqresp',
        'p7s'   => 'application/pkcs7-signature',
        'pdf'   => 'application/pdf',
        'pem'   => 'application/x-x509-user-cert',
        'pgp'   => 'application/pgp',
        'php'   => 'application/x-httpd-php',
        'php3'  => 'application/x-httpd-php',
        'php4'  => 'application/x-httpd-php',
        'phps'  => 'application/x-httpd-php-source',
        'phtml' => 'application/x-httpd-php',
        'png'   => 'image/png',
        'ppt'   => 'application/powerpoint',
        'pptx'  => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'ps'    => 'application/postscript',
        'psd'   => 'application/x-photoshop',
        'qt'    => 'video/quicktime',
        'ra'    => 'audio/x-realaudio',
        'ram'   => 'audio/x-pn-realaudio',
        'rar'   => 'application/x-rar',
        'rm'    => 'audio/x-pn-realaudio',
        'rpm'   => 'audio/x-pn-realaudio-plugin',
        'rsa'   => 'application/x-pkcs7',
        'rtf'   => 'text/rtf',
        'rtx'   => 'text/richtext',
        'rv'    => 'video/vnd.rn-realvideo',
        'shtml' => 'text/html',
        'sit'   => 'application/x-stuffit',
        'smi'   => 'application/smil',
        'smil'  => 'application/smil',
        'svg'   => 'image/svg+xml',
        'swf'   => 'application/x-shockwave-flash',
        'tar'   => 'application/x-tar',
        'text'  => 'text/plain',
        'tgz'   => 'application/x-tar',
        'tif'   => 'image/tiff',
        'tiff'  => 'image/tiff',
        'txt'   => 'text/plain',
        'vlc'   => 'application/videolan',
        'wav'   => 'audio/x-wav',
        'wbxml' => 'application/wbxml',
        'webm'  => 'video/webm',
        'wma'   => 'audio/x-ms-wma',
        'wmlc'  => 'application/wmlc',
        'wmv'   => 'video/x-ms-wmv',
        'word'  => 'application/msword',
        'xht'   => 'application/xhtml+xml',
        'xhtml' => 'application/xhtml+xml',
        'xl'    => 'application/excel',
        'xls'   => 'application/vnd.ms-excel',
        'xlsx'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'xml'   => 'application/xml',
        'xsl'   => 'application/xml',
        'xspf'  => 'application/xspf+xml',
        'z'     => 'application/x-compress',
        'zip'   => 'application/x-zip',
        'zsh'   => 'text/x-scriptzsh'

    ];

    /**
     * Get mimetype by filename or extension
     * @param  string $fileName File name or extension
     * @return string           MIME
     */
    
    public static function get( string $fileName ){

    	/** Get extension form file name */

        $fileName = strtolower( strstr( $fileName, '.' ) ? substr( strrchr( $fileName, '.' ), 1 ) : $fileName );

        /** Return match or default */

        return array_key_exists( $fileName, self :: $mimetypes ) ? self :: $mimetypes[ $fileName ] : 'application/octet-stream'; }

}
