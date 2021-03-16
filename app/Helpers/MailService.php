<?php


namespace App\Helpers;


use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;

class MailService
{
    public static function send($subject, $text, $to, $template, $attachments): int
    {
        $transport = (new Swift_SmtpTransport(config("custom.mailer_host"), config("custom.mailer_port"), config("custom.mailer_enc")))
            ->setUsername(config("custom.mailer_username"))
            ->setPassword(config("custom.mailer_pass"));

        $mailer = new Swift_Mailer($transport);


        $message = (new Swift_Message($subject))
            ->setfrom([env('MAIL_FROM_ADDRESS') => env('APP_NAME')])
            ->setTo($to)
            ->setreplyto(env('MAIL_FROM_ADDRESS'));

        $message->setBody(view($template, $text)->render(), 'text/html');
        return $mailer->send($message);
    }
}
