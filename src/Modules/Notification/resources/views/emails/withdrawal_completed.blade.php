@extends('notification::emails.layout')

@section('content')
    <p style="font-size: 16px; margin-bottom: 15px; color: #374151;">
        Your withdrawal request has been completed successfully!
    </p>

    <!-- Transaction Details -->
    <div style="background-color: white; padding: 20px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #10b981;">
        <h2 style="margin-top: 0; color: #10b981; font-size: 20px;">Withdrawal Details</h2>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; font-weight: bold; color: #6b7280;">Transaction Reference:</td>
                <td style="padding: 8px 0; color: #111827;">{{ $entity->uuid ?? 'N/A' }}</td>
            </tr>
            @if(isset($entity->amount) && isset($entity->currency))
            <tr>
                <td style="padding: 8px 0; font-weight: bold; color: #6b7280;">Amount:</td>
                <td style="padding: 8px 0; color: #111827; font-weight: bold;">{{ number_format($entity->amount, 8) }} {{ $entity->currency->code ?? 'N/A' }}</td>
            </tr>
            @endif
            @if(isset($entity->fiat_amount) && isset($entity->fiatCurrency) && ($entity->fiatCurrency->code !== $entity->currency->code))
            <tr>
                <td style="padding: 8px 0; font-weight: bold; color: #6b7280;">Fiat Amount:</td>
                <td style="padding: 8px 0; color: #111827; font-weight: bold;">{{ number_format($entity->fiat_amount, 2) }} {{ $entity->fiatCurrency->code ?? 'N/A' }}</td>
            </tr>
            @endif
            {{-- @if(isset($entity->payments) && $entity->payments->count() > 0)
            <tr>
                <td style="padding: 8px 0; font-weight: bold; color: #6b7280;">Payment Currency:</td>
                <td style="padding: 8px 0; color: #111827;">
                    @foreach($entity->payments as $payment)
                        {{ number_format($payment->amount, 2) }} {{ $payment->currency->code ?? 'N/A' }}@if(!$loop->last), @endif
                    @endforeach
                </td>
            </tr>
            @endif --}}
            @if(isset($entity->status) && $entity->status !== null)
            <tr>
                <td style="padding: 8px 0; font-weight: bold; color: #6b7280;">Status:</td>
                <td style="padding: 8px 0; color: #111827;">{{ ucfirst($entity->status) }}</td>
            </tr>
            @endif
            @if(isset($entity->notes) && $entity->notes !== null)
            <tr>
                <td style="padding: 8px 0; font-weight: bold; color: #6b7280;">Notes:</td>
                <td style="padding: 8px 0; color: #111827;">{{ $entity->notes }}</td>
            </tr>
            @endif
        </table>
    </div>

    <p style="font-size: 14px; color: #6b7280; margin-top: 20px;">
        Your withdrawal request has been completed. If you have any questions, please don't hesitate to contact our support team.
    </p>
@endsection

