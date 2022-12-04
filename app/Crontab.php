<?php

/**
 * 
 * Crontab controller
 * 
 */

namespace CC\Api;

class Crontab {
    
    	/** Cache */

	private $moment = null;

	/**
	 * Constructor
	 * Set time of moment
	 */
	
	public function __construct( ){

		$this -> moment = new \DateTime( ); }

	/**
	 * Validate 
	 * @param String $schedule '* * * * *'
	 */
	
	public function scheduleIsCurrentMoment( $schedule = '0,5-8,12 * * * *', $dateFormat = 'i-H-d-m-w' ){

		$moment = array_map( 'intval', explode( '-', date( $dateFormat ) ) );
		$schedule = $this -> scheduleToArray( $schedule );

		/** Validate */

		for( $i = 0; $i < count( $moment ); $i++ ){

			if( ! in_array( $moment[ $i ], $schedule[ $i ] ) ){

				return false; } }

		/** Validated */

		return true; }

	/**
	 * Parse schedule values
	 * @param String  $value   Schedule part (i.e. '*')
	 * @param Integer $maxAmount  Maximum number allowed (i.e. 60)
	 * @param integer $minAmount Start at 0 or 1 (time vs. date)
	 */
	
	private function schedulePartToArray( $value, $maxAmount = 0, $minAmount = 0 ){

		/** Parse NUMBER */

		$values = []; if( preg_match( '/^[0-9]+$/', $value ) ){

			$values[] = ( int ) $value; }

		/** Parse LIST */

		else if( preg_match( '/[\,]/', $value ) ){ foreach( explode( ',', $value ) as $value ){

			$values = array_merge( $values, $this -> schedulePartToArray( $value, $maxAmount, $minAmount ) ); } }

		/** Parse DIVISION */

		else if( preg_match( '/([^\/]+)\/([^\/]+)/', $value, $matches ) ){ foreach( $this -> schedulePartToArray( $matches[ 1 ], $maxAmount, $minAmount ) as $value ){

			if( $value % ( int ) $matches[ 2 ] === 0 ){

				$values[] = $value; } } }

		/** Parse RANGE */

		else if( preg_match( '/([0-9]+)\-([0-9]+)/', $value, $Range ) ){ for( $i = ( int ) $Range[ 1 ] + $minAmount; $i < ( int ) $Range[ 2 ] + $minAmount + 1; $i++ ){

			$values[] = $i; } }

		/** Parse ANY */

		else if( preg_match( '/[\*]/', $value ) ){ for( $i = $minAmount; $i < $maxAmount + $minAmount; $i++ ){

			$values[] = $i; } }

		/** Return values */

		return $values; }

	/**
	 * Parse schedule
	 * @param String $schedule '* * * * *'
	 */
	
	private function scheduleToArray( $schedule ){

		/** Validate schedule */

		if( ! preg_match( '/\s*([0-9\*\,\-\/]+\s+[0-9\*\,\-\/]+\s+[0-9\*\,\-\/]+\s+[0-9\*\,\-\/]+\s+[0-9\*\,\-\/]+)\s*/', $schedule ) ){

			new Error( _( 'Invalid cron schedule' ), 500 ); }

		/** Split schedule */

		list( $minute, $hours, $day, $month, $weekday ) = preg_split( '/\s/', preg_replace( '/(^\s+|\s+)$/', '', $schedule ) );

		/** Return parsed */

		return [ 

			$this -> schedulePartToArray( $minute, 60 ),
			$this -> schedulePartToArray( $hours, 24 ),
			$this -> schedulePartToArray( $day, 31, 1 ),
			$this -> schedulePartToArray( $month, 12, 1 ),
			$this -> schedulePartToArray( $weekday, 8 ) ]; }
    
}

?>