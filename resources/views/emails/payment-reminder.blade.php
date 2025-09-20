<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Reminder - POSU Traffic Violation System</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #1f2937;
            background: linear-gradient(135deg, #dc2626 0%, #ef4444 50%, #f87171 100%);
            min-height: 100vh;
        }

        .email-container {
            max-width: 650px;
            margin: 20px auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(220, 38, 38, 0.2);
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
            animation: urgent 4s ease-in-out infinite;
        }

        @keyframes urgent {
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

        .reminder-card {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            border: none;
            border-radius: 20px;
            padding: 30px;
            margin: 30px 0;
            box-shadow: 0 10px 25px rgba(239, 68, 68, 0.2);
            position: relative;
            overflow: hidden;
            text-align: center;
        }

        .reminder-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #ef4444, #dc2626, #b91c1c);
        }

        .warning-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            color: white;
            margin-bottom: 20px;
            box-shadow: 0 8px 25px rgba(239, 68, 68, 0.3);
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
            color: #dc2626;
            font-weight: 800;
            font-size: 20px;
            text-shadow: 0 2px 4px rgba(220, 38, 38, 0.2);
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

        .urgent-notice {
            text-align: center;
            margin: 30px 0;
        }

        .urgent-notice h2 {
            color: #dc2626;
            margin: 0 0 15px 0;
            font-size: 28px;
            font-weight: 800;
            text-shadow: 0 2px 4px rgba(220, 38, 38, 0.2);
        }

        .days-pending {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border: none;
            border-radius: 20px;
            padding: 25px;
            margin: 30px 0;
            text-align: center;
            box-shadow: 0 10px 25px rgba(245, 158, 11, 0.2);
            position: relative;
        }

        .days-pending::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #f59e0b, #d97706, #b45309);
        }

        .days-pending h3 {
            margin: 0 0 10px 0;
            color: #92400e;
            font-size: 24px;
            font-weight: 800;
            text-shadow: 0 2px 4px rgba(146, 64, 14, 0.2);
        }

        .action-required {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            border: none;
            border-radius: 20px;
            padding: 25px;
            margin: 30px 0;
            box-shadow: 0 8px 25px rgba(239, 68, 68, 0.15);
            position: relative;
        }

        .action-required::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #ef4444, #dc2626, #b91c1c);
            border-radius: 20px 20px 0 0;
        }

        .contact-info {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            border: none;
            border-radius: 20px;
            padding: 25px;
            margin: 30px 0;
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.15);
            position: relative;
        }

        .contact-info::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #3b82f6, #1e40af, #1d4ed8);
            border-radius: 20px 20px 0 0;
        }

        @media (max-width: 600px) {
            .email-container {
                margin: 10px;
                border-radius: 15px;
            }

            .header, .content, .footer {
                padding: 25px;
            }

            .warning-icon {
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
                <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #dc2626, #ef4444); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 36px; color: white; margin-bottom: 20px; box-shadow: 0 8px 25px rgba(220, 38, 38, 0.3);">
                    ⚠️
                </div>
                <h1 style="color: #dc2626; margin: 0 0 10px 0; font-size: 32px; font-weight: 800; text-shadow: 0 2px 4px rgba(220, 38, 38, 0.2);">
                    @if(($data['reminder_type'] ?? '') === '3-day')
                        Payment Reminder
                    @elseif(($data['reminder_type'] ?? '') === '5-day')
                        Payment Warning
                    @elseif(($data['reminder_type'] ?? '') === '7-day')
                        Court Filing Notice
                    @else
                        Payment Reminder
                    @endif
                </h1>
                <p style="color: #6b7280; font-size: 18px; margin: 0;">
                    Your traffic violation payment is overdue
                </p>
            </div>

            <div class="reminder-card">
                <div class="warning-icon">⚠️</div>
                <h2 style="margin:0 0 15px 0; color:#dc2626; font-size: 24px; font-weight: 700; text-shadow: 0 2px 4px rgba(220, 38, 38, 0.2);">
                    @if(($data['reminder_type'] ?? '') === '3-day')
                        Payment Reminder
                    @elseif(($data['reminder_type'] ?? '') === '5-day')
                        Payment Warning
                    @elseif(($data['reminder_type'] ?? '') === '7-day')
                        Court Filing Notice
                    @else
                        Payment Reminder
                    @endif
                </h2>
                <p style="margin:0; color:#374151; font-size: 16px; font-weight: 500;">Dear <strong>{{ $data['violator_name'] ?? 'Valued Citizen' }}</strong>, your traffic violation payment is overdue and requires immediate attention.</p>
            </div>

            <div class="days-pending">
                <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #f59e0b, #d97706); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 28px; color: white; margin-bottom: 15px; box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);">
                    📅
                </div>
                <h3>{{ $data['days_pending'] ?? '0' }} Days Overdue</h3>
                <p style="margin:10px 0 0 0; color:#92400e; font-weight:600; font-size: 16px;">
                    @if(($data['reminder_type'] ?? '') === '3-day')
                        Please settle your payment to avoid further action.
                    @elseif(($data['reminder_type'] ?? '') === '5-day')
                        Immediate payment required to avoid legal consequences.
                    @elseif(($data['reminder_type'] ?? '') === '7-day')
                        Your case has been filed in court due to non-payment.
                    @endif
                </p>
            </div>

            <div class="action-required">
                <div style="width: 50px; height: 50px; background: #dc2626; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 24px; color: white; margin-bottom: 15px;">
                    🚨
                </div>
                <h3 style="margin:0 0 15px 0; color:#dc2626; font-size: 20px; font-weight: 700;">Action Required</h3>
                <p style="margin:0; color:#374151; font-size: 16px; font-weight: 500; line-height: 1.6;">
                    @if(($data['reminder_type'] ?? '') === '3-day')
                        Please visit our office or contact us to settle your payment within the next 2 days to avoid additional penalties.
                    @elseif(($data['reminder_type'] ?? '') === '5-day')
                        This is your final warning. Please settle your payment immediately to avoid court filing and additional legal fees.
                    @elseif(($data['reminder_type'] ?? '') === '7-day')
                        Your violation has been escalated to court. Please contact our office immediately to discuss your case and avoid further legal action.
                    @endif
                </p>
            </div>

            <div class="payment-details">
                <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #3b82f6, #1e40af); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 24px; color: white; margin-bottom: 15px;">
                    📋
                </div>
                <h3 style="margin:0 0 20px 0; color:#1f2937; font-size: 20px; font-weight: 700; text-align: center;">Violation Details</h3>
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

            <div class="contact-info">
                <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #3b82f6, #1e40af); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 24px; color: white; margin-bottom: 15px;">
                    📞
                </div>
                <h3 style="margin:0 0 15px 0; color:#1e40af; font-size: 20px; font-weight: 700;">Contact Information</h3>
                <p style="margin:0; color:#1e40af; font-size: 16px; font-weight: 500; line-height: 1.6;">
                    For payment inquiries or to discuss your case, please contact:<br><br>
                    <strong>POSU Traffic Enforcement Office</strong><br>
                    Municipality of Echague, Province of Isabela<br>
                    Phone: (078) 123-4567<br>
                    Email: posu@echague.gov.ph
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
