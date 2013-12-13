@echo off

if exist small.obj del small.obj
if exist small.exe del small.exe
if exist rsrc.obj del /f /q rsrc.obj

\masm32\bin\rc /r rsrc.rc
\masm32\bin\cvtres /machine:ix86 rsrc.res

\masm32\bin\ml /c /coff /nologo small.asm
\masm32\bin\link /subsystem:windows /merge:.rdata=.text small.obj rsrc.obj