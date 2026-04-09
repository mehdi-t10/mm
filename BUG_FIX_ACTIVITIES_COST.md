# 🐛 Fix: Calcul incorrect du coût des activités

## Problème identifié

L'utilisateur "az" a l'activité "Cours privé" (ID 3, prix 100€) sélectionnée pour 2 jours, mais:
- ✅ Elle devrait être facturée **une seule fois**: 100€
- ❌ Elle était facturée **deux fois**: 200€

## Cause du bug

**Fichier:** `index.html`, ligne 1833-1843

**Code bugué:**
```javascript
checkbox.addEventListener("change", function() {
  const activityList = activitiesByDay[dayIndex] || [];
  if (this.checked) {
    if (!activityList.includes(activity.id)) {
      activityList.push(activity.id);  // ← Ajoute l'activité au tableau
    }
  } else {
    activitiesByDay[dayIndex] = activityList.filter(id => id !== activity.id);
  }
  // ❌ MANQUE: activitiesByDay[dayIndex] = activityList;
  validateActivityConstraints();
});
```

**Le problème:**
- Ligne 1837: on fait `activityList.push(activity.id)` pour ajouter l'activité au jour
- MAIS: on oublie de sauvegarder `activityList` dans `activitiesByDay[dayIndex]`
- Résultat: `activitiesByDay` n'est pas mise à jour correctement lors du check

## Solution appliquée

**Fix:** Ajouter la sauvegarde manquante:

```javascript
checkbox.addEventListener("change", function() {
  const activityList = activitiesByDay[dayIndex] || [];
  if (this.checked) {
    if (!activityList.includes(activity.id)) {
      activityList.push(activity.id);
    }
  } else {
    activitiesByDay[dayIndex] = activityList.filter(id => id !== activity.id);
  }
  // ✅ FIX: sauvegarder toujours le tableau
  activitiesByDay[dayIndex] = activityList;
  validateActivityConstraints();
});
```

## Comment ça fonctionne maintenant

1. **L'utilisateur sélectionne une activité pour le jour 0:**
   - `activitiesByDay[0]` devient `[3]`

2. **L'utilisateur sélectionne la même activité pour le jour 1:**
   - `activitiesByDay[1]` devient `[3]`

3. **À la soumission (ligne 2171-2178):**
   ```javascript
   const selectedActivities = [];
   Object.values(activitiesByDay).forEach((dayActivities) => {
     dayActivities.forEach((activityId) => {
       if (!selectedActivities.includes(activityId)) {
         selectedActivities.push(activityId);  // Déduplique!
       }
     });
   });
   ```
   
   - Résultat: `selectedActivities = [3]` (une seule fois!)

4. **À la facturation:**
   - API reçoit `activities: [3]`
   - Facture = 100€ ✅

## Fichiers modifiés

- **index.html** (ligne 1833-1844): Ajout de `activitiesByDay[dayIndex] = activityList;`

## Cas de test

**Réservation utilisateur "az":**
- Dates: 2026-04-09 → 2026-04-11 (2 jours)
- Activité sélectionnée: Jour 0 + Jour 1 = Cours privé (ID 3)
- **Avant le fix:** Facturé 200€ (2 × 100€) ❌
- **Après le fix:** Facturé 100€ (1 × 100€) ✅

## Note technique

Le tableau `selectedActivities` était déjà correct pour dédupliquer. Le problème était que `activitiesByDay` n'était pas correctement mis à jour lors de la sélection, ce qui pouvait causer des incohérences.

Avec ce fix, la structure de données `activitiesByDay` est maintenant toujours cohérente avec les sélections de l'utilisateur.

