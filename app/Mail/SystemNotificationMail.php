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
    ) {}

    public function build(): self
    {
        return $this
            ->from($this->fromAddress, $this->fromName)
            ->subject($this->mailSubject)
            ->view('emails.system_notification');
    }
}
