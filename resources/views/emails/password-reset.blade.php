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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #1f2937;
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 50%, #1d4ed8 100%);
            min-height: 100vh;
        }
        .email-container {
            max-width: 650px;
            margin: 20px auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(59, 130, 246, 0.2);
            overflow: hidden;
            position: relative;
        }
        .header {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 50%, #06b6d4 100%);
            color: white;
            padding: 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: rotate 8s linear infinite;
        }

        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .logo {
            position: relative;
            z-index: 2;
        }

        .logo img {
            height: 80px;
            width: auto;
            display: block;
            margin: 0 auto 20px;
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.2));
        }

        .content {
            padding: 50px 40px;
            background: white;
        }
        .reset-card {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border: none;
            border-radius: 20px;
            padding: 30px;
            margin: 30px 0;
            text-align: center;
            box-shadow: 0 10px 25px rgba(245, 158, 11, 0.2);
            position: relative;
            overflow: hidden;
        }

        .reset-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #f59e0b, #d97706, #b45309);
        }
        .footer {
            background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
            color: white;
            padding: 40px;
            text-align: center;
            position: relative;
        }

        .footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        }

        .footer-link {
            color: #60a5fa !important;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .footer-link:hover {
            color: #93c5fd !important;
        }
        .action-button {
            display: inline-block;
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            color: white !important;
            text-decoration: none;
            padding: 18px 36px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 16px;
            margin: 20px 0;
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
            transition: all 0.3s ease;
            text-align: center;
            border: none;
            cursor: pointer;
        }

        .action-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(59, 130, 246, 0.6);
        }

        .alternative-section {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border-radius: 20px;
            padding: 30px;
            margin: 30px 0;
            border: none;
            box-shadow: 0 8px 25px rgba(14, 165, 233, 0.1);
            position: relative;
        }

        .alternative-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #0ea5e9, #06b6d4, #0891b2);
            border-radius: 20px 20px 0 0;
        }

        .token-display {
            background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
            color: white;
            padding: 20px;
            border-radius: 15px;
            font-family: 'Courier New', monospace;
            font-size: 18px;
            font-weight: 600;
            letter-spacing: 2px;
            text-align: center;
            margin: 15px 0;
            box-shadow: 0 8px 25px rgba(31, 41, 55, 0.3);
            border: 2px solid #4b5563;
        }

        @media (max-width: 600px) {
            .email-container { 
                margin: 10px;
                border-radius: 15px;
            }
            .header, .content, .footer { 
                padding: 25px; 
            }
        }
    </style>
</head>
<body>
<div class="email-container">
    <!-- Header -->
    <div class="header">
        <div class="logo">
            <img src="{{ asset('resources/assets/posu_logo.png') }}" alt="Echague Logo">
        </div>
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
           ">posumoms.netlify.app</a>
    </div>

    <!-- Content -->
    <div class="content">
        <div style="text-align: center; margin-bottom: 30px;">
            <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #3b82f6, #1e40af); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 36px; color: white; margin-bottom: 20px; box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3); line-height: 1; text-align: center;">
                🔐
            </div>
            <h1 style="color: #1e40af; margin: 0 0 10px 0; font-size: 32px; font-weight: 800; text-shadow: 0 2px 4px rgba(30, 64, 175, 0.2);">
                Password Reset Request
            </h1>
            <p style="color: #6b7280; font-size: 18px; margin: 0;">
                Secure password reset for your POSU account
            </p>
        </div>

        <div style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border-radius: 15px; padding: 25px; margin: 30px 0; text-align: center;">
            <p style="margin: 0; color: #1e40af; font-size: 16px; font-weight: 600;">
                Hello <strong>{{ $data['user_name'] ?? 'User' }}</strong>,<br>
                We received a request to reset your POSU Traffic Violation System password.
            </p>
        </div>

        <!-- Reset Card -->
        <div class="reset-card">
            <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #f59e0b, #d97706); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 28px; color: white; margin-bottom: 20px; box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3); line-height: 1; text-align: center;">
                🔑
            </div>
            <h2 style="font-size:28px; font-weight:800; color:#92400e; margin:0 0 15px 0; text-shadow: 0 2px 4px rgba(146, 64, 14, 0.2);">Reset Your Password</h2>
            <p style="margin:0; color:#92400e; font-weight:600; font-size: 16px;">Click the button below to create a new secure password for your account</p>
        </div>

        <!-- Reset Button -->
        <div style="text-align:center; margin:40px 0;">
            <a href="{{ $data['reset_url'] ?? '#' }}" class="action-button">
                🔐 Reset My Password
            </a>
        </div>

        <!-- Alternative Link & Token -->
        <div class="alternative-section">
            <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #0ea5e9, #06b6d4); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 24px; color: white; margin-bottom: 15px; line-height: 1; text-align: center;">
                🔗
            </div>
            <h3 style="color:#0369a1; margin:0 0 20px 0; font-size:20px; font-weight:700; text-shadow: 0 2px 4px rgba(3, 105, 161, 0.2);">Alternative Method</h3>
            <p style="color: #374151; font-weight: 500; margin-bottom: 20px;">If the button above doesn't work, copy and paste this link into your browser:</p>
            <div style="background:#ffffff; padding:20px; border-radius:12px; border:2px solid #e5e7eb; word-break:break-all; font-family:'Courier New', monospace; font-size:14px; color: #374151; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                {{ $data['reset_url'] ?? 'Reset URL here' }}
            </div>
            <p style="color: #374151; font-weight: 500; margin: 20px 0 15px 0;">Or use this reset token:</p>
            <div class="token-display">
                {{ $data['reset_token'] ?? 'RESET-TOKEN-HERE' }}
            </div>
        </div>

        <div style="background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%); border-radius: 15px; padding: 25px; margin: 30px 0; text-align: center; border: 2px solid #fecaca;">
            <div style="width: 50px; height: 50px; background: #dc2626; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 24px; color: white; margin-bottom: 15px; line-height: 1; text-align: center;">
                ⏰
            </div>
            <p style="margin: 0; color: #dc2626; font-size: 16px; font-weight: 600;">
                <strong>Important:</strong> This password reset link expires in 
                <strong>{{ $data['expires_in'] ?? '60 minutes' }}</strong>.
            </p>
            <p style="margin: 10px 0 0 0; color: #991b1b; font-size: 14px;">
                If you did not request this reset, please contact support immediately.
            </p>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p><strong>POSU Traffic Violation System</strong></p>
        <p>Municipality of Echague, Province of Isabela</p>
        <p><a href="https://posumoms.netlify.app" style="color:#ffffff !important; text-decoration:none;">posumoms.netlify.app</a></p>
        <div style="background: rgba(255, 255, 255, 0.1); padding: 20px; border-radius: 15px; margin-top: 20px; font-size: 13px; color: #d1d5db; line-height: 1.5;">
            <strong>Security Notice:</strong> This email contains sensitive account information. Keep it confidential. If you did not request this reset, delete this email and contact support immediately.
        </div>
    </div>
</div>
</body>
</html>
