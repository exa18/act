<?php
/*
	Class based on
	https://github.com/kgkilo/act2gpx
	written by <kg.kilo@gmail.com> https://github.com/kgkilo
	
	Copyright (C) 2017 Julian Cenkier <julan.cenkier@wp.eu> 
 */
class gpx {

	private $gpx;

	function __construct ( $act, $baro=0 ) {

		$this->gpx = new SimpleXMLElement("<gpx></gpx>");
		$root=$this->gpx;

		/* Namespace */
		/*
		
		<gpx version="1.1"
creator="Garmin Edge 800"
xmlns="http://www.topografix.com/GPX/1/1"
xmlns:gpxtpx="http://www.garmin.com/xmlschemas/TrackPointExtension/v1"
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xsi:schemaLocation="http://www.topografix.com/GPX/1/1 http://www.topografix.com/GPX/1/1/gpx.xsd">

  <metadata>
    <link href="https://github.com/kgkilo/act2gpx">
      <text>Act2GPX</text>
    </link>
  </metadata>

  <trk>
    <trkseg>
		*/
		
		/*
<gpx version="1.1"   creator="IpBike" 
xsi:schemaLocation="http://www.topografix.com/GPX/1/1/gpx.xsd 
http://www.garmin.com/xmlschemas/GpxExtensions/v3 
http://www.garmin.com/xmlschemas/GpxExtensionsv3.xsd 
http://www.garmin.com/xmlschemas/TrackPointExtension/v1 
http://www.garmin.com/xmlschemas/TrackPointExtensionv1.xsd"
xmlns="http://www.topografix.com/GPX/1/1" 
xmlns:gpxtpx="http://www.garmin.com/xmlschemas/TrackPointExtension/v1" 
xmlns:gpxx="http://www.garmin.com/xmlschemas/GpxExtensions/v3" 
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" >		
		
		
		*/
		
		$root->addAttribute('version', '1.1');
		$root->addAttribute('creator', $act->getDeviceName() . ($baro?' with Barometer':''));
		$root->addAttribute('xsi:schemaLocation', 'http://www.topografix.com/GPX/1/1/gpx.xsd http://www.garmin.com/xmlschemas/GpxExtensions/v3 http://www.garmin.com/xmlschemas/GpxExtensionsv3.xsd http://www.garmin.com/xmlschemas/TrackPointExtension/v1 http://www.garmin.com/xmlschemas/TrackPointExtensionv1.xsd');
		$root->addAttribute('xmlns', 'http://www.topografix.com/GPX/1/1');
		$root->addAttribute('xmlns:xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
		$root->addAttribute('xmlns:xmlns:gpxtpx', 'http://www.garmin.com/xmlschemas/TrackPointExtension/v1');
		$root->addAttribute('xmlns:xmlns:gpxx', 'http://www.garmin.com/xmlschemas/GpxExtensions/v3');
		/*
		 <metadata>
		<link href="connect.garmin.com">
		 <text>Garmin Connect</text>
		</link>
		<time>2017-02-25T12:52:27Z</time>
		<calories>0</calories>
		</metadata>
		*/
		$metadata = $root->addChild( 'metadata' );
		$metadata_link = $metadata->addChild( 'link' );
		$metadata_link->addAttribute( 'href' , 'connect.garmin.com');
		$metadata_link->addChild( 'text' , 'Garmin Connect');
		$metadata->addChild( 'time' , $act->getStarttime() );
		//$metadata->addChild( 'calories' , $act->getCalories() );
		$trk = $root->addChild( 'trk' );
	
		/*
		  <trk>
		    <name>20170324165424</name>
		    <extensions>
		      <profile>Juliancenkier</profile>
		      <time>2017-03-24T16:54:24</time>
		      +<length>31031</length>
		      <timelength>6031</timelength>
		      +<avgspeed>5.144444</avgspeed>
		      +<maxspeed>13.055556</maxspeed>
		      +<minaltitude>0</minaltitude>
		      +<maxaltitude>696</maxaltitude>
		      +<totalascent>647</totalascent>
		      +<totaldescent>654</totaldescent>
		      +<calories>1358</calories>
		      +<avgcadence>74</avgcadence>
		      +<maxcadence>147</maxcadence>
		      +<avgheartrate>142</avgheartrate>
		      +<minheartrate>30</minheartrate>
		      +<maxheartrate>188</maxheartrate>
		      +<avgpower>0</avgpower>
		      +<maxpower>0</maxpower>
		    </extensions>
		*/
		
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
		/*
			 <trkseg>
			   <trkpt lat="50.8758450" lon="4.6710150">
			    <ele>60.0</ele>
			    <time>2014-06-15T15:45:39Z</time>
			    <extensions>
			        <power>0</power>
			        <speed>0</speed>
			     <gpxtpx:TrackPointExtension>
			      <gpxtpx:atemp>25</gpxtpx:atemp>
			      <gpxtpx:hr>107</gpxtpx:hr>
			      <gpxtpx:cad>65</gpxtpx:cad>
			     </gpxtpx:TrackPointExtension>
			    </extensions>
			</trkpt>
		*/
		
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
