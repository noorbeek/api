<?php

namespace CC\Api;

use OzdemirBurak\Iris\Color;

/**
 * 
 * Constants
 * 
 */

$error = false;
$session = false;
$user = false;

/**
 *
 * Session STOP
 * 
 */

if( $_POST[ 'logoff' ] ?? null ){

	/** Stop & remove session */

	session_unset();
	$_SESSION = array();
	Session::stop();

	/** Redirect page */

	if( Request::data( 'redirect' ) ){

		header( 'Location:' . Request::data( 'redirect' ) ); die(); }

	/** Reload page */

	header( 'Refresh:0' ); die(); }

/**
 *
 * Session START
 * 
 */

else if( isset( $_SESSION[ 'authorization' ] ) ){

	$session = Session::resume( $_SESSION[ 'authorization' ] );
	$user = ( new Sql( 'main' ) ) -> select( 'users' ) -> where( 'id:' . $session[ 'user' ] ) -> execute( )[ 0 ] ?? false; }

/**
 *
 * Authenticate
 * 
 */

else if( ( $_POST[ 'username' ] ?? null ) && ( $_POST[ 'password' ] ?? null ) ){

	/** Get user */

	$user = ( new Models\Users ) -> authenticate( $_POST[ 'username' ], $_POST[ 'password' ], true ) ?? false;

	/** Start session */

	if( $user[ 'id' ] ?? false ){

		$session = Session::start( $user, [ 'type' => 'session' ]);

		/** Set PHP session */

		foreach( $session as $key => $value ){

			$_SESSION[ $key ] = $value; }

	/** Set error object & clear user */

	} else if( $user[ 'error' ] ?? false ){

		$error = $user; $user = false; } }

/**
 * 
 * Redirect
 * 
 */

if( $session && Request::data( 'redirect' ) ){

	Router::redirect( Request::data( 'redirect' ) ); die(); }

/**
 * 
 * Properties
 * 
 */

$primary = ( Color\Factory::init( Options::get( 'api.color' ) ) ) -> toRgb();
$primaryLight = ( new Color\Rgb( $primary ) ) -> lighten( 20 ) -> toRgb();
$primaryGlow = ( new Color\Rgb( $primary ) ) -> lighten( 40 ) -> toRgb();

?>

<!DOCTYPE html>
<html>
<head>
	<title><?= Options::get( 'api.name' ); ?></title>

	<style type="text/css" media="screen">

		@import url('https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');

		/* BASIC */

		html {
			background-color: <?= $primary; ?>;
		}

		body {
			font-family: "Montserrat", sans-serif;
			height: 100vh;
			background-color: <?= $primary; ?>;
			background-image: url( 'cdn/app-background.png' );
			background-size: cover;
			background-position: center center;
			margin: 0;
		}

		a {
			color: <?= $primary; ?>;
			display:inline-block;
			text-decoration: none;
			font-weight: 400;
		}

		h2 {
			text-align: center;
			font-size: 24px;
			font-weight: 800;
			text-transform: uppercase;
			display:inline-block;
			margin: 40px 8px 10px 8px; 
			color: #cccccc;
		}



		/* STRUCTURE */

		.wrapper {
			display: flex;
			align-items: center;
			flex-direction: column; 
			justify-content: center;
			width: 100%;
			min-height: 100%;
			padding: 20px;
		}

		#formContent {
			-webkit-border-radius: 10px 10px 10px 10px;
			border-radius: 10px 10px 10px 10px;
			background: #fff;
			padding: 30px;
			width: 90%;
			max-width: 450px;
			position: relative;
			padding: 0px;
			-webkit-box-shadow: 0 30px 60px 0 rgba(0,0,0,0.3);
			box-shadow: 0 30px 60px 0 rgba(0,0,0,0.3);
			text-align: center;
		}

		#formFooter {
			background-color: #f6f6f6;
			border-top: 1px solid #dce8f1;
			padding: 25px;
			text-align: center;
			-webkit-border-radius: 0 0 10px 10px;
			border-radius: 0 0 10px 10px;
		}

		#formError {
			color: #c62828;
			padding: 25px;
			text-align: center;
			-webkit-border-radius: 0 0 10px 10px;
			border-radius: 0 0 10px 10px;
		}



		/* TABS */

		h2.inactive {
			color: #cccccc;
		}

		h2.active {
			color: #0d0d0d;
			border-bottom: 2px solid <?= $primaryLight; ?>;
		}



		/* FORM TYPOGRAPHY*/

		input[type=button], input[type=submit], input[type=reset]  {
			background-color: <?= $primary; ?>;
			border: none;
			color: white;
			padding: 20px 32px;
			text-align: center;
			text-decoration: none;
			display: inline-block;
			text-transform: uppercase;
			font-size: 14px;
			font-weight: 600;
			width: 85%;
			-webkit-box-shadow: 0 10px 30px 0 <?= $primaryGlow; ?>
			box-shadow: 0 10px 30px 0 <?= $primaryGlow; ?>
			-webkit-border-radius: 5px 5px 5px 5px;
			border-radius: 5px 5px 5px 5px;
			margin: 5px 5px 35px 5px;
			-webkit-transition: all 0.3s ease-in-out;
			-moz-transition: all 0.3s ease-in-out;
			-ms-transition: all 0.3s ease-in-out;
			-o-transition: all 0.3s ease-in-out;
			transition: all 0.3s ease-in-out;
		}

		input[type=button]:hover, input[type=submit]:hover, input[type=reset]:hover  {
			background-color: <?= $primaryLight; ?>;
		}

		input[type=button]:active, input[type=submit]:active, input[type=reset]:active  {
			-moz-transform: scale(0.95);
			-webkit-transform: scale(0.95);
			-o-transform: scale(0.95);
			-ms-transform: scale(0.95);
			transform: scale(0.95);
		}

		input[type=text], input[type=password] {
			background-color: #f6f6f6;
			border: none;
			color: #0d0d0d;
			padding: 15px 32px;
			text-align: center;
			text-decoration: none;
			display: inline-block;
			font-size: 16px;
			margin: 5px;
			width: 85%;
			border: 2px solid #f6f6f6;
			-webkit-transition: all 0.5s ease-in-out;
			-moz-transition: all 0.5s ease-in-out;
			-ms-transition: all 0.5s ease-in-out;
			-o-transition: all 0.5s ease-in-out;
			transition: all 0.5s ease-in-out;
			-webkit-border-radius: 5px 5px 5px 5px;
			border-radius: 5px 5px 5px 5px;
		}

		input[type=text]:focus, input[type=password]:focus {
			background-color: #fff;
			border-bottom: 2px solid <?= $primaryLight; ?>;
		}

		input[type=text]:placeholder, input[type=password]:placeholder {
			color: #cccccc;
		}



		/* ANIMATIONS */

		/* Simple CSS3 Fade-in-down Animation */
		.fadeInDown {
			-webkit-animation-name: fadeInDown;
			animation-name: fadeInDown;
			-webkit-animation-duration: 1s;
			animation-duration: 1s;
			-webkit-animation-fill-mode: both;
			animation-fill-mode: both;
		}

		@-webkit-keyframes fadeInDown {
			0% {
				opacity: 0;
				-webkit-transform: translate3d(0, -100%, 0);
				transform: translate3d(0, -100%, 0);
			}
			100% {
				opacity: 1;
				-webkit-transform: none;
				transform: none;
			}
		}

		@keyframes fadeInDown {
			0% {
				opacity: 0;
				-webkit-transform: translate3d(0, -100%, 0);
				transform: translate3d(0, -100%, 0);
			}
			100% {
				opacity: 1;
				-webkit-transform: none;
				transform: none;
			}
		}

		/* Simple CSS3 Fade-in Animation */
		@-webkit-keyframes fadeIn { from { opacity:0; } to { opacity:1; } }
		@-moz-keyframes fadeIn { from { opacity:0; } to { opacity:1; } }
		@keyframes fadeIn { from { opacity:0; } to { opacity:1; } }

		.fadeIn {
			opacity:0;
			-webkit-animation:fadeIn ease-in 1;
			-moz-animation:fadeIn ease-in 1;
			animation:fadeIn ease-in 1;

			-webkit-animation-fill-mode:forwards;
			-moz-animation-fill-mode:forwards;
			animation-fill-mode:forwards;

			-webkit-animation-duration:1s;
			-moz-animation-duration:1s;
			animation-duration:1s;
		}

		.fadeIn.first {
			-webkit-animation-delay: 0.4s;
			-moz-animation-delay: 0.4s;
			animation-delay: 0.4s;
		}

		.fadeIn.second {
			-webkit-animation-delay: 0.6s;
			-moz-animation-delay: 0.6s;
			animation-delay: 0.6s;
		}

		.fadeIn.third {
			-webkit-animation-delay: 0.8s;
			-moz-animation-delay: 0.8s;
			animation-delay: 0.8s;
		}

		.fadeIn.fourth {
			-webkit-animation-delay: 1s;
			-moz-animation-delay: 1s;
			animation-delay: 1s;
		}

		/* Simple CSS3 Fade-in Animation */
		.underlineHover:after {
			display: block;
			left: 0;
			bottom: -10px;
			width: 0;
			height: 2px;
			background-color: <?= $primaryLight; ?>;
			content: "";
			transition: width 0.2s;
		}

		.underlineHover:hover {
			color: #0d0d0d;
		}

		.underlineHover:hover:after{
			width: 100%;
		}



		/* OTHERS */

		*:focus {
			outline: none;
		} 

		#icon {
			width:60%;
		}

		* {
			box-sizing: border-box;
		}

		.layer { position: absolute; top: 0; right: 0; bottom: 0; left: 0; }

	</style>
</head>
<body>

	<div class="layer" style="opacity: 0.8; background: linear-gradient(90deg, <?= $primary; ?>, <?= $primaryLight; ?>);"></div>

	<div class="wrapper <?php echo isset( $_POST ) ? '' : 'fadeInDown'; ?>">

		<div id="formContent">

			<!-- Title -->
			
			<h2 class="active"><img class="active" src="cdn/app-icon-purple.svg" style="width: 16px; height: 16px;"> <?= Options::get( 'api.name' ); ?> <?php echo $user ? $user[ 'name' ] : ''; ?></h2>

			<!-- Login Form -->

			<br><br><form action="<?= $session ? '/logoff' : '/login?redirect=GET/'; ?>" method="post"><?php if( ! $session ){ ?>

				<input type="text" id="username" class="<?php echo isset( $_POST ) ? '' : 'fadeIn'; ?> second" name="username" placeholder="anna.aardbei@organisatie.nl" value="<?= $_POST[ 'username' ] ?? '' ?>">
				<input type="password" id="password" class="<?php echo isset( $_POST ) ? '' : 'fadeIn'; ?> third" name="password" placeholder="************" value="<?= $_POST[ 'password' ] ?? '' ?>">

				<?php if( $error ){ ?><div id="formError">

					<b><?= $error[ 'code' ]; ?></b> <?= $error[ 'error' ]; ?>

				</div><?php } ?>

				<input type="submit" class="<?php echo isset( $_POST ) ? '' : 'fadeIn'; ?> fourth" value="<?= _( 'Login' ); ?>">

			<?php } else { ?>

				<input type="hidden" id="logoff" name="logoff" value="true">
				<br><input type="submit" class="fourth" value="<?= _( 'Logoff' ); ?>">

			<?php } ?></form>

			<!-- Reminder -->

			<div id="formFooter"><?php if( ! $session ){ ?>

				<a class="underlineHover" href="#"><?= _( 'Forgot password?' ); ?></a>

			<?php } else { ?>

				<?= _( 'You are authenticated' ); ?>

			<?php } ?></div>

		</div>
	
	</div>

</body>
</html>