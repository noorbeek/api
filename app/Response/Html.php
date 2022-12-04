<?php

/**
 * 
 */

namespace CC\Api\Response;

    use CC\Api\Response;
    use CC\Api\Error;

class Html {

    /**
     * Parse array as CSV
     * @param array $response PHP array
     */
    
    public static function get( $response = [] ){

        /** Parse JSON */

        $json = Response\Json::get( $response );

        /** Parse HTML */

        $response = '<html><head>';

            $response .= '<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/highlight.js/10.7.2/styles/default.min.css">';
            $response .= '<script src="//cdnjs.cloudflare.com/ajax/libs/highlight.js/10.7.2/highlight.min.js"></script>';
            $response .= '<style type="text/css">

                html, body { background-color: #282a36; padding: 15px; }
                pre { border: none !important; }

                /* Dracula Theme v1.2.5
                 *
                 * https://github.com/dracula/highlightjs
                 *
                 * Copyright 2016-present, All rights reserved
                 *
                 * Code licensed under the MIT license
                 *
                 * @author Denis Ciccale <dciccale@gmail.com>
                 * @author Zeno Rocha <hi@zenorocha.com>
                 */

                .hljs {
                  display: block;
                  overflow-x: auto;
                  padding: 0.5em;
                  background: #282a36;
                }

                .hljs-built_in,
                .hljs-selector-tag,
                .hljs-section,
                .hljs-link {
                  color: #8be9fd;
                }

                .hljs-keyword {
                  color: #ff79c6;
                }

                .hljs,
                .hljs-subst {
                  color: #f8f8f2;
                }

                .hljs-title {
                  color: #50fa7b;
                }

                .hljs-string,
                .hljs-meta,
                .hljs-name,
                .hljs-type,
                .hljs-attr,
                .hljs-symbol,
                .hljs-bullet,
                .hljs-addition,
                .hljs-variable,
                .hljs-template-tag,
                .hljs-template-variable {
                  color: #f1fa8c;
                }

                .hljs-comment,
                .hljs-quote,
                .hljs-deletion {
                  color: #6272a4;
                }

                .hljs-keyword,
                .hljs-selector-tag,
                .hljs-literal,
                .hljs-title,
                .hljs-section,
                .hljs-doctag,
                .hljs-type,
                .hljs-name,
                .hljs-strong {
                  font-weight: bold;
                }

                .hljs-literal {
                  color: #e57373;
                }

                .hljs-number {
                  color: #bd93f9;
                }

                .hljs-emphasis {
                  font-style: italic;
                }


            </style>';

        $response .= '</head><body>';

            $response .= '<pre><code class="json">' . $json . '</code></pre>';
            $response .= '<script> hljs.highlightAll(); </script>';

        $response .= '</body></html>';

        /** Return */

        return $response; }

}

?>