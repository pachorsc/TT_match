<?php

return [

    /*
    |--------------------------------------------------------------------------
    | StatsTT API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the StatsTT data import integration.
    | StatsTT provides table tennis data via a SQL playground API.
    |
    */

    'api_base_url' => env('STATSTT_API_URL', 'https://tts-production-78d0.up.railway.app'),

    'daily_limit' => (int) env('STATSTT_DAILY_LIMIT', 10),

    'max_rows_per_query' => (int) env('STATSTT_MAX_ROWS', 20),

    'import_path' => storage_path('app/import/statstt'),

    /*
    |--------------------------------------------------------------------------
    | Token Storage
    |--------------------------------------------------------------------------
    |
    | Path where the Clerk JWT token is stored after login.
    |
    */

    'token_path' => storage_path('app/statstt_token.json'),

    /*
    |--------------------------------------------------------------------------
    | Field Mapping
    |--------------------------------------------------------------------------
    |
    | Maps StatsTT field names to Laravel model fields for each entity.
    |
    */

    'field_mapping' => [

        'player' => [
            'full_name' => ['first_name', 'last_name'],
            'country' => 'country',
            'country_code' => 'country_code',
            'playing_hand' => 'dominant_hand',
            'playing_style' => 'playing_style',
            'is_active' => null,
        ],

        'match' => [
            'player_a_id' => 'player_a_id',
            'player_b_id' => 'player_b_id',
            'winner_id' => 'winner_id',
            'score' => ['player_a_sets', 'player_b_sets'],
            'match_date' => 'match_date',
            'round' => 'round',
        ],

        'tournament' => [
            'name' => 'name',
            'location' => 'location',
            'country' => 'country',
            'country_code' => 'country_code',
            'start_date' => 'start_date',
            'end_date' => 'end_date',
            'category' => 'category',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Import Queries
    |--------------------------------------------------------------------------
    |
    | Predefined SQL queries for common imports.
    | :limit placeholder is replaced at runtime.
    |
    */

    'queries' => [

        'rankings_male' => 'SELECT * FROM player_rankings_male_active ORDER BY ranking LIMIT :limit',

        'rankings_female' => 'SELECT * FROM player_rankings_female_active ORDER BY ranking LIMIT :limit',

        'player_by_id' => 'SELECT * FROM players_basic WHERE id = :id',

        'players_by_ids' => 'SELECT * FROM players_basic WHERE id IN (:ids)',

        'matches_by_player' => 'SELECT * FROM matches_basic WHERE player_a_id = :player_id OR player_b_id = :player_id LIMIT :limit',

        'matches_by_tournament' => 'SELECT * FROM matches_basic WHERE event_id = :event_id LIMIT :limit',

        'events_by_ids' => 'SELECT * FROM events_basic WHERE id IN (:ids)',

        'games_by_match' => 'SELECT * FROM games_basic WHERE match_id = :match_id LIMIT :limit',

    ],

];
