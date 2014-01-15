
/*
 * WhatsApp API implementation in C++ for libpurple.
 * Written by David Guillen Fandos (david@davidgf.net) based 
 * on the sources of WhatsAPI PHP implementation.
 *
 * Share and enjoy!
 *
 */

#include <iostream>
#include <map>
#include <vector>
#include <map>
#include <stdlib.h>
#include <stdio.h>
#include <string.h>
#include <assert.h>
#include <time.h>

#ifdef ENABLE_OPENSSL
#include <openssl/md5.h>
#include <openssl/sha.h>
#include <openssl/hmac.h>
#else
#include "wa_api.h"
#endif

std::string temp_thumbnail = "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";

class RC4Decoder;
class DataBuffer;
class Tree;
class WhatsappConnection;

#define MESSAGE_CHAT     0
#define MESSAGE_IMAGE    1
#define MESSAGE_LOCATION 2
enum SessionStatus { SessionNone = 0, SessionConnecting = 1, SessionWaitingChallenge = 2, SessionWaitingAuthOK = 3, SessionConnected = 4 };
enum ErrorCode { errorAuth, errorUnknown };

struct t_fileupload {
	std::string to, from;
	std::string file, hash;
	int rid;
	std::string type;
	std::string uploadurl, host;
	bool uploading;
	int totalsize;
};

std::string base64_decode(std::string const &encoded_string);
unsigned long long str2lng(std::string s);
std::string int2str(unsigned int num);
int str2int(std::string s);
double str2dbl(std::string s);
unsigned char lookupDecoded(std::string value);
std::string getDecoded(int n);

unsigned long long str2lng(std::string s)
{
	unsigned long long r;
	sscanf(s.c_str(), "%llu", &r);
	return r;
}

std::string int2str(unsigned int num)
{
	char temp[512];
	sprintf(temp, "%d", num);
	return std::string(temp);
}

int str2int(std::string s)
{
	int d;
	sscanf(s.c_str(), "%d", &d);
	return d;
}

double str2dbl(std::string s)
{
	double d;
	sscanf(s.c_str(), "%lf", &d);
	return d;
}

std::string getusername(std::string user)
{
	size_t pos = user.find('@');
	if (pos != std::string::npos)
		return user.substr(0, pos);
	else
		return user;
}

inline std::map < std::string, std::string > makeAttr1(std::string k1, std::string v1)
{
	std::map < std::string, std::string > at;
	at[k1] = v1;
	return at;
}

inline std::map < std::string, std::string > makeAttr2(std::string k1, std::string v1, std::string k2, std::string v2)
{
	std::map < std::string, std::string > at;
	at[k1] = v1;
	at[k2] = v2;
	return at;
}

inline std::map < std::string, std::string > makeAttr3(std::string k1, std::string v1, std::string k2, std::string v2, std::string k3, std::string v3)
{
	std::map < std::string, std::string > at;
	at[k1] = v1;
	at[k2] = v2;
	at[k3] = v3;
	return at;
}

inline std::map < std::string, std::string > makeAttr4(std::string k1, std::string v1, std::string k2, std::string v2, std::string k3, std::string v3, std::string k4, std::string v4)
{
	std::map < std::string, std::string > at;
	at[k1] = v1;
	at[k2] = v2;
	at[k3] = v3;
	at[k4] = v4;
	return at;
}

inline std::map < std::string, std::string > makeAttr5(std::string k1, std::string v1, std::string k2, std::string v2, std::string k3, std::string v3, std::string k4, std::string v4, std::string k5, std::string v5)
{
	std::map < std::string, std::string > at;
	at[k1] = v1;
	at[k2] = v2;
	at[k3] = v3;
	at[k4] = v4;
	at[k5] = v5;
	return at;
}

const char dictionary[256][40] = { "", "", "", "", "", "account", "ack", "action", "active", "add", "after",
	"ib", "all", "allow", "apple", "audio", "auth", "author", "available", "bad-protocol", "bad-request",
	"before", "Bell.caf", "body", "Boing.caf", "cancel", "category", "challenge", "chat", "clean", "code",
	"composing", "config", "conflict", "contacts", "count", "create", "creation", "default", "delay",
	"delete", "delivered", "deny", "digest", "DIGEST-MD5-1", "DIGEST-MD5-2", "dirty", "elapsed", "broadcast",
	"enable", "encoding", "duplicate", "error", "event", "expiration", "expired", "fail", "failure", "false",
	"favorites", "feature", "features", "field", "first", "free", "from", "g.us", "get", "Glass.caf", "google",
	"group", "groups", "g_notify", "g_sound", "Harp.caf", "http://etherx.jabber.org/streams",
	"http://jabber.org/protocol/chatstates", "id", "image", "img", "inactive", "index", "internal-server-error",
	"invalid-mechanism", "ip", "iq", "item", "item-not-found", "user-not-found", "jabber:iq:last", "jabber:iq:privacy",
	"jabber:x:delay", "jabber:x:event", "jid", "jid-malformed", "kind", "last", "latitude", "lc", "leave", "leave-all",
	"lg", "list", "location", "longitude", "max", "max_groups", "max_participants", "max_subject", "mechanism",
	"media", "message", "message_acks", "method", "microsoft", "missing", "modify", "mute", "name", "nokia", "none",
	"not-acceptable", "not-allowed", "not-authorized", "notification", "notify", "off", "offline", "order", "owner",
	"owning", "paid", "participant", "participants", "participating", "password", "paused", "picture", "pin", "ping",
	"platform", "pop_mean_time", "pop_plus_minus", "port", "presence", "preview", "probe", "proceed", "prop", "props",
	"p_o", "p_t", "query", "raw", "reason", "receipt", "receipt_acks", "received", "registration", "relay",
	"remote-server-timeout", "remove", "Replaced by new connection", "request", "required", "resource",
	"resource-constraint", "response", "result", "retry", "rim", "s.whatsapp.net", "s.us", "seconds", "server",
	"server-error", "service-unavailable", "set", "show", "sid", "silent", "sound", "stamp", "unsubscribe", "stat",
	"status", "stream:error", "stream:features", "subject", "subscribe", "success", "sync", "system-shutdown",
	"s_o", "s_t", "t", "text", "timeout", "TimePassing.caf", "timestamp", "to", "Tri-tone.caf", "true", "type",
	"unavailable", "uri", "url", "urn:ietf:params:xml:ns:xmpp-sasl", "urn:ietf:params:xml:ns:xmpp-stanzas",
	"urn:ietf:params:xml:ns:xmpp-streams", "urn:xmpp:delay", "urn:xmpp:ping", "urn:xmpp:receipts",
	"urn:xmpp:whatsapp", "urn:xmpp:whatsapp:account", "urn:xmpp:whatsapp:dirty", "urn:xmpp:whatsapp:mms",
	"urn:xmpp:whatsapp:push", "user", "username", "value", "vcard", "version", "video", "w", "w:g", "w:p", "w:p:r",
	"w:profile:picture", "wait", "x", "xml-not-well-formed", "xmlns", "xmlns:stream", "Xylophone.caf", "1", "WAUTH-1",
	"", "", "", "", "", "", "", "", "", "", "", "XXX", "", "", "", "", "", "", ""
};

std::string getDecoded(int n)
{
	return std::string(dictionary[n & 255]);
}

unsigned char lookupDecoded(std::string value)
{
	for (int i = 0; i < 256; i++) {
		if (strcmp(dictionary[i], value.c_str()) == 0)
			return i;
	}
	return 0;
}

const char hexmap[16] = { '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f' };
const char hexmap2[16] = { '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F' };

class KeyGenerator {
public:
	static void generateKeyImei(const char *imei, const char *salt, int saltlen, char *out)
	{
		char imeir[strlen(imei)];
		for (unsigned int i = 0; i < strlen(imei); i++)
			imeir[i] = imei[strlen(imei) - i - 1];

		char hash[16];
		MD5((unsigned char *)imeir, strlen(imei), (unsigned char *)hash);

		/* Convert to hex */
		char hashhex[32];
		for (int i = 0; i < 16; i++) {
			hashhex[2 * i] = hexmap[(hash[i] >> 4) & 0xF];
			hashhex[2 * i + 1] = hexmap[hash[i] & 0xF];
		}

		PKCS5_PBKDF2_HMAC_SHA1(hashhex, 32, (unsigned char *)salt, saltlen, 16, 20, (unsigned char *)out);
	}
	static void generateKeyV2(const std::string pw, const char *salt, int saltlen, char *out)
	{
		std::string dec = base64_decode(pw);

		PKCS5_PBKDF2_HMAC_SHA1(dec.c_str(), 20, (unsigned char *)salt, saltlen, 16, 20, (unsigned char *)out);
	}
	static void generateKeyMAC(std::string macaddr, const char *salt, int saltlen, char *out)
	{
		macaddr = macaddr + macaddr;

		char hash[16];
		MD5((unsigned char *)macaddr.c_str(), 34, (unsigned char *)hash);

		/* Convert to hex */
		char hashhex[32];
		for (int i = 0; i < 16; i++) {
			hashhex[2 * i] = hexmap[(hash[i] >> 4) & 0xF];
			hashhex[2 * i + 1] = hexmap[hash[i] & 0xF];
		}

		PKCS5_PBKDF2_HMAC_SHA1(hashhex, 32, (unsigned char *)salt, saltlen, 16, 20, (unsigned char *)out);
	}
	static void calc_hmac(const unsigned char *data, int l, const unsigned char *key, unsigned char *hmac)
	{
		unsigned char temp[20];
		HMAC_SHA1(data, l, key, 20, temp);
		memcpy(hmac, temp, 4);
	}
private:
	static void HMAC_SHA1(const unsigned char *text, int text_len, const unsigned char *key, int key_len, unsigned char *digest)
	{
		unsigned char SHA1_Key[4096], AppendBuf2[4096], szReport[4096];
		unsigned char *AppendBuf1 = new unsigned char[text_len + 64];
		unsigned char m_ipad[64], m_opad[64];

		memset(SHA1_Key, 0, 64);
		memset(m_ipad, 0x36, sizeof(m_ipad));
		memset(m_opad, 0x5c, sizeof(m_opad));

		if (key_len > 64)
			SHA1(key, key_len, SHA1_Key);
		else
			memcpy(SHA1_Key, key, key_len);

		for (unsigned int i = 0; i < sizeof(m_ipad); i++)
			m_ipad[i] ^= SHA1_Key[i];

		memcpy(AppendBuf1, m_ipad, sizeof(m_ipad));
		memcpy(AppendBuf1 + sizeof(m_ipad), text, text_len);

		SHA1(AppendBuf1, sizeof(m_ipad) + text_len, szReport);

		for (unsigned int j = 0; j < sizeof(m_opad); j++)
			m_opad[j] ^= SHA1_Key[j];

		memcpy(AppendBuf2, m_opad, sizeof(m_opad));
		memcpy(AppendBuf2 + sizeof(m_opad), szReport, 20);

		SHA1(AppendBuf2, sizeof(m_opad) + 20, digest);

		delete[]AppendBuf1;
	}
};

class RC4Decoder {
public:
	unsigned char s[256];
	unsigned char i, j;
	inline void swap(unsigned char i, unsigned char j)
	{
		unsigned char t = s[i];
		s[i] = s[j];
		s[j] = t;
	}
public:
	RC4Decoder(const unsigned char *key, int keylen, int drop)
	{
		for (unsigned int k = 0; k < 256; k++)
			s[k] = k;
		i = j = 0;
		do {
			unsigned char k = key[i % keylen];
			j = (j + k + s[i]) & 0xFF;
			swap(i, j);
		} while (++i != 0);
		i = j = 0;

		unsigned char temp[drop];
		for (int k = 0; k < drop; k++)
			temp[k] = k;
		cipher(temp, drop);
	}

	void cipher(unsigned char *data, int len)
	{
		while (len--) {
			i++;
			j += s[i];
			swap(i, j);
			unsigned char idx = s[i] + s[j];
			*data++ ^= s[idx];
		}
	}
};

class DataBuffer {
private:
	unsigned char *buffer;
	int blen;
public:
	DataBuffer(const void *ptr = 0, int size = 0)
	{
		if (ptr != NULL and size > 0) {
			buffer = (unsigned char *)malloc(size + 1);
			memcpy(buffer, ptr, size);
			blen = size;
		} else {
			blen = 0;
			buffer = (unsigned char *)malloc(1024);
		}
	}
	~DataBuffer()
	{
		free(buffer);
	}
	DataBuffer(const DataBuffer & other)
	{
		blen = other.blen;
		buffer = (unsigned char *)malloc(blen + 1024);
		memcpy(buffer, other.buffer, blen);
	}
	DataBuffer operator+(const DataBuffer & other) const
	{
		DataBuffer result = *this;

		result.addData(other.buffer, other.blen);
		return result;
	}
	DataBuffer & operator =(const DataBuffer & other)
	{
		if (this != &other) {
			free(buffer);
			this->blen = other.blen;
			buffer = (unsigned char *)malloc(blen + 1024);
			memcpy(buffer, other.buffer, blen);
		}
		return *this;
	}
	DataBuffer(const DataBuffer * d)
	{
		blen = d->blen;
		buffer = (unsigned char *)malloc(blen + 1024);
		memcpy(buffer, d->buffer, blen);
	}
	DataBuffer *decodedBuffer(RC4Decoder * decoder, int clength, bool dout)
	{
		DataBuffer *deco = new DataBuffer(this->buffer, clength);
		if (dout)
			decoder->cipher(&deco->buffer[0], clength - 4);
		else
			decoder->cipher(&deco->buffer[4], clength - 4);
		return deco;
	}
	DataBuffer encodedBuffer(RC4Decoder * decoder, unsigned char *key, bool dout)
	{
		DataBuffer deco = *this;
		decoder->cipher(&deco.buffer[0], blen);
		unsigned char hmacint[4];
		DataBuffer hmac;
		KeyGenerator::calc_hmac(deco.buffer, blen, key, (unsigned char *)&hmacint);
		hmac.addData(hmacint, 4);

		if (dout)
			deco = deco + hmac;
		else
			deco = hmac + deco;

		return deco;
	}
	void clear()
	{
		blen = 0;
		free(buffer);
		buffer = (unsigned char *)malloc(1);
	}
	void *getPtr()
	{
		return buffer;
	}
	void addData(const void *ptr, int size)
	{
		if (ptr != NULL and size > 0) {
			buffer = (unsigned char *)realloc(buffer, blen + size);
			memcpy(&buffer[blen], ptr, size);
			blen += size;
		}
	}
	void popData(int size)
	{
		if (size > blen) {
			throw 0;
		} else {
			memmove(&buffer[0], &buffer[size], blen - size);
			blen -= size;
			if (blen + size > blen * 2 and blen > 8 * 1024)
				buffer = (unsigned char *)realloc(buffer, blen + 1);
		}
	}
	void crunchData(int size)
	{
		if (size > blen) {
			throw 0;
		} else {
			blen -= size;
		}
	}
	int getInt(int nbytes, int offset = 0)
	{
		if (nbytes > blen)
			throw 0;
		int ret = 0;
		for (int i = 0; i < nbytes; i++) {
			ret <<= 8;
			ret |= buffer[i + offset];
		}
		return ret;
	}
	void putInt(int value, int nbytes)
	{
		assert(nbytes > 0);

		unsigned char out[nbytes];
		for (int i = 0; i < nbytes; i++) {
			out[nbytes - i - 1] = (value >> (i << 3)) & 0xFF;
		}
		this->addData(out, nbytes);
	}
	int readInt(int nbytes)
	{
		if (nbytes > blen)
			throw 0;
		int ret = getInt(nbytes);
		popData(nbytes);
		return ret;
	}
	int readListSize()
	{
		if (blen == 0)
			throw 0;
		int ret;
		if (buffer[0] == 0xf8 or buffer[0] == 0xf3) {
			ret = buffer[1];
			popData(2);
		} else if (buffer[0] == 0xf9) {
			ret = getInt(2, 1);
			popData(3);
		} else {
			/* FIXME throw 0 error */
			ret = -1;
			printf("Parse error!!\n");
		}
		return ret;
	}
	void writeListSize(int size)
	{
		if (size == 0) {
			putInt(0, 1);
		} else if (size < 256) {
			putInt(0xf8, 1);
			putInt(size, 1);
		} else {
			putInt(0xf9, 1);
			putInt(size, 2);
		}
	}
	std::string readRawString(int size)
	{
		if (size < 0 or size > blen)
			throw 0;
		std::string st(size, ' ');
		memcpy(&st[0], buffer, size);
		popData(size);
		return st;
	}
	std::string readString()
	{
		if (blen == 0)
			throw 0;
		int type = readInt(1);
		if (type > 4 and type < 0xf5) {
			return getDecoded(type);
		} else if (type == 0) {
			return "";
		} else if (type == 0xfc) {
			int slen = readInt(1);
			return readRawString(slen);
		} else if (type == 0xfd) {
			int slen = readInt(3);
			return readRawString(slen);
		} else if (type == 0xfe) {
			return getDecoded(readInt(1) + 0xf5);
		} else if (type == 0xfa) {
			std::string u = readString();
			std::string s = readString();

			if (u.size() > 0 and s.size() > 0)
				return u + "@" + s;
			else if (s.size() > 0)
				return s;
			return "";
		}
		return "";
	}
	void putRawString(std::string s)
	{
		if (s.size() < 256) {
			putInt(0xfc, 1);
			putInt(s.size(), 1);
			addData(s.c_str(), s.size());
		} else {
			putInt(0xfd, 1);
			putInt(s.size(), 3);
			addData(s.c_str(), s.size());
		}
	}
	void putString(std::string s)
	{
		unsigned char lu = lookupDecoded(s);
		if (lu > 4 and lu < 0xf5) {
			putInt(lu, 1);
		} else if (s.find('@') != std::string::npos) {
			std::string p1 = s.substr(0, s.find('@'));
			std::string p2 = s.substr(s.find('@') + 1);
			putInt(0xfa, 1);
			putString(p1);
			putString(p2);
		} else if (s.size() < 256) {
			putInt(0xfc, 1);
			putInt(s.size(), 1);
			addData(s.c_str(), s.size());
		} else {
			putInt(0xfd, 1);
			putInt(s.size(), 3);
			addData(s.c_str(), s.size());
		}
	}
	bool isList()
	{
		if (blen == 0)
			throw 0;
		return (buffer[0] == 248 or buffer[0] == 0 or buffer[0] == 249);
	}
	std::vector < Tree > readList(WhatsappConnection * c);
	int size()
	{
		return blen;
	}
	std::string toString()
	{
		std::string r(blen, ' ');
		memcpy(&r[0], buffer, blen);
		return r;
	}
};

class Tree {
private:
	std::map < std::string, std::string > attributes;
	std::vector < Tree > children;
	std::string tag, data;
	bool forcedata;
public:
	Tree(std::string tag = "")
	{
		this->tag = tag;
		forcedata = false;
	}
	Tree(std::string tag, std::map < std::string, std::string > attributes)
	{
		this->tag = tag;
		this->attributes = attributes;
		forcedata = false;
	}
	~Tree()
	{
	}
	void forceDataWrite()
	{
		forcedata = true;
	}
	bool forcedData() const
	{
		return forcedata;
	}
	void addChild(Tree t)
	{
		children.push_back(t);
	}
	void setTag(std::string tag)
	{
		this->tag = tag;
	}
	void setAttributes(std::map < std::string, std::string > attributes)
	{
		this->attributes = attributes;
	}
	void readAttributes(DataBuffer * data, int size)
	{
		int count = (size - 2 + (size % 2)) / 2;
		while (count--) {
			std::string key = data->readString();
			std::string value = data->readString();
			attributes[key] = value;
		}
	}
	void writeAttributes(DataBuffer * data)
	{
		for (std::map < std::string, std::string >::iterator iter = attributes.begin(); iter != attributes.end(); iter++) {
			data->putString(iter->first);
			data->putString(iter->second);
		}
	}
	void setData(const std::string d)
	{
		data = d;
	}
	std::string getData()
	{
		return data;
	}
	std::string getTag()
	{
		return tag;
	}
	void setChildren(std::vector < Tree > c)
	{
		children = c;
	}
	std::vector < Tree > getChildren()
	{
		return children;
	}
	std::map < std::string, std::string > &getAttributes()
	{
		return attributes;
	}
	bool hasAttributeValue(std::string at, std::string val)
	{
		if (hasAttribute(at)) {
			return (attributes[at] == val);
		}
		return false;
	}
	bool hasAttribute(std::string at)
	{
		return (attributes.find(at) != attributes.end());
	}
	std::string getAttribute(std::string at)
	{
		if (hasAttribute(at))
			return (attributes[at]);
		return "";
	}

	Tree getChild(std::string tag)
	{
		for (unsigned int i = 0; i < children.size(); i++) {
			if (children[i].getTag() == tag)
				return children[i];
			Tree t = children[i].getChild(tag);
			if (t.getTag() != "treeerr")
				return t;
		}
		return Tree("treeerr");
	}
	bool hasChild(std::string tag)
	{
		for (unsigned int i = 0; i < children.size(); i++) {
			if (children[i].getTag() == tag)
				return true;
			if (children[i].hasChild(tag))
				return true;
		}
		return false;
	}

	std::string toString(int sp = 0)
	{
		std::string ret;
		std::string spacing(' ', sp);
		ret += spacing + "Tag: " + tag + "\n";
		for (std::map < std::string, std::string >::iterator iter = attributes.begin(); iter != attributes.end(); iter++) {
			ret += spacing + "at[" + iter->first + "]=" + iter->second + "\n";
		}
		ret += spacing + "Data: " + data + "\n";

		for (unsigned int i = 0; i < children.size(); i++) {
			ret += children[i].toString(sp + 1);
		}
		return ret;
	}
};

class Group {
public:
	Group(std::string id, std::string subject, std::string owner)
	{
		this->id = id;
		this->subject = subject;
		this->owner = owner;
	}
	~Group() {
	}
	std::string id, subject, owner;
	std::vector < std::string > participants;
};

class Contact {
public:
	Contact()
	{
	}
	Contact(std::string phone, bool myc)
	{
		this->phone = phone;
		this->mycontact = myc;
		this->last_seen = 0;
		this->subscribed = false;
		this->typing = "paused";
		this->status = "";
	}

	std::string phone, name;
	std::string presence, typing;
	std::string status;
	unsigned long long last_seen, last_status;
	bool mycontact;
	std::string ppprev, pppicture;
	bool subscribed;
};

class Message {
public:
	Message(const WhatsappConnection * wc, const std::string from, const unsigned long long time, const std::string id, const std::string author)
	{
		size_t pos = from.find('@');
		if (pos != std::string::npos) {
			this->from = from.substr(0, pos);
			this->server = from.substr(pos + 1);
		} else
			this->from = from;
		this->t = time;
		this->wc = const_cast < WhatsappConnection * >(wc);
		this->id = id;
		this->author = getusername(author);
	}
	virtual ~ Message()
	{
	}
	std::string from, server, author;
	unsigned long long t;
	std::string id;
	WhatsappConnection *wc;

	virtual int type() const = 0;

	virtual Message *copy() const = 0;
};

class WhatsappConnection {
	friend class ChatMessage;
	friend class ImageMessage;
	friend class Message;
private:
	/* Current dissection classes */
	RC4Decoder * in, *out;
	unsigned char session_key[20];
	DataBuffer inbuffer, outbuffer;
	DataBuffer sslbuffer, sslbuffer_in;
	std::string challenge_data, challenge_response;
	std::string phone, password;
	SessionStatus conn_status;

	/* State stuff */
	unsigned int msgcounter, iqid;
	std::string nickname;
	std::string whatsappserver, whatsappservergroup;
	std::string mypresence, mymessage;

	/* Various account info */
	std::string account_type, account_status, account_expiration, account_creation;

	/* Groups stuff */
	std::map < std::string, Group > groups;
	int gq_stat;
	int gw1, gw2, gw3;
	bool groups_updated;

	/* Contacts & msg */
	std::map < std::string, Contact > contacts;
	std::vector < Message * >recv_messages, recv_messages_delay;
	std::vector < std::string > user_changes, user_icons, user_typing;

	void processIncomingData();
	void processSSLIncomingData();
	DataBuffer serialize_tree(Tree * tree, bool crypt = true);
	DataBuffer write_tree(Tree * tree);
	Tree parse_tree(DataBuffer * data);

	/* Upload */
	std::vector < t_fileupload > uploadfile_queue;

	/* HTTP interface */
	std::string generateHttpAuth(std::string nonce);

	/* SSL / HTTPS interface */
	std::string sslnonce;
	int sslstatus;		/* 0 none, 1/2 requesting A, 3/4 requesting Q */
	/* 5/6 for image upload */

	void receiveMessage(const Message & m);
	void notifyPresence(std::string from, std::string presence);
	void notifyLastSeen(std::string from, std::string seconds);
	void addPreviewPicture(std::string from, std::string picture);
	void addFullsizePicture(std::string from, std::string picture);
	void sendResponse();
	void doPong(std::string id, std::string from);
	void subscribePresence(std::string user);
	void getLast(std::string user);
	void queryPreview(std::string user);
	void queryFullSize(std::string user);
	void gotTyping(std::string who, std::string tstat);
	void updateGroups();

	void notifyMyMessage();
	void notifyMyPresence();
	void sendInitial();
	void notifyError(ErrorCode err);
	DataBuffer generateResponse(std::string from, std::string type, std::string id, std::string answer);
	std::string generateUploadPOST(t_fileupload * fu);
	void processUploadQueue();

	void generateSyncARequest();
	void generateSyncQRequest();
	void updateContactStatuses(std::string json);
	void updateFileUpload(std::string);

public:
	Tree read_tree(DataBuffer * data);

	WhatsappConnection(std::string phone, std::string password, std::string nick);
	~WhatsappConnection();

	void doLogin(std::string);
	void receiveCallback(const char *data, int len);
	int sendCallback(char *data, int len);
	void sentCallback(int len);
	bool hasDataToSend();

	void addContacts(std::vector < std::string > clist);
	void sendChat(std::string to, std::string message);
	void sendGroupChat(std::string to, std::string message);
	bool query_chat(std::string & from, std::string & message, std::string & author, unsigned long &t);
	bool query_chatimages(std::string & from, std::string & preview, std::string & url, std::string & author, unsigned long &t);
	bool query_chatsounds(std::string & from, std::string & url, std::string & author, unsigned long &t);
	bool query_chatlocations(std::string & from, double &lat, double &lng, std::string & prev, std::string & author, unsigned long &t);
	int query_next();
	bool query_status(std::string & from, int &status);
	bool query_icon(std::string & from, std::string & icon, std::string & hash);
	bool query_avatar(std::string user, std::string & icon);
	bool query_typing(std::string & from, int &status);
	void send_avatar(const std::string & avatar);
	void account_info(unsigned long long &creation, unsigned long long &freeexp, std::string & status);
	int getuserstatus(const std::string & who);
	std::string getuserstatusstring(const std::string & who);
	unsigned long long getlastseen(const std::string & who);

	void manageParticipant(std::string group, std::string participant, std::string command);
	void leaveGroup(std::string group);

	void notifyTyping(std::string who, int status);
	void setMyPresence(std::string s, std::string msg);
	std::map < std::string, Group > getGroups();
	bool groupsUpdated();
	void addGroup(std::string subject);

	int loginStatus() const
	{
		return ((int)conn_status) - 1;
	}
	int sendImage(std::string to, int w, int h, unsigned int size, const char *fp);

	int sendSSLCallback(char *buffer, int maxbytes);
	int sentSSLCallback(int bytessent);
	void receiveSSLCallback(char *buffer, int bytesrecv);
	bool hasSSLDataToSend();
	bool closeSSLConnection();
	void SSLCloseCallback();
	bool hasSSLConnection(std::string & host, int *port);
	int uploadProgress(int &rid, int &bs);

	std::string generateHeaders(std::string auth, int content_length);
};

class ChatMessage:public Message {
public:
	ChatMessage(const WhatsappConnection * wc, const std::string from, const unsigned long long time, const std::string id, const std::string message, const std::string author):Message(wc, from, time, id, author)
	{
		this->message = message;
	}
	int type() const
	{
		return 0;
	}
	std::string message;	/* Message */

	DataBuffer serialize() const
	{
		Tree request("request", makeAttr1("xmlns", "urn:xmpp:receipts"));
		Tree notify("notify", makeAttr2("xmlns", "urn:xmpp:whatsapp", "name", author));
		Tree xhash("x", makeAttr1("xmlns", "jabber:x:event"));
		xhash.addChild(Tree("server"));
		Tree tbody("body");
		tbody.setData(this->message);

		std::string stime = int2str(t);
		std::map < std::string, std::string > attrs;
		if (server.size())
			attrs["to"] = from + "@" + server;
		else
			attrs["to"] = from + "@" + wc->whatsappserver;
		attrs["type"] = "chat";
		attrs["id"] = stime + "-" + id;
		attrs["t"] = stime;

		Tree mes("message", attrs);
		mes.addChild(xhash);
		mes.addChild(notify);
		mes.addChild(request);
		mes.addChild(tbody);

		return wc->serialize_tree(&mes);
	}
	Message *copy() const
	{
		return new ChatMessage(wc, from, t, id, message, author);
	}
};

class ImageMessage:public Message {
public:
	ImageMessage(const WhatsappConnection * wc, const std::string from, const unsigned long long time, const std::string id, const std::string author, const std::string url, const unsigned int width, const unsigned int height, const unsigned int size, const std::string encoding, const std::string hash, const std::string filetype, const std::string preview):Message(wc, from, time, id, author)
	{

		this->url = url;
		this->width = width;
		this->height = height;
		this->size = size;
		this->encoding = encoding;
		this->hash = hash;
		this->filetype = filetype;
		this->preview = preview;
	}
	int type() const
	{
		return 1;
	}
	DataBuffer serialize() const
	{
		Tree request("request", makeAttr1("xmlns", "urn:xmpp:receipts"));
		Tree notify("notify", makeAttr2("xmlns", "urn:xmpp:whatsapp", "name", author));
		Tree xhash("x", makeAttr1("xmlns", "jabber:x:event"));
		xhash.addChild(Tree("server"));

		Tree tmedia("media", makeAttr5("xmlns", "urn:xmpp:whatsapp:mms", "type", "image", "url", url, "size", int2str(size), "file", "myfile.jpg"));

		tmedia.setData(preview);	/* ICON DATA! */

		std::string stime = int2str(t);
		std::map < std::string, std::string > attrs;
		if (server.size())
			attrs["to"] = from + "@" + server;
		else
			attrs["to"] = from + "@" + wc->whatsappserver;
		attrs["type"] = "chat";
		attrs["id"] = stime + "-" + id;
		attrs["t"] = stime;

		Tree mes("message", attrs);
		mes.addChild(xhash);
		mes.addChild(notify);
		mes.addChild(request);
		mes.addChild(tmedia);

		return wc->serialize_tree(&mes);
	}
	Message *copy() const
	{
		return new ImageMessage(wc, from, t, id, author, url, width, height, size, encoding, hash, filetype, preview);
	}
	std::string url;	/* Image URL */
	std::string encoding, hash, filetype;
	std::string preview;
	unsigned int width, height, size;
};

class SoundMessage:public Message {
public:
	SoundMessage(const WhatsappConnection * wc, const std::string from, const unsigned long long time, const std::string id, const std::string author, const std::string url, const std::string hash, const std::string filetype):Message(wc, from, time, id, author)
	{
		this->url = url;
		this->filetype = filetype;
	}
	int type() const
	{
		return 3;
	}
	Message *copy() const
	{
		return new SoundMessage(wc, from, t, id, author, url, hash, filetype);
	}
	std::string url;	/* Sound URL */
	std::string hash, filetype;
};

class LocationMessage:public Message {
public:
	LocationMessage(const WhatsappConnection * wc, const std::string from, const unsigned long long time, const std::string id, const std::string author, double lat, double lng, std::string preview):Message(wc, from, time, id, author)
	{
		this->latitude = lat;
		this->longitude = lng;
		this->preview = preview;
	}
	int type() const
	{
		return 2;
	}
	Message *copy() const
	{
		return new LocationMessage(wc, from, t, id, author, latitude, longitude, preview);
	}
	double latitude, longitude;	/* Location */
	std::string preview;
};

DataBuffer WhatsappConnection::generateResponse(std::string from, std::string type, std::string id, std::string answer)
{
	Tree received(answer, makeAttr1("xmlns", "urn:xmpp:receipts"));

	/*std::string stime = int2str(t); */
	std::map < std::string, std::string > attrs;
	attrs["to"] = from;
	attrs["type"] = type;
	attrs["id"] = id;
	attrs["t"] = int2str(time(NULL));

	Tree mes("message", attrs);
	mes.addChild(received);

	return serialize_tree(&mes);
}

std::vector < Tree > DataBuffer::readList(WhatsappConnection * c)
{
	std::vector < Tree > l;
	int size = readListSize();
	while (size--) {
		l.push_back(c->read_tree(this));
	}
	return l;
}

/* Send image transaction */
int WhatsappConnection::sendImage(std::string to, int w, int h, unsigned int size, const char *fp)
{
	/* Type can be: audio/image/video */
	std::string sha256b64hash = SHA256_file_b64(fp);
	Tree iq("media", makeAttr4("xmlns", "w:m", "type", "image", "hash", sha256b64hash, "size", int2str(size)));
	Tree req("iq", makeAttr3("id", int2str(++iqid), "type", "set", "to", whatsappserver));
	req.addChild(iq);

	t_fileupload fu;
	fu.to = to;
	fu.file = std::string(fp);
	fu.rid = iqid;
	fu.hash = sha256b64hash;
	fu.type = "image";
	fu.uploading = false;
	fu.totalsize = 0;
	uploadfile_queue.push_back(fu);
	outbuffer = outbuffer + serialize_tree(&req);

	return iqid;
}

void WhatsappConnection::generateSyncARequest()
{
	sslbuffer.clear();

	std::string httpr = "POST /v2/sync/a HTTP/1.1\r\n" + generateHeaders(generateHttpAuth("0"), 0) + "\r\n";

	sslbuffer.addData(httpr.c_str(), httpr.size());
}

void WhatsappConnection::generateSyncQRequest()
{
	sslbuffer.clear();

	/* Query numbers with and without "+" */
	/* Seems that american numbers do not like the + symbol */
	std::string body = "ut=all&t=c";
	for (std::map < std::string, Contact >::iterator iter = contacts.begin(); iter != contacts.end(); iter++) {
		body += ("&u[]=" + iter->first);
		body += ("&u[]=%2B" + iter->first);
	}
	std::string httpr = "POST /v2/sync/q HTTP/1.1\r\n" + generateHeaders(generateHttpAuth(sslnonce), body.size()) + "\r\n";
	httpr += body;

	sslbuffer.addData(httpr.c_str(), httpr.size());
}

WhatsappConnection::WhatsappConnection(std::string phone, std::string password, std::string nickname)
{
	this->phone = phone;
	this->password = password;
	this->in = NULL;
	this->out = NULL;
	this->conn_status = SessionNone;
	this->msgcounter = 1;
	this->iqid = 1;
	this->nickname = nickname;
	this->whatsappserver = "s.whatsapp.net";
	this->whatsappservergroup = "g.us";
	this->mypresence = "available";
	this->groups_updated = false;
	this->gq_stat = 0;
	this->gw1 = -1;
	this->gw2 = -1;
	this->gw3 = 0;
	this->sslstatus = 0;

	/* Trim password spaces */
	while (password.size() > 0 and password[0] == ' ')
		password = password.substr(1);
	while (password.size() > 0 and password[password.size() - 1] == ' ')
		password = password.substr(0, password.size() - 1);
}

WhatsappConnection::~WhatsappConnection()
{
	if (this->in)
		delete this->in;
	if (this->out)
		delete this->out;
	for (unsigned int i = 0; i < recv_messages.size(); i++) {
		delete recv_messages[i];
	}
}

std::map < std::string, Group > WhatsappConnection::getGroups()
{
	return groups;
}

bool WhatsappConnection::groupsUpdated()
{
	if (gq_stat == 7) {
		groups_updated = true;
		gq_stat = 8;
	}

	if (groups_updated and gw3 <= 0) {
		groups_updated = false;
		return true;
	}

	return false;
}

void WhatsappConnection::updateGroups()
{
	/* Get the group list */
	groups.clear();
	{
		gw1 = iqid;
		Tree iq("list", makeAttr2("xmlns", "w:g", "type", "owning"));
		Tree req("iq", makeAttr3("id", int2str(iqid++), "type", "get", "to", "g.us"));
		req.addChild(iq);
		outbuffer = outbuffer + serialize_tree(&req);
	}
	{
		gw2 = iqid;
		Tree iq("list", makeAttr2("xmlns", "w:g", "type", "participating"));
		Tree req("iq", makeAttr3("id", int2str(iqid++), "type", "get", "to", "g.us"));
		req.addChild(iq);
		outbuffer = outbuffer + serialize_tree(&req);
	}
	gq_stat = 1;		/* Queried the groups */
	gw3 = 0;
}

void WhatsappConnection::manageParticipant(std::string group, std::string participant, std::string command)
{
	Tree part("participant", makeAttr1("jid", participant));
	Tree iq(command, makeAttr1("xmlns", "w:g"));
	iq.addChild(part);
	Tree req("iq", makeAttr3("id", int2str(iqid++), "type", "set", "to", group + "@g.us"));
	req.addChild(iq);

	outbuffer = outbuffer + serialize_tree(&req);
}

void WhatsappConnection::leaveGroup(std::string group)
{
	Tree gr("group", makeAttr1("id", group + "@g.us"));
	Tree iq("leave", makeAttr1("xmlns", "w:g"));
	iq.addChild(gr);
	Tree req("iq", makeAttr3("id", int2str(iqid++), "type", "set", "to", "g.us"));
	req.addChild(iq);

	outbuffer = outbuffer + serialize_tree(&req);
}

void WhatsappConnection::addGroup(std::string subject)
{
	Tree gr("group", makeAttr3("xmlns", "w:g", "action", "create", "subject", subject));
	Tree req("iq", makeAttr3("id", int2str(iqid++), "type", "set", "to", "g.us"));
	req.addChild(gr);

	outbuffer = outbuffer + serialize_tree(&req);
}

void WhatsappConnection::doLogin(std::string resource)
{
	/* Send stream init */
	DataBuffer first;

	{
		std::map < std::string, std::string > auth;
		first.addData("WA\1\2", 4);
		auth["resource"] = resource;
		auth["to"] = whatsappserver;
		Tree t("start", auth);
		first = first + serialize_tree(&t, false);
	}

	/* Send features */
	{
		Tree p;
		p.setTag("stream:features");
		p.addChild(Tree("receipt_acks"));
		p.addChild(Tree("w:profile:picture", makeAttr1("type", "all")));
		p.addChild(Tree("w:profile:picture", makeAttr1("type", "group")));
		p.addChild(Tree("notification", makeAttr1("type", "participant")));
		p.addChild(Tree("status"));
		first = first + serialize_tree(&p, false);
	}

	/* Send auth request */
	{
		std::map < std::string, std::string > auth;
		auth["xmlns"] = "urn:ietf:params:xml:ns:xmpp-sasl";
		auth["mechanism"] = "WAUTH-1";
		auth["user"] = phone;
		Tree t("auth", auth);
		t.forceDataWrite();
		first = first + serialize_tree(&t, false);
	}

	conn_status = SessionWaitingChallenge;
	outbuffer = first;
}

void WhatsappConnection::receiveCallback(const char *data, int len)
{
	if (data != NULL and len > 0)
		inbuffer.addData(data, len);
	this->processIncomingData();
}

int WhatsappConnection::sendCallback(char *data, int len)
{
	int minlen = outbuffer.size();
	if (minlen > len)
		minlen = len;

	memcpy(data, outbuffer.getPtr(), minlen);
	return minlen;
}

bool WhatsappConnection::hasDataToSend()
{
	return outbuffer.size() != 0;
}

void WhatsappConnection::sentCallback(int len)
{
	outbuffer.popData(len);
}

int WhatsappConnection::sendSSLCallback(char *buffer, int maxbytes)
{
	int minlen = sslbuffer.size();
	if (minlen > maxbytes)
		minlen = maxbytes;

	memcpy(buffer, sslbuffer.getPtr(), minlen);
	return minlen;
}

int WhatsappConnection::sentSSLCallback(int bytessent)
{
	sslbuffer.popData(bytessent);
	return bytessent;
}

void WhatsappConnection::receiveSSLCallback(char *buffer, int bytesrecv)
{
	if (buffer != NULL and bytesrecv > 0)
		sslbuffer_in.addData(buffer, bytesrecv);
	this->processSSLIncomingData();
}

bool WhatsappConnection::hasSSLDataToSend()
{
	return sslbuffer.size() != 0;
}

bool WhatsappConnection::closeSSLConnection()
{
	return sslstatus == 0;
}

void WhatsappConnection::SSLCloseCallback()
{
	sslstatus = 0;
}

bool WhatsappConnection::hasSSLConnection(std::string & host, int *port)
{
	host = "sro.whatsapp.net";
	*port = 443;

	if (sslstatus == 5)
		for (unsigned int j = 0; j < uploadfile_queue.size(); j++)
			if (uploadfile_queue[j].uploading) {
				host = uploadfile_queue[j].host;
				break;
			}

	return (sslstatus == 1 or sslstatus == 3 or sslstatus == 5);
}

int WhatsappConnection::uploadProgress(int &rid, int &bs)
{
	if (!(sslstatus == 5 or sslstatus == 6))
		return 0;
	int totalsize = 0;
	for (unsigned int j = 0; j < uploadfile_queue.size(); j++)
		if (uploadfile_queue[j].uploading) {
			rid = uploadfile_queue[j].rid;
			totalsize = uploadfile_queue[j].totalsize;
			break;
		}
	bs = totalsize - sslbuffer.size();
	if (bs < 0)
		bs = 0;
	return 1;
}

void WhatsappConnection::subscribePresence(std::string user)
{
	Tree request("presence", makeAttr2("type", "subscribe", "to", user));
	outbuffer = outbuffer + serialize_tree(&request);
}

void WhatsappConnection::getLast(std::string user)
{
	Tree iq("query", makeAttr1("xmlns", "jabber:iq:last"));
	Tree req("iq", makeAttr3("id", int2str(iqid++), "type", "get", "to", user));
	req.addChild(iq);

	outbuffer = outbuffer + serialize_tree(&req);
}

void WhatsappConnection::gotTyping(std::string who, std::string tstat)
{
	who = getusername(who);
	if (contacts.find(who) != contacts.end()) {
		contacts[who].typing = tstat;
		user_typing.push_back(who);
	}
}

void WhatsappConnection::notifyTyping(std::string who, int status)
{
	std::string s = "paused";
	if (status == 1)
		s = "composing";

	Tree mes("message", makeAttr2("to", who + "@" + whatsappserver, "type", "chat"));
	mes.addChild(Tree(s, makeAttr1("xmlns", "http://jabber.org/protocol/chatstates")));

	outbuffer = outbuffer + serialize_tree(&mes);
}

void WhatsappConnection::account_info(unsigned long long &creation, unsigned long long &freeexp, std::string & status)
{
	creation = str2lng(account_creation);
	freeexp = str2lng(account_expiration);
	status = account_status;
}

void WhatsappConnection::queryPreview(std::string user)
{
	Tree pic("picture", makeAttr2("xmlns", "w:profile:picture", "type", "preview"));
	Tree req("iq", makeAttr3("id", int2str(iqid++), "type", "get", "to", user));
	req.addChild(pic);

	outbuffer = outbuffer + serialize_tree(&req);
}

void WhatsappConnection::queryFullSize(std::string user)
{
	Tree pic("picture", makeAttr2("xmlns", "w:profile:picture", "type", "image"));
	Tree req("iq", makeAttr3("id", int2str(iqid++), "type", "get", "to", user));
	req.addChild(pic);

	outbuffer = outbuffer + serialize_tree(&req);
}

void WhatsappConnection::send_avatar(const std::string & avatar)
{
	Tree pic("picture", makeAttr2("type", "image", "xmlns", "w:profile:picture"));
	Tree prev("picture", makeAttr1("type", "preview"));
	pic.setData(avatar);
	prev.setData(avatar);
	Tree req("iq", makeAttr3("id", int2str(iqid++), "type", "set", "to", phone + "@" + whatsappserver));
	req.addChild(pic);
	req.addChild(prev);

	outbuffer = outbuffer + serialize_tree(&req);
}

void WhatsappConnection::sendChat(std::string to, std::string message)
{
	ChatMessage msg(this, to, time(NULL), int2str(msgcounter++), message, nickname);
	DataBuffer buf = msg.serialize();

	outbuffer = outbuffer + buf;
}

void WhatsappConnection::sendGroupChat(std::string to, std::string message)
{
	ChatMessage msg(this, to, time(NULL), int2str(msgcounter++), message, nickname);
	msg.server = "g.us";
	DataBuffer buf = msg.serialize();

	outbuffer = outbuffer + buf;
}

void WhatsappConnection::addContacts(std::vector < std::string > clist)
{
	/* Insert the contacts to the contact list */
	for (unsigned int i = 0; i < clist.size(); i++) {
		if (contacts.find(clist[i]) == contacts.end())
			contacts[clist[i]] = Contact(clist[i], true);
		else
			contacts[clist[i]].mycontact = true;

		user_changes.push_back(clist[i]);
	}
	/* Query the profile pictures */
	for (std::map < std::string, Contact >::iterator iter = contacts.begin(); iter != contacts.end(); iter++) {
		if (not iter->second.subscribed) {
			iter->second.subscribed = true;

			this->subscribePresence(iter->first + "@" + whatsappserver);
			this->queryPreview(iter->first + "@" + whatsappserver);
			this->getLast(iter->first + "@" + whatsappserver);
		}
	}
	/* Query statuses */
	if (sslstatus == 0) {
		sslbuffer_in.clear();
		sslstatus = 1;
		generateSyncARequest();
	}
}

unsigned char hexchars(char c1, char c2)
{
	if (c1 >= '0' and c1 <= '9')
		c1 -= '0';
	else if (c1 >= 'A' and c1 <= 'F')
		c1 = c1 - 'A' + 10;
	else if (c1 >= 'a' and c1 <= 'f')
		c1 = c1 - 'a' + 10;

	if (c2 >= '0' and c2 <= '9')
		c2 -= '0';
	else if (c2 >= 'A' and c2 <= 'F')
		c2 = c2 - 'A' + 10;
	else if (c2 >= 'a' and c2 <= 'f')
		c2 = c2 - 'a' + 10;

	unsigned char r = c2 | (c1 << 4);
	return r;
}

std::string UnicodeToUTF8(unsigned int c)
{
	std::string ret;
	if (c <= 0x7F)
		ret += ((char)c);
	else if (c <= 0x7FF) {
		ret += ((char)(0xC0 | (c >> 6)));
		ret += ((char)(0x80 | (c & 0x3F)));
	} else if (c <= 0xFFFF) {
		if (c >= 0xD800 and c <= 0xDFFF)
			return ret;	/* Invalid char */
		ret += ((char)(0xE0 | (c >> 12)));
		ret += ((char)(0x80 | ((c >> 6) & 0x3F)));
		ret += ((char)(0x80 | (c & 0x3F)));
	}
	return ret;
}

std::string utf8_decode(std::string in)
{
	std::string dec;
	for (unsigned int i = 0; i < in.size(); i++) {
		if (in[i] == '\\' and in[i + 1] == 'u') {
			i += 2;	/* Skip \u */
			unsigned char hex1 = hexchars(in[i + 0], in[i + 1]);
			unsigned char hex2 = hexchars(in[i + 2], in[i + 3]);
			unsigned int uchar = (hex1 << 8) | hex2;
			dec += UnicodeToUTF8(uchar);
			i += 3;
		} else if (in[i] == '\\' and in[i + 1] == '"') {
			dec += '"';
			i++;
		} else
			dec += in[i];
	}
	return dec;
}

std::string query_field(std::string work, std::string lo, bool integer = false)
{
	size_t p = work.find("\"" + lo + "\"");
	if (p == std::string::npos)
		return "";

	work = work.substr(p + ("\"" + lo + "\"").size());

	p = work.find("\"");
	if (integer)
		p = work.find(":");
	if (p == std::string::npos)
		return "";

	work = work.substr(p + 1);

	p = 0;
	while (p < work.size()) {
		if (work[p] == '"' and(p == 0 or work[p - 1] != '\\'))
			break;
		p++;
	}
	if (integer) {
		p = 0;
		while (p < work.size()and work[p] >= '0' and work[p] <= '9')
			p++;
	}
	if (p == std::string::npos)
		return "";

	work = work.substr(0, p);

	return work;
}

void WhatsappConnection::updateContactStatuses(std::string json)
{
	while (true) {
		size_t offset = json.find("{");
		if (offset == std::string::npos)
			break;
		json = json.substr(offset + 1);

		/* Look for closure */
		size_t cl = json.find("{");
		if (cl == std::string::npos)
			cl = json.size();
		std::string work = json.substr(0, cl);

		/* Look for "n", the number and "w","t","s" */
		std::string n = query_field(work, "n");
		std::string w = query_field(work, "w", true);
		std::string t = query_field(work, "t", true);
		std::string s = query_field(work, "s");

		if (w == "1") {
			contacts[n].status = utf8_decode(s);
			contacts[n].last_status = str2lng(t);
		}

		json = json.substr(cl);
	}
}

void WhatsappConnection::updateFileUpload(std::string json)
{
	size_t offset = json.find("{");
	if (offset == std::string::npos)
		return;
	json = json.substr(offset + 1);

	/* Look for closure */
	size_t cl = json.find("{");
	if (cl == std::string::npos)
		cl = json.size();
	std::string work = json.substr(0, cl);

	std::string url = query_field(work, "url");
	std::string type = query_field(work, "type");
	std::string size = query_field(work, "size");
	std::string width = query_field(work, "width");
	std::string height = query_field(work, "height");
	std::string filehash = query_field(work, "filehash");
	std::string mimetype = query_field(work, "mimetype");

	std::string to;
	for (unsigned int j = 0; j < uploadfile_queue.size(); j++)
		if (uploadfile_queue[j].uploading and uploadfile_queue[j].hash == filehash) {
			to = uploadfile_queue[j].to;
			uploadfile_queue.erase(uploadfile_queue.begin() + j);
			break;
		}
	/* Send the message with the URL :) */
	ImageMessage msg(this, to, time(NULL), int2str(msgcounter++), "author", url, str2int(width), str2int(height), str2int(size), "encoding", filehash, mimetype, temp_thumbnail);

	DataBuffer buf = msg.serialize();

	outbuffer = outbuffer + buf;
}

/* Quick and dirty way to parse the HTTP responses */
void WhatsappConnection::processSSLIncomingData()
{
	/* Parse HTTPS headers and JSON body */
	if (sslstatus == 1 or sslstatus == 3 or sslstatus == 5)
		sslstatus++;

	if (sslstatus == 2) {
		std::string toparse((char *)sslbuffer_in.getPtr(), sslbuffer_in.size());
		if (toparse.find("nonce=\"") != std::string::npos) {
			toparse = toparse.substr(toparse.find("nonce=\"") + 7);
			if (toparse.find("\"") != std::string::npos) {
				toparse = toparse.substr(0, toparse.find("\""));
				sslnonce = toparse;
				sslstatus = 4;

				sslbuffer.clear();
				sslbuffer_in.clear();
				generateSyncQRequest();
			}
		}
	}
	if (sslstatus == 4 or sslstatus == 6) {
		/* Look for the first line, to be 200 OK */
		std::string toparse((char *)sslbuffer_in.getPtr(), sslbuffer_in.size());
		if (toparse.find("\r\n") != std::string::npos) {
			std::string fl = toparse.substr(0, toparse.find("\r\n"));
			if (fl.find("200") == std::string::npos)
				goto abortStatus;

			if (toparse.find("\r\n\r\n") != std::string::npos) {
				std::string headers = toparse.substr(0, toparse.find("\r\n\r\n") + 4);
				std::string content = toparse.substr(toparse.find("\r\n\r\n") + 4);

				/* Look for content length */
				if (headers.find("Content-Length:") != std::string::npos) {
					std::string clen = headers.substr(headers.find("Content-Length:") + strlen("Content-Length:"));
					clen = clen.substr(0, clen.find("\r\n"));
					while (clen.size() > 0 and clen[0] == ' ')
						clen = clen.substr(1);
					unsigned int contentlength = str2int(clen);
					if (contentlength == content.size()) {
						/* Now we can proceed to parse the JSON */
						if (sslstatus == 4)
							updateContactStatuses(content);
						else
							updateFileUpload(content);
						sslstatus = 0;
					}
				}
			}
		}
	}

	processUploadQueue();
	return;
abortStatus:
	sslstatus = 0;
	processUploadQueue();
	return;
}

std::string WhatsappConnection::generateUploadPOST(t_fileupload * fu)
{
	std::string file_buffer;
	FILE *fd = fopen(fu->file.c_str(), "rb");
	int read = 0;
	do {
		char buf[1024];
		read = fread(buf, 1, 1024, fd);
		file_buffer += std::string(buf, read);
	} while (read > 0);
	fclose(fd);

	std::string mime_type = std::string(file_mime_type(fu->file.c_str(), file_buffer.c_str(), file_buffer.size()));
	std::string encoded_name = "TODO..:";

	std::string ret;
	/* BODY HEAD */
	ret += "--zzXXzzYYzzXXzzQQ\r\n";
	ret += "Content-Disposition: form-data; name=\"to\"\r\n\r\n";
	ret += fu->to + "\r\n";
	ret += "--zzXXzzYYzzXXzzQQ\r\n";
	ret += "Content-Disposition: form-data; name=\"from\"\r\n\r\n";
	ret += fu->from + "\r\n";
	ret += "--zzXXzzYYzzXXzzQQ\r\n";
	ret += "Content-Disposition: form-data; name=\"file\"; filename=\"" + encoded_name + "\"\r\n";
	ret += "Content-Type: " + mime_type + "\r\n\r\n";

	/* File itself */
	ret += file_buffer;

	/* TAIL */
	ret += "\r\n--zzXXzzYYzzXXzzQQ--\r\n";

	std::string post;
	post += "POST " + fu->uploadurl + "\r\n";
	post += "Content-Type: multipart/form-data; boundary=zzXXzzYYzzXXzzQQ\r\n";
	post += "Host: " + fu->host + "\r\n";
	post += "User-Agent: WhatsApp/2.4.7 S40Version/14.26 Device/Nokia302\r\n";
	post += "Content-Length:  " + int2str(ret.size()) + "\r\n\r\n";

	std::string all = post + ret;

	fu->totalsize = file_buffer.size();

	return all;
}

void WhatsappConnection::processUploadQueue()
{
	/* At idle check for new uploads */
	if (sslstatus == 0) {
		for (unsigned int j = 0; j < uploadfile_queue.size(); j++) {
			if (uploadfile_queue[j].uploadurl != "" and not uploadfile_queue[j].uploading) {
				uploadfile_queue[j].uploading = true;
				std::string postq = generateUploadPOST(&uploadfile_queue[j]);

				sslbuffer_in.clear();
				sslbuffer.clear();

				sslbuffer.addData(postq.c_str(), postq.size());

				sslstatus = 5;
				break;
			}
		}
	}
}

void WhatsappConnection::processIncomingData()
{
	/* Parse the data and create as many Trees as possible */
	std::vector < Tree > treelist;
	try {
		if (inbuffer.size() >= 3) {
			/* Consume as many trees as possible */
			Tree t;
			do {
				t = parse_tree(&inbuffer);
				if (t.getTag() != "treeerr")
					treelist.push_back(t);
			} while (t.getTag() != "treeerr" and inbuffer.size() >= 3);
		}
	}
	catch(int n) {
		printf("In stream error! Need to handle this properly...\n");
		return;
	}

	/* Now process the tree list! */
	for (unsigned int i = 0; i < treelist.size(); i++) {
		if (treelist[i].getTag() == "challenge") {
			/* Generate a session key using the challege & the password */
			assert(conn_status == SessionWaitingChallenge);

			if (password.size() == 15) {
				KeyGenerator::generateKeyImei(password.c_str(), treelist[i].getData().c_str(), treelist[i].getData().size(), (char *)this->session_key);
			} else if (password.find(":") != std::string::npos) {
				KeyGenerator::generateKeyMAC(password, treelist[i].getData().c_str(), treelist[i].getData().size(), (char *)this->session_key);
			} else {
				KeyGenerator::generateKeyV2(password, treelist[i].getData().c_str(), treelist[i].getData().size(), (char *)this->session_key);
			}

			in = new RC4Decoder(session_key, 20, 256);
			out = new RC4Decoder(session_key, 20, 256);

			conn_status = SessionWaitingAuthOK;
			challenge_data = treelist[i].getData();

			this->sendResponse();
		} else if (treelist[i].getTag() == "success") {
			/* Notifies the success of the auth */
			conn_status = SessionConnected;
			if (treelist[i].hasAttribute("status"))
				this->account_status = treelist[i].getAttributes()["status"];
			if (treelist[i].hasAttribute("kind"))
				this->account_type = treelist[i].getAttributes()["kind"];
			if (treelist[i].hasAttribute("expiration"))
				this->account_expiration = treelist[i].getAttributes()["expiration"];
			if (treelist[i].hasAttribute("creation"))
				this->account_creation = treelist[i].getAttributes()["creation"];

			this->notifyMyPresence();
			this->sendInitial();
			this->updateGroups();

			/*std::cout << "Logged in!!!" << std::endl; */
			/*std::cout << "Account " << phone << " status: " << account_status << " kind: " << account_type << */
			/*      " expires: " << account_expiration << " creation: " << account_creation << std::endl; */
		} else if (treelist[i].getTag() == "failure") {
			if (conn_status == SessionWaitingAuthOK)
				this->notifyError(errorAuth);
			else
				this->notifyError(errorUnknown);
		} else if (treelist[i].getTag() == "message") {
			/* Receives a message! */
			if (treelist[i].hasAttributeValue("type", "chat") and treelist[i].hasAttribute("from")) {
				unsigned long long time = 0;
				if (treelist[i].hasAttribute("t"))
					time = str2lng(treelist[i].getAttribute("t"));
				std::string from = treelist[i].getAttribute("from");
				std::string id = treelist[i].getAttribute("id");
				std::string author = treelist[i].getAttribute("author");

				Tree t = treelist[i].getChild("body");
				if (t.getTag() != "treeerr") {
					this->receiveMessage(ChatMessage(this, from, time, id, t.getData(), author));
				}
				t = treelist[i].getChild("media");
				if (t.getTag() != "treeerr") {
					if (t.hasAttributeValue("type", "image")) {
						this->receiveMessage(ImageMessage(this, from, time, id, author, t.getAttribute("url"), str2int(t.getAttribute("width")), str2int(t.getAttribute("height")), str2int(t.getAttribute("size")), t.getAttribute("encoding"), t.getAttribute("filehash"), t.getAttribute("mimetype"), t.getData()));
					} else if (t.hasAttributeValue("type", "location")) {
						this->receiveMessage(LocationMessage(this, from, time, id, author, str2dbl(t.getAttribute("latitude")), str2dbl(t.getAttribute("longitude")), t.getData()));
					} else if (t.hasAttributeValue("type", "audio")) {
						this->receiveMessage(SoundMessage(this, from, time, id, author, t.getAttribute("url"), t.getAttribute("filehash"), t.getAttribute("mimetype")));
					}
				}
				t = treelist[i].getChild("composing");
				if (t.getTag() != "treeerr") {
					this->gotTyping(from, "composing");
					continue;
				}
				t = treelist[i].getChild("paused");
				if (t.getTag() != "treeerr") {
					this->gotTyping(from, "paused");
					continue;
				}
			} else if (treelist[i].hasAttributeValue("type", "notification") and treelist[i].hasAttribute("from")) {
				/* If the nofitication comes from a group, assume we have to reload groups ;) */
				updateGroups();
			}
			/* Generate response for the messages */
			if (treelist[i].hasAttribute("type") and treelist[i].hasAttribute("from")) {
				std::string answer = "received";
				if (treelist[i].hasChild("received"))
					answer = "ack";
				DataBuffer reply = generateResponse(treelist[i].getAttribute("from"),
								    treelist[i].getAttribute("type"),
								    treelist[i].getAttribute("id"),
								    answer);
				outbuffer = outbuffer + reply;
			}
			if (treelist[i].hasAttribute("type") and treelist[i].hasAttribute("from")) {
				/*and treelist[i].hasChild("request")) { // and treelist[i].hasChild("notify") */

			}
		} else if (treelist[i].getTag() == "presence") {
			/* Receives the presence of the user */
			if (treelist[i].hasAttribute("from") and treelist[i].hasAttribute("type")) {
				this->notifyPresence(treelist[i].getAttribute("from"), treelist[i].getAttribute("type"));
			}
		} else if (treelist[i].getTag() == "iq") {
			/* Receives the presence of the user */
			if (atoi(treelist[i].getAttribute("id").c_str()) == gw1)
				gq_stat |= 2;
			if (atoi(treelist[i].getAttribute("id").c_str()) == gw2)
				gq_stat |= 4;

			if (treelist[i].hasAttributeValue("type", "result") and treelist[i].hasAttribute("from")) {
				Tree t = treelist[i].getChild("query");
				if (t.getTag() != "treeerr") {
					if (t.hasAttributeValue("xmlns", "jabber:iq:last") and t.hasAttribute("seconds")) {

						this->notifyLastSeen(treelist[i].getAttribute("from"), t.getAttribute("seconds"));
					}
				}
				t = treelist[i].getChild("picture");
				if (t.getTag() != "treeerr") {
					if (t.hasAttributeValue("type", "preview"))
						this->addPreviewPicture(treelist[i].getAttribute("from"), t.getData());
					if (t.hasAttributeValue("type", "image"))
						this->addFullsizePicture(treelist[i].getAttribute("from"), t.getData());
				}

				t = treelist[i].getChild("media");
				if (t.getTag() != "treeerr") {
					for (unsigned int j = 0; j < uploadfile_queue.size(); j++) {
						if (uploadfile_queue[j].rid == str2int(treelist[i].getAttribute("id"))) {
							/* Queue to upload the file */
							uploadfile_queue[j].uploadurl = t.getAttribute("url");
							std::string host = uploadfile_queue[j].uploadurl.substr(8);	/* Remove https:// */
							for (unsigned int i = 0; i < host.size(); i++)
								if (host[i] == '/')
									host = host.substr(0, i);
							uploadfile_queue[j].host = host;

							this->processUploadQueue();
							break;
						}
					}
				}

				t = treelist[i].getChild("duplicate");
				if (t.getTag() != "treeerr") {
					for (unsigned int j = 0; j < uploadfile_queue.size(); j++) {
						if (uploadfile_queue[j].rid == str2int(treelist[i].getAttribute("id"))) {
							/* Generate a fake JSON and process directly */
							std::string json = "{\"name\":\"" + uploadfile_queue[j].file + "\"," "\"url\":\"" + t.getAttribute("url") + "\"," "\"size\":\"" + t.getAttribute("size") + "\"," "\"mimetype\":\"" + t.getAttribute("mimetype") + "\"," "\"filehash\":\"" + t.getAttribute("filehash") + "\"," "\"type\":\"" + t.getAttribute("type") + "\"," "\"width\":\"" + t.getAttribute("width") + "\"," "\"height\":\"" + t.getAttribute("height") + "\"}";

							uploadfile_queue[j].uploading = true;
							this->updateFileUpload(json);
							break;
						}
					}
				}

				std::vector < Tree > childs = treelist[i].getChildren();
				int acc = 0;
				for (unsigned int j = 0; j < childs.size(); j++) {
					if (childs[j].getTag() == "group") {
						bool rep = groups.find(getusername(childs[j].getAttribute("id"))) != groups.end();
						if (not rep) {
							groups.insert(std::pair < std::string, Group > (getusername(childs[j].getAttribute("id")), Group(getusername(childs[j].getAttribute("id")), childs[j].getAttribute("subject"), getusername(childs[j].getAttribute("owner")))));

							/* Query group participants */
							Tree iq("list", makeAttr1("xmlns", "w:g"));
							Tree req("iq", makeAttr3("id", int2str(iqid++), "type", "get", "to", childs[j].getAttribute("id") + "@g.us"));
							req.addChild(iq);
							gw3++;
							outbuffer = outbuffer + serialize_tree(&req);
						}
					} else if (childs[j].getTag() == "participant") {
						std::string gid = getusername(treelist[i].getAttribute("from"));
						std::string pt = getusername(childs[j].getAttribute("jid"));
						if (groups.find(gid) != groups.end()) {
							groups.find(gid)->second.participants.push_back(pt);
						}
						if (!acc)
							gw3--;
						acc = 1;
					} else if (childs[j].getTag() == "add") {

					}
				}

				t = treelist[i].getChild("group");
				if (t.getTag() != "treeerr") {
					if (t.hasAttributeValue("type", "preview"))
						this->addPreviewPicture(treelist[i].getAttribute("from"), t.getData());
					if (t.hasAttributeValue("type", "image"))
						this->addFullsizePicture(treelist[i].getAttribute("from"), t.getData());
				}
			}
			if (treelist[i].hasAttribute("from") and treelist[i].hasAttribute("id") and treelist[i].hasChild("ping")) {
				this->doPong(treelist[i].getAttribute("id"), treelist[i].getAttribute("from"));
			}
		}
	}

	if (gq_stat == 8 and recv_messages_delay.size() != 0) {
		for (unsigned int i = 0; i < recv_messages_delay.size(); i++) {
			recv_messages.push_back(recv_messages_delay[i]);
		}
		recv_messages_delay.clear();
	}
}

DataBuffer WhatsappConnection::serialize_tree(Tree * tree, bool crypt)
{
	DataBuffer data = write_tree(tree);
	unsigned char flag = 0;
	if (crypt) {
		data = data.encodedBuffer(this->out, this->session_key, true);
		flag = 0x10;
	}

	DataBuffer ret;
	ret.putInt(flag, 1);
	ret.putInt(data.size(), 2);
	ret = ret + data;
	return ret;
}

DataBuffer WhatsappConnection::write_tree(Tree * tree)
{
	DataBuffer bout;
	int len = 1;

	if (tree->getAttributes().size() != 0)
		len += tree->getAttributes().size() * 2;
	if (tree->getChildren().size() != 0)
		len++;
	if (tree->getData().size() != 0 or tree->forcedData())
		len++;

	bout.writeListSize(len);
	if (tree->getTag() == "start")
		bout.putInt(1, 1);
	else
		bout.putString(tree->getTag());
	tree->writeAttributes(&bout);

	if (tree->getData().size() > 0 or tree->forcedData())
		bout.putRawString(tree->getData());
	if (tree->getChildren().size() > 0) {
		bout.writeListSize(tree->getChildren().size());

		for (unsigned int i = 0; i < tree->getChildren().size(); i++) {
			DataBuffer tt = write_tree(&tree->getChildren()[i]);
			bout = bout + tt;
		}
	}
	return bout;
}

Tree WhatsappConnection::parse_tree(DataBuffer * data)
{
	int bflag = (data->getInt(1) & 0xF0) >> 4;
	int bsize = data->getInt(2, 1);
	if (bsize > data->size() - 3) {
		return Tree("treeerr");	/* Next message incomplete, return consumed data */
	}
	data->popData(3);

	if (bflag & 8) {
		/* Decode data, buffer conversion */
		if (this->in != NULL) {
			DataBuffer *decoded_data = data->decodedBuffer(this->in, bsize, false);

			/* Remove hash */
			decoded_data->popData(4);

			/* Call recursive */
			data->popData(bsize);	/* Pop data unencrypted for next parsing! */
			return read_tree(decoded_data);
		} else {
			printf("Received crypted data before establishing crypted layer! Skipping!\n");
			data->popData(bsize);
			return Tree("treeerr");
		}
	} else {
		return read_tree(data);
	}
}

Tree WhatsappConnection::read_tree(DataBuffer * data)
{
	int lsize = data->readListSize();
	int type = data->getInt(1);
	if (type == 1) {
		data->popData(1);
		Tree t;
		t.readAttributes(data, lsize);
		t.setTag("start");
		return t;
	} else if (type == 2) {
		data->popData(1);
		return Tree("treeerr");	/* No data in this tree... */
	}

	Tree t;
	t.setTag(data->readString());
	t.readAttributes(data, lsize);

	if ((lsize & 1) == 1) {
		return t;
	}

	if (data->isList()) {
		t.setChildren(data->readList(this));
	} else {
		t.setData(data->readString());
	}

	return t;
}

static int isgroup(const std::string user)
{
	return (user.find('-') != std::string::npos);
}

void WhatsappConnection::receiveMessage(const Message & m)
{
	/* Push message to user and generate a response */
	Message *mc = m.copy();
	if (isgroup(m.from) and gq_stat != 8)	/* Delay the group message deliver if we do not have the group list */
		recv_messages_delay.push_back(mc);
	else
		recv_messages.push_back(mc);

	/*std::cout << "Received message type " << m.type() << " from " << m.from << " at " << m.t << std::endl; */

	/* Now add the contact in the list (to query the profile picture) */
	if (contacts.find(m.from) == contacts.end())
		contacts[m.from] = Contact(m.from, false);
	this->addContacts(std::vector < std::string > ());
}

void WhatsappConnection::notifyLastSeen(std::string from, std::string seconds)
{
	from = getusername(from);
	contacts[from].last_seen = str2lng(seconds);
}

void WhatsappConnection::notifyPresence(std::string from, std::string status)
{
	from = getusername(from);
	contacts[from].presence = status;
	user_changes.push_back(from);
}

void WhatsappConnection::addPreviewPicture(std::string from, std::string picture)
{
	from = getusername(from);
	if (contacts.find(from) == contacts.end()) {
		Contact newc(from, false);
		contacts[from] = newc;
	}
	contacts[from].ppprev = picture;
	user_icons.push_back(from);
}

void WhatsappConnection::addFullsizePicture(std::string from, std::string picture)
{
	from = getusername(from);
	if (contacts.find(from) == contacts.end()) {
		Contact newc(from, false);
		contacts[from] = newc;
	}
	contacts[from].pppicture = picture;
}

void WhatsappConnection::setMyPresence(std::string s, std::string msg)
{
	if (s != mypresence) {
		mypresence = s;
		notifyMyPresence();
	}
	if (msg != mymessage) {
		mymessage = msg;
		notifyMyMessage();	/*TODO */
	}
}

void WhatsappConnection::notifyMyPresence()
{
	/* Send the nickname and the current status */
	Tree pres("presence", makeAttr2("name", nickname, "type", mypresence));

	outbuffer = outbuffer + serialize_tree(&pres);
}

void WhatsappConnection::sendInitial()
{
	Tree iq("iq", makeAttr3("id", int2str(iqid++), "type", "get", "to", whatsappserver));
	Tree conf("config", makeAttr1("xmlns", "urn:xmpp:whatsapp:push"));
	iq.addChild(conf);

	outbuffer = outbuffer + serialize_tree(&iq);
}

void WhatsappConnection::notifyMyMessage()
{
	/* Send the status message */
	Tree xhash("x", makeAttr1("xmlns", "jabber:x:event"));
	xhash.addChild(Tree("server"));
	Tree tbody("body");
	tbody.setData(this->mymessage);

	Tree mes("message", makeAttr3("to", "s.us", "type", "chat", "id", int2str(time(NULL)) + "-" + int2str(iqid++)));
	mes.addChild(xhash);
	mes.addChild(tbody);

	outbuffer = outbuffer + serialize_tree(&mes);
}

void WhatsappConnection::notifyError(ErrorCode err)
{

}

// Returns an integer indicating the next message type (sorting by timestamp)
int WhatsappConnection::query_next() {
	int res = -1;
	unsigned int cur_ts = ~0;
	for (unsigned int i = 0; i < recv_messages.size(); i++) {
		if (recv_messages[i]->t < cur_ts) {
			cur_ts = recv_messages[i]->t;
			res = recv_messages[i]->type();
		}
	}
	return res;
}

bool WhatsappConnection::query_chat(std::string & from, std::string & message, std::string & author, unsigned long &t)
{
	for (unsigned int i = 0; i < recv_messages.size(); i++) {
		if (recv_messages[i]->type() == 0) {
			from = recv_messages[i]->from;
			t = recv_messages[i]->t;
			message = ((ChatMessage *) recv_messages[i])->message;
			author = ((ChatMessage *) recv_messages[i])->author;
			delete recv_messages[i];
			recv_messages.erase(recv_messages.begin() + i);
			return true;
		}
	}
	return false;
}

bool WhatsappConnection::query_chatimages(std::string & from, std::string & preview, std::string & url, std::string & author, unsigned long &t)
{
	for (unsigned int i = 0; i < recv_messages.size(); i++) {
		if (recv_messages[i]->type() == 1) {
			from = recv_messages[i]->from;
			t = recv_messages[i]->t;
			preview = ((ImageMessage *) recv_messages[i])->preview;
			url = ((ImageMessage *) recv_messages[i])->url;
			author = ((ImageMessage *) recv_messages[i])->author;
			delete recv_messages[i];
			recv_messages.erase(recv_messages.begin() + i);
			return true;
		}
	}
	return false;
}

bool WhatsappConnection::query_chatsounds(std::string & from, std::string & url, std::string & author, unsigned long &t)
{
	for (unsigned int i = 0; i < recv_messages.size(); i++) {
		if (recv_messages[i]->type() == 3) {
			from = recv_messages[i]->from;
			t = recv_messages[i]->t;
			url = ((SoundMessage *) recv_messages[i])->url;
			author = ((SoundMessage *) recv_messages[i])->author;
			delete recv_messages[i];
			recv_messages.erase(recv_messages.begin() + i);
			return true;
		}
	}
	return false;
}

bool WhatsappConnection::query_chatlocations(std::string & from, double &lat, double &lng, std::string & prev, std::string & author, unsigned long &t)
{
	for (unsigned int i = 0; i < recv_messages.size(); i++) {
		if (recv_messages[i]->type() == 2) {
			from = recv_messages[i]->from;
			t = recv_messages[i]->t;
			prev = ((LocationMessage *) recv_messages[i])->preview;
			lat = ((LocationMessage *) recv_messages[i])->latitude;
			lng = ((LocationMessage *) recv_messages[i])->longitude;
			author = ((LocationMessage *) recv_messages[i])->author;
			delete recv_messages[i];
			recv_messages.erase(recv_messages.begin() + i);
			return true;
		}
	}
	return false;
}

int WhatsappConnection::getuserstatus(const std::string & who)
{
	if (contacts.find(who) != contacts.end()) {
		if (contacts[who].presence == "available")
			return 1;
		return 0;
	}
	return -1;
}

std::string WhatsappConnection::getuserstatusstring(const std::string & who)
{
	if (contacts.find(who) != contacts.end()) {
		return contacts[who].status;
	}
	return "";
}

unsigned long long WhatsappConnection::getlastseen(const std::string & who)
{
	/* Schedule a last seen update, just in case */
	this->getLast(std::string(who) + "@" + whatsappserver);

	if (contacts.find(who) != contacts.end()) {
		return contacts[who].last_seen;
	}
	return ~0;
}

bool WhatsappConnection::query_status(std::string & from, int &status)
{
	while (user_changes.size() > 0) {
		if (contacts.find(user_changes[0]) != contacts.end()) {
			from = user_changes[0];
			status = 0;
			if (contacts[from].presence == "available")
				status = 1;

			user_changes.erase(user_changes.begin());
			return true;
		}
		user_changes.erase(user_changes.begin());
	}
	return false;
}

bool WhatsappConnection::query_typing(std::string & from, int &status)
{
	while (user_typing.size() > 0) {
		if (contacts.find(user_typing[0]) != contacts.end()) {
			from = user_typing[0];
			status = 0;
			if (contacts[from].typing == "composing")
				status = 1;

			user_typing.erase(user_typing.begin());
			return true;
		}
		user_typing.erase(user_typing.begin());
	}
	return false;
}

bool WhatsappConnection::query_icon(std::string & from, std::string & icon, std::string & hash)
{
	while (user_icons.size() > 0) {
		if (contacts.find(user_icons[0]) != contacts.end()) {
			from = user_icons[0];
			icon = contacts[from].ppprev;
			hash = "";

			user_icons.erase(user_icons.begin());
			return true;
		}
		user_icons.erase(user_icons.begin());
	}
	return false;
}

bool WhatsappConnection::query_avatar(std::string user, std::string & icon)
{
	user = getusername(user);
	if (contacts.find(user) != contacts.end()) {
		icon = contacts[user].pppicture;
		if (icon.size() == 0) {
			/* Return preview icon and query the fullsize picture */
			/* for future displays to save bandwidth */
			this->queryFullSize(user + "@" + whatsappserver);
			icon = contacts[user].ppprev;
		}
		return true;
	}
	return false;
}

void WhatsappConnection::doPong(std::string id, std::string from)
{
	std::map < std::string, std::string > auth;
	auth["to"] = from;
	auth["id"] = id;
	auth["type"] = "result";
	Tree t("iq", auth);

	outbuffer = outbuffer + serialize_tree(&t);
}

void WhatsappConnection::sendResponse()
{
	std::map < std::string, std::string > auth;
	auth["xmlns"] = "urn:ietf:params:xml:ns:xmpp-sasl";
	Tree t("response", auth);

	std::string response = phone + challenge_data + int2str(time(NULL));
	DataBuffer eresponse(response.c_str(), response.size());
	eresponse = eresponse.encodedBuffer(this->out, this->session_key, false);
	response = eresponse.toString();
	t.setData(response);

	outbuffer = outbuffer + serialize_tree(&t, false);
}

std::string WhatsappConnection::generateHeaders(std::string auth, int content_length)
{
	std::string h = "User-Agent: WhatsApp/2.4.7 S40Version/14.26 Device/Nokia302\r\n" "Accept: text/json\r\n" "Content-Type: application/x-www-form-urlencoded\r\n" "Authorization: " + auth + "\r\n" "Accept-Encoding: identity\r\n" "Content-Length: " + int2str(content_length) + "\r\n";
	return h;
}

std::string WhatsappConnection::generateHttpAuth(std::string nonce)
{
	/* cnonce is a 10 ascii char random string */
	std::string cnonce;
	for (int i = 0; i < 10; i++)
		cnonce += ('a' + (rand() % 25));

	std::string credentials = phone + ":s.whatsapp.net:" + base64_decode(password);
	std::string response = md5hex(md5hex(md5raw(credentials) + ":" + nonce + ":" + cnonce) + ":" + nonce + ":00000001:" + cnonce + ":auth:" + md5hex("AUTHENTICATE:WAWA/s.whatsapp.net"));

	return "X-WAWA: username=\"" + phone + "\",digest-uri=\"WAWA/s.whatsapp.net\"" + ",realm=\"s.whatsapp.net\",nonce=\"" + nonce + "\",cnonce=\"" + cnonce + "\",nc=\"00000001\",qop=\"auth\",digest-uri=\"WAWA/s.whatsapp.net\"," + "response=\"" + response + "\",charset=\"utf-8\"";
}

class WhatsappConnectionAPI {
private:
	WhatsappConnection * connection;

public:
	WhatsappConnectionAPI(std::string phone, std::string password, std::string nick);
	~WhatsappConnectionAPI();

	void doLogin(std::string);
	void receiveCallback(const char *data, int len);
	int sendCallback(char *data, int len);
	void sentCallback(int len);
	bool hasDataToSend();

	void addContacts(std::vector < std::string > clist);
	void sendChat(std::string to, std::string message);
	void sendGroupChat(std::string to, std::string message);
	bool query_chat(std::string & from, std::string & message, std::string & author, unsigned long &t);
	bool query_chatimages(std::string & from, std::string & preview, std::string & url, std::string & author, unsigned long &t);
	bool query_chatsounds(std::string & from, std::string & url, std::string & author, unsigned long &t);
	bool query_chatlocations(std::string & from, double &lat, double &lng, std::string & prev, std::string & author, unsigned long &t);
	bool query_status(std::string & from, int &status);
	int query_next();
	bool query_icon(std::string & from, std::string & icon, std::string & hash);
	bool query_avatar(std::string user, std::string & icon);
	bool query_typing(std::string & from, int &status);
	void account_info(unsigned long long &creation, unsigned long long &freeexp, std::string & status);
	void send_avatar(const std::string & avatar);
	int getuserstatus(const std::string & who);
	std::string getuserstatusstring(const std::string & who);
	unsigned long long getlastseen(const std::string & who);
	void addGroup(std::string subject);
	void leaveGroup(std::string group);
	void manageParticipant(std::string group, std::string participant, std::string command);

	void notifyTyping(std::string who, int status);
	void setMyPresence(std::string s, std::string msg);

	std::map < std::string, Group > getGroups();
	bool groupsUpdated();

	int loginStatus() const;

	int sendImage(std::string to, int w, int h, unsigned int size, const char *fp);

	int sendSSLCallback(char *buffer, int maxbytes);
	int sentSSLCallback(int bytessent);
	void receiveSSLCallback(char *buffer, int bytesrecv);
	bool hasSSLDataToSend();
	bool closeSSLConnection();
	void SSLCloseCallback();
	bool hasSSLConnection(std::string & host, int *port);
	int uploadProgress(int &rid, int &bs);
};

WhatsappConnectionAPI::WhatsappConnectionAPI(std::string phone, std::string password, std::string nick)
{
	connection = new WhatsappConnection(phone, password, nick);
}

WhatsappConnectionAPI::~WhatsappConnectionAPI()
{
	delete connection;
}

std::map < std::string, Group > WhatsappConnectionAPI::getGroups()
{
	return connection->getGroups();
}

bool WhatsappConnectionAPI::groupsUpdated()
{
	return connection->groupsUpdated();
}

int WhatsappConnectionAPI::getuserstatus(const std::string & who)
{
	return connection->getuserstatus(who);
}

void WhatsappConnectionAPI::addGroup(std::string subject)
{
	connection->addGroup(subject);
}

void WhatsappConnectionAPI::leaveGroup(std::string subject)
{
	connection->leaveGroup(subject);
}

void WhatsappConnectionAPI::manageParticipant(std::string group, std::string participant, std::string command)
{
	connection->manageParticipant(group, participant, command);
}

unsigned long long WhatsappConnectionAPI::getlastseen(const std::string & who)
{
	return connection->getlastseen(who);
}

int WhatsappConnectionAPI::query_next() {
	return connection->query_next();
}

int WhatsappConnectionAPI::sendImage(std::string to, int w, int h, unsigned int size, const char *fp)
{
	return connection->sendImage(to, w, h, size, fp);
}

int WhatsappConnectionAPI::uploadProgress(int &rid, int &bs)
{
	return connection->uploadProgress(rid, bs);
}

void WhatsappConnectionAPI::send_avatar(const std::string & avatar)
{
	connection->send_avatar(avatar);
}

bool WhatsappConnectionAPI::query_icon(std::string & from, std::string & icon, std::string & hash)
{
	return connection->query_icon(from, icon, hash);
}

bool WhatsappConnectionAPI::query_avatar(std::string user, std::string & icon)
{
	return connection->query_avatar(user, icon);
}

bool WhatsappConnectionAPI::query_typing(std::string & from, int &status)
{
	return connection->query_typing(from, status);
}

void WhatsappConnectionAPI::setMyPresence(std::string s, std::string msg)
{
	connection->setMyPresence(s, msg);
}

void WhatsappConnectionAPI::notifyTyping(std::string who, int status)
{
	connection->notifyTyping(who, status);
}

std::string WhatsappConnectionAPI::getuserstatusstring(const std::string & who)
{
	return connection->getuserstatusstring(who);
}

bool WhatsappConnectionAPI::query_chatimages(std::string & from, std::string & preview, std::string & url, std::string & author, unsigned long &t)
{
	return connection->query_chatimages(from, preview, url, author, t);
}

bool WhatsappConnectionAPI::query_chatsounds(std::string & from, std::string & url, std::string & author, unsigned long &t)
{
	return connection->query_chatsounds(from, url, author, t);
}

bool WhatsappConnectionAPI::query_chat(std::string & from, std::string & msg, std::string & author, unsigned long &t)
{
	return connection->query_chat(from, msg, author, t);
}

bool WhatsappConnectionAPI::query_chatlocations(std::string & from, double &lat, double &lng, std::string & prev, std::string & author, unsigned long &t)
{
	return connection->query_chatlocations(from, lat, lng, prev, author, t);
}

bool WhatsappConnectionAPI::query_status(std::string & from, int &status)
{
	return connection->query_status(from, status);
}

void WhatsappConnectionAPI::sendChat(std::string to, std::string message)
{
	connection->sendChat(to, message);
}

void WhatsappConnectionAPI::sendGroupChat(std::string to, std::string message)
{
	connection->sendGroupChat(to, message);
}

int WhatsappConnectionAPI::loginStatus() const
{
	return connection->loginStatus();
}

void WhatsappConnectionAPI::doLogin(std::string resource)
{
	connection->doLogin(resource);
}

void WhatsappConnectionAPI::receiveCallback(const char *data, int len)
{
	connection->receiveCallback(data, len);
}

int WhatsappConnectionAPI::sendCallback(char *data, int len)
{
	return connection->sendCallback(data, len);
}

void WhatsappConnectionAPI::sentCallback(int len)
{
	connection->sentCallback(len);
}

void WhatsappConnectionAPI::addContacts(std::vector < std::string > clist)
{
	connection->addContacts(clist);
}

bool WhatsappConnectionAPI::hasDataToSend()
{
	return connection->hasDataToSend();
}

void WhatsappConnectionAPI::account_info(unsigned long long &creation, unsigned long long &freeexp, std::string & status)
{
	connection->account_info(creation, freeexp, status);
}

int WhatsappConnectionAPI::sendSSLCallback(char *buffer, int maxbytes)
{
	return connection->sendSSLCallback(buffer, maxbytes);
}

int WhatsappConnectionAPI::sentSSLCallback(int bytessent)
{
	return connection->sentSSLCallback(bytessent);
}

void WhatsappConnectionAPI::receiveSSLCallback(char *buffer, int bytesrecv)
{
	connection->receiveSSLCallback(buffer, bytesrecv);
}

bool WhatsappConnectionAPI::hasSSLDataToSend()
{
	return connection->hasSSLDataToSend();
}

bool WhatsappConnectionAPI::closeSSLConnection()
{
	return connection->closeSSLConnection();
}

void WhatsappConnectionAPI::SSLCloseCallback()
{
	connection->SSLCloseCallback();
}

bool WhatsappConnectionAPI::hasSSLConnection(std::string & host, int *port)
{
	return connection->hasSSLConnection(host, port);
}

static const std::string base64_chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";
static inline bool is_base64(unsigned char c)
{
	return (isalnum(c) || (c == '+') || (c == '/'));
}

std::string base64_decode(std::string const &encoded_string)
{
	int in_len = encoded_string.size();
	int i = 0;
	int j = 0;
	int in_ = 0;
	unsigned char char_array_4[4], char_array_3[3];
	std::string ret;

	while (in_len-- && (encoded_string[in_] != '=') && is_base64(encoded_string[in_])) {
		char_array_4[i++] = encoded_string[in_];
		in_++;
		if (i == 4) {
			for (i = 0; i < 4; i++)
				char_array_4[i] = base64_chars.find(char_array_4[i]);

			char_array_3[0] = (char_array_4[0] << 2) + ((char_array_4[1] & 0x30) >> 4);
			char_array_3[1] = ((char_array_4[1] & 0xf) << 4) + ((char_array_4[2] & 0x3c) >> 2);
			char_array_3[2] = ((char_array_4[2] & 0x3) << 6) + char_array_4[3];

			for (i = 0; (i < 3); i++)
				ret += char_array_3[i];
			i = 0;
		}
	}

	if (i) {
		for (j = i; j < 4; j++)
			char_array_4[j] = 0;

		for (j = 0; j < 4; j++)
			char_array_4[j] = base64_chars.find(char_array_4[j]);

		char_array_3[0] = (char_array_4[0] << 2) + ((char_array_4[1] & 0x30) >> 4);
		char_array_3[1] = ((char_array_4[1] & 0xf) << 4) + ((char_array_4[2] & 0x3c) >> 2);
		char_array_3[2] = ((char_array_4[2] & 0x3) << 6) + char_array_4[3];

		for (j = 0; (j < i - 1); j++)
			ret += char_array_3[j];
	}

	return ret;
}
