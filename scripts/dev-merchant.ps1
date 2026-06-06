# Start merchant portal (localhost:8001) with Vite hot reload.
# Usage: .\scripts\dev-merchant.ps1

Set-Location $PSScriptRoot\..

Write-Host "Starting Vite + merchant server on http://localhost:8001" -ForegroundColor Cyan
Write-Host "Press Ctrl+C to stop both processes." -ForegroundColor DarkGray

npm run dev:merchant
