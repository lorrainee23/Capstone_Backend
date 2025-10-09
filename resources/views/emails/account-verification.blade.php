<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your POSU Account Email</title>
    <style>
        body { margin:0; padding:0; font-family: Arial, sans-serif; background:#f8fafc; color:#333; }
        .email-container { max-width:600px; margin:0 auto; background:#fff; box-shadow:0 4px 20px rgba(0,0,0,.08); }
        .header { background:linear-gradient(135deg,#1e40af,#3b82f6); color:#fff; padding:30px 40px; text-align:center; }
        .logo img { height:64px; width:auto; display:block; margin:0 auto 12px; }
        .website-link { color:#fff !important; text-decoration:none; font-size:14px; font-weight:500; border:1px solid rgba(255,255,255,.3); padding:8px 16px; border-radius:20px; display:inline-block; }
        .content { padding:40px; }
        .verify-card { background:#ecfeff; border-left:6px solid #06b6d4; border-radius:12px; padding:20px; margin:20px 0; }
        .primary-button { display:inline-block; background:#ffffff; color:#1e40af; text-decoration:none; padding:14px 28px; border-radius:12px; font-weight:600; box-shadow:0 4px 16px rgba(0,0,0,.1); border:2px solid #1e40af; }
        .footer { background:#374151; color:#fff; padding:30px 40px; text-align:center; }
        .footer a { color:#fff !important; text-decoration:none; }
    </style>
    </head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>REPUBLIC OF THE PHILIPPINES</h1>
            <h2>Province of Isabela</h2>
            <h3>MUNICIPALITY OF ECHAGUE</h3>
            <a href="https://posumoms.netlify.app" class="website-link">www.posumoms.netlify.app</a>
        </div>
        <div class="content">
            <h2 style="margin:0 0 12px 0; color:#111827;">Verify your email address</h2>
            <p>Hello {{ $data['full_name'] ?? $data['user_name'] ?? 'User' }}, please verify your email to activate your POSU account.</p>
            <div class="verify-card">
                <p style="margin:0 0 8px 0;">Click the button below to verify:</p>
                <div style="text-align:center; margin-top:10px;">
                    <a href="{{ $data['verification_url'] ?? '#' }}" class="primary-button">Verify Email</a>
                </div>
            </div>
            <p style="color:#6b7280; font-size:14px;">If you did not create an account, you can ignore this message.</p>
        </div>
        <div class="footer">
            <p><strong>POSU Traffic Violation System</strong></p>
            <p>Municipality of Echague, Province of Isabela</p>
            <p><a href="https://posumoms.netlify.app">www.posumoms.netlify.app</a></p>
        </div>
    </div>
</body>
</html>


