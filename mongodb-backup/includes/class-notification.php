<?php 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if( !class_exists('Notification') ) {
    class Notification {

        function __construct() {

        }

        public static function send( $to, $subject, $body ) {

            $mail = new PHPMailer(true);

            try {
                $mail->SMTPDebug = 0;                                      
                $mail->isSMTP();                                           
                $mail->Host       = 'smtp.zoho.com';                   
                $mail->SMTPAuth   = true;                            
                $mail->Username   = 'dd.dev@zohomail.com';                
                $mail->Password   = 'c=kEU4ewKGrbvdh2';                       
                $mail->SMTPSecure = 'tls';                             
                $mail->Port       = 587; 
            
                $mail->setFrom('dd.dev@zohomail.com', 'Cluram DB Backup');          
                $mail->addAddress( $to );
                
                $mail->isHTML(true);                                 
                $mail->Subject = $subject;
                $mail->Body    = $body;
                $mail->AltBody = 'Body in plain text for non-HTML mail clients';
                $mail->send();
                echo "Mail has been sent successfully!";
            } catch (Exception $e) {
                echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }

        }

    }
}

?>