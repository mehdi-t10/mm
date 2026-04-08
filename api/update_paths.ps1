$apiDir = Get-Location
Get-ChildItem -Recurse -File -Filter "*.php" | Where-Object { $_.Name -ne "utils.php" } | ForEach-Object {
    $path = $_.FullName
    $content = [System.IO.File]::ReadAllText($path)
    $content = $content -replace "require_once 'utils.php';", "require_once __DIR__ . '/../utils.php';"
    [System.IO.File]::WriteAllText($path, $content)
    Write-Host "✅ Mis à jour: $($_.Name)"
}
Write-Host "✅ Tous les chemins ont été mis à jour!"

