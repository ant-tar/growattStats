param(
    [Parameter(Mandatory = $true)][string]$ModxPath,
    [Parameter(Mandatory = $true)][string]$PhpPath,
    [string]$TaskName = 'growattStats-Laragon-CronManager'
)

$ErrorActionPreference = 'Stop'
$sitePath = (Resolve-Path -LiteralPath $ModxPath).Path
$phpExecutable = (Resolve-Path -LiteralPath $PhpPath).Path
$cronScript = Join-Path $sitePath 'assets\components\cronmanager\cron.php'
if (!(Test-Path -LiteralPath $cronScript -PathType Leaf)) {
    throw 'CronManager cron.php is missing from the selected MODX installation.'
}
if ((Split-Path -Leaf $phpExecutable) -ne 'php-win.exe') {
    throw 'Use php-win.exe so scheduled runs do not open console windows.'
}
$arguments = '-f "' + $cronScript + '"'
$existingTask = Get-ScheduledTask -TaskName $TaskName -ErrorAction SilentlyContinue
if ($existingTask) {
    if ($existingTask.Actions.Count -ne 1 -or
        $existingTask.Actions[0].Execute -ne $phpExecutable -or
        $existingTask.Actions[0].Arguments -ne $arguments) {
        throw 'A task with this name uses a different command. Choose another TaskName.'
    }
    Write-Output "Existing task retained: $TaskName"
    return
}
$identity = [Security.Principal.WindowsIdentity]::GetCurrent().Name
$action = New-ScheduledTaskAction -Execute $phpExecutable -Argument $arguments -WorkingDirectory $sitePath
$trigger = New-ScheduledTaskTrigger -Once -At (Get-Date).AddMinutes(1) -RepetitionInterval (New-TimeSpan -Minutes 1)
$principal = New-ScheduledTaskPrincipal -UserId $identity -LogonType Interactive -RunLevel Limited
$settings = New-ScheduledTaskSettingsSet -MultipleInstances IgnoreNew -ExecutionTimeLimit (New-TimeSpan -Minutes 2)
Register-ScheduledTask -TaskName $TaskName -Action $action -Trigger $trigger -Principal $principal `
    -Settings $settings -Description 'Run local MODX CronManager every minute while this user is logged in. Requires Laragon.' |
    Out-Null
Write-Output "Created task: $TaskName"
