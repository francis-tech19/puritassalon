<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Code - Purita's Beauty Lounge</title>
</head>
<body style="margin: 0; padding: 0; background-color: #FFF5F8; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #2D3748;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #FFF5F8; padding: 30px 15px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" style="max-width: 520px; background-color: #ffffff; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 25px rgba(122, 28, 73, 0.08); border: 1px solid #FCE7F3;" cellspacing="0" cellpadding="0">
                    <!-- Top Gradient Bar -->
                    <tr>
                        <td style="height: 6px; background: linear-gradient(90deg, #7A1C49 0%, #DB2777 50%, #D97706 100%);"></td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 35px; text-align: center;">
                            <!-- Logo / Badge -->
                            <div style="display: inline-block; width: 64px; height: 64px; line-height: 64px; border-radius: 18px; background-color: #FDF2F8; border: 1px solid #FBCFE8; color: #7A1C49; font-size: 28px; font-weight: 900; margin-bottom: 20px; font-family: Georgia, serif;">
                                P
                            </div>
                            
                            <h1 style="margin: 0 0 10px 0; color: #7A1C49; font-size: 24px; font-weight: 800; letter-spacing: -0.5px;">
                                Purita's Beauty Lounge
                            </h1>
                            <p style="margin: 0 0 25px 0; color: #4B5563; font-size: 15px; line-height: 1.5;">
                                Hello <strong>{{ $name ?? ($user->name ?? 'Valued Customer') }}</strong>,<br>
                                Thank you for registering! Please use the verification code below to confirm your email and activate your account.
                            </p>
                            
                            <!-- Verification Code Box -->
                            <div style="background-color: #FFF5F8; border: 2px dashed #F472B6; border-radius: 16px; padding: 22px 15px; margin: 25px 0; text-align: center;">
                                <div style="font-size: 12px; font-weight: 700; color: #9D174D; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 8px;">
                                    Your 6-Digit Code
                                </div>
                                <div style="font-size: 36px; font-weight: 900; letter-spacing: 8px; color: #7A1C49; font-family: 'Courier New', Courier, monospace;">
                                    {{ $code }}
                                </div>
                            </div>
                            
                            <!-- Timer Notice -->
                            <p style="margin: 0 0 20px 0; color: #6B7280; font-size: 13px; line-height: 1.5;">
                                ⏱️ This code will expire in <strong>10 minutes</strong>.
                            </p>
                            
                            <!-- Security notice -->
                            <div style="border-top: 1px solid #F3F4F6; padding-top: 20px; margin-top: 25px; text-align: left;">
                                <p style="margin: 0; color: #9CA3AF; font-size: 12px; line-height: 1.5;">
                                    If you did not request this verification code, please ignore this email. No changes will be made to your account.
                                </p>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #FDF2F8; padding: 18px 30px; text-align: center; border-top: 1px solid #FCE7F3;">
                            <p style="margin: 0; color: #9D174D; font-size: 12px; font-weight: 600;">
                                &copy; {{ date('Y') }} Purita's Beauty Lounge. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
