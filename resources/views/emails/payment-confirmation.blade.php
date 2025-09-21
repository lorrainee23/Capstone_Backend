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

        .payment-card {
            background: #f0fdf4;
            border-left: 6px solid #22c55e;
            border-radius: 12px;
            padding: 25px;
            margin: 25px 0;
        }

        .content {
            padding: 40px;
        }

        .primary-button {
            display: inline-block;
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            margin: 20px 0;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(34, 197, 94, 0.3);
        }

        .primary-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(34, 197, 94, 0.4);
        }

        .payment-details {
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .detail-row:last-child {
            border-bottom: none;
            font-weight: 600;
            color: #1e40af;
        }

        .footer {
            background: #f8fafc;
            padding: 30px 40px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }

        .footer p {
            margin: 5px 0;
            color: #64748b;
            font-size: 14px;
        }

        .security-notice {
            background: #fef3c7;
            border: 2px solid #f59e0b;
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }

        .security-notice h3 {
            color: #92400e;
            margin: 0 0 10px 0;
        }

        .security-notice p {
            margin: 0;
            color: #92400e;
            font-size: 14px;
            font-weight: 500;
        }

        @media (max-width: 600px) {
            .email-container {
                margin: 10px;
            }
            
            .header, .content, .footer {
                padding: 25px;
            }
            
            .detail-row {
                flex-direction: column;
                gap: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="logo">
                <img src="{{ asset('resources/assets/posu_logo.png') }}" alt="POSU Logo">
            </div>
            <h1>Payment Confirmed! 🎉</h1>
            <p style="margin: 10px 0 0 0; opacity: 0.9; font-size: 16px;">Your violation has been successfully paid</p>
        </div>

        <div class="content">
            <div class="payment-card">
                <div style="width: 60px; height: 60px; background: #22c55e; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 28px; color: white; margin-bottom: 15px; line-height: 1; text-align: center;">
                    ✅
                </div>
                <h2 style="margin:0 0 15px 0; color:#16a34a; font-size: 24px; font-weight: 700;">Payment Successfully Processed</h2>
                <p style="margin:0 0 20px 0; color:#374151; font-weight: 500; font-size: 16px;">Dear {{ $data['user_name'] ?? 'Valued User' }},</p>
                <p style="margin:0 0 20px 0; color:#374151; font-size: 15px; line-height: 1.6;">We are pleased to confirm that your payment for the traffic violation has been successfully processed and your account is now in good standing.</p>
            </div>

            <div class="payment-details">
                <h3 style="margin:0 0 20px 0; color:#1e40af; font-size: 18px; font-weight: 700;">Payment Details</h3>
                
                <div class="detail-row">
                    <span><strong>Violation ID:</strong></span>
                    <span>{{ $data['violation_id'] ?? 'N/A' }}</span>
                </div>
                
                <div class="detail-row">
                    <span><strong>Violation Type:</strong></span>
                    <span>{{ $data['violation_type'] ?? 'N/A' }}</span>
                </div>
                
                <div class="detail-row">
                    <span><strong>Amount Paid:</strong></span>
                    <span>₱{{ number_format($data['amount_paid'] ?? 0, 2) }}</span>
                </div>
                
                <div class="detail-row">
                    <span><strong>Payment Date:</strong></span>
                    <span>{{ $data['payment_date'] ?? now()->format('F j, Y \a\t g:i A') }}</span>
                </div>
                
                <div class="detail-row">
                    <span><strong>Payment Method:</strong></span>
                    <span>{{ $data['payment_method'] ?? 'Cash' }}</span>
                </div>
                
                <div class="detail-row">
                    <span><strong>Status:</strong></span>
                    <span style="color: #22c55e; font-weight: 600;">✅ PAID</span>
                </div>
            </div>

            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $data['login_url'] ?? 'https://posumoms.netlify.app/login' }}" class="primary-button">🔐 Access Your Account</a>
            </div>

            <div class="security-notice">
                <div style="width: 50px; height: 50px; background: #f59e0b; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 24px; color: white; margin-bottom: 15px; line-height: 1; text-align: center;">
                    🔒
                </div>
                <h3 style="margin:0 0 15px 0; color:#92400e; font-size: 18px; font-weight: 700;">Important Notice</h3>
                <p style="color:#92400e; font-size:14px; margin-bottom: 15px; font-weight: 500;">Your payment has been recorded in our system. Please keep this email as proof of payment.</p>
                <p style="color:#92400e; font-size:14px; margin:0; font-weight: 500;">If you have any questions about this payment, please contact our support team.</p>
            </div>

            <div style="background: #f0f9ff; border-left: 6px solid #0ea5e9; border-radius: 12px; padding: 20px; margin: 25px 0;">
                <h3 style="margin:0 0 15px 0; color:#0369a1; font-size: 18px; font-weight: 700;">What's Next?</h3>
                <ul style="margin:0; padding-left: 20px; color:#374151; font-size: 14px; line-height: 1.6;">
                    <li>Your violation record has been marked as paid</li>
                    <li>You can now access your account without restrictions</li>
                    <li>Keep your driving record clean to avoid future violations</li>
                    <li>Contact us if you need any assistance</li>
                </ul>
            </div>
        </div>

        <div class="footer">
            <p><strong>POSU Traffic Violation System</strong></p>
            <p>Echague, Isabela</p>
            <p>📧 support@posu.gov.ph | 📞 (078) 123-4567</p>
            <p style="margin-top: 20px; font-size: 12px; color: #94a3b8;">
                This is an automated message. Please do not reply to this email.
            </p>
        </div>
    </div>
</body>
</html>