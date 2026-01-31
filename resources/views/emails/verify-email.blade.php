<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification de votre adresse e-mail</title>
</head>
<body style="margin: 0; padding: 0; width: 100%; height: 100%; font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4;">
    <div style="width: 100%; background-color: #f4f4f4; padding: 20px 0;">
        <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">

            <!-- Header -->
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 40px 30px; text-align: center;">
                <h1 style="margin: 0; font-size: 28px; font-weight: bold; text-align: center;">Vérification de compte</h1>
                <p style="font-size: 16px; opacity: 0.9; margin-top: 10px; text-align: center;">Hi3D</p>
            </div>

            <!-- Content -->
            <div style="padding: 40px;">
                <p style="font-size: 18px; color: #333; margin-bottom: 20px;">
                    Bonjour <strong>{{ $user->name ?? $user->first_name ?? 'utilisateur' }}</strong>,
                </p>

                <p style="font-size: 16px; color: #555; margin-bottom: 30px; line-height: 1.8;">
                    Merci de vous être inscrit sur <strong>Hi3D</strong> !
                    Nous sommes ravis de vous compter parmi nos membres.
                </p>

                <p style="font-size: 16px; color: #555; margin-bottom: 30px; line-height: 1.8;">
                    Pour finaliser la création de votre compte et accéder à toutes nos fonctionnalités,
                    nous devons vérifier votre adresse e-mail. Cliquez sur le bouton ci-dessous :
                </p>

                <div style="text-align: center; margin: 30px 0;">
                    <a href="{{ $verificationUrl }}" style="display: inline-block; padding: 16px 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; border-radius: 6px; font-size: 16px; font-weight: bold;">Vérifier mon adresse e-mail</a>
                </div>

                <div style="background-color: #f8f9fa; border-left: 4px solid #667eea; padding: 20px; margin: 20px 0; border-radius: 0 4px 4px 0;">
                    <p style="margin: 0; color: #555; font-size: 14px;">
                        <strong>🔒 Lien sécurisé:</strong> Ce lien expire dans 60 minutes pour des raisons de sécurité.
                    </p>
                </div>

                <p style="font-size: 16px; color: #555; margin-bottom: 30px; line-height: 1.8;">
                    Si le bouton ne fonctionne pas, copiez et collez le lien ci-dessous dans votre navigateur :
                </p>
                <p style="word-break: break-all; font-size: 13px; color: #667eea; margin-bottom: 30px;">
                    <a href="{{ $verificationUrl }}" style="color: #667eea; text-decoration: none;">{{ $verificationUrl }}</a>
                </p>

                <p style="font-size: 16px; color: #555; margin-top: 30px; line-height: 1.8;">
                    Si vous n'avez pas créé de compte sur <strong>Hi3D</strong>, vous pouvez ignorer cet e-mail en toute sécurité.
                </p>
            </div>

            <!-- Footer -->
            <div style="background-color: #f8f9fa; padding: 30px; text-align: center; border-top: 1px solid #eee;">
                <p style="margin: 5px 0; color: #888; font-size: 14px;">
                    <strong>Hi3D</strong>
                </p>
                <p style="margin: 5px 0; color: #888; font-size: 14px;">
                    Cet e-mail a été envoyé à {{ $user->email ?? 'votre adresse e-mail' }}
                </p>
                <p style="margin: 5px 0; color: #888; font-size: 14px;">
                    &copy; {{ date('Y') }} Hi3D. Tous droits réservés.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
