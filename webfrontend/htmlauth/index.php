<?php
/**
 * intercom22Lox - Bedienoberflaeche
 *
 * Eine Seite mit fuenf Reitern statt fuenf Einzelseiten:
 * Einstellungen | Einbindung in Loxone | Archiv | Test | Protokoll
 *
 * Alle Variablen tragen das Praefix ic_, weil LBWeb::lbheader() eigene
 * globale Variablen setzt und es sonst zu Namenskollisionen kommt.
 *
 * (c) intercom22Lox Plugin Authors - MIT-Lizenz
 */

require_once "config.php";

$L = LBSystem::readlanguage("language.ini");

$ic_titel   = "intercom22Lox";
$ic_hilfe   = "https://github.com/bladerb/intercom22lox/";
$ic_hilfetp = "help.html";

require_once "menu.php";
$navbar[1]['active'] = True;
LBWeb::lbheader($ic_titel, $ic_hilfe, $ic_hilfetp);

$ic_datei   = LBPCONFIGDIR . '/data.json';
$ic_host    = $_SERVER['HTTP_HOST'];
$ic_plugin  = getenv('LBPPLUGINDIR') ?: 'intercom22lox';
$ic_log     = '';
$ic_meldung = '';

function ic_e($wert) { return htmlspecialchars((string) $wert, ENT_QUOTES, 'UTF-8'); }

/** Felder, die gespeichert werden duerfen. Alles andere wird verworfen. */
$ic_felder_text = array('intercomip', 'storage_path', 'timelapse_time', 'tv_ip', 'tv_port',
                        'ai_url', 'ai_minconf', 'cleanup_days', 'cleanup_count',
                        'mqtt_server', 'mqtt_port', 'mqtt_user', 'mqtt_password',
                        'webhook1', 'webhook2', 'webhook3', 'webhook4',
                        'videowebhook1', 'videowebhook2');
$ic_felder_haken = array('timestamp_image', 'timestamp_video', 'timelapse_enable',
                         'timelapse_video', 'tv_enable', 'ai_enable');

$ic_cfg = file_exists($ic_datei) ? (json_decode(file_get_contents($ic_datei), true) ?: array()) : array();
if (!is_array($ic_cfg)) { $ic_cfg = array(); }

if (isset($_POST['speichern'])) {
    $ic_neu = $ic_cfg;
    foreach ($ic_felder_text as $ic_k) {
        // Nur Steuerzeichen und Anfuehrungszeichen entfernen - niemals Doppelpunkt,
        // Schraegstrich oder Punkt, sonst wird aus einer eingefuegten Adresse Buchstabensalat.
        $ic_w = isset($_POST[$ic_k]) ? (string) $_POST[$ic_k] : '';
        $ic_neu[$ic_k] = trim(preg_replace('/[\x00-\x1F\x7F"\']/', '', $ic_w));
    }
    foreach ($ic_felder_haken as $ic_k) {
        $ic_neu[$ic_k] = isset($_POST[$ic_k]) ? 'on' : '';
    }
    $ic_neu['mqtt_enable']   = isset($_POST['mqtt_enable']) ? '1' : '0';
    $ic_neu['mqtt_uselocal'] = isset($_POST['mqtt_uselocal']) ? '1' : '0';

    if (file_put_contents($ic_datei, json_encode($ic_neu, JSON_PRETTY_PRINT)) !== false) {
        @chmod($ic_datei, 0600);
        $ic_cfg = $ic_neu;
        $ic_meldung = 'Die Einstellungen wurden gespeichert.';
    } else {
        $ic_meldung = 'FEHLER: Die Einstellungen konnten nicht geschrieben werden.';
    }
}

// Voreinstellungen fuer noch nie gespeicherte Felder
$ic_cfg += array('intercomip' => '', 'storage_path' => '', 'timelapse_time' => '12:00',
    'tv_ip' => '', 'tv_port' => '', 'ai_url' => '', 'ai_minconf' => '50',
    'cleanup_days' => '90', 'cleanup_count' => '', 'mqtt_enable' => '0',
    'mqtt_uselocal' => '1', 'mqtt_server' => '', 'mqtt_port' => '1883',
    'mqtt_user' => '', 'mqtt_password' => '', 'webhook1' => '', 'webhook2' => '',
    'webhook3' => '', 'webhook4' => '', 'videowebhook1' => '', 'videowebhook2' => '',
    'timestamp_image' => '', 'timestamp_video' => '', 'timelapse_enable' => '',
    'timelapse_video' => '', 'tv_enable' => '', 'ai_enable' => '');

function ic_haken($cfg, $k) { return (isset($cfg[$k]) && $cfg[$k] === 'on') ? ' checked' : ''; }

// Archivstand ermitteln
$ic_bilder = glob($folder_img_archive . '*.jpg') ?: array();
$ic_videos = glob($folder_video_archive . '*') ?: array();
rsort($ic_bilder);
rsort($ic_videos);

// Protokoll
$ic_logdatei = '';
$ic_kandidaten = array('/opt/loxberry/log/plugins/' . $ic_plugin . '/intercom22lox.log');
if (defined('LBPLOGDIR')) { array_unshift($ic_kandidaten, LBPLOGDIR . '/intercom22lox.log'); }
foreach ($ic_kandidaten as $ic_p) {
    if (@is_file($ic_p)) { $ic_logdatei = $ic_p; break; }
}
if ($ic_logdatei !== '') {
    $ic_zeilen = @file($ic_logdatei);
    if (is_array($ic_zeilen)) { $ic_log = implode('', array_slice($ic_zeilen, -200)); }
}
?>
<style>
.icw, .icw * { text-shadow: none !important; }
.icw { max-width: 1100px; }
.icw h1 { color: #6dac20; font-size: 1.5em; margin: 0 0 4px; }
.icw h2 { color: #6dac20; margin: 18px 0 6px; font-size: 1.15em; }
.icw p, .icw li { line-height: 1.5; }
.icw .ic-reiter { display: flex; flex-wrap: wrap; gap: 4px; border-bottom: 3px solid #6dac20; margin: 14px 0 0; }
.icw .ic-reiter div { padding: 9px 16px; background: #eee; border-radius: 8px 8px 0 0; cursor: pointer; font-weight: 600; color: #444; }
.icw .ic-reiter div.aktiv { background: #6dac20; color: #fff; }
.icw .ic-seite { display: none; padding: 14px 2px; }
.icw .ic-seite.aktiv { display: block; }
.icw label { display: block; font-weight: 600; margin: 12px 0 3px; }
.icw input[type=text], .icw input[type=number], .icw input[type=password] {
    width: 100%; max-width: 520px; padding: 7px 9px; border: 1px solid #bbb; border-radius: 6px; font-size: 1em; }
.icw .ic-klein { font-size: 0.88em; color: #666; margin: 3px 0 0; max-width: 720px; }
.icw .ic-mono { font-family: monospace; background: #f4f4f4; padding: 1px 5px; border-radius: 4px; }
.icw .ic-btn { background: #6dac20; color: #fff !important; border: 0; border-radius: 8px; padding: 9px 18px;
    cursor: pointer; text-decoration: none; font-size: 0.95em; }
.icw .ic-hinweis { border-left: 5px solid #6dac20; background: #f4faee; padding: 10px 14px; margin: 12px 0; border-radius: 0 8px 8px 0; }
.icw .ic-warn { border-left-color: #e0620d; background: #fff5ee; }
.icw .ic-schritt { border: 1px solid #ddd; border-radius: 10px; padding: 12px 14px; margin: 10px 0; }
.icw table { border-collapse: collapse; width: 100%; max-width: 900px; margin: 8px 0; }
.icw th, .icw td { border: 1px solid #ddd; padding: 6px 9px; text-align: left; font-size: 0.93em; }
.icw th { background: #f2f2f2; }
.icw pre { background: #f6f6f6; border: 1px solid #ddd; border-radius: 8px; padding: 10px;
    max-height: 460px; overflow: auto; font-size: 0.85em; }

/* Hausstandard: Kachel-Raster im Reiter Test */
.icw .ic-h3 { color: #4f7d17; font-size: 1.0em; font-weight: 700; margin: 16px 0 2px; }
.icw .ic-knopfreihe { display: flex; flex-wrap: wrap; gap: 10px; margin: 10px 0 4px; align-items: stretch; }
.icw .ic-knopfreihe form { margin: 0; display: flex; }
.icw .ic-knopfreihe .ic-btn { flex: 0 0 auto; min-width: 250px; text-align: center;
    display: inline-flex; align-items: center; justify-content: center; line-height: 1.25; }
.icw .ic-legende { display: flex; flex-wrap: wrap; gap: 14px; margin: 10px 0 2px; font-size: 0.86em; color: #555; }
.icw .ic-legende span { display: inline-flex; align-items: center; gap: 6px; }
.icw .ic-punkt { width: 13px; height: 13px; border-radius: 3px; display: inline-block; }
.icw .ic-btn.ic-b-lesen   { background: #6dac20; }
.icw .ic-btn.ic-b-technik { background: #546e7a; }
.icw .ic-btn.ic-b-aktion  { background: #e0620d; }
.icw .ic-punkt.ic-b-lesen   { background: #6dac20; }
.icw .ic-punkt.ic-b-technik { background: #546e7a; }
.icw .ic-punkt.ic-b-aktion  { background: #e0620d; }
.icw .ic-gal { display: flex; flex-wrap: wrap; gap: 10px; }
.icw .ic-gal figure { margin: 0; width: 220px; }
.icw .ic-gal img { width: 100%; border-radius: 8px; border: 1px solid #ddd; }
.icw .ic-gal figcaption { font-size: 0.8em; color: #666; word-break: break-all; }
</style>

<div class="icw">
<h1>intercom22Lox</h1>
<p>Holt das Kamerabild der Loxone Intercom auf den LoxBerry, legt es im Archiv ab und stellt es
Loxone und anderen Programmen bereit &mdash; ohne dass Benutzername und Passwort der Intercom
irgendwo sonst eingetragen werden m&uuml;ssen.</p>

<?php if ($ic_meldung !== '') { ?>
<div class="ic-hinweis<?= strpos($ic_meldung, 'FEHLER') === 0 ? ' ic-warn' : '' ?>"><?= ic_e($ic_meldung) ?></div>
<?php } ?>
<?php if (trim((string) $ic_cfg['intercomip']) === '') { ?>
<div class="ic-hinweis ic-warn"><b>Noch nicht eingerichtet.</b> Tragen Sie im Reiter
<b>Einstellungen</b> die Adresse der Intercom ein &mdash; ohne sie kann kein Bild geholt werden.</div>
<?php } ?>

<div class="ic-reiter">
    <div class="aktiv" data-seite="tab-settings">Einstellungen</div>
    <div data-seite="tab-loxone">Einbindung in Loxone</div>
    <div data-seite="tab-archiv">Archiv</div>
    <div data-seite="tab-test">Test</div>
    <div data-seite="tab-log">Protokoll</div>
</div>

<!-- ===================== Einstellungen ===================== -->
<div class="ic-seite aktiv" id="tab-settings">
<form method="post">

<h2>Intercom</h2>
<label>Adresse der Intercom</label>
<input type="text" data-role="none" name="intercomip" value="<?= ic_e($ic_cfg['intercomip']) ?>" placeholder="192.168.1.50">
<p class="ic-klein">IP-Adresse der Intercom, bei Bedarf mit Port (<span class="ic-mono">192.168.1.50:8080</span>).
Benutzername und Passwort holt sich das Plugin automatisch aus den Miniserver-Daten des LoxBerry &mdash;
Sie m&uuml;ssen sie hier nicht eintragen.</p>

<h2>Speicherort</h2>
<label>Eigener Speicherpfad (optional)</label>
<input type="text" data-role="none" name="storage_path" value="<?= ic_e($ic_cfg['storage_path']) ?>" placeholder="/media/usbstick">
<p class="ic-klein">Leer lassen, dann liegt alles auf der SD-Karte des LoxBerry. Wer viele Bilder und
Videos aufbewahrt, sollte hier eine USB-Festplatte angeben &mdash; das schont die SD-Karte.
Vorhandene Aufnahmen werden beim ersten Speichern einmalig mit umgezogen.</p>

<h2>Aufbewahrung</h2>
<label>Aufnahmen l&ouml;schen nach (Tagen)</label>
<input type="number" data-role="none" name="cleanup_days" min="0" max="3650" value="<?= ic_e($ic_cfg['cleanup_days']) ?>">
<p class="ic-klein">0 bedeutet: nie automatisch l&ouml;schen. Voreingestellt sind 90 Tage.</p>
<label>H&ouml;chstzahl aufbewahrter Aufnahmen (optional)</label>
<input type="number" data-role="none" name="cleanup_count" min="0" value="<?= ic_e($ic_cfg['cleanup_count']) ?>">
<p class="ic-klein">Zus&auml;tzliche Obergrenze. Sind mehr Dateien vorhanden, werden die &auml;ltesten
entfernt &mdash; auch wenn sie noch nicht alt genug sind.</p>

<h2>Zeitstempel im Bild</h2>
<label><input type="checkbox" data-role="none" name="timestamp_image"<?= ic_haken($ic_cfg, 'timestamp_image') ?>> Datum und Uhrzeit in archivierte Bilder schreiben</label>
<label><input type="checkbox" data-role="none" name="timestamp_video"<?= ic_haken($ic_cfg, 'timestamp_video') ?>> Datum und Uhrzeit in Videos schreiben</label>
<p class="ic-klein">Der Stempel wird links oben ins Bild gesetzt. Daf&uuml;r wird das PHP-Modul
<span class="ic-mono">php-gd</span> ben&ouml;tigt; fehlt es, bleibt das Bild einfach ohne Stempel.</p>

<h2>Zeitraffer</h2>
<label><input type="checkbox" data-role="none" name="timelapse_enable"<?= ic_haken($ic_cfg, 'timelapse_enable') ?>> T&auml;glich ein Zeitrafferbild aufnehmen</label>
<label>Uhrzeit</label>
<input type="text" data-role="none" name="timelapse_time" value="<?= ic_e($ic_cfg['timelapse_time']) ?>" placeholder="12:00">
<label><input type="checkbox" data-role="none" name="timelapse_video"<?= ic_haken($ic_cfg, 'timelapse_video') ?>> Nach jeder Aufnahme ein Video aus allen Bildern erzeugen</label>
<p class="ic-klein">Ergibt &uuml;ber Wochen einen Film, der den Jahreslauf vor der Haust&uuml;r zeigt.
F&uuml;r das Video wird <span class="ic-mono">ffmpeg</span> ben&ouml;tigt
(nachinstallieren mit <span class="ic-mono">sudo apt-get install -y ffmpeg</span>).</p>

<h2>Bild auf einen Bildschirm schicken</h2>
<label><input type="checkbox" data-role="none" name="tv_enable"<?= ic_haken($ic_cfg, 'tv_enable') ?>> Beim Klingeln das Bild an ein Anzeigeger&auml;t senden</label>
<label>Adresse des Ger&auml;ts</label>
<input type="text" data-role="none" name="tv_ip" value="<?= ic_e($ic_cfg['tv_ip']) ?>">
<label>Port</label>
<input type="text" data-role="none" name="tv_port" value="<?= ic_e($ic_cfg['tv_port']) ?>">
<p class="ic-klein">Gedacht f&uuml;r Ger&auml;te, die Bilder per HTTP entgegennehmen &mdash; etwa ein
Fernseher oder Tablet mit passender Anzeige-Software. Bleiben die Felder leer, passiert nichts.</p>

<h2>Objekterkennung (optional)</h2>
<label><input type="checkbox" data-role="none" name="ai_enable"<?= ic_haken($ic_cfg, 'ai_enable') ?>> Erkennen, was auf dem Bild zu sehen ist</label>
<label>Adresse des Erkennungsdienstes</label>
<input type="text" data-role="none" name="ai_url" value="<?= ic_e($ic_cfg['ai_url']) ?>" placeholder="http://192.168.1.60:32168/v1/vision/detection">
<label>Mindestsicherheit in Prozent</label>
<input type="number" data-role="none" name="ai_minconf" min="1" max="99" value="<?= ic_e($ic_cfg['ai_minconf']) ?>">
<p class="ic-klein">Das Bild wird an einen selbst betriebenen Dienst geschickt (CodeProject.AI oder
DeepStack), der zur&uuml;ckmeldet, ob darauf eine Person, ein Auto oder ein Paket zu sehen ist.
Damit kann Loxone unterscheiden, ob wirklich jemand vor der T&uuml;r steht oder nur eine Katze
vorbeigelaufen ist. Das Bild verl&auml;sst das Heimnetz dabei nicht.</p>

<h2>MQTT</h2>
<label><input type="checkbox" data-role="none" name="mqtt_enable"<?= ((string) $ic_cfg['mqtt_enable'] === '1') ? ' checked' : '' ?>> Meldungen per MQTT verschicken</label>
<label><input type="checkbox" data-role="none" name="mqtt_uselocal"<?= ((string) $ic_cfg['mqtt_uselocal'] !== '0') ? ' checked' : '' ?>> Den MQTT-Dienst des LoxBerry verwenden (empfohlen)</label>
<p class="ic-klein">Der LoxBerry bringt einen eigenen MQTT-Dienst mit
(System-Einstellungen &rarr; MQTT Gateway). Ist der Haken gesetzt, holt sich das Plugin Adresse,
Benutzer und Passwort automatisch von dort. Sie m&uuml;ssen unten dann nichts eintragen &mdash; und
vor allem steht das Passwort nur an einer einzigen Stelle. Nur wer einen eigenen Broker betreibt,
nimmt den Haken heraus und f&uuml;llt die folgenden Felder aus.</p>
<label>Eigener Broker &mdash; Adresse</label>
<input type="text" data-role="none" name="mqtt_server" value="<?= ic_e($ic_cfg['mqtt_server']) ?>">
<label>Port</label>
<input type="text" data-role="none" name="mqtt_port" value="<?= ic_e($ic_cfg['mqtt_port']) ?>">
<label>Benutzer</label>
<input type="text" data-role="none" name="mqtt_user" value="<?= ic_e($ic_cfg['mqtt_user']) ?>">
<label>Passwort</label>
<input type="password" data-role="none" name="mqtt_password" value="<?= ic_e($ic_cfg['mqtt_password']) ?>">
<table>
<tr><th>Thema</th><th>Wird gesendet</th></tr>
<tr><td><span class="ic-mono">intercom22lox</span></td><td>nach jedem Bild</td></tr>
<tr><td><span class="ic-mono">intercom22loxvideo</span></td><td>nach jedem Video</td></tr>
<tr><td><span class="ic-mono">intercom22lox/trigger/NAME</span></td><td>bei Aufruf mit <span class="ic-mono">?trigger=NAME</span></td></tr>
<tr><td><span class="ic-mono">intercom22lox/ai</span></td><td>nur bei eingeschalteter Objekterkennung</td></tr>
</table>

<h2>Webhooks</h2>
<p class="ic-klein">Ein Webhook ist eine Adresse, die das Plugin von sich aus aufruft, sobald etwas
passiert ist &mdash; so erf&auml;hrt ein anderes Programm davon, ohne nachfragen zu m&uuml;ssen.
Alle Felder sind freiwillig; leere Felder werden &uuml;bersprungen.</p>
<label>Nach einem Bild &mdash; als POST mit JSON</label>
<input type="text" data-role="none" name="webhook1" value="<?= ic_e($ic_cfg['webhook1']) ?>">
<label>Nach einem Bild &mdash; Bildadresse als Parameter angeh&auml;ngt</label>
<input type="text" data-role="none" name="webhook2" value="<?= ic_e($ic_cfg['webhook2']) ?>">
<label>Nach einem Bild &mdash; weiterer Empf&auml;nger (POST)</label>
<input type="text" data-role="none" name="webhook3" value="<?= ic_e($ic_cfg['webhook3']) ?>">
<label>Nach einem Bild &mdash; weiterer Empf&auml;nger (Parameter)</label>
<input type="text" data-role="none" name="webhook4" value="<?= ic_e($ic_cfg['webhook4']) ?>">
<label>Nach einem Video &mdash; als POST mit JSON</label>
<input type="text" data-role="none" name="videowebhook1" value="<?= ic_e($ic_cfg['videowebhook1']) ?>">
<label>Nach einem Video &mdash; Videoadresse als Parameter angeh&auml;ngt</label>
<input type="text" data-role="none" name="videowebhook2" value="<?= ic_e($ic_cfg['videowebhook2']) ?>">

<div style="margin-top:16px;"><button data-role="none" class="ic-btn" type="submit" name="speichern" value="1">Speichern</button></div>
</form>
</div>

<!-- ===================== Einbindung in Loxone ===================== -->
<div class="ic-seite" id="tab-loxone">
<h2>So kommt das Bild nach Loxone</h2>
<p>Loxone ruft eine Adresse auf dem LoxBerry auf; der LoxBerry holt daraufhin das Bild von der
Intercom und legt es ab. Alles, was Loxone dazu braucht, ist ein <b>virtueller Ausgang</b>.</p>

<div class="ic-schritt"><b>Schritt 1: Virtuellen Ausgang anlegen</b><br>
In Loxone Config einen virtuellen Ausgang mit dieser Adresse anlegen:<br>
<span class="ic-mono">http://<?= ic_e($ic_host) ?>/plugins/<?= ic_e($ic_plugin) ?>/getpicture.php</span>
<p class="ic-klein"><b>Wichtig:</b> Zwischen dem Klingeln und dem Aufruf sollten etwa
<b>3 Sekunden</b> liegen. Vorher liefert die Intercom noch kein brauchbares Bild. In Loxone Config
setzt man daf&uuml;r einen Einschaltverz&ouml;gerungs-Baustein davor.</p>
</div>

<div class="ic-schritt"><b>Schritt 2: Bild anzeigen</b><br>
Das jeweils neueste Bild liegt immer unter derselben Adresse &mdash; verwendbar in Loxone, in der
App oder als Anhang einer Benachrichtigung:<br>
<span class="ic-mono">http://<?= ic_e($ic_host) ?>/plugins/<?= ic_e($ic_plugin) ?>/lastpicture.jpg</span>
</div>

<div class="ic-schritt"><b>Schritt 3: Unterscheiden, wer ausgel&ouml;st hat</b><br>
H&auml;ngt man <span class="ic-mono">?trigger=NAME</span> an, wandert der Name in den Dateinamen und
wird als eigenes MQTT-Thema gemeldet. So l&auml;sst sich sp&auml;ter erkennen, ob geklingelt wurde
oder der Bewegungsmelder ausgel&ouml;st hat:<br>
<span class="ic-mono">…/getpicture.php?trigger=klingel</span><br>
<span class="ic-mono">…/getpicture.php?trigger=bewegung</span>
</div>

<div class="ic-schritt"><b>Schritt 4: Video statt Einzelbild</b><br>
<span class="ic-mono">http://<?= ic_e($ic_host) ?>/plugins/<?= ic_e($ic_plugin) ?>/getvideo.php?time=15</span>
<p class="ic-klein">Nimmt 15 Sekunden auf (h&ouml;chstens 120). Die Antwort kommt sofort und
enth&auml;lt die Adresse des Videos &mdash; herunterladen l&auml;sst es sich aber erst, wenn die
Aufnahmezeit vorbei ist. Praktisch am Briefkasten: eine kurze Sequenz zeigt, wer die Post gebracht hat.</p>
</div>

<div class="ic-schritt"><b>Schritt 5: Livebild ohne Passwort</b><br>
<span class="ic-mono">http://<?= ic_e($ic_host) ?>/plugins/<?= ic_e($ic_plugin) ?>/mjpgproxy.php</span>
<p class="ic-klein">Der LoxBerry meldet sich bei der Intercom an und reicht den Bilderstrom weiter.
Diese Adresse kann jedes Programm im Heimnetz &ouml;ffnen &mdash; ohne Benutzername und Passwort.
Genau deshalb sollte sie nicht ins Internet weitergeleitet werden.</p>
</div>
</div>

<!-- ===================== Archiv ===================== -->
<div class="ic-seite" id="tab-archiv">
<h2>Gespeicherte Aufnahmen</h2>
<p>Gespeichert sind derzeit <b><?= count($ic_bilder) ?></b> Bilder und
<b><?= count($ic_videos) ?></b> Videos.
Aufbewahrt wird <?= ((int) $ic_cfg['cleanup_days'] === 0) ? 'unbegrenzt' : ((int) $ic_cfg['cleanup_days']) . ' Tage lang' ?>.</p>

<div class="ic-knopfreihe">
<a class="ic-btn ic-b-lesen" href="live.php">Livebild ansehen</a>
<a class="ic-btn ic-b-lesen" href="archive.php">Bilder-Archiv &ouml;ffnen</a>
<a class="ic-btn ic-b-lesen" href="videoarchive.php">Video-Archiv &ouml;ffnen</a>
</div>

<?php if ($ic_bilder) { ?>
<h2>Die neuesten Bilder</h2>
<div class="ic-gal">
<?php foreach (array_slice($ic_bilder, 0, 8) as $ic_f) { $ic_n = basename($ic_f); ?>
<figure>
    <img src="/legacy/intercom22lox_data/img_archive/<?= rawurlencode($ic_n) ?>" alt="">
    <figcaption><?= ic_e($ic_n) ?></figcaption>
</figure>
<?php } ?>
</div>
<?php } else { ?>
<div class="ic-hinweis">Noch keine Aufnahmen vorhanden. Im Reiter <b>Test</b> l&auml;sst sich sofort eine ausl&ouml;sen.</div>
<?php } ?>
</div>

<!-- ===================== Test ===================== -->
<div class="ic-seite" id="tab-test">
<h2>Test</h2>
<div class="ic-legende">
<span><i class="ic-punkt ic-b-lesen"></i> Ansehen &mdash; fragt nur ab, ver&auml;ndert nichts</span>
<span><i class="ic-punkt ic-b-technik"></i> Technische Auskunft &mdash; f&uuml;r die Fehlersuche</span>
<span><i class="ic-punkt ic-b-aktion"></i> L&ouml;st etwas aus &mdash; nimmt auf oder verschickt</span>
</div>

<h3 class="ic-h3">Ansehen</h3>
<div class="ic-knopfreihe">
<a class="ic-btn ic-b-lesen" href="/plugins/<?= ic_e($ic_plugin) ?>/lastpicture.jpg" target="_blank">Letztes Bild &ouml;ffnen</a>
<a class="ic-btn ic-b-lesen" href="/plugins/<?= ic_e($ic_plugin) ?>/mjpgproxy.php" target="_blank">Livebild &ouml;ffnen</a>
</div>

<h3 class="ic-h3">Technische Auskunft</h3>
<div class="ic-knopfreihe">
<a class="ic-btn ic-b-technik" href="/plugins/<?= ic_e($ic_plugin) ?>/getpicture.php?hook=false" target="_blank">Bildabruf pr&uuml;fen (JSON)</a>
</div>
<p class="ic-klein">Holt ein Bild und zeigt die Antwort im Klartext &mdash; einschliesslich
Fehlermeldung, falls die Intercom nicht antwortet. Mit <span class="ic-mono">?hook=false</span>
wird das Bild <b>nicht</b> ins Archiv gelegt.</p>

<h3 class="ic-h3">L&ouml;st etwas aus</h3>
<div class="ic-knopfreihe">
<a class="ic-btn ic-b-aktion" href="/plugins/<?= ic_e($ic_plugin) ?>/getpicture.php?trigger=test" target="_blank">Jetzt ein Bild aufnehmen</a>
<a class="ic-btn ic-b-aktion" href="/plugins/<?= ic_e($ic_plugin) ?>/getvideo.php?time=10" target="_blank">10 Sekunden Video aufnehmen</a>
<a class="ic-btn ic-b-aktion" href="/plugins/<?= ic_e($ic_plugin) ?>/timelapse.php" target="_blank">Zeitrafferbild aufnehmen</a>
<a class="ic-btn ic-b-aktion" href="/plugins/<?= ic_e($ic_plugin) ?>/cleanup.php" target="_blank">Alte Aufnahmen jetzt aufr&auml;umen</a>
</div>
</div>

<!-- ===================== Protokoll ===================== -->
<div class="ic-seite" id="tab-log">
<h2>Protokoll</h2>
<?php if ($ic_log !== '') { ?>
<p class="ic-klein">Die letzten 200 Zeilen aus <span class="ic-mono"><?= ic_e($ic_logdatei) ?></span>.</p>
<pre><?= ic_e($ic_log) ?></pre>
<?php } else { ?>
<div class="ic-hinweis">Es liegt noch kein Protokoll vor. Sobald das Plugin zum ersten Mal ein Bild
geholt hat, erscheinen hier die Eintr&auml;ge.</div>
<?php } ?>
</div>

</div>

<script>
(function () {
    var reiter = document.querySelectorAll('.icw .ic-reiter div');
    for (var i = 0; i < reiter.length; i++) {
        reiter[i].addEventListener('click', function () {
            var ziel = this.getAttribute('data-seite');
            var alle = document.querySelectorAll('.icw .ic-reiter div');
            for (var j = 0; j < alle.length; j++) { alle[j].classList.remove('aktiv'); }
            this.classList.add('aktiv');
            var seiten = document.querySelectorAll('.icw .ic-seite');
            for (var k = 0; k < seiten.length; k++) { seiten[k].classList.remove('aktiv'); }
            var s = document.getElementById(ziel);
            if (s) { s.classList.add('aktiv'); }
        });
    }
})();
</script>

<?php
LBWeb::lbfooter();
?>
