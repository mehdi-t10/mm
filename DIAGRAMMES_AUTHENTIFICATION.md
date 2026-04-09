# 📊 DIAGRAMME DU SYSTÈME D'AUTHENTIFICATION UNIFIÉ

## 1. Vue d'ensemble de l'architecture

```
┌────────────────────────────────────────────────────────────────┐
│                     FOOTER CAMP DREAMS                          │
│              Système d'Authentification Unifié                 │
└────────────────────────────────────────────────────────────────┘

                         index.html
                      (Page de login)
                            │
                    handleClientLogin()
                            │
                            ├─→ API: api/auth/login.php
                            │        └─→ Valide credentials
                            │
                            ├─→ sessionManager.saveUserSession()
                            │        ├─→ localStorage.setItem("currentUser")
                            │        └─→ sessionStorage.setItem("currentUser")
                            │
                            └─→ Redirect client-dashboard.html?user=...

                                     │
                                     ↓

                    client-dashboard.html
                      (Dashboard client)
                            │
                    auto-redirect.js ✓
                    (Verificateur)
                            │
            ┌───────────────┴────────────────┐
            │                                │
        Session OK?               Session NOK?
            │ YES                    │ NO
            ↓                        ↓
    initializeUserData()        Redirect
    ├─ getCurrentUser()        index.html
    ├─ Afficher données
    └─ Dashboard ✓
```

## 2. Flux détaillé du login

```
┌─────────────┐
│ Utilisateur │
│  Se connecte│
└──────┬──────┘
       │
       ↓
    ┌─────────────────────────────────────────┐
    │         index.html                       │
    │  ┌─────────────────────────────────────┐│
    │  │ Email: demo@footcamp.test          ││
    │  │ Password: ••••••••                 ││
    │  │ [Se connecter]                     ││
    │  └─────────────────────────────────────┘│
    └──────┬──────────────────────────────────┘
           │
           ↓ handleClientLogin()
    ┌──────────────────────────────────────┐
    │  POST api/auth/login.php             │
    │  {email, password}                   │
    └──────┬───────────────────────────────┘
           │
           ↓
    ┌──────────────────────────────────────┐
    │  Serveur: Valider credentials       │
    │  ✓ Email trouvé                     │
    │  ✓ Password correct                 │
    │  → Retourner user data              │
    └──────┬───────────────────────────────┘
           │
           ↓ Response: {success: true, user: {...}}
    ┌──────────────────────────────────────────────────┐
    │  sessionManager.saveUserSession(user)            │
    │  ├─→ localStorage.setItem("currentUser", user)   │
    │  └─→ sessionStorage.setItem("currentUser", user) │
    │                                                   │
    │  Données stockées:                               │
    │  {                                               │
    │    "id": "123",                                  │
    │    "email": "demo@footcamp.test",               │
    │    "prenom": "Demo",                            │
    │    "nom": "User",                               │
    │    "telephone": "0123456789"                    │
    │  }                                               │
    └──────┬───────────────────────────────────────────┘
           │
           ↓ Attendre 1500ms
    ┌──────────────────────────────────────────────────┐
    │  window.location.href =                          │
    │    "client-dashboard.html?user=..."              │
    └──────┬───────────────────────────────────────────┘
           │
           ↓ Navigation
    ┌──────────────────────────────────────────────────┐
    │              client-dashboard.html              │
    │  ┌────────────────────────────────────────────┐ │
    │  │  auto-redirect.js charge                  │ │
    │  │  └─ checkAndRedirect()                   │ │
    │  │     ├─ currentPage = 'client-dashboard'  │ │
    │  │     ├─ sessionManager.isUserLoggedIn() ✓ │ │
    │  │     └─ initClientDashboard()             │ │
    │  └────────────────────────────────────────────┘ │
    │                                                  │
    │  ┌────────────────────────────────────────────┐ │
    │  │  DOM ready: initializeUserData()          │ │
    │  │  ├─ userData = sessionManager.getCurrentUser()
    │  │  ├─ document.getElementById("userName")  │ │
    │  │  │  .textContent = userData.prenom      │ │
    │  │  ├─ displayUserProfile(userData)        │ │
    │  │  └─ ✓ Dashboard affiché                 │ │
    │  └────────────────────────────────────────────┘ │
    │                                                  │
    │  ┌────────────────────────────────────────────┐ │
    │  │ 👤 Demo                                  │ │
    │  │ 📧 demo@footcamp.test                    │ │
    │  │                                          │ │
    │  │ [Mes Réservations]  [Déconnexion]       │ │
    │  └────────────────────────────────────────────┘ │
    └──────┬───────────────────────────────────────────┘
           │
           ✓ LOGIN RÉUSSI
```

## 3. Flux de persistance (Fermeture navigateur)

```
┌─────────────────────────────────────────────────┐
│  Utilisateur dans client-dashboard.html        │
│  ✓ Connecté                                    │
│  ✓ Données dans localStorage                  │
└──────┬────────────────────────────────────────┘
       │
       ↓ Utilisateur ferme complètement le navigateur
       │
    ~~~│~~~ (Navigateur fermé) ~~~
       │
       ↓ Utilisateur réouvre le navigateur
       │
    ┌──────────────────────────────────────┐
    │  index.html                          │
    └──────┬───────────────────────────────┘
           │
           ↓ auto-redirect.js charge
    ┌──────────────────────────────────────┐
    │  checkAndRedirect()                  │
    │  ├─ currentPage = 'login'            │
    │  ├─ sessionManager.isUserLoggedIn()? │
    │  │  ├─ localStorage.currentUser? ✓   │
    │  │  └─ YES!                          │
    │  └─ window.location.href =           │
    │     'client-dashboard.html'          │
    └──────┬───────────────────────────────┘
           │
           ↓
    ┌──────────────────────────────────────┐
    │  client-dashboard.html               │
    │  ├─ initializeUserData()             │
    │  ├─ sessionManager.getCurrentUser()  │
    │  │  └─ localStorage.getItem()  ✓     │
    │  └─ Dashboard affiché               │
    │                                      │
    │  ✓ SESSION RESTAURÉE!               │
    └──────────────────────────────────────┘
```

## 4. Flux de déconnexion

```
┌──────────────────────────────────┐
│  Dashboard affiché               │
│  [Déconnexion] button clické    │
└──────┬───────────────────────────┘
       │
       ↓ logout()
    ┌──────────────────────────────────────────┐
    │  if (confirm("Êtes-vous sûr?")) {      │
    │    sessionManager.logoutUser()          │
    │    ├─ localStorage.removeItem()  ✓      │
    │    ├─ sessionStorage.removeItem() ✓     │
    │    └─ Données effacées                  │
    │    window.location.href = "index.html" │
    │  }                                       │
    └──────┬───────────────────────────────────┘
           │
           ↓
    ┌──────────────────────────────────────────┐
    │  index.html                              │
    │  ├─ auto-redirect.js charge             │
    │  ├─ sessionManager.isUserLoggedIn()     │
    │  │  ├─ localStorage.currentUser?        │
    │  │  └─ NO                               │
    │  └─ Affiche page login                  │
    │                                          │
    │  ✓ DÉCONNEXION COMPLÈTE!               │
    └──────────────────────────────────────────┘
```

## 5. Matrice de routage

```
┌──────────────────┬─────────────┬─────────────┐
│ Page Actuelle    │ Connecté?   │ Action      │
├──────────────────┼─────────────┼─────────────┤
│ index.html       │ NO          │ Afficher login
│ index.html       │ OUI (client)│ Redirect client-dash
│ index.html       │ OUI (admin) │ Redirect admin-dash
├──────────────────┼─────────────┼─────────────┤
│ client-dash      │ NO          │ Redirect index
│ client-dash      │ OUI (client)│ Afficher dashboard
│ client-dash      │ OUI (admin) │ Redirect admin-dash
├──────────────────┼─────────────┼─────────────┤
│ admin-dash       │ NO          │ Redirect index
│ admin-dash       │ OUI (client)│ Redirect client-dash
│ admin-dash       │ OUI (admin) │ Afficher dashboard
└──────────────────┴─────────────┴─────────────┘
```

## 6. Structure SessionManager

```
┌─────────────────────────────────────────────────┐
│         SessionManager (Class)                  │
├─────────────────────────────────────────────────┤
│                                                 │
│ CONFIGURATION                                   │
│ └─ STORAGE_KEYS = {                            │
│    - currentUser                               │
│    - currentAdmin                              │
│    - lastLogin                                 │
│    - sessionToken                              │
│  }                                              │
│                                                 │
│ GETTERS                                        │
│ ├─ getCurrentUser()                            │
│ ├─ getCurrentAdmin()                           │
│ ├─ getSessionType()                            │
│ ├─ isUserLoggedIn()                            │
│ └─ isAdminLoggedIn()                           │
│                                                 │
│ SETTERS                                        │
│ ├─ saveUserSession(userData)                   │
│ └─ saveAdminSession(adminData)                 │
│                                                 │
│ LOGOUT                                         │
│ ├─ logoutUser()                                │
│ ├─ logoutAdmin()                               │
│ └─ logoutAll()                                 │
│                                                 │
│ DEBUG                                          │
│ └─ debug()                                     │
│                                                 │
└─────────────────────────────────────────────────┘
```

## 7. État de la donnée

```
LOGIN        NAVIGATION       CLOSE BROWSER    REOPEN
│            │               │                │
├── localStorage vide        │                │
│                            │                │
├─→ saveUserSession()        │                │
│   └── localStorage = user  │                │
│       sessionStorage = user│                │
│                            │                │
│                    ├─→ localStorage ✓      │
│                    │   sessionStorage ✓    │
│                    │                       │
│                    │                ~~~    │
│                    │                       │
│                    │                       ├─→ localStorage = user ✓
│                    │                       │   sessionStorage = empty
│                    │                       │   → Sesion OK!
│                    │                       │
│ LOGOUT                                     │
│ │                                          │
│ └─→ localStorage.removeItem()              │
│     sessionStorage.removeItem()            │
│                                            │
│ → localStorage = empty                    │
│   sessionStorage = empty                  │
│                                            │
│ → Session = NO                            │
```

---

## TL;DR - Un seul système

```
┌─────────────────────────┐
│   SessionManager        │  ← Source unique de vérité
└────────────┬────────────┘
             │
    ┌────────┴─────────┐
    │                  │
    ↓                  ↓
  login()           logout()
    │                  │
    └────────┬─────────┘
             │
    ┌────────v──────────┐
    │  auto-redirect.js │  ← Orchestrateur
    │  (Détection auto) │
    └────────┬──────────┘
             │
      ┌──────┴──────┐
      ↓             ↓
   Dashboard    index.html
   (Affichage) (Login)
```

**C'est tout! Simple et efficace! 🚀**

