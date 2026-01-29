<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email Address</title>
    <style>
        /* Reset styles */
        body, html { margin: 0; padding: 0; width: 100%; height: 100%; }
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4; }
        
        /* Container */
        .email-wrapper { width: 100%; background-color: #f4f4f4; padding: 20px 0; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        
        /* Header */
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 40px 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 28px; font-weight: bold; }
        .header .subtitle { font-size: 16px; opacity: 0.9; margin-top: 10px; }
        
        /* Content */
        .content { padding: 40px 30px; }
        .greeting { font-size: 18px; color: #333; margin-bottom: 20px; }
        .message { font-size: 16px; color: #555; margin-bottom: 30px; line-height: 1.8; }
        
        /* Button */
        .button-wrapper { text-align: center; margin: 30px 0; }
        .button { display: inline-block; padding: 16px 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; border-radius: 6px; font-size: 16px; font-weight: bold; }
        .button:hover { opacity: 0.9; }
        
        /* Info box */
        .info-box { background-color: #f8f9fa; border-left: 4px solid #667eea; padding: 20px; margin: 20px 0; border-radius: 0 4px 4px 0; }
        .info-box p { margin: 0; color: #555; font-size: 14px; }
        
        /* Footer */
        .footer { background-color: #f8f9fa; padding: 30px; text-align: center; border-top: 1px solid #eee; }
        .footer p { margin: 5px 0; color: #888; font-size: 14px; }
        .footer a { color: #667eea; text-decoration: none; }
        
        /* Responsive */
        @media only screen and (max-width: 600px) {
            .container { margin: 0 10px; }
            .content, .header { padding: 20px; }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="container">
            <!-- Header -->
            <div class="header">
                <h1>Account Verification</h1>
                <p class="subtitle">{{ config('app.name') }}</p>
            </div>
            
            <!-- Content -->
            <div class="content">
                <p class="greeting">
                    Hello <strong>{{ $user->name ?? $user->first_name ?? 'user' }}</strong>,
                </p>
                
                <p class="message">
                    Thank you for signing up on <strong>{{ config('app.name') }}</strong>! 
                    We are thrilled to have you as a member.
                </p>
                
                <p class="message">
                    To complete your account creation and access all our features, 
                    we need to verify your email address. Click the button below:
                </p>
                
                <div class="button-wrapper">
                    <a href="{{ $verificationUrl }}" class="button">Verify My Email Address</a>
                </div>
                
                <div class="info-box">
                    <p><strong>🔒 Secure Link:</strong> This link expires in 60 minutes for security reasons.</p>
                </div>
                
                <p class="message">
                    If the button doesn't work, copy and paste the link below into your browser:
                </p>
                <p class="message" style="word-break: break-all; font-size: 13px; color: #667eea;">
                    <a href="{{ $verificationUrl }}" style="color: #667eea; text-decoration: none;">{{ $verificationUrl }}</a>
                </p>
                
                <p class="message" style="margin-top: 30px;">
                    If you did not create an account on {{ config('app.name') }}, you can safely ignore this email.
                </p>
            </div>
            
            <!-- Footer -->
            <div class="footer">
                <p><strong>{{ config('app.name') }}</strong></p>
                <p>This email was sent to {{ $user->email ?? 'your email address' }}</p>
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
