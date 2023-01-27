![Logo](https://github.com/bladerb/intercom22lox/raw/fc83e11790a860c5de03edbe479ace656b6be588/icons/icon_256.png)

# Loxberry Plugin intercom22Lox

Dieses Loxberry Plugin greift Fotos der Loxone Intercom Version 2 ab um sie für andere Anwendungen vorzuhalten. Das Plugin kann über einen Virtuellen Ausgang aus der Loxone Config heraus aufgerufen werden. Anschließend werden die Bilder über eine URL bereitgestellt und es besteht die möglichkeit einen weitern Webhook aufzurufen um die Bild URL an andere Programme / Scripte weiterzugeben.

Das Plugin ist QuickAndDirty aus einem Beitrag des Loxforum.com entstanden.

https://www.loxforum.com/forum/hardware-zubeh%C3%B6r-sensorik/330121-loxone-intercom-gen2-webschnittstelle-um-bild-video-rauszubekommen/page3#post343007
https://www.loxforum.com/forum/hardware-zubeh%C3%B6r-sensorik/353631-warnung-loxone-intercom-gen-2-aktuell-bekannte-probleme#post356031



## Funktionsumfang

- LoxConfig Intercom Bild an Loxberry Plugin über Virtuellen Ausgang übergeben
- Webhook via POST-/GET-Request bzw. MQTT-Broker
- Backupfunktion für die letzten InterCom Bilder

## Dank

Vielen Dank an das Loxberry Forum speziell Laubi und hismastersvoice für die Informationen zum Bilderauslesen.

## todo 

- Timelapse Funktion jeden Tag ein Foto schießen zu bestimmter Uhrzeit
- Bilder archiv
- Bild alle X Sekunden mit Bilderkennung!? javscript library?
- aktuell geht das Auslesen nur für den ersten hinterlegten Miniserver
- Bilder archiv Zeitstempel im html ausgeben
- Bild bei Briefkasrten trigger