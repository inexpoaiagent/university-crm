param(
    [string]$ProjectRoot = (Resolve-Path "$PSScriptRoot\..").Path,
    [string]$OutputDir = (Join-Path (Resolve-Path "$PSScriptRoot\..").Path "outputs"),
    [string]$PackageName = "vertue-crm-student-portal-v1.0.0"
)

$ErrorActionPreference = "Stop"

if (!(Test-Path $OutputDir)) {
    New-Item -ItemType Directory -Path $OutputDir | Out-Null
}

$stagingDir = Join-Path $OutputDir "$PackageName-staging"
$zipPath = Join-Path $OutputDir "$PackageName.zip"

if (Test-Path $stagingDir) {
    Remove-Item -Recurse -Force $stagingDir
}

if (Test-Path $zipPath) {
    Remove-Item -Force $zipPath
}

New-Item -ItemType Directory -Path $stagingDir | Out-Null

$exclude = @(
    ".git",
    ".github",
    "node_modules",
    ".idea",
    ".vscode",
    "tests",
    "outputs",
    "storage\logs\*.log",
    ".env"
)

Get-ChildItem -Path $ProjectRoot -Force | ForEach-Object {
    $name = $_.Name
    if ($exclude -contains $name) {
        return
    }
    Copy-Item -Path $_.FullName -Destination (Join-Path $stagingDir $name) -Recurse -Force
}

# Clean any nested logs that might still exist
Get-ChildItem -Path $stagingDir -Recurse -File -Filter "*.log" -ErrorAction SilentlyContinue | Remove-Item -Force

Compress-Archive -Path "$stagingDir\*" -DestinationPath $zipPath -Force

Write-Host "Package created:"
Write-Host $zipPath
