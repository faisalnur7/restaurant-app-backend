<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Most Selling Products Report</title>
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
        <p><strong>Most Selling Products Report</strong> ({{ ucfirst($type) }}{{ $start && $end ? ": $start to $end" : '' }})</p>
    </header>

    @if(count($data) > 0)
        <table>
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Product</th>
                    <th>Total Quantity Sold</th>
                    <th>Total Revenue</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $item)
                    <tr>
                        <td>{{ $item['Rank'] }}</td>
                        <td>{{ $item['Product'] }}</td>
                        <td>{{ $item['Total Quantity Sold'] }}</td>
                        <td>{{ number_format($item['Total Revenue'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="no-data">No products sold for the selected period.</div>
    @endif
</body>
</html>
