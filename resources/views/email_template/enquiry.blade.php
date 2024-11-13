<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation Confirmation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 600px;
            margin: 20px auto;
            background-color: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }
        .header {
            background-color: #000;
            text-align: center;
            padding: 15px;
            border-radius: 10px 10px 0 0;
        }
        .header img {
            max-width: 150px;
        }
        .content {
            padding: 20px;
            text-align: center;
        }
        .content h1 {
            color: #333;
        }
        .reservation-details {
            background-color: #333;
            color: white;
            padding: 10px;
            margin: 20px 0;
            text-align: center;
        }
        .reservation-details th, .reservation-details td {
            padding: 8px;
        }
        .info {
            text-align: center;
        }
        .footer {
            text-align: center;
            padding: 15px;
            background-color: #f4f4f4;
        }
        .app-buttons img {
            max-width: 120px;
            margin: 5px;
        }
        a {
            color: #e63946;
            text-decoration: none;
        }
        .review {
            margin-top: 15px;
        }
        img {
            display: block;
            margin: 0 auto; /* Centre-align all images */
        }

        .content p {
            font-size: 16px;
            color: #666;
            line-height: 24px;
        }

        .content a {
            display: inline-block;
            background-color: #f44336;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            font-weight: bold;
            border-radius: 5px;
            margin-top: 20px;
        }

        .content a:hover {
            background-color: #d7352b;
        }

        .content .link {
            margin-top: 30px;
            font-size: 14px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header with Logo -->

        <div class="content">
            <h1>{{$data->restaurant_name}}</h1>
            <h2>Thank you for your enquiry.</h2>

            <p>Dear <strong>{{ ucwords($data->first_name) }} {{ ucwords($data->last_name) }}</strong>,</p>
            <p>If you need to make any changes to your reservation, please call <strong>{{ $data->phone }}</strong>.</p>
            <p><em>Need help?</em> If there is anything else you would like to know or amend, please call <strong>{{ $data->phone }}</strong>.</p>


            <!-- User Information Section -->
            <div class="info">
                <p><strong>Customer Details</strong></p>
                <p>{{ ucwords($data->first_name) }} {{ ucwords($data->last_name) }}</p>
                <p>{{ $data->email }}</p>
                <p>{{ $data->phone }}</p>
            </div>
        </div>
        <div class="footer">
            <div class="app-buttons">
                <a href="{{url('logo.png')}}"><img src="{{ url('logo.png') }}" alt="Tablebookings.co.uk"></a>
            </div>
            <p>Thank you for your reservation with <strong>{{ ucwords($data->restaurant_name) }}</strong>.</p>
            <p>Tel: {{ $data->phone }}</p>
        </div>

    </div>
</body>
</html>
