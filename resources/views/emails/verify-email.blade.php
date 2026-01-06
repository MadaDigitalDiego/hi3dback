<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification de votre adresse e-mail</title>
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
                <h1>Vérification de compte</h1>
                <p class="subtitle">{{ config('app.name') }}</p>
            </div>
            
            <!-- Content -->
            <div class="content">
                <p class="greeting">
                    Bonjour <strong>{{ $user->name ?? $user->first_name ?? 'utilisateur' }}</strong>,
                </p>
                
                <p class="message">
                    Merci de vous être inscrit sur <strong>{{ config('app.name') }}</strong> ! 
                    Nous sommes ravis de vous compter parmi nos membres.
                </p>
                
                <p class="message">
                    Pour finaliser la création de votre compte et accéder à toutes nos fonctionnalités, 
                    nous devons vérifier votre adresse e-mail. Cliquez sur le bouton ci-dessous :
                </p>
                
                <div class="button-wrapper">
                    <a href="{{ $verificationUrl }}" class="button">Vérifier mon adresse e-mail</a>
                </div>
                
                <div class="info-box">
                    <p><strong>🔒 Lien sécurisé:</strong> Ce lien expire dans 60 minutes pour des raisons de sécurité.</p>
                </div>
                
                <p class="message">
                    Si le bouton ne fonctionne pas, copiez et collez le lien ci-dessous dans votre navigateur :
                </p>
                <p class="message" style="word-break: break-all; font-size: 13px; color: #667eea;">
                    <a href="{{ $verificationUrl }}" style="color: #667eea; text-decoration: none;">{{ $verificationUrl }}</a>
                </p>
                
                <p class="message" style="margin-top: 30px;">
                    Si vous n'avez pas créé de compte sur {{ config('app.name') }}, vous pouvez ignorer cet e-mail en toute sécurité.
                </p>
            </div>
            
            <!-- Footer -->
            <div class="footer">
                <p><strong>{{ config('app.name') }}</strong></p>
                <p>Cet e-mail a été envoyé à {{ $user->email ?? 'votre adresse e-mail' }}</p>
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Tous droits réservés.</p>
            </div>
        </div>
    </div>
</body>
</html>
