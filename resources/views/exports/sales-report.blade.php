<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sales Report</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 13px;
            color: #333;
            margin: 30px;
        }

        /* Company Header */
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .company-name {
            font-size: 22px;
            font-weight: bold;
            color: #444;
            margin-bottom: 5px;
        }
        .company-info {
            font-size: 12px;
            color: #666;
        }

        /* Report Title */
        h2 {
            text-align: center;
            margin: 20px 0;
            font-size: 18px;
            color: #555;
            text-transform: uppercase;
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 5px;
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px 10px;
            text-align: right;
            font-size: 12px;
        }
        th {
            background-color: #f9f9f9;
            font-weight: bold;
            text-align: center;
        }
        td:first-child, th:first-child {
            text-align: left;
        }

        /* Totals */
        tfoot td {
            font-weight: bold;
            background-color: #fdfdfd;
        }

        /* No data message */
        .no-data {
            text-align: center;
            font-style: italic;
            color: #999;
            padding: 20px;
        }

        /* Light stripe rows */
        tbody tr:nth-child(odd) {
            background-color: #fcfcfc;
        }
        tbody tr:nth-child(even) {
            background-color: #fefefe;
        }
    </style>
</head>
<body>

    <!-- Company Header -->
    <div class="header">
        <div class="company-name">{{ $companyName ?? 'Your Company Name' }}</div>
        <div class="company-info">
            {{ $companyAddress ?? 'Company Address Here' }} | Phone: {{ $companyPhone ?? '123-456-7890' }} | Email: {{ $companyEmail ?? 'info@company.com' }}
        </div>
    </div>

    <!-- Report Title -->
    <h2>Sales Report</h2>

    @if(count($data) > 0)
        <table>
            <thead>
                <tr>
                    <th>Period</th>
                    <th>Total Orders</th>
                    <th>Total Revenue (Tk)</th>
                    <th>Cash (Tk)</th>
                    <th>Card/Other (Tk)</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalOrders = 0;
                    $totalRevenue = 0;
                    $totalCash = 0;
                    $totalOther = 0;
                @endphp

                @foreach($data as $row)
                    @php
                        $totalOrders += $row["Total Orders"];
                        $totalRevenue += $row["Total Revenue"];
                        $totalCash += $row["Cash"];
                        $totalOther += $row["Other"];
                    @endphp
                    <tr>
                        <td>{{ $row["Period"] }}</td>
                        <td>{{ $row["Total Orders"] }}</td>
                        <td>{{ number_format($row["Total Revenue"], 2) }}</td>
                        <td>{{ number_format($row["Cash"], 2) }}</td>
                        <td>{{ number_format($row["Other"], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td>Total</td>
                    <td>{{ $totalOrders }}</td>
                    <td>{{ number_format($totalRevenue, 2) }}</td>
                    <td>{{ number_format($totalCash, 2) }}</td>
                    <td>{{ number_format($totalOther, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    @else
        <div class="no-data">No sales data available for the selected period.</div>
    @endif

</body>
</html>
