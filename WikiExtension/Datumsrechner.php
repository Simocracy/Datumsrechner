<?php

/* 
 * Simocracy-Datumsrechner - Mediawiki-Extension
 * by gobo7733
 */

// Versionsinfos
$wgExtensionCredits['parserhook'][] = array(
    'path' => __FILE__,
    'name' => 'Datumsrechner',
    'author' => 'Gerald Siegert', 
    'url' => 'http://simocracy.de/Hilfe:Datumsrechner', 
    'description' => 'Fügt einen Simocracy-Datumsrechner zum Wiki hinzu.',
    'version'  => '4.1.0',
	'license-name' => "cc-by-sa",
);

$wgHooks['ParserFirstCallInit'][] = 'wfDRechnerSetup';

function wfDRechnerSetup( Parser $parser ) {
	$parser->setHook( 'drechner', 'wfDRechnerRender' );
       return true;
}

/*
 * PHP-Berechnungen
 * Gobo77 2013, 2015
 *
 * $input: Eingabe <drechner> input </drechner>
 * $args[]: Argumente <drechner dir="rl">input</drechner>
 * 
 * Agrumente:
 * dir="rl" : Richtung SY-RL
 * eing="j" : Ausgabe des Eingabedatums
 * day="j" : Ausgabe nur Zieldatum ohne Uhrzeit
 *
 * Hinweise:
 * $richtung=1 : RL->SY, Standardfall, auch wenn was anderes angegeben wurde
 * $richtung=2 : SY->RL, nur möglich wenn $input angegeben und nur wenn arg['dir']=rl
 */
function wfDRechnerRender( $input, array $args, Parser $parser, PPFrame $frame ) {

	$valid = false;
	
	// Wenn kein Datum angegeben
	if(!isset($input) || trim($input)==='')
	{
		date_default_timezone_set('Europe/Berlin');
		$timestampJetzt = time();

		$eingabeJah = date('Y', $timestampJetzt);
		$eingabeMon = date('n', $timestampJetzt);
		$eingabeTag = date('j', $timestampJetzt);
		$eingabeStu = date('G', $timestampJetzt);
		$eingabeMin = date('i', $timestampJetzt);
		$richtung = 1;
		
		$valid = true;
	}
	else
	{
		// Eingaben auslesen
		if($args['dir'] == 'rl') $richtung = 2;
		else $richtung = 1;
		
		$inputDate = DateTime::createFromFormat('Y-m-d H:i', $input);
		if($inputDate != false)
		{
			$eingabeJah = $inputDate->format('Y');
			$eingabeMon = $inputDate->format('n');
			$eingabeTag = $inputDate->format('j');
			$eingabeStu = $inputDate->format('G');
			$eingabeMin = $inputDate->format('i');
			
			$valid = true;
		}
		else
		{
			$inputDate = DateTime::createFromFormat('Y-m-d', $input);
			if($inputDate != false)
			{
				$eingabeJah = $inputDate->format('Y');
				$eingabeMon = $inputDate->format('n');
				$eingabeTag = $inputDate->format('j');
				$eingabeStu = 12;
				$eingabeMin = 00;
				
				$valid = true;
			}
			else
			{
				$inputDate = DateTime::createFromFormat('d.m.Y', $input);
				if($inputDate != false)
				{
					$eingabeJah = $inputDate->format('Y');
					$eingabeMon = $inputDate->format('n');
					$eingabeTag = $inputDate->format('j');
					$eingabeStu = 12;
					$eingabeMin = 00;
					
					$valid = true;
				}
				else
				{
					$valid = false;
				}
			}
		}
	}
	
	// Eingaben prüfen
	if($richtung != 1 && $richtung != 2) $valid = false; // Berechnungsrichtung
	if($valid) // Berechnungsbeginn
	{
		if($richtung == 1)
		{
			if($eingabeJah < 2008) $valid = false;
			if($eingabeJah == 2008 && $eingabeMon < 10) $valid = false;
		}
		elseif($richtung == 2)
		{
			if($eingabeJah < 2020) $valid = false;
		}
		else
		{
			$valid = false;
		}
	}
	/*if($valid) // Tagesangabe
	{
		switch ($eingabeMon)
		{
			case 4:
			case 6:
			case 9:
			case 11:
				if($eingabeTag > 30) $valid = false;
				break;
			case 2:
				if($eingabeTag > schaltjahrFeb($eingabeJah)) $valid = false;
				break;
		}
	}*/
	
	// Datum berechnen
	if($valid)
	{
		if($richtung == 1)
		{
			$ergebnis = rlSy($eingabeTag, $eingabeMon, $eingabeJah, $eingabeStu, $eingabeMin);
			
			$ausgansRichtung = 'RL';
			$zielRichtung = 'SY';
		}
		elseif($richtung == 2)
		{
			$ergebnis = syRl($eingabeTag, $eingabeMon, $eingabeJah, $eingabeStu, $eingabeMin);
			
			$ausgansRichtung = 'SY';
			$zielRichtung = 'RL';
		}
	}
	else
	{
		$ergebnis = null;
	}
	
	// Ausgabe formatieren
	if($ergebnis == null)
	{
		$eingabe = 'Ung&uuml;ltige Eingaben.';
		$ausgabe = 'Berechnung konnte nicht durchgef&uuml;hrt werden.';
	}
	else
	{
		if(strlen($eingabeMin) < 2) $eingabeMin = '0' . $eingabeMin;
		if(strlen($ergebnis[4]) < 2) $ergebnis[4] = '0' . $ergebnis[4];
		
		$eingabe = $eingabeTag . '.' . $eingabeMon . '.' . $eingabeJah . ' ' . $eingabeStu . ':' . $eingabeMin . ' ' . $ausgansRichtung;
		$ausgabe = $ergebnis[0] . '.' . $ergebnis[1] . '.' . $ergebnis[2] . ' ';
		if($args['day'] != 'j') $ausgabe = $ausgabe . $ergebnis[3] . ':' . $ergebnis[4] . ' ';
		$ausgabe = $ausgabe . $zielRichtung;
	}
	
	if($args['eing'] == 'j') $output = '<abbr title="' . $eingabe . '">' . $ausgabe . '</abbr>';
	else $output = $ausgabe;
	
	return $output;
}
	
	

/*
 * Funktionen übersetzt aus Java aus dem Simocracy PostWriter
 * Basierend auf altem PHP-Datumsrechner von Fluggi
 * Fluggi, Gobo77 2012-2013
 */
 
function rlSy($rlTag, $rlMon, $rlJahr, $rlStu, $rlMin){
// Allgemeines Zeug
	// RL-Schaltjahr-Pruefung mit Ausgabe der Tage im Februar
	$rlFeb = schaltjahrFeb($rlJahr);
	
	// Tage eines RL-Quartals berechnen und in Array speichern
	$tageQuartalRl = array(
		31 + $rlFeb + 31,
		30 + 31 + 30,
		31 + 31 + 30,
		31 + 30 + 31
	);
	
	// Vergangene RL-Tage im RL-Quartal berechnen und in Array speichern
	$tageVergMonQuartRl = array(
		0, // Januar
		31, // Februar
		31 + $rlFeb, // März
		0, // April
		0 + 30, // Mai
		0 + 30 + 31, // Juni
		0, // Juli
		0 + 31, // August
		0 + 31 + 31, // September
		0, // Oktober
		0 + 31, // November
		0 + 31 + 30 // Dezember
	);
	
	// Ermittlung des RL-Quartals
	$rlQuartal = quartalErm($rlMon);
	
	// Ermittlung des Zeitanteils eines Tages
	$rlZeitAnteil = (($rlStu * 60 + $rlMin) / (24 * 60));
	
	// Ermittlung der RL-Tagesnummer im RL-Quartal
	$rlTagNrQartal = $tageVergMonQuartRl[$rlMon-1] + $rlTag + $rlZeitAnteil - 1;
	
// Jahresermittlung
	// Berechnung SY-Jahr
	$syJahr = ($rlJahr - 2009) * 4 + 2020 + $rlQuartal;
	
	// SY-Schaltjahr-Pruefung mit Ausgabe der Tage im Februar
	$syFeb = schaltjahrFeb($syJahr);
	
// Tages- und Monatsermittlung
	
	// Vergangene RL-Tage im RL-Quartal berechnen und in Array speichern
	$tageVergMonQuartSy = array(
		0, // Januar
		31, // Februar
		31 + $syFeb, // März
		0, // April
		0 + 30, // Mai
		0 + 30 + 31, // Juni
		0, // Juli
		0 + 31, // August
		0 + 31 + 31, // September
		0, // Oktober
		0 + 31, // November
		0 + 31 + 30 // Dezember
	);
	
	// Tage aller Quartale berechnen und in Array speichern
	$tageQuartalGesSy = array(
		0,
		31 + $syFeb + 31,
		31 + $syFeb + 31 + 30 + 31 + 30,
		31 + $syFeb + 31 + 30 + 31 + 30 + 31 + 31 + 30
	);
	
	// Berechnung Anzahl Tage im SY-Jahr
	$tageSyJahrGes = 365 - 28 + $syFeb;
	
	// Berechnung SY-Tage pro RL-Tag
	$syTageRlTage = 1/($tageQuartalRl[$rlQuartal-1] / $tageSyJahrGes);
	
	// Berechnung SY-Tag im SY-Jahr
	$syTagSyJahrD = (($rlTagNrQartal * $syTageRlTage) + 1);
	
	// Abrunden SY-Tag im SY-Jahr
	$syTagSyJahr = floor($syTagSyJahrD);
	
	// Ermittlung SY-Monat und SY-Quartal
	$syMon = 0;
	$syQuartal = 0;
	if($syTagSyJahr > $tageQuartalGesSy[3] + $tageVergMonQuartSy[11]){
		$syMon = 12;
		$syQuartal = 4;
	}
	elseif($syTagSyJahr > $tageQuartalGesSy[3] + $tageVergMonQuartSy[10]){
		$syMon = 11;
		$syQuartal = 4;
	}
	elseif($syTagSyJahr > $tageQuartalGesSy[3] + $tageVergMonQuartSy[9]){
		$syMon = 10;
		$syQuartal = 4;
	}
	elseif($syTagSyJahr > $tageQuartalGesSy[2] + $tageVergMonQuartSy[8]){
		$syMon = 9;
		$syQuartal = 3;
	}
	elseif($syTagSyJahr > $tageQuartalGesSy[2] + $tageVergMonQuartSy[7]){
		$syMon = 8;
		$syQuartal = 3;
	}
	elseif($syTagSyJahr > $tageQuartalGesSy[2] + $tageVergMonQuartSy[6]){
		$syMon = 7;
		$syQuartal = 3;
	}
	elseif($syTagSyJahr > $tageQuartalGesSy[1] + $tageVergMonQuartSy[5]){
		$syMon = 6;
		$syQuartal = 2;
	}
	elseif($syTagSyJahr > $tageQuartalGesSy[1] + $tageVergMonQuartSy[4]){
		$syMon = 5;
		$syQuartal = 2;
	}
	elseif($syTagSyJahr > $tageQuartalGesSy[1] + $tageVergMonQuartSy[3]){
		$syMon = 4;
		$syQuartal = 2;
	}
	elseif($syTagSyJahr > $tageQuartalGesSy[0] + $tageVergMonQuartSy[2]){
		$syMon = 3;
		$syQuartal = 1;
	}
	elseif($syTagSyJahr > $tageQuartalGesSy[0] + $tageVergMonQuartSy[1]){
		$syMon = 2;
		$syQuartal = 1;
	}
	elseif($syTagSyJahr > $tageQuartalGesSy[0] + $tageVergMonQuartSy[0]){
		$syMon = 1;
		$syQuartal = 1;
	}
	
	// Berechnung SY-Tag
	$syTag = $syTagSyJahr - ($tageQuartalGesSy[$syQuartal-1] + $tageVergMonQuartSy[$syMon-1]);
	
	// Berechnung SY-Stunde
	$syStundeD = (($syTagSyJahrD - $syTagSyJahr) * 24);
	
	// Rundung SY-Stunde
	$syStunde = floor($syStundeD);
	
	// Berechnung SY-Minute
	$syMinute = floor(($syStundeD - $syStunde)* 60);
	
	// Führende 0
	if($syMinute < 10) $syMinute = "0" . $syMinute;
	
// Rueckgabe der Methode
	$erg = array($syTag, $syMon, $syJahr, $syStunde, $syMinute);
	return $erg;
}

// SY->RL
function syRl($syTag, $syMon, $syJahr, $syStu, $syMin){
	// Initialisierung Variable Jahre nach 2020
	$syJahreN2020 = 0;
	
	// Initialisierung Variable rlJahr
	$rlJahr = 0;
	
	// Pruefung, welches Eingabejahr
	if($syJahr > 2020){
		// Wenn Eingabe nach 2020
		$syJahreN2020 = $syJahr - 2020;
		
		// Pruefung, ob bei SY-Jahre nach 2020 etwas agezogen werden muss
		$syJahreN2020N = $syJahreN2020;
		if($syJahreN2020N % 4 == 0){
			$syJahreN2020N--;
		}
		
		// RL-Jahr berechnen
		$rlJahr = 2009 + floor($syJahreN2020N / 4);
	}
	else if ($syJahr == 2020){
		// Wenn Eingabejahr 2020
		$rlJahr = 2008;
	}
	
	// Ermittle RL-Quartal
	$rlQuartalZW = $syJahreN2020 % 4;
	$rlQuartal = 0;
	switch ($rlQuartalZW) {
	case 0:
		$rlQuartal = 4;
		break;
	case 1:
		$rlQuartal = 1;
		break;
	case 2:
		$rlQuartal = 2;
		break;
	case 3:
		$rlQuartal = 3;
		break;
	}

	// RL-Schaltjahr-Pruefung mit Ausgabe der Tage im Februar
	$rlFeb = schaltjahrFeb($rlJahr);

	// SY-Schaltjahr-Pruefung mit Ausgabe der Tage im Februar
	$syFeb = schaltjahrFeb($syJahr);
	
	// Array mit Tage pro Quartal fuellen (RL)
	$vergangeneTageQuartalRl = array(
			0, // Platzhalter
			0, // Januar
			31, // Februar
			31 + $rlFeb, // Maerz
			0, // April
			30, // Mai
			30 + 31, // Juni
			0, // Juli
			31, // August
			31 + 31, // September
			0, // Oktober
			31, // November
			31 + 30 // Dezember
	);
	
	// Array mit Tage pro Monat fuellen (SY)
	$vergangeneTageQuartalSy = array(
			0, // Platzhalter
			0, // Januar
			31, // Februar
			31 + $syFeb, // Maerz
			0, // April
			30, // Mai
			30 + 31, // Juni
			0, // Juli
			31, // August
			31 + 31, // September
			0, // Oktober
			31, // November
			31 + 30 // Dezember
	);
	
	// Array mit Tage pro Quartal mit RL-Februar
	$tageProQuartalRl = array(
			0,
			31 + $rlFeb + 31,
			30 + 31 + 30,
			31 + 31 + 30,
			31 + 30 + 31
	);
	
	// Array vergangener Tage im Jahr mit RL-Februar
	$tageBisherJahrRl = array(
			0,
			0,
			31 + $rlFeb + 31,
			31 + $rlFeb + 31 + 30 + 31 + 30,
			31 + $rlFeb + 31 + 30 + 31 + 30 + 31 + 31 + 30
	);
	
	// Array vergangener Tage im Jahr mit SY-Februar
	$tageBisherJahrSy = array(
			0,
			0,
			31 + $syFeb + 31,
			31 + $syFeb + 31 + 30 + 31 + 30,
			31 + $syFeb + 31 + 30 + 31 + 30 + 31 + 31 + 30
	);
	
	// Berechnung SY-Quartal
	$syQuartal = quartalErm($syMon);
	
	// Berechnung SY-Zeitpunkt
	$syZeitAnteil = (($syStu * 60 + $syMin) / (24 * 60));
	
	// Berechnung vergangener SY-Tage im ganzen SY-Jahr
	$vergSyTageSyJahrD = ($tageBisherJahrSy[$syQuartal] + 
			$vergangeneTageQuartalSy[$syMon] + $syTag + $syZeitAnteil - 1);
	
	// Ermittlung gesamtTage SY-Jahr
	$gesTageSyJahr = 365 - 28 + $syFeb;
	
	// Ermittlung RL-Tag pro SY-Tag
	$rlTagProSyTag = $tageProQuartalRl[$rlQuartal] / $gesTageSyJahr;
	
	// Vergangene RL-Tage im SY-Jahr
	$vergRlTageSyJahr = $vergSyTageSyJahrD * $rlTagProSyTag;
	
	// Berechnung vergangener RL-Tage im Jahr
	$vergRlTageRlJahr = $tageBisherJahrRl[$rlQuartal] + $vergRlTageSyJahr;
	
	// Ermittlung des RL-Monats
	$rlMon = 0;
	if($vergRlTageRlJahr >= $tageBisherJahrRl[4] + $vergangeneTageQuartalRl[12]){
		$rlMon = 12;
	}
	elseif($vergRlTageRlJahr >= $tageBisherJahrRl[4] + $vergangeneTageQuartalRl[11]){
		$rlMon = 11;
	}
	elseif($vergRlTageRlJahr >= $tageBisherJahrRl[4] + $vergangeneTageQuartalRl[10]){
		$rlMon = 10;
	}
	elseif($vergRlTageRlJahr >= $tageBisherJahrRl[3] + $vergangeneTageQuartalRl[9]){
		$rlMon = 9;
	}
	elseif($vergRlTageRlJahr >= $tageBisherJahrRl[3] + $vergangeneTageQuartalRl[8]){
		$rlMon = 8;
	}
	elseif($vergRlTageRlJahr >= $tageBisherJahrRl[3] + $vergangeneTageQuartalRl[7]){
		$rlMon = 7;
	}
	elseif($vergRlTageRlJahr >= $tageBisherJahrRl[2] + $vergangeneTageQuartalRl[6]){
		$rlMon = 6;
	}
	elseif($vergRlTageRlJahr >= $tageBisherJahrRl[2] + $vergangeneTageQuartalRl[5]){
		$rlMon = 5;
	}
	elseif($vergRlTageRlJahr >= $tageBisherJahrRl[2] + $vergangeneTageQuartalRl[4]){
		$rlMon = 4;
	}
	elseif($vergRlTageRlJahr >= $tageBisherJahrRl[1] + $vergangeneTageQuartalRl[3]){
		$rlMon = 3;
	}
	elseif($vergRlTageRlJahr >= $tageBisherJahrRl[1] + $vergangeneTageQuartalRl[2]){
		$rlMon = 2;
	}
	elseif($vergRlTageRlJahr >= $tageBisherJahrRl[1] + $vergangeneTageQuartalRl[1]){
		$rlMon = 1;
	}
	
	// Berechnung RL-Tag
	$rlTagD = ($vergRlTageRlJahr - ($tageBisherJahrRl[$rlQuartal] + $vergangeneTageQuartalRl[$rlMon]));
	
	// Runden RL-Tag
	$rlTag = round($rlTagD + 0.5);
	
	// Runden Stunde
	$rlStundeD = ((($rlTagD - $rlTag + 1) * 24));
	$rlStunde = floor($rlStundeD);
	
	// Runden Minute
	$rlMinuteD = ((($rlStundeD - $rlStunde) * 60 ));
	$rlMinute = round($rlMinuteD);
	
	// Fuehrende 0
	if($rlMinute < 10) $rlMinute = "0" . $rlMinute;
	
// Rueckgabe der Methode
	$erg = array($rlTag, $rlMon, $rlJahr, $rlStunde, $rlMinute);
	return $erg;
}

// Pruefung, ob Jahr ein Schaltjahr
function schaltjahrFeb($jahr){
	if(($jahr % 4 == 0 && $jahr % 100 != 0) || $jahr % 400 == 0){
		// Wenn Schaltjahr, dann Februar 29 Tage
		return 29;
	}
	else{
		// Wenn kein Schaltjahr, dann Februar 28 Tage
		return 28;
	}
}

// Ermittlung des Quartals eines Jahres
function quartalErm($mon){
	$q = 0;
	switch ($mon) {
	case 1:
	case 2:
	case 3:
		$q = 1;
		break;
	case 4:
	case 5:
	case 6:
		$q = 2;
		break;
	case 7:
	case 8:
	case 9:
		$q = 3;
		break;
	case 10:
	case 11:
	case 12:
		$q = 4;
		break;
	}
	return $q;
}

?>