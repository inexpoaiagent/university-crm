@extends('layouts.app')

@section('content')
<div class="grid-4">
    <div class="card"><h3>Total Amount</h3><div class="metric">{{ number_format($summary['total_amount'], 2) }}</div></div>
    <div class="card"><h3>Paid Amount</h3><div class="metric">{{ number_format($summary['paid_amount'], 2) }}</div></div>
    <div class="card"><h3>Commission</h3><div class="metric">{{ number_format($summary['commission'], 2) }}</div></div>
    <div class="card"><h3>Outstanding</h3><div class="metric">{{ number_format(max(0, $summary['total_amount'] - $summary['paid_amount']), 2) }}</div></div>
</div>

<div class="card" style="margin-top:12px;">
    <div class="toolbar">
        <form method="GET" action="/finance" style="display:flex;gap:8px;">
            <select name="status">
                <option value="">All statuses</option>
                @foreach(['pending','paid','failed','refunded'] as $s)
                    <option value="{{ $s }}" {{ $status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            <select name="currency">
                <option value="">All currencies</option>
                @foreach(['USD','EUR','TRY'] as $cur)
                    <option value="{{ $cur }}" {{ $currency === $cur ? 'selected' : '' }}>{{ $cur }}</option>
                @endforeach
            </select>
            <button type="submit">Filter</button>
        </form>
        <button onclick="document.getElementById('addPayment').showModal()">+ Add Payment</button>
    </div>
    <table class="table-compact">
        <thead><tr><th>ID</th><th>Student</th><th>Type</th><th>Amount</th><th>Commission</th><th>Status</th><th>Paid At</th><th>Action</th></tr></thead>
        <tbody>
        @forelse($payments as $payment)
            <tr>
                <td>#{{ $payment->id }}</td>
                <td>{{ optional($students->firstWhere('id', $payment->student_id))->full_name ?: '#'.$payment->student_id }}</td>
                <td>{{ $payment->type }}</td>
                <td>{{ $payment->currency }} {{ number_format((float) $payment->amount, 2) }}</td>
                <td>{{ number_format((float) $payment->commission_amount, 2) }}</td>
                <td><span class="badge {{ $payment->status }}">{{ ucfirst($payment->status) }}</span></td>
                <td>{{ $payment->paid_at ?: '-' }}</td>
                <td style="display:flex;gap:6px;">
                    <button type="button" class="secondary" onclick="document.getElementById('editPayment{{ $payment->id }}').showModal()">Edit</button>
                    <form method="POST" action="/finance/{{ $payment->id }}" onsubmit="return confirm('Delete payment?')">
                        @csrf
                        @method('DELETE')
                        <button class="secondary" type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="8">No payments found.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div style="margin-top:10px;">{{ $payments->links() }}</div>
</div>

<dialog id="addPayment" class="card" style="max-width:900px;">
    <h3 style="margin-top:0;">Add Payment</h3>
    <form method="POST" action="/finance">
        @csrf
        @include('finance.partials.form', ['payment' => null, 'students' => $students])
        <div style="margin-top:10px;"><button type="submit">Save</button></div>
    </form>
</dialog>

@foreach($payments as $payment)
<dialog id="editPayment{{ $payment->id }}" class="card" style="max-width:900px;">
    <h3 style="margin-top:0;">Edit Payment #{{ $payment->id }}</h3>
    <form method="POST" action="/finance/{{ $payment->id }}">
        @csrf
        @method('PUT')
        @include('finance.partials.form', ['payment' => $payment, 'students' => $students])
        <div style="margin-top:10px;"><button type="submit">Update</button></div>
    </form>
</dialog>
@endforeach
@endsection

