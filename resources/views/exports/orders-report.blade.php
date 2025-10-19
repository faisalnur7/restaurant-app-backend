<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Orders Report</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 30px;
        }

        header {
            text-align: center;
            margin-bottom: 20px;
        }

        header h1 {
            font-size: 20px;
            margin: 0;
            text-transform: uppercase;
            color: #555;
        }

        header p {
            font-size: 12px;
            margin: 2px 0;
            color: #666;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 6px 8px;
            text-align: left;
        }

        th {
            background: #f9f9f9;
            font-weight: bold;
            text-align: center;
        }

        td {
            vertical-align: top;
        }

        tfoot td {
            font-weight: bold;
            background: #fafafa;
        }

        .no-data {
            text-align: center;
            font-style: italic;
            color: #777;
            padding: 20px;
        }
    </style>
</head>
<body>
    <header>
        <h1>{{ $companyName ?? 'Your Company Name' }}</h1>
        <p>{{ $companyAddress ?? 'Company Address Here' }}</p>
        <p><strong>Orders Report</strong> ({{ ucfirst($type) }}{{ $start && $end ? ": $start to $end" : '' }})</p>
    </header>

    @if(count($data) > 0)
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Order ID</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Payment</th>
                    <th>Type</th>
                    <th>Total (Tk)</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalOrders = 0;
                    $totalRevenue = 0;
                @endphp

                @foreach($data as $row)
                    @php
                        $totalOrders++;
                        $totalRevenue += $row['Total'];
                    @endphp
                    <tr>
                        <td>{{ $totalOrders }}</td>
                        <td>{{ $row['Order ID'] }}</td>
                        <td>{{ $row['Date'] }}</td>
                        <td>{{ $row['Customer'] }}</td>
                        <td>{{ $row['Payment'] }}</td>
                        <td>{{ $row['Type'] }}</td>
                        <td>{{ number_format($row['Total'], 2) }}</td>
                        <td>{{ $row['Status'] }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="6" style="text-align: center;">Total</td>
                    <td>{{ number_format($totalRevenue, 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    @else
        <div class="no-data">No orders available for the selected period.</div>
    @endif
</body>
</html>
