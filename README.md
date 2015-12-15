# Datumsrechner
Verschiedene Implementationen des Simocracy-Datumsrechner in PHP

## WikiExtension
MediaWiki-Extension für den Datumsrechner. Basiert auf der Webpage und enthält zudem Umrechnungsfunktionen dieser. Nutzung dieser siehe unten.

### Nutzung

Rechnet aktuelle RL-Zeit um in SY-Zeit:
```
Variante 1: <drechner />
Variante 2: <drechner></drechner> 
```


Rechnet beliebiges RL-Datum im angegebenen Format um in SY-Zeit:
```
<drechner>YYYY-MM-DD HH:MM</drechner>
<drechner>YYYY-MM-DD</drechner>
<drechner>DD.MM.YYYY</drechner> 
```


Ausgabe der Eingabe als Tooltip:
```
<drechner eing="j" />
```


Rechnet SY-Datum in RL-Datum um:
```
<drechner dir="rl">YYYY-MM-DD</drechner>
```


Ausgabe ohne Uhrzeit:
```
<drechner day="j">YYYY-MM-DD</drechner>
```


Nutzungsmögilchkeit mittels Parserfunktion:
```
{{#tag:drechner|YYYY-MM-DD}}
{{#tag:drechner|YYYY-MM-DD|dir=rl|day=j}} 
```


### Changelog
Version 4.0:
* Implementierung als Wiki-Extension mittels Tag.

Version 4.0.2:
* Bugfix: Korrekte Anzahl von Nullen bei Ausgabe von Einstelligen Minutenangaben.

Version 4.1:
* Neu: Angabe im Datumsformat DD.MM.YYYY.
* Neu: Ausgabe nur mit Datum ohne Uhrzeit möglich.


Volle Dokumentation siehe http://simocracy.de/Hilfe:Datumsrechner

## Webpage

Stellt eine HTML-Seite zur Verfügung, auf der das Datum umgerechnet werden kann.

#### rlSy($rlTag, $rlMon, $rlJahr, $rlStu, $rlMin)
Rechnet RL-Datum in ein SY-Datum um. Angabe Tag, Monat, Jahr, Stunde und Minute als int-Werte. Es muss zudem vor Aufruf der Funktion geprüft werden, dass die jeweiligen Angaben valide sind, da die Funktion lediglich umrechnet und nicht auf korrekte Eingaben prüft. Müsste rein theoretisch auch mit Angaben funktionieren, die vor Festlegung der Simocracy-Zeitrechnung (Oktober 2008) liegen, jedoch nicht getestet.

#### syRl($syTag, $syMon, $syJahr, $syStu, $syMin)
Rechnet SY-Datum in ein RL-Datum um. Angabe Tag, Monat, Jahr, Stunde und Minute als int-Werte. Es muss zudem vor Aufruf der Funktion geprüft werden, dass die jeweiligen Angaben valide sind, da die Funktion lediglich umrechnet und nicht auf korrekte Eingaben prüft. Müsste rein theoretisch auch mit Angaben funktionieren, die vor Festlegung der Simocracy-Zeitrechnung (2020) liegen, jedoch nicht getestet.

#### schaltjahrFeb($jahr)
Ermittelt ob das angegebene Jahr ein Schaltjahr (nach gregorianischem Kalender, gültige Berechnung seit 1582).

#### quartalErm($mon)
Ermittelt das Quartal, in dem der angegebene Monat (Nummer) liegt. Bei ungültiger Eingabe wird 0 zurückgegeben.
