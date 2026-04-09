# 🔐 Système d'Authentification Unifié - FootCamp Dreams

## Vue d'ensemble

**Un seul système d'authentification et de gestion de session** pour toute l'application.

Pas de duplication, pas de conflit, une seule source de vérité: **SessionManager**

## Architecture

### Couches du système

```
┌─────────────────────────────────────────────────────────┐
│                    PAGES HTML                            │
│  (index.html, client-dashboard.html, admin-dashboard)  │
└──────────────────────┬──────────────────────────────────┘
                       │
┌──────────────────────v──────────────────────────────────┐
│           auto-redirect.js (Orchestration)              │
│  - Vérifie la session au chargement                    │
│  - Redirige vers le bon dashboard                      │
│  - Initialise les données utilisateur                  │
│  - Expose la fonction logout() globale                 │
└──────────────────────┬──────────────────────────────────┘
                       │
┌──────────────────────v──────────────────────────────────┐
│      session-manager.js (Source unique de vérité)      │
│  - Gère localStorage et sessionStorage                 │
│  - saveUserSession() / saveAdminSession()              │
│  - getCurrentUser() / getCurrentAdmin()                │
│  - logoutUser() / logoutAdmin()                        │
└──────────────────────┬──────────────────────────────────┘
                       │
┌──────────────────────v──────────────────────────────────┐
│          Navigateur (localStorage / sessionStorage)     │
└─────────────────────────────────────────────────────────┘
```

## Flux d'authentification

### 1. Login (index.html)

```javascript
handleClientLogin(e) {
  // Recevoir les données du serveur
  $.ajax("api/auth/login.php", ...)
    .done(function(resp) {
      if (resp.success) {
        // ÉTAPE UNIQUE: Sauvegarder via SessionManager
        sessionManager.saveUserSession(resp.user);
        
        // Rediriger vers le dashboard
        window.location.href = "client-dashboard.html?user=...";
      }
    });
}
```

### 2. Redirection (auto-redirect.js)

```javascript
// Au chargement de chaque page
if (currentPage === 'client-dashboard') {
  // Vérifier la session via SessionManager
  if (!sessionManager.isUserLoggedIn()) {
    // Redirection vers login
    window.location.href = 'index.html';
  } else {
    // Initialiser le dashboard
    initClientDashboard();
  }
}
```

### 3. Utilisation des données (client-dashboard.html)

```javascript
// Initialiser au chargement
function initializeUserData() {
  // Récupérer les données du SessionManager
  userData = sessionManager.getCurrentUser();
  
  if (!userData) {
    // Redirection vers login (géré par auto-redirect.js)
    window.location.href = "index.html";
    return;
  }
  
  // Afficher les données
  displayUserProfile(userData);
}

// Logout
function logout() {
  sessionManager.logoutUser();
  window.location.href = "index.html";
}
```

## Fichiers du système

| Fichier | Responsabilité |
|---------|-----------------|
| `assets/session-manager.js` | **Source unique de vérité** pour la gestion de session |
| `assets/auto-redirect.js` | **Orchestrateur** - redirige et initialise les pages |
| `index.html` | **Page de login** - utilise SessionManager |
| `client-dashboard.html` | **Dashboard client** - utilise SessionManager + auto-redirect |
| `admin-dashboard.html` | **Dashboard admin** - utilise SessionManager + auto-redirect |

## Points clés

### ✅ UN SEUL système
- Pas d'authentification custom dans les dashboards
- SessionManager gère TOUT
- auto-redirect.js orchestre TOUT

### ✅ Source unique de vérité
- SessionManager = source d'autorité
- localStorage = persistence
- Pas de duplication

### ✅ Pas de conflit
- Pas de `loadUserData()` vs `getCurrentUser()`
- Pas de `redirectToLogin()` vs `logoutUser()`
- Une seule fonction pour chaque besoin

### ✅ Sécurité
- Mots de passe jamais stockés
- Validation côté serveur requise
- Logout propre et complet

## Cas d'usage

### Cas 1: Utilisateur se connecte
```
1. Utilisateur saisit email/password dans index.html
2. handleClientLogin() envoie à api/auth/login.php
3. Serveur valide et retourne user data
4. sessionManager.saveUserSession(user) → localStorage
5. Redirection vers client-dashboard.html?user=...
6. auto-redirect.js détecte la session ✓
7. initializeUserData() charge les données du SessionManager
8. Dashboard affiché ✓
```

### Cas 2: Utilisateur rafraîchit la page
```
1. Utilisateur appuie sur F5
2. auto-redirect.js se charge
3. sessionManager.getCurrentUser() → données de localStorage ✓
4. initializeUserData() affiche les données
5. Dashboard affiché ✓
```

### Cas 3: Utilisateur ferme le navigateur
```
1. Utilisateur ferme le navigateur complètement
2. localStorage persiste les données
3. Utilisateur rouvre le navigateur
4. Accès à index.html
5. auto-redirect.js se charge
6. sessionManager.isUserLoggedIn() = true ✓
7. Redirection automatique vers client-dashboard.html ✓
8. Dashboard affiché avec les données ✓
```

### Cas 4: Utilisateur se déconnecte
```
1. Utilisateur clique sur "Déconnexion"
2. logout() appelle sessionManager.logoutUser()
3. localStorage.removeItem("currentUser") ✓
4. Redirection vers index.html
5. auto-redirect.js détecte pas de session
6. Affiche la page de login ✓
```

## À ne PAS faire

❌ **Ne créez pas de logique d'authentification custom** dans les dashboards
- Utilisez UNIQUEMENT sessionManager.getCurrentUser()

❌ **Ne créez pas de redirectToLogin()** custom
- Laissez auto-redirect.js gérer les redirections

❌ **Ne stockez pas les données ailleurs** que via sessionManager
- sessionManager = seul endroit pour sauvegarder/charger

❌ **Ne cherchez pas les données dans localStorage** directement
- Passez par sessionManager.getCurrentUser()

## À faire

✅ **Utiliser SessionManager partout**
```javascript
const user = sessionManager.getCurrentUser();
const admin = sessionManager.getCurrentAdmin();
```

✅ **Laisser auto-redirect.js gérer les redirections**
- Elle détecte automatiquement les droits d'accès
- Elle redirige vers le bon dashboard

✅ **Appeler logout() pour la déconnexion**
```javascript
function logout() {
  sessionManager.logoutUser();
  window.location.href = "index.html";
}
```

✅ **Initialiser les données au chargement du DOM**
```javascript
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initializeUserData);
} else {
  initializeUserData();
}
```

## Débogage

```javascript
// Dans la console du navigateur:

// Voir l'état actuel
sessionManager.debug();

// Vérifier l'utilisateur
sessionManager.getCurrentUser();

// Vérifier l'admin
sessionManager.getCurrentAdmin();

// Vérifier le type de session
sessionManager.getSessionType();

// Faire un logout complet
sessionManager.logoutAll();
```

## Tests

Utilisez **test-session.html** pour:
- Vérifier l'état des sessions
- Simuler des logins
- Vider les données
- Tester les redirections

```
http://localhost/mm/test-session.html
```

## Résumé

🎯 **Un seul système. Simple. Efficace. Maintenable.**

| Aspact | Solution |
|--------|----------|
| **Source de vérité** | SessionManager |
| **Orchestration** | auto-redirect.js |
| **Persistance** | localStorage |
| **Backup** | sessionStorage |
| **Logout** | sessionManager.logoutUser() |
| **Vérification** | sessionManager.getCurrentUser() |

C'est tout ce qu'il faut! 🚀

