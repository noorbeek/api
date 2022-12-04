<?php

/**
 * Office 365 Connector
 * @subpackage \Api\Notifications\Mail
 */

namespace CC\Api\Notifications;

    use CC\Api\Options;
    use CC\Api\Models;
    use CC\Api\Error;
    use CC\Api\Response;
    use CC\Api\Request;

	use \PHPMailer\PHPMailer\PHPMailer;
	use \PHPMailer\PHPMailer\Exception;

class Mail {

	/**
	 * Cache
	 */
	
	private $mail = null;
	private $mailbox = null;
	private $softFail = false;

	/**
	 * Log
	 */
	
	private static $log = [];

	/**
	 * Connect to SMTP mail
	 */
	
	public function __construct( $mailbox = null, $softFail = false ){

		/** Parse mailbox object */

		$this -> mailbox = $mailbox = array_merge([
		
			'outType' => 'php',
			'outHost' => 'localhost',
			'outPort' => 25,
			'outSsl' => false,
			'outUsername' => 'username@domain.org',
			'outPassword' => '**********',
			'outSendFrom' => 'username@domain.org'
		
		], $mailbox ?? Options::get( 'mailbox' ) ?? [] );

		/** Return if exists */

		if( $this -> mail ){

			return $this; }

		/** Set fail */

		$this -> softFail = $softFail;

		/** Switch types */

		$this -> mail = new PHPMailer(); switch( $mailbox[ 'outType' ] ){

			case 'SMTP':

				$this -> mail -> isSMTP( );
				$this -> mail -> SMTPDebug = Options::get( 'api.debug' ) ? 3 : 0;
				$this -> mail -> SMTPAuth = true;
				$this -> mail -> SMTPOptions = [ 'ssl' => [ 'verify_peer' => false ] ];
				$this -> mail -> SMTPSecure = $mailbox[ 'outSSL' ] ?? 'tls';

			break; }

		/** Try to send mail */

		try {

			$this -> mail -> Timeout = 10;
			$this -> mail -> Debugoutput = function( $message, $level ){ Response::log([ 'message' => $message, 'level' => $level ]); };
			$this -> mail -> Host = $mailbox[ 'outHost' ] ?? 'localhost';
			$this -> mail -> Port = $mailbox[ 'outPort' ] ??  25;
			$this -> mail -> Username = $mailbox[ 'outUsername' ] ?? '';
			$this -> mail -> Password = $mailbox[ 'outPassword' ] ?? ''; }

		/** Catch Exceptions */
		
		catch( phpmailerException $e ){

			return $softFail ? false : new Error( $e -> errorMessage( ), 500 ); }

		catch( Exception $e ){

			return $softFail ? false : new Error( $e -> getMessage( ), 500 ); }

		/** Return */

		return $this; }

	/**
	 * MailCode ussing Google's Maps Api
	 * @param object $options  Mail options
	 */
	
	public function Send( $options = [], $variables = [], $returnHtml = false ){

		/**
		 * 
		 * echo ( new Notifications\Mail ) -> Send([

            'to' => 'mike@thecodecrowd.nl',
            'subject' => '😡 Iemand is b00s',
            'description' => 'Een bepaalde mail omschrijving',
            'body' => '<!-- HTML5 Kitchen sink by @dbox -->
              <section>
                <article>
                  <p>This paragraph is nested inside an article. It contains many different, sometimes useful, <a href="https://www.w3schools.com/tags/">HTML5 tags</a>. Of course there are classics like <em>emphasis</em>, <strong>strong</strong>, and <small>small</small>        but there are many others as well. Hover the following text for abbreviation tag: <abbr title="abbreviation">abbr</abbr>. Similarly, you can use acronym tag like this: <acronym title="For The Win">ftw</acronym>. You can define <del>deleted text</del>        which often gets replaced with <ins>inserted</ins> text.</p>
                  <p>You can also use <kbd>keyboard text</kbd>, which sometimes is styled similarly to the <code>&lt;code&gt;</code> or <samp>samp</samp> tags. Even more specifically, there is a tag just for <var>variables</var>. Not to be mistaken with blockquotes below, the quote tag lets you denote something as <q>quoted text</q>. Lastly don\'t forget the sub (H<sub>2</sub>O) and sup (E = MC<sup>2</sup>) tags.</p>
                </article>
                <aside>This is an aside.</aside>
                <footer>This is footer for this section</footer>
              </section>',
            'action' => '#',
            'actionText' => 'Call to action',
            'signature' => true

        ]);
		 * 
		 */

		/** Set connection */

		if( ! $this -> mail ){

			$this -> __construct(); }

		/** Set mail options */

		$options = array_merge([

			'id' => null,
			'to' => [],
			'cc' => [],
			'bcc' => [],
			'priority' => 2,
			'subject' => '',
			'description' => '',
			'template' => Options::get( 'mail.template' ) ?? __DIR__ . '/../Templates/Notifications/mail.html',
			'attachments' => null,
			'body' => '',
			'signature' => false ], $options );

		/** Create ID section */

		$options[ 'body' ] = ( $options[ 'id' ] ? '<span style="opacity: 0; display: none; visibility: hidden; color: transparent;" id="' . $options[ 'id' ] . '">' . $options[ 'description' ] . '</span>' : '' ) . $options[ 'body' ];

		/** Get user account and organization */

		$account = ( new Models\Users ) -> getAccount();
		$organization = $variables[ 'organization' ] ?? $account[ 'organization' ] ?? [];

		/** Parse variables */

		$variables = array_merge([

			'body' => $options[ 'body' ],
			'subject' => $options[ 'subject' ],
			'description' => $options[ 'description' ],
			'body' => $options[ 'body' ],
			'action' => $options[ 'action' ] ?? Options::get( 'app.url' ) ?? Request::$url ?? null,
			'actionText' => $options[ 'actionText' ] ?? Options::get( 'app.name' ) ?? Options::get( 'api.name' ) ?? 'Openen',
			'signature' => $options[ 'signature' ] ? ( $account[ 'signature' ] ?? '' ) : '',
			'color' => $options[ 'color' ] ?? ( $organization[ 'color' ] ?? Options::get( 'api.color' ) ),
			'organizationName' => $organization[ 'name' ] ?? '',
			'organizationDescription' => $organization[ 'description' ] ?? '',
			'organizationWebsite' => $organization[ 'website' ] ?? '',
			'organizationPhoneNumber' => $organization[ 'phoneNumber' ] ?? '',
			'organizationLogo' => $organization[ 'logo' ] ?? '',
			'organizationAddress' => implode( ' ', [

				$organization[ 'street' ] ?? '',
				$organization[ 'houseNumber' ] ?? '',
				$organization[ 'houseNumberExtension' ] ?? '',
				$organization[ 'zipCode' ] ?? '',
				$organization[ 'city' ] ?? '' ]) ], $variables );

		/** Get HTML template */

        $templateDir = preg_replace('/\/[^\/]+$/', '', $options[ 'template' ]);
        $templateFile = preg_replace('/^.+\/([^\/]+)$/', '$1', $options[ 'template' ]);

		$body = ( new \Twig\Environment( new \Twig\Loader\FilesystemLoader( $templateDir ) ) )

			-> render( $templateFile, $variables );

		/** Return HTML */

		if( $returnHtml ){

			return $body; }

		/** Create Mail object */

		$mail = $this -> mail;

		/** Create message and add recipients */

		$mail -> CharSet = 'UTF-8';
		$mail -> setFrom( $this -> mailbox[ 'outSendFrom' ], $this -> mailbox[ 'name' ] ?? $this -> mailbox[ 'outSendFrom' ] ?? '' );
		$mail -> isHTML( true );
		$mail -> Subject = $variables[ 'subject' ];
		$mail -> Body = $body;

		/** Add attachments */

		if( $options[ 'attachments' ] ){

			foreach( is_array( $options[ 'attachments' ] ) ? $options[ 'attachments' ] : [ $options[ 'attachments' ] ] as $attachments ){

				$mail -> AddAttachment( $attachments ); }}

		/** Add recipients */

		$count = 0; foreach( is_array( $options[ 'to' ] ) ? $options[ 'to' ] : [ $options[ 'to' ] ] as $to ){

			$address = is_string( $to ) ? $to : ( $to[ 0 ] ?? '' ); if( filter_var( $address, FILTER_VALIDATE_EMAIL ) ){

				$mail -> addAddress( $address, is_string( $to ) ? $to : ( $to[ 1 ] ?? $address ) ); $count++; } }

		$countCcBcc = 0; foreach( is_array( $options[ 'cc' ] ) ? $options[ 'cc' ] : [ $options[ 'cc' ] ] as $cc ){

			$address = is_string( $cc ) ? $cc : ( $cc[ 0 ] ?? '' ); if( filter_var( $address, FILTER_VALIDATE_EMAIL ) ){

				$mail -> addAddress( $address, is_string( $cc ) ? $cc : ( $cc[ 1 ] ?? $address ) ); $countCcBcc++; } }

		foreach( is_array( $options[ 'bcc' ] ) ? $options[ 'bcc' ] : [ $options[ 'bcc' ] ] as $bcc ){

			$address = is_string( $bcc ) ? $bcc : ( $bcc[ 0 ] ?? '' ); if( filter_var( $address, FILTER_VALIDATE_EMAIL ) ){

				$mail -> addAddress( $address, is_string( $bcc ) ? $bcc : ( $bcc[ 1 ] ?? $address ) ); $countCcBcc++; } }

		/** Add TO if not present and cc/bcc is */

		if( ! $count && $countCcBcc ){

			$mail -> addAddress( $this -> mailbox[ 'outSendFrom' ], $this -> mailbox[ 'name' ] ); $count++; }

		/** Break if no recipients */

		if( ! $count ){

			return $this -> softFail ? false : new Error( _( 'No recipients found' ), 500 ); }

		/** Set priority */

		switch( $options[ 'priority' ] ){

			case 1:

				$mail -> Priority =  1;
				$mail -> AddCustomHeader( 'X-MSMail-priority: High' );
				$mail -> AddCustomHeader( 'Importance: High' ); break; }

		/** Send mail */

		return ! $mail -> send( ) ? ( $this -> softFail ? false : new Error([

			'description' => _( 'Failed to send mail' ),
			'error' => preg_split( '/\r\n/', $mail -> ErrorInfo ) ], 500 ) ) : true; }

}