;---------------------------------------------------;
;   File:         digiclck.asm                      ;
;   Author:       Titarenko Dmitriy                 ;
;   Date:         30.09.2007                        ;
;---------------------------------------------------;

.386
.model flat, stdcall
option casemap :none

include clck.inc

include \masm32\include\windows.inc
include \masm32\include\user32.inc
include \masm32\include\kernel32.inc
include \masm32\include\gdi32.inc
include \masm32\include\advapi32.inc

includelib \masm32\lib\user32.lib
includelib \masm32\lib\kernel32.lib
includelib \masm32\lib\gdi32.lib
includelib \masm32\lib\advapi32.lib

WND_WIDTH		equ 156
WND_HEIGHT		equ 81
WND_BKCOLOR		equ 0
WND_HOVER_TRANSPARENCY	equ 150
WND_LEAVE_TRANSPARENCY	equ 120
FONT_BACK_COLOR		equ 000f2f0fh
FONT_TIME_COLOR		equ 007f7fdfh
FONT_SECONDS_COLOR	equ 007fdf7fh
FONT_DATE_COLOR		equ 00df7f7fh

.data
	szFormat	db '%.2u:%.2u%.2u%u      %.2u.%.2u.%u', 0
	sDayOfWeek	db 'VSPNVTSRCTPTSB' ;'ÂÑÏÍÂÒÑÐ×ÒÏÒÑÁ'
	sTimeBack	db '88:88'
	bLeaved		db 1
	fnt		LOGFONT <60, 20, 0, 0, FW_NORMAL, 0, 0, 0, DEFAULT_CHARSET, OUT_DEFAULT_PRECIS, CLIP_DEFAULT_PRECIS, CLEARTYPE_QUALITY, DEFAULT_PITCH or FF_DONTCARE, 'digiclck'>

.data?
	hInstance	dd ?
	hWndDlg		dd ?
	hDC		dd ?
	time		db 10h dup(?)	; SYSTEMTIME <?>
	sDateTime	db 25 dup(?)
	hTimeFont	dd ?
	hSecFont	dd ?
	hDateFont	dd ?
	hBackDC		dd ?
	hBackBMP	dd ?
	iRelativeX	dd ?
	iRelativeY	dd ?
	ps		PAINTSTRUCT <?>
	rct		RECT <?>
	tme		TRACKMOUSEEVENT <?>

	old		dd ?

	sec		dd ?
	lpData		db 20 dup(?)	; sizeof szKey
	lpcbData	dd ?
	hKey		dd ?

.code

timproc proc
	mov	ebx, offset time
	invoke	GetLocalTime, ebx

	;time.wYear
	movzx	eax, word ptr [ebx]
	push	eax

	;time.wMonth
	movzx	eax, word ptr [ebx + 2]
	push	eax

	;time.wDay
	movzx	eax, word ptr [ebx + 6]
	push	eax

	;time.wDayOfWeek
	movzx	eax, word ptr [ebx + 4]
	push	eax

	;time.wSecond
	movzx	eax, word ptr [ebx + 0ch]
	push	eax

	mov 	sec, eax

	;time.wMinute
	movzx	eax, word ptr [ebx + 0ah]
	push	eax

	;time.wHour
	movzx	eax, word ptr [ebx + 8]
	push	eax

	push	offset szFormat
	push	offset sDateTime
	call	wsprintfA
	add	esp, 36

	mov	ebx, offset sDateTime + 7
	movzx	eax, byte ptr [ebx]
	push	word ptr [eax * 2 - '0' * 2 + sDayOfWeek]
	pop	word ptr [ebx]

	mov	ebx, hBackDC

	push	offset sDateTime
	push	FONT_TIME_COLOR
	push	5
	push	0
	push	7
	push	hTimeFont
	call	PaintTime

	push	offset sDateTime + 5
	push	FONT_SECONDS_COLOR
	push	2
	push	24
	push	121
	push	hSecFont
	call	PaintTime

	invoke	SetBkMode, ebx, OPAQUE
	invoke	SelectObject, ebx, hDateFont
	invoke	SetTextColor, ebx, FONT_DATE_COLOR
	invoke	TextOut, ebx, 9, 55, offset sDateTime + 7, 17

	invoke	InvalidateRect, hWndDlg, 0, 1

	.if sec == 0
		call	checkproc
	.endif

	ret	16
timproc endp

align 4
;===== Encrypt this! =====
enc_start:
	szPath		db 'Software\digiclck', 0
	szName		db 'lic', 0
	szKey		db 'key1dc57f47104ac146', 0
	szMsg		db 'Check your license key!', 0
	szTitle		db 'clck', 0

checkproc:
	mov	lpcbData, sizeof szKey
	invoke	RegOpenKeyEx, HKEY_CURRENT_USER, addr szPath, 0, KEY_QUERY_VALUE, addr hKey
	invoke	RegQueryValueEx, hKey, addr szName, 0, 0, addr lpData, addr lpcbData
	invoke	RegCloseKey, hKey

	invoke	lstrcmp, addr lpData, addr szKey

	.if eax != 0
		invoke	MessageBox, 0, addr szMsg, addr szTitle, MB_OK
	.endif

	ret

align 4
enc_end:
;=====

PaintTime:
	mov	ebp, esp

	invoke	SetBkMode, ebx, OPAQUE
	invoke	SelectObject, ebx, [ebp + 4h]
	invoke	SetTextColor, ebx, FONT_BACK_COLOR
	invoke	TextOut, ebx, [ebp + 8h], [ebp + 0ch], offset sTimeBack, [ebp + 10h]
	invoke	SetTextColor, ebx, [ebp + 14h]
	invoke	SetBkMode, ebx, TRANSPARENT
	invoke	TextOut, ebx, [ebp + 8h], [ebp + 0ch], [ebp + 18h], [ebp + 10h]

	ret	24

DlgProc	proc
	wParam	equ [ebp + 10h]
	lParam	equ [ebp + 14h]
	uMsg	equ [ebp + 0ch]
	hWnd	equ [ebp + 8]

	enter	0, 0

	mov	eax, uMsg
	mov	ebx, hWnd
	xor	edi, edi
	xor	esi, esi
	inc	esi

	.if eax == WM_MOUSEMOVE
		mov	eax, wParam
		cmp	eax, MK_LBUTTON
		jnz	@NotMoved

		invoke	GetWindowRect, ebx, offset rct
		call	@SplitWords
		add	eax, rct.left
		add	ecx, rct.top
		sub	eax, iRelativeX
		sub	ecx, iRelativeY
		invoke	MoveWindow, ebx, eax, ecx, WND_WIDTH, WND_HEIGHT, edi

	@NotMoved:
		cmp	bLeaved, 0
		jz	@MouseHover
		invoke	SetLayeredWindowAttributes, ebx, edi, WND_HOVER_TRANSPARENCY, LWA_ALPHA
		invoke	TrackMouseEvent, offset tme
		mov	bLeaved, 0

	@MouseHover:

	.elseif	eax == WM_PAINT
		invoke	BeginPaint, ebx, offset ps
		invoke	BitBlt, eax, edi, edi, WND_WIDTH, WND_HEIGHT, hBackDC, edi, edi, SRCCOPY
		invoke	EndPaint, ebx, offset ps

	.elseif eax == WM_MOUSELEAVE
		mov	bLeaved, 1
		invoke	SetLayeredWindowAttributes, ebx, edi, WND_LEAVE_TRANSPARENCY, LWA_ALPHA

	.elseif eax == WM_LBUTTONDOWN
		invoke	GetWindowRect, ebx, offset rct
		call	@SplitWords
		mov	iRelativeX, eax
		mov	iRelativeY, ecx

	.elseif	eax == WM_INITDIALOG
		mov	hWndDlg, ebx

		mov	tme.cbSize, 10h
		mov	tme.dwHoverTime, esi
		mov	tme.hwndTrack, ebx
		mov	tme.dwFlags, TME_LEAVE

		invoke	SetWindowPos, ebx, HWND_BOTTOM, edi, edi, edi, edi, SWP_NOMOVE or SWP_NOSIZE or SWP_NOACTIVATE or SWP_NOREDRAW
		invoke	CreateRoundRectRgn, edi, edi, WND_WIDTH, WND_HEIGHT, 6, 6
		invoke	SetWindowRgn, ebx, eax, esi
		invoke	SetLayeredWindowAttributes, ebx, edi, WND_LEAVE_TRANSPARENCY, LWA_ALPHA

		invoke	CreateFontIndirect, offset fnt
		mov	hTimeFont, eax

		mov	fnt.lfHeight, 30
		mov	fnt.lfWidth, 10
		invoke	CreateFontIndirect, offset fnt
		mov	hSecFont, eax

		mov	fnt.lfHeight, 20
		mov	fnt.lfWidth, 8
		invoke	CreateFontIndirect, offset fnt
		mov	hDateFont, eax

		invoke	GetDC, ebx
		mov	hDC, eax

		push	eax					; ReleaseDC(*, hDC)

		push	WND_HEIGHT				; CreateCompatibleBitmap(*, *, WND_HEIGHT)
		push	WND_WIDTH				; CreateCompatibleBitmap(*, WND_WIDTH, *)
		push	eax					; CreateCompatibleBitmap(hDC, *, *)

		invoke	CreateCompatibleDC, eax
		mov	hBackDC, eax

		call	CreateCompatibleBitmap
		mov	hBackBMP, eax

		invoke	SetBkColor, hBackDC, WND_BKCOLOR
		invoke	SelectObject, hBackDC, hBackBMP

		push	ebx
		call	ReleaseDC

		invoke	GetCurrentProcess
		invoke	SetPriorityClass, eax, 00004000h	; BELOW_NORMAL_PRIORITY_CLASS

		;===== SMC =====
		; When executing self-modifying code the use of FlushInstructionCache is required on CPU architectures that do not implement a transparent (self-snooping) I-cache.
		; FlushInstructionCache is not necessary on x86 or x64 CPU architectures as these have a transparent cache.
		invoke	VirtualProtect, offset enc_start, enc_end - enc_start, PAGE_EXECUTE_READWRITE, offset old

		mov	ecx, enc_end - enc_start
		mov	edi, offset enc_start
		mov	edx, 3f98f272h

	decrypt:
		mov	eax, dword ptr [edi]
		xor	eax, edx
		mov	dword ptr [edi], eax
		add	edi, 4
		cmp	edi, enc_end
		jl	decrypt

		invoke	VirtualProtect, offset enc_start, enc_end - enc_start, old, offset old
		;=====

		invoke	SetTimer, ebx, esi, 1000, offset timproc

	.elseif	eax == WM_LBUTTONDBLCLK
		invoke	SendMessage, ebx, WM_CLOSE, edi, edi

	.elseif	eax == WM_CLOSE
		invoke	DeleteDC, hBackDC
		invoke	DeleteObject, hBackBMP
		invoke	DeleteObject, hTimeFont
		invoke	DeleteObject, hSecFont
		invoke	DeleteObject, hDateFont
		invoke	KillTimer, ebx, esi
		invoke	EndDialog, ebx, edi

	.else
		xor	esi, esi

	.endif
	mov	eax, esi
	leave
	ret

@SplitWords:
	mov	eax, lParam
	mov	ecx, eax
	and	eax, 0ffffh
	shr	ecx, 16

	ret

DlgProc	endp

start:
	xor	ebx, ebx

	invoke	GetModuleHandle, ebx
	mov	hInstance, eax
	invoke	DialogBoxParam, eax, IDD_MAIN, ebx, offset DlgProc, ebx
	invoke	ExitProcess, ebx
end start