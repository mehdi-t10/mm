# 💡 Exemples d'Utilisation - FootCamp Dreams

## 1️⃣ Générer un Mot de Passe

### Utilisation Simple
```javascript
// Générer un mot de passe aléatoire
const newPassword = generatePassword();
console.log('Nouveau mot de passe:', newPassword);
// Exemple: "K7$pL9@mQ2#R"
```

### Dans une Boucle
```javascript
// Générer plusieurs mots de passe pour des utilisateurs
const users = ['user1', 'user2', 'user3'];
const credentials = users.map(user => ({
  username: user,
  password: generatePassword()
}));
console.log(credentials);
```

### Stocker dans localStorage
```javascript
const password = generatePassword();
sessionStorage.setItem('tempPassword', password);
// Récupérer plus tard
const storedPassword = sessionStorage.getItem('tempPassword');
```

---

## 2️⃣ Copier dans le Presse-papiers

### Copier du texte simple
```javascript
copyToClipboard('client@email.com');
// L'utilisateur verra: ✅ Mot de passe copié dans le presse-papiers!
```

### Copier un mot de passe
```javascript
const password = generatePassword();
copyToClipboard(password);
```

### Copier depuis un input
```javascript
const inputValue = document.getElementById('emailInput').value;
copyToClipboard(inputValue);
```

### Copier avec gestion d'erreur
```javascript
function safeCopy(text) {
  try {
    copyToClipboard(text);
    console.log('✅ Copié');
  } catch (err) {
    console.error('❌ Erreur copie:', err);
    alert('Erreur lors de la copie');
  }
}
```

---

## 3️⃣ Afficher le Modal des Identifiants

### Utilisation Simple
```javascript
const email = 'client@footcamp.fr';
const password = generatePassword();
const reservationId = 12345;

showCredentialsModal(email, password, reservationId);
```

### Après Soumission de Formulaire
```javascript
function handleReservationSubmit(formData) {
  // ... valider les données ...
  
  const generatedPassword = generatePassword();
  
  // Envoyer au serveur
  $.ajax({
    url: 'api/reservation_request.php',
    type: 'POST',
    data: JSON.stringify(formData),
    success: function(response) {
      if (response.success) {
        // Afficher les identifiants
        showCredentialsModal(
          formData.email,
          generatedPassword,
          response.reservation_id
        );
      }
    }
  });
}
```

### Avec Délai
```javascript
// Attendre 500ms avant d'afficher
setTimeout(() => {
  showCredentialsModal(email, password, resId);
}, 500);
```

### Afficher plusieurs Modals en Succession
```javascript
function showMultipleCredentials(clients) {
  clients.forEach((client, index) => {
    setTimeout(() => {
      showCredentialsModal(
        client.email,
        generatePassword(),
        client.id
      );
    }, index * 1000); // 1 seconde entre chaque
  });
}
```

---

## 4️⃣ Afficher des Messages avec Icônes

### Succès
```javascript
const msgDiv = document.getElementById('myMessage');
showMsg(msgDiv, '<i class="fas fa-check-circle"></i> Opération réussie!', true);
```

### Erreur
```javascript
showMsg(msgDiv, '<i class="fas fa-times-circle"></i> Une erreur s\'est produite', false);
```

### Attention
```javascript
showMsg(msgDiv, '<i class="fas fa-exclamation-circle"></i> Veuillez vérifier vos données', false);
```

### Information
```javascript
showMsg(msgDiv, '<i class="fas fa-info-circle"></i> Informations importantes', true);
```

### Avec Multiple Icônes
```javascript
showMsg(msgDiv, `
  <i class="fas fa-check-circle"></i> 
  Réservation #12345 créée avec succès!
`, true);
```

---

## 5️⃣ Utiliser Font Awesome dans du HTML

### Simple
```html
<button>
  <i class="fas fa-download"></i> Télécharger
</button>
```

### Avec couleur
```html
<span style="color: #00e676;">
  <i class="fas fa-check-circle"></i> Valide
</span>
```

### Avec animation
```html
<span>
  <i class="fas fa-spinner fa-spin"></i> Chargement...
</span>
```

### Grande taille
```html
<div style="font-size: 3rem;">
  <i class="fas fa-soccer-ball"></i>
</div>
```

### Plusieurs icônes
```html
<h2>
  <i class="fas fa-bed"></i>
  <i class="fas fa-utensils"></i>
  <i class="fas fa-users"></i>
  Nos Services
</h2>
```

---

## 6️⃣ Cas d'Usage Réels

### Validation de Formulaire
```javascript
function validateEmail(email) {
  const isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  
  if (isValid) {
    showMsg(msgDiv, 
      '<i class="fas fa-check-circle"></i> Email valide',
      true
    );
    return true;
  } else {
    showMsg(msgDiv,
      '<i class="fas fa-times-circle"></i> Email invalide',
      false
    );
    return false;
  }
}
```

### Progression Réservation
```javascript
function updateReservationStatus(status) {
  const icons = {
    'pending': 'fa-clock',
    'confirmed': 'fa-check-circle',
    'error': 'fa-times-circle'
  };
  
  const icon = icons[status] || 'fa-info-circle';
  const message = `<i class="fas ${icon}"></i> Statut: ${status}`;
  
  showMsg(msgDiv, message, status !== 'error');
}
```

### Confirmation d'Action
```javascript
function confirmAction(actionName) {
  const result = confirm(
    `<i class="fas fa-question-circle"></i> Êtes-vous sûr de ${actionName}?`
  );
  
  if (result) {
    showMsg(msgDiv,
      `<i class="fas fa-check"></i> ${actionName} confirmée`,
      true
    );
    return true;
  }
  return false;
}
```

### Créer Plusieurs Comptes
```javascript
async function createMultipleAccounts(accountsData) {
  for (const account of accountsData) {
    const password = generatePassword();
    
    showMsg(msgDiv,
      `<i class="fas fa-spinner fa-spin"></i> Création de ${account.email}...`,
      true
    );
    
    const response = await fetch('api/create_user.php', {
      method: 'POST',
      body: JSON.stringify({
        ...account,
        password: password
      })
    });
    
    if (response.ok) {
      showCredentialsModal(
        account.email,
        password,
        account.id
      );
    }
  }
}
```

---

## 7️⃣ Intégration avec Backend

### Envoyer le Mot de Passe au Serveur
```javascript
function submitReservationWithPassword(formData) {
  const generatedPassword = generatePassword();
  
  const completeData = {
    ...formData,
    tempPassword: generatedPassword
  };
  
  $.ajax({
    url: 'api/reservation_request.php',
    type: 'POST',
    data: JSON.stringify(completeData),
    success: function(resp) {
      if (resp.success) {
        // Afficher au client
        showCredentialsModal(
          formData.email,
          generatedPassword,
          resp.reservation_id
        );
      }
    }
  });
}
```

### Récupérer depuis le Serveur
```javascript
$.ajax({
  url: 'api/get_user_credentials.php?userId=123',
  type: 'GET',
  success: function(response) {
    if (response.success) {
      showCredentialsModal(
        response.user.email,
        response.tempPassword,
        response.user.id
      );
    }
  }
});
```

---

## 8️⃣ Bonnes Pratiques

### ✅ Toujours Valider
```javascript
// ✅ BON
function copyPassword(password) {
  if (!password || typeof password !== 'string') {
    console.error('Password invalide');
    return;
  }
  copyToClipboard(password);
}
```

### ✅ Gérer les Erreurs
```javascript
// ✅ BON
try {
  const pwd = generatePassword();
  copyToClipboard(pwd);
} catch (error) {
  showMsg(msgDiv, 
    '<i class="fas fa-exclamation-triangle"></i> Erreur: ' + error,
    false
  );
}
```

### ✅ Sécurité
```javascript
// ✅ BON - Ne pas log les mots de passe
const password = generatePassword();
// console.log(password); ❌ JAMAIS
showCredentialsModal(email, password, id);

// ✅ BON - Nettoyer après affichage
setTimeout(() => {
  sessionStorage.removeItem('tempPassword');
}, 60000); // Après 1 minute
```

### ✅ Accessibilité
```javascript
// ✅ BON - Toujours inclure du texte avec les icônes
showMsg(msgDiv,
  '<i class="fas fa-check-circle"></i> Réservation confirmée',
  true
);

// ❌ MAUVAIS - Juste une icône
showMsg(msgDiv, '<i class="fas fa-check-circle"></i>', true);
```

---

## 9️⃣ Dépannage

### Le mot de passe ne s'affiche pas
```javascript
// Vérifier que la fonction existe
if (typeof generatePassword === 'function') {
  const pwd = generatePassword();
  console.log('Mot de passe généré:', pwd.length, 'caractères');
} else {
  console.error('Fonction generatePassword non trouvée');
}
```

### Le modal ne s'affiche pas
```javascript
// Vérifier les dépendances
console.log('Font Awesome:', !!document.querySelector('[href*="font-awesome"]'));
console.log('Modal function:', typeof showCredentialsModal);
console.log('DOM ready:', document.readyState);
```

### La copie ne fonctionne pas
```javascript
// Vérifier le navigateur
if (!navigator.clipboard) {
  console.warn('Clipboard API non supportée');
  alert('Veuillez copier manuellement: ' + text);
}
```

---

## 🔟 Résumé des Fonctions

| Fonction | Paramètres | Retour | Usage |
|----------|-----------|--------|-------|
| `generatePassword()` | aucun | string | Générer pwd |
| `copyToClipboard(text)` | string | void | Copier texte |
| `showCredentialsModal(email, pwd, id)` | 3 strings | void | Modal identifiants |
| `showMsg(elem, html, isSuccess)` | element, string, boolean | void | Afficher message |

---

**Version** : 1.0  
**Last Updated** : 2026-04-06  
**Status** : ✅ Production Ready

