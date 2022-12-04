<?php

/**
 * Office 365 Connector
 * @subpackage \Api\Services\Zabbix
 */

namespace CC\Api\Services;

    use CC\Api\Options;
    use CC\Api\Models;
    use CC\Api\Sql;
    use CC\Api\Error;
    use CC\Api\Request;
    use CC\Api\Response;
    use CC\Api\Cryptography;
    use CC\Api\Session;

class Zabbix {

	/**
	 * Variables
	 */

	public $headers = [ 'Accept' => 'application/json', 'Content-Type' => 'application/json' ];
	private $payload = [ 'jsonrpc' => '2.0', 'id' => 1, 'auth' => null ];
	private $endpoint = null;
	private $endpointGraph = null;

	/**
	 * Constants
	 */
	
	private static $groupId = 35;
	private static $serviceId = 78;
	private static $templateId = 11165;
	private static $templateTriggerId = 19239;

	/**
	 * Create class and set constants
	 */
	
	public function __construct( ){

		/** Set class constants */

		$this -> endpoint = Options::get( 'services.zabbix')[ 'endpoint' ] ?? '';
		$this -> endpointGraph = Options::get( 'services.zabbix')[ 'endpointGraph' ] ?? '';
		$this -> username = Options::get( 'services.zabbix')[ 'username' ] ?? '';
		$this -> password = Options::get( 'services.zabbix')[ 'password' ] ?? '';

		/** Set UniRest class constants */

        \Unirest\Request :: timeout( 30 );
        \Unirest\Request :: verifyPeer( false );
        \Unirest\Request :: verifyHost( false );

        /** Return class */

	return $this -> login( ); }

	/**
	 * Build URL
	 */
	
	private function payload( $payload = [] ){

		return \Unirest\Request\Body :: json( array_merge( $this -> payload, $payload ) ); }

	/**
	 * Get system token
	 */
	
	public function login( ){

		/** Return if authenticated */

		if( $this -> payload[ 'auth' ] ){

			return $this; }

		/** Perform request */

		$request = \Unirest\Request :: post( $this -> endpoint, $this -> headers, $this -> payload([

			'method' => 'user.login', 'params' => [ 'user' => $this -> username, 'password' => $this -> password ] ]) );

		/** Verify state */

		if( isset( $request -> error ) || $request -> code > 200 || isset( $request -> body -> error ) ){

			new Error([ $request ], $request -> code ?? 500 ); }

		/** Extract & set cookie */

		$this -> payload[ 'auth' ] = $request -> body -> result;

	return $this; }

	/**
	 * Get method
	 * @param String $Uri URI
	 */
	
	public function execute( $method, $params = [], $softFail = false ){

		/** Perform request */

		$request = \Unirest\Request :: post( $this -> endpoint, $this -> headers, $this -> payload([

			'method' => $method, 'params' => $params ]) );

		/** Verify state */

		if( isset( $request -> body -> error ) || $request -> code > 200 ){

			return $softFail ? false : new Error( ( $request -> body -> error -> message ?? '' ) . ( $request -> body -> error -> data ?? '' ), ( $request -> code ?? 500 ) >= 400 ? $request -> code ?? 500 : 400 ); }

		/** Return data */

	return $request -> body -> result; }

	/**
	 * Get method
	 * @param String $Uri URI
	 */
	
	public function graph( $params = [], $softFail = false ){

		/** Perform request */

		$request = \Unirest\Request :: post( $this -> endpointGraph, array_merge( $this -> headers, [ 'Content-Type' => 'application/json-rpc' ]), $this -> payload([

			'method' => 'graph.image',
			'params' => array_merge( $params, [ 'authtype' => 'token' ]) ]) );

		/** Verify state */

		if( ( $request -> body -> error -> code ?? false ) || $request -> code > 200 ){

			return $softFail ? false : new Error( [$request], ( $request -> code ?? 500 ) >= 400 ? $request -> code ?? 500 : 400 ); }

		/** Return data */

	return $request -> body -> result -> image; }

	/**
	 *
	 * Methods
	 * 
	 */
	
	public function poll( $ipOrId ){

        /** Parse IP/ID */

        $filter = filter_var( $ipOrId, FILTER_VALIDATE_IP ) ? [ 'ip' => $ipOrId ] : [ 'hostid' => $ipOrId ];
        $host = $this -> execute( 'host.get', [ 'filter' => $filter ] )[ 0 ] ?? null;

        /** Check if host exists */

        if( ! $host ){

        	return false; }

        /** Get items */

        $items = $this -> execute( 'item.get', [ 'filter' => [ 'hostid' => $host -> hostid ?? '0' ] ] ) ?? []; $data = [

            /** Defaults */

            'id' => ( int ) $host -> hostid,
            'host' => $host -> host,
            'name' => $host -> name,
            'description' => '',
            'location' => '',
            'contact' => '',
            'available' => false,
            'uptime' => '',
            'pingloss' => 0,
            'pingtime' => 0,
            'sla' => [],
            'snmp' => [],
            'graphs' => null

        ]; foreach( $items as $item ){

            /** Map from default items */

            $template = false; foreach([

                'icmpping' => 'available',
                'icmppingloss' => 'pingloss',
                'icmppingsec' => 'pingtime',
                'sysUpTimeInstance' => 'uptime',
                'sysUpTime' => 'uptime',
                'sysDescr' => 'description',
                'sysLocation' => 'location'

            ] as $snmpKey => $dataKey ){

                if( $snmpKey === ( $item -> key_ ?? '' ) ){

                    $template = true; $data[ $dataKey ] = $item -> lastvalue ?? ''; }}

            /** Set from custom SNMP items */

            if( ! $template && ( $item -> snmp_oid ?? false ) ){

                /** Parse key */

                $data[ 'snmp' ][ $item -> key_ ][ 'name' ] = $item -> name;
                $data[ 'snmp' ][ $item -> key_ ][ 'description' ] = $item -> description;
                $data[ 'snmp' ][ $item -> key_ ][ 'oid' ] = $item -> snmp_oid;
                $data[ 'snmp' ][ $item -> key_ ][ 'units' ] = $item -> units;
                $data[ 'snmp' ][ $item -> key_ ][ 'timestamp' ] = ( new \DateTime ) -> setTimestamp( $item -> lastclock ) -> format( 'c' );

                /** Covert datatypes */

                switch( ( int ) $item -> value_type ){

                    case 0: $data[ 'snmp' ][ $item -> key_ ][ 'value' ] = ( float ) $item -> lastvalue; break;
                    case 3: $data[ 'snmp' ][ $item -> key_ ][ 'value' ] = ( int ) $item -> lastvalue; break;
                    default: $data[ 'snmp' ][ $item -> key_ ][ 'value' ] = $item -> lastvalue; break; } }

            /** Covert datatypes */

            $data[ 'available' ] = ( bool ) $data[ 'available' ];
            $data[ 'pingloss' ] = ( float ) $data[ 'pingloss' ];
            $data[ 'pingtime' ] = ( float ) $data[ 'pingtime' ]; }

        /** Sort SNMP */

        usort( $data[ 'snmp' ], function( $a, $b ){

            return $a[ 'name' ] <=> $b[ 'name' ]; });

        /** Get availability of last YEAR */

        $availability = ( array ) $this -> execute( 'service.getsla', [ 'intervals' => [

            'from' => ( new \DateTime ) -> sub( new \DateInterval( 'P1Y' ) ) -> format( 'c' ),
            'to' => ( new \DateTime ) -> format( 'c' ) ] ]);

        /** 
         * 
         * Get SLA
         * 
         */

        foreach( $this -> execute( 'service.get', [] ) as $service ){

            if( preg_match( '/' . $host -> name . '/i', $service -> name ) ){ $data[ 'sla' ] = [

                'id' => ( int ) $service -> serviceid,
                'name' => $service -> name,
                'target' => ( float ) $service -> goodsla,
                'availability' => ( float ) number_format( $availability[ $service -> serviceid ] -> sla[ 0 ] -> sla ?? 0, 3 ) ]; }}

        /**
         * 
         * Sync
         * 
         */
        


        /** 
         * 
         * Get graph
         * 
         */

        $graph = $this -> execute( 'graph.get', [ 'filter' => [ 'hostid' => $host -> hostid ?? '0' ], 'search' => [ 'name' => 'ICMP' ] ])[ 0 ] ?? null; if( $graph ){

	        /** Get graph */

	        $data[ 'graphs' ][ 'hour' ] = 'data:image/png;base64,' . $this -> graph([

	        	'format' => 'base64',
	        	'from' => ( new \DateTime ) -> sub( new \DateInterval( 'PT1H' ) ) -> format( 'Y-m-d H:i' ),
	        	'to' => ( new \DateTime ) -> format( 'Y-m-d H:i' ),
	        	'graphid' => $graph -> graphid ]);

	        $data[ 'graphs' ][ 'day' ] = 'data:image/png;base64,' . $this -> graph([

	        	'format' => 'base64',
	        	'from' => ( new \DateTime ) -> sub( new \DateInterval( 'P1D' ) ) -> format( 'Y-m-d H:i' ),
	        	'to' => ( new \DateTime ) -> format( 'Y-m-d H:i' ),
	        	'graphid' => $graph -> graphid ]);

	        $data[ 'graphs' ][ 'week' ] = 'data:image/png;base64,' . $this -> graph([

	        	'format' => 'base64',
	        	'from' => ( new \DateTime ) -> sub( new \DateInterval( 'P7D' ) ) -> format( 'Y-m-d H:i' ),
	        	'to' => ( new \DateTime ) -> format( 'Y-m-d H:i' ),
	        	'graphid' => $graph -> graphid ]);

	        $data[ 'graphs' ][ 'month' ] = 'data:image/png;base64,' . $this -> graph([

	        	'format' => 'base64',
	        	'from' => ( new \DateTime ) -> sub( new \DateInterval( 'P30D' ) ) -> format( 'Y-m-d H:i' ),
	        	'to' => ( new \DateTime ) -> format( 'Y-m-d H:i' ),
	        	'graphid' => $graph -> graphid ]); }

        /** Return */

        return $data; }

    /**
     * Sync IP if not exists
     * @param  String $ip IP-address
     * @return array     Object
     */
    
	public function sync( $ip, $name = null, $description = null, $serviceLevelId = null ){

		/** Validate IP */

		if( ! filter_var( $ip, FILTER_VALIDATE_IP ) ){

			new Error( sprintf( _( 'Not a valid IP address: "%s"' ), $ip ), 400 ); }

		/** 
		 * Check if HOST exists or create
		 */

		$host = $this -> execute( 'host.get', [ 'filter' => [ 'ip' => $ip ] ] )[ 0 ] ?? null;

		/** Create or update */

		$data =  [

        	'host' => $ip,
        	'name' => $name ? $name : '',
        	'description' => $description ? $description : '' ];

		if( ! $host ){

        	/** Create */

	        $this -> execute( 'host.create', array_merge( $data, [

	            'groups' => [[ 'groupid' => self::$groupId ]],
	            'templates' => [[ 'templateid' => self::$templateId ]],
	            'interfaces' => [[

	            	'type' => 1,
	            	'main' => 1,
	            	'useip' => 1,
	            	'ip' => $ip,
	            	'dns' => '',
	            	'port' => '10050' ]]]));

        	/** Get */

	       	$host = $this -> execute( 'host.get', [ 'filter' => [ 'ip' => $ip ] ] )[ 0 ] ?? null; }

	    /** Update */

	    $this -> execute( 'host.update', array_merge([ 'hostid' => $host -> hostid ?? '0' ], $data ) );

		    $host -> name = $data[ 'name' ];
		    $host -> description = $data[ 'description' ];

	    /** Get serviceLevel */

	    $serviceLevel = ( new Models\ServiceLevels ) -> get( $serviceLevelId ?? null, $serviceLevelId ? [] : [ 'where' => 'default:true' ]);
	    $serviceLevel = $serviceLevelId ? $serviceLevel : $serviceLevel[ 0 ];

	    /** Get trigger */

	    $triggerId = ( int ) $this -> execute( 'trigger.get', [ 'filter' => [

	    	'hostid' => $host -> hostid ?? '0',
	    	'templateid' => self::$templateTriggerId ] ] )[ 0 ] -> triggerid ?? 0;

	    /** 
	     * Check if SLA exists or create
	     */

	    $sla = $this -> execute( 'service.get', [ 'filter' => [ 'parentids' => self::$serviceId ], 'search' => [ 'name' => $ip ] ] )[ 0 ] ?? null; $updateSla = true; if( ! $sla ){

        	/** Create */

	        $updateSla = false; $this -> execute( 'service.create', [

	        	'name' => $ip,
	            'parentid' => self::$serviceId,
	            'triggerid' => $triggerId,
	            'algorithm' => 1,
	            'showsla' => 1,
	            'sortorder' => 0,
	            'goodsla' => $serviceLevel[ 'availability' ] ]);

        	/** Get */

	       	$sla = $this -> execute( 'service.get',[ 'filter' => [ 'parentids' => self::$serviceId ], 'search' => [ 'name' => $ip ] ] )[ 0 ] ?? null; }

	    /** Update SLA */

	    if( $updateSla ){ $this -> execute( 'service.update', [

        	'name' => $ip,
	        'serviceid' => $sla -> serviceid,
            'goodsla' => $serviceLevel[ 'availability' ] ]); }

	    /** Reset serviceTimes */

	    $this -> execute( 'service.deletetimes', [ $sla -> serviceid ]); foreach( $serviceLevel[ 'periods' ] as $period ){

	    	$from = $to = []; preg_match( '/^([a-z]{2,3})\-([\d]{2})\:([\d]{2})\-([a-z]{2,3})\-([\d]{2})\:([\d]{2})$/i', $period, $part );

	    	/** Skip if invalid format */

	    	if( ! isset( $part[ 6 ] ) ){

	    		continue; }

	    	/** Convert days */

	    	$first = true; foreach([ $part[ 1 ], $part[ 4 ] ] as $day ){

	    		if( preg_match( '/(su|sun|zo|zon)/i', $day ) ){ $day = 0; }
	    		else if( preg_match( '/(mo|mon|ma|man)/i', $day ) ){ $day = 1; }
	    		else if( preg_match( '/(tu|tue|di|din)/i', $day ) ){ $day = 2; }
	    		else if( preg_match( '/(we|wed|wo|woe)/i', $day ) ){ $day = 3; }
	    		else if( preg_match( '/(th|thu|do|don)/i', $day ) ){ $day = 4; }
	    		else if( preg_match( '/(fr|fri|vr|vri)/i', $day ) ){ $day = 5; }
	    		else if( preg_match( '/(sa|sat|za|zat)/i', $day ) ){ $day = 6; }
	    		else if( ! is_integer( $day ) ){ $day = 0; }

	    	if( $first ){ $part[ 1 ] = $day; } else { $part[ 4 ] = $day; } $first = false; }

	    	/** Calculate seconds */

	    	$now = ( new \DateTime ) -> getTimestamp();
	    	$tsFrom = ( new \DateTime ) -> add( ( new \DateInterval( 'P' . $part[ 1 ] . 'DT' . $part[ 2 ] . 'H' . $part[ 3 ] . 'M' ) )) -> getTimestamp() - $now;
	    	$tsTo = ( new \DateTime ) -> add( ( new \DateInterval( 'P' . $part[ 4 ] . 'DT' . $part[ 5 ] . 'H' . $part[ 6 ] . 'M' ) )) -> getTimestamp() - $now;

	    	/** Reverse if From > To (i.e. mon-sun) */

	    	if( $tsFrom > $tsTo ){

	    		$tmp = $tsFrom; $tsFrom = $tsTo; $tsTo = $tmp; }

	    	/** POST */

	    	$this -> execute( 'service.addtimes', [

	    		'serviceid' => $sla -> serviceid,
	    		'type' => 0,
	    		'ts_from' => $tsFrom,
	    		'ts_to' => $tsTo ]); }

        /** Return */

        return [ 'host' => $host, 'sla' => $sla ]; }

    /**
     * Remove sync
     * @param  string $ip IP-address
     * @return null
     */
    
    public function unsync( $ip ){

		/** Validate IP */

		if( ! filter_var( $ip, FILTER_VALIDATE_IP ) ){

			new Error( printf( _( 'Not a valid IP address: "%s"' ), $ip ), 400 ); }

    	/** Remove host and SLA */

    	$sla = $this -> execute( 'service.get', [ 'filter' => [ 'parentids' => self::$serviceId ], 'search' => [ 'name' => $ip ] ] )[ 0 ] ?? null;
    	$sla = $sla ? $this -> execute( 'service.delete', [ $sla -> serviceid ?? 0 ] ) : false;
    	$host = $this -> execute( 'host.get', [ 'filter' => [ 'ip' => $ip ] ] )[ 0 ] ?? null;
    	$host = $host ? $this -> execute( 'host.delete', [ $host -> hostid ?? 0 ] ) : false;

    	/** Return */

    	return true; }

}