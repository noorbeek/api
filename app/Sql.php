<?php

/**
 * 
 */

namespace CC\Api;

class Sql {

	/** General */

    private static $operators = [

        '@' => 'REGEXP',
        '!@' => 'NOT REGEXP',
        '-:' => '<=',
        '*:' => '>=',
        '!~' => 'NOT LIKE',
        '*-' => 'BETWEEN',
        '-*' => 'NOT BETWEEN',
        '!' => '<>',
        ':' => '=',
        '~' => 'LIKE',
        '*' => '>',
        '-' => '<' ];

    /** Current */

    public $database = false;
    public $connection = false;
    public $model = 'null';
    public $models = [];
    public $tables = [];
    public $columns = [];

    /** Query */

    public $query = '';
    public $querySelect = '';
    public $queryJoin = '';
    public $queryInsert = '';
    public $queryUpdate = '';
    public $queryDelete = '';
    public $queryWhere = '';
    public $queryOrder = '';
    public $values = [];
    public $limit = 0;
    public $offset = 0;
    public $page = 1;

    /** Manual joins other databases */

    private $manualJoins = [];

    /** Options */

    private $allow = [];
    private $unrestrict = [];

    /**
     *
     * Constructor
     * 
     */
    
    function __construct( string $database, $allow = null ){

        /** Set database */

        $this -> database = $database;
        $this -> connection = Sql\Connections::connect( $database );

        /** Defaults */

        $this -> limit = Options::get( 'sql.limit.default' ) ? Options::get( 'sql.limit.default' ) : 50;

        /** Return */

        return $allow !== null ? $this -> allow( $allow ) : $this; }

    /**
     *
     * Query
     * 
     */

    public function allow( $allow ){

        /** Return if boolean */

        if( is_bool( $this -> allow ) ){

            return $this -> allow; }

        /** Set allowed functionalities */

        $this -> allow = is_bool( $allow ) ? $allow : 
        
            array_map( 'strtolower', array_merge( $this -> allow, $allow ?? [] ) );

        /** Return */

        return $this; }
    
    /** Unrestrict model restrictions*/

    public function unrestrict( $unrestrict ){

        /** Return if boolean */

        if( is_bool( $this -> unrestrict ) ){

            return $this -> unrestrict; }

        /** Set unrestricted functionalities */

        $this -> unrestrict = is_bool( $unrestrict ) ? $unrestrict : 
        
            array_map( 'strtolower', array_merge( $this -> unrestrict, $unrestrict ?? [] ) );

        /** Return */

        return $this; }

    /**
     * Check if operation is allowed
     */

    private function isAllowed( string $allowedOperation ){

        return is_bool( $this -> allow ) ? $this -> allow : in_array( $allowedOperation, $this -> allow ?? [] ); }

    /**
     * Check if operation is allowed
     */

    private function isUnrestricted( string $allowedField ){

        return is_bool( $this -> unrestrict ) ? $this -> unrestrict :  in_array( $allowedField, $this -> unrestrict ?? [] ); }

    /**
     * Parse value to PDO query
     * @param  mixed $value       field value
     * @param  object $fieldObject field object
     * @return String             PDO bind key
     */
    
    private function getValueFromField( $value, $fieldObject, $pdoDataType = null, $softFail = false, $raw = false ){

        /** Get PDO datatype */

        if( ! $pdoDataType ){

            $pdoDataType = \PDO::PARAM_STR; if( preg_match( '/int|float|hash/i', $fieldObject[ 'datatype' ] ?? '' ) ){

                $pdoDataType = \PDO::PARAM_INT; }

            if( preg_match( '/bool/i', $fieldObject[ 'datatype' ] ?? '' ) ){

                $pdoDataType = \PDO::PARAM_BOOL; }

            if( $value === null ){

                $pdoDataType = \PDO::PARAM_NULL; }}

        /** Convert to database value */

        if( preg_match( '/^array:?(.+)?/i', $fieldObject[ 'datatype' ] ?? '', $matches ) ){

            /** Make array */

            $values = is_array( $value ) ? $value : explode( ',', $value ); foreach( $values as &$arrayValue ){

                /** Perform datatype conversion on array value */

                switch( $matches[ 1 ] ?? 'mixed' ){

                    case 'hash': $arrayValue = ( int ) Cryptography::unhash( $arrayValue ); break;
                    default: $arrayValue = Datatypes::convert( $arrayValue, $matches[ 1 ] ?? 'mixed', $softFail ? true : false ); break; }}

            /** Implode to SQL value */

            $value = is_array( $values ) ? implode( ',', $values ) : $values; }

        /** Convert to database value */

        else if( ( $fieldObject[ 'datatype' ] ?? '' ) === 'switch' ){

            $switch = $fieldObject[ 'switch' ][ $value ] ?? null; if( ! $switch && ! $softFail ){

                new Error( sprintf( _( 'Value not allowed: "%s" for field "%s", possible options: "%s"' ), $value, $fieldObject[ 'key' ], implode( ',', array_keys( $fieldObject[ 'switch' ] ) ) ), 400 ); }}

        /** Default conversion */

        else { 

            $value = $raw ? $value : Datatypes::convert( $value, $fieldObject[ 'datatype' ] ?? 'mixed', $softFail ? true : false ); }

        /** Cache to PDO binds */

        $this -> values[] = [ ':f' . count( $this -> values ), $value, $pdoDataType ];

        /** Return key */

        return end( $this -> values )[ 0 ]; }

    /**
     * Parse model from string
     * @param  String $field Field
     * @return Object        Model class
     */
    
    private function getColumnFromField( string $field, $softFail = false ){

        /** Get Model */

        $Model = $this -> getModelFromField( $field, $softFail ); if( ! $Model && $softFail ){

            return false; }

        /** Parse string */

        if( preg_match( '/^([a-z]+)\.([a-z]+)$/i', $field, $fieldParts ) ){

            $field = strtolower( $fieldParts[ 2 ] ); }

        else { $field = strtolower( $field ); }

        /** Die if column does not exist */

        $fields = array_change_key_case( $Model::$fields, CASE_LOWER ); if( ! isset( $fields[ $field ] ) ){

            return $softFail ? false : new Error( sprintf( _( 'Invalid field "%s"' ), $field ), 400 ); }

        $Field = $fields[ $field ];
        $Field[ 'key' ] = $field;        
        
        /** Validate group of field */

        if( ! $this -> isUnrestricted( $field ) && array_key_exists( 'roles', $Field ) && ! Authorization::user( $Field[ 'roles' ] ?? [] ) ){

            return $softFail ? false : new Error( sprintf( _( 'Not authorized to view field: "%s"' ), $field ), 403 ); }

        if( ! $this -> isUnrestricted( $field ) && array_key_exists( 'scopes', $Field ) && ! Authorization::scope( $Field[ 'scopes' ], true ) ){

            return $softFail ? false : new Error( sprintf( _( 'Not authorized to view field: "%s"' ), $field ), 403 ); }

        /** Get model */

        return $Field; }

    /**
     * Parse model from string
     * @param  String $field Field
     * @return Object        Model class
     */
    
    private function getModelFromField( string $field, $softFail = false ){

        if( preg_match( '/^([a-z]+)\.([a-z]+|\*)$/i', $field, $fieldParts ) ){

            $modelName = $fieldParts[ 1 ]; }

        else { $modelName = $this -> model; }

        /** Check if name is alias */

        $alias = false; if( isset( $this -> tables[ strtolower( $modelName ) ] ) ){

            $alias = strtolower( $modelName ); $modelName = $this -> tables[ $alias ]; }

        /** Get model or Error */

        $Model = Models::get( $modelName, $softFail ); if( ! $Model && $softFail ){

            return false; }

        /** Cache model */

        if( ! in_array( $Model::$name, $this -> models ) ){

            $this -> models[] = $Model::$name; }

        /** Set alias */

        if( $alias ){

            $Model -> alias = $alias; }

        /** Return model */

        return $Model; }

    /**
     * Parse field to SQL
     * @param  string $query field
     * @return string        SQL-field
     */
    
    public function fieldToColumn( string $query ){

        /** Get model */

        $Model = $this -> getModelFromField( $query );
        $Field = $this -> getColumnFromField( $query );

        /** Standard column name */

        $table = $Model -> alias ?? $Model::$table;
        $column = $Field[ 'column' ];

        /** Replace table with alias IF alias is not itself a table */

        foreach( $this -> tables as $tableName => $tableAlias ){

            if( ! isset( $this -> tables[ $table ] ) && strtolower( $tableAlias ) === strtolower( $table ) ){

                $table = $tableName; } }

        /** Replace column with alias */

        foreach( $this -> columns as $columnName => $columnAlias ){

            if( strtolower( $columnAlias ) === strtolower( $column ) ){

                $column = $columnAlias; } }

        /** Write query part */

        return $table . '.' . $column; }

    /**
     * Parse query string
     * @param  string $query Query
     * @return string        Query
     */
    
    public function queryToSql( string $query, bool $softFail = false ) {

        /** Remove excessive spaces */

        $query = preg_replace( '/[\s]{2,}/', ' ', trim( $query ) );

        /** Get parentheses parts */

        $queryParts = []; $open = true; foreach( preg_split( '/\s*(\()\s*|\s*(\))\s*/', $query, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY ) as $queryPart ){

            /** Get parentheses */

            if( ! strlen( $queryPart ) ){

                $queryParts[] = $open ? '(' : ')'; $open = $open ? false : true; }

            /** Get AND/OR */

            else { foreach( preg_split( '/^|\s+(and)\s+|$|\s+|^(or)\s+|$/', $queryPart, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY ) as $subPart ){

                $queryParts[] = $subPart; }}}

        /** Parse fields */

        foreach( $queryParts as &$queryPart ){

            /** Remove excessive spaces */

            $queryPart = trim( $queryPart );

            /** 
             * 
             * Verify parse & sanitize fields
             * 
             */

            if( preg_match( '/^([a-z\.]+)\s*([\+\-\*\~\!\:\@]{1,2})\s*([^\(\)]+)$/i', $queryPart, $matches ) ){

                /** Get model */

                $Model = $this -> getModelFromField( $matches[ 1 ] );
                $Field = $this -> getColumnFromField( $matches[ 1 ] );

                /** Write query part */

                $queryPart = $this -> fieldToColumn( $matches[ 1 ] ) . ' ';

                /** 
                 * 
                 * Special operator/value combinations
                 * 
                 */
                
                /** Comparison when FIELD operator FIELD 
                 * @TODO not able to insert "value.subvalue" as string
                 */

                if( preg_match( '/^\s*([a-z\.]+)\s*$/i', strtolower( $matches[ 3 ] ) ) && $this -> getModelFromField( $matches[ 3 ], true ) && $this -> getColumnFromField( $matches[ 3 ], true ) ){

                    $secondModel = $this -> getModelFromField( $matches[ 3 ] );
                    $secondField = $this -> getColumnFromField( $matches[ 3 ] );

                    $queryPart .= self::$operators[ $matches[ 2 ] ] . ' ' . ( $secondModel -> alias ?? $secondModel::$table ) . '.' . $secondField[ 'column' ] . ' '; }
                
                /** NULL */

                else if( strtoupper( $matches[ 3 ] ) === 'NULL' && $matches[ 2 ] === ':' ){

                    $queryPart .= 'IS NULL '; }

                else if( strtoupper( $matches[ 3 ] ) === 'NULL' && $matches[ 2 ] === '!' ){

                    $queryPart .= 'IS NOT NULL '; }

                /** BETWEEN */

                else if( $matches[ 2 ] === '*-' || $matches[ 2 ] === '-*' ){

                    /** Validate values */

                    $values = explode( ',', $matches[ 3 ] ); if( ! count( $values ) ){

                        new Error( sprintf( _( 'Invalid WHERE clause: Field "%s"' ), $matches[ 3 ] ), 400 ); }

                    /** Parse values */

                    $queryPart .= self::$operators[ $matches[ 2 ] ] . ' ' . $this -> getValueFromField( $values[ 0 ], $Field, null, $softFail ) . ' AND ' . $this -> getValueFromField( $values[ 1 ], $Field, null, $softFail ); }

                /** REGEXP or LIKE */

                else if( $matches[ 3 ] && ( $matches[ 2 ] === '@' || $matches[ 2 ] === '!@' ) ){

                    /** Get value */

                    $column = $this -> getValueFromField( $matches[ 3 ], $Field, null, $softFail );

                    /** Find value from values and override with REGEX value */

                    foreach( $this -> values as &$arrayValue ){

                        if( $arrayValue[ 0 ] === $column ){

                            $arrayValue[ 1 ] = '^' .$arrayValue[ 1 ] . '$|^' .$arrayValue[ 1 ] . ',|,' .$arrayValue[ 1 ] . ',|,' .$arrayValue[ 1 ] . '$'; } }

                    /** Write */

                    $queryPart .= self::$operators[ preg_replace( '/~/', '@', $matches[ 2 ] ) ] . ' ' . $column; }

                /** LIKE */

                else if( $matches[ 3 ] && ( $matches[ 2 ] === '~' || $matches[ 2 ] === '!~' ) ){

                    /** Get value */

                    $column = $this -> getValueFromField( $matches[ 3 ], $Field, null, $softFail, true );

                    /** Write */

                    $queryPart .= self::$operators[ preg_replace( '/~/', '@', $matches[ 2 ] ) ] . ' ' . $column; }

                /** 
                 * 
                 * Standard operator/value combinations
                 * 
                 */

                else { $queryPart .= ( self::$operators[ $matches[ 2 ] ] ?? '=' ) . ' ' . $this -> getValueFromField( $matches[ 3 ], $Field, null, $softFail ); }

            /** 
             * 
             * Match AND/OR
             * 
             */

            } else if( preg_match( '/^and|or$/i', $queryPart ) ){

               $queryPart = strtoupper( $queryPart ); }

            /** 
             * Return false if error
             */

             else if( ! preg_match( '/^(\(|\)|and|or)$/i', $queryPart ) ){

                new Error( sprintf( _( 'Invalid WHERE clause: "%s"' ), $queryPart ), 400 ); }

        }

        return preg_replace( '/[\s]{2,}/', ' ', trim( implode( ' ', $queryParts ) ) );

    }

    /**
     * Parse PDO select 
     * @param  Object $Model Resource model
     * @return Object        Class instance
     */
    
    public function select( string $modelName, $fields = '*', $alias = false ){

        /** Set model */

        $Model = Models::get( $modelName ); $this -> model = $Model::$name;

        /** Set alias */

        $tableName = strtolower( $alias ? $alias : $Model::$table ); if( $alias && ! preg_match( '/^[a-z]+$/i', $alias ) ){

            new Error( sprintf( _( 'Only characters are allowed in aliases: "%s"' ), $alias ), 400 ); }

        $this -> tables[ $tableName ] = $Model::$table;

        /**
         * Wait before optional select joins to continue
         */

        if( ! $this -> querySelect ){

            $this -> querySelect = func_get_args(); return $this; }

        /** Set query */

        $this -> querySelect = 'SELECT ';

        /** Use parameter fields */

        if( $this -> isAllowed( 'select' ) && Request::parameters( 'select' ) ){

            $fields = trim( ( string ) Request::parameters( 'select' ) ); }

        /** Append ALL fields */

        if( $fields === '*' || ! $fields ){

            /** Get all fields */

            $parsed = []; foreach( $this -> tables as $tableAlias => $fieldModel ){

                /** Get model */

                $fieldModel = Models::get( $fieldModel );

                /** Parse fields */

                foreach( $fieldModel::$fields as $name => $field ){

                    $column = strtolower( $tableAlias . '.' . $field[ 'column' ] );
                    $this -> columns[ $column ] = strtolower( $tableAlias . $field[ 'column' ] );
                    $parsed[] = $column . ' AS ' . $this -> columns[ $column ]; } } }

        /** Append custom fields */

        else {

            /** Parse fields */

            $parsedFields = array_filter( is_string( $fields ) ? explode( ',', $fields ) : $fields );

            /** Add ID if missing */

            if( ! preg_match( '/(^id$|^id,|,id,|,id$)/i', implode( ',', $parsedFields ) ) ){

                $parsedFields[] = 'id'; }

            /** Enum */

            $parsed = []; foreach( $parsedFields as $fieldName ){

                /** Get model */

                $fieldModel = $this -> getModelFromField( $fieldName );

                /** Get table alias */

                $tableAlias = strtolower( $fieldModel -> alias ?? $fieldModel::$table );

                /** Parse fields */

                foreach( preg_match( '/\.\*$/', $fieldName ) ? $fieldModel::$fields : [ $this -> getColumnFromField( $fieldName ) ] as $name => $field ){

                    $column = strtolower( $tableAlias . '.' . $field[ 'column' ] );
                    $this -> columns[ $column ] = strtolower( $tableAlias . $field[ 'column' ] );
                    $parsed[] = $column . ' AS ' . $this -> columns[ $column ]; } } }

        /** Build query */

        $this -> querySelect .= implode( ', ', $parsed );

        /** Append table */

        $this -> querySelect .= ' FROM ' . $Model::$table . ' AS ' . $tableName . ' ';

        /** Return */

        return $this; }

    /**
     * Parse PDO select 
     * @param  Object $Model Resource model
     * @return Object        Class instance
     */
    
    public function join( string $modelName, string $query, $alias = false, $mode = 'LEFT' ){

        /** Only allow SELECT, UPDATE, DELETE */

        if( ! $this -> querySelect ){

            return $this; }

        $Model = Models::get( $modelName );
        $MainModel = Models::get( $this -> model );

        /** Set alias */

        $tableName = strtolower( $alias ? $alias : $Model::$table ); if( $alias && ! preg_match( '/^[a-z]+$/i', $alias ) ){

            new Error( sprintf( _( 'Only characters are allowed in aliases: "%s"' ), $alias ), 400 ); }

        /** Check for duplicates */

        if( preg_match( '/\sAS\s' . $tableName . '\s/i', $this -> queryJoin )){

            return $this; }

        /** Verify whether models are in same database */

        if( $Model::$database !== $MainModel::$database ){
            
            /** Remember manual join */

            $this -> manualJoins[] = [

                'model' => $Model,
                'field' => $tableName ];

            /** Continue */

            return $this; }

        /** Cache join */

        $this -> tables[ $tableName ] = $Model::$table;

        /** Set query */

        $this -> queryJoin .= $mode . ' JOIN ' . $Model::$table . ' AS ' . $tableName .  ' ON ' . $this -> queryToSql( $query ) . ' ';

        /** Return */

        return $this; }

    /** Short methods */

    public function innerJoin( string $modelName, string $query, $alias = false ){

        return $this -> join( $modelName, $query, $alias, 'INNER' ); }

    public function leftJoin( string $modelName, string $query, $alias = false ){

        return $this -> join( $modelName, $query, $alias, 'LEFT' ); }

    public function rightJoin( string $modelName, string $query, $alias = false ){

        return $this -> join( $modelName, $query, $alias, 'RIGHT' ); }

    /**
     * Parse PDO where clause from string
     * @param  String $string Where clause
     * @param  array  $object Options
     * @return object         output
     */
    
    public function where( string $query = '' ){

        /** Only allow SELECT, UPDATE, DELETE */

        if( ! $this -> querySelect && ! $this -> queryUpdate && ! $this -> queryDelete ){

            return $this; }

        /** Return if empty */

        $parsedQuery = $this -> queryToSql( $query, false ); if( ! $parsedQuery ){

            return $this; }

        /** Set query */

        $this -> queryWhere = ' ' . $this -> queryToSql( $query, false ) . ' ';

        /** Return */

        return $this; }

    /**
     * Order by
     * @param  string $query ORDER BY clause
     * @return object        output
     */
    
    public function order( string $query = '' ){

        /** Only allow SELECT */

        if( ! $this -> querySelect ){

            return $this; }

        /** Validate parts */

        $this -> queryOrder = ''; foreach( explode( ',', $query ) as $field ){

            /** validate part */

            if( ! preg_match( '/^\s*([a-z\.]+)\s*(asc|ascending|desc|descending|)$/i', $field, $matches ) ){

                new Error( _( 'SQL order by invalid: ' ) . $field, 400 ); }

            /** Parse query */

            $this -> queryOrder .= ( $this -> queryOrder ? ', ' : '' );

            /** Special ordering */

            $Field = $this -> getColumnFromField( strstr( $matches[ 1 ], '.' ) ? $matches[ 1 ] : $this -> fieldToColumn( $matches[ 1 ] ) );

            switch( $Field[ 'datatype' ] ?? 'mixed' ){

                case 'ip': $this -> queryOrder .= 'INET_ATON( ' . $this -> fieldToColumn( $matches[ 1 ] ) . ' )';  break;
            
            default: $this -> queryOrder .= $this -> fieldToColumn( $matches[ 1 ] ); break; }

            /** Order direction */

            $this -> queryOrder .= ( preg_match( '/desc/i', $matches[ 2 ] ) ? ' DESC' : ' ASC' ); }

        /** Return */

        $this -> queryOrder .= $this -> queryOrder ? ' ' : ''; return $this; }

    /**
     * INSERT
     * @param  string $modelName Model name
     * @param  array  $data      Column => Value
     * @return object            SQL
     */
    
    public function insert( string $modelName, array $data = [] ){

        /** Get model & columns */

        $Model = Models::get( $modelName ); 
        $modelColumns = array_change_key_case( $Model::$fields, CASE_LOWER );
        $columns = $values = [];

        /** Build query */

        $this -> model = $Model::$name;
        $this -> tables[] = $Model::$table;
        $this -> queryInsert = 'INSERT INTO ' . $Model::$table . ' ( '; 

        /** Enumerate fields */

        foreach( $data as $fieldName => $fieldValue ){

            /** Get column */

            if( ! isset( $modelColumns[ strtolower( $fieldName ) ] ) ){

                continue; }

            $field = $modelColumns[ strtolower( $fieldName ) ];

            /** Verify column */

            if( ! $this -> isUnrestricted( strtolower( $fieldName ) ) && ( $field[ 'readonly' ] ?? false ) ){

                continue; }

            /** Authorize */

            if( ! $this -> isUnrestricted( strtolower( $fieldName ) ) && ! Authorization::user( $field[ 'roles' ] ?? [], 'w', true ) ){

                continue; }

            if( ! $this -> isUnrestricted( strtolower( $fieldName ) ) && array_key_exists( 'scopes', $field ) && ! Authorization::scope( $field[ 'scopes' ], true ) ){

                continue; }

            /** Append values */

            $columns[] = $this -> columns[] = $field[ 'column' ];
            $values[] = $this -> getValueFromField( $fieldValue, $field ); }

        /** Die is empty */

        if( ! count( $columns ) ){

            new Error( _( 'No data received for INSERT statement' ), 400 ); }

        /** Check if all required fields are inserted */

        $required = []; foreach( $Model::$fields as $column => $properties ){

            if( ( $properties[ 'required' ] ?? false ) && ! in_array( $column, $columns ) ){

                $required[] = $column; }}

        if( count( $required ) ){

            new Error( sprintf( _( 'Not all required fields inserted: "%s"' ), implode( ', ', $required ) ), 400 ); }

        /** Write columns */

        $first = true; foreach( $columns as $column ){

            $this -> queryInsert .= ( $first ? '' : ', ' ) . $column . ''; $first = false; }

        /** Write values */

        $this -> queryInsert .= ' ) VALUES ( '; $first = true; foreach( $values as $value ){

            $this -> queryInsert .= ( $first ? '' : ', ' ) . $value . ''; $first = false; }

        /** Return */

        $this -> queryInsert .= ' )'; return $this; }

    /**
     * UPDATE
     * @param  string $modelName Model name
     * @param  array  $data      Column => Value
     * @return object            SQL
     */
    
    public function update( string $modelName, array $data = [] ){

        /** Get model & columns */

        $Model = Models::get( $modelName );
        $modelColumns = array_change_key_case( $Model::$fields, CASE_LOWER );
        $columns = $values = [];
        
        $this -> model = $Model::$name;
        $this -> tables[ $Model::$table ] = $Model::$table;

        /** Build query */

        $this -> queryUpdate = 'UPDATE ' . $Model::$table . ' SET '; 

        /** Enumerate fields */

        $first = true; foreach( $data as $fieldName => $fieldValue ){

            /** Get column */

            if( ! isset( $modelColumns[ strtolower( $fieldName ) ] ) ){

                continue; }

            /** Get column */

            $field = $modelColumns[ strtolower( $fieldName ) ];
            $this -> columns[ strtolower( $fieldName ) ] = strtolower( $fieldName );

            /** Verify column */

            if( ! $this -> isUnrestricted( strtolower( $fieldName ) ) && ( $field[ 'readonly' ] ?? false ) ){

                continue; }

            /** Authorize */

            if( ! $this -> isUnrestricted( strtolower( $fieldName ) ) && ! Authorization::user( $field[ 'roles' ] ?? [], 'w', true ) ){

                continue; }

            if( ! $this -> isUnrestricted( strtolower( $fieldName ) ) && array_key_exists( 'scopes', $field ) && ! Authorization::scope( $field[ 'scopes' ], true ) ){

                continue; }

            /** Write query */

            $this -> queryUpdate .= ( $first ? '' : ', ' ) . $field[ 'column' ] . ' = ' . $this -> getValueFromField( $fieldValue, $field );

        $first = false; }

        /** Die is empty */

        if( $first ){

            new Error( _( 'No data received for UPDATE statement' ), 400 ); }

        /** Return */

        $this -> queryUpdate .= ' '; return $this; }

    /**
     * DELETE
     * @param  string $modelName Model name
     * @return object            SQL
     */
    
    public function delete( string $modelName ){

        /** Get model & columns */

        $Model = Models::get( $modelName );
        $modelColumns = array_change_key_case( $Model::$fields, CASE_LOWER );
        $columns = $values = [];

        $this -> model = $Model::$name;
        $this -> tables[ $Model::$table ] = $Model::$table;

        /** Build query */

        $this -> queryDelete = 'DELETE FROM ' . $Model::$table . ' ';

        /** Return */

        return $this; }

    /**
     * LIMIT
     * @param  int         $limit  LIMIT
     * @param  int|integer $offset OFFSET
     * @return object              SQL
     */
    
    public function limit( int $limit = 0, int $offset = 0 ){

        $this -> limit = $limit ? $limit : $this -> limit;
        $this -> offset = $offset ? $offset : $this -> offset;

    return $this; }

    /**
     * OFFSET
     * @param  int         $offset  OFFSET
     * @return object              SQL
     */
    
    public function offset( int $offset ){

        $this -> offset = $offset;

    return $this; }

    /**
     * PAGE
     * @param  int         $page  PAGE
     * @return object              SQL
     */
    
    public function page( int $page ){

        $this -> page = $page;
        $this -> offset = $page > 1 ? ( $page - 1 ) * $this -> limit : 0;

    return $this; }

    /**
     * Build query
     * @return string SQL query
     */
    
    public function query(){

        /** Get main model */

        $Model = Models::get( $this -> model );

        /** 
         * 
         * Switch statements
         * 
         */

        $query = ''; if( $this -> querySelect ){

            /** JOIN (before SELECT * statement!) */

            if( $this -> isAllowed( 'join' ) && Request::join() ){

                /** Verify if join model is registered in main modal as resource */

                $joins = []; foreach( Request::join() as $join => $void ){

                    /** Skip duplicates */

                    if( in_array( $join, $joins ) ){

                        continue; }

                    /** Find rigth Model from column */

                    $joinColumn = null; foreach( $Model::$fields as $column => $field ){

                        if( $join === 'all' || strtolower( $column ) === strtolower( $join ) ){

                            /** Skip if not authorized */

                            if( ! $this -> isUnrestricted( strtolower( $column ) ) && ! Authorization::user( $field[ 'roles' ] ?? [], 'r', true ) ){

                                continue; }

                            if( ! $this -> isUnrestricted( strtolower( $column ) ) && array_key_exists( 'scopes', $field ) && ! Authorization::scope( $field[ 'scopes' ], true ) ){

                                continue; }

                            /** Add join */

                            $joins[] = $column; $joinColumn = array_merge([ 'key' => $column ], $field );
                            $joinModel = $joinColumn[ 'model' ] ?? null ? Models::get( $joinColumn[ 'model' ] ) : null;
                            $joinModel ? $this -> leftJoin( $joinModel::$name, $column . ':' . $column . '.' . $joinModel::$key, $column ) : false; }}}}

            /** SELECT */

            if( is_array( $this -> querySelect ) ){

                call_user_func_array([ $this, 'select' ], $this -> querySelect ); }

            /** Append query */

            $query .= $this -> querySelect;
            $query .= $this -> queryJoin; }

        /** INSERT */

        else if( $this -> queryInsert ){

            $query .= $this -> queryInsert; }

        /** UPDATE */

        else if( $this -> queryUpdate ){

            $query .= $this -> queryUpdate; }

        /** DELETE */

        else if( $this -> queryDelete ){

            $query .= $this -> queryDelete; }

        /**
         *
         * WHERE
         * 
         */
        
        if( $this -> querySelect || $this -> queryUpdate || $this -> queryDelete ){

            /** WHERE */

            if( $this -> isAllowed( 'where' ) && Request::parameters( 'where' ) ){

                /** Default */

                $where = $this -> queryToSql( ( string ) Request::parameters( 'where' ) );
                $this -> queryWhere = $this -> queryWhere ? '('. $this -> queryWhere . ') AND (' . $where . ')' : $where; }

            /** Search */

            if( $this -> isAllowed( 'search' ) && Request::parameters( 'q' ) ){

                /** Build search query: field1~q or field2~q ... */

                $search = ''; if( $this -> isAllowed( 'search' ) && Request::parameters( 'q' ) ){

                    foreach( $Model::$fields as $column => $field ){

                        /** Check if there is a value after datatype conversion */

                        $value = Datatypes::convert( Request::parameters( 'q' ), $field[ 'datatype' ] ?? 'mixed' );

                        /** Append to query */

                        if( $value && preg_match( '/str|arr|json|switch/i', $field[ 'datatype' ] ?? 'mixed' ) ){
                             
                             $search .= ( $search ? ' or ' : '' ) . $column . '~' . Request::parameters( 'q' ); }

                        if( $value && ! preg_match( '/bool|password|crypt/i', $field[ 'datatype' ] ?? 'mixed' ) ){

                            $search .= ( $search ? ' or ' : '' ) . $column . ':' . Request::parameters( 'q' ); }}}

                /** Concat */

                $this -> queryWhere = $search ? '('. $this -> queryToSql( $search, true ) . ') AND (' . $this -> queryWhere . ')' : $this -> queryWhere; }

            /** Append to query */

            $query .= $this -> queryWhere ? 'WHERE ' . $this -> queryWhere . ' ' : ''; }

        /**
         *
         * ORDER BY
         * 
         */

        if( $this -> querySelect ){

            if( $this -> isAllowed( 'order' ) && Request::parameters( 'order' ) ){

                $this -> order( ( string ) Request::parameters( 'order' ) ); $query .= 'ORDER BY ' . $this -> queryOrder; }

            else { $query .= $this -> queryOrder ? 'ORDER BY ' . $this -> queryOrder : ''; }}

        /**
         *
         * LIMIT, OFFSET
         * 
         */

        if( $this -> querySelect ){

            $this -> limit = $this -> isAllowed( 'page' ) && Request::parameters( 'limit' ) ? ( int ) Request::parameters( 'limit' ) : $this -> limit;
            $this -> offset = $this -> isAllowed( 'page' ) && Request::parameters( 'offset' ) ? ( int ) Request::parameters( 'offset' ) : $this -> offset;
 
            /** Override parameters */

            if( $this -> isAllowed( 'page' ) && ( int ) Request::parameters( 'page' ) ){

                $this -> page = ( int ) Request::parameters( 'page' );
                $this -> offset = $this -> page > 1 ? ( $this -> page - 1 ) * $this -> limit : 0; }

            /** Validate max limit */

            if( $this -> limit > Options::get( 'sql.limit.max' ) ){

                $this -> limit = Options::get( 'sql.limit.max' ); }

            if( $this -> limit < 0 ){

                new Error( sprintf( _( 'SQL limit %s invalid' ), $this -> limit ), 400 ); }

            if( $this -> offset < 0 ){

                new Error( sprintf( _( 'SQL offset %s invalid' ), $this -> offset ), 400 ); }

            /** Appen LIMIT/OFFSET */

            $query .= 'LIMIT ' . $this -> limit . ' OFFSET ' . $this -> offset . ' '; }

        /** 
         * Return
         */

        return preg_replace( '/[\s]{2,}/', ' ', trim( $query ) ); }


    /**
     * Count query results
     * @return number Count
     */
    
    public function count( $fullCount = false ){

        $id = count( $this -> columns ) ? array_key_first( $this -> columns ) : '*';

        /** Convert query to count */

        $query = preg_replace( '/SELECT\s.+\sFROM\s/i', 'SELECT count( ' . $id . ' ) FROM ', $this -> query() );

        /** Full count */

        if( $fullCount ){

            $query = preg_replace( '/\s(LIMIT|OFFSET|ORDER|GROUP)\s.+/i', '', $query ); }

        /** Execute */

        try {

            /** Set query */

            $pdo = $this -> connection -> prepare( $query );

            /** Bind values */

            foreach( $this -> values as $value ){

                if( preg_match( '/([^0-9a-z]+|^)' . $value[ 0 ] . '([^0-9a-z]+|$)/i', $query ) ){
    
                    $binds[] = $value; $pdo -> bindValue( $value[ 0 ], $value[ 1 ], $value[ 2 ] ); }}

            /** Log */

            Response::log([ 'count' => $this -> interpolateQuery( $query, $binds ?? [] ) ]);

            /** Execute */

            $pdo -> execute( );

        } catch( \PDOException $error ){

            new Error([ 'title' => 'SQL count error', 'description' => $error -> getMessage() ], 400 ); }

        /** Return results */

        return ( int ) $pdo -> fetchColumn(); }

    /**
     * Parse PDO where clause from string
     * @param  String $string Where clause
     * @param  array  $object Options
     * @return object         output
     */
    
    public function execute( $query = null ){

        /** Set model */

        $MainModel = Models::get( $this -> model );

        /** Get/build query */

        $query = $query ? $query : $this -> query();

        /** Execute */

        try {

            /**
             * Pagination
             */
            
            if( $this -> querySelect && $this -> isAllowed( 'page' ) ){

                $count = $this -> count( true );
                $pages = $count ? ceil( ( int ) $count / $this -> limit ) : 1;

                /** Build response */

                Response::setMetadata([ 'pagination' => [

                    'count' => $count,
                    'limit' => $this -> limit,
                    'offset' => $this -> offset,
                    'pages' => $pages,
                    'page' => $this -> page ]]); }

            /** 
             * Prepare query
             */

            $pdo = $this -> connection -> prepare( $query );

            /** Bind values */

            foreach( $this -> values as $value ){

                if( preg_match( '/([^0-9a-z]+|^)' . $value[ 0 ] . '([^0-9a-z]+|$)/i', $query ) ){
    
                    $binds[] = $value; $pdo -> bindValue( $value[ 0 ], $value[ 1 ], $value[ 2 ] ); }}

            /** Log data */

            Response::log([ 'query' => $this -> interpolateQuery( $query, $binds ?? [] ) ]);

            /** Execute */

            $pdo -> execute( );

        } catch( \PDOException $error ){

            new Error([ 'title' => 'SQL error', 'description' => $error -> getMessage() ], 400 ); }

        /**
         *
         * DELETE
         * 
         */
                
        if( $this -> queryDelete && ! $this -> queryWhere ){

            new Error( _( 'DELETE statement not allow without WHERE clause' ), 400 ); }

        /**
         * 
         * INSERT/UPDATE/DELETE
         * 
         */
        
        if( $this -> queryInsert || $this -> queryUpdate || $this -> queryDelete ){

            if( $this -> queryInsert ){

                /** Get ID */

                $id = $this -> connection -> lastInsertId(); $id = preg_match( '/^[\d]+$/', $id ) ? ( int ) $id : $id;

                /** Hash if required */

                if( ( $MainModel::$fields[ 'id' ][ 'datatype' ] ?? null ) === 'hash' ){

                    $id = Cryptography::hash( $id ); }}

            /** Return */

            return isset( $id ) ? [ 'id' => $id, 'count' => $pdo -> rowCount() ] : [ 'count' => $pdo -> rowCount() ]; }

        /**
         * 
         * SELECT: Convert recordset to (allowed) model
         * 
         */

        $records = []; foreach( $pdo -> fetchAll() as $record ){

            $recordIsMainModel = true; $new = [];

            /** Enumerate models */

            foreach( $this -> tables as $tableAlias => $modelName ){

                /** Enumerate model fields */

                $Model = Models::get( $modelName ); foreach( $Model::$fields as $name => $properties ){

                    $column = strtolower( $tableAlias . $properties[ 'column' ] );

                    /** Skip if column not present in record */

                    if( ! array_key_exists( $column, $record ) ){

                        continue; }

                    /** Authorize */

                    if( ! $this -> isUnrestricted( strtolower( $name ) ) && array_key_exists( 'roles', $properties ) && ! Authorization::user( $properties[ 'roles' ] ?? [], 'r', true ) ){

                        continue; }

                    if( ! $this -> isUnrestricted( strtolower( $name ) ) && array_key_exists( 'scopes', $properties ) && ! Authorization::scope( $properties[ 'scopes' ], true ) ){

                        continue; }

                    /** Convert to database value */

                    if( preg_match( '/^array:(.+)/i', $properties[ 'datatype' ] ?? '', $matches ) ){

                        /** Make array */

                        $values = Datatypes::convert( $record[ $column ], 'array', true ) ?? []; foreach( $values as &$arrayValue ){

                            /** Apply deeper datatype onversion */

                            switch( $matches[ 1 ] ){

                                case 'datetime': $arrayValue = $arrayValue ? ( new \DateTime( Datatypes::convert( $arrayValue, 'datetime', true ) ) ) -> format( \DateTime::ISO8601 ) : $arrayValue; break;
                                case 'crypt': $arrayValue = Datatypes::convert( Cryptography::decrypt( $arrayValue ), 'string', true ); break;
                                case 'hash': $arrayValue = Cryptography::hash( Datatypes::convert( $arrayValue, 'integer', true ) ); break;
                                case 'password': $arrayValue = Datatypes::convert( $arrayValue, 'string', true ); break;

                            default: $arrayValue = Datatypes::convert( $arrayValue, $properties[ 'datatype' ] ?? 'mixed', true ); break; } }

                        /** Return array */

                        $value = $values; }

                    /** Default conversion */

                    else {

                        switch( $properties[ 'datatype' ] ?? 'mixed' ){

                            case 'datetime': $value = ( new \DateTime( Datatypes::convert( $record[ $column ], 'datetime', true ) ) ) -> format( \DateTime::ISO8601 ); break;
                            case 'crypt': $value = Datatypes::convert( Cryptography::decrypt( $record[ $column ] ), 'string', true ); break;
                            case 'hash': $value = Cryptography::hash( Datatypes::convert( $record[ $column ], 'integer', true ) ); break;
                            case 'password': $value = Datatypes::convert( $record[ $column ], 'string', true ); break;
                            case 'json': $value = json_decode( $record[ $column ], true ); break;

                        default: $value = Datatypes::convert( $record[ $column ], $properties[ 'datatype' ] ?? 'mixed', true ); break; } }

                    /** Manual join */

                    foreach($this->manualJoins as $join){

                        if($value && is_string($value) && strtolower($name) === strtolower($join['field'])){

                            $value = method_exists($join[ 'model' ], 'get') ? $join[ 'model' ]->get($value) : $value; } }

                    /** Main model */

                    if( $recordIsMainModel === true ){

                        $new[ $name ] = $value; }

                    /** Sub model */

                    else if( array_key_exists( $column, $record ) ){

                        $subModel = lcfirst( $tableAlias === strtolower( $Model::$name ) ? $Model::$nameSingle : $tableAlias );

                        /** Find original camelCase record key */

                        foreach( $new as $originalKey => $originalProperties ){

                            if( strtolower( $originalKey ) === strtolower( $subModel ) ){

                                $subModel = $originalKey; break; } } 

                        /** Append to new record */

                        $new[ $subModel ] = isset( $new[ $subModel ] ) && is_array( $new[ $subModel ] ) ? $new[ $subModel ] : [];
                        $new[ $subModel ][ $name ] = $value; }

                /** Switch to sub model */

                } $recordIsMainModel = false; }

            /** Build tree */

            if( $this -> isAllowed( 'tree' ) && isset( $MainModel::$parent ) && array_key_exists( $MainModel::$key, $new ) && array_key_exists( $MainModel::$parent, $new ) ){

                /** Parents */

                if( Request::join( 'parents' ) ){

                    $new[ 'parents' ] = Models::getParents( $new[ $MainModel::$key ], [ 'model' => $MainModel::$name, 'parent' => $MainModel::$parent ]); }

                /** Children */

                if( Request::join( 'children' ) ){

                    $new[ 'children' ] = Models::getChildren( $new[ $MainModel::$key ], [ 'model' => $MainModel::$name, 'parent' => $MainModel::$parent ]); }

                /** Siblings */

                if( Request::join( 'siblings' ) ){

                    $new[ 'siblings' ] = Models::getSiblings( $new[ $MainModel::$key ], $new[ $MainModel::$parent ], [ 'model' => $MainModel::$name, 'parent' => $MainModel::$parent ]); }}

            /** Add to records */

            if( $new ){

                $records[] = $new; }}

        /** Return data */

        return $records; }

    /**
     * Interpolate PDO query string
     *
     * @param string $query
     * @param array $binds
     * @return void
     */

    public function interpolateQuery( string $query, array $binds = [] ){

        /** Replace binds with values & add quotes */

        foreach( $binds ?? [] as $bind ){

            /** Parse value */

            $value = ( is_string( $bind[ 1 ] ) && $bind[ 2 ] > 1 ? "'" : '' ) . ( $bind[ 1 ] ? ( string ) $bind[ 1 ] : 'false' ) . ( is_string( $bind[ 1 ] ) && $bind[ 2 ] > 1 ? "'" : '' );

            /** Parse query */

            $query = preg_replace( '/\s' . $bind[ 0 ] . '(\s|[^\d]+|$)/', ' ' . $value . '$1', $query ); }

        /** Return */

        return $query; }
    
    /**
     * Execute raw SQL file or string
     */

    public function raw( string $sql ){ 
        
        /** Get data (if file) or DIE */

        if( preg_match( '/^[^\t\n\r]+\.sql$/i', $sql ) && @file_exists( $sql ) ){

            $sql = file_get_contents( $sql ); }

        else {
            
            new Error([ 'title' => 'SQL invalid', 'description' => $sql ], 500 ); }

        /** Execute SQL */

        try {

            $pdo = $this -> connection -> exec( $sql );

        } catch( \PDOException $error ){

            new Error([ 'title' => 'SQL error', 'description' => $error -> getMessage() ], 500 ); }

        /** Return */

        return $pdo;

    }
}
