For recent changes, refer to the [Change Log](https://github.com/venomous0x/WhatsAPI/blob/master/CHANGELOG.md)

# WhatsAPI

Interface to WhatsApp Messenger

----------


### What is WhatsApp?
According to [the company](http://www.whatsapp.com/): 

> “WhatsApp Messenger is a cross-platform mobile messenger that replaces SMS and works through the existing internet data plan of your device. WhatsApp is available for iPhone, BlackBerry, Android, Windows Phone, Nokia Symbian60 & S40 phones. Because WhatsApp Messenger uses the same internet data plan that you use for email and web browsing, there is no cost to message and stay in touch with your friends.”

Late 2011 numbers: 1 billion messages per day, ~20 million users.

### Modified XMPP
WhatsApp uses some sort of customized XMPP server, named internally as FunXMPP, which is basically some extended proprietary version.

### Login procedure
Much like XMPP, WhatsApp uses JID (jabber id) and password to successfully login to the service. The password is hashed and happened to be an MD5’d, reversed-version of the mobile’s IMEI (International Mobile Equipment Identity) or equivalent unique ID, stored in servers upon account creation and used transparently everytime the client connects the server.


The JID is a concatenation between your country’s code and mobile number.

Initial login uses Digest Access Authentication.

### Message sending
Messages are basically sent as TCP packets, following WhatsApp’s own format (unlike what’s defined in XMPP RFCs).


Despite the usage of SSL-like communication, messages are being sent in plain-text format.

### Multimedia Message sending
Photos, Videos and Audio files shared with WhatsApp contacts are HTTP-uploaded to a server before being sent to the recipient(s) along with Base64 thumbnail of media file (if applicable) along with the generated HTTP link as the message body.

# FAQ


- **What’s with the hex chars floating all over the code?**

	Mostly WhatsApp’s proprietary control chars/commands, or formatted data according to their server’s specifications, stored in predefined dictionaries within the clients.

- **What’s your future development plans?**

	We don’t have any.

- **Would it run over the web?**

	We’ve tested a slightly-modified version on top of Tornado Web Server and worked like a charm, however, building a chat client is a bit tricky, do your research.

- **Can I receive chats?**

	Indeed, using the same socket-receiving mechanism. But you have to parse the incoming data. Parsing functions aren’t included in this release, maybe in the next one?

- **I think the code is messy.**

	It’s working.

- **How can I obtain my password?**

It depends on your platform, with Android for example, you can use TelephonyManager

```JAVA
TelephonyManager tm = (TelephonyManager) getSystemService(Context.TELEPHONY_SERVICE);
```
```java
tm.getDeviceId();
```

With the sufficent permissions of course

```xml
<uses-permission android:name="android.permission.READ_PHONE_STATE"/>
```

On iOS however the password is the MD5 hash of the MAC address repeated twice
thanks to http://www.ezioamodio.it/?p=29

# NOTES

- This proof of concept is extensible to contain every feature that make a fully-fledged client, similar to the official ones, actually could be even better.

- During the two weeks of analysis of service mechanisms, we stumbled upon serious design and security flaws (they fixed some of them since 2011). For a company with such massive user base, we expected better practises and engineering.

- Perfectly working as PHP and JAVA ports.

# License

MIT - refer to the source code for the extra line.

# Venomous

Team of Bahraini Developers.

Ahmed Moh'd ([fb.com/ahmed.mhd](https://www.facebook.com/ahmed.mhd)) and Ali Hubail ([@hubail](https://twitter.com/hubail)) contributed to this release.
