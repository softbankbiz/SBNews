@echo off
:Shift_JIS -> UTF-8
setlocal enabledelayedexpansion
for %%f in (%*) do (
  echo %%~ff| findstr /l /e /i ".txt .csv"
  if !ERRORLEVEL! equ 0 (
    powershell -nop -c "&{[IO.File]::WriteAllText($args[1], [IO.File]::ReadAllText($args[0], [Text.Encoding]::GetEncoding(932)))}" \"%%~ff\" \"%%~ff.utf8%%~xf\"
  )
)