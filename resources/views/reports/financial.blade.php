<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Financial Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 14px; }
        h1 { color: #2563eb; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        .income { color: green; }
        .expense { color: red; }
    </style>
</head>
<body>
    <h1>{{ $society_name ?? 'Society Manager Pro' }}</h1>
    <p><strong>Report:</strong> {{ $report_type ?? 'Balance Sheet' }}</p>
    <p><strong>Period:</strong> {{ $period['from'] }} to {{ $period['to'] }}</p>
    <table>
        <tr><th>Total Income</th><td class="income">₹{{ number_format($total_income, 2) }}</td></tr>
        <tr><th>Total Expense</th><td class="expense">₹{{ number_format($total_expense, 2) }}</td></tr>
        <tr><th>Net Balance</th><td><strong>₹{{ number_format($net_balance, 2) }}</strong></td></tr>
    </table>
</body>
</html>
