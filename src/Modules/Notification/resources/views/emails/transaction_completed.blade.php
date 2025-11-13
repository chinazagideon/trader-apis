@extends('notification::emails.layout')

@section('content')
    <p style="font-size: 16px; margin-bottom: 15px; color: #374151;">
        Your transaction has been completed successfully!
    </p>

    <!-- Transaction Details -->
    <div style="background-color: white; padding: 20px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #10b981;">
        <h2 style="margin-top: 0; color: #10b981; font-size: 20px;">Transaction Details</h2>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; font-weight: bold; color: #6b7280;">Transaction ID:</td>
                <td style="padding: 8px 0; color: #111827;">#{{ $entityId }}</td>
            </tr>
            @if(isset($entity->amount))
            <tr>
                <td style="padding: 8px 0; font-weight: bold; color: #6b7280;">Amount:</td>
                <td style="padding: 8px 0; color: #111827; font-weight: bold;">{{ $entity->amount }}</td>
            </tr>
            @endif
            @if(isset($entity->type))
            <tr>
                <td style="padding: 8px 0; font-weight: bold; color: #6b7280;">Type:</td>
                <td style="padding: 8px 0; color: #111827;">{{ ucfirst($entity->type) }}</td>
            </tr>
            @endif
            @if(isset($entity->completed_at))
            <tr>
                <td style="padding: 8px 0; font-weight: bold; color: #6b7280;">Completed:</td>
                <td style="padding: 8px 0; color: #111827;">{{ $entity->completed_at->format('M d, Y H:i') }}</td>
            </tr>
            @endif
        </table>
    </div>

    <p style="font-size: 14px; color: #6b7280; margin-top: 20px;">
        Your transaction has been processed and completed. If you have any questions, please don't hesitate to contact our support team.
    </p>
@endsection

