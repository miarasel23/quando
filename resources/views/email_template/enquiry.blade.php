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
            <td align="center" style="padding: 20px 0;">
                <table width="600" cellpadding="0" cellspacing="0" border="0" style="background-color: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);">

                    <!-- Logo Section -->
                    <tr>
                        <td align="center" style="padding-bottom: 20px;">
                            <a href="{{ url('logo.png') }}">
                                <img src="{{ url('logo.png') }}" alt="tablebookings.co.uk" style="display: block; width: 180px;">
                            </a>
                        </td>
                    </tr>

                    <!-- Message Section -->
                    <tr>
                        <td align="left" style="font-size: 18px; color: #333; padding: 0 20px;">
                            <strong>Dear {{ ucwords($data->first_name) }} {{ ucwords($data->last_name) }}</strong>, <br><br>
                            Thank you for contacting <strong>{{ $data->restaurant_name }}</strong>. We will get back to you soon.
                        </td>
                    </tr>

                    <!-- Enquiry Details Title -->
                    <tr>
                        <td align="left" style="padding: 20px; font-size: 16px; font-weight: bold; color: #333;">
                            Here are your enquiry details:
                        </td>
                    </tr>

                    <!-- Enquiry Details -->
                    <tr>
                        <td style="padding: 0 40px; font-size: 14px; color: #333;">
                            <p><strong>Name:</strong> {{ ucwords($data->first_name) }} {{ ucwords($data->last_name) }}</p>
                            <p><strong>Email:</strong> {{ $data->email ? $data->email : 'N/A' }}</p>
                            <p><strong>Phone:</strong> {{ $data->phone ? $data->phone : 'N/A' }}</p>
                            <p><strong>Restaurant Name:</strong> {{ $data->restaurant_name ? $data->restaurant_name : 'N/A' }}</p>
                            <p><strong>Post Code:</strong> {{ $data->post_code ? $data->post_code : 'N/A' }}</p>
                            <p><strong>Message:</strong> {{ $data->message ? $data->message : 'N/A' }}</p>
                        </td>
                    </tr>

                    <!-- Help Section -->
                    <tr>
                        <td align="center" style="padding: 20px; font-size: 16px; font-weight: bold; color: #333;">
                            <em>Need help?</em><br>
                            If there is anything else you would like to know or amend, please call
                            <strong>{{ $data->phone ? $data->phone : 'our support team' }}</strong>.
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center" style="padding: 20px; font-size: 12px; color: #666;">
                            &copy; 2025 ChefOnline. All rights reserved.
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>
