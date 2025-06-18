<?php

return [

    'paths' => ['api/*', 'login', '*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'], // o ['http://localhost:4200'] para más seguridad

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false, // cambia a true si usas cookies o auth por sesión

];
