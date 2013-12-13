.386
.model flat, stdcall
option casemap :none

include \masm32\include\windows.inc
include \masm32\include\user32.inc
include \masm32\include\kernel32.inc

includelib \masm32\lib\user32.lib
includelib \masm32\lib\kernel32.lib

.code

szTitle		db "Hello, world!", 0
szMsg		db "This is not a flag!", 0

start:
	invoke MessageBox, 0, addr szMsg, addr szTitle, MB_OK
	invoke ExitProcess, 0
end start