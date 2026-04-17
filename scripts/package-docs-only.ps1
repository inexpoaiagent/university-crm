param(
    [string]$ProjectRoot = (Resolve-Path "$PSScriptRoot\..").Path,
    [string]$OutputDir = (Join-Path (Resolve-Path "$PSScriptRoot\..").Path "outputs"),
    [string]$PackageName = "vertue-crm-buyer-documentation-v1.0.0"
)

$ErrorActionPreference = "Stop"

$docsRoot = Join-Path $ProjectRoot "docs"
$packRoot = Join-Path $docsRoot "buyer-pack"
$zipPath = Join-Path $OutputDir "$PackageName.zip"

if (!(Test-Path $packRoot)) {
    throw "buyer-pack folder not found: $packRoot"
}

if (!(Test-Path $OutputDir)) {
    New-Item -ItemType Directory -Path $OutputDir | Out-Null
}

if (Test-Path $zipPath) {
    Remove-Item -Force $zipPath
}

Compress-Archive -Path "$packRoot\*" -DestinationPath $zipPath -Force

Write-Host "Docs package created:"
Write-Host $zipPath
