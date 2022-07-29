<?php
return [


    'user_model' => App\Models\User::class,

    'ping_federate_url' => env('PING_FEDERATE_URL'),
    'ping_federate_client_id' => env('PING_FEDERATE_CLIENT_ID'),
    'ping_federate_secret' => env('PING_FEDERATE_SECRET'),
    'ping_federate_redirect_uri' => env('PING_FEDERATE_REDIRECT_URI')
];
