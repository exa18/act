<?php
/*
	Class based on
	https://github.com/kgkilo/act2gpx
	written by <kg.kilo@gmail.com> https://github.com/kgkilo
	
	Copyright (C) 2017 Julian Cenkier <julan.cenkier@wp.eu> 
 */
class gpx {

	private $gpx;

	function __construct ( $act, $baro=0) {

		$this->gpx = new SimpleXMLElement("<gpx></gpx>");
		$root=$this->gpx;
		
		$root->addAttribute('version', '1.1');
		$root->addAttribute('creator', $act->getDeviceName() . ($baro?' with Barometer':''));
		$root->addAttribute('xsi:schemaLocation', 'http://www.topografix.com/GPX/1/1/gpx.xsd http://www.garmin.com/xmlschemas/GpxExtensions/v3 http://www.garmin.com/xmlschemas/GpxExtensionsv3.xsd http://www.garmin.com/xmlschemas/TrackPointExtension/v1 http://www.garmin.com/xmlschemas/TrackPointExtensionv1.xsd');
		$root->addAttribute('xmlns', 'http://www.topografix.com/GPX/1/1');
		$root->addAttribute('xmlns:xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
		$root->addAttribute('xmlns:xmlns:gpxtpx', 'http://www.garmin.com/xmlschemas/TrackPointExtension/v1');
		$root->addAttribute('xmlns:xmlns:gpxx', 'http://www.garmin.com/xmlschemas/GpxExtensions/v3');

		$metadata = $root->addChild( 'metadata' );
		//$metadata_link = $metadata->addChild( 'link' );
		//$metadata_link->addAttribute( 'href' , 'connect.garmin.com');
		//$metadata_link->addChild( 'text' , 'Garmin Connect');
		$metadata->addChild( 'time' , $act->getStarttime() );
		$metadata->addChild( 'calories' , $act->getCalories() );
		$trk = $root->addChild( 'trk' );
		
		/*
			this section is ignored by Strava
		*/
		$extensions = $trk->addChild( 'extensions');
		$extensions->addChild( 'length', $act->getDistanceMeters () );
		$extensions->addChild( 'avgspeed', $act->getAvgSpeed() );
		$extensions->addChild( 'maxspeed', $act->getMaxSpeed() );
		$extensions->addChild( 'minaltitude', $act->getMinAltitude() );
		$extensions->addChild( 'maxaltitude', $act->getMaxAltitude() );
		$extensions->addChild( 'totalascent', $act->getEleUp() );
		$extensions->addChild( 'totaldescent', $act->getEleDown() );
		$extensions->addChild( 'calories', $act->getCalories() );
		$extensions->addChild( 'avgcadence', $act->getAvgCadence() );
		$extensions->addChild( 'maxcadence', $act->getMaxCadence() );
		$extensions->addChild( 'avgheartrate', $act->getAverageHeartRateBpm() );
		$extensions->addChild( 'minheartrate', $act->getMinHeartRate() );
		$extensions->addChild( 'maxheartrate', $act->getMaxHearRate() );
		$extensions->addChild( 'avgpower', $act->getAvgPower() );
		$extensions->addChild( 'maxpower', $act->getMaxPower() );
		
		$trkseg = $trk->addChild( 'trkseg' );
		
		$total=$act->getTracks();
		for ( $i = 0; $i < $total; $i++ ) {

			$trkpt = $trkseg->addChild('trkpt');
			$trkpt->addAttribute( 'lat' , $act->getLatitude($i) );
			$trkpt->addAttribute( 'lon' , $act->getLongitude($i) );
			$trkpt->addChild('time', $act->getTimeTrack($i) );
			$trkpt->addChild('ele', $act->getAltitude($i) );
			//$trkpt->addChild('name', 'wp_'.$i );
			//$trkpt->addChild('speed', $act->getSpeed($i) );
			
			$extensions = $trkpt->addChild('extensions');
			$extensions->addChild('power', $act->getPower($i) );
			$extensions->addChild('speed', $act->getSpeed($i) );
			$gpxtpx = $extensions->addChild('gpxtpx:gpxtpx:TrackPointExtension');
			$gpxtpx->addChild('gpxtpx:gpxtpx:hr', $act->getHeartRate($i) );
			$gpxtpx->addChild('gpxtpx:gpxtpx:cad', $act->getCadenceTrack($i) );
			$gpxtpx->addChild('gpxtpx:gpxtpx:atemp', $act->getTemp($i) );
		}
	}

	function GetGpx ( ) {
		return $this->gpx;
	}
}
?>
