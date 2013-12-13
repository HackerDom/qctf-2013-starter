@echo off

if exist rsrc.obj del /f /q rsrc.obj

echo === compile res ===

\masm32\bin\rc /r rsrc.rc
if errorlevel 1 goto errres

\masm32\bin\cvtres /machine:ix86 rsrc.res

if exist %1.obj del /f /q %1.obj
if exist %1.exe del /f /q %1.exe

echo === assembly ===

\masm32\bin\ml /c /coff %1.asm
if errorlevel 1 goto errasm

echo === link ===

\masm32\bin\link /subsystem:windows /opt:noref /release %1.obj rsrc.obj
if errorlevel 1 goto errlnk

goto success

:errres
echo _
echo resources compile error
goto end

:errasm
echo _
echo assembly error
goto end

:errlnk
echo _
echo link error
goto end

:success
echo OK

:end