<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email - ChatooAI</title>
    <style>
        body {
            background-color: #060d1a;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #cbd5e1;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: rgba(13, 20, 37, 0.9);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.3);
            text-align: center;
        }
        .logo {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 30px;
        }
        .logo-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: linear-gradient(135deg, #059669, #10b981);
            display: inline-block;
            line-height: 40px;
            color: white;
            font-weight: bold;
            font-size: 20px;
            text-align: center;
        }
        .logo-text {
            font-size: 24px;
            font-weight: 800;
            color: #ffffff;
            margin: 0;
            display: inline-block;
            vertical-align: middle;
            margin-left: 8px;
        }
        h1 {
            color: #ffffff;
            font-size: 22px;
            font-weight: 700;
            margin-top: 0;
            margin-bottom: 16px;
        }
        p {
            font-size: 15px;
            line-height: 1.6;
            margin-bottom: 24px;
            color: #94a3b8;
        }
        .otp-box {
            background-color: #070e1f;
            border: 1px solid rgba(16, 185, 129, 0.2);
            border-radius: 12px;
            padding: 20px 40px;
            margin: 30px 0;
            display: inline-block;
        }
        .otp-code {
            font-size: 36px;
            font-weight: 800;
            letter-spacing: 6px;
            color: #10b981;
            margin: 0;
        }
        .footer {
            margin-top: 40px;
            font-size: 12px;
            color: #475569;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            padding-top: 20px;
        }
        .footer a {
            color: #10b981;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <span class="logo-icon">C</span>
            <span class="logo-text">ChatooAI</span>
        </div>
        <h1>Verify Your Email Address</h1>
        <p>Thanks for signing up for ChatooAI! To complete your registration and create your workspace, please use the 6-digit verification code below:</p>
        
        <div class="otp-box">
            <h2 class="otp-code">{{ $otpCode }}</h2>
        </div>
        
        <p>This verification code is valid for <strong>15 minutes</strong>. If the code expires, you can request a new one from the verification screen.</p>
        <p style="font-size: 13px; color: #475569;">If you did not create an account on ChatooAI, you can safely ignore this email.</p>
        
        <div class="footer">
            <p>© {{ date('Y') }} ChatooAI. All rights reserved.<br>
            <a href="{{ config('app.url') }}">www.chatooai.com</a></p>
        </div>
    </div>
</body>
</html>
