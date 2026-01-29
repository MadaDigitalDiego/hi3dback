<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #f44336; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 5px 5px; }
        .plan-details { background: white; padding: 20px; border-radius: 5px; margin: 20px 0; }
        .footer { text-align: center; margin-top: 30px; color: #666; font-size: 0.9em; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Subscription Cancellation Confirmation</h1>
        </div>
        
        <div class="content">
            <p>Hello <strong>{{ $user->name }}</strong>,</p>
            
            <p>We confirm the cancellation of your subscription.</p>
            
            <div class="plan-details">
                <h3>Cancelled Subscription Details</h3>
                <p><strong>Plan:</strong> {{ $plan->title ?? $plan->name }}</p>
                <p><strong>Cancellation Date:</strong> {{ $cancellationDate->format('d/m/Y') }}</p>
                <p><strong>Status:</strong> Cancelled</p>
            </div>
            
            <p>Your access to premium features will remain active until the end of your current billing period.</p>
            
            <p>If you have changed your mind, you can reactivate your subscription at any time from your personal dashboard.</p>
            
            <p>We hope to see you again soon!</p>
        </div>
        
        <div class="footer">
            <p>For any questions, contact our support at support@yourdomain.com</p>
            <p>&copy; {{ date('Y') }} Your Company</p>
        </div>
    </div>
</body>
</html>
