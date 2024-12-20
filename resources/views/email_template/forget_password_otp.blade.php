<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1" name="viewport">
    <meta name="x-apple-disable-message-reformatting">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="telephone=no" name="format-detection">
    <title>Activate Your Account</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #3d4a49;
            font-family: Arial, sans-serif;
        }

        table {
            max-width: 600px;
            margin: 50px auto;
            background-color: #ffffff;
            border-radius: 10px;
            overflow: hidden;
        }

        .header {
            background-color: #29c2d8;
            text-align: center;
            padding: 20px;
        }

        .header h1 {
            color: white;
            font-size: 24px;
            margin: 0;
            text-transform: uppercase;
        }

        .content {
            padding: 30px;
            text-align: center;
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

        .footer {
            text-align: center;
            background-color: #3d4a49;
            color: white;
            padding: 20px;
            font-size: 14px;
        }

        .footer p {
            margin: 5px 0;
        }

        .footer a {
            color: white;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <table>
        <tr>
            <td class="header">
                <h1> OTP:  {{$otp}} </h1>
            </td>
        </tr>
        <tr>
            <td class="content">
                <p>Keep your OTP secure – don't share it with anyone! This OTP is only valid for the next 5 minutes. </p>

            </td>
        </tr>
        <tr>
            <td class="footer">
                <p>© Tablebookings, 2024</p>
            </td>
        </tr>
    </table>
</body>

</html>
