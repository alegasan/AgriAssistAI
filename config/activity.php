<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Activity PII retention
    |--------------------------------------------------------------------------
    |
    | Activity logs may include personal data such as IP address and user agent.
    | Configure how long (in days) those fields may be retained before they are
    | deleted or anonymized.
    |
    | Lawful basis (GDPR): legitimate interests (security/auditing) and, where
    | applicable, compliance with legal obligations.
    |
    */

    // Number of days to retain ip_address + user_agent on Activity records.
    'pii_retention_days' => env('ACTIVITY_PII_RETENTION_DAYS', 30),

    // What to do when retention expires: 'anonymize' or 'delete'.
    'pii_retention_action' => env('ACTIVITY_PII_RETENTION_ACTION', 'anonymize'),
];
