<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Traffic and Other Violations Report</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 20px;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
        }

        .container {
            margin: 0 auto;
            width: 92%; 
        }

        .header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            position: relative;
            width: 100%;
        }

        .header img {
            width: 80px;
            height: 80px;
            margin-right: 20px;
            flex-shrink: 0;
        }

        .header-text {
            text-align: center;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .header-text h2, .header-text h3, .header-text h4 {
            margin: 3px 0;
            font-weight: bold;
            text-align: center;
        }

        .header-text h3 {
            font-size: 14px;
        }

        .header-text h4 {
            font-size: 12px;
        }

        .header-text p {
            margin: 5px 0;
            text-align: center;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
            margin-left: -10px;
        }

        th, td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
            font-size: 10px;
        }

        th {
            text-transform: uppercase;
        }

        .footer {
            margin-top: 20px;
            font-size: 12px;
        }

        .footer .note {
            margin-top: 10px;
        }

        .total-row {
            font-weight: bold;
            background: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('resources/assets/posu_logo.png') }}" alt="Municipality Seal">
            <div class="header-text">
                <h4>REPUBLIC OF THE PHILIPPINES</h4>
                <h3>MUNICIPALITY OF ECHAGUE</h3>
                <h4>TRAFFIC AND OTHER VIOLATIONS</h4>
                <h4>SUMMARY OF REPORT</h4>
                <p>{{ now()->format('F d, Y') }}</p>
            </div>
        </div>


        @if($type === 'all_violators')
            <table>
                <thead>
                    <tr>
                        <th rowspan="2">NO.</th>
                        <th rowspan="2">NAME OF VIOLATOR</th>
                        <th rowspan="2">VIOLATOR'S ADDRESS</th>
                        <th rowspan="2">VIOLATION</th>
                        <th colspan="6">VEHICLE IDENTITY</th>
                        <th colspan="3">CITATION TICKET</th>
                        <th colspan="2">APPREHENDING PERSONNEL</th>
                        <th rowspan="2">REMARKS</th>
                        <th rowspan="2">AMOUNT OF PENALTY</th>
                    </tr>
                    <tr>
                        <th>TYPE</th>
                        <th>MAKE</th>
                        <th>MODEL</th>
                        <th>PLATE NO.</th>
                        <th>OWNER NAME</th>
                        <th>OWNER ADDRESS</th>
                        <th>TICKET NO.</th>
                        <th>DATE</th>
                        <th>TIME</th>
                        <th>OFFICER</th>
                        <th>OFFICE</th>
                    </tr>
                </thead>
                <tbody>
                    @php $grandTotal = 0; @endphp
                    @foreach($rows as $index => $row)
                        @php $grandTotal += $row['Penalty Amount'] ?? 0; @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $row['Violator Name'] ?? 'N/A' }}</td>
                            <td>{{ $row['Violator Address'] ?? 'N/A' }}</td>
                            <td>{{ $row['Violation Name'] ?? 'N/A' }}</td>
                            <td>{{ $row['Vehicle Type'] ?? 'N/A' }}</td>
                            <td>{{ $row['Vehicle Make'] ?? 'N/A' }}</td>
                            <td>{{ $row['Vehicle Model'] ?? 'N/A' }}</td>
                            <td>{{ $row['Plate Number'] ?? 'N/A' }}</td>
                            <td>{{ $row['Owner Name'] ?? 'N/A' }}</td>
                            <td>{{ $row['Owner Address'] ?? 'N/A' }}</td>
                            <td>{{ $row['Ticket Number'] ?? 'N/A' }}</td>
                            <td>{{ $row['Ticket Date'] ?? 'N/A' }}</td>
                            <td>{{ $row['Ticket Time'] ?? 'N/A' }}</td>
                            <td>{{ $row['Officer Name'] ?? 'N/A' }}</td>
                            <td>{{ $row['Officer Office'] ?? 'N/A' }}</td>
                            <td>{{ $row['Remarks'] ?? '' }}</td>
                            <td>₱{{ number_format($row['Penalty Amount'] ?? 0, 2) }}</td>
                        </tr>
                    @endforeach
                    <tr class="total-row">
                        <td colspan="16" style="text-align: left;">PENALTY TO BE COLLECTED</td>
                        <td>₱{{ number_format($grandTotal, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        @elseif($type === 'common_violations')
            <table>
                <thead>
                    <tr>
                        <th>NO.</th>
                        <th>VIOLATION</th>
                        <th>TOTAL COUNT</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $index => $row)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $row['Violation Name'] ?? $row['violation_name'] ?? 'N/A' }}</td>
                            <td>{{ $row['Count'] ?? $row['count'] ?? 0 }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @elseif($type === 'enforcer_performance')
            <table>
                <thead>
                    <tr>
                        <th>NO.</th>
                        <th>ENFORCER NAME</th>
                        <th>VIOLATIONS ISSUED</th>
                        <th>COLLECTION RATE (%)</th>
                        <th>TOTAL FINES</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $index => $row)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $row['Enforcer Name'] ?? $row['enforcer_name'] ?? 'N/A' }}</td>
                            <td>{{ $row['Violations Issued'] ?? $row['violations_issued'] ?? 0 }}</td>
                            <td>{{ $row['Collection Rate (%)'] ?? $row['collection_rate'] ?? 0 }}%</td>
                            <td>₱{{ number_format($row['Total Fines'] ?? $row['total_fines'] ?? 0, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @elseif($type === 'total_revenue')
            <p><strong>Total Revenue: ₱{{ number_format($totalPenalty ?? ($rows[0]['Total Revenue'] ?? $rows[0]['total_revenue'] ?? 0), 2) }}</strong></p>
        @else
            <p>No data available for this report.</p>
        @endif

        <div class="footer">
            @if($type === 'all_violators')
                <p><strong>Total Penalty to be Collected: ₱{{ number_format($grandTotal, 2) }}</strong></p>
            @endif
            <div class="note" style="margin-top: 20px;">
                <div style="text-align: left; margin-bottom: 20px;">
                    <p><strong>Prepared By:</strong> {{ $prepared_by ?? '_____________________' }}</p>
                </div>
                <div style="text-align: left;">
                    <p><strong>Noted By:</strong> {{ $noted_by ?? '_____________________' }}</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>