![Logo](https://github.com/bladerb/intercom22lox/raw/fc83e11790a860c5de03edbe479ace656b6be588/icons/icon_256.png)

# Loxberry Plugin intercom22Lox

Dieses Loxberry Plugin greift Fotos der Loxone Intercom Version 2 ab um sie für andere Anwendungen vorzuhalten. Das Plugin kann über einen Virtuellen Ausgang aus der Loxone Config heraus aufgerufen werden. Anschließend werden die Bilder über eine URL bereitgestellt und es besteht die möglichkeit einen weitern Webhook aufzurufen um die Bild URL an andere Programme / Scripte weiterzugeben.

Das Plugin ist QuickAndDirty aus einem Beitrag des Loxforum.com entstanden.

https://www.loxforum.com/forum/hardware-zubeh%C3%B6r-sensorik/330121-loxone-intercom-gen2-webschnittstelle-um-bild-video-rauszubekommen/page3#post343007
https://www.loxforum.com/forum/hardware-zubeh%C3%B6r-sensorik/353631-warnung-loxone-intercom-gen-2-aktuell-bekannte-probleme#post356031



## Funktionsumfang

- Manuelle Bildaufname über Trigger ( http://<IP>/plugins/intercom22lox/getpicture.php )
- LoxConfig Intercom Bild an Loxberry Plugin über Virtuellen Ausgang übergeben
- Webhook via POST-/GET-Request bzw. MQTT-Broker
- Bilder Archiv für Bilder die über URL Trigger angestossen wurden
- Video aufnahme durch URL Trigger mit Angabe der Videolänge (max 120 Sekunden http://<IP>/plugins/intercom22lox/getvideo.php?s=<SEKUNDEN> )
- Videoaufnahmen mit Zeitsatempel (optional) über Trigger ( http://<IP>/plugins/intercom22lox/getpicture.php )
- Video Archiv
- Video stream Proxy ( http://<IP>/plugins/intercom22lox/mjpgproxy.php ) ohne authentifizierung

## Anwendungsfälle

- Bild / Video aufzeichnen wenn der Briefkasten über einen Sensor auslöst
- Bild / Video aufzeichnen wenn der Näherungssensor auslöst oder ein Bewegungsmelder
- Bilder der Intercom inhouse archivieren (sonst liegen sie nur auf der SD Karte in der Intercom)

## Dank

Vielen Dank an das Loxberry Forum speziell Laubi und hismastersvoice für die Informationen zum Bilderauslesen.

Folgende Librarys wurden verwendet

- https://github.com/simonwalz/php-mjpeg-proxy
- http://www.lavrsen.dk/foswiki/bin/view/Motion/MjpegFrameGrabPHP

## ChangeLog

1.3.0

- ffmpeg hinzugefügt
- Videoaufzeichnung kann über URL Trigger angestossen werden. Videolänge als Parameter.
- video von mjpgproxy.php mit ffmpeg aufnehmen und abspeichern.

## Feature Requests 

- Zeitstempel als option video / Foto
- ffmpeg mit plugin mit installieren
- update testen ob bilderarchiv bleibt
- update testen ob einstellungen bleiben
- eine Einstellmöglichkeit, wo die Bilder genau landen (z.B. auf einem externen USB-Speicher)
- evtl objekterkennung
- Bild an TV senden (Android TV)
- Timelapse Funktion jeden Tag ein Foto schießen zu bestimmter Uhrzeit
- Bild alle X Sekunden mit Bilderkennung!? javscript library?
- Bild bei Briefkaseten trigger (mehrere trigger ermöglichen)
- aktuell geht das Auslesen nur für den ersten hinterlegten Miniserver

- Bilder bei update nicht löschen
- AI erkennung bei getpicture.php 
- schauen was machen andere klingeln noch so was man übernehmen kann

