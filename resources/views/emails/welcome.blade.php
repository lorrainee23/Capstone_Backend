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

        .header h1, .header h2, .header h3 {
            margin: 0;
        }

        .welcome-card {
            background: #f0f9ff;
            border-left: 6px solid #0ea5e9;
            border-radius: 12px;
            padding: 25px;
            margin: 25px 0;
        }

        .content {
            padding: 40px;
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
            .email-container {
                margin: 0;
            }

            .header, .content, .footer {
                padding: 20px;
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
            <h2 style="color: #1f2937; margin: 0 0 20px 0;">
                Welcome, {{ $data['full_name'] ?? $data['user_name'] ?? 'User' }}!
            </h2>
            <p>
                Welcome to the POSU Traffic Violation System as a
                <strong>{{ ucfirst($data['account_type'] ?? 'User') }}</strong>.
            </p>

            <div class="welcome-card">
                <p style="margin:0 0 8px 0;">
                    Your account has been created on
                    <strong>{{ $data['registration_date'] ?? now()->format('F j, Y') }}</strong>.
                </p>
                @if(!empty($data['temporary_password']))
                    <p style="margin:0;">
                        Temporary Password: <strong>{{ $data['temporary_password'] }}</strong>
                    </p>
                @endif
            </div>

            <!-- Go to Login Button -->
            <div style="text-align:center; margin: 12px 0 24px 0;">
                <a href="http://localhost:8080/officials-login"
                style="
                        display:inline-block;
                        background: linear-gradient(135deg, #1e40af, #3b82f6);
                        color: #ffffff !important;
                        text-decoration: none;
                        padding: 14px 28px;
                        border-radius: 12px;
                        font-weight: 600;
                        font-size: 16px;
                        margin: 12px 0;
                        box-shadow: 0 4px 16px rgba(59, 130, 246, 0.3);
                ">
                Officials Login
                </a>
            </div>

            @if(!empty($data['verification_url']))
                <p style="margin: 20px 0 0 0;">For your security, please verify your email address:</p>
                <div style="text-align:center; margin: 12px 0 24px 0;">
                    <a href="{{ $data['verification_url'] }}"
                       style="
                            display:inline-block;
                            background: linear-gradient(135deg, #1e40af, #3b82f6);
                            color: #ffffff !important;
                            text-decoration: none;
                            padding: 14px 28px;
                            border-radius: 12px;
                            font-weight: 600;
                            font-size: 16px;
                            margin: 12px 0;
                            box-shadow: 0 4px 16px rgba(59, 130, 246, 0.3);
                       ">
                       Verify Email
                    </a>
                </div>
            @endif

            <p style="color:#6b7280; font-size:14px;">
                If you did not expect this email, you can ignore it.
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>POSU Traffic Violation System</strong></p>
            <p>Municipality of Echague, Province of Isabela</p>
            <p>
                <a href="https://posumoms.netlify.app"
                   style="color: #ffffff !important; text-decoration:none;">www.posumoms.netlify.app</a>
            </p>
            
            <div class="disclaimer">
                This email contains account information. Please keep it confidential and do not forward it to others.
            </div>
        </div>
    </div>
</body>
</html>
