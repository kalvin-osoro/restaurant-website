<?php
use App\Infrastructure\Notifications\Channels\EmailChannel;
use App\Infrastructure\Notifications\Channels\TwilioChannel;
return [
    'channels' => [
        'email' => ['enabled' => env('CONSULTATION_EMAIL_ENABLED', true), 'driver' => EmailChannel::class],
        'sms' => ['enabled' => env('CONSULTATION_SMS_ENABLED', true), 'driver' => TwilioChannel::class, 'from' => env('TWILIO_SMS_FROM')],
        'whatsapp' => ['enabled' => env('CONSULTATION_WHATSAPP_ENABLED', false), 'driver' => TwilioChannel::class, 'from' => env('TWILIO_WHATSAPP_FROM')],
    ],
    'twilio' => ['sid' => env('TWILIO_ACCOUNT_SID'), 'token' => env('TWILIO_AUTH_TOKEN'), 'whatsapp_template' => env('TWILIO_WHATSAPP_CONTENT_SID')],
];
