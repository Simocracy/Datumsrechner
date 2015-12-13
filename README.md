# Datumsrechner
Verschiedene Implementationen des Simocracy-Datumsrechner in PHP

## WikiExtension
MediaWiki-Extension für den Datumsrechner.

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

