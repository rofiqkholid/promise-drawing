@extends('errors::minimal')

@section('title', __('Session Expired'))
@section('code', '419')

@section('message')
<div class="flex flex-col items-center justify-center text-center">
    <div class="text-2xl uppercase tracking-wider">
        Session Expired
    </div>
    <a href="{{ route('login') }}" class="px-6 py-3 bg-blue-600 font-semibold rounded hover:bg-blue-700 transition duration-200 hover:underline">

        CLICK HERE TO LOGIN
    </a>
</div>
@endsection