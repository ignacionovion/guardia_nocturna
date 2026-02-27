<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SystemNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $fromAddress,
        public string $fromName,
        public string $mailSubject,
        public array $lines,
        public array $attachments = [],
        public ?string $notificationType = null,
        public ?string $sourceLabel = null,
        public ?string $senderName = null,
        public ?string $senderEmail = null,
        public ?string $senderRole = null,
    ) {}

    public function build(): self
    {
        $mail = $this
            ->from($this->fromAddress, $this->fromName)
            ->subject($this->mailSubject)
            ->view('emails.system_notification');

        foreach ($this->attachments as $att) {
            if (!is_array($att)) {
                continue;
            }
            $data = $att['data'] ?? null;
            if (!is_string($data) || $data === '') {
                continue;
            }
            $mail->attachData(
                $data,
                (string) ($att['name'] ?? 'adjunto'),
                [
                    'mime' => (string) ($att['mime'] ?? 'application/octet-stream'),
                ]
            );
        }

        return $mail;
    }
}
