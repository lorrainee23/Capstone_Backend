<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Confirmation - POSU Traffic Violation System</title>
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
            animation: celebrate 8s ease-in-out infinite;
        }

        @keyframes celebrate {
            0%, 100% { transform: scale(1) rotate(0deg); opacity: 0.5; }
            50% { transform: scale(1.3) rotate(360deg); opacity: 0.8; }
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

        .success-card {
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            border: none;
            border-radius: 20px;
            padding: 30px;
            margin: 30px 0;
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.2);
            position: relative;
            overflow: hidden;
            text-align: center;
        }

        .success-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #10b981, #059669, #047857);
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #10b981, #059669);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            color: white;
            margin-bottom: 20px;
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.3);
        }

        .payment-details {
            background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
            border-radius: 20px;
            padding: 30px;
            margin: 30px 0;
            border: none;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            position: relative;
        }

        .payment-details::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #3b82f6, #1e40af, #1d4ed8);
            border-radius: 20px 20px 0 0;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 15px 0;
            padding: 15px 0;
            border-bottom: 1px solid rgba(229, 231, 235, 0.5);
            transition: all 0.3s ease;
        }

        .detail-row:hover {
            background: rgba(59, 130, 246, 0.05);
            border-radius: 8px;
            padding: 15px 10px;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 700;
            color: #374151;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .detail-value {
            color: #1f2937;
            font-weight: 600;
            font-size: 15px;
        }

        .amount {
            color: #059669;
            font-weight: 800;
            font-size: 20px;
            text-shadow: 0 2px 4px rgba(5, 150, 105, 0.2);
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

        .thank-you {
            text-align: center;
            margin: 30px 0;
        }

        .thank-you h2 {
            color: #059669;
            margin: 0 0 15px 0;
            font-size: 28px;
            font-weight: 800;
            text-shadow: 0 2px 4px rgba(5, 150, 105, 0.2);
        }

        .important-note {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border: none;
            border-radius: 20px;
            padding: 25px;
            margin: 30px 0;
            box-shadow: 0 8px 25px rgba(245, 158, 11, 0.15);
            position: relative;
        }

        .important-note::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #f59e0b, #d97706, #b45309);
            border-radius: 20px 20px 0 0;
        }

        .important-note h4 {
            margin: 0 0 15px 0;
            color: #92400e;
            font-size: 18px;
            font-weight: 700;
        }

        .important-note p {
            margin: 0;
            color: #92400e;
            font-size: 14px;
            font-weight: 500;
            line-height: 1.6;
        }

        @media (max-width: 600px) {
            .email-container {
                margin: 10px;
                border-radius: 15px;
            }

            .header, .content, .footer {
                padding: 25px;
            }

            .success-icon {
                width: 60px;
                height: 60px;
                font-size: 28px;
            }

            .detail-row {
                flex-direction: column;
                align-items: flex-start;
            }

            .detail-value {
                text-align: left;
                margin-top: 5px;
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
                    💰
                </div>
                <h1 style="color: #10b981; margin: 0 0 10px 0; font-size: 32px; font-weight: 800; text-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);">
                    Payment Confirmed!
                </h1>
                <p style="color: #6b7280; font-size: 18px; margin: 0;">
                    Your traffic violation payment has been successfully processed
                </p>
            </div>

            <div class="success-card">
                <div class="success-icon">✅</div>
                <h2 style="margin:0 0 15px 0; color:#059669; font-size: 24px; font-weight: 700; text-shadow: 0 2px 4px rgba(5, 150, 105, 0.2);">Payment Successfully Processed</h2>
                <p style="margin:0; color:#374151; font-size: 16px; font-weight: 500;">Dear <strong>{{ $data['violator_name'] ?? 'Valued Citizen' }}</strong>, your traffic violation payment has been successfully processed and your account is now up to date.</p>
            </div>

            <div class="thank-you">
                <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #10b981, #059669); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 28px; color: white; margin-bottom: 15px; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);">
                    🙏
                </div>
                <h2>Thank You for Your Payment</h2>
                <p style="color: #6b7280; font-size: 16px; font-weight: 500;">Your compliance with traffic regulations helps keep our roads safe for everyone. We appreciate your prompt payment.</p>
            </div>

            <div class="payment-details">
                <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #3b82f6, #1e40af); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 24px; color: white; margin-bottom: 15px;">
                    📋
                </div>
                <h3 style="margin:0 0 20px 0; color:#1f2937; font-size: 20px; font-weight: 700; text-align: center;">Payment Details</h3>
                <div class="detail-row">
                    <span class="detail-label">Ticket Number:</span>
                    <span class="detail-value">{{ $data['ticket_number'] ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Violation Type:</span>
                    <span class="detail-value">{{ $data['violation_type'] ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Fine Amount:</span>
                    <span class="detail-value amount">₱{{ number_format($data['fine_amount'] ?? 0, 2) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Payment Date:</span>
                    <span class="detail-value">{{ $data['payment_date'] ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Payment Time:</span>
                    <span class="detail-value">{{ $data['payment_datetime'] ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Violation Date:</span>
                    <span class="detail-value">{{ $data['violation_date'] ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Location:</span>
                    <span class="detail-value">{{ $data['location'] ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">License Number:</span>
                    <span class="detail-value">{{ $data['license_number'] ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Vehicle:</span>
                    <span class="detail-value">{{ $data['vehicle_info'] ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Plate Number:</span>
                    <span class="detail-value">{{ $data['plate_number'] ?? 'N/A' }}</span>
                </div>
            </div>

            <div class="important-note">
                <div style="width: 50px; height: 50px; background: #f59e0b; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 24px; color: white; margin-bottom: 15px;">
                    📄
                </div>
                <h3 style="margin:0 0 15px 0; color:#92400e; font-size: 20px; font-weight: 700;">Important Note</h3>
                <p style="margin:0; color:#92400e; font-size: 14px; font-weight: 500; line-height: 1.6;">
                    Please keep this email as proof of payment. If you have any questions about this payment, please contact our office immediately.
                </p>
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
