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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Felican\Configurations\Rendering\TemplateRenderer;



class BackupController
{
    private $templateRenderer;
    private $client;
    private $service;
    private $oauthcreds;
    private $tokenPath;
    private $revisionFile;
    private $db;

    public function __construct(TemplateRenderer $templateRenderer) {
        $this->templateRenderer = $templateRenderer;
        $this->client = new \Google_Client;
        $this->oauthcreds = $_SERVER['DOCUMENT_ROOT'].'/backups/oauth-credentials.json';
        $this->tokenPath = $_SERVER['DOCUMENT_ROOT'].'/backups/token.json';
        $this->revisionFile =   $_SERVER['DOCUMENT_ROOT'].'/backups/registro.txt';
        $this->db = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/storage/felican.db');
    }

    public function showBackupForm(Request $request):Response
    {

        $oauth_credentials = $this->getOAuthCredentialsFile();

        //Calcula los dias que han pasado desde la ultima copia
        $date1 = trim(file_get_contents($this->revisionFile));
        $date2 = date('Y-m-d');
        $date1Timestamp = strtotime($date1);
        $date2Timestamp = strtotime($date2);
        $diff = abs($date2Timestamp - $date1Timestamp);
        $daysdiff = floor($diff / (60*60*24) );

        $authUrl = "";

        if( ($daysdiff < 2)){

            return new RedirectResponse('/home');
        }
        else{

            //Configuraciones necesarias
            $authUrl = $this->clientConfig();

            //entro aquí tras pinchar en Conectar con Google, me redirige Google con la redirect url puesta.
            $code = $request->get('code');
            if(isset($code)){
                $token = $this->client->fetchAccessTokenWithAuthCode($code);
                $this->client->setAccessToken($token);

                // Save the token to a file.
                if (!file_exists(dirname($this->tokenPath))) {
                    mkdir(dirname($this->tokenPath), 0700, true);
                }
                file_put_contents($this->tokenPath, json_encode($this->client->getAccessToken()));

                return new RedirectResponse('/backup');
            }

            $content = $this->templateRenderer->render('BackupView.html.twig',[
                'authUrl'=>$authUrl
            ]);
            return new Response($content);
        }


    }

    public function performDBUpload(Request $request):Response{

        $success = 0;

        //Configuraciones necesarias
        $this->clientConfig();

        if($this->client->getAccessToken()){
            // Upload felican db

            $file = new \Google_Service_Drive_DriveFile();
            $currentdate = join('',explode('-',date("Y-m-d")));
            $name="backup_".$currentdate.".db";
            $file->setName($name);

            //Google needs data upload to be a string not a file handler.
             $this->service->files->create(
                $file,
                array(
                    'data' => $this->db,
                    'mimeType' => 'application/octet-stream',
                    'uploadType' => 'media'
                )
            );

            // Print the names and IDs for up to 10 files.
            $optParams = array(
                //'pageSize' => 10,
                'fields' => 'nextPageToken, files(id, name, size)'
            );
            $results = $this->service->files->listFiles($optParams);

            if (count($results->getFiles()) != 0) {

                foreach ($results->getFiles() as $f) {
                    if($f->getName() == $file->getName() && $f->getSize() > 0 ){
                        $success = 1;
                    }
                }
                file_put_contents($this->revisionFile,date('Y-m-d') );
            }
        }


        $content = $this->templateRenderer->render('BackupView.html.twig',[
            'success'=>$success
        ]);
        return new Response($content);
    }


    private function clientConfig(){

        $oauth_credentials = $this->getOAuthCredentialsFile();

        $redirect_url = $redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/backup';

        $this->client->setAuthConfig($oauth_credentials);
        $this->client->setRedirectUri($redirect_url);
        $this->client->addScope("https://www.googleapis.com/auth/drive.file");
        $this->service = new \Google_Service_Drive($this->client);
        $authUrl = "";

        /************************************************
         * If we have a code back from the OAuth 2.0 flow,
         * we need to exchange that with the
         * Google_Client::fetchAccessTokenWithAuthCode()
         * function. We store the resultant access token
         * bundle in the session, and redirect to ourself.
         ************************************************/

        if (file_exists($this->tokenPath)) {
            $accessToken = json_decode(file_get_contents($this->tokenPath), true);
            $this->client->setAccessToken($accessToken);
        }

        if ($this->client->isAccessTokenExpired()) {
            // Refresh the token if possible, else fetch a new one.
            if ($this->client->getRefreshToken()) {
                $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
            } else {

                $authUrl = $this->client->createAuthUrl();
            }
        }

        return $authUrl;
    }
    private function getOAuthCredentialsFile()
    {
        // oauth2 creds

        if (file_exists($this->oauthcreds)) {
            return $this->oauthcreds;
        }

        return false;
    }

}