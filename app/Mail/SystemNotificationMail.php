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
        public ?string $notificationType = null,
        public ?string $sourceLabel = null,
        public ?string $senderName = null,
        public ?string $senderEmail = null,
        public ?string $senderRole = null,
    ) {}

    public function build(): self
    {
        return $this
            ->from($this->fromAddress, $this->fromName)
            ->subject($this->mailSubject)
            ->view('emails.system_notification');
    }
}
