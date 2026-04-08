# Structure des APIs - FootCamp Dreams

## Organisation par fonctionnalité

### 📁 `/api/auth/`
**Authentification et gestion des comptes utilisateurs**
- `login.php` - Connexion client
- `register.php` - Création de compte client
- `forgot_password.php` - Récupération de mot de passe oublié

### 📁 `/api/reservations/`
**Gestion complète des réservations**
- `reservation_request.php` - Demande de réservation depuis la landing page
- `reserve.php` - Création de réservation
- `admin_validate_reservation.php` - Validation d'une réservation par l'admin
- `admin_validate_reservation_with_room.php` - Validation avec assignation de chambre
- `admin_reject_reservation.php` - Rejet d'une réservation
- `admin_list_reservations.php` - Liste toutes les réservations
- `get_my_reservations.php` - Récupère les réservations du client connecté

### 📁 `/api/rooms/`
**Gestion des chambres et disponibilités**
- `get_available_rooms.php` - Affiche les chambres disponibles pour une période
- `get_room_calendar.php` - Calendrier des réservations
- `assign_room_number.php` - Assigner une chambre à une réservation
- `auto_assign_rooms.php` - Assignation automatique de chambres

### 📁 `/api/admin/`
**Fonctions administrateur**
- `admin_login.php` - Connexion administrateur
- `admin_register.php` - Création de compte admin
- `admin_delete_client.php` - Suppression d'un client
- `admin_day_requests.php` - Demandes pour un jour spécifique
- `admin_plan_activity.php` - Planification d'activités
- `admin_set_deposit.php` - Gestion du dépôt (désactivé)
- `admin_set_discount.php` - Gestion des réductions
- `admin_email_config.php` - Configuration des emails

### 📁 `/api/billing/`
**Facturation et paiements**
- `invoice.php` - Génération de facture
- `get_invoice.php` - Récupération d'une facture
- `send_invoice.php` - Envoi manuel de facture
- `send_invoice_email.php` - Envoi de facture par email
- `get_payment_status.php` - Statut de paiement
- `record_payment.php` - Enregistrement d'un paiement

### 📁 `/api/services/`
**Services et activités**
- `add_service.php` - Ajout d'un service à une réservation
- `get_available_facilities.php` - Récupère les installations disponibles
- `get_facilities_for_activities.php` - Installations par activité

### 📁 `/api/email/`
**Gestion des emails**
- `send_welcome_email.php` - Email de bienvenue
- `email_logs.php` - Logs des emails envoyés

### 📄 `utils.php`
**Utilitaires partagés** (à la racine du dossier api)
- Fonctions de lecture/écriture JSON
- Gestion des réponses JSON
- Envoi d'emails via SMTP
- Calcul des nuits, génération de mots de passe, etc.

### 📄 `index.php`
**Router API** (à la racine du dossier api)
- Redirectionne les appels d'API vers la nouvelle structure
- Permet la compatibilité avec les anciens appels

## Appels d'API

Les URLs peuvent être appelées de deux manières:

### Ancienne structure (compatible)
```
/api/login.php
/api/reserve.php
/api/get_invoice.php
```

### Nouvelle structure (directe)
```
/api/auth/login.php
/api/reservations/reserve.php
/api/billing/get_invoice.php
```

## Notes
- Tous les fichiers dans les sous-dossiers utilisent `require_once __DIR__ . '/../utils.php'` pour accéder aux utilitaires
- Le fichier `index.php` à la racine du dossier `api/` assure la rétrocompatibilité
- Les données JSON sont stockées dans `/data/`
- La configuration SMTP est dans `/config.php`

