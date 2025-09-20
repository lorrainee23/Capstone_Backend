<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your POSU Account Email</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #1f2937;
            background: linear-gradient(135deg, #10b981 0%, #059669 50%, #047857 100%);
            min-height: 100vh;
        }

        .email-container {
            max-width: 650px;
            margin: 20px auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(16, 185, 129, 0.2);
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
            animation: sparkle 6s ease-in-out infinite;
        }

        @keyframes sparkle {
            0%, 100% { transform: scale(1) rotate(0deg); opacity: 0.5; }
            50% { transform: scale(1.2) rotate(180deg); opacity: 0.8; }
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

        .website-link {
            color: white !important;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            border: 2px solid rgba(255,255,255,0.3);
            padding: 12px 24px;
            border-radius: 25px;
            display: inline-block;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .content {
            padding: 50px 40px;
            background: white;
        }

        .verify-card {
            background: linear-gradient(135deg, #ecfeff 0%, #cffafe 100%);
            border: none;
            border-radius: 20px;
            padding: 30px;
            margin: 30px 0;
            text-align: center;
            box-shadow: 0 10px 25px rgba(6, 182, 212, 0.2);
            position: relative;
            overflow: hidden;
        }

        .verify-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #06b6d4, #0891b2, #0e7490);
        }

        .verify-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #06b6d4, #0891b2);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            color: white;
            margin-bottom: 20px;
            box-shadow: 0 8px 25px rgba(6, 182, 212, 0.3);
        }

        .primary-button {
            display: inline-block;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white !important;
            text-decoration: none;
            padding: 18px 36px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 16px;
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
            margin: 20px 0;
            transition: all 0.3s ease;
            text-align: center;
            border: none;
            cursor: pointer;
        }

        .primary-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(16, 185, 129, 0.6);
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

        .footer a {
            color: #60a5fa !important;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .footer a:hover {
            color: #93c5fd !important;
        }

        .security-notice {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border: none;
            border-radius: 20px;
            padding: 25px;
            margin: 30px 0;
            box-shadow: 0 8px 25px rgba(245, 158, 11, 0.15);
            position: relative;
        }

        .security-notice::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #f59e0b, #d97706, #b45309);
            border-radius: 20px 20px 0 0;
        }

        .security-notice h4 {
            margin: 0 0 15px 0;
            color: #92400e;
            font-size: 18px;
            font-weight: 700;
        }

        .security-notice p {
            margin: 0;
            color: #92400e;
            font-size: 14px;
            font-weight: 500;
            line-height: 1.6;
        }

        .alternative-link {
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
            padding: 20px;
            border-radius: 15px;
            word-break: break-all;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            color: #374151;
            margin: 15px 0;
            border: 2px solid #d1d5db;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        @media (max-width: 600px) {
            .email-container {
                margin: 10px;
                border-radius: 15px;
            }

            .header, .content, .footer {
                padding: 25px;
            }

            .verify-icon {
                width: 60px;
                height: 60px;
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="logo">
                <img src="{{ asset('resources/assets/posu_logo.png') }}" alt="Echague Logo">
            </div>
            <h1>REPUBLIC OF THE PHILIPPINES</h1>
            <h2>Province of Isabela</h2>
            <h3>MUNICIPALITY OF ECHAGUE</h3>
            <a href="https://posumoms.netlify.app" class="website-link">posumoms.netlify.app</a>
        </div>
        <div class="content">
            <div style="text-align: center; margin-bottom: 30px;">
                <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #10b981, #059669); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 36px; color: white; margin-bottom: 20px; box-shadow: 0 8px 25px rgba(16, 185, 129, 0.3);">
                    ✉️
                </div>
                <h1 style="color: #10b981; margin: 0 0 10px 0; font-size: 32px; font-weight: 800; text-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);">
                    Email Verification Required
                </h1>
                <p style="color: #6b7280; font-size: 18px; margin: 0;">
                    Complete your POSU account setup
                </p>
            </div>

            <div style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border-radius: 15px; padding: 25px; margin: 30px 0; text-align: center;">
                <p style="margin: 0; color: #059669; font-size: 16px; font-weight: 600;">
                    Hello <strong>{{ $data['full_name'] ?? $data['user_name'] ?? 'User' }}</strong>,<br>
                    Please verify your email address to activate your POSU account and complete your registration.
                </p>
            </div>
            
            <div class="verify-card">
                <div class="verify-icon">📧</div>
                <h2 style="margin:0 0 15px 0; color:#0369a1; font-size: 24px; font-weight: 700; text-shadow: 0 2px 4px rgba(3, 105, 161, 0.2);">Email Verification Required</h2>
                <p style="margin:0 0 25px 0; color:#374151; font-weight: 500; font-size: 16px;">Click the button below to verify your email address and activate your account:</p>
                <a href="{{ $data['verification_url'] ?? '#' }}" class="primary-button">✅ Verify My Email Address</a>
            </div>

            <div class="security-notice">
                <div style="width: 50px; height: 50px; background: #f59e0b; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 24px; color: white; margin-bottom: 15px;">
                    🔒
                </div>
                <h3 style="margin:0 0 15px 0; color:#92400e; font-size: 20px; font-weight: 700;">Security Notice</h3>
                <p style="margin:0; color:#92400e; font-size: 14px; font-weight: 500; line-height: 1.6;">
                    This verification link will expire in <strong>24 hours</strong>. If you did not create an account with POSU, please ignore this email and contact our support team immediately.
                </p>
            </div>

            <div style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-radius: 15px; padding: 25px; margin: 30px 0;">
                <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #6b7280, #4b5563); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 24px; color: white; margin-bottom: 15px;">
                    🔗
                </div>
                <h3 style="margin:0 0 15px 0; color:#374151; font-size: 18px; font-weight: 700;">Alternative Method</h3>
                <p style="color:#6b7280; font-size:14px; margin-bottom: 20px; font-weight: 500;">If the button above doesn't work, you can copy and paste this link into your browser:</p>
                <div class="alternative-link">
                    {{ $data['verification_url'] ?? 'Verification URL here' }}
                </div>
            </div>
        </div>
        <div class="footer">
            <p><strong>POSU Traffic Violation System</strong></p>
            <p>Municipality of Echague, Province of Isabela</p>
            <p><a href="https://posumoms.netlify.app">posumoms.netlify.app</a></p>
            <div style="background: rgba(255, 255, 255, 0.1); padding: 20px; border-radius: 15px; margin-top: 20px; font-size: 13px; color: #d1d5db; line-height: 1.5;">
                <strong>Automated Message:</strong> This is an automated message. Please do not reply to this email.
            </div>
        </div>
    </div>
</body>
</html>


