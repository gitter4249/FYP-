<?php
require __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * @param string $to     
 * @param string $name     
 * @param string $subject  
 * @param string $body     
 * @param string $altBody  
 * @return bool            
 */
function sendYSAluminiumEmail($to, $name, $subject, $body = '', $altBody = '') {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'simhengping@gmail.com';
        $mail->Password   = 'umsk fyxz ngat jrlg';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom('simhengping@gmail.com', 'YS Aluminium');
        $mail->addAddress($to, $name);

        $mail->isHTML(true);
        $mail->Subject = $subject;

        if (empty($body)) {
            $imageUrl = 'https://s3.bmp.ovh/2026/04/20/Ydm3xxvR.jpg';
            $body = "
                <!DOCTYPE html>
                <html>
                <head><meta charset='UTF-8'></head>
                <body>
                    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;'>
                        <div style='text-align: center;'>
                            <img src='$imageUrl' alt='YS Aluminium' style='width: 80px;'>
                        </div>
                        <h2>Hello " . htmlspecialchars($name) . ",</h2>
                        <p>Thank you for choosing <strong>YS Aluminium</strong>. We are committed to providing high‑quality aluminium doors and windows that combine durability with elegant design.</p>
                        <p>If you have any questions or need assistance, please don't hesitate to contact our support team.</p>
                        <div style='margin-top: 30px; font-size: 0.8rem; color: #777; text-align: center; border-top: 1px solid #eee; padding-top: 15px;'>
                            &copy; " . date('Y') . " YS Aluminium Sdn Bhd. All rights reserved.<br>
                            This is an automated message, please do not reply.
                        </div>
                    </div>
                </body>
                </html>
            ";
            $altBody = "Hello $name,\n\nThank you for choosing YS Aluminium. We are committed to providing high‑quality aluminium doors and windows that combine durability with elegant design.\n\nIf you have any questions or need assistance, please contact our support team.\n\nYS Aluminium Team";
        }

        $mail->Body    = $body;
        $mail->AltBody = $altBody ?: strip_tags($body);

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("YS Aluminium email failed: {$mail->ErrorInfo}");
        return false;
    }
}
?>