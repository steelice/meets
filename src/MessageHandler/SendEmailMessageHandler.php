<?php

namespace App\MessageHandler;

use App\Entity\MeetingComment;
use App\Message\SendEmailMessage;
use App\Repository\MeetingCommentRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Mime\Email;

final class SendEmailMessageHandler implements MessageHandlerInterface
{
    private MailerInterface $mailer;
    private string $fromAddress;

    public function __construct(string $fromAddress, MailerInterface $mailer)
    {
        $this->mailer = $mailer;
        $this->fromAddress = $fromAddress;
    }

    public function __invoke(SendEmailMessage $message)
    {
        $email = (new TemplatedEmail())
            ->from($this->fromAddress)
            ->to($message->getTo())
            ->subject($message->getSubject())
            ->htmlTemplate($message->getTemplate())
            ->context($message->getContext());

        $this->mailer->send($email);
    }
}
