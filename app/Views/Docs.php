<?php 

namespace CC\Api;

?>

<!DOCTYPE html><html lang="en"><head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    
    <link rel="icon" href="/favicon.png" sizes="32x32" />
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/swagger-ui-dist@4.0.0-beta.4/swagger-ui.css">

	<title><?= Options::get( 'api.name' ); ?></title>
    <style>
      html
      {
        box-sizing: border-box;
        overflow: -moz-scrollbars-vertical;
        overflow-y: scroll;
      }
      *,
      *:before,
      *:after
      {
        box-sizing: inherit;
      }

      body
      {
        margin:0;
        background: #fafafa;
      }

      .page-header { position: relative; background-size: cover; color: #fff; text-align: center; }
      .layer { position: absolute; top: 0; right: 0; bottom: 0; left: 0; }
    </style>
  </head>

  <body>

    <div id="swagger-ui"></div>
    <script src="https://unpkg.com/swagger-ui-dist@4.0.0-beta.4/swagger-ui-standalone-preset.js"></script>
    <script src="https://unpkg.com/swagger-ui-dist@4.0.0-beta.4/swagger-ui-bundle.js"></script>
    
    <script>
    window.onload = function() {
      
      const ui = SwaggerUIBundle({
        
        urls: [{url: window.location.protocol + "//" + window.location.host + "/docs/api", name: "API"}],
        dom_id: '#swagger-ui',
        deepLinking: true,
          presets: [
              SwaggerUIBundle.presets.apis,
              SwaggerUIStandalonePreset
          ],
          layout: "StandaloneLayout"
      });
      
      window.ui = ui;

      document.querySelector( '.topbar-wrapper a:first-child' ).innerHTML = '<?= Options::get( 'api.name' ); ?>';
    }
  </script><style type="text/css" media="screen">
    
    .topbar { background-color: <?= Options::get( 'api.color' ); ?> !important; }
    .topbar-wrapper a:first-child img { display: none; }
    .topbar-wrapper a:first-child { display: block; height: 40px; width: 200px; background-image: url('none'); background-size: auto 80%; background-repeat: no-repeat; background-position: left center; }

  </style>
  </body>
</html>