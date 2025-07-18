@extends('layouts.app')
@section('title', 'Sales History')
@section('content')

<style>
    .page-title {
        font-size: 22px;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 20px;
        font-family: 'Rubik', sans-serif;
    }
    .history-card {
        margin-bottom: 20px;
        border: 1px solid #ddd;
        border-radius: 12px;
        background: #fff;
        padding: 16px 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    .summary-row {
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
        font-family: 'Rubik', sans-serif;
        font-weight: 500;
    }
    .details-table {
        display: none;
        margin-top: 15px;
    }
    .details-table.show {
        display: block;
    }
    .toggle-btn {
        padding: 6px 12px;
        background: #059669;
        color: white;
        font-weight: bold;
        border: none;
        border-radius: 8px;
        cursor: pointer;
    }
    .table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }
    .table th, .table td {
        padding: 10px;
        border: 1px solid #ddd;
        text-align: center;
    }
    .table th {
        background-color: #f3f4f6;
    }
</style>

<h1 class="page-title">Sales History</h1>

@foreach($dailySummary as $day)
    <div class="history-card">
        <div class="summary-row">
            <div><strong>{{ $day['date'] }}</strong></div>
            <div>Total Sold: {{ $day['totalSold'] }}</div>
            <div>Total Profit: ₱{{ number_format($day['totalProfit'], 2) }}</div>
            <button class="toggle-btn" onclick="toggleDetails('{{ $day['date'] }}')">Show Details</button>
        </div>

        <div id="details-{{ $day['date'] }}" class="details-table">
            <table class="table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Discount</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($day['sales'] as $sale)
                        @foreach($sale->items as $item)
                            <tr>
                                <td>{{ $sale->created_at->format('h:i A') }}</td>
                                <td>{{ optional($item->product)->name ?? 'Unknown' }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>₱{{ number_format($sale->discount_amount ?? 0, 2) }}</td>
                                <td>₱{{ number_format($item->total_price, 2) }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endforeach

<script>
    function toggleDetails(date) {
        const el = document.getElementById(`details-${date}`);
        const isShown = el.classList.contains('show');
        el.classList.toggle('show');
        const button = el.previousElementSibling.querySelector('.toggle-btn');
        button.textContent = isShown ? 'Show Details' : 'Hide Details';
    }
</script>

@endsection
