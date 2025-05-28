<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>New Booking Notification</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
        }
        .card-header {
            background-color: #343a40;
            color: white;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
            text-align: center;
            padding: 30px 20px;
        }
        .card-body {
            padding: 30px;
        }
        .footer {
            background-color: #f1f3f5;
            padding: 15px;
            text-align: center;
            border-bottom-left-radius: 15px;
            border-bottom-right-radius: 15px;
            font-size: 13px;
            color: #6c757d;
        }
        th{
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-8">

                <div class="card">
                    <div class="card-header">
                        <h2>📦 New Booking Received</h2>
                        <p class="mb-0">A customer has placed a new order</p>
                    </div>

                    <div class="card-body">
                        <p>Dear {{$materialOwner->name}},</p>

                        <p>You have received a new booking. Please review the details below and begin processing the order promptly.</p>

                        <table class="table table-bordered mt-4">
                            <tbody>
                                <tr>
                                    <th scope="row">Customer Name</th>
                                    <td>{{ $user->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th scope="row">Material</th>
                                    <td>{{ $material->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th scope="row">Booking Type</th>
                                    <td>{{ ucfirst($booking->booking_type ?? 'N/A') }}</td>
                                </tr>
                                <tr>
                                    <th scope="row">Units</th>
                                    <td>{{ $booking->unit ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th scope="row">Quantity</th>
                                    <td>{{ $booking->quantity ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th scope="row">Date</th>
                                    <td>
                                        {{ isset($booking->date) ? \Carbon\Carbon::parse($booking->date)->format('F d, Y') : 'N/A' }}
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Time</th>
                                    <td>
                                        {{ isset($booking->time) ? \Carbon\Carbon::parse($booking->time)->format('h:i A') : 'N/A' }}
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Delivery Address</th>
                                    <td>{{ $booking->address ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th scope="row">Total Price</th>
                                    <td><strong>${{ number_format($booking->total_price ?? 0, 2) }}</strong></td>
                                </tr>
                                <tr>
                                    <th scope="row">Status</th>
                                    <td>
                                        <span class="badge bg-warning text-dark">{{ ucfirst($booking->status ?? 'pending') }}</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <p class="mt-4">Please take the necessary action to fulfill this order.</p>

                        <p>Thanks,<br><strong>Construction Material System</strong></p>
                    </div>

                    <div class="footer">
                        &copy; {{ now()->year }} Construction Material Portal. All rights reserved.
                    </div>
                </div>

            </div>
        </div>
    </div>
</body>
</html>
