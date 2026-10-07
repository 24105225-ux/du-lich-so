param(
    [string]$Output = "database/backup/dulichso-$(Get-Date -Format yyyyMMdd-HHmmss).sql"
)

Set-StrictMode -Version Latest
$ErrorActionPreference = "Stop"
$envFile = Join-Path $PSScriptRoot "..\backend\.env"
if (-not (Test-Path $envFile)) { throw "Khong tim thay backend/.env" }

$vars = @{}
Get-Content $envFile | ForEach-Object {
    if ($_ -match '^([A-Za-z_][A-Za-z0-9_]*)=(.*)$') {
        $vars[$Matches[1]] = $Matches[2].Trim('"')
    }
}

$host = if ($vars.ContainsKey('DB_HOST')) { $vars['DB_HOST'] } else { '127.0.0.1' }
$port = if ($vars.ContainsKey('DB_PORT')) { $vars['DB_PORT'] } else { '3306' }
$db = if ($vars.ContainsKey('DB_DATABASE')) { $vars['DB_DATABASE'] } else { 'dulichso' }
$user = if ($vars.ContainsKey('DB_USERNAME')) { $vars['DB_USERNAME'] } else { 'root' }
$pass = if ($vars.ContainsKey('DB_PASSWORD')) { $vars['DB_PASSWORD'] } else { '' }

$parent = Split-Path -Parent (Join-Path $PSScriptRoot "..\$Output")
New-Item -ItemType Directory -Force -Path $parent | Out-Null

$MYSQLDUMP = "mysqldump"
if ($pass) {
    & $MYSQLDUMP -h $host -P $port -u $user "--password=$pass" --single-transaction --routines --triggers $db > (Join-Path $PSScriptRoot "..\$Output")
} else {
    & $MYSQLDUMP -h $host -P $port -u $user --single-transaction --routines --triggers $db > (Join-Path $PSScriptRoot "..\$Output")
}

Write-Host "BACKUP_OK: $Output" -ForegroundColor Green
