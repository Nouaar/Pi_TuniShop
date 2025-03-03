<?php
namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailService
{
    private $mailer;
    
    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendLikeNotification(string $adminEmail, string $blogTitle)
    {
        $email = (new Email())
            ->from('your_email@gmail.com')
            ->to($adminEmail)
            ->subject('Nouveau Like sur votre Blog')
            ->text("Un utilisateur a aimé l'article : $blogTitle");

        $this->mailer->send($email);
    }
}
