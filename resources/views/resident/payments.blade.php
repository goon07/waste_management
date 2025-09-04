@extends('layouts.app')

@section('content')
    <section class="py-20 bg-gray-100">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-gray-800 mb-6">Payments</h2>
            @if ($payments->isEmpty())
                <p>No payments found.</p>
            @else
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Payment History</h3>
                    <ul class="divide-y divide-gray-200">
                        @foreach ($payments as $payment)
                            <li class="py-4">
                                <p>Amount: {{ $payment->amount }}</p>
                                <p class="text-sm text-gray-600">Status: {{ ucfirst($payment->status) }}</p>
                                <p class="text-sm text-gray-600">Date: {{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d H:i') : 'N/A' }}</p>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </section>
@endsection