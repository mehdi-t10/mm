$DocPath = 'D:\parisss\WEB\mm\web.docx'

# Charger les assemblies Word
Add-Type -AssemblyName 'Microsoft.Office.Interop.Word' -ErrorAction SilentlyContinue

if ($null -eq ([Type]::GetType("Microsoft.Office.Interop.Word.Application"))) {
    Write-Host "Word COM n'est pas disponible. Utilisation de approche alternative."
    exit 1
}

# Créer instance Word
$WordApp = New-Object -ComObject Word.Application
$WordApp.Visible = $false
$WordApp.DisplayAlerts = 0

# Créer nouveau document
$Doc = $WordApp.Documents.Add()

# Fonction helper pour ajouter texte formaté
function Add-Title {
    param($Text, $Level = 1)
    $Para = $Doc.Range.InsertParagraphAfter()
    $Para.Text = $Text
    if ($Level -eq 1) { $Para.Style = 'Heading 1' }
    elseif ($Level -eq 2) { $Para.Style = 'Heading 2' }
    elseif ($Level -eq 3) { $Para.Style = 'Heading 3' }
    return $Para
}

function Add-Paragraph {
    param($Text)
    $Para = $Doc.Range.InsertParagraphAfter()
    $Para.Text = $Text
    return $Para
}

# PAGE DE TITRE
Add-Title "FOOTCAMP DREAMS" 1
Add-Paragraph "Application Web de Gestion de Réservations"
Add-Paragraph "Rapport Technique - $(Get-Date -Format 'dd MMMM yyyy')"
Add-Paragraph ""

# PRÉSENTATION GÉNÉRALE
Add-Title "1. PRÉSENTATION GÉNÉRALE" 1
Add-Paragraph "FootCamp Dreams est une application web complète et professionnelle pour la gestion des réservations d'un centre de vacances football premium. L'application permet aux clients de réserver des séjours, de sélectionner des activités et des services, tandis que les administrateurs gèrent les réservations, les chambres, les tarifs et la facturation."

Add-Title "1.1 Objectifs Principaux" 2
$Objectives = @(
    "Faciliter la réservation de séjours pour les clients",
    "Automatiser la gestion des réservations côté administrateur",
    "Optimiser l'allocation des chambres",
    "Générer automatiquement les factures et les devis",
    "Gérer les activités et les installations",
    "Authentifier les utilisateurs de manière sécurisée",
    "Envoyer des notifications par email"
)
foreach ($Obj in $Objectives) {
    $Para = $Doc.Range.InsertParagraphAfter()
    $Para.Text = $Obj
    $Para.Style = 'List Bullet'
}

Add-Title "1.2 Informations du Projet" 2

# Créer un tableau
$Table = $Doc.Tables.Add($Doc.Range, 11, 2)
$Table.Style = 'Light Grid Accent 1'

$Rows = @(
    @("Nom du projet", "FootCamp Dreams"),
    @("Type", "Application Web - SPA (Single Page Application)"),
    @("Technologie Frontend", "HTML5, CSS3, JavaScript (Vanilla + jQuery)"),
    @("Technologie Backend", "PHP 7.4+"),
    @("Stockage des données", "JSON (Fichiers plats)"),
    @("Design", "Responsive - Mobile First"),
    @("Framework CSS", "Bootstrap 5.3.3"),
    @("Icônes", "Font Awesome 6.4.0"),
    @("Polices", "Bebas Neue, DM Sans"),
    @("Version actuelle", "2.0"),
    @("Statut", "Production Ready")
)

$RowNum = 1
foreach ($Row in $Rows) {
    $Table.Rows($RowNum).Cells(1).Range.Text = $Row[0]
    $Table.Rows($RowNum).Cells(2).Range.Text = $Row[1]
    $RowNum++
}

# ARCHITECTURE TECHNIQUE
Add-Title "2. ARCHITECTURE TECHNIQUE" 1
Add-Paragraph "Le projet suit une architecture modulaire et organisée pour une maintenance optimale."

Add-Title "2.1 Structure du Projet" 2
$StructureItems = @(
    "📁 mm/ - Répertoire racine",
    "├── 📄 index.html - Landing page et formulaire de réservation",
    "├── 📄 client-dashboard.html - Tableau de bord client",
    "├── 📄 admin-dashboard.html - Tableau de bord administrateur",
    "├── 📁 api/ - API REST endpoints",
    "│   ├── index.php (Router) - Routeur centralisé",
    "│   ├── auth/ - Authentification",
    "│   ├── reservations/ - Gestion des réservations",
    "│   ├── rooms/ - Gestion des chambres",
    "│   ├── billing/ - Facturation et paiements",
    "├── 📁 assets/ - Ressources statiques (CSS, JS, images)",
    "└── 📁 data/ - Stockage JSON (users, reservations, rooms, etc.)"
)
foreach ($Item in $StructureItems) {
    $Para = $Doc.Range.InsertParagraphAfter()
    $Para.Text = $Item
    $Para.Style = 'No Spacing'
}

Add-Paragraph ""
Add-Title "2.2 Stack Technologique" 2

$StackTable = $Doc.Tables.Add($Doc.Range, 12, 2)
$StackTable.Style = 'Light Grid Accent 1'

$StackRows = @(
    @("Frontend", "HTML5, CSS3, JavaScript (ES6+)"),
    @("Framework Frontend", "Bootstrap 5.3.3"),
    @("JavaScript", "jQuery 3.5.1, Vanilla JS"),
    @("Icônes", "Font Awesome 6.4.0"),
    @("Backend", "PHP 7.4+"),
    @("Base de données", "JSON (fichiers plats)"),
    @("Stockage", "Système de fichiers"),
    @("Communication", "AJAX/Fetch API"),
    @("Email", "SMTP (Gmail/Custom)"),
    @("Chiffrement mot de passe", "bcrypt (PHP)"),
    @("Design responsive", "Mobile First")
)

$RowNum = 1
foreach ($Row in $StackRows) {
    $StackTable.Rows($RowNum).Cells(1).Range.Text = $Row[0]
    $StackTable.Rows($RowNum).Cells(2).Range.Text = $Row[1]
    $RowNum++
}

# FONCTIONNALITÉS PRINCIPALES
Add-Title "3. FONCTIONNALITÉS PRINCIPALES" 1
Add-Title "3.1 Pour les Clients" 2

Add-Title "3.1.1 Réservation" 3
Add-Paragraph "Les clients peuvent créer une réservation en remplissant un formulaire avec:"
$ResFeatures = @(
    "Informations personnelles (nom, prénom, email, téléphone)",
    "Dates d'arrivée et de départ",
    "Nombre de personnes",
    "Sélection des types de chambres (simple, double, triple)",
    "Choix des activités (entraînement, match, cours privé, tournoi)",
    "Sélection des installations (stade principal, terrain 7v7, 5v5)",
    "Services additionnels (repas, transport, etc.)",
    "Validation automatique de la saisie"
)
foreach ($Feature in $ResFeatures) {
    $Para = $Doc.Range.InsertParagraphAfter()
    $Para.Text = $Feature
    $Para.Style = 'List Bullet'
}

Add-Title "3.1.2 Authentification" 3
Add-Paragraph "Un mot de passe est généré automatiquement lors de la création de la réservation:"
$AuthFeatures = @(
    "Génération de mot de passe 12 caractères (majuscules, minuscules, chiffres, caractères spéciaux)",
    "Chiffrement bcrypt des mots de passe",
    "Affichage modal avec identifiants et boutons de copie",
    "Connexion sécurisée au dashboard client",
    "Récupération de mot de passe oublié"
)
foreach ($Feature in $AuthFeatures) {
    $Para = $Doc.Range.InsertParagraphAfter()
    $Para.Text = $Feature
    $Para.Style = 'List Bullet'
}

Add-Title "3.2 Pour les Administrateurs" 2
Add-Title "3.2.1 Gestion des Réservations" 3
$AdminResFeatures = @(
    "Vue d'ensemble de toutes les réservations en attente",
    "Validation/Rejet de réservations",
    "Affichage des détails complets de chaque réservation",
    "Calendrier de disponibilité des chambres",
    "Allocation automatique ou manuelle des chambres",
    "Historique des modifications",
    "Filtrage par statut, date, client"
)
foreach ($Feature in $AdminResFeatures) {
    $Para = $Doc.Range.InsertParagraphAfter()
    $Para.Text = $Feature
    $Para.Style = 'List Bullet'
}

Add-Title "3.2.2 Gestion des Chambres" 3
$RoomsFeatures = @(
    "Trois types de chambres: Simple (1 pers), Double (2 pers), Triple (3 pers)",
    "Affichage du stock total et disponible par type",
    "Tarification par type et par nuit",
    "Visualisation du calendrier des réservations",
    "Assignation intelligente des chambres",
    "Gestion des prix et des capacités"
)
foreach ($Feature in $RoomsFeatures) {
    $Para = $Doc.Range.InsertParagraphAfter()
    $Para.Text = $Feature
    $Para.Style = 'List Bullet'
}

Add-Title "3.2.3 Gestion Financière" 3
$FinancialFeatures = @(
    "Génération automatique de factures",
    "Calcul des tarifs (chambres + activités + services)",
    "Gestion des acomptes/dépôts",
    "Application de réductions et promotions",
    "Enregistrement des paiements",
    "Suivi du statut de paiement",
    "Export des factures en PDF"
)
foreach ($Feature in $FinancialFeatures) {
    $Para = $Doc.Range.InsertParagraphAfter()
    $Para.Text = $Feature
    $Para.Style = 'List Bullet'
}

# Sauvegarder le document
$Doc.SaveAs($DocPath, 1)
$WordApp.Quit()

Write-Host "✓ Rapport généré avec succès: web.docx"

