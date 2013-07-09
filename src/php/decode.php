<?php
function decode($hex)
{
    $hexarr = str_split($hex, 2);
    $str = null;
    foreach ($hexarr as $k => $v) {
        $str .= "  " . getToken(hexdec($v));
    }

    return $str;
}

function str2hex($string)
{
    $hexstr = unpack('H*', $string);

    return array_shift($hexstr);
}

function hex2str($hexstr)
{
    $hexstr = str_replace(' ', '', $hexstr);
    $hexstr = str_replace('\x', '', $hexstr);
    $retstr = pack('H*', $hexstr);

    return $retstr;
}

function printhexstr($data, $name)
{
    $data = str2hex($data);
    $len = strlen($data);
    print("Len: $len - $name\n");
    for ($i = 0; $i < $len; $i += 2) {
        if ((($i - 1) % 32) == 31) {
            print("\n");
        }
        printf(" %s%s", $data[$i], $data[$i + 1]);
    }

    print("\n");
}

function getDictionary()
{
    $dict = "WHATISTHIS";
    $dic[0] = 0;
    $dic[1] = 0;
    $dic[2] = 0;
    $dic[3] = 0;
    $dic[4] = 0;
    $dic[5] = "account";
    $dic[6] = "ack";
    $dic[7] = "action";
    $dic[8] = "active";
    $dic[9] = "add";
    $dic[10] = "after";
    $dic[11] = "ib";
    $dic[12] = "all";
    $dic[13] = "allow";
    $dic[14] = "apple";
    $dic[15] = "audio";
    $dic[16] = "auth";
    $dic[17] = "author";
    $dic[18] = "available";
    $dic[19] = "bad-protocol";
    $dic[20] = "bad-request";
    $dic[21] = "before";
    $dic[22] = "Bell.caf";
    $dic[23] = "body";
    $dic[24] = "Boing.caf";
    $dic[25] = "cancel";
    $dic[26] = "category";
    $dic[27] = "challenge";
    $dic[28] = "chat";
    $dic[29] = "clean";
    $dic[30] = "code";
    $dic[31] = "composing";
    $dic[32] = "config";
    $dic[33] = "conflict";
    $dic[34] = "contacts";
    $dic[35] = "count";
    $dic[36] = "create";
    $dic[37] = "creation";
    $dic[38] = "default";
    $dic[39] = "delay";
    $dic[40] = "delete";
    $dic[41] = "delivered";
    $dic[42] = "deny";
    $dic[43] = "digest";
    $dic[44] = "DIGEST-MD5-1";
    $dic[45] = "DIGEST-MD5-2";
    $dic[46] = "dirty";
    $dic[47] = "elapsed";
    $dic[48] = "broadcast";
    $dic[49] = "enable";
    $dic[50] = "encoding";
    $dic[51] = "duplicate";
    $dic[52] = "error";
    $dic[53] = "event";
    $dic[54] = "expiration";
    $dic[55] = "expired";
    $dic[56] = "fail";
    $dic[57] = "failure";
    $dic[58] = "false";
    $dic[59] = "favorites";
    $dic[60] = "feature";
    $dic[61] = "features";
    $dic[62] = "field";
    $dic[63] = "first";
    $dic[64] = "free";
    $dic[65] = "from";
    $dic[66] = "g.us";
    $dic[67] = "get";
    $dic[68] = "Glass.caf";
    $dic[69] = "google";
    $dic[70] = "group";
    $dic[71] = "groups";
    $dic[72] = "g_notify";
    $dic[73] = "g_sound";
    $dic[74] = "Harp.caf";
    $dic[75] = "http://etherx.jabber.org/streams";
    $dic[76] = "http://jabber.org/protocol/chatstates";
    $dic[77] = "id";
    $dic[78] = "image";
    $dic[79] = "img";
    $dic[80] = "inactive";
    $dic[81] = "index";
    $dic[82] = "internal-server-error";
    $dic[83] = "invalid-mechanism";
    $dic[84] = "ip";
    $dic[85] = "iq";
    $dic[86] = "item";
    $dic[87] = "item-not-found";
    $dic[88] = "user-not-found";
    $dic[89] = "jabber:iq:last";
    $dic[90] = "jabber:iq:privacy";
    $dic[91] = "jabber:x:delay";
    $dic[92] = "jabber:x:event";
    $dic[93] = "jid";
    $dic[94] = "jid-malformed";
    $dic[95] = "kind";
    $dic[96] = "last";
    $dic[97] = "latitude";
    $dic[98] = "lc";
    $dic[99] = "leave";
    $dic[100] = "leave-all";
    $dic[101] = "lg";
    $dic[102] = "list";
    $dic[103] = "location";
    $dic[104] = "longitude";
    $dic[105] = "max";
    $dic[106] = "max_groups";
    $dic[107] = "max_participants";
    $dic[108] = "max_subject";
    $dic[109] = "mechanism";
    $dic[110] = "media";
    $dic[111] = "message";
    $dic[112] = "message_acks";
    $dic[113] = "method";
    $dic[114] = "microsoft";
    $dic[115] = "missing";
    $dic[116] = "modify";
    $dic[117] = "mute";
    $dic[118] = "name";
    $dic[119] = "nokia";
    $dic[120] = "none";
    $dic[121] = "not-acceptable";
    $dic[122] = "not-allowed";
    $dic[123] = "not-authorized";
    $dic[124] = "notification";
    $dic[125] = "notify";
    $dic[126] = "off";
    $dic[127] = "offline";
    $dic[128] = "order";
    $dic[129] = "owner";
    $dic[130] = "owning";
    $dic[131] = "paid";
    $dic[132] = "participant";
    $dic[133] = "participants";
    $dic[134] = "participating";
    $dic[135] = "password";
    $dic[136] = "paused";
    $dic[137] = "picture";
    $dic[138] = "pin";
    $dic[139] = "ping";
    $dic[140] = "platform";
    $dic[141] = "pop_mean_time";
    $dic[142] = "pop_plus_minus";
    $dic[143] = "port";
    $dic[144] = "presence";
    $dic[145] = "preview";
    $dic[146] = "probe";
    $dic[147] = "proceed";
    $dic[148] = "prop";
    $dic[149] = "props";
    $dic[150] = "p_o";
    $dic[151] = "p_t";
    $dic[152] = "query";
    $dic[153] = "raw";
    $dic[154] = "reason";
    $dic[155] = "receipt";
    $dic[156] = "receipt_acks";
    $dic[157] = "received";
    $dic[158] = "registration";
    $dic[159] = "relay";
    $dic[160] = "remote-server-timeout";
    $dic[161] = "remove";
    $dic[162] = "Replaced by new connection";
    $dic[163] = "request";
    $dic[164] = "required";
    $dic[165] = "resource";
    $dic[166] = "resource-constraint";
    $dic[167] = "response";
    $dic[168] = "result";
    $dic[169] = "retry";
    $dic[170] = "rim";
    $dic[171] = "s.whatsapp.net";
    $dic[172] = "s.us";
    $dic[173] = "seconds";
    $dic[174] = "server";
    $dic[175] = "server-error";
    $dic[176] = "service-unavailable";
    $dic[177] = "set";
    $dic[178] = "show";
    $dic[179] = "sid";
    $dic[180] = "silent";
    $dic[181] = "sound";
    $dic[182] = "stamp";
    $dic[183] = "unsubscribe";
    $dic[184] = "stat";
    $dic[185] = "status";
    $dic[186] = "stream:error";
    $dic[187] = "stream:features";
    $dic[188] = "subject";
    $dic[189] = "subscribe";
    $dic[190] = "success";
    $dic[191] = "sync";
    $dic[192] = "system-shutdown";
    $dic[193] = "s_o";
    $dic[194] = "s_t";
    $dic[195] = "t";
    $dic[196] = "text";
    $dic[197] = "timeout";
    $dic[198] = "TimePassing.caf";
    $dic[199] = "timestamp";
    $dic[200] = "to";
    $dic[201] = "Tri-tone.caf";
    $dic[202] = "true";
    $dic[203] = "type";
    $dic[204] = "unavailable";
    $dic[205] = "uri";
    $dic[206] = "url";
    $dic[207] = "urn:ietf:params:xml:ns:xmpp-sasl";
    $dic[208] = "urn:ietf:params:xml:ns:xmpp-stanzas";
    $dic[209] = "urn:ietf:params:xml:ns:xmpp-streams";
    $dic[210] = "urn:xmpp:delay";
    $dic[211] = "urn:xmpp:ping";
    $dic[212] = "urn:xmpp:receipts";
    $dic[213] = "urn:xmpp:whatsapp";
    $dic[214] = "urn:xmpp:whatsapp:account";
    $dic[215] = "urn:xmpp:whatsapp:dirty";
    $dic[216] = "urn:xmpp:whatsapp:mms";
    $dic[217] = "urn:xmpp:whatsapp:push";
    $dic[218] = "user";
    $dic[219] = "username";
    $dic[220] = "value";
    $dic[221] = "vcard";
    $dic[222] = "version";
    $dic[223] = "video";
    $dic[224] = "w";
    $dic[225] = "w:g";
    $dic[226] = "w:p";
    $dic[227] = "w:p:r";
    $dic[228] = "w:profile:picture";
    $dic[229] = "wait";
    $dic[230] = "x";
    $dic[231] = "xml-not-well-formed";
    $dic[232] = "xmlns";
    $dic[233] = "xmlns:stream";
    $dic[234] = "Xylophone.caf";
    $dic[235] = "1";
    $dic[236] = "WAUTH-1";
    $dic[237] = 0;
    $dic[238] = 0;
    $dic[239] = 0;
    $dic[240] = 0;
    $dic[241] = 0;
    $dic[242] = 0;
    $dic[243] = 0;
    $dic[244] = 0;
    $dic[245] = 0;
    $dic[246] = 0;
    $dic[247] = 0;
    $dic[248] = "XXX";

    return $dic;
}

function getToken($token)
{
    $dic = getDictionary();

    return $dic[$token];
}
