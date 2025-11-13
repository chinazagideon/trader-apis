@extends('notification::emails.layout')

@section('content')
    <p style="font-size: 16px; margin-bottom: 15px; color: #374151;">
        Your investment has been created successfully!
    </p>

    <!-- Investment Details -->
    <div style="background-color: white; padding: 20px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #4F46E5;">
        <h2 style="margin-top: 0; color: #4F46E5; font-size: 20px;">Investment Details</h2>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; font-weight: bold; color: #6b7280;">Investment ID:</td>
                <td style="padding: 8px 0; color: #111827;">#{{ $entityId }}</td>
            </tr>
            @if(isset($entity->amount))
            <tr>
                <td style="padding: 8px 0; font-weight: bold; color: #6b7280;">Amount:</td>
                <td style="padding: 8px 0; color: #111827;">{{ $entity->amount }}</td>
            </tr>
            @endif
            @if(isset($entity->created_at))
            <tr>
                <td style="padding: 8px 0; font-weight: bold; color: #6b7280;">Date:</td>
                <td style="padding: 8px 0; color: #111827;">{{ $entity->created_at->format('M d, Y H:i') }}</td>
            </tr>
            @endif
            @if(isset($entity->status))
            <tr>
                <td style="padding: 8px 0; font-weight: bold; color: #6b7280;">Status:</td>
                <td style="padding: 8px 0; color: #111827;">{{ ucfirst($entity->status) }}</td>
            </tr>
            @endif
        </table>
    </div>

    <p style="font-size: 14px; color: #6b7280; margin-top: 20px;">
        You will receive updates about your investment via email. You can also check the status anytime in your dashboard.
    </p>
@endsection

