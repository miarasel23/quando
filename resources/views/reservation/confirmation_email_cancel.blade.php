<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $reservation->status == 'confirmed' ? '#4CAF50' :
        ($reservation->status == 'rejected' ? '#f44336' :
        ($reservation->status == 'cancelled' ? '#FF9800' : '#000')) }}">
     {{ $reservation->status == 'confirmed' ? 'Reservation has been confirmed' :
        ($reservation->status == 'rejected' ? 'Reservation has been rejected' :
        ($reservation->status == 'cancelled' ? 'Reservation has been cancelled' : 'Something went wrong!!')) }}</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f4f4; font-family: Arial, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f4f4f4; padding: 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" border="0" style="background-color:#ffffff; border-radius:10px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);">
                    <!-- Header with Logo -->
                    <tr>
                        <td align="center" style="background-color:#000; padding:15px; border-radius:10px 10px 0 0;">
                            <img src="{{url('logo.png')}}" alt="Tablebookings.co.uk" style="max-width:150px;">
                        </td>
                    </tr>

                    <!-- Reservation Confirmation -->
                    <tr>
                        <td align="center" style="padding:20px;">
                            <h1 style="color:#333;">{{ $reservation->restaurant->name }}</h1>
                            <h2 style="color:
                            {{ $reservation->status == 'confirmed' ? '#4CAF50' :
                               ($reservation->status == 'rejected' ? '#f44336' :
                               ($reservation->status == 'cancelled' ? '#FF9800' : '#000')) }}">
                            {{ $reservation->status == 'confirmed' ? 'Reservation has been confirmed' :
                               ($reservation->status == 'rejected' ? 'Reservation has been rejected' :
                               ($reservation->status == 'cancelled' ? 'Reservation has been cancelled' : 'Something went wrong!!')) }}
                           </h2>

                            <h4>Reservation ID: {{ $reservation->reservation_id }}</h4>
                            <p>Dear <strong>{{ ucwords($reservation->guest_information->first_name) }} {{ ucwords($reservation->guest_information->last_name) }}</strong>,</p>
                            <p>If you need to make any changes, please call <strong>{{ $reservation->restaurant->phone }}</strong>.</p>
                        </td>
                    </tr>

                    <!-- Reservation Details -->
                    <tr>
                        <td align="center">
                            <table width="90%" cellpadding="8" cellspacing="0" border="0" style="background-color:#333; color:white; text-align:center; margin: 20px auto; border-radius:5px;">
                                <tr>
                                    <th style="padding:8px;">Reservation Date:</th>
                                    <td>{{ $reservation->reservation_date }}</td>
                                </tr>
                                <tr>
                                    <th style="padding:8px;">Time:</th>
                                    <td>{{ $reservation->reservation_time }}</td>
                                </tr>
                                <tr>
                                    <th style="padding:8px;">Guests:</th>
                                    <td>{{ $reservation->number_of_people }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Customer Details -->
                    <tr>
                        <td align="center">
                            <table width="90%" cellpadding="8" cellspacing="0" border="0" style="text-align:center; margin: 10px auto;">
                                <tr>
                                    <td colspan="2" style="font-weight:bold;">Customer Details</td>
                                </tr>
                                <tr>
                                    <td colspan="2">{{ ucwords($reservation->guest_information->first_name) }} {{ ucwords($reservation->guest_information->last_name) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="2">{{ $reservation->guest_information->email }}</td>
                                </tr>
                                <tr>
                                    <td colspan="2">{{ $reservation->guest_information->phone }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
