<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? 'Notification' }}</title>
</head>

<body
    style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">

    <!-- Header -->
    <div style="background-color: #4F46E5; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0;">
        <img src="https://www.shutterstock.com/image-vector/google-icon-made-manually-based-600w-2317500299.jpg"
            alt="Logo" style="width: 100px; height: 100px;">
        <h1 style="margin: 0; font-size: 24px;">{{ $title ?? 'Notification' }}</h1>
    </div>

    <!-- Content -->
    <div style="background-color: #f9fafb; padding: 30px; border: 1px solid #e5e7eb; border-top: none;">

        @if (isset($greeting))
            <p style="font-size: 16px; margin-bottom: 20px; color: #374151;">{{ $greeting }}</p>
        @endif

        @yield('content')

        <!-- Action Button -->
        {{-- @if (isset($action_url))
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $action_url }}"
                    style="display: inline-block; background-color: #4F46E5; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 16px;">
                    {{ $action_text ?? 'View Details' }}
                </a>
            </div>
        @endif --}}

        <!-- Footer -->
        <div
            style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; font-size: 14px; color: #6b7280;">
            <p style="margin: 5px 0;">Thank you for using our service!</p>
            <p style="margin: 5px 0;">If you have any questions, please contact our support team.</p>
        </div>

    </div>

    <!-- Footer -->
    <div style="text-align: center; padding: 20px; color: #6b7280; font-size: 12px;">
        @if (isset($notifiable_client_name))
            <p style="margin: 5px 0;">© {{ date('Y') }} {{ $notifiable_client_name ?? '' }}. All rights reserved.
            </p>
        @else
            <p style="margin: 5px 0;">© {{ date('Y') }} All rights reserved.</p>
        @endif
    </div>

</body>

</html>
