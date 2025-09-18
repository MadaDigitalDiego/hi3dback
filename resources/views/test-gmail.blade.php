<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Authentification Gmail</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-md max-w-md w-full">
        <h1 class="text-2xl font-bold text-center mb-6 text-gray-800">
            Test Authentification Gmail
        </h1>
        
        <div class="space-y-4">
            <!-- Statut de la configuration -->
            <div id="status" class="p-4 rounded-lg bg-gray-50">
                <p class="text-sm text-gray-600">Vérification de la configuration...</p>
            </div>
            
            <!-- Bouton de connexion Gmail -->
            <button 
                id="gmailLogin" 
                class="w-full bg-red-500 hover:bg-red-600 text-white font-bold py-3 px-4 rounded-lg transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                disabled
            >
                <svg class="w-5 h-5 inline-block mr-2" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="currentColor" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="currentColor" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path fill="currentColor" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
                Se connecter avec Gmail
            </button>
            
            <!-- Résultat -->
            <div id="result" class="hidden p-4 rounded-lg">
                <h3 class="font-semibold mb-2">Résultat:</h3>
                <pre id="resultContent" class="text-sm bg-gray-100 p-2 rounded overflow-auto"></pre>
            </div>
        </div>
    </div>

    <script>
        // Vérifier le statut de la configuration
        async function checkStatus() {
            try {
                const response = await fetch('/api/auth/gmail/status');
                const data = await response.json();
                
                const statusDiv = document.getElementById('status');
                const loginButton = document.getElementById('gmailLogin');
                
                if (data.configured && data.configuration.is_complete) {
                    statusDiv.innerHTML = `
                        <div class="text-green-600">
                            <strong>✅ Configuration Gmail active</strong>
                            <p class="text-sm mt-1">Nom: ${data.configuration.name}</p>
                            <p class="text-sm">URI: ${data.configuration.redirect_uri}</p>
                            <p class="text-sm">Scopes: ${data.configuration.scopes.join(', ')}</p>
                        </div>
                    `;
                    loginButton.disabled = false;
                } else {
                    statusDiv.innerHTML = `
                        <div class="text-red-600">
                            <strong>❌ Configuration Gmail incomplète</strong>
                            <p class="text-sm mt-1">Veuillez configurer Gmail OAuth dans l'administration.</p>
                        </div>
                    `;
                }
            } catch (error) {
                document.getElementById('status').innerHTML = `
                    <div class="text-red-600">
                        <strong>❌ Erreur de connexion</strong>
                        <p class="text-sm mt-1">${error.message}</p>
                    </div>
                `;
            }
        }
        
        // Gérer la connexion Gmail
        document.getElementById('gmailLogin').addEventListener('click', function() {
            // Utiliser la route web qui gère les sessions
            window.location.href = '/auth/gmail/redirect';
        });
        
        // Afficher le résultat
        function showResult(data) {
            const resultDiv = document.getElementById('result');
            const resultContent = document.getElementById('resultContent');
            
            resultDiv.classList.remove('hidden');
            resultContent.textContent = JSON.stringify(data, null, 2);
            
            if (data.success) {
                resultDiv.className = 'p-4 rounded-lg bg-green-50 border border-green-200';
            } else {
                resultDiv.className = 'p-4 rounded-lg bg-red-50 border border-red-200';
            }
        }
        
        // Vérifier si on revient du callback
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('success') !== null || urlParams.get('error')) {
            // On revient du callback Gmail
            const success = urlParams.get('success') === '1';
            const result = {
                success: success,
                message: urlParams.get('message') || urlParams.get('error'),
                is_new_user: urlParams.get('is_new_user') === '1',
                user_email: urlParams.get('user_email'),
                token_preview: urlParams.get('token')
            };

            showResult(result);

            // Nettoyer l'URL
            window.history.replaceState({}, document.title, '/test-gmail');
        }
        
        // Vérifier le statut au chargement
        checkStatus();
    </script>
</body>
</html>
