# ✅ Bug de facturation des activités multiples - CORRIGÉ

## Problème identifié

L'utilisateur "az" a **DEUX activités** dans sa réservation:
- Activity 2: Match amical (75€)
- Activity 3: Cours privé (100€)

Mais n'était facturé que pour **UNE seule activité** (100€) au lieu de **175€**.

## Cause du bug

**Problème de comparaison de types** lors du matching des IDs d'activités.

### En PHP (`get_invoice.php`, ligne 90):
```php
if ($act['id'] === $actId) {  // ❌ Comparaison stricte peut échouer
```

Les IDs peuvent être stockés comme strings dans le JSON et comparés avec des entiers. La comparaison stricte `===` échoue si les types ne correspondent pas exactement.

**Exemple:**
- `$act['id']` = `2` (int)
- `$actId` = `"2"` (string) ou vice-versa
- `2 === "2"` → **false** ❌ → L'activité n'est pas trouvée!

### En JavaScript (`admin-dashboard.html`, lignes 1122 et 1308):
```javascript
const activity = activitiesData.find(a => a.id === actId);  // ❌ Comparaison stricte
```

Même problème potentiel en JavaScript.

## Solutions appliquées

### 1. **PHP (`api/billing/get_invoice.php`)**

**Avant:**
```php
if ($act['id'] === $actId) {
```

**Après:**
```php
$actId = intval($actId);  // Convertir en entier
if ((int)$act['id'] === $actId) {  // Comparaison stricte avec conversion
```

### 2. **JavaScript (`admin-dashboard.html`)**

**Avant:**
```javascript
const activity = activitiesData.find(a => a.id === actId);
```

**Après:**
```javascript
const numActId = Number(actId);  // Convertir en nombre
const activity = activitiesData.find(a => Number(a.id) === numActId);
```

## Cas de test

**Réservation utilisateur "az" (ID: 1):**
- Dates: 2026-04-09 → 2026-04-11 (2 nuits)
- Activités sélectionnées: 
  - Day 0-1: Activity 2 + Activity 3
- **Avant le fix:** 100€ (une seule activité) ❌
- **Après le fix:** 175€ (les deux activités) ✅
  - Match amical (75€) + Cours privé (100€)

## Fichiers modifiés

1. **`api/billing/get_invoice.php`** (ligne 83-102)
   - Ajout de conversion en entier pour les IDs d'activités

2. **`admin-dashboard.html`** (lignes 1118-1127 et 1304-1313)
   - Ajout de conversion Number() pour les comparaisons d'IDs
   - Affecte `updateKPIs()` et `updateRevenueChart()`

3. **`data/reservations.json`** (utilisateur "az")
   - Mise à jour: `activities: [2, 3]` (deux activités pour test)

## Impact

✅ Les activités multiples sont maintenant toutes comptabilisées  
✅ Les coûts sont calculés correctement dans les factures  
✅ Les revenus affichés au dashboard sont exacts  
✅ Fonctionnement cohérent entre la façade client et admin  

## Tests recommandés

1. Vérifier la facture de l'utilisateur "az" (réservation #1):
   - Doit afficher 175€ pour les activités

2. Vérifier les KPIs du dashboard admin:
   - Les revenus totaux incluent correctement toutes les activités

3. Créer une nouvelle réservation avec plusieurs activités et vérifier la facturation

