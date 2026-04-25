<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;

    public function __construct($otp, $subject)
    {
        $this->otp = $otp;
        $this->subject = $subject;
    }

    # Mail subject method
    public function envelope(): \Illuminate\Mail\Mailables\Envelope
    {
        return new \Illuminate\Mail\Mailables\Envelope(
            subject: $this->subject ?? 'Your OTP Code',
        );
    }

    # Mail content method
    public function content(): \Illuminate\Mail\Mailables\Content
    {
        return new \Illuminate\Mail\Mailables\Content(
            htmlString: <<<HTML
            <html>
              <body style="margin:0; padding:0; background-color:#0f0f1a; font-family:Arial, Helvetica, sans-serif;">
                <table width="100%" cellpadding="0" cellspacing="0" style="padding:40px 0;">
                 <tr>
                    <td align="center">
                    <table width="500" cellpadding="0" cellspacing="0"
                        style="background:#17172b; border-radius:12px; padding:40px; box-shadow:0 0 20px rgba(140, 82, 255, 0.4);">

                    <tr>
                        <td align="center" style="color:#ffffff; font-size:24px; font-weight:bold; padding-bottom:20px;">
                          MPAudiodec
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="color:#c7c7ff; font-size:18px; padding-bottom:10px;">
                            OTP Verification
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="color:#9aa0ff; font-size:14px; padding-bottom:30px;">
                            Use the OTP below to complete your login.
                            This code will expire in 15 minutes.
                        </td>
                    </tr>

                    <tr>
                        <td align="center">
                            <div style="
                                background: linear-gradient(90deg, #7f5cff, #00d4ff);
                                padding: 2px;
                                border-radius: 10px;
                                display: inline-block;">

                            <div style="
                                background:#0f0f1a;
                                padding:15px 40px;
                                border-radius: 8px;
                                color:#ffffff;
                                font-size:32px;
                                letter-spacing:8px;
                                font-weight:bold;">
                                 {$this->otp}
                                </div>
                            </div>
                        </td>
                     </tr>

                        <tr>
                            <td align="center" style="color:#6c6cff; font-size:12px; padding-top:30px;">
                                If you did not request this code, please ignore this email.
                            </td>
                        </tr>
                     </table>
                    </td>
                </tr>
            </table>
          </body>
        </html>
        HTML,
        );
    }

    # Attachment method
    public function attachments(): array
    {
        return [];
    }
}

?>
