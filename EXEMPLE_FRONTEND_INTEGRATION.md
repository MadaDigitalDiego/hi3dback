# Intégration Frontend - Nouveaux Champs ServiceOffer

## Exemple de formulaire de création/modification

### HTML Structure

```html
<form id="serviceOfferForm">
    <!-- Champs existants -->
    <div class="form-group">
        <label for="title">Titre du service *</label>
        <input type="text" id="title" name="title" required>
    </div>
    
    <div class="form-group">
        <label for="description">Description</label>
        <textarea id="description" name="description"></textarea>
    </div>
    
    <div class="form-group">
        <label for="price">Prix *</label>
        <input type="number" id="price" name="price" required>
    </div>
    
    <!-- NOUVEAUX CHAMPS -->
    <div class="form-group">
        <label for="what_you_get">Ce que vous obtenez</label>
        <textarea id="what_you_get" name="what_you_get" 
                  placeholder="Décrivez ce que le client recevra exactement...
• Modélisation 3D haute qualité
• Rendus photoréalistes
• Fichiers sources inclus"></textarea>
    </div>
    
    <div class="form-group">
        <label for="who_is_this_for">Pour qui est ce service</label>
        <textarea id="who_is_this_for" name="who_is_this_for" 
                  placeholder="Définissez votre public cible...
• Architectes
• Promoteurs immobiliers
• Designers d'intérieur"></textarea>
    </div>
    
    <div class="form-group">
        <label for="delivery_method">Méthode de livraison</label>
        <textarea id="delivery_method" name="delivery_method" 
                  placeholder="Comment livrez-vous votre service...
• Livraison numérique via plateforme
• Fichiers 3D au format .blend, .fbx
• Documentation technique incluse"></textarea>
    </div>
    
    <div class="form-group">
        <label for="why_choose_me">Pourquoi me choisir</label>
        <textarea id="why_choose_me" name="why_choose_me" 
                  placeholder="Vos avantages concurrentiels...
✓ Plus de 5 ans d'expérience
✓ Portfolio de 200+ projets
✓ Révisions illimitées"></textarea>
    </div>
    
    <button type="submit">Créer le service</button>
</form>
```

### JavaScript - Création d'un service

```javascript
document.getElementById('serviceOfferForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const serviceData = {
        title: formData.get('title'),
        description: formData.get('description'),
        price: parseFloat(formData.get('price')),
        price_unit: 'par projet',
        execution_time: '1 semaine',
        concepts: '3',
        revisions: '2',
        categories: ['Architecture', 'Modélisation 3D'],
        status: 'published',
        // Nouveaux champs
        what_you_get: formData.get('what_you_get'),
        who_is_this_for: formData.get('who_is_this_for'),
        delivery_method: formData.get('delivery_method'),
        why_choose_me: formData.get('why_choose_me')
    };
    
    try {
        const response = await fetch('/api/service-offers', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
                'Accept': 'application/json'
            },
            body: JSON.stringify(serviceData)
        });
        
        if (response.ok) {
            const result = await response.json();
            console.log('Service créé:', result.data);
            alert('Service créé avec succès !');
        } else {
            const error = await response.json();
            console.error('Erreur:', error);
            alert('Erreur lors de la création du service');
        }
    } catch (error) {
        console.error('Erreur réseau:', error);
        alert('Erreur de connexion');
    }
});
```

### Affichage d'un service (Vue détaillée)

```html
<div class="service-detail">
    <h1 id="serviceTitle"></h1>
    <p id="serviceDescription"></p>
    <div class="service-price">
        <span id="servicePrice"></span>
    </div>
    
    <!-- Nouveaux sections -->
    <div class="service-section" id="whatYouGetSection" style="display: none;">
        <h3>🎯 Ce que vous obtenez</h3>
        <div id="whatYouGet" class="service-content"></div>
    </div>
    
    <div class="service-section" id="whoIsThisForSection" style="display: none;">
        <h3>👥 Pour qui est ce service</h3>
        <div id="whoIsThisFor" class="service-content"></div>
    </div>
    
    <div class="service-section" id="deliveryMethodSection" style="display: none;">
        <h3>📦 Méthode de livraison</h3>
        <div id="deliveryMethod" class="service-content"></div>
    </div>
    
    <div class="service-section" id="whyChooseMeSection" style="display: none;">
        <h3>⭐ Pourquoi me choisir</h3>
        <div id="whyChooseMe" class="service-content"></div>
    </div>
</div>
```

```javascript
async function loadServiceDetails(serviceId) {
    try {
        const response = await fetch(`/api/service-offers/${serviceId}`);
        const result = await response.json();
        const service = result.data;
        
        // Champs existants
        document.getElementById('serviceTitle').textContent = service.title;
        document.getElementById('serviceDescription').textContent = service.description;
        document.getElementById('servicePrice').textContent = `${service.price}€`;
        
        // Nouveaux champs - afficher seulement s'ils ont du contenu
        if (service.what_you_get) {
            document.getElementById('whatYouGet').innerHTML = 
                service.what_you_get.replace(/\n/g, '<br>');
            document.getElementById('whatYouGetSection').style.display = 'block';
        }
        
        if (service.who_is_this_for) {
            document.getElementById('whoIsThisFor').innerHTML = 
                service.who_is_this_for.replace(/\n/g, '<br>');
            document.getElementById('whoIsThisForSection').style.display = 'block';
        }
        
        if (service.delivery_method) {
            document.getElementById('deliveryMethod').innerHTML = 
                service.delivery_method.replace(/\n/g, '<br>');
            document.getElementById('deliveryMethodSection').style.display = 'block';
        }
        
        if (service.why_choose_me) {
            document.getElementById('whyChooseMe').innerHTML = 
                service.why_choose_me.replace(/\n/g, '<br>');
            document.getElementById('whyChooseMeSection').style.display = 'block';
        }
        
    } catch (error) {
        console.error('Erreur lors du chargement du service:', error);
    }
}
```

### CSS pour le styling

```css
.service-section {
    margin: 20px 0;
    padding: 15px;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    background-color: #f9f9f9;
}

.service-section h3 {
    margin-top: 0;
    color: #333;
    font-size: 1.2em;
}

.service-content {
    line-height: 1.6;
    color: #555;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}

.form-group textarea {
    width: 100%;
    min-height: 100px;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-family: inherit;
}
```

## Notes importantes

1. **Validation côté client** : Ajoutez une validation pour s'assurer que les champs ne sont pas trop longs
2. **Formatage** : Les champs supportent les retours à la ligne (`\n`) qui peuvent être convertis en `<br>` pour l'affichage
3. **Optionnalité** : Tous les nouveaux champs sont optionnels, vérifiez leur existence avant affichage
4. **Sécurité** : Échappez le contenu HTML si nécessaire pour éviter les attaques XSS
