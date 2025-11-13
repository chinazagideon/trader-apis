@extends('notification::emails.layout')

@section('content')
    <p style="font-size: 16px; margin-bottom: 15px; color: #374151;">
        We have successfully received your payment!
    </p>

    <!-- Payment Details -->
    <div style="background-color: white; padding: 20px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #10b981;">
        <h2 style="margin-top: 0; color: #10b981; font-size: 20px;">Payment Details</h2>
        <table style="width: 100%; border-collapse: collapse;">
            @if(isset($entity->amount))
            <tr>
                <td style="padding: 8px 0; font-weight: bold; color: #6b7280;">Amount:</td>
                <td style="padding: 8px 0; color: #111827; font-size: 18px; font-weight: bold;">{{ $entity->amount }}</td>
            </tr>
            @endif
            @if(isset($entity->payment_method))
            <tr>
                <td style="padding: 8px 0; font-weight: bold; color: #6b7280;">Payment Method:</td>
                <td style="padding: 8px 0; color: #111827;">{{ ucfirst($entity->payment_method) }}</td>
            </tr>
            @endif
            @if(isset($entity->transaction_id))
            <tr>
                <td style="padding: 8px 0; font-weight: bold; color: #6b7280;">Transaction ID:</td>
                <td style="padding: 8px 0; color: #111827;">#{{ $entity->transaction_id }}</td>
            </tr>
            @endif
            @if(isset($entity->created_at))
            <tr>
                <td style="padding: 8px 0; font-weight: bold; color: #6b7280;">Date:</td>
                <td style="padding: 8px 0; color: #111827;">{{ $entity->created_at->format('M d, Y H:i') }}</td>
            </tr>
            @endif
        </table>
    </div>

    <p style="font-size: 14px; color: #6b7280; margin-top: 20px;">
        Thank you for your payment! Your account has been updated accordingly. If you have any questions about this payment, please contact our support team.
    </p>
@endsection

