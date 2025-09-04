@extends('layouts.app')

@section('content')
    <section class="py-20 bg-gray-100">
<div id="requests" class="tab-pane" data-intro="Manage pending council requests here">
    <div class="mt-6 bg-white rounded-xl shadow-md p-6">
        <h3 class="text-xl font-bold text-gray-800 mb-4">Pending Council Requests</h3>
        @if ($requests->isEmpty())
            <p>No pending requests.</p>
        @else
            <ul class="divide-y divide-gray-200">
                @foreach ($requests as $request)
                    <li class="py-4 flex justify-between items-center">
                        <div>
                            <p>{{ $request->user->name ?? 'Unknown' }}</p>
                            <p class="text-sm text-gray-600">Requested: {{ \Carbon\Carbon::parse($request->requested_at)->format('Y-m-d H:i') }}</p>
                            <form action="{{ route('council.request.approve', $request->id) }}" method="POST">
                                @csrf
                                <select name="collector_company_id" class="px-4 py-2 border border-gray-300 rounded-lg">
                                    <option value="">Select Collector Company</option>
                                    @foreach ($collectorCompanies as $company)
                                        <option value="{{ $company->id }}" {{ $request->collector_company_id == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                                    @endforeach
                                </select>
                                @error('collector_company_id')
                                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                                @enderror
                                <button type="submit" class="text-green-600 hover:underline ml-2">Approve</button>
                            </form>
                        </div>
                        <form action="{{ route('council.request.reject', $request->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="text-red-600 hover:underline">Reject</button>
                        </form>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
  </section>
@endsection