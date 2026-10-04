<?php
namespace App\Infrastructure\Notifications\Channels;
use App\Domain\Consultations\Contracts\ConsultationChannel;
use App\Domain\Consultations\Data\NotificationRecipient;
use Illuminate\Support\Facades\Http;
use RuntimeException;
class TwilioChannel implements ConsultationChannel {
    public function send(NotificationRecipient $consultation, string $channel, string $idempotencyKey): ?string {
        $sid = config('consultations.twilio.sid'); $token = config('consultations.twilio.token');
        $from = config("consultations.channels.$channel.from");
        if (!$sid || !$token || !$from) throw new RuntimeException('Notification provider is not configured.');
        $payload = ['From' => $from, 'To' => $channel === 'whatsapp' ? 'whatsapp:'.$consultation->phone : $consultation->phone];
        if ($channel === 'whatsapp') {
            $template = config('consultations.twilio.whatsapp_template');
            if (!$template) throw new RuntimeException('WhatsApp template is not configured.');
            $payload += ['ContentSid' => $template, 'ContentVariables' => json_encode(['1' => $consultation->reference])];
        } else {
            $payload['Body'] = "Your consultation inquiry has been received. Reference: {$consultation->reference}. Our team will contact you to arrange a time.";
        }
        $response = Http::asForm()->withBasicAuth($sid, $token)->connectTimeout(5)->timeout(20)
            ->post('https://api.twilio.com/2010-04-01/Accounts/'.rawurlencode($sid).'/Messages.json', $payload)->throw();
        return $response->json('sid');
    }
}
