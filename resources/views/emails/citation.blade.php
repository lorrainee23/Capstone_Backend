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
            position: relative;
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
            background: linear-gradient(135deg, #dc2626, #ef4444);
            color: white;
            padding: 20px;
            text-align: center;
            font-weight: 600;
            font-size: 18px;
        }
        
        .content {
            padding: 40px;
        }
        
        .citation-card {
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            border-left: 6px solid #f59e0b;
            border-radius: 12px;
            padding: 25px;
            margin: 25px 0;
        }
        
        .citation-number {
            font-size: 24px;
            font-weight: 700;
            color: #92400e;
            text-align: center;
            margin-bottom: 15px;
        }
        
        .violation-details {
            background: #f8fafc;
            border-radius: 12px;
            padding: 25px;
            margin: 25px 0;
            border: 1px solid #e2e8f0;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .detail-row:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            font-weight: 600;
            color: #374151;
            flex: 1;
        }
        
        .detail-value {
            flex: 2;
            text-align: right;
            color: #1f2937;
        }
        
        .violation-type {
            color: #dc2626;
            font-weight: 600;
        }
        
        .fine-amount {
            color: #059669;
            font-weight: 700;
            font-size: 18px;
        }
        
        .action-section {
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            border-radius: 12px;
            padding: 25px;
            margin: 25px 0;
            border: 2px solid #fca5a5;
            text-align: center;
        }
        
        .action-title {
            color: #991b1b;
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 15px;
        }
        
        .deadline {
            color: #dc2626;
            font-size: 18px;
            font-weight: 600;
            background: white;
            padding: 12px 20px;
            border-radius: 8px;
            display: inline-block;
            margin: 10px 0;
        }
        
        .instructions {
            background: #f0f9ff;
            border-radius: 12px;
            padding: 25px;
            margin: 25px 0;
            border: 1px solid #bae6fd;
        }
        
        .instructions h4 {
            color: #0369a1;
            font-size: 18px;
            font-weight: 600;
            margin: 0 0 15px 0;
        }
        
        .instructions ol {
            margin: 15px 0;
            padding-left: 20px;
        }
        
        .instructions li {
            margin: 10px 0;
            color: #374151;
        }
        
        .contact-info {
            background: #f9fafb;
            border-radius: 12px;
            padding: 25px;
            margin: 25px 0;
            border: 1px solid #d1d5db;
        }
        
        .contact-info h4 {
            color: #374151;
            font-size: 16px;
            font-weight: 600;
            margin: 0 0 15px 0;
        }
        
        .footer {
            background: #374151;
            color: white;
            padding: 30px 40px;
            text-align: center;
        }
        
        .footer p {
            margin: 5px 0;
            opacity: 0.9;
        }
        
        .footer-link {
            color: #60a5fa;
            text-decoration: none;
        }
        
        .footer-link:hover {
            color: #93c5fd;
        }
        
        .disclaimer {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 20px;
            line-height: 1.4;
        }
        
        @media (max-width: 600px) {
            .email-container {
                margin: 0;
            }
            
            .header, .content, .footer {
                padding: 20px;
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
        <!-- Header -->
        <div class="header">
            <h1>REPUBLIC OF THE PHILIPPINES</h1>
            <h2>Province of Isabela</h2>
            <h3>MUNICIPALITY OF ECHAGUE</h3>
            <a href="https://echague.gov.ph" class="website-link">www.echague.gov.ph</a>
        </div>

        <!-- Alert Banner -->
        <div class="alert-banner">
            🚨 TRAFFIC CITATION NOTICE - IMMEDIATE ACTION REQUIRED
        </div>

        <!-- Main Content -->
        <div class="content">
            <h2 style="color: #1f2937; margin: 0 0 20px 0;">Traffic Violation Citation</h2>
            
            <p>Dear {{ $data['violator_name'] ?? 'Violator' }},</p>
            
            <p>You have been issued a traffic citation by the POSU Traffic Enforcement team. This is an official notice regarding a traffic violation committed on <strong>{{ $data['violation_date'] ?? date('F j, Y') }}</strong>.</p>

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
                Website: <a href="https://echague.gov.ph" style="color: #3b82f6;">www.echague.gov.ph</a></p>
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
            <p><a href="https://echague.gov.ph" class="footer-link">www.echague.gov.ph</a></p>
            
            <div class="disclaimer">
                This email was sent from an automated system. Please do not reply to this email address. 
                If you believe you received this email in error, please contact our support team immediately.
                Your privacy is important to us - this information is confidential and intended only for the addressee.
            </div>
        </div>
    </div>
</body>
</html>