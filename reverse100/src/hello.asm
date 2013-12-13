.model tiny
.data
	flag	db  078h, 076h, 06Ah, 023h, 023h, 02Bh, 025h, 02Ah, 070h, 02Bh, 027h, 026h, 076h, 022h, 020h, 075h, 02Bh, 02Ah, 072h, 037h ;'key00869c845e13f89a$'
.code
	org	100h
start:
	mov	cx, sizeof flag
	mov	si, offset flag

lbl:
	xor	byte ptr [si], 13h
	inc	si
	loop	lbl

	mov	ah, 09h
	lea	dx, flag
	int	21h

	mov	ax, 4c00h
	int	21h
end start