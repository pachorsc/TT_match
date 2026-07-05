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

    /*
    |--------------------------------------------------------------------------
    | Field Mapping
    |--------------------------------------------------------------------------
    |
    | Maps ITTF field names to Laravel model fields for each entity.
    |
    */

    'field_mapping' => [

        'player' => [
            'ittf_id' => 'ittf_id',
            'name' => ['first_name', 'last_name'],
            'country_code' => 'country_code',
        ],

        'match' => [
            'player_a' => null,
            'player_b' => null,
            'score' => ['player_a_sets', 'player_b_sets'],
            'round' => 'round',
        ],

        'tournament' => [
            'name' => 'name',
        ],

    ],

];
