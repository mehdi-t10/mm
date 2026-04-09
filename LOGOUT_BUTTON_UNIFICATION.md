# ✅ Bouton de déconnexion - Unification des interfaces

## Objectif
Utiliser le **même bouton de déconnexion** dans les deux interfaces (client et admin) pour une cohérence visuelle.

## Changements appliqués

### 1. **Ajout de la variable CSS `--green-dark`**
**Fichier:** `admin-dashboard.html` (ligne 22-30)

**Avant:**
```css
:root {
  --bg: #070a0f;
  --green: #00e676;
  --gold: #ffd740;
  ...
}
```

**Après:**
```css
:root {
  --bg: #070a0f;
  --green: #00e676;
  --green-dark: #00c853;  /* ← AJOUTÉ */
  --gold: #ffd740;
  ...
}
```

### 2. **Ajout du style `.btn-logout` du client**
**Fichier:** `admin-dashboard.html` (ligne 136-148)

```css
/* Style du bouton de déconnexion du client */
.btn-logout {
  background: var(--green);
  color: #001a0a;
  border: none;
  padding: 8px 16px;
  border-radius: 8px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 0.9rem;
}

.btn-logout:hover {
  background: var(--green-dark);
}
```

### 3. **Remplacement du bouton de déconnexion**
**Fichier:** `admin-dashboard.html` (ligne 583)

**Avant:**
```html
<button class="logout-btn" onclick="logout()">🚪 Déconnexion</button>
```

**Après:**
```html
<button class="btn-logout" onclick="logout()">🚪 Déconnexion</button>
```

## Comparaison visuelle

### **Interface Client**
- **Couleur:** Vert (`--green: #00e676`)
- **Hover:** Vert foncé (`--green-dark: #00c853`)
- **Padding:** 8px 16px
- **Font weight:** 700 (bold)
- **Font size:** 0.9rem
- **Transition:** Smooth 0.2s

### **Interface Admin - Avant**
- **Couleur:** Or avec gradient
- **Hover:** Lift effect + shadow
- **Padding:** 10px 24px
- **Font weight:** 600

### **Interface Admin - Après** ✅
- **Couleur:** Vert (identique au client)
- **Hover:** Vert foncé (identique au client)
- **Padding:** 8px 16px (identique au client)
- **Font weight:** 700 (identique au client)
- **Font size:** 0.9rem (identique au client)
- **Transition:** Smooth 0.2s (identique au client)

## Bénéfices

✅ **Cohérence visuell:** Les deux interfaces utilisent le même style  
✅ **Expérience utilisateur:** L'utilisateur reconnaît le bouton partout  
✅ **Maintenabilité:** Un seul style à maintenir  
✅ **Branding:** Utilise la couleur verte primaire du système  

## Fichiers modifiés

- ✅ `admin-dashboard.html`
  - Ligne 22-30: Ajout de `--green-dark`
  - Ligne 136-148: Ajout du style `.btn-logout`
  - Ligne 583: Changement de classe du bouton

## Testing

Pour vérifier le changement:
1. Ouvrir `admin-dashboard.html`
2. Observer le bouton "🚪 Déconnexion" - il doit être **vert**
3. Le hover doit rendre le bouton **vert foncé**
4. Comparer avec le bouton du `client-dashboard.html` - les styles doivent être identiques

