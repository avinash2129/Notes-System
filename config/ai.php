<?php

return [
    'api_key' => env('OPENAI_API_KEY'),
    'model_embedding' => 'text-embedding-3-small',
    'model_summary' => 'gpt-3.5-turbo',
    'timeout' => 30,
];