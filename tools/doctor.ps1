# tools/doctor.ps1
# CSE703073 - Kiem chung moi truong phat trien tren Windows

$checks = @(
    @{ n = "git";      c = "git --version";      r = "Git >= 2.40" },
    @{ n = "php";      c = "php -v";            r = "PHP >= 8.2 (khuyen nghi 8.3)" },
    @{ n = "composer"; c = "composer --version"; r = "Composer >= 2.7" },
    @{ n = "mysql";    c = "mysql --version";    r = "MySQL >= 8.0" },
    @{ n = "node";     c = "node -v";            r = "Node >= 20" },
    @{ n = "npm";      c = "npm -v";             r = "npm >= 10" },
    @{ n = "python";   c = "python --version";   r = "Python >= 3.11 (khuyen nghi 3.12)" },
    @{ n = "docker";   c = "docker --version";   r = "Docker >= 24" }
)

Write-Host "== Kiem chung moi truong CSE703073 =="

foreach ($k in $checks) {
    $exe = $k.c.Split(" ")[0]

    if (Get-Command $exe -ErrorAction SilentlyContinue) {
        $v = (
            Invoke-Expression $k.c 2>&1 |
            Select-Object -First 1
        )

        Write-Host (
            " [OK] {0,-10} {1}" -f $k.n, $v
        )
    }
    else {
        Write-Host (
            " [THIEU] {0,-10} can: {1}" -f $k.n, $k.r
        ) -ForegroundColor Yellow
    }
}

Write-Host ""
Write-Host "-- Phan mo rong PHP bat buoc --"

$requiredExtensions = @(
    "pdo_mysql",
    "mbstring",
    "openssl",
    "fileinfo",
    "curl",
    "zip",
    "intl",
    "gd"
)

$phpModules = php -m 2>$null

foreach ($extension in $requiredExtensions) {
    if ($phpModules -contains $extension) {
        Write-Host " [OK] $extension"
    }
    else {
        Write-Host (
            " [THIEU] {0} - bat trong php.ini" -f $extension
        ) -ForegroundColor Yellow
    }
}

Write-Host ""
Write-Host "-- Cau hinh Git ca nhan --"

Write-Host (
    " user.name : {0}" -f (git config user.name)
)

Write-Host (
    " user.email: {0}" -f (git config user.email)
)
