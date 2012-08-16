<?php

function decode($hex){
	$hexarr = str_split($hex, 2);
	$str = null;
	foreach($hexarr as $k=>$v)	$str .= "  ".getToken(hexdec($v));
	return $str;
}

function str2hex($string) {
	$hexstr = unpack('H*', $string);
	return array_shift($hexstr);
}

function hex2str($hexstr) {
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
    for ($i = 0; $i < $len; $i += 2)
    {
        if ((($i-1) % 32) == 31)
        {
            print("\n");
        }
        printf(" %s%s", $data[$i], $data[$i+1]);
    }

    print("\n");
}

function getDictionary(){
    $dict = "WHATISTHIS";
	$dic[0] = 0;
	$dic[1] = 0;
	$dic[2] = 0;
	$dic[3] = 0;
	$dic[4] = 0;
	$dic[5] = "1";
	$dic[6] = "1.0";
	$dic[7] = "ack";
	$dic[8] = "action";
	$dic[9] = "active";
	$dic[10] = "add";
	$dic[11] = "all";
	$dic[12] = "allow";
	$dic[13] = "apple";
	$dic[14] = "audio";
	$dic[15] = "auth";
	$dic[16] = "author";
	$dic[17] = "available";
	$dic[18] = "bad-request";
	$dic[19] = "basee64";
	$dic[20] = "Bell.caf";
	$dic[21] = "bind";
	$dic[22] = "body";
	$dic[23] = "Boing.caf";
	$dic[24] = "cancel";
	$dic[25] = "category";
	$dic[26] = "challenge";
	$dic[27] = "chat";
	$dic[28] = "clean";
	$dic[29] = "code";
	$dic[30] = "composing";
	$dic[31] = "config";
	$dic[32] = "conflict";
	$dic[33] = "contacts";
	$dic[34] = "create";
	$dic[35] = "creation";
	$dic[36] = "default";
	$dic[37] = "delay";
	$dic[38] = "delete";
	$dic[39] = "delivered";
	$dic[40] = "deny";
	$dic[41] = "DIGEST-MD5";
	$dic[42] = "DIGEST-MD5-1";
	$dic[43] = "dirty";
	$dic[44] = "en";
	$dic[45] = "enable";
	$dic[46] = "encoding";
	$dic[47] = "error";
	$dic[48] = "expiration";
	$dic[49] = "expired";
	$dic[50] = "failure";
	$dic[51] = "false";
	$dic[52] = "favorites";
	$dic[53] = "feature";
	$dic[54] = "field";
	$dic[55] = "free";
	$dic[56] = "from";
	$dic[57] = "g.us";
	$dic[58] = "get";
	$dic[59] = "Glas.caf";
	$dic[60] = "google";
	$dic[61] = "group";
	$dic[62] = "groups";
	$dic[63] = "g_sound";
	$dic[64] = "Harp.caf";
	$dic[65] = "http://etherx.jabber.org/streams";
	$dic[66] = "http://jabber.org/protocol/chatstates";
	$dic[67] = "id";
	$dic[68] = "image";
	$dic[69] = "img";
	$dic[70] = "inactive";
	$dic[71] = "internal-server-error";
	$dic[72] = "iq";
	$dic[73] = "item";
	$dic[74] = "item-not-found";
	$dic[75] = "jabber:client";
	$dic[76] = "jabber:iq:last";
	$dic[77] = "jabber:iq:privacy";
	$dic[78] = "jabber:x:delay";
	$dic[79] = "jabber:x:event";
	$dic[80] = "jid";
	$dic[81] = "jid-malformed";
	$dic[82] = "kind";
	$dic[83] = "leave";
	$dic[84] = "leave-all";
	$dic[85] = "list";
	$dic[86] = "location";
	$dic[87] = "max_groups";
	$dic[88] = "max_participants";
	$dic[89] = "max_subject";
	$dic[90] = "mechanism";
	$dic[91] = "mechanisms";
	$dic[92] = "media";
	$dic[93] = "message";
	$dic[94] = "message_acks";
	$dic[95] = "missing";
	$dic[96] = "modify";
	$dic[97] = "name";
	$dic[98] = "not-acceptable";
	$dic[99] = "not-allowed";
	$dic[100] = "not-authorized";
	$dic[101] = "notify";
	$dic[102] = "Offline Storage";
	$dic[103] = "order";
	$dic[104] = "owner";
	$dic[105] = "owning";
	$dic[106] = "paid";
	$dic[107] = "participant";
	$dic[108] = "participants";
	$dic[109] = "participating";
	$dic[110] = "fail";
	$dic[111] = "paused";
	$dic[112] = "picture";
	$dic[113] = "ping";
	$dic[114] = "PLAIN";
	$dic[115] = "platform";
	$dic[116] = "presence";
	$dic[117] = "preview";
	$dic[118] = "probe";
	$dic[119] = "prop";
	$dic[120] = "props";
	$dic[121] = "p_o";
	$dic[122] = "p_t";
	$dic[123] = "query";
	$dic[124] = "raw";
	$dic[125] = "receipt";
	$dic[126] = "receipt_acks";
	$dic[127] = "received";
	$dic[128] = "relay";
	$dic[129] = "remove";
	$dic[130] = "Replaced by new connection";
	$dic[131] = "request";
	$dic[132] = "resource";
	$dic[133] = "resource-constraint";
	$dic[134] = "response";
	$dic[135] = "result";
	$dic[136] = "retry";
	$dic[137] = "rim";
	$dic[138] = "s.whatsapp.net";
	$dic[139] = "seconds";
	$dic[140] = "server";
	$dic[141] = "session";
	$dic[142] = "set";
	$dic[143] = "show";
	$dic[144] = "sid";
	$dic[145] = "sound";
	$dic[146] = "stamp";
	$dic[147] = "starttls";
	$dic[148] = "status";
	$dic[149] = "stream:error";
	$dic[150] = "stream:features";
	$dic[151] = "subject";
	$dic[152] = "subscribe";
	$dic[153] = "success";
	$dic[154] = "system-shutdown";
	$dic[155] = "s_o";
	$dic[156] = "s_t";
	$dic[157] = "t";
	$dic[158] = "TimePassing.caf";
	$dic[159] = "timestamp";
	$dic[160] = "to";
	$dic[161] = "Tri-tone.caf";
	$dic[162] = "type";
	$dic[163] = "unavailable";
	$dic[164] = "uri";
	$dic[165] = "url";
	$dic[166] = "urn:ietf:params:xml:ns:xmpp-bind";
	$dic[167] = "urn:ietf:params:xml:ns:xmpp-sasl";
	$dic[168] = "urn:ietf:params:xml:ns:xmpp-session";
	$dic[169] = "urn:ietf:params:xml:ns:xmpp-stanzas";
	$dic[170] = "urn:ietf:params:xml:ns:xmpp-streams";
	$dic[171] = "urn:xmpp:delay";
	$dic[172] = "urn:xmpp:ping";
	$dic[173] = "urn:xmpp:receipts";
	$dic[174] = "urn:xmpp:whatsapp";
	$dic[175] = "urn:xmpp:whatsapp:dirty";
	$dic[176] = "urn:xmpp:whatsapp:mms";
	$dic[177] = "urn:xmpp:whatsapp:push";
	$dic[178] = "value";
	$dic[179] = "vcard";
	$dic[180] = "version";
	$dic[181] = "video";
	$dic[182] = "w";
	$dic[183] = "w:g";
	$dic[184] = "w:p:r";
	$dic[185] = "wait";
	$dic[186] = "x";
	$dic[187] = "xml-not-well-formed";
	$dic[188] = "xml:lang";
	$dic[189] = "xmlns";
	$dic[190] = "xmlns:stream";
	$dic[191] = "Xylophone.caf";
	$dic[192] = "account";
	$dic[193] = "digest";
	$dic[194] = "g_notify";
	$dic[195] = "method";
	$dic[196] = "password";
	$dic[197] = "registration";
	$dic[198] = "stat";
	$dic[199] = "text";
	$dic[200] = "user";
	$dic[201] = "username";
	$dic[202] = "event";
	$dic[203] = "latitude";
	$dic[204] = "longitude";
	$dic[205] = "true";
	$dic[206] = "after";
	$dic[207] = "before";
	$dic[208] = "broadcast";
	$dic[209] = "count";
	$dic[210] = "features";
	$dic[211] = "first";
	$dic[212] = "index";
	$dic[213] = "invalid-mechanism";
	$dic[214] = "l$dict";
	$dic[215] = "max";
	$dic[216] = "offline";
	$dic[217] = "proceed";
	$dic[218] = "required";
	$dic[219] = "sync";
	$dic[220] = "elapsed";
	$dic[221] = "ip";
	$dic[222] = "microsoft";
	$dic[223] = "mute";
	$dic[224] = "nokia";
	$dic[225] = "off";
	$dic[226] = "pin";
	$dic[227] = "pop_mean_time";
	$dic[228] = "pop_plus_minus";
	$dic[229] = "port";
	$dic[230] = "reason";
	$dic[231] = "server-error";
	$dic[232] = "silent";
	$dic[233] = "timeout";
	$dic[234] = "lc";
	$dic[235] = "lg";
	$dic[236] = "bad-protocol";
	$dic[237] = "none";
	$dic[238] = "remote-server-timeout";
	$dic[239] = "service-unavailable";
	$dic[240] = "w:p";
	$dic[241] = "w:profile:picture";
	$dic[242] = "notification";
	$dic[243] = 0;
	$dic[244] = 0;
	$dic[245] = 0;
	$dic[246] = 0;
	$dic[247] = 0;
    $dic[248] = "XXX";
    return $dic;
}

function getToken($token){
    $dic = getDictionary();
	return $dic[$token];
}

		
?>
