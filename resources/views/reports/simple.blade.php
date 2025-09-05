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
            width: 95%; /* Slightly narrower for Word compatibility */
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            position: relative;
        }

        .header img {
            width: 60px;
            height: 60px;
            display: inline-block; /* avoid absolute positioning */
            vertical-align: middle;
            margin-right: 10px;
        }

        .header h2, .header h3, .header h4 {
            margin: 2px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="logo.png" alt="Municipality Seal">
            <div style="display:inline-block; text-align:center;">
                <h4>REPUBLIC OF THE PHILIPPINES</h4>
                <h3>MUNICIPALITY OF ECHAGUE</h3>
                <h4>TRAFFIC AND OTHER VIOLATIONS</h4>
                <h4>S U M M A R Y  O F  R E P O R T</h4>
                <p>{{ now()-> format('F d, Y')}}</p>
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
                        <th rowspan="2">PENALTY</th>
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
                    @foreach($rows as $index => $row)
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
                </tbody>
            </table>
        @elseif($type === 'Common Violations')
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
                            <td>{{ $row['violation_name'] }}</td>
                            <td>{{ $row['count'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @elseif($type === 'Enforcer Performance')
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
                            <td>{{ $row['enforcer_name'] }}</td>
                            <td>{{ $row['violations_issued'] }}</td>
                            <td>{{ $row['collection_rate'] }}%</td>
                            <td>₱{{ number_format($row['total_fines'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @elseif($type === 'Total Revenue')
            <p><strong>Total Revenue: ₱{{ number_format($rows[0]['total_revenue'], 2) }}</strong></p>
        @else
            <p>No data available for this report.</p>
        @endif

        <div class="footer">
            @if($type === 'All Violators')
                <p><strong>Total Penalty to be Collected: ₱{{ number_format($totalPenalty ?? 0, 2) }}</strong></p>
            @endif
            <div class="note">
                <p>Noted by:</p>
                <br><br>
                <p><strong>{{ $noted_by ?? '_____________________' }}</strong></p>
            </div>
        </div>
    </div>
</body>
</html>
