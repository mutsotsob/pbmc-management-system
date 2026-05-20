<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password</title>
    <style>
        body { margin: 0; padding: 0; background: #f3f4f6; font-family: Arial, Helvetica, sans-serif; color: #1f2937; }
        .wrapper { max-width: 600px; margin: 32px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
        .header { background: #f97316; padding: 28px 32px; }
        .header h1 { margin: 0; color: #ffffff; font-size: 20px; font-weight: 700; }
        .header p  { margin: 4px 0 0; color: #fff7ed; font-size: 13px; }
        .body { padding: 32px; }
        .greeting { font-size: 15px; color: #374151; margin: 0 0 16px; }
        .message { font-size: 14px; color: #6b7280; line-height: 1.6; margin: 0 0 28px; }
        .btn-wrap { text-align: center; margin-bottom: 28px; }
        .btn { display: inline-block; background: #f97316; color: #ffffff; text-decoration: none; font-size: 15px; font-weight: 700; padding: 14px 36px; border-radius: 8px; }
        .divider { border: none; border-top: 1px solid #e5e7eb; margin: 24px 0; }
        .fallback { font-size: 12px; color: #9ca3af; line-height: 1.6; }
        .fallback a { color: #f97316; word-break: break-all; }
        .expiry { display: inline-block; background: #fff7ed; border: 1px solid #fed7aa; color: #ea580c; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 6px; }
        .footer { background: #f9fafb; border-top: 1px solid #e5e7eb; padding: 16px 32px; text-align: center; font-size: 11px; color: #9ca3af; }
    </style>
</head>
<body>
<div class="wrapper">

    <div class="header">
        <h1>Password Reset Request</h1>
        <p>PBMC Processing Portal</p>
    </div>

    <div class="body">

        <p class="greeting">Hi {{ $name }},</p>

        <p class="message">
            We received a request to reset the password for your PBMC Portal account.
            Click the button below to choose a new password.
        </p>

        <div class="btn-wrap">
            <a href="{{ $resetUrl }}" class="btn">Reset My Password</a>
        </div>

        <p style="text-align:center; margin: 0 0 24px;">
            <span class="expiry">This link expires in {{ $expiresIn }} minutes</span>
        </p>

        <p class="message" style="margin:0;">
            If you did not request a password reset, no action is needed — your password will remain unchanged.
            Please ignore this email or contact your system administrator if you have concerns.
        </p>

        <hr class="divider">

        <p class="fallback">
            If the button above doesn't work, copy and paste this link into your browser:<br>
            <a href="{{ $resetUrl }}">{{ $resetUrl }}</a>
        </p>

    </div>

    <div class="footer">
        This is an automated notification from the PBMC Processing Portal.<br>
        Please do not reply to this email.
    </div>

</div>
</body>
</html>
