<?php
/**
 * Created by PhpStorm.
 * User: Alejandro
 * Date: 20/12/2020
 * Time: 23:12
 */

namespace Felican\Models\Mailing;

use PHPMailer\PHPMailer\PHPMailer;
#use Doctrine\DBAL\Connection;


class MailFactory
{

    public function create(): Mailer
    {

        $mailer = new PHPMailer(true);

        return new Mailer($mailer);
    }

}