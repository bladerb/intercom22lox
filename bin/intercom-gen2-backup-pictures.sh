#!/bin/bash
# Das ist der zweite (erfolgreicher) Proof of Concept um Bilder von einer Loxone intercom2 in anderen Systemen weiter zu verarbeiten.
# Ich verwende das Tool dazu, um ab und zu mal die Bilder von der Intercom wegzusichern
# Achtung wscat ist offensichtlich nicht geegnet im Hintergrund zu laufen, das Script benötigt daher websocat siehe https://github.com/vi/websocat
# für den Raspberry sollte das funktionieren
#
# 	cd /usr/local/bin
#	wget https://github.com/vi/websocat/releases/download/v1.9.0/websocat_linuxarm32
#	chmod 755 websocat_linuxarm32
#
# Autor Markus Laube, markus@laube.email
# Version: 2022-01-19 20:01
# Licence: GNU GENERAL PUBLIC LICENSE 3 - https://github.com/markuslaube/loxone/blob/main/LICENSE
#
intercom=$(cat $LBPDATA/intercom22lox/data.json | jq .intercomip | tr -d '"')
wscatwait=20                                                     # Geschätzte Zeit für den Lauf der Funktion make_pictures -> brauchen wir bei -w vom wscat
makewait=2                                                       # Geschätzte Zeit bis die Info von der Intercom im tempfile sind -> brauchen wir beim sleep in der funktion vom wscat
picturedir="$LBPHTML/intercom22lox/backup"                            # -> Backup-Destination-Pfad
#websocat="/usr/local/bin/websocat_linuxarm32"                    # wscat hat sich als unbrauchbar für den Scripteinsatz erwiesen  
websocat="websocat"                    # wscat hat sich als unbrauchbar für den Scripteinsatz erwiesen  
tempfile="$(mktemp)"                                             # TempFile für Output aus WebSocket

function make_pictures () {

        # Sicherheitshalber Verzeichnis anlenegm
        mkdir -p ${picturedir}
        # Verzeichnis betreteb
        cd ${picturedir}
        # Wir warten datauf das die Websocket-Verbindung wirklich die Pfad-Informationen eingesammelt hat, wie lange sagen wir beim Aufruf als Parameter 1
        sleep ${makewait}
        # Jetzt einmal ein bissl grep, awk und sed zusammen mit einem wget
        for image in $(cat ${tempfile} | sed 's#,#\n#g' | grep imagePath | awk -F\" '{print$4}' | sed 's#\\/#/#g' )  ; do wget -N "http://${intercom}${image}" ; done
        # um die -w Angabe und damit die "Leerlaufzeit" des Scripts zu optimieren, geben wir hier mal die Zeit aus wenn der eigentliche Download fertig ist und später dann wenn das Script beendet ist
        # echo "DEBUG: Download beendet: $(date)"
}

# Also ersteinmal die wget Funktion starten und in den Hintergrund schieben, man könnte auch den websocat in den Hintergrund schieben, was jetzt sinnvoller ... 
make_pictures &

# und jetzt die wscat Verbindung starten, -w  sorgt dafür das wir x Sekunden nach dem Command-Versand warten, das sollte angepasst werden, bei mir reicht das akutell
# wscat -c ${intercom} -s webrtc-signaling --slash -P -w ${wscatwait} -x '{"jsonrpc":"2.0","method":"getLastActivities","id":1,"params":[0,100]}' > ${tempfile}

# wir verwenden websocat, weil wscat nicht hintergrund-tauglich ist :-/ .... https://stackoverflow.com/questions/48912184/wscat-commands-from-script-how-to-pass/48914019
( echo '{"jsonrpc":"2.0","method":"getLastActivities","id":1,"params":[0,100]}' ; sleep ${wscatwait} ) | ${websocat} --protocol webrtc-signaling ws://${intercom} > ${tempfile}

# Die Verbindung mit wscat ist jetzt eh weg, daher löschen wir jetzt auch das tempfile
rm ${tempfile}

# Nochmal Uhrzeit -> die Differenz in Sekunden kann man dann vom in der Variable wait runter nehmen, bitte Puffer lassen
# echo "DEBUG: Script beendet: $(date)"
