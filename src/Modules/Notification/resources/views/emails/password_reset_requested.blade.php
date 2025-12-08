@extends('notification::emails.layout')

@section('content')
    <h1>Password Reset Request</h1>
    <p>Hello {{ $name ?? 'User' }},</p>
    <p>You have requested to reset your password. Use the code below:</p>

    <div style="background: #f5f5f5; padding: 20px; text-align: center; font-size: 24px; font-weight: bold; letter-spacing: 4px; margin: 20px 0;">
        {{ $token }}
    </div>

    {{-- <p>Or click the button below to reset your password:</p> --}}
    {{-- <a href="{{ $reset_url }}" style="display: inline-block; padding: 12px 24px; background: #007bff; color: white; text-decoration: none; border-radius: 4px;">
        {{ $action_text ?? 'Reset Password' }}
    </a> --}}

    <p style="margin-top: 20px; color: #666; font-size: 12px;">
        This code will expire in 1 hour. If you didn't request this, please ignore this email.
    </p>
@endsection
