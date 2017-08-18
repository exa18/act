<?php
/*
	Copyright (C) 2014 Salvatore Santagati <salvatore.santagati@gmail.com> 
	Copyright (C) 2017 Julian Cenkier <julan.cenkier@wp.eu> 
*/
class tcx {

	private $tcx;

	function __construct ( $act, $baro=0) {
		
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
		$Lap = $Activity->addChild('Lap');
		$Lap->addAttribute( 'StartTime',  $act->getStarttime() );
		$TotalTimeSeconds = $Lap->addChild( 'TotalTimeSeconds', $act->getTotalTimeSeconds() );
		$DistanceMeters = $Lap->addChild ( 'DistanceMeters', $act->getDistanceMeters() );
		$Calories = $Lap->addChild ( 'Calories', $act->getCalories() );
		$AverageHeartRateBpm = $Lap->addChild ( 'AverageHeartRateBpm' );
		$AverageHeartRateBpm->addAttribute ('xmlns:xsi:type', 'HeartRateInBeatsPerMinute_t' );
		$Value = $AverageHeartRateBpm->addChild ( 'Value', $act->getAverageHeartRateBpm() );
		$MaximumHeartRateBpm = $Lap->addChild ('MaximumHeartRateBpm' );
		$MaximumHeartRateBpm->addAttribute ('xmlns:xsi:type', 'HeartRateInBeatsPerMinute_t' );
		$Value = $MaximumHeartRateBpm->addChild ( 'Value', $act->getMaxHearRate() );
		$Cadence = $Lap->addChild ('Cadence', $act->getAvgCadence() );
		$Track = $Lap->addChild ('Track');

		$total=$act->getTracks();
		for ( $i = 0; $i < $total; $i++ ) {

			$Trackpoint = $Track->addChild('Trackpoint');
			$Time = $Trackpoint->addChild('Time', $act->getTimeTrack( $i ) );
			$Position = $Trackpoint->addChild('Position');
			$LatitudeDegrees = $Position->addChild('LatitudeDegrees', $act->getLatitude($i) );
			$LongitudeDegrees = $Position->addChild('LongitudeDegrees', $act->getLongitude($i) );
			$AltitudeMeters = $Trackpoint->addChild('AltitudeMeters', $act->getAltitude($i) );
			$DistanceMeters = $Trackpoint->addchild('DistanceMeters', $act->getDistance($i) );
			$HeartRateBpm = $Trackpoint->addChild('HeartRateBpm');
			$HeartRateBpm->addAttribute('xmlns:xsi:type','HeartRateInBeatsPerMinute_t');
			$Value = $HeartRateBpm->addChild('Value', $act->getHeartRate($i) );
			$Cadence = $Trackpoint->addChild('Cadence', $act->getCadenceTrack($i) );
			//$Trackpoint->addChild('Temperature', $act->getTemp($i) );

			$Extension = $Trackpoint->addChild('Extensions');
			$TPX = $Extension->addChild('TPX');
			$TPX->addAttribute ('xmlns', $ae );
			$TPX->addChild('Speed', $act->getSpeed($i) );
			$TPX->addChild('Watts', $act->getPower($i) );
		}

		$Extensions = $Lap->addChild('Extensions');
		$LX = $Extensions->addChild('LX');
		$LX->addAttribute ('xmlns', $ae );
		$LX->addChild('AvgSpeed', $act->getAvgSpeed() );
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
