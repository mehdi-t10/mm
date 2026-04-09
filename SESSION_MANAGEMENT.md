# Gestion des Sessions Persistantes - FootCamp Dreams

## Vue d'ensemble

Le système de gestion des sessions a été amélioré pour permettre aux utilisateurs de rester connectés même après la fermeture du navigateur ou d'un onglet. Lorsqu'un utilisateur se reconnecte, il est automatiquement redirigé vers son interface appropriée (client ou admin).

## Architecture

### Fichiers implémentés

1. **assets/session-manager.js** - Classe `SessionManager`
   - Gère la persistance des données de session
   - Utilise `localStorage` comme stockage principal
   - Utilise `sessionStorage` comme backup
   - Méthodes principales :
     - `getCurrentUser()` - Récupère l'utilisateur client
     - `getCurrentAdmin()` - Récupère l'admin
     - `saveUserSession(userData)` - Sauvegarde une session client
     - `saveAdminSession(adminData)` - Sauvegarde une session admin
     - `logoutUser()` - Déconnecte un client
     - `logoutAdmin()` - Déconnecte un admin
     - `logoutAll()` - Déconnecte complètement

2. **assets/auto-redirect.js** - Redirection automatique
   - Vérifie la session au chargement de chaque page
   - Redirige automatiquement vers le bon dashboard
   - Affiche les informations utilisateur si connecté
   - Expose une fonction `logout()` globale

## Flux d'utilisation

### Scénario 1 : Login réussi et fermeture du navigateur

1. Utilisateur se connecte depuis `index.html`
2. Les données sont sauvegardées dans `localStorage`
3. Redirection vers `client-dashboard.html` ou `admin-dashboard.html`
4. **Utilisateur ferme le navigateur complètement**
5. Utilisateur ouvre le navigateur et accède à `index.html`
6. Le script `auto-redirect.js` détecte une session existante
7. Redirection automatique vers le dashboard approprié

### Scénario 2 : Fermeture d'un onglet et ouverture d'un nouveau

1. Utilisateur a un onglet ouvert sur `client-dashboard.html`
2. **Utilisateur ferme l'onglet**
3. Utilisateur ouvre un nouvel onglet et accède à `index.html`
4. `localStorage` persiste les données de session
5. Redirection automatique vers `client-dashboard.html`

### Scénario 3 : Accès à l'interface incorrecte

1. Admin se connecte et va sur `admin-dashboard.html`
2. Admin ouvre un nouvel onglet et accède à `client-dashboard.html`
3. `auto-redirect.js` détecte que l'utilisateur est admin
4. Redirige vers `admin-dashboard.html`

## Sécurité

- Les mots de passe ne sont jamais stockés en localStorage
- Seules les informations publiques de l'utilisateur sont stockées
- Les données sont supprimées lors du logout
- Validation côté serveur requise pour chaque action

## Implémentation dans les fichiers HTML

### index.html
```html
<script src="assets/session-manager.js"></script>
<script src="assets/auto-redirect.js"></script>

<!-- Dans handleClientLogin() -->
sessionManager.saveUserSession(resp.user);

<!-- Dans handleAdminLogin() -->
sessionManager.saveAdminSession(resp.user);
```

### client-dashboard.html
```html
<script src="assets/session-manager.js"></script>
<script src="assets/auto-redirect.js"></script>

<!-- Dans logout() -->
function logout() {
  sessionManager.logoutUser();
  window.location.href = "index.html";
}
```

### admin-dashboard.html
```html
<script src="assets/session-manager.js"></script>
<script src="assets/auto-redirect.js"></script>

<!-- Dans checkAdminSession() -->
const admin = sessionManager.getCurrentAdmin();
```

## Débogage

Pour déboguer les sessions, ouvrez la console du navigateur et utilisez :

```javascript
// Voir les informations de session actuelles
debugSession();

// Récupérer manuellement l'utilisateur
sessionManager.getCurrentUser();

// Récupérer manuellement l'admin
sessionManager.getCurrentAdmin();

// Voir le type de session actuelle
console.log(sessionManager.getSessionType());

// Voir les détails complets
sessionManager.debug();
```

## Tests recommandés

1. **Test de persistance**
   - Connectez-vous à index.html
   - Fermez complètement le navigateur
   - Rouvrez et accédez à index.html
   - Vérifiez que vous êtes redirigé automatiquement

2. **Test de fermeture d'onglet**
   - Connectez-vous dans un onglet
   - Fermez cet onglet
   - Ouvrez un nouvel onglet
   - Accédez à index.html
   - Vérifiez la redirection automatique

3. **Test de redirection correcte**
   - Connectez-vous en tant qu'admin
   - Essayez d'accéder manuellement à client-dashboard.html
   - Vérifiez que vous êtes redirigé vers admin-dashboard.html

4. **Test de logout**
   - Connectez-vous
   - Cliquez sur "Déconnexion"
   - Vérifiez que vous êtes redirigé vers index.html
   - Vérifiez que localStorage est nettoyé

## Données stockées dans localStorage

```javascript
{
  "currentUser": {
    "id": "user-id",
    "email": "user@example.com",
    "prenom": "John",
    "nom": "Doe",
    "telephone": "0123456789",
    "role": "client"
  },
  "lastLogin": "2026-04-09T10:30:00.000Z"
}

// Ou pour admin:
{
  "currentAdmin": {
    "id": "admin-id", 
    "email": "admin@example.com",
    "prenom": "Jane",
    "nom": "Smith",
    "role": "admin"
  },
  "lastLogin": "2026-04-09T10:35:00.000Z"
}
```

## Limitations connues

1. Les sessions sont basées sur le navigateur/domaine
2. Si l'utilisateur efface le localStorage manuellement, la session est perdue
3. Si JavaScript est désactivé, le système ne fonctionne pas
4. Les sessions ne sont pas synchronisées entre les appareils

## Prochaines améliorations possibles

1. Implémenter une expiration de session (timeout)
2. Ajouter la synchronisation entre onglets du même navigateur
3. Implémenter un système de tokens côté serveur
4. Ajouter la gestion des sessions à deux facteurs
5. Implémenter un "Remember Me" optionnel avec expiration personnalisée

