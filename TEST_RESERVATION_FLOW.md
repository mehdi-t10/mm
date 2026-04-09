# Test du Flux de Réservation - Client Dashboard

## Vue d'ensemble
Les clients connectés peuvent maintenant créer des réservations supplémentaires depuis le client-dashboard avec le même système que la page d'accueil.

## Flux de Données

### 1. Frontend (client-dashboard.html)

#### Sélection des Chambres
- L'API `get_available_rooms.php` retourne une liste de types de chambres disponibles
- Pour chaque type, un formulaire avec +/- boutons pour sélectionner la quantité
- Les inputs sont générés dynamiquement avec `id="rooms_${room.type}"` et `data-type="${room.type}"`

#### Collecte des Données
Quand l'utilisateur soumet le formulaire :
```javascript
const selectedRooms = {};  // ex: {"simple": 1, "double": 2}
const activities = [];     // ex: [1, 2, 3]

// Boucle sur les inputs de chambres
document.querySelectorAll('input[id^="rooms_"]').forEach((input) => {
  const quantity = parseInt(input.value || 0);
  const roomType = input.dataset.type;
  if (quantity > 0) {
    selectedRooms[roomType] = quantity;
  }
});

// Boucle sur les checkboxes d'activités
document.querySelectorAll('[name="activity"]:checked').forEach((checkbox) => {
  activities.push(parseInt(checkbox.value));  // IDs d'activités, pas les noms!
});
```

#### Envoi au Backend
```javascript
const data = {
  nom, prenom, email, telephone,
  date_arrivee, date_depart,
  nb_personnes,
  selected_rooms: JSON.stringify(selectedRooms),  // Objet sérialisé
  activities: JSON.stringify(activities),         // Array sérialisé
};

$.ajax({
  url: "api/reservations/reserve.php",
  type: "POST",
  data: data,
  ...
});
```

### 2. Backend (api/reservations/reserve.php)

#### Réception des Données
```php
$selected_rooms = [];
if (!empty($_POST['selected_rooms'])) {
    $selected_rooms = json_decode($_POST['selected_rooms'], true);
    // Résultat: array("simple" => 1, "double" => 2)
}
```

#### Création de la Réservation
```php
$newReservation = [
    'id' => nextId($reservations),
    'nom' => $nom,
    ...
    'activities' => $activities,
    'selected_rooms' => $selected_rooms,  // Stocké comme objet
    'status' => 'en_attente',
    'created_at' => date('Y-m-d H:i:s'),
    ...
];
```

### 3. Stockage (data/reservations.json)

La réservation est stockée comme:
```json
{
  "id": 5,
  "nom": "Dupont",
  "prenom": "Jean",
  "email": "jean@example.com",
  "date_arrivee": "2026-04-15",
  "date_depart": "2026-04-20",
  "nb_personnes": 5,
  "activities": [1, 2],
  "selected_rooms": {
    "simple": 1,
    "double": 2
  },
  "status": "en_attente",
  "created_at": "2026-04-09 15:30:00",
  ...
}
```

## Compatibilité

### Avec index.html (Page d'Accueil)
- ✅ Même format `selected_rooms` (objet type/quantité)
- ✅ Même API backend (reserve.php)
- ✅ Même structure de données en base

### Avec le Système Admin
- ✅ Le format `selected_rooms` est compatible avec le système existant
- ✅ L'admin peut valider les réservations du client-dashboard comme d'autres

## Points de Test à Vérifier

### Test 1: Sélection des Chambres
- [ ] Les boutons +/- changent la quantité dans l'input
- [ ] Le calcul de capacité se met à jour automatiquement
- [ ] Le message d'avertissement s'affiche si capacité insuffisante
- [ ] On ne peut pas descendre en-dessous de 0
- [ ] On ne peut pas dépasser la disponibilité

### Test 2: Sélection des Activités
- [ ] Les checkboxes d'activités peuvent être sélectionnées/désélectionnées
- [ ] Les IDs d'activités sont correctement collectés (pas les noms)
- [ ] Pas de doublon dans le array

### Test 3: Validation du Formulaire
- [ ] Au moins une chambre doit être sélectionnée
- [ ] Le téléphone doit avoir 10 chiffres
- [ ] Les dates doivent être valides (arrivée < départ)
- [ ] La capacité doit être >= nombre de personnes

### Test 4: Soumission
- [ ] Le bouton de validation est désactivé pendant l'envoi
- [ ] La requête AJAX est envoyée à `/api/reservations/reserve.php`
- [ ] Le format `selected_rooms` est sérialisé en JSON
- [ ] Le format `activities` est sérialisé en JSON

### Test 5: Réponse du Backend
- [ ] Réponse avec `success: true` si tout est OK
- [ ] Réponse avec `success: false` si erreur de validation
- [ ] Un ID de réservation est retourné
- [ ] La réservation est bien stockée en base

## Données JSON Envoyées par le Frontend

```
Exemple complet de POST data:
{
  nom: "Dupont",
  prenom: "Jean",
  email: "jean@example.com",
  telephone: "0612345678",
  date_arrivee: "2026-04-15",
  date_depart: "2026-04-20",
  nb_personnes: "5",
  selected_rooms: '{"simple":1,"double":2}',
  activities: '[1,2]'
}
```

## Différences Clés par rapport à l'Ancien Système

| Aspect | Ancien | Nouveau |
|--------|--------|---------|
| Sélection chambres | Checkboxes simples | Quantités (+/-) |
| Données envoyées | `room_ids: [1,2,3]` | `selected_rooms: {"simple":1,"double":2}` |
| Format stocké | `room_numbers` | `selected_rooms` + `room_numbers` |
| Validité d'activités | Noms d'activités | IDs d'activités |

## Prochaines Étapes

1. ✅ **Frontend Updated**: Système de quantités et envoi de `selected_rooms`
2. ✅ **Backend Updated**: Réception et stockage de `selected_rooms`
3. **Testing**: Tester le flux complet end-to-end
4. **Admin Integration**: Vérifier que l'admin peut valider les réservations du client-dashboard
5. **Email Notifications**: S'assurer que les emails sont bien envoyés après validation


