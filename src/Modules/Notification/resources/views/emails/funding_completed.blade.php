@extends('notification::emails.layout')

@section('content')
    <p style="font-size: 16px; margin-bottom: 15px; color: #374151;">
        Your funding request has been completed successfully!
    </p>

    <!-- Transaction Details -->
    <div style="background-color: white; padding: 20px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #10b981;">
        <h2 style="margin-top: 0; color: #10b981; font-size: 20px;">Funding Details</h2>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; font-weight: bold; color: #6b7280;">Funding ID:</td>
                <td style="padding: 8px 0; color: #111827;">#{{ $entityId }}</td>
            </tr>
            @if(isset($entity->fiat_amount))
            <tr>
                <td style="padding: 8px 0; font-weight: bold; color: #6b7280;">Amount:</td>
                <td style="padding: 8px 0; color: #111827; font-weight: bold;">{{ $entity->fiat_amount }}</td>
            </tr>
            @endif
            @if(isset($entity->notes) && $entity->notes !== null)
            <tr>
                <td style="padding: 8px 0; font-weight: bold; color: #6b7280;">Notes:</td>
                <td style="padding: 8px 0; color: #111827;">{{ $entity->notes  }}</td>
            </tr>
            @endif
            @if(isset($entity->status) && $entity->status !== null)
            <tr>
                <td style="padding: 8px 0; font-weight: bold; color: #6b7280;">Status:</td>
                <td style="padding: 8px 0; color: #111827;">{{ ucfirst($entity->status) }}</td>
            </tr>
            @endif
        </table>
    </div>

    <p style="font-size: 14px; color: #6b7280; margin-top: 20px;">
        Your funding request has been completed. If you have any questions, please don't hesitate to contact our support team.
    </p>
@endsection

