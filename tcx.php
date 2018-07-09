<?php

/*
	Copyright (C) 2014 Salvatore Santagati <salvatore.santagati@gmail.com>
	Copyright (C) 2017 Julian Cenkier <julian.cenkier@wp.eu>
*/

class tcx {

	private $tcx;

	function __construct ( $act, $baro=1, $indoor=0, $inlaps=1, $strava=true) {
		$ms=true;
		$ae='http://www.garmin.com/xmlschemas/ActivityExtension/v2';
		$this->tcx = new SimpleXMLElement("<TrainingCenterDatabase></TrainingCenterDatabase>");
		$root = $this->tcx;
		/* Namespace */
		$root->addAttribute('xmlns', 'http://www.garmin.com/xmlschemas/TrainingCenterDatabase/v2');
		$root->addAttribute('xmlns:xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
		$root->addAttribute('xmlns:xsi:schemaLocation', ' http://www.garmin.com/xmlschemas/TrainingCenterDatabase/v2 http://www.garmin.com/xmlschemas/TrainingCenterDatabasev2.xsd');
		$root->addAttribute('xmlns:xmlns:ns3', $ae);

		$Activities = $root->addChild( 'Activities' );
		$Activity = $Activities->addChild( 'Activity' );
		$Activity->addAttribute( 'Sport',  $act->getActivitySport() );  
		$Id = $Activity->addChild( 'Id', $act->getId() );
		
		$laps = $act->getNoOfLaps();
		$total=$act->getTracks();
		
		if ($laps>1 && $inlaps){
		/*
			Laps
		*/
			$l = $act->getLaps();
				$s=0;
				for ( $i=0; $i < $laps; $i++ ) {
					$li = $l[$i];
					$e = $li['lap'];
					
					$Lap = $Activity->addChild('Lap');
					$Lap->addAttribute( 'StartTime',  $li['starttime'] );
					$TotalTimeSeconds = $Lap->addChild( 'TotalTimeSeconds', $li['totaltime'] );
					$DistanceMeters = $Lap->addChild ( 'DistanceMeters', $li['totaldistance'] );
					$Calories = $Lap->addChild ( 'Calories', $li['calory'] );
					$AverageHeartRateBpm = $Lap->addChild ( 'AverageHeartRateBpm' );
					$AverageHeartRateBpm->addAttribute ('xmlns:xsi:type', 'HeartRateInBeatsPerMinute_t' );
					$AverageHeartRateBpm->addChild ( 'Value', $li['hravg'] );
					$MaximumHeartRateBpm = $Lap->addChild ('MaximumHeartRateBpm' );
					$MaximumHeartRateBpm->addAttribute ('xmlns:xsi:type', 'HeartRateInBeatsPerMinute_t' );
					$MaximumHeartRateBpm->addChild ( 'Value', $li['hrmax'] );
					$Cadence = $Lap->addChild ('Cadence', $li['cadavg'] );
					$Track = $Lap->addChild ('Track');					
					$yd = 0;
					for ($x=$s; $x <= $e; $x++) {
						$Trackpoint = $Track->addChild('Trackpoint');
						$Trackpoint->addChild('Time', $act->getTimeTrack( $x ) );
						$Position = $Trackpoint->addChild('Position');
						$Position->addChild('LatitudeDegrees', $act->getLatitude($x) );
						$Position->addChild('LongitudeDegrees', $act->getLongitude($x) );
						$Trackpoint->addChild('AltitudeMeters', $act->getAltitude($x) );
							$xd = $act->getDistance($x,$strava, $indoor);
							if ($indoor) {
								$yd += $xd;
								$xd = $yd;
							}
						$Trackpoint->addchild('DistanceMeters', $xd );
						$HeartRateBpm = $Trackpoint->addChild('HeartRateBpm');
						$HeartRateBpm->addAttribute('xmlns:xsi:type','HeartRateInBeatsPerMinute_t');
						$HeartRateBpm->addChild('Value', $act->getHeartRate($x) );
						$Trackpoint->addChild('Cadence', $act->getCadenceTrack($x) );
						//$Trackpoint->addChild('Temperature', $act->getTemp($x) );
			
						$Extension = $Trackpoint->addChild('Extensions');
						$TPX = $Extension->addChild('TPX');
						$TPX->addAttribute ('xmlns', $ae );
						$TPX->addChild('Speed', $act->getSpeed($x,$ms) );
						$TPX->addChild('Watts', $act->getPower($x) );
					}					
					$s = $e+1;
				}
		}else{
		/*
			NO Laps
		*/
			$Lap = $Activity->addChild('Lap');
			$Lap->addAttribute( 'StartTime',  $act->getStarttime() );
			$TotalTimeSeconds = $Lap->addChild( 'TotalTimeSeconds', $act->getTotalTimeSeconds() );
			$DistanceMeters = $Lap->addChild ( 'DistanceMeters', $act->getDistanceMeters() );
			$Calories = $Lap->addChild ( 'Calories', $act->getCalories() );
			$AverageHeartRateBpm = $Lap->addChild ( 'AverageHeartRateBpm' );
			$AverageHeartRateBpm->addAttribute ('xmlns:xsi:type', 'HeartRateInBeatsPerMinute_t' );
			$AverageHeartRateBpm->addChild ( 'Value', $act->getAverageHeartRateBpm() );
			$MaximumHeartRateBpm = $Lap->addChild ('MaximumHeartRateBpm' );
			$MaximumHeartRateBpm->addAttribute ('xmlns:xsi:type', 'HeartRateInBeatsPerMinute_t' );
			$MaximumHeartRateBpm->addChild ( 'Value', $act->getMaxHearRate() );
			$Cadence = $Lap->addChild ('Cadence', $act->getAvgCadence() );
			$Track = $Lap->addChild ('Track');
			$yd = 0;
			for ( $i = 0; $i < $total; $i++ ) {
				$Trackpoint = $Track->addChild('Trackpoint');
				$Trackpoint->addChild('Time', $act->getTimeTrack( $i ) );
				$Position = $Trackpoint->addChild('Position');
				$Position->addChild('LatitudeDegrees', $act->getLatitude($i) );
				$Position->addChild('LongitudeDegrees', $act->getLongitude($i) );
				$Trackpoint->addChild('AltitudeMeters', $act->getAltitude($i) );
					$xd = $act->getDistance($i,$strava, $indoor);
					if ($indoor) {
						$yd += $xd;
						$xd = $yd;
					}
				$Trackpoint->addchild('DistanceMeters', $xd );
				$HeartRateBpm = $Trackpoint->addChild('HeartRateBpm');
				$HeartRateBpm->addAttribute('xmlns:xsi:type','HeartRateInBeatsPerMinute_t');
				$HeartRateBpm->addChild('Value', $act->getHeartRate($i) );
				$Trackpoint->addChild('Cadence', $act->getCadenceTrack($i) );
				//$Trackpoint->addChild('Temperature', $act->getTemp($i) );
	
				$Extension = $Trackpoint->addChild('Extensions');
				$TPX = $Extension->addChild('TPX');
				$TPX->addAttribute ('xmlns', $ae );
				$TPX->addChild('Speed', $act->getSpeed($i,$ms) );
				$TPX->addChild('Watts', $act->getPower($i) );
			}
		}
		$Extensions = $Lap->addChild('Extensions');
		$LX = $Extensions->addChild('LX');
		$LX->addAttribute ('xmlns', $ae );
		$LX->addChild('AvgSpeed', $act->getAvgSpeed($ms) );
		$LX->addChild('MaxBikeCadence', $act->getMaxCadence() );
		
		$Creator = $Activity->addChild ('Creator');
		$Creator->addAttribute('xmlns:xsi:type', 'Device_t');
		$Name = $Creator->addChild ('Name', $act->getDeviceName() . ($baro?' with barometer':'') );

	}

	function GetTcx ( ) {
		return $this->tcx;
	}
}



?>
