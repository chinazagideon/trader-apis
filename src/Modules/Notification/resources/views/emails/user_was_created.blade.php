@extends('notification::emails.layout')

@section('content')
    <p style="font-size: 16px; margin-bottom: 15px; color: #374151;">
        Welcome to our platform! We're excited to have you on board.
    </p>

    <!-- Welcome Message -->
    <div style="background-color: white; padding: 20px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #4F46E5;">
        <h2 style="margin-top: 0; color: #4F46E5; font-size: 20px;">Getting Started</h2>
        <p style="color: #374151; margin-bottom: 15px;">
            Your account has been successfully created. Here's what you can do next:
        </p>
        <ul style="color: #374151; padding-left: 20px;">
            <li style="margin-bottom: 10px;">Complete your profile</li>
            <li style="margin-bottom: 10px;">Explore our features</li>
            <li style="margin-bottom: 10px;">Start using our services</li>
        </ul>
    </div>

    @if(isset($entity->email))
    <div style="background-color: #f3f4f6; padding: 15px; border-radius: 5px; margin: 20px 0;">
        <p style="margin: 0; font-size: 14px; color: #6b7280;">
            <strong>Account Email:</strong> {{ $entity->email }}
        </p>
    </div>
    @endif

    <p style="font-size: 14px; color: #6b7280; margin-top: 20px;">
        If you have any questions or need assistance, our support team is here to help. Don't hesitate to reach out!
    </p>
@endsection

