<?php

namespace CC\Api;

?>

<!DOCTYPE html>
<html lang="en" >

<head>

  <meta charset="UTF-8">
  <title><?= Options::get( 'api.name' ); ?> - <?= $options[ 'title' ] ?></title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/5.0.0/normalize.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">

    <style type="text/css">

      body { font-family: 'Barlow Condensed', sans-serif; font-size: 18pt; text-align: center; color: #fff; }
      h1, h2, h3, h4, h5, h6 { font-weight: bold; text-transform: uppercase; }

    </style>

</head><body translate="no">

  <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;">

    <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:svgjs="http://svgjs.com/svgjs" width="100%" height="100%" preserveAspectRatio="none" viewBox="0 0 100% 100%">
      <g mask="url(&quot;#SvgjsMask1009&quot;)" fill="none">
          <rect width="100%" height="100%" x="0" y="0" fill="<?= $color; ?>" style="opacity: 0.9;"></rect>
          <path d="M65.909,322.1C98.968,321.867,127.997,301.961,144.566,273.353C161.177,244.672,163.654,209.765,147.837,180.639C131.281,150.152,100.583,129.618,65.909,128.513C29.242,127.344,-7.859,142.997,-25.593,175.112C-42.856,206.374,-33.562,244.156,-14.689,274.474C2.987,302.868,32.464,322.335,65.909,322.1" fill="<?= $color; ?>" class="triangle-float3"></path>
          <path d="M604.707,466.838C659.333,464.931,697.385,418.286,723.269,370.144C747.49,325.096,759.277,271.507,733.471,227.349C707.857,183.52,655.466,168.059,604.707,167.267C552.201,166.448,496.849,178.834,468.789,223.22C439.055,270.254,443.772,330.362,470.883,378.955C498.751,428.906,547.543,468.834,604.707,466.838" fill="<?= $color; ?>" class="triangle-float3"></path>
          <path d="M699.4649685732588 596.8255854920787L724.8379795270328 502.13221947159246 630.1446135065465 476.7592085178185 604.7716025527726 571.4525745383048z" fill="<?= $color; ?>" class="triangle-float3"></path>
          <path d="M351.20555515076103 341.1642632237863L241.48923861499205 205.4115687595857 184.3313706197973 346.8822564037962z" fill="<?= $color; ?>" class="triangle-float3"></path>
          <path d="M311.783,623.231C364.042,620.307,405.61,584.945,432.292,539.916C459.624,493.791,476.9,436.915,449.491,390.835C422.508,345.47,364.551,336.052,311.783,337.316C261.723,338.515,213,355.624,185.045,397.168C153.352,444.268,137.644,505.332,165.406,554.851C193.632,605.198,254.153,626.456,311.783,623.231" fill="<?= $color; ?>" class="triangle-float1"></path>
          <path d="M199.7615259137235 482.2486067516503L287.6800135621754 576.5296419385418 381.96104874906683 488.6111542900898 294.04256110061493 394.3301191031984z" fill="<?= $color; ?>" class="triangle-float1"></path>
      </g>
      <defs>
          <mask id="SvgjsMask1009">
              <rect width="100%" height="100%" fill="#ffffff"></rect>
          </mask>
          <style>
              @keyframes float1 {
                  0%{transform: translate(0, 0)}
                  50%{transform: translate(-10px, 0)}
                  100%{transform: translate(0, 0)}
              }

              .triangle-float1 {
                  animation: float1 5s infinite;
              }

              @keyframes float2 {
                  0%{transform: translate(0, 0)}
                  50%{transform: translate(-5px, -5px)}
                  100%{transform: translate(0, 0)}
              }

              .triangle-float2 {
                  animation: float2 4s infinite;
              }

              @keyframes float3 {
                  0%{transform: translate(0, 0)}
                  50%{transform: translate(0, -10px)}
                  100%{transform: translate(0, 0)}
              }

              .triangle-float3 {
                  animation: float3 6s infinite;
              }
          </style>
      </defs>
  </svg>

  </div><div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;"><div class="d-flex flex-column min-vh-100 justify-content-center align-items-center">

    <h1 style="margin-bottom: 50px;"><?= $options[ 'title' ] ?></h1>
    <p><?= $options[ 'message' ] ?></p>
    <?= isset( $options[ 'action' ] ) ? '<a href="' . $options[ 'actionUrl' ] . '" class="btn btn-primary">' . $options[ 'action' ] . '</a>' : '' ?>

  </div></div>

</body>
</html>