@echo off

echo === assembly ===

\masm32\bin\ml /c %1.asm
if errorlevel 1 goto errasm

echo === link ===

\masm32\bin\link16 /tiny %1.obj,,nul,,,
if errorlevel 1 goto errlnk

goto success

:errasm
echo assembly error
goto end

:errlnk
echo link error
goto end

:success
echo OK

:end