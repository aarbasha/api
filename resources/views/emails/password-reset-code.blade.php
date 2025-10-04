@extends('emails.MainLayout')
@section('title', 'Password Reset Code')


@section('content')
    <div class="flex justify-center items-center min-h-screen">
        <div class="bg-black rounded-lg p-8 w-full max-w-md flex flex-col justify-center items-center">
            <img src="{{ asset('images/logo1.png') }}" alt="Logo" width="200" height="200">
            <div class="text-center">
                <h1 class="text-4xl font-bold mb-4">Password Reset Code</h1>
                <p class="mb-4">Your code for reset password :</p>
                <p class="text-red-500 text-3xl font-bold mb-6  ">{{ $code }}</p>
                <div class="flex items-center justify-center mb-4">
                    <i class="fas fa-envelope text-gray-400 mr-2"></i>
                    <div class=" my-4 font-bold text-blue-600">{{ $email }}</div>
                </div>
                <p>Please enter this code to reset your password.</p>
            </div>
        </div>
    </div>
@endsection
