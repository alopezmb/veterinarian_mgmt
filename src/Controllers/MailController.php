<?php
/**
 * Created by PhpStorm.
 * User: Alejandro
 * Date: 20/12/2020
 * Time: 13:17
 */

namespace Felican\Controllers;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Felican\Configurations\Rendering\TemplateRenderer;
use Felican\Models\Mailing\MailerInterface;


class MailController
{
    private $templateRenderer;
    private $Mailer;


    public function __construct(TemplateRenderer $templateRenderer, MailerInterface $Mailer) {
        $this->templateRenderer = $templateRenderer;
        $this->Mailer = $Mailer;
    }

    public function showMailForm(): Response
    {
        $content = $this->templateRenderer->render('MailView.html.twig');
        return new Response($content);
    }

    public function sendMailToClients(Request $request):Response
    {
        //recojo info del POST y envío correo
        $subject = $request->get("subject");
        $message = $request->get("message");
        //$mailing_list=$this->Mailer->getMailsForDistribution();
        //$admin_emails;
        //envíamelo a mí primero para ver y saber cómo va
        //$recipients = $this->Mailer->sendMail($subject,$message,$admin_emails);
        //$recipients = $this->mailer->sendMail($subject,$message,$mailing_list);




        $content = $this->templateRenderer->render('MailView.html.twig',[
            'mailsent'=>true,
            'recipients'=>$admin_emails
        ]);
        return new Response($content);
    }

}