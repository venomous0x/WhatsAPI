# WhatsAPI

Interfaz de WhatsApp Messenger

Ejemplos y proyectos Open Source en español por [@_mgp25](http://twitter.com/_mgp25)

----------


### Nota Junio 18, 2013

*Especial agradecimiento a:*
- *[Ali Hubail](https://github.com/hubail) y*
- *[Ahmed Moh'd](fb.com/ahmed.mhd) por permitir que este proyecto sea posible*
- *[Jannik Vogel](https://github.com/JayFoxRox) por conseguir el token de WhatsApp, quizás alguien deba escribir un libro sobre eso algún día...*
- *[Tarek Galal](https://github.com/tgalal) por proporcionar las ultimas funciones a yowsup*
- *[Atans](https://github.com/atans) y*
- *[Jonathan Williamson](https://github.com/jonnywilliamson) por diferentes parches*

*\- [shirioko](https://github.com/shirioko)*


----------

### ¿Qué es WhatsApp?
Según [la compañia](http://www.whatsapp.com/):

> “WhatsApp Messenger es una aplicación de mensajería multiplataforma que te permite enviar y recibir mensajes sin pagar por SMS. WhatsApp Messenger está disponible para iPhone, BlackBerry, Windows Phone, Android y Nokia, y todos esos dispositivos pueden comunicarse del uno al otro! Debido a que WhatsApp Messenger usa el plan de datos que ya tienes para email e internet, no hay un coste adicional para enviar mensajes y mantenerte en contacto con tus amigos.
> Además de aprovechar de la mensajería básica, usuarios WhatsApp pueden crear grupos, y enviar entre ellos un número ilimitado de imágenes, videos y mensajes de audio.”

Final del 2011: 1 billon de mensajes cada día, ~20 millones de usuarios.

### XMPP modificado
WhatsApp usa algún tipo de servidor XMPP customizado, llamado internamente FunXMPP, que es básicamente una versión ampliada.

### Procedimiento del login (inicio de sesión)
Como XMPP, WhatsApp utiliza JID (Jabber ID) y una contraseña para entrar con éxito al servicio. La contraseña se genera por el servidor y se recibe al momento del registro.


La JID es una concatenación entre el código del país y el número de móvil.

El inicio de sesión utiliza la autenticación de acceso Digest.

### Enviar Mensaje
Los mensajes se envían básicamente en forma de paquetes TCP, a raíz de un formato propio cd WhatsApp (a diferencia de lo que se define en el RFC XMPP).

Los mensajes son de nivel de aplicación utilizando cifrado RC4 keystreams

### Envío de mensajes multimedia
Fotos, vídeos y archivos de audio compartidos con los contactos WhatsApp se hacen a través de HTTP a un servidor antes de ser enviado al destinatario(s) junto con una miniatura de archivo multimedia codificado en base64 (si es aplicable), junto con el enlace HTTP generado como el cuerpo del mensaje.

### Sistema de eventos
WhatsApi utiliza un gestor de eventos (creada por [Facine] (https://github.com/facine)), que le permite responder a ciertos eventos.

Lista de los eventos y el código de ejemplo sobre cómo enlazar un controlador de eventos:
https://github.com/shirioko/WhatsAPI/wiki/WhatsApi-events

# FAQ


- **¿Qué pasa con los caracteres hexadecimales flotantes en todo el código?**

Mayormente caracteres / comandos o datos con formato de control de propiedad de WhatsApp acuerdo a las especificaciones de su servidor, el almacenamiento en los diccionarios predefinidos dentro de los clientes.

- **¿Cuál es su plan de desarrollo futuro?**

No tengo ninguno. Generalmente ire investigando por cuenta propia y desarrollando aquello que me parezca más interesante.

- **¿Se ejecutará a través de Internet?**

Puedes probar WhatsAppea.me un servicio online que te permite hablar con tus contactos de WhatsApp WATools ([http://whatsappea.me](https://www.whatsappea.me)) 

- **¿Puedo recibir chats?**

De hecho, utilizando el mismo mecanismo de enchufe-receptor. Pero hay que analizar los datos entrantes. Funciones de análisis no se incluyen en esta versión, tal vez en la próxima?

- **Creo que el código es desordenado.**

Está funcionando, ¿no?

- **¿Cómo puedo obtener mi contraseña?**

Registre un número usando WhatsAPI o WART (WhatsApp Registration Tool) (creada por [Shirioko] (https://github.com/shirioko))


# NOTA

- El PoC es extensible para contener todas las características y funciones que cualquier usuario dispondría con su versión en el móvil, al igual que los oficiales, en realidad podría ser aún mejor.
- Durante las dos semanas de análisis de los mecanismos de servicio, nos topamos con serios fallos en el diseño y seguridad (que arreglaron algunos de ellos desde 2011). Para una empresa con tal base de usuarios masiva, esperábamos mejores prácticas de ingeniería y seguridad.

# Licencia

MIT


# mgp25

Actualmente desarrollo yo solo, pero sería genial que la gente participase y aportase su granito de arena :)


# WATools

Herramienta online que te permite ver la imagen de perfil, estado y última conexión de una persona, así como mandar mensajes y archivos de manera anónima.

WATools ([http://watools.es](https://www.watools.es)) 
