<?php

/**
 * 
 */

namespace CC\Api\Response;

    use CC\Api\Response;

class Pdf {

    /**
     * Parse array as CSV
     * @param array $response PHP array
     */
    
    public static function get( $response = [] ){
        
        /** Parse field method for PDF */
        
        $parseField = function( $field ){
            
            $field = str_replace( '";', '', $field );
            $field = str_replace( '"', '', $field );
            $field = str_replace( '\/', '/', $field );
            $field = str_replace( 'null', '', $field );
            
        return $field; };

        /** Build PDF content */

        $pdf = '<style>';
        $pdf .= 'th { border-bottom: 1px solid #ccc; padding: 10px 5px 10px 5px; text-align: left; text-transform: uppercase; background-color: #f7f7f7; }';
        $pdf .= 'td { border-bottom: 1px solid #eee; padding: 5px; vertical-align: top; }';
        $pdf .= '</style><table cellspacing="0" cellpadding="0"><thead>';

        /** Enumerate records from CSV */

        $lineNumber = 0; foreach( preg_split( '/\r\n/', Response\Csv::get( $response ) ) as $item ){

            /** First line (headers) */

            if( preg_match( '/\";/', $item ) ){ if( ++$lineNumber === 1 ){

                $pdf .= '<tr><th>#</th>'; foreach( explode( ';', $item ) as $field ){

                    $pdf .= '<th>' . $parseField( $field ) . '</th>'; }

                $pdf .= '</tr></thead><tbody>'; }

            /** Other lines (records) */

            else {

                $pdf .= '<tr><td style="color: #999;">' . ( $lineNumber - 1 ) . '</td>'; foreach( explode( ';', $item ) as $field ){

                    $pdf .= '<td>' . $parseField( $field ) . '</td>'; } $pdf .= '</tr>'; } } }

            /** Build PDF */

            $mpdf = new \Mpdf\Mpdf([ 'mode' => 'iso-8859-4', 'format' => 'A4-L', 'orientation' => 'L' ]);
            $mpdf -> allow_charset_conversion = true;
            $mpdf -> charset_in = 'UTF-8';
            $mpdf -> packTableData = true;
            $mpdf -> shrink_tables_to_fit = 1;

            /** Write content */

            $mpdf -> WriteHTML( mb_convert_encoding( $pdf, 'UTF-8', 'UTF-8' ) . '</tbody></table>' );

        /** Return */

        return $mpdf -> Output(); }

}

?>