<?php

/**
 * 
 */

namespace CC\Api;

class Datatypes {
    
    /**
     * Convert to datatype
     * @param  mixed  $value    Raw data
     * @param  string  $dataType Convert to
     * @param  boolean $softFail Return value or error
     */
    
    public static function convert( $value, string $dataType, $softFail = true ){

        /** 
         * Switch datatypes
         */
        
        /** NULL */

        if( $value === null ){

            return null; }
        
        /** String */

        if( preg_match( '/^str|string$/i', $dataType ) ){

            switch( gettype( $value ) ){

                case 'array': return json_encode( $value, true ); break;
            
            default; return ( string ) $value; break; } }
        
        /** IP-address */

        if( preg_match( '/^ip$/i', $dataType ) ){

            return filter_var( $value, FILTER_VALIDATE_IP ) ? $value : null; }
        
        /** Email */

        if( preg_match( '/^mail|email|e\-mail$/i', $dataType ) ){

            return filter_var( $value, FILTER_VALIDATE_EMAIL ) ? $value : null; }
        
        /** Phone number */

        if( preg_match( '/^phone|phonenumber|e\.164$/i', $dataType ) ){

            return preg_match( '/^\+?[1-9]\d{1,14}$/', $value ) ? $value : null; }
        
        /** Integer */

        if( preg_match( '/^integer|int|numeric|num|number$/i', $dataType ) ){

            return intval( $value ); }
        
        /** Float */

        else if( preg_match( '/^float\:?(\d+)?$/i', $dataType, $decimals ) ){

            return ( float ) ( $decimals[ 1 ] ? number_format( ( float ) $value, ( int ) $decimals[ 1 ], '.', '' ) :  $value ); }

        /** Boolean */

        else if( preg_match( '/^(bool|boolean)$/i', $dataType ) ){

            switch( gettype( $value ) ){

                case 'string': return preg_match( '/^(true|yes|on|1)$/i', $value ) ? true : false; break;
                case 'array': return count( $value ) > 0; break;
            
            default; return ( boolean ) $value; break; } }

        /** Json */

        else if( preg_match( '/^(json)$/i', $dataType ) ){

            switch( gettype( $value ) ){

                case 'string':

                    if( preg_match( '/^null$/', $value ) ){ return null; }
                    else if( preg_match( '/^(true)$/', $value ) ){ return true; }
                    else if( preg_match( '/^(false)$/', $value ) ){ return false; } break;

                /*case 'array': foreach( $value as $key => $deeperLayer ){

                    $value[ $key ] = self :: convert( $deeperLayer, 'json', $softFail ); } return json_encode( $value, true ); break; */
            
            default; return json_encode( $value, true ); break; } }

        /** Array */

        else if( preg_match( '/^(arr|array|list)(\:(.+))?$/i', $dataType, $matches ) ){

            $values = []; switch( gettype( $value ) ){

                case 'string': $values = array_filter( explode( ',', $value ) ); break;
                case 'array': $values = $value; break; }

            /** Convert values if datatype is given */

            if( $matches[ 3 ] ?? false ){ 

                foreach( $values as &$value ){

                    $value = self::convert( $value, $matches[ 3 ], $softFail ); } }

            /** Return */
                
            return array_filter( $values ); }

        /** Datetime */

        else if( preg_match( '/^(datetime)$/i', $dataType ) ){

            try { return ( new \DateTime( ( string ) $value ) ) -> format( 'Y-m-d H:i:s' ); } catch( \Exception $error ){ return null; } }

        /** Date */

        else if( preg_match( '/^(date)$/i', $dataType ) ){

            try { return ( new \DateTime( ( string ) $value ) ) -> format( 'Y-m-d' ); } catch( \Exception $error ){ return null; } }

        /** Time */

        else if( preg_match( '/^(time)$/i', $dataType ) ){

            try { return ( new \DateTime( ( string ) $value ) ) -> format( 'H:i:s' ); } catch( \Exception $error ){ return null; } }

        /**
         *
         * Cryptography
         * 
         */
        
        /** Password */
        
        else if( preg_match( '/^(password)$/i', $dataType ) ){

            return Cryptography::password( $value ); }
        
        /** Encrypted */
        
        else if( preg_match( '/^(crypt)$/i', $dataType ) ){

            return Cryptography::encrypt( $value ); }
        
        /** Hashed */
        
        else if( preg_match( '/^(hash)$/i', $dataType ) ){

            return Cryptography::unhash( is_string( $value ) ? $value : '' ); }

        /**
         *
         * Custom
         * 
         */
        
        else if( preg_match( '/^(any|mixed|switch|html)$/i', $dataType ) ){

            return $value; }

        /** Return if set */

        return $softFail ? $value : new Error( sprintf( _( 'Invalid datatype %s: "%s"' ),  $dataType, $value ), 400 ); }

}

