<?php
/**
 * Created by PhpStorm.
 * User: Alejandro
 * Date: 20/12/2020
 * Time: 23:47
 */

namespace Felican\Models\Mailing;


interface MailerInterface
{
    public function getMailsForDistribution();
    public function sendMail(string $subject, string $message, array $mailing_list): array;

}