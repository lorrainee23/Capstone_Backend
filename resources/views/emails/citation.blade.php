<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POSU Traffic Citation Notice</title>
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
            animation: pulse 4s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1) rotate(0deg); opacity: 0.5; }
            50% { transform: scale(1.1) rotate(180deg); opacity: 0.8; }
        }
        
        .logo {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 16px rgba(255, 255, 255, 0.2);
        }
        
        .logo img {
            width: 60px;
            height: 60px;
            object-fit: contain;
        }
        
        .logo-text {
            font-size: 24px;
            font-weight: bold;
            color: #1e40af;
        }
        
        .header h1 {
            margin: 0 0 5px 0;
            font-size: 18px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        
        .header h2 {
            margin: 0 0 5px 0;
            font-size: 16px;
            font-weight: 500;
            opacity: 0.95;
        }
        
        .header h3 {
            margin: 0 0 20px 0;
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        
        .website-link {
            color: #fbbf24;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            border: 1px solid rgba(251, 191, 36, 0.3);
            padding: 8px 16px;
            border-radius: 20px;
            display: inline-block;
            transition: all 0.3s ease;
        }
        
        .website-link:hover {
            background: rgba(251, 191, 36, 0.1);
            border-color: #fbbf24;
        }
        
        .alert-banner {
            background: linear-gradient(135deg, #dc2626 0%, #ef4444 50%, #f87171 100%);
            color: white;
            padding: 25px;
            text-align: center;
            font-weight: 700;
            font-size: 20px;
            position: relative;
            overflow: hidden;
        }

        .alert-banner::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0% { left: -100%; }
            100% { left: 100%; }
        }
        
        .content {
            padding: 50px 40px;
            background: white;
        }
        
        .citation-card {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border: none;
            border-radius: 20px;
            padding: 30px;
            margin: 30px 0;
            box-shadow: 0 10px 25px rgba(245, 158, 11, 0.2);
            position: relative;
            overflow: hidden;
        }

        .citation-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #f59e0b, #d97706, #b45309);
        }
        
        .citation-number {
            font-size: 28px;
            font-weight: 800;
            color: #92400e;
            text-align: center;
            margin-bottom: 20px;
            text-shadow: 0 2px 4px rgba(146, 64, 14, 0.2);
            letter-spacing: 1px;
        }
        
        .violation-details {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 20px;
            padding: 30px;
            margin: 30px 0;
            border: none;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            position: relative;
        }

        .violation-details::before {
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
            padding: 15px 0;
            border-bottom: 1px solid rgba(226, 232, 240, 0.5);
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
            flex: 1;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .detail-value {
            flex: 2;
            text-align: right;
            color: #1f2937;
            font-weight: 600;
            font-size: 15px;
        }
        
        .violation-type {
            color: #dc2626;
            font-weight: 700;
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .fine-amount {
            color: #059669;
            font-weight: 800;
            font-size: 20px;
            text-shadow: 0 2px 4px rgba(5, 150, 105, 0.2);
        }
        
        .action-section {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            border-radius: 20px;
            padding: 30px;
            margin: 30px 0;
            border: none;
            text-align: center;
            box-shadow: 0 10px 25px rgba(220, 38, 38, 0.15);
            position: relative;
            overflow: hidden;
        }

        .action-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #dc2626, #ef4444, #f87171);
        }
        
        .action-title {
            color: #991b1b;
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 20px;
            text-shadow: 0 2px 4px rgba(153, 27, 27, 0.2);
        }
        
        .deadline {
            color: #dc2626;
            font-size: 20px;
            font-weight: 700;
            background: white;
            padding: 15px 25px;
            border-radius: 15px;
            display: inline-block;
            margin: 15px 0;
            box-shadow: 0 4px 15px rgba(220, 38, 38, 0.2);
            border: 2px solid #fecaca;
        }
        
        .instructions {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border-radius: 20px;
            padding: 30px;
            margin: 30px 0;
            border: none;
            box-shadow: 0 8px 25px rgba(14, 165, 233, 0.1);
            position: relative;
        }

        .instructions::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #0ea5e9, #06b6d4, #0891b2);
            border-radius: 20px 20px 0 0;
        }
        
        .instructions h4 {
            color: #0369a1;
            font-size: 20px;
            font-weight: 700;
            margin: 0 0 20px 0;
            text-shadow: 0 2px 4px rgba(3, 105, 161, 0.2);
        }
        
        .instructions ol {
            margin: 15px 0;
            padding-left: 20px;
        }
        
        .instructions li {
            margin: 12px 0;
            color: #374151;
            font-weight: 500;
            line-height: 1.6;
        }
        
        .contact-info {
            background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
            border-radius: 20px;
            padding: 30px;
            margin: 30px 0;
            border: none;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.05);
            position: relative;
        }

        .contact-info::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #6b7280, #4b5563, #374151);
            border-radius: 20px 20px 0 0;
        }
        
        .contact-info h4 {
            color: #374151;
            font-size: 18px;
            font-weight: 700;
            margin: 0 0 20px 0;
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
        
        .footer p {
            margin: 5px 0;
            opacity: 0.9;
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
            color: #d1d5db;
            line-height: 1.5;
        }
        
        @media (max-width: 600px) {
            .email-container {
                margin: 10px;
                border-radius: 15px;
            }
            
            .header, .content, .footer {
                padding: 25px;
            }
            
            .detail-row {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .detail-value {
                text-align: left;
                margin-top: 5px;
            }

            .citation-number {
                font-size: 22px;
            }

            .action-title {
                font-size: 20px;
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
            <a href="https://posumoms.netlify.app" class="website-link">posumoms.netlify.app</a>
        </div>

        <!-- Alert Banner -->
        <div class="alert-banner">
            🚨 TRAFFIC CITATION NOTICE - IMMEDIATE ACTION REQUIRED
        </div>

        <!-- Main Content -->
        <div class="content">
            <div style="text-align: center; margin-bottom: 30px;">
                <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #dc2626, #ef4444); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 36px; color: white; margin-bottom: 20px; box-shadow: 0 8px 25px rgba(220, 38, 38, 0.3);">
                    🚨
                </div>
                <h1 style="color: #dc2626; margin: 0 0 10px 0; font-size: 32px; font-weight: 800; text-shadow: 0 2px 4px rgba(220, 38, 38, 0.2);">
                    Traffic Citation Issued
                </h1>
                <p style="color: #6b7280; font-size: 18px; margin: 0;">
                    Official notice from POSU Traffic Enforcement
                </p>
            </div>
            
            <div style="background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%); border-radius: 15px; padding: 25px; margin: 30px 0; text-align: center;">
                <p style="margin: 0; color: #dc2626; font-size: 16px; font-weight: 600;">
                    Dear <strong>{{ $data['violator_name'] ?? 'Violator' }}</strong>,<br>
                    You have been issued a traffic citation for a violation committed on 
                    <strong>{{ $data['violation_date'] ?? date('F j, Y') }}</strong>.
                </p>
            </div>

            <!-- Citation Card -->
            <div class="citation-card">
                <div class="citation-number">
                    Citation Ticket No: {{ $data['ticket_number'] ?? 'N/A' }}
                </div>
                <p style="text-align: center; margin: 0; color: #92400e; font-weight: 500;">
                    This citation has been recorded in the POSU Traffic Violation System
                </p>
            </div>

            <!-- Violation Details -->
            <div class="violation-details">
                <h3 style="margin: 0 0 20px 0; color: #1f2937;">Violation Details</h3>
                
                <div class="detail-row">
                    <span class="detail-label">Violator Name:</span>
                    <span class="detail-value">{{ $data['violator_name'] ?? 'N/A' }}</span>
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
                
                <div class="detail-row">
                    <span class="detail-label">Violation:</span>
                    <span class="detail-value violation-type">{{ $data['violation_type'] ?? 'Traffic Violation' }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Location:</span>
                    <span class="detail-value">{{ $data['location'] ?? 'Echague, Isabela' }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Date & Time:</span>
                    <span class="detail-value">{{ $data['violation_datetime'] ?? date('F j, Y - g:i A') }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Fine Amount:</span>
                    <span class="detail-value fine-amount">₱{{ number_format($data['fine_amount'] ?? 0) }}</span>
                </div>
            </div>

            <!-- Action Required -->
            <div class="action-section">
                <div class="action-title">⚠️ IMMEDIATE ACTION REQUIRED</div>
                <p>You must report to the Echague Police Station within:</p>
                <div class="deadline">7 DAYS FROM {{ strtoupper($data['violation_date'] ?? date('F j, Y')) }}</div>
                <p><strong>Failure to comply will result in filing of charges to the appropriate court.</strong></p>
            </div>

            <!-- Instructions -->
            <div class="instructions">
                <h4>📋 What You Need to Do:</h4>
                <ol>
                    <li><strong>Report to Echague Police Station</strong> within 7 days from the violation date</li>
                    <li><strong>Bring valid identification</strong> and your driver's license</li>
                    <li><strong>Present this email</strong> or the physical citation ticket</li>
                    <li><strong>Choose one of the following options:</strong>
                        <ul style="margin: 10px 0; padding-left: 20px;">
                            <li>Accept the citation and pay the fine to the Municipal Treasurer</li>
                            <li>Contest the citation (if you believe it was issued in error)</li>
                        </ul>
                    </li>
                </ol>
                
                <p><strong style="color: #dc2626;">Important:</strong> If you contest this citation, you may still be required to pay the fine pending the resolution of your case.</p>
            </div>

            <!-- Contact Information -->
            <div class="contact-info">
                <h4>📞 Need Help or Have Questions?</h4>
                <p><strong>Echague Police Station</strong><br>
                Address: Banchetto San Fabian Echague, Isabela<br>
                Phone: 09123456789 <br>
                Office Hours: Monday to Friday, 8:00 AM - 5:00 PM</p>
                
                <p><strong>POSU Traffic Enforcement</strong><br>
                Email: support@echague.gov.ph<br>
                Website: <a href="https://posumoms.netlify.app" style="color: #3b82f6;">posumoms.netlify.app</a></p>
            </div>

            <p style="margin: 30px 0 0 0; color: #6b7280; font-size: 14px;">
                <strong>Note:</strong> This is an automated notification from the POSU Traffic Violation System. 
                Please do not reply to this email. For inquiries, use the contact information provided above.
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>POSU Traffic Enforcement System</strong></p>
            <p>Municipality of Echague, Province of Isabela</p>
            <p><a href="https://posumoms.netlify.app" class="footer-link">posumoms.netlify.app</a></p>
            
            <div class="disclaimer">
                This email was sent from an automated system. Please do not reply to this email address. 
                If you believe you received this email in error, please contact our support team immediately.
                Your privacy is important to us - this information is confidential and intended only for the addressee.
            </div>
        </div>
    </div>
</body>
</html>