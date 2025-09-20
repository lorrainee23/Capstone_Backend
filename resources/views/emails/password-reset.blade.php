<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your POSU Account Password</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8fafc;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #1e40af, #3b82f6);
            color: white;
            padding: 30px 40px;
            text-align: center;
        }
        .logo img {
            height: 64px;
            width: auto;
            display: block;
            margin: 0 auto 12px;
        }
        .content {
            padding: 40px;
        }
        .reset-card {
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            border-left: 6px solid #f59e0b;
            border-radius: 12px;
            padding: 25px;
            margin: 25px 0;
            text-align: center;
        }
        .footer {
            background: #374151;
            color: white;
            padding: 30px 40px;
            text-align: center;
        }
        .footer-link {
            color: #ffffff !important;
            text-decoration: none;
        }
        @media (max-width: 600px) {
            .email-container { margin: 0; }
            .header, .content, .footer { padding: 20px; }
        }
    </style>
</head>
<body>
<div class="email-container">
    <!-- Header -->
    <div class="header">
        <h1>REPUBLIC OF THE PHILIPPINES</h1>
        <h2>Province of Isabela</h2>
        <h3>MUNICIPALITY OF ECHAGUE</h3>
        <a href="https://posumoms.netlify.app"
           style="
            display:inline-block;
            color: #ffffff !important;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            border:1px solid rgba(255,255,255,0.3);
            padding: 8px 16px;
            border-radius: 20px;
           ">www.posumoms.netlify.app</a>
    </div>

    <!-- Content -->
    <div class="content">
        <h2 style="color: #1f2937; margin: 0 0 20px 0;">Password Reset Request</h2>
        <p>Hello {{ $data['user_name'] ?? 'User' }},</p>
        <p>We received a request to reset your POSU Traffic Violation System password. If you made this request, follow the instructions below.</p>

        <!-- Reset Card -->
        <div class="reset-card">
            <div style="font-size:24px; font-weight:700; color:#92400e; margin-bottom:15px;">Reset Your Password</div>
            <p style="margin:0; color:#92400e; font-weight:500;">Click the button below to create a new password for your account</p>
        </div>

        <!-- Reset Button -->
        <div style="text-align:center; margin:30px 0;">
            <a href="{{ $data['reset_url'] ?? '#' }}"
               style="
                display:inline-block;
                background: linear-gradient(135deg, #1e40af, #3b82f6);
                color: #ffffff !important;
                text-decoration: none;
                padding: 16px 32px;
                border-radius: 12px;
                font-weight: 600;
                font-size: 16px;
                margin: 20px 0;
                box-shadow: 0 4px 16px rgba(59, 130, 246, 0.3);
               ">
               Reset My Password
            </a>
        </div>

        <!-- Alternative Link & Token -->
        <div style="background:#f0f9ff; border:1px solid #bae6fd; border-radius:12px; padding:25px; margin:25px 0;">
            <h4 style="color:#0369a1; margin:0 0 15px 0; font-size:16px; font-weight:600;">Alternative Method</h4>
            <p>If the button above doesn't work, copy and paste this link into your browser:</p>
            <div style="background:#ffffff; padding:15px; border-radius:8px; border:1px solid #cbd5e1; word-break:break-all; font-family:'Courier New', monospace; font-size:14px;">
                {{ $data['reset_url'] ?? 'Reset URL here' }}
            </div>
            <p>Or use this reset token:</p>
            <div style="background:#374151; color:white; padding:15px; border-radius:8px; font-family:'Courier New', monospace; font-size:18px; font-weight:600; letter-spacing:2px; text-align:center; margin:15px 0;">
                {{ $data['reset_token'] ?? 'RESET-TOKEN-HERE' }}
            </div>
        </div>

        <p style="color:#6b7280; font-size:14px;">This password reset link expires in <strong>{{ $data['expires_in'] ?? '60 minutes' }}</strong>. If you did not request this reset, contact support immediately.</p>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p><strong>POSU Traffic Violation System</strong></p>
        <p>Municipality of Echague, Province of Isabela</p>
        <p><a href="https://posumoms.netlify.app" style="color:#ffffff !important; text-decoration:none;">www.posumoms.netlify.app</a></p>
        <div style="font-size:12px; color:#9ca3af; margin-top:20px; line-height:1.4;">
            This email contains sensitive account information. Keep it confidential. If you did not request this reset, delete this email and contact support.
        </div>
    </div>
</div>
</body>
</html>
