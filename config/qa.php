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

    /*
    |--------------------------------------------------------------------------
    | Max plain-text length after HTML stripping (Gemini input)
    |--------------------------------------------------------------------------
    |
    | Prevents pathological pages from blowing token usage. Set to 0 to disable.
    |
    */
    'max_stripped_content_length' => (int) env('QA_MAX_STRIPPED_CONTENT_LENGTH', 65535),

    /*
    |--------------------------------------------------------------------------
    | Embedded HTTPS link probe (HEAD, unauthenticated)
    |--------------------------------------------------------------------------
    | Only failed probes (not HTTP 2xx reachable) are stored in qa_run_link_probes
    | and shown in CSV / result UI; successful URLs are not persisted.
    |
    */
    'link_probe_max_per_page' => (int) env('QA_LINK_PROBE_MAX_PER_PAGE', 30),

    'link_probe_concurrency' => (int) env('QA_LINK_PROBE_CONCURRENCY', 10),

    'link_probe_allow_insecure_http' => (bool) env('QA_LINK_PROBE_ALLOW_INSECURE_HTTP', false),

    /*
    |--------------------------------------------------------------------------
    | ProcessQA job timeout (seconds) — allow time for many link HEAD requests
    |--------------------------------------------------------------------------
    */
    'process_qa_timeout' => (int) env('QA_PROCESS_QA_TIMEOUT', 300),

];
