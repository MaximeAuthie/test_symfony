<?php

namespace App\Service;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Messagerie {
    public function sendMail(string $login, string $mdp, string $sendMail, string $subject, string $body) {
        //Load Composer's autoloader
        require '../vendor/autoload.php';

        //Create an instance; passing `true` enables exceptions
        $mail = new PHPMailer(true);

        try {
            //Server settings
            $mail->SMTPDebug =0;                      //Enable verbose debug output : permet de gérer le debogage -> mettre à 0 pour désactiver
            $mail->isSMTP();                                            //Send using SMTP : pour dire qu'on utilise un server SMTP
            $mail->Host       = 'smtp.hostinger.com';                   //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = $login;                                 //SMTP username
            $mail->Password   = $mdp;                                   //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
            $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $mail->setFrom($login, 'Administrateur');                   // Adresse de l'expéditeur + alias qui apparait dans la boite du destinataire
            $mail->addAddress($sendMail, 'Joe User');                   //Adresse mail du destinataire
            // $mail->addAddress('ellen@example.com');                     //Si on veut une autre adresse de destination
            // $mail->addReplyTo('info@example.com', 'Information');       //Si on veut une autre adresse de réponse de l'adresse d'envoi
            // $mail->addCC('cc@example.com');                             //Si on veut une autre adresse en copie
            // $mail->addBCC('bcc@example.com');                           //Si on veut une autre adresse en copie cachée

            //Attachments
            // $mail->addAttachment('/var/tmp/file.tar.gz');               //Pour ajouter des PJ
            // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');          //Pour ajouter des PJ

            //Content
            $mail->isHTML(true);                                                            //Set email format to HTML : voir https://www.alsacreations.com/tuto/lire/1533-Un-e-mail-en-HTML-responsive-multi-clients.html pour la mise en forme
            $mail->Subject = $subject;                                                      //Objet du mail
            $mail->Body    = $body;               
            // $mail->AltBody = 'This is the body in plain text for non-HTML mail clients'; //Pour faire le body si le client de messagerie n'accepte pas le html

            $mail->send();
            return 'Le mail a bien été envoyé';
        } catch (Exception $e) {
            return "Le mail n'a pas pu être envoyé : {$mail->ErrorInfo}";
        }
    }
}
?>