<?php
/**
 * Seit v1.5.0 stehen die Einstellungen als Reiter auf der Startseite.
 * Diese Datei leitet nur noch dorthin um, damit alte Lesezeichen und Links
 * nicht ins Leere laufen.
 */
header('Location: index.php#tab-settings');
exit;
