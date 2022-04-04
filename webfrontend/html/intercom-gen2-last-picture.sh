#!/bin/bash
# Das ist ein (erfolgreicher) Proof of Concept um Bilder von einer Loxone intercom2 in anderen Systemen weiter zu verarbeiten.
# Ich verwende das Tool dazu, um nach einem Klingelsignal mittels Virtuellem Ausgang das letzt Bild zu kopieren und unter einem
# generischen Pfad der Fritz!Box bereitzustellen. In Verbindung mit einem anschließend ausgelösten Text2SIP-Call sehe ich dann
# das letzte Bild auf dem Fritz!Fon ;)
#
# Ursprünglich habe ich das ganze mit curl implementiert, nachdem für das Backup der Bilder websocat zum Einsatz kommt, und das wesentlich
# passender ist, gibt es hier die option websocat zu verwenden, wscat musste wieder verworfen werden, Hintergrund dazu ist die fehlende
# Script-Tauglichkeit von wscat https://stackoverflow.com/questions/48912184/wscat-commands-from-script-how-to-pass/48914019
# das Script benötigt daher websocat siehe https://github.com/vi/websocat
# für den Raspberry sollte das funktionieren
#
#       cd /usr/local/bin
#       wget https://github.com/vi/websocat/releases/download/v1.9.0/websocat_linuxarm32
#       chmod 755 websocat_linuxarm32
#
# Eigentlich hatte der Hersteller ja eine SIP-Integration versprochen. Mal schauen ob die noch kommt.
#
# Autor Markus Laube, markus@laube.email
# Version: 2022-01-19 19:46 
# Licence: GNU GENERAL PUBLIC LICENSE 3 - https://github.com/markuslaube/loxone/blob/main/LICENSE
#
# echo "DEBUG: Script startet: $(date)"

# intercom="192.168.127.3:80" # Die IP und der Port der InterCom Gen.2
intercom=$(cat $LBPDATA/intercom22lox/data.json | jq .intercomip | tr -d '"')

wscatwait=0.9							 # Geschätzte Zeit für den Lauf der Funktion make_pictures -> brauchen wir bei -w vom wscat
makewait=0.5							 # Geschätzte Zeit bis die Info von der Intercom im tempfile sind -> brauchen wir beim sleep vor dem auslesen des tempfiles
# websocat="/usr/local/bin/websocat_linuxarm32"                    # wscat hat sich als unbrauchbar für den Scripteinsatz erwiesen
websocat="websocat"                    # wscat hat sich als unbrauchbar für den Scripteinsatz erwiesen
# lastpict="/opt/loxberry/webfrontend/html/tmp/lastpicture.jpg"    # -> Ja das sollte noch hinter das Auth landen
lastpict="lastpicture.jpg"                                                                 
WSDEFAULT="--websocat"						 # hier kann man den DEFAUL [--websocat,--curl] anpassen
tempfile="$(mktemp)"                                             # TempFile für Output aus WebSocket


function usage () {
echo "Usage: $0 [option] 

Options:
  -c, --curl            	      use curl for WebSocket Connection
  -w, --wscat                         use websocat for WebSocket Connection (recommended but dependent to websocat Installation)
  -h, --help                          display help for command

  "
  exit $1
}

function curl_websocket () {

curl --include \
     --no-buffer \
     --header 'Connection: Upgrade' \
     --header 'Pragma: no-cache' \
     --header 'Cache-Control: no-cache' \
     --header 'Upgrade: websocket' \
     --header 'Origin: file://' \
     --header 'Accept-Encoding: gzip, deflate' \
     --header 'Accept-Language: de' \
     --header 'Sec-WebSocket-Version: 13' \
     --header 'Sec-WebSocket-Key: 00KILL0ME0LATER0000000==' \
     --header 'Sec-WebSocket-Extensions: permessage-deflate; client_max_window_bits' \
     --header 'Sec-WebSocket-Protocol: webrtc-signaling' \
     http://${intercom}/ --output ${tempfile} 2>/dev/null  
}

function websocat_websocket () {

( echo '{"jsonrpc":"2.0","method":"info","id":0}' ; sleep ${wscatwait} ) | ${websocat} --protocol webrtc-signaling ws://${intercom} > ${tempfile}
rm -f ${tempfile}

}

function make_pictures () {
	# Kurz warten, das der Websocket sich aufbauen kann
	sleep ${makewait}
	mv ${lastpict} ${lastpict}.sic                                   # wenn das mal nötig ist können wir ein step back im Fehlerfall einbauen
	# Das Tempfile jetzt nach dem Auth-Key durchsuchen (erster Wert nach der '[' in der Zeile mit "authenticate")
	authkey="$(cat ${tempfile}| iconv -c  | grep 'authenticate' | sed 's/.*\["//g' | sed 's/",".*//g')"
	# Das Bild abholen, wir sind ja Authentifiziert :D
	curl --output ${lastpict} "http://${intercom}/jpg/image.jpg?auth=${authkey}"
	# Falls unser WebSocket von Curl war müssen wir Ihn  killen und dann das Tempfile löschen, wscat kann das selbst
	if [ "${websocket_cmd}" == "curl_websocket" ]  ; then
		# Wir brauchen den Pseudo-WebSocket nicht mehr, also machen wir jetzt das was im Key steht (KILL ME LATER) jetzt
		kill $(ps auxwwww | grep '[0]0KILL0ME0LATER0000000' | awk '{print$2}')
		# Das Tempfile kann jetzt weg
		rm -f ${tempfile}
	fi
        # echo "DEBUG: Download beendet: $(date)"
}

if [ $# -gt 1 ] ; then echo "ERROR: To many Options" ; usage 1 ; fi

if [ $# -lt 1 ] ; 
	then
		echo "no Options set; use DEFAULT [${WSDEFAULT}]" ; option="${WSDEFAULT}" ; 
	else 
		option="$1"
fi

case "${option}" in
	-c | --curl)		websocket_cmd="curl_websocket"
				;;
	-w | --websocat)	websocket_cmd="websocat_websocket"
				;;
	-h | --help)		usage 0
				;;
	*)			echo "ERROR: Option not implemented" ; usage 7 
				;;
esac

# Nachdem wscat sich bezüglich Hinterhrund-Prozesse komisch/strange/seltsam verhält Schicken wir als erstes den make_pictures in den Hintergrund, der wartet dann einfach kurz bevor er läuft
make_pictures &

# Wir Initieren ein WebSocket ohne jeden Auth und die Intercom sagt uns den aktuellen Auth-Key :Facepalm:
eval ${websocket_cmd} 

# echo "DEBUG: Script beendet: $(date)"

