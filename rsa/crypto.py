#!/usr/bin/python3

from sys import *
from http.server import SimpleHTTPRequestHandler
import socketserver
from Crypto.Random.random import StrongRandom
from Crypto.Util.number import *
from Crypto.PublicKey import RSA
from urllib.parse import urlparse, quote_from_bytes, unquote_to_bytes

KEY_LENGTH = 512
EXP_LENGTH = 256
PASSWORD = bytes("dLzjWZ5gj/X+PZHv8+UeiQRK9zzOj/2Nf5CU90SSWtqEm3/jKpEK/o1QsSbTlYDuahgIVZbj", "UTF-8")
p = getPrime(KEY_LENGTH)
q = getPrime(KEY_LENGTH)

class Handler(SimpleHTTPRequestHandler):
	def do_HEAD(s):
		s.send_response(200, "OK")
		s.send_header("Content-type", "text/html")
		s.end_headers()
	def do_GET(s):
		request = urlparse(s.path)
		path = request[2]
		query = request[4]
		if path == "/":
			sendIndex(s)
		elif path == "/public":
			sendPublicKey(s)
		elif path == "/generate":
			sendNewKeyPair(s)
		elif path == "/key":
			sendGetKey(s, query)
		else:
			s.send_response(404, "Not Found")
			s.send_header("Content-type", "text/html")
			s.end_headers()

def sendIndex(s):
	s.send_response(200, "OK")
	s.send_header("Content-type", "text/html")
	s.end_headers()
	s.wfile.write(bytes("<a href='/'>home</a><br>", "UTF-8"))
	s.wfile.write(bytes("<a href='/public'>get server's public key</a><br>", "UTF-8"))
	s.wfile.write(bytes("<a href='/generate'>generate new RSA key pair</a><br>", "UTF-8"))
	s.wfile.write(bytes("<a href='/key'>get key file (need admin permition)</a><br>", "UTF-8"))

def sendNewKeyPair(s):
	s.send_response(200, "OK")
	s.send_header("Content-type", "text/plain")
	s.end_headers()
	s.wfile.write(generateKeyPair().exportKey())

def sendPublicKey(s):
	s.send_response(200, "OK")
	s.send_header("Content-type", "text/plain")
	s.end_headers()
	s.wfile.write(serverKey.exportKey())

def sendGetKey(s, sign):
	if isInt(sign) and serverKey.verify(PASSWORD, [int(sign), 0]):
		s.send_response(200, "OK")
		s.send_header("Content-type", "text/plain")
		s.end_headers()
		s.wfile.write(bytes(key, "UTF-8"))
	else:
		s.send_response(403, "Forbidden")
		s.send_header("Content-type", "text/plain")
		s.end_headers()
		s.wfile.write(bytes("403 Forbidden", "UTF-8"))

def isInt(s):
	try:
		int(s)
		return True
	except ValueError:
		return False

def generateKeyPair():
	p = getPrime(KEY_LENGTH)
	f = (p - 1) * (q - 1);
	e = getRandomInteger(EXP_LENGTH)
	while GCD(e, f) != 1:
		e = getRandomInteger(EXP_LENGTH)
	d = inverse(e, f)
	return RSA.construct([p * q, e, d])

def startServer(port):
	httpd = socketserver.TCPServer(("", port), Handler)
	httpd.serve_forever()

if len(sys.argv) < 3:
	print("usage: %s key port" % sys.argv[0], file=sys.stderr)
	exit(1)

keyFile = open(sys.argv[1])
key = keyFile.read().strip()

serverKey = generateKeyPair()
print(serverKey.exportKey())
serverKey = serverKey.publickey()

startServer(int(sys.argv[2]))
