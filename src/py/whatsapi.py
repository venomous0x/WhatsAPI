'''
Copyright (C) 2011 Venemous <venomous0x@gmail.com>

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

WhatsApp, WhatsApp service, clients and logo are registered trademarks of WhatsApp Inc. All other trademarks are the property of their respective owners.
Usage of WhatsApp service is subjected to WhatsApp Legal Terms.
'''
import socket
import uuid
import hashlib
import base64
import struct
import time
import wa_functions

# \x05\xf8\x03\x74\xa2\x11 idle

_server = "s.whatsapp.net"
_NC = "00000001"
_Qop = "auth"
_digset_uri = "xmpp/s.whatsapp.net"

HOST = 'bin-short.whatsapp.net'
PORT = 5222

def init():
	global s
	s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
	s.connect((HOST, PORT))

def start(JID, wa_pass):
	global _number, _password
	_number = str(JID)
	_password = str(wa_pass)
	init()
	login()
	IamAvailable()

def getnonce(data):
	data = data[26:]
	data = base64.b64decode(data)
	data = data.split("\"")
	data = data[1]
	return data

def getResponse(nonce):
	cnonce = str(uuid.uuid4())
	a1 = "{}:{}:{}".format(_number,_server,_password)
	a1 = hashlib.md5(a1).hexdigest()
	a1 = struct.pack('16B', *[int(c, 16) for c in (a1[i:i+2]
	for i in xrange(0, len(a1), 2))])
	a1 = a1+':'+nonce+':'+cnonce
	a2 = "AUTHENTICATE:"+_digset_uri
	password = hashlib.md5(a1).hexdigest()+':'+nonce+':'+_NC+':'+cnonce+':'+_Qop+':'+hashlib.md5(a2).hexdigest()
	password = hashlib.md5(password).hexdigest()
	response = "username=\"{}\",realm=\"{}\",nonce=\"{}\",cnonce=\"{}\",nc={},qop={},digest-uri=\"{}\",response={},charset=utf-8".format(_number,_server,nonce,cnonce,_NC,_Qop,_digset_uri,password)
	return response

def IamAvailable():
	global s
	s.send("\x05\xf8\x03\x74\xa2\x11")
	
def sendMessage(i, to, txt):
	global s
	
	t = str(time.time())
	t = t[:10]
	msgid = t+"-"+i
	
	long_txt_bool = wa_functions.isShort(txt)
	txtle = len(txt)
	txt_length = wa_functions._hex(txtle) # Returns length in hex
	txt_length = txt_length.decode("hex")
	to_length = chr(wa_functions.mb_strlen(to,"UTF-8"))
	msgid_length = chr(wa_functions.mb_strlen(msgid))
	content = "\xF8\x08\x5D\xA0\xFA\xFC"+to_length
	content += to;
	content += "\x8A\xA2\x1B\x43\xFC"+msgid_length
	content += msgid;
	content += "\xF8\x02\xF8\x04\xBA\xBD\x4F\xF8\x01\xF8\x01\x8C\xF8\x02\x16"
	if not (long_txt_bool):
		content += "\xFD\x00"+txt_length
	else:
		content += "\xFC"+txt_length
	content += txt;
	total_length = wa_functions._hex(len(content))
	total_length = total_length.decode("hex")
	if(len(str(total_length)) == 1):
		total_length = "\x00"+total_length
	msg = ""
	msg += total_length
	msg += content

	s.send(msg)
	report = s.recv(1024)
	report = s.recv(1024)

	print 'Last read ===>', repr(report)
	return msg

def sendMedia(i, to, path, size, link, b64thumb):
	global s
	
	t = str(time.time())
	t = t[:10]
	msgid = t+"-"+i
	
	thumblen = len(b64thumb)
	thumb_length = wa_functions._hex(thumblen)
	thumb_length = thumb_length.decode("hex")
	
	to_length = chr(wa_functions.mb_strlen(to,"UTF-8"))
	msgid_length = chr(wa_functions.mb_strlen(msgid))
	path_length = chr(wa_functions.mb_strlen(path))
	size_length = chr(wa_functions.mb_strlen(str(size)))
	link_length = chr(wa_functions.mb_strlen(link))
	
	content = "\xF8\x08\x5D\xA0\xFA\xFC"+to_length
	content += to
	content += "\x8A\xA2\x1B\x43\xFC"+msgid_length
	content += msgid
	content += "\xF8\x02\xF8\x04\xBA\xBD\x4F\xF8\x01\xF8\x01\x8C\xF8\x0C\x5C\xBD\xB0\xA2\x44\xFC\x04\x66\x69\x6C\x65\xFC"+path_length
	content += path
	content += "\xFC\x04\x73\x69\x7A\x65\xFC"+size_length
	content += str(size)
	content += "\xA5\xFC"+link_length
	content += link
	content += "\xFD\x00"+thumb_length
	content += b64thumb
	
	contentlen = len(content)
	total_length = wa_functions._hex(contentlen)
	total_length = total_length.decode("hex")
	msg = ""
	msg += total_length
	msg += content
	
	s.send(msg)
	report = s.recv(1024)
	report = s.recv(1024)

	print 'Last read ===>', repr(report)
	
def login():
	global s
	#Login 
	Logindata = "WA\x01\x01\x00\x19\xf8\x05\x01\xa0\x8a\x84\xfc\x11iPhone-2.6.9-5222\x00\x08\xf8\x02\x96\xf8\x01\xf8\x01\x7e\x00\x07\xf8\x05\x0f\x5a\x2a\xbd\xa7"
	s.send(Logindata)
	data = s.recv(1024)
	nonce = getnonce(data)
	response = getResponse(nonce)
	ResData = "\x01\x31\xf8\x04\x86\xbd\xa7\xfd\x00\x01\x28" + base64.b64encode(response)
	s.send(ResData)
	data1 = s.recv(1024)
	next = "\x00\x12\xf8\x05\x74\xa2\xa3\x61\xfc\x0a\x41\x68\x6d\x65\x64\x20\x4d\x6f\x68\x64\x00\x15\xf8\x06\x48\x43\x05\xa2\x3a\xf8\x01\xf8\x04\x7b\xbd\x4d\xf8\x01\xf8\x03\x55\x61\x24\x00\x12\xf8\x08\x48\x43\xfc\x01\x32\xa2\x3a\xa0\x8a\xf8\x01\xf8\x03\x1f\xbd\xb1";
	s.send(next)
	data2 = s.recv(1024)

def finish():
	global s
	s.close()
	
'''
JID = Your Mobile Number = CountryCode+MobileNumber = e.g. 97339000000
wa_pass = 32 chars password
'''
#start(JID, wa_pass)
'''
i = Incremental Message ID
to = Recipient JID = CountryCode+MobileNumber = 97339000000
txt = Well, the text, the message.
'''
#sendMessage(i, to, txt)