<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #e1e1e1;
            border-radius: 1px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: #1e40af;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 1px;
            font-weight: bold;
            margin-top: 20px;
        }

        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #777;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h2>Password Reset Request</h2>
        </div>
        <p>Hello,</p>
        <p>We received a request to reset your password for your PROMISE App account. Click the button below to reset it:</p>
        <div style="text-align: center;">
            <a href="{{ $resetLink }}" class="btn">Reset Password</a>
        </div>
        <p>If you did not request a password reset, please ignore this email.</p>
        <p>This link will expire in 60 minutes.</p>
        <div class="footer">
            <p>&copy; {{ date('Y') }} ICT Dept - Summit Adyawinsa Indonesia. All rights reserved.</p>
        </div>
    </div>
</body>

</html>