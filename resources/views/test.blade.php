@extends('layouts.app')
@section('content')
    <div x-data="{ open: false }">
        <button @click="open = true; console.log('Test modal clicked, open:', open)">Open Test Modal</button>
        <div x-show="open" x-cloak class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center">
            <div class="bg-white p-4 rounded-lg">
                <p>Test Modal</p>
                <button @click="open = false">Close</button>
            </div>
        </div>
    </div>
@endsection