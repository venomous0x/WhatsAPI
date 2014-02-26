#!/usr/bin/php
<?php
require '../src/php/whatsprot.class.php'; // Ruta a whatsprot.class.php

function fgets_u($pStdn)
{
    $pArr = array($pStdn);

    if (false === ($num_changed_streams = stream_select($pArr, $write = NULL, $except = NULL, 0))) {
        print("\$ 001 Socket Error : UNABLE TO WATCH STDIN.\n");

        return FALSE;
    } elseif ($num_changed_streams > 0) {
        return trim(fgets($pStdn, 1024));
    }
    return null;
}


$nickname = "WhatsAPI Test"; // Nick de usuario


// #### DO NOT ADD YOUR INFO AND THEN COMMIT THIS FILE! ####

// #### NO PONGAS TUS DATOS Y HAGAS COMMIT! ####
$sender = 	""; // numero de telefono con el codigo de pais ej: 34123456789
$imei = 	""; // Direccion MAC para iOS / IMEI para otras plataformas [NOTA]: NO es necesario
$password =     ""; // Tu contraseña obtenida con WART o WhatsAPI


if ($argc < 2) {
    echo "USO: ".$_SERVER['argv'][0]." [-l] [-s <telefono> <mensaje>] [-i <telefono>] [-set <estado>] [-music <telefono> <url>] [-photo <ruta de la imagen>]\n";
    echo "\ttelefono: Numero de telefono con codigo de pais ej: 34123456789\n";
    echo "\t-s: Envia mensaje\n";
    echo "\t-l: Se mantiene a la escucha de mensajes que te envian\n";
    echo "\t-i: conversacion interactiva con <telefono>\n";
    echo "\t-set: Cambia to estado a <estado>\n";
    echo "\t-music: Envia un fichero de musica a alguien\n";
	echo "\t-photo: Cambia tu foto de perfil\n";
	echo "\t-send: Manda una imagen a alguien\n";
    exit(1);
}

$dst=$_SERVER['argv'][2];
$msg = "";
for ($i=3; $i<$argc; $i++) {
    $msg .= $_SERVER['argv'][$i]." ";
}

echo "[] Iniciando sesion como '$nickname' ($sender)\n";
$wa = new WhatsProt($sender, $imei, $nickname, TRUE);

$wa->connect(); // Nos conectamos a la red de WhatsApp
$wa->loginWithPassword($password); // Iniciamos sesion con nuestra contraseña

if ($_SERVER['argv'][1] == "-i") {
    echo "\n[] Conversacion interactiva con $dst:\n";
    stream_set_timeout(STDIN,1);
    while (TRUE) {
        $wa->pollMessages();
        $buff = $wa->getMessages();
        if (!empty($buff)) {
            print_r($buff);
        }
        $line = fgets_u(STDIN);
        if ($line != "") {
            if (strrchr($line, " ")) {
                // needs PHP >= 5.3.0
                $command = trim(strstr($line, ' ', TRUE));
            } else {
                $command = $line;
            }
            $contact = $_SERVER['argv'][2];
            switch ($command) {
                case "/query":
                    $dst = trim(strstr($line, ' ', FALSE));
                    echo "[] Conversacion interactiva con $contact:\n";
                    break;
                case "/lastseen":
                    echo "[] Ultima vez en linea de $contact: ";
                    $wa->sendGetRequestLastSeen($dst);
                    break;
                case "/status":
                	echo "[] El estado de $contact es: ";
                	$wa->sendGetStatus($contact);
                	break;
                default:
                    echo "[] Enviar mensaje a $dst: $line\n";
                    $wa->sendMessage($dst , $line);
                    break;
            }
        }
    }
    exit(0);
}

if ($_SERVER['argv'][1] == "-l") {
    echo "\n[] Modo escucha:\n";
    while (TRUE) {
        $wa->pollMessages();
        $data = $wa->getMessages();
        if(!empty($data)) print_r($data);
        sleep(1);
    }
    exit(0);
}

if ($_SERVER['argv'][1] == "-set") {
    echo "\n[] Cambiando estado:\n";
    $wa->sendStatusUpdate($_SERVER['argv'][2]);
    exit(0);
}

if ($_SERVER['argv'][1] == "-music") {
	$music = $_SERVER['argv'][3];
    echo "\n[] Enviando archivo de musica: $music\n";
    $wa->sendMessageAudio($dst, $music);
    exit(0);
}

if ($_SERVER['argv'][1] == "-photo") {
	$path = $_SERVER['argv'][3];
    echo "\n[] Cambiando foto de perfil...\n";
    $wa->sendSetProfilePicture($path);
    exit(0);
}

if ($_SERVER['argv'][1] == "-send") {
	$image = $_SERVER['argv'][3];
    echo "\n[] Enviando el archivo de imagen: $image\nn";
    $wa->sendMessageImage($dst, $music);
    exit(0);
}


echo "\n[] Ultima vez en linea de $dst: ";
$wa->sendGetRequestLastSeen($dst);

echo "\n[] Enviar mensaje a $dst: $msg\n";
$wa->sendMessage($dst , $msg);
echo "\n";

?>
