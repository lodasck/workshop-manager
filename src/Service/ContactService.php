<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class ContactService
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendEmail(string $to, string $subject, string $htmlContent): void
    {
        $email = (new Email())
            ->from('no-reply@example.com')
            ->to($to)
            ->subject($subject)
            ->html($htmlContent);

        $this->mailer->send($email);
    }
}
