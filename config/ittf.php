<?php

return [

    /*
    |--------------------------------------------------------------------------
    | ITTF Portal Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the ITTF results portal data import integration.
    | Data is scraped from results.ittf.link and stored as JSON before
    | being imported into the database via an artisan command.
    |
    */

    'import_path' => storage_path('app/import/ittf'),

];
