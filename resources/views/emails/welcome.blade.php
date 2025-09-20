<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to POSU Traffic Violation System</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #1f2937;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .email-container {
            max-width: 650px;
            margin: 20px auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
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
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
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

        .header h1 {
            font-size: 18px;
            font-weight: 600;
            margin: 0 0 8px 0;
            letter-spacing: 1px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        .header h2 {
            font-size: 16px;
            font-weight: 500;
            margin: 0 0 8px 0;
            opacity: 0.9;
        }

        .header h3 {
            font-size: 24px;
            font-weight: 700;
            margin: 0 0 20px 0;
            letter-spacing: 1px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        .website-link {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            color: white !important;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 14px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .content {
            padding: 50px 40px;
            background: white;
        }

        .welcome-title {
            font-size: 32px;
            font-weight: 700;
            color: #1e40af;
            margin: 0 0 20px 0;
            text-align: center;
            background: linear-gradient(135deg, #1e40af, #3b82f6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .welcome-subtitle {
            font-size: 18px;
            color: #6b7280;
            text-align: center;
            margin: 0 0 40px 0;
        }

        .welcome-card {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border: none;
            border-radius: 20px;
            padding: 30px;
            margin: 30px 0;
            box-shadow: 0 10px 25px rgba(14, 165, 233, 0.1);
            position: relative;
            overflow: hidden;
        }

        .welcome-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #0ea5e9, #06b6d4, #3b82f6);
        }

        .account-info {
            display: flex;
            align-items: center;
            margin: 20px 0;
            padding: 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        .account-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #1e40af, #3b82f6);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            font-size: 24px;
            color: white;
        }

        .account-details h4 {
            margin: 0 0 5px 0;
            color: #1f2937;
            font-size: 18px;
            font-weight: 600;
        }

        .account-details p {
            margin: 0;
            color: #6b7280;
            font-size: 14px;
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

        .verification-button {
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
        }

        .verification-button:hover {
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

        .footer-link {
            color: #60a5fa !important;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .footer-link:hover {
            color: #93c5fd !important;
        }

        .disclaimer {
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 15px;
            margin-top: 20px;
            font-size: 13px;
            line-height: 1.5;
            color: #d1d5db;
        }

        @media (max-width: 600px) {
            .email-container {
                margin: 10px;
                border-radius: 15px;
            }

            .header, .content, .footer {
                padding: 25px;
            }

            .welcome-title {
                font-size: 24px;
            }

            .account-info {
                flex-direction: column;
                text-align: center;
            }

            .account-icon {
                margin: 0 0 15px 0;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <div class="logo">
                <img src="{{ asset('images/posu_logo.jpg', true) }}" alt="Echague Logo">
            </div>
            <h1>REPUBLIC OF THE PHILIPPINES</h1>
            <h2>Province of Isabela</h2>
            <h3>MUNICIPALITY OF ECHAGUE</h3>

        </div>

        <!-- Main Content -->
        <div class="content">
            <h1 class="welcome-title">Welcome to POSU!</h1>
            <p class="welcome-subtitle">
                Your account has been successfully created and you're ready to get started.
            </p>

            <div class="welcome-card">
                <div style="text-align: center; margin-bottom: 25px;">
                    <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #10b981, #059669); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 28px; color: white; margin-bottom: 15px;">
                        🎉
                    </div>
                    <h3 style="margin: 0 0 10px 0; color: #1e40af; font-size: 24px; font-weight: 700;">
                        Welcome, {{ $data['full_name'] ?? $data['user_name'] ?? 'User' }}!
                    </h3>
                    <p style="margin: 0; color: #6b7280; font-size: 16px;">
                        You're now part of the POSU Traffic Violation System as a
                        <strong style="color: #1e40af;">{{ ucfirst($data['account_type'] ?? 'User') }}</strong>
                    </p>
                </div>

                <div class="account-info">
                    <div class="account-icon">📅</div>
                    <div class="account-details">
                        <h4>Account Created</h4>
                        <p>{{ $data['registration_date'] ?? now()->format('F j, Y') }}</p>
                    </div>
                </div>

                @if(!empty($data['temporary_password']))
                <div class="account-info">
                    <div class="account-icon">🔑</div>
                    <div class="account-details">
                        <h4>Temporary Password</h4>
                        <p style="font-family: 'Courier New', monospace; background: #f3f4f6; padding: 8px 12px; border-radius: 8px; color: #1f2937; font-weight: 600;">
                            {{ $data['temporary_password'] }}
                        </p>
                    </div>
                </div>
                @endif
            </div>

            <!-- Action Buttons -->
            <div style="text-align: center; margin: 40px 0;">
                <a href="{{ $data['login_url'] ?? url('/login') }}" class="action-button">
                    🚀 Get Started - Login Now
                </a>
            </div>

            @if(!empty($data['verification_url']))
            <div style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-radius: 20px; padding: 30px; margin: 30px 0; text-align: center; border: 2px solid #f59e0b;">
                <div style="width: 50px; height: 50px; background: #f59e0b; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 24px; color: white; margin-bottom: 15px;">
                    ✉️
                </div>
                <h3 style="margin: 0 0 10px 0; color: #92400e; font-size: 20px; font-weight: 700;">
                    Email Verification Required
                </h3>
                <p style="margin: 0 0 20px 0; color: #92400e; font-size: 14px;">
                    Please verify your email address to complete your account setup and ensure security.
                </p>
                <a href="{{ $data['verification_url'] }}" class="action-button verification-button">
                    ✅ Verify My Email Address
                </a>
            </div>
            @endif

            <div style="background: #f8fafc; border-radius: 15px; padding: 25px; margin: 30px 0; text-align: center;">
                <p style="margin: 0; color: #6b7280; font-size: 14px; line-height: 1.6;">
                    <strong>Need help?</strong> If you didn't create this account or have any questions, 
                    please contact our support team immediately.
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>POSU Traffic Violation System</strong></p>
            <p>Municipality of Echague, Province of Isabela</p>
            <p>
                <a href="https://posumoms.netlify.app"
                   style="color: #ffffff !important; text-decoration:none;">posumoms.netlify.app</a>
            </p>
            
            <div class="disclaimer">
                This email contains account information. Please keep it confidential and do not forward it to others.
            </div>
        </div>
    </div>
</body>
</html>
