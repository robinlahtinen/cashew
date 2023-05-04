<?php


namespace Cashew\Kernel\Mail;

/**
 * Interface Mail
 * @package Cashew\Kernel\Mail
 * @author Robin Lahtinen
 */
interface MailInterface {

    public function __construct();

    public function queueEmail(string $from, array $to, string $subject = "", string $text = "", array $cc = [], array $bcc = [], string $replyTo = ""): void;

    public function sendEmail(string $from, array $to, string $subject = "", string $text = "", array $cc = [], array $bcc = [], string $replyTo = ""): void;

    public function sendEmails(): void;

}
