<?php
/*
  Twilio credentials TEMPLATE.
 
  Setup:
    1. Copy this file to `twilio_config.php` (same folder).
    2. Fill in your own values from https://console.twilio.com.
 
  twilio_config.php is gitignored, so your real secrets never get pushed.
 */
return [
    'account_sid' => 'YOUR_ACCOUNT_SID',
    'auth_token'  => 'YOUR_AUTH_TOKEN',
    'from_number' => '14155238886',   // Twilio WhatsApp sandbox number
    'to_number'   => '',              // number that joined the sandbox
    'content_sid' => '',              // optional Content Template SID
];
