<?php
/**
 * Created by PhpStorm.
 * User: Alejandro
 * Date: 20/12/2020
 * Time: 23:54
 */

namespace Felican\Models\Mailing;

use const Felican\Framework\Mailing\FIRMA_EMAIL;
use RedBeanPHP\R as R;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
include('firma_email.php');

class Mailer implements MailerInterface
{
    private $mailer;
    private $notifications;
    private $firma;

    public function __construct(PHPMailer $mailer)
    {
        $this->mailer = $mailer;
        $this->notifications = [];
        $this->firma = FIRMA_EMAIL;
    }

    public function getMailsForDistribution()
    {
        $cliente_bean = R::load( 'cliente', 1 );
        return $cliente_bean;
       /* $qb = $this->DBHandler->createQueryBuilder();

        $qb->addSelect('email')
            ->from('cliente')
            ->where(
                $qb->expr()->like('email', ':filter')
            )
            ->orderBy('email', 'ASC')
            ->setParameter(':filter','%_@__%.__%');
        $stmt = $qb->execute();
        $rows = $stmt->fetchAll();

        $emails = [];
        foreach ($rows as $row) {
            $clean_address = explode(" ",trim($row['email']))[0];
            array_push($emails,$clean_address);
        }
        return $emails;
       */
    }

    public function sendMail(string $subject, string $message, array $mailing_list): array
    {
        //$this->mailer->Subject=$subject;
        $final_message = $message.$this->firma;
        //$this->mailer->msgHTML($final_message);

        $recipients = array("success"=>[],"failed"=>[]);

        $size = count($mailing_list);

        foreach ($mailing_list as $email) {
            try {
                //$this->mailer->addAddress($email);
                array_push($recipients['success'], $email);
            } catch (Exception $e) {
                //$notif = 'Invalid address skipped: ' . htmlspecialchars($email) . '<br>';
                //array_push($this->notifications,$notif);
                array_push($recipients['failed'], $email);
                continue;
            }
            try {
                //$this->mailer->send();
                array_push($recipients['success'], $email);
                //echo 'Message sent to :' . htmlspecialchars($email) . ')<br>';

            } catch (Exception $e) {
                array_push($recipients['failed'], $email);
                //echo 'Mailer Error (' . htmlspecialchars($email) . ') ' . $this->mailer->ErrorInfo . '<br>';
                //Reset the connection to abort sending this message
                //The loop will continue trying to send to the rest of the list
                //$this->mailer->getSMTPInstance()->reset();
            }
            //Clear all addresses and attachments for the next iteration
            //$this->mailer->clearAddresses();
            //$this->mailer->clearAttachments();
        }

        $recipients['success'] = array_unique($recipients['success']);
        $recipients['failed'] = array_unique($recipients['failed']);
        return $recipients;
    }

}