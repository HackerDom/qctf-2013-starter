@echo off

echo Font installation batch

set font=digiclck.ttf
set script=install.js

echo Check Windows version...

for /f "tokens=4-5 delims=. " %%i in ('ver') do set version=%%i.%%j
if "%version%" lss "6.1" goto old

echo Windows 7 or higher
echo Create installation script

echo var shell = WScript.CreateObject("WScript.Shell");>%script%
echo var app = WScript.CreateObject("Shell.Application");>>%script%
echo var folder = app.NameSpace(shell.CurrentDirectory);>>%script%
echo var font = folder.ParseName("%font%")>>%script%
echo font.InvokeVerb("Install")>>%script%

goto exit

:old

echo Windows Vista or lower

echo Copy font file
copy /y %font% "%SystemRoot%\Fonts" 1> nul

echo Create installation script
echo var app = WScript.CreateObject("Shell.Application");>%script%
echo var folder = app.NameSpace(0x14);>>%script%
echo folder.CopyHere("%font%");>>%script%

:exit

echo Run installation script
cscript /nologo %script%

echo Remove temp files
del /q %script%

echo =====
echo Done!
echo If some errors occured install font "%font%" manually