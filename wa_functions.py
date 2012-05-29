'''
Copyright (C) 2011 Venemous <venomous0x@gmail.com>

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

WhatsApp, WhatsApp service, clients and logo are registered trademarks of WhatsApp Inc. All other trademarks are the property of their respective owners.
Usage of WhatsApp service is subjected to WhatsApp Legal Terms.
'''
import hashlib

def isShort(str):
	length = len(str)
	if(length < 256):
		return True
	else:
		return False

def _hex(_length):
	hex = "%x" % _length
	length = len(hex)
	if(length%2 == 0):
		return  hex
	else:
		return "0"+hex

def mb_strlen( string , encoding = 'UTF-8' ):
	return len( string.encode( encoding ) )

def encryptPassword(imei):
	imei = imei[::-1]
	imei = hashlib.md5(imei).hexdigest()
	return imei