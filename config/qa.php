<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Smoke test / dummy AI
    |--------------------------------------------------------------------------
    |
    | When true, the queue job calls GeminiService::dummyAnalyze() instead of
    | hitting the Gemini API. You can still force dummy mode when an API key
    | is missing.
    |
    */
    'use_dummy_ai' => env('QA_USE_DUMMY_AI', true),

    /*
    |--------------------------------------------------------------------------
    | Gemini API rate limit (queue middleware)
    |--------------------------------------------------------------------------
    |
    | Matches RateLimiter::for('gemini-api') in AppServiceProvider.
    |
    */
    'gemini_rate_per_minute' => (int) env('QA_GEMINI_RATE_PER_MINUTE', 15),

];
