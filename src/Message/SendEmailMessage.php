<?php

namespace App\Message;

/**
 * Почтовое сообщение
 */
final class SendEmailMessage
{
    private string $to;
    private string $subject;
    private string $template;
    private array $context;


    public function __construct(string $to, string $subject, string $template, array $context)
    {

        $this->to = $to;
        $this->subject = $subject;
        $this->template = $template;
        $this->context = $context;
    }

    /**
     * @return string
     */
    public function getTo(): string
    {
        return $this->to;
    }

    /**
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * @return string
     */
    public function getTemplate(): string
    {
        return $this->template;
    }

    /**
     * @return array
     */
    public function getContext(): array
    {
        return $this->context;
    }


}
