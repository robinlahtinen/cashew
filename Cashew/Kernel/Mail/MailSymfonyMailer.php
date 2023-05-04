<?php


namespace Cashew\Kernel\Mail;

use Cashew\Kernel\Kernel;
use Cashew\Kernel\Log\Log;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

/**
 * Class MailSymfonyMailer
 * @package Cashew\Kernel\Mail
 */
class MailSymfonyMailer implements MailInterface {

    protected MailerInterface $mailer;

    /**
     * @var Email[] Email instances.
     */
    protected array $emails = [];

    public function __construct() {
        $config = Kernel::getInstance()->getConfig();

        $dsn = $config->getMailDriver();
        $dsn .= "://";

        if (!empty($config->getMailUsername())) {
            $dsn .= $config->getMailUsername();

            if (!empty($config->getMailPassword())) {
                $dsn .= ":" . $config->getMailPassword();
            }
        }

        $dsn .= "@" . $config->getMailHost();

        if (!empty($config->getMailPort())) {
            $dsn .= ":" . $config->getMailPort();
        }

        $transport = Transport::fromDsn($dsn);
        $mailer = new Mailer($transport);

        $this->setMailer($mailer);
    }

    public function queueEmail(string $from, array $to, string $subject = "", string $text = "", array $cc = [], array $bcc = [], string $replyTo = ""): void {
        $this->addEmail($this->doCreateEmail($from, $to, $subject, $text, $cc, $bcc, $replyTo));
    }

    /**
     * @param Email $email
     * @return void
     */
    protected function addEmail(Email $email): void {
        $emails = $this->getEmails();
        $emails[] = $email;
        $this->setEmails($emails);
    }

    /**
     * @return array
     */
    protected function getEmails(): array {
        return $this->emails;
    }

    /**
     * @param array $emails
     */
    protected function setEmails(array $emails): void {
        $this->emails = $emails;
    }

    /**
     * @param string $from
     * @param string[] $to
     * @param string $subject
     * @param string $text
     * @param string[] $cc
     * @param string[] $bcc
     * @param string $replyTo
     * @return Email
     */
    protected function doCreateEmail(string $from, array $to, string $subject = "", string $text = "", array $cc = [], array $bcc = [], string $replyTo = ""): Email {
        $email = (new Email())->from($from)->to(...$to)->subject($subject)->text($text)->cc(...$cc)->bcc(...$bcc);

        if (!empty($replyTo)) {
            $email->replyTo($replyTo);
        }

        return $email;
    }

    public function sendEmail(string $from, array $to, string $subject = "", string $text = "", array $cc = [], array $bcc = [], string $replyTo = ""): void {
        $this->doSendEmail($this->doCreateEmail($from, $to, $subject, $text, $cc, $bcc, $replyTo));
    }

    protected function doSendEmail(Email $email): void {
        if (Kernel::getInstance()->getConfig()->isMailEnabled()) {
            $this->getMailer()->send($email);
        }
    }

    /**
     * @return MailerInterface
     */
    protected function getMailer(): MailerInterface {
        return $this->mailer;
    }

    /**
     * @param MailerInterface $mailer
     */
    protected function setMailer(MailerInterface $mailer): void {
        $this->mailer = $mailer;
    }

    /**
     * @return void
     * @throws TransportExceptionInterface
     */
    public function sendEmails(): void {
        $mailer = $this->getMailer();
        $emails = $this->getEmails();

        $count = count($emails);

        foreach ($emails as $i => $email) {
            $i++;

            Log::info("Sending email {$i} of {$count}.");

            $this->doSendEmail($email);

            unset($emails[$i]);
        }

        $emails = array_values($emails);
        $this->setEmails($emails);
    }

}
