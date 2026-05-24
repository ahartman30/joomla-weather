<?php
/**
 * @copyright   (C) 2026 Alexander Hartmann
 * @license     GNU GPL v3 or later
 */

defined('_JEXEC') or die;

?>

<h1>1. Produkte</h1>
Die Komponente ermöglicht den Zugriff auf Dateien des DWD Opendata oder Bilder aus dem Web über eine Url.<br/>
Die Produkte und die Optionen werden in der Wetter DWD Opendata Komponente konfiguriert.
Die Dateien werden zur Optimierung für eine vorgegebene Zeitspanne lokal gespeichert, bevor im Opendata auf eine neuere
Version geprüft wird. Die Bilder aus dem Web werden ebenfalls gespeichert und nur ersetzt, wenn das Bild einen anderen Inhalt als das Bild im Cache besitzt.<br/>
Das Plugin ermöglicht das Darstellen von Bildern und Text. Bei Bildern kann zusätzlich ein
Vergrößerungs-, Verkleinerungsfaktor angegeben werden und es gibt die Möglichkeit, beim Klick auf das Bild, dieses in Originalgröße anzuzeigen.<br/><br/>
Alle Bilder, die denselben Text in der Gruppierung besitzen, können in der vergrößerten Ansicht durch Klick oder Mausrad durchgeblättert werden.<br/>
Fehlermeldungen werden nur angezeigt, wenn ein Benutzer eingeloggt ist.<br/>
Das Command <i>load</i> baut eine aktive Verbindung zum Opendata auf, um die Datei zu aktualisieren, ohne den Inhalt in den Artikel einzubinden.
Sinn des load Command ist es, z.B. viele Dateien mit einem einzigen Artikel nur in den Cache zu aktualisieren, ohne diese anzuzeigen und damit zu viel Last
über den Webserver zu erzeugen. Diese Seite kann dann z.B. über die Cron aufgerufen werden. Besser ist allerdings das Verwenden der CLI über die Cron.<br/>
Das Command <i>get</i> baut eine aktive Verbindung zum Opendata auf, um die Datei zu aktualisieren, und bindet diese in die Seite ein.<br/>
Das Command <i>show</i> bindet nur die aktuellste Datei aus dem Cache ein, ohne eine aktive Verbindung zum Opendata auzubauen.<br/><br/>

<h3>Anwendung:</h3>
<i>{opendata:product-[load,get,show] &lt;Produktname&gt;;&lt;[opt] Größe in %&gt;;&lt;[opt] Titel und Vergrößerung bei Klick&gt;;[opt] Gruppierung}</i><br/><br/>
Bsp.:<br/>
<i>{opendata:product-get Webradar_Deutschland_akt;;}</i> (Anzeige Bild in Originalgröße ohne Vergrößerung bei Klick)<br/>
<i>{opendata:product-get Euro640_502_heute;50;Bodenvorhersage Europa Heute;gruppe1}</i> (Anzeige Bild in 50% mit Vergrößerung, Titel und Weiterblättern bei Klick)<br/>
<i>{opendata:product-get Euro640_502_morgen;50;Bodenvorhersage Europa Morgen;gruppe1}</i> (Anzeige Bild in 50% mit Vergrößerung, Titel und Weiterblättern bei Klick)<br/>
<i>{opendata:product-get FPDL13_DWMZ_0;;}</i> (Anzeige Text)<br/><br/>
<i>{opendata:product-load Webradar_Deutschland_akt;;}</i> (Nur Laden des Produktes in den Cache)<br/>
<i>{opendata:product-show Webradar_Deutschland_akt;;}</i> (Nur Anzeigen des Produktes aus dem Cache)<br/>

<p></p>
<h1>2. Charts</h1>
<pre>
{opendata:chart &lt;Name Chart&gt;;&lt;Breite&gt;;&lt;Höhe&gt}
</pre>
<h3>Beispiel:</h3>
<pre>
{opendata:chart Multi_Test;600px;300px}
</pre>
Höhe und Breite können als px oder % angegeben werden.

<p></p>
<h1>3. Text</h1>
Ermöglicht den Zugriff auf Textdateien, um diese in einen Beitrag einzubinden.<br/>
Zusätzlich werden alle Sonderzeichen (außer Zeilenumbrüche) nach HTML Entitäten umgewandelt. Endet die
Datei auf .html wird der Inhalt direkt als HTML eingebunden. Sonderzeichen werden dann nicht mehr
ersetzt.
Bsp:
<pre>
{opendata:insert Unterverzeichnis/Textdatei.txt}
{opendata:insert Htmldatei.html}
</pre>

<p></p>
<h1>4. Command Line Interface</h1>
Alle vorhandenen Produkte können mittels der Joomla CLI über die Cron abgerufen werden.<br/>
Laden des gesamten Cache für Produkte:
<pre>
php ./cli/joomla.php weatheropendata:loadcache
</pre>

Laden und Überschreiben eines einzelnen Produkts:
<pre>
php ./cli/joomla.php weatheropendata:fetchProduct
php ./cli/joomla.php weatheropendata:fetchProduct --product=&lt;productname&gt;
php ./cli/joomla.php weatheropendata:loadcache --product=&lt;productname&gt;
</pre>
