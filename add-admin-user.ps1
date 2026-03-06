# PowerShell Script to Add Admin User
# Usage: .\add-admin-user.ps1

Write-Host "Adding admin user to Pharmax database..." -ForegroundColor Cyan
Write-Host ""

try {
    # Colors for output
    $infoColor = "Green"
    $errorColor = "Red"
    $warningColor = "Yellow"
    
    # Check if we're in the project root
    if (-not (Test-Path "vendor/autoload.php")) {
        Write-Host "Error: Not in Pharmax project root!" -ForegroundColor $errorColor
        Write-Host "Please run this script from the project root directory."
        exit 1
    }
    
    # Run the PHP script
    Write-Host "Running PHP script to create user..." -ForegroundColor $warningColor
    
    $output = & php add-admin-user.php 2>&1
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host $output -ForegroundColor $infoColor
        Write-Host ""
        Write-Host "User added successfully!" -ForegroundColor $infoColor
        Write-Host ""
        Write-Host "You can now login with:"
        Write-Host "  Email: nayrouzdaikhi@gmail.com"
        Write-Host "  Password: nayrouz123"
        Write-Host ""
        Write-Host "Face Recognition: Not enabled (optional)"
        Write-Host ""
    } else {
        Write-Host "Error output:" -ForegroundColor $errorColor
        Write-Host $output -ForegroundColor $errorColor
        exit 1
    }
    
} catch {
    Write-Host "Error: $_" -ForegroundColor $errorColor
    exit 1
}
