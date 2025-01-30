<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation Confirmation</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f2f2f2; font-family: Arial, sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" border="0" align="center">
        <tr>
            <td align="center">
                <table width="600px" cellpadding="0" cellspacing="0" border="0" style="background-color: #ffffff; padding: 20px;">
                    <!-- Logo Section -->
                    <tr>
                        <td align="center" style="padding-bottom: 20px;">
                            <a href="{{url('logo.png')}}"><img src="{{ url('logo.png') }}" alt="tablebookings.co.uk" style="display: block; width: 180px;"></a>
                        </td>
                    </tr>
                    <!-- Message Section -->
                    <tr>
                        <td align="center" style="font-size: 18px; font-weight: bold; color: #333;">
                            Dear <strong>{{ ucwords($data->first_name) }} {{ ucwords($data->last_name) }}</strong>, <br>
                            Thank you for contacting with {{$data->restaurant_name}}. We will get back to you soon. <br>
                            <em>Need help?</em> If there is anything else you would like to know or amend, please call <strong>{{ $data->phone }}</strong>.
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding: 20px 0; font-size: 16px; font-weight: bold; color: #333;">
                            Here are your enquiry details:
                        </td>
                    </tr>
                    <!-- Enquiry Details -->
                    <tr>
                        <td style="padding: 0 40px; font-size: 14px; color: #333;">
                            <p>{{ ucwords($data->first_name) }} {{ ucwords($data->last_name) }}</p>
                            <p>{{ $data->email }}</p>
                            <p>{{ $data->phone }}</p>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td align="center" style="padding-top: 20px; font-size: 12px; color: #666;">
                            &copy; 2025 ChefOnline. All rights reserved.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

</body>
</html>

