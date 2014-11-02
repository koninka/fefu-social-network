# Installer for Windows (Powershell version)
# path to git should be in PATH variable to make clone
Add-Type -AssemblyName System.Windows.Forms
Add-Type -AssemblyName System.Drawing 

function DownloadFile($url, $targetFile){
    Write-Host Downloading $url 
    $uri = New-Object "System.Uri" "$url" 
    $request = [System.Net.HttpWebRequest]::Create($uri) 
    $request.set_Timeout(15000) #15 second timeout 
    $response = $request.GetResponse() 
    $totalLength = [System.Math]::Floor($response.get_ContentLength()/1024) 
    $responseStream = $response.GetResponseStream() 
    $targetStream = New-Object -TypeName System.IO.FileStream -ArgumentList $targetFile, Create 
    $buffer = new-object byte[] 10KB 
    $count = $responseStream.Read($buffer,0,$buffer.length) 
    $downloadedBytes = $count 
    while ($count -gt 0){ 
        [System.Console]::CursorLeft = 0 
        [System.Console]::Write("Downloaded {0}K of {1}K", [System.Math]::Floor($downloadedBytes/1024), $totalLength) 
        $targetStream.Write($buffer, 0, $count) 
        $count = $responseStream.Read($buffer,0,$buffer.length) 
        $downloadedBytes = $downloadedBytes + $count 
    } 
    Write-Host " Finished Download" 
    $targetStream.Flush()
    $targetStream.Close() 
    $targetStream.Dispose() 
    $responseStream.Dispose() 
}

function UnzipFile($file, $destination){ 
    Write-Host Extract $file to $destination  
    $shell = new-object -com shell.application
    $zip = $shell.Namespace($file)
    $shell.Namespace($destination).copyhere($zip.items())
    Write-Host Done
}

function CheckCommand($cmdName){
    if(Get-Command $cmdName -errorAction SilentlyContinue){
        return $true
    } else {
        return $false
    }
}

function ChooseDir(){
    $FolderBrowser = New-Object System.Windows.Forms.FolderBrowserDialog -Property @{
        Description = 'Select folder for PHP'
        RootFolder = 'MyComputer'
    }
    [void]$FolderBrowser.ShowDialog()
    return $FolderBrowser.SelectedPath
}

function UpdatePath(){
    $env:Path = [environment]::GetEnvironmentVariable("Path","Machine") +';'+ [environment]::GetEnvironmentVariable("Path","User")
}

function AddToPath($dir){
    $path = $env:PATH.Split(';')
    Write-Host $path
    Write-Host $dir
    Write-Host $path -notcontains $dir
    if ($path -notcontains $dir){
        $user_path = [environment]::GetEnvironmentVariable("Path","User").Split(';')
        $user_path += $dir
        [Environment]::SetEnvironmentVariable("Path", [String]::Join(';', $user_path), "User")
        UpdatePath
    }
}

function InstallPHP(){
    $dir = ChooseDir
    if ($dir -ne $NULL){
        Write-Host Selected path $dir
        DownloadFile http://windows.php.net/downloads/releases/archives/php-5.6.1-Win32-VC11-x86.zip ./cache/php.zip
        UnzipFile $((Get-Location).Path + "\cache\php.zip") $dir
        AddToPath $dir
        cp $($dir + "\php.ini-development") $($dir + "\php.ini")
    }
}

function RegistryCheckMySQL(){
    $a = Get-ItemProperty HKLM:\Software\Microsoft\Windows\CurrentVersion\Uninstall\*
    foreach($b in $a){
        if (Test-Path $($b.InstallLocation + "bin\mysql.exe")){
	    return $True
	}
    }
    return $False
}

function InstallMySQL(){
    if ( -not ( RegistryCheckMySQL ) ){
    $title   = 'I dont find mysql?'
    $prompt  = 'Should I install it?'
    $yes     = New-Object System.Management.Automation.Host.ChoiceDescription '&Yes','Continues the operation'
    $no      = New-Object System.Management.Automation.Host.ChoiceDescription '&No','Aborts the operation'
    $options = [System.Management.Automation.Host.ChoiceDescription[]] ($yes,$no)
    $choice  = $host.ui.PromptForChoice($title,$prompt,$options,0)
    if ($choice -eq 0){
        DownloadFile http://dev.mysql.com/get/Downloads/MySQLInstaller/mysql-installer-community-5.6.21.1.msi .\cache\mysql_installer.msi
        start-process .\cache\mysql_installer.msi -Wait
	Write-Host -NoNewLine 'Press any key to continue'
        $null = $Host.UI.RawUI.ReadKey('NoEcho,IncludeKeyDown')
        UpdatePath
    }
    } else {
	Write-Host mysql is installed
    }
}

function InstallGit(){
    DownloadFile https://github.com/msysgit/msysgit/releases/download/Git-1.9.4-preview20140929/Git-1.9.4-preview20140929.exe .\cache\git_installer.exe
    start-process .\cache\git_installer.exe -Wait
}

function CheckAndInstall($name, [scriptblock]$install_block){
    if (CheckCommand($name)){
        Write-Host $name is installed
    } else {
        & $install_block
    }
}

function EnableExtensionDir($ini){
    cat $ini | % { $_ -replace ";+", ";"} | % { $_ -replace "^;\s*extension_dir\s*=\s*`"ext`"", "extension_dir = `"ext`""} | sc temp_repl
    cat temp_repl | sc $ini
    del temp_repl
    Write-Host extension_dir
}

function RealpathCacheSize($ini){
    cat $ini | % { $_ -replace ";+", ";"} | % { $_ -replace "^;\s*realpath_cache_size\s*=.*", "realpath_cache_size = 1024K" } | sc temp_repl
    cat temp_repl | sc $ini
    del temp_repl
    Write-Host realpath_cache_size
}

function EnablePhpExtension($extension, $ini){
    cat $ini | % { $_ -replace ";+", ";"} | % { $_ -replace "^;extension=$extension`$", "extension=$extension" } | sc temp_repl
    cat temp_repl | sc $ini
    del temp_repl 
    Write-Host $extension
}

function SetTimeZone($timezone, $ini){
    cat $ini | % { $_ -replace ";+", ";"} | % { $_ -replace "^;date.timezone", "date.timezone"} | % { $_ -replace "date.timezone =.*`$", "date.timezone = $timezone" } | sc temp_repl
    cat temp_repl | sc $ini
    del temp_repl
    Write-Host timezone = $timezone
}

function GetTimeZones(){
    $TIMEZONESCRIPT="temp.timezone.php"
    "`<?php" | sc $TIMEZONESCRIPT 
    "`$timezones = DateTimeZone::listIdentifiers`(DateTimeZone::ALL`)`;" | ac $TIMEZONESCRIPT 
    "foreach `(`$timezones as `$timezone`) `{" | ac $TIMEZONESCRIPT 
    "print `$timezone`.`"\n`"`;" | ac $TIMEZONESCRIPT 
    "`}" | ac $TIMEZONESCRIPT 
    "?`>" | ac $TIMEZONESCRIPT 
    $timezones = php $TIMEZONESCRIPT
    return $timezones
}

function ConfigureTimeZone($ini){
    $timezones = GetTimeZones
    $script:selectedItem = $NULL
    $objForm = New-Object System.Windows.Forms.Form 
    $objForm.Text = "Select your timezone"
    $objForm.Size = New-Object System.Drawing.Size(300,520) 
    $objForm.StartPosition = "CenterScreen"
    $objForm.FormBorderStyle = "FixedDialog"

    $objForm.KeyPreview = $True
    $objForm.Add_KeyDown({if ($_.KeyCode -eq "Enter") 
        {$script:selectedItem=$objListBox.SelectedItem;$objForm.Close()}})
    $objForm.Add_KeyDown({if ($_.KeyCode -eq "Escape") 
        {$objForm.Close()}})

    $OKButton = New-Object System.Windows.Forms.Button
    $OKButton.Location = New-Object System.Drawing.Size(75,440)
    $OKButton.Size = New-Object System.Drawing.Size(75,23)
    $OKButton.Text = "OK"
    $OKButton.Add_Click({
        $script:selectedItem=$objListBox.SelectedItem;
        $objForm.Close()
    })
    $objForm.Controls.Add($OKButton)

    $CancelButton = New-Object System.Windows.Forms.Button
    $CancelButton.Location = New-Object System.Drawing.Size(150,440)
    $CancelButton.Size = New-Object System.Drawing.Size(75,23)
    $CancelButton.Text = "Cancel"
    $CancelButton.Add_Click({$objForm.Close()})
    $objForm.Controls.Add($CancelButton)

    $objLabel = New-Object System.Windows.Forms.Label
    $objLabel.Location = New-Object System.Drawing.Size(10,20) 
    $objLabel.Size = New-Object System.Drawing.Size(280,20) 
    $objLabel.Text = "Please select your timezone:"
    $objForm.Controls.Add($objLabel) 

    $objListBox = New-Object System.Windows.Forms.ListBox 
    $objListBox.Location = New-Object System.Drawing.Size(10,40) 
    $objListBox.Size = New-Object System.Drawing.Size(260,20) 
    $objListBox.Height = 400

    foreach ($timezone in $timezones){
        [void] $objListBox.Items.Add($timezone)
    }

    $objForm.Controls.Add($objListBox) 

    $objForm.Topmost = $True

    $objForm.Add_Shown({$objForm.Activate()})
    [void] $objForm.ShowDialog()
    $x = $script:selectedItem
    if ($x -ne $NULL){
        SetTimeZone $x $ini
    }
}

function ConfigurePhpIni(){
    if (CheckCommand php){
        $ini = $(split-path $(Get-Command php).path) + "\php.ini"
        if (-Not (Test-Path $ini)){
            cp $($ini + "-development") $ini
        }
        Write-Host Configuring php.ini
        EnableExtensionDir $ini
        RealpathCacheSize $ini
        if ( ( CheckCommand mysql ) -or ( RegistryCheckMySQL ) ){
            EnablePhpExtension "php_pdo_mysql.dll" $ini
        }
        EnablePhpExtension "php_intl.dll" $ini
        EnablePhpExtension "php_openssl.dll" $ini
        EnablePhpExtension "php_mbstring.dll" $ini
        ConfigureTimezone $ini
        Write-Host Configure php.ini: Done!
    } else {
        Write-Host "php not installed, Aborting..."
        exit
    }
}

function CloneRepo(){
    Write-Host Init repo
    if (-Not (Test-Path .git)) {
        git init
    }
    git remote add upstream https://github.com/koninka/fefu-social-network
    git fetch upstream
    git checkout upstream/master
    Write-Host Init repo: Done!
}

function InstallComposer(){
    if (-not (Test-Path composer.phar) -and -not (CheckCommand composer) ){
        DownloadFile https://getcomposer.org/installer .\cache\composer_installer
        php .\cache\composer_installer
    }
}

function InstallBundles(){
    if (Test-Path composer.phar){
        php composer.phar install
    } elseif (CheckCommand composer) {
        composer install
    } else {
        Write-Host "composer not installed, Aborting..."
        exit
    }
}

UpdatePath
mkdir cache
CheckAndInstall php   { InstallPHP }
CheckAndInstall mysql { InstallMySQL }
CheckAndInstall git   { InstallGit }
ConfigurePhpIni
CloneRepo
InstallComposer
InstallBundles
php app/console doctrine:database:create
php app/console doctrine:schema:create
php app/check.php
Remove-Item cache -Recurse
