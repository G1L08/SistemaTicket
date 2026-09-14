$ErrorActionPreference = "Stop"

Write-Host "=== VALIDACION PREVIA DEL PROYECTO ===" -ForegroundColor Cyan
Write-Host "Ruta: $(Get-Location)"

$php = Get-Command php -ErrorAction SilentlyContinue
if (-not $php) {
    Write-Host "[ERROR] PHP no esta disponible en PATH." -ForegroundColor Red
    Write-Host "Instala/configura PHP y vuelve a ejecutar la validacion."
    exit 1
}

Write-Host "`n[1/3] Version de PHP" -ForegroundColor Yellow
php -v

Write-Host "`n[2/3] Buscando marcadores de conflicto Git" -ForegroundColor Yellow
$conflicts = Get-ChildItem -Recurse -File |
    Where-Object { $_.FullName -notmatch '\\.git\\' } |
    Select-String -Pattern '^(<<<<<<<|=======|>>>>>>>)' -ErrorAction SilentlyContinue

if ($conflicts) {
    Write-Host "[ERROR] Se encontraron conflictos sin resolver:" -ForegroundColor Red
    $conflicts | ForEach-Object {
        Write-Host ("  {0}:{1}  {2}" -f $_.Path, $_.LineNumber, $_.Line.Trim())
    }
    $hasErrors = $true
} else {
    Write-Host "[OK] No se encontraron marcadores de conflicto." -ForegroundColor Green
}

Write-Host "`n[3/3] Validando sintaxis PHP" -ForegroundColor Yellow
$phpFiles = Get-ChildItem .\api -Recurse -Filter *.php -File
foreach ($file in $phpFiles) {
    $output = & php -l $file.FullName 2>&1
    if ($LASTEXITCODE -ne 0) {
        Write-Host "[ERROR] $($file.FullName)" -ForegroundColor Red
        Write-Host $output
        $hasErrors = $true
    }
}

if ($hasErrors) {
    Write-Host "`nRESULTADO: NO PUBLICAR. Corrige los errores anteriores." -ForegroundColor Red
    exit 1
}

Write-Host "`nRESULTADO: Validacion tecnica basica correcta." -ForegroundColor Green
Write-Host "Aun se deben ejecutar pruebas funcionales y de base de datos antes del push."
