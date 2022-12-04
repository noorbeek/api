<?php

namespace CC\Api;

?>

<!doctype html>
<html>
<head>
	
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?= Options::get( 'api.name' ); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootswatch@4.5.2/dist/cyborg/bootstrap.min.css" integrity="sha384-nEnU7Ae+3lD52AK+RGNzgieBWMnEfgTbRHIwEvp1XXPdqdO6uLTd/NwXbzboqjc2" crossorigin="anonymous">
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous"/>

    <style type="text/css">
	
		body { background-color: #212121; color: #fff; }
		
		.Loading { position:fixed; top:0; right:0; bottom:0; left:0; background-color:rgba(255,255,255,.8); background-image:url(//preloaders.net/preloaders/712/Floating%20rays.gif); background-position:center center; background-repeat:no-repeat; display:none; z-index:999999; }
		
		select { height:40px; line-height:40px; }
		input { text-indent:10px; width:100%; height:40px; line-height:40px; border:none; }
		pre { border:none; background:transparent; }
		pre span { word-wrap: break-word; white-space: -moz-pre-wrap; white-space: pre-wrap; }

		i.fas { color: #fff; }

		a { color: #fff; }
		a:hover { color: #eee; }
		
		.Uri a { color: #8be9fd; }
		
		.string { color: #a5d6a7; }
		.number { color: #ffbd45; }
		.boolean { color: #ff844c; }
		.null { color: #ff79c6; }
		.key { color: #fff9c4; }

		input, select { background: rgba(0,0,0,0.2); color: #fff; }

		.btn-file {
		    position: relative;
		    overflow: hidden;
		    display: block;
		    background: rgba(0,0,0,0.2);
		    height:30px; border:none; width:100%;border-radius: 0;
		}
		.btn-file input[type=file] {
		    position: absolute;
		    top: 0;
		    right: 0;
		    min-width: 100%;
		    min-height: 100%;
		    font-size: 100px;
		    text-align: right;
		    filter: alpha(opacity=0);
		    opacity: 0;
		    outline: none;
		    background: rgba(0,0,0,0.3);
		    cursor: inherit;
		    display: block;
		}
		
    </style>
    
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
    
    <script>
        
		window.onerror = function( m,f,l ){
			
			$( '#Rsp' ).html( '<pre id="RspCode">' + Sandbox.Json({ 'Message' : m, 'File' : f, 'Line' : l }) + '</pre>' ); $( '.Loading' ).hide( ); return true; };
		  
        $.ajaxSetup({ crossDomain : true, beforeSend: function( ){ $( '.Loading' ).show( ); } });

        if( ! localStorage[ 'History' ] ){ localStorage[ 'History' ] = JSON.stringify([ ]); }
            
        var Sandbox = {
            
            Api : '',
            Headers : {

            	'X-Content-Type' : 'application/json',
            	'X-Content-Timezone' : 'Europe/Amsterdam',
            	'X-Content-Language':  'nl_NL' },
            
			setHeader : function( Header, Value ){ Sandbox.Headers[ Header ] = Value; },
			
			Go : function( Url ){ $( '#Uri' ).val( Url.replace( Sandbox.Api, '' ) ); $( '#Call' ).click( ); },
			
			Print : function( Response, Call, Xhr ){

				var Mime = Xhr && Xhr.getResponseHeader && Xhr.getResponseHeader( 'Content-Type' ) ? Xhr.getResponseHeader( 'Content-Type' ) : 'none';

				/** Detect Video */

				if( Mime.match( /^video\//i ) ){

					return $( '#Rsp' ).html( '<video controls="controls" width="100%"><source src="' + Call + '" type="' + Mime + '"></video>' );

				}

				/** Else */
				
				var Head = '', ContentType = $( '#ContentType' ).val( ) === 'application/xml' ? 'Xml' : 'Json',
					Response = ContentType === 'Xml' ? Response = Sandbox.XML( Response ) : Response = Sandbox.Json( Response );
					
				var Label = 'success'; var AlertCls = 'warning';
				
				if( Xhr.status > 299 ){ var Label = 'warning'; var AlertCls = 'warning'; }
				else if( Xhr.status > 399 ){ var Label = 'danger'; var AlertCls = 'danger'; }

				if( Xhr.responseJSON && Xhr.responseJSON.Meta ){
					
					var Status = '<span class="badge badge-' + Label + '">' + Xhr.status + '</span> ';
					var Alert = ''; if( Response.Errors ){ for( var i = 0; i < Response.Errors.length; i++ ){
						
						var Err = Response.Errors[ i ];
						var Code = ( ( Err.Code ) ? '<strong>' + Err.Code + ( ( Err.Description && Err.Description.Title ) ? ' ' + Err.Description.Title : '' ) + '</strong> ' : '' );
						var Description = ( Err.Description.Description ) ? Err.Description.Description : Err.Description;
						
						Alert += '<div class="alert alert-' + AlertCls + ' alert-dismissible" role="alert">';
							Alert += '<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>';
							Alert += Code + Description;
						Alert += '</div>';
						
					}}
					
					Head = 'Status: ' + Status + '<b>' + Call.replace( /^\//, '' ) + '</b>';
					Response = '<hr>' + Alert + '<pre id="RspCode">' + Response + '</pre>';
					
				} else if( Mime.match( /^image\//i ) ){
					
					Head = 'Status: <span class="badge badge-' + Label + '">' + Xhr.status + '</span> <b>' + Call.replace( /(^\/|\?.+$)/g, '' ) + '</b>';
					Response = '<hr><img src="' + Sandbox.Api + '/' + Call + '">';
					
				} else if( Mime.match( /^video\//i ) ){
					
					Head = 'Status: <span class="badge badge-' + Label + '">' + Xhr.status + '</span> <b>' + Call.replace( /(^\/|\?.+$)/g, '' ) + '</b>';
					Response = '<hr><video controls="controls"><source src="' + $( '#Uri' ).val( Url.replace( Sandbox.Api, '' ) ) + '" type="' + Mime + '"></video>';
					
				} else {
					
					Head = 'Status: <span class="badge badge-' + Label + '">' + Xhr.status + '</span> <b>' + Call.replace( /(^\/|\?.+$)/g, '' ) + '</b>';
					Response = '<hr><pre id="RspCode">' + Response + '</pre>';
					
				}

				/** Append headers */

				Head += '<small>'; for( var Header in Sandbox.Headers ){

					Head += '<br>' + Header + ': ' + Sandbox.Headers[ Header ]; } Head += '</small>';

				/** Write */
				
				$( '#Rsp' ).html( Head + Response.replace( /[\\]+/g, '\\' ).replace( /[\/]+/g, '/' ) );
				$( '#Rsp' ).find( '.Url' ).each( function( ){
					
					var Uri = $( this ).text( ).replace( /^https?\:\/\/[^\/]+/i, '' );

					$( this ).wrap( '<a href="#" onclick="Sandbox.Go( \''  + Uri + '\' );" />' );
					
				});
				
			},
			
			XML : function( xml ){
				
				var xml = new XMLSerializer( ).serializeToString( xml );
				var reg = /(>)\s*(<)(\/*)/g;
					xml = xml.replace(/\r|\n/g, '');
					xml = xml.replace(reg, '$1\r\n$2$3');
					
				return xml.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
				
			},
			
			Json : function( json ){
				
				json = JSON.stringify( json, undefined, 4 );
				json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
				json = json.replace(/\"(https?\:[^\"]+)\"/gi, '"<span class=\'Url\'>$1</span>"');
				
				return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function (match) {
					var cls = 'number';
					if (/^"/.test(match)) { if (/:$/.test(match)) { cls = 'key'; } else { cls = 'string'; } }
					else if (/true|false/.test(match)) { cls = 'boolean'; } 
					else if (/null/.test(match)) { cls = 'null'; }
					
					return '<span class="' + cls + '">' + match + '</span>';
					
				});
				
			},
			
            Call : function( Uri, DataParams, Method, Callback ){

            	var Files = document.getElementById( 'Uploads' ).files;

            	if( ! DataParams && Files.length ){
					
					var DataParams = new FormData( );
					$.each( Files, function( Key, Value ){ DataParams.append( Key, Value ); });
					$.each( Data.Get( ) ? Data.Get( ) : [], function( Key, Value ){ DataParams.append( Key, Value ); }); }

            	else if( ! DataParams && Data.Get( ) ){ DataParams = Data.Get( ); }

                if( Uri === null ){ var Uri = ''; } else { Uri = Uri.replace( /^\/+/, '' ); }
                if( Method == null || ! Method.match( /^(GET|PUT|DELETE|POST|OPTIONS)$/ ) ){ var Method = 'GET'; }
                if( ! Callback ){ var Callback = function( Rsp, Status, Xhr ){
                    
                    Sandbox.Print( Rsp, Uri, Xhr );

                    $( '.Loading' ).hide( );
                    $( '#UploadsReset' ).click( );
                        
                }}

                var History = JSON.parse( localStorage[ 'History' ] );
                var Arguments = [ Uri, DataParams, Method, Callback ]; if( Arguments[ 0 ] !== '' ){ Arguments[ 0 ] = '/' + Arguments[ 0 ].replace( '/(^\/|\/$)/', '' ); }

                $( '#Queries' ).html( '<option value="' + Arguments[ 0 ] + '">'); for( var i = 0; i < History.length; i++ ){
                	
                	if( Uri !== '' && History[ i ][ 0 ] === Arguments[ 0 ] ){ History.splice( History.indexOf( i ), 1 ); } else {

                		$( '#Queries' ).append( '<option value="' + History[ i ][ 0 ] + '">' );


                	}

                }

                History.unshift( Arguments );

                localStorage[ 'History' ] = JSON.stringify( History );

                Uri = Uri.replace( /(\?|\&)Jwt\=[^\&]+/i, '$1Jwt=' + encodeURIComponent( sessionStorage[ 'Authorization' ] ) );

				Sandbox.Headers[ 'X-Content-Language' ] = $( '#XContentLanguage' ).val( );
				Sandbox.Headers[ 'X-Content-Timezone' ] = $( '#XContentTimezone' ).val( );

                var ContentType = $( '#ContentType' ).val( ); if( ContentType.match( /csv|pdf/gi ) ){

                	return window.open( Sandbox.Api + '/' + Uri + ( Uri.match( /\?/g ) ? '&' : '?' ) + 'Jwt=' + encodeURIComponent( sessionStorage[ 'Authorization' ] ) + '&ContentType=' + ContentType ); }

                var AjaxParams = {
                    
                    url : Sandbox.Api + '/' + Uri,
                    headers : Sandbox.Headers,
                    data : DataParams,
                    method : Method,
                    success : Callback,
                    error : function( Rsp, Reason, Xhr ){
                        
                        Sandbox.Print( ( Rsp.responseJSON ) ? Rsp.responseJSON : Rsp, Uri, Rsp ); $( '.Loading' ).hide( );
                        
                    }
                    
                }; if( Files.length ){

                	AjaxParams.cache = false;
                	AjaxParams.contentType = false;
                	AjaxParams.processData = false;

                } console.log( AjaxParams ); $.ajax( AjaxParams );
                
            },
            
            Login : function( ){
                
                Sandbox.Call( $( '#AuthUri' ).val( ), { Username : $( '#Username' ).val( ), Password : $( '#Password' ).val( ) }, 'POST', function( Rsp ){
                    
                    sessionStorage[ 'Username' ] = $( '#Username' ).val( );

                    Sandbox.Print( Rsp, '/Auth', Rsp ); $( '.Loading' ).hide( );
					
					for( Key in Rsp.Items ){
						
						if( Rsp.Items.hasOwnProperty( Key ) ){
							
							sessionStorage[ Key ] = Rsp.Items[ Key ];
							
						}
						
					}
					
					Sandbox.setHeader( 'Authorization', 'Bearer ' + sessionStorage[ 'Authorization' ] );
					
				});
                
            },

            Uploads : function( Elm ){

            	var Html = '', Files = document.getElementById( 'Uploads' ).files;

            	if( Files && Files.length ){ $.each( Files, function( Key, Value ){

            		Html += '<div><small><b>' + Value.name + '</b></small></div>';

            	}); }

            	$( '#UploadsList' ).html( Html );

            }
            
        };

        var Data = {

        	Get : function( ){

        		var DataParams = null; $( '#Data' ).find( 'tr' ).each( function( ){ if( $( this ).find( '.Field' ).val( ) ){

        			DataParams = DataParams ? DataParams : { } ; DataParams[ $( this ).find( '.Field' ).val( ) ] = $( this ).find( '.Data' ).val( );

        		}}); return DataParams;

        	},

        	Add : function( ){

        		$( '#Data' ).append( '<tr><td style="padding-right:5px;"><input type="text" placeholder="Field" class="Field" style="margin-bottom:5px;"></td><td><input type="text" placeholder="Data" class="Data" style="margin-bottom:5px;"></td><td width="30px" style="text-align:center;"><i class="fas fa-trash-alt" onclick="Data.Remove( this );" style="cursor:pointer;"></i></td></tr>' );

        	},

        	Reset : function( ){ $( '#Data' ).html( '' ); Data.Add( ); },
        	Remove : function( Elm ){ $( Elm ).parentsUntil( 'tr' ).parent( ).remove( ); if( $( '#Data' ).find( 'tr' ).length === 0 ){ Data.Add( ); } }

        };
		
		$( document ).ready( function( e ){
            
            var History = JSON.parse( localStorage[ 'History' ] );

            for( var i = 0; i < History.length; i++ ){ $( '#Queries' ).append( '<option value="' + History[ i ][ 0 ] + '">' ); }

			if( sessionStorage[ 'Authorization' ] ){ Sandbox.setHeader( 'Authorization', 'Bearer ' + sessionStorage[ 'Authorization' ] ); }
			if( sessionStorage[ 'Username' ] ){ $( '#Username' ).val( sessionStorage[ 'Username' ] ); }
			
        });
    
    </script>

</head>
<body>

    <div class="navbar navbar-inverse navbar-fixed-top" role="navigation" style="background-color: <?= Options::get( 'api.color' ); ?>;">
        <div class="navbar-header">
          <a class="navbar-brand" style="color:#fff; font-weight:bold;"><?= Options::get( 'api.name' ); ?></a>
        </div>
        <ul class="nav navbar-nav">
            <li class="nav-item"><a class="nav-link" href="/docs" target="_blank">Docs</a></li>
	</ul><ul class="nav navbar-nav">
            <?php if( Session::get( 'id' ) ){ ?><li class="nav-item"><a class="nav-link" href="/logoff">Logoff</a></li><?php } ?>
            <?php if( ! Session::get( 'id' ) ){ ?><li class="nav-item"><a class="nav-link" href="/login?redirect=GET/">Authenticate</a></li><?php } ?>
        </ul>
    </div><div class="NavCall navbar navbar-fixed-top" role="navigation" style="background:rgba(0,0,0,0.25); border:0;"><div style="margin:10px; width:100%;">
        
        <table cellspacing="0" cellpadding="0" width="100%"><tr>
        
        <td><div><input type="text" list="Queries" id="Uri" value="/" placeholder="/Auth/Login/" onkeyup="if( event.keyCode === 13 ){ $( '#Call' ).click( ); }"><datalist id="Queries"></datalist></div></td>
        <td width="40px">
        	
        	<button id="Call" style="height:40px; border:none; width:100%; background-color: <?= Options::get( 'api.color' ); ?>;" onclick="Sandbox.Call( $( '#Uri' ).val( ), null, $( '#Method' ).val( ) );">
            	<i class="fas fa-search"></i></button>
        
        </td>
        
        </tr></table>
        
    </div></div>
    
    <div style="position:fixed; top:125px; bottom:0; left:0; width:350px; overflow:auto;"><div style="margin:20px;">
    	
    	<p><b>Method:</b></p>
        
        <p><select id="Method" style="width:100%; border:none;">
            <option value="GET">GET</option>
            <option value="POST">POST</option>
            <option value="PUT">PUT</option>
            <option value="DELETE">DELETE</option>
            <option value="OPTIONS">OPTIONS</option>
        </select></p>
        
        <p><b>Content-Type:</b></p>
        
        <p><select id="ContentType" style="width:100%; border:none;" onchange="Sandbox.setHeader( 'X-Content-Type', this.value );">
            <option value="application/json">JSON</option>
            <option value="application/xml">XML</option>
            <option value="text/csv">CSV</option>
            <option value="application/pdf">PDF</option>
        </select></p>
        
        <p><b>Locale:</b></p>

        <p><input type="text" id="XContentLanguage" placeholder="nl_NL" value="nl_NL" onchange="Sandbox.setHeader( 'X-Content-Language', this.value ); window.localStorage[ 'X-Content-Language' ] = this.value;"></p><script type="text/javascript">
        	
        	if( window.localStorage[ 'X-Content-Language' ] ){

        		$( '#XContentLanguage' ).val( window.localStorage[ 'X-Content-Language' ] ); }


        </script>
        
        <p><b>Timezone:</b></p>

        <p><input type="text" id="XContentTimezone" placeholder="Europe/Amsterdam" value="Europe/Amsterdam" onchange="Sandbox.setHeader( 'X-Content-Timezone', this.value ); window.localStorage[ 'X-Content-Timezone' ] = this.value;"></p><script type="text/javascript">
        	
        	if( window.localStorage[ 'X-Content-Timezone' ] ){

        		$( '#XContentTimezone' ).val( window.localStorage[ 'X-Content-Timezone' ] ); }


        </script>

        <p><b>Data:</b>
        	<i class="fas fa-plus-square" onclick="Data.Add( );" style="cursor:pointer; float:right; font-size:small; margin:0 3px;"></i>
        	<i class="fas fa-trash-alt" onclick="Data.Reset( );" style="cursor:pointer; float:right; font-size:small; margin:0 3px;"></i>
        </p>

        <p><table cellspacing="0" cellpadding="0" width="100%"><tbody id="Data"><tr>

        	<td style="padding-right:5px;"><input type="text" placeholder="Field" class="Field" style="margin-bottom:5px;" onkeyup="if( event.keyCode === 13 ){ $( this ).parent( ).next( ).find( 'input' ).focus( ); }"></td>
        	<td><input type="text" placeholder="Data" class="Data" style="margin-bottom:5px;" onkeyup="if( event.keyCode === 13 ){ $( '#Call' ).click( ); }"></td>
        	<td width="30px" style="text-align:center;"><i class="fas fa-trash-alt" onclick="Data.Remove( this );" style="cursor:pointer;"></i></td>

        </tr></tbody></table></p>

        <p><b>Uploads:</b></p><form id="UploadsForm">

        <p><table cellspacing="0" cellpadding="0" width="100%"><tr>

        	<td><span class="btn btn-default btn-file" style="background: <?= Options::get( 'api.color' ); ?>; height: 40px;">Select<input type="file" id="Uploads" name="Uploads[]" multiple="multiple" onchange="Sandbox.Uploads( );"></span></td>
        	<td width="30px"><button type="reset" id="UploadsReset" style="height:40px; border:none; width:100%; background-color: transparent;" onclick="$( '#UploadsList' ).html( '' );">
        		<i class="fas fa-trash-alt"></i></button></td>

        </tr></table></p>
        
        </form>

        <hr><div id="UploadsList"></div><hr>
    
    </div></div>
    <div style="position:fixed; top:125px; bottom:0; left:350px; right:0; overflow:auto; border-left:2px solid rgba(0,0,0,0.2);"><div style="margin:50px;" id="Rsp"></div></div>
    
    <div class="Loading"></div>

</body>
</html>