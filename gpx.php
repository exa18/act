<?php
/*
	Class based on
	https://github.com/kgkilo/act2gpx
	written by <kg.kilo@gmail.com> https://github.com/kgkilo
	
 */
class gpx {

	private $gpx;

	function __construct ( $act, $baro=0) {

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
		$metadata->addChild( 'calories' , '0' );
		$trk = $root->addChild( 'trk' );
		$trkseg = $trk->addChild( 'trkseg' );
		
		for ( $i = 0; $i < $act->getTracks(); $i++ ) {

			$trkpt = $trkseg->addChild('trkpt');
			$trkpt->addAttribute( 'lat' , $act->getLatitude($i) );
			$trkpt->addAttribute( 'lon' , $act->getLongitude($i) );
			$trkpt->addChild('time', $act->getTimeTrack($i) );
			$trkpt->addChild('ele', $act->getAltitude($i) );
			$trkpt->addChild('name', 'wp_'.$i );
			//$trkpt->addChild('speed', $act->getSpeed($i) );
			
			$extensions = $trkpt->addChild('extensions');
			$gpxtpx = $extensions->addChild('gpxtpx:TrackPointExtension');
			$extensions->addChild('power', $act->getPower($i) );
			$extensions->addChild('temp', $act->getTemp($i) );
			$gpxtpx->addChild('gpxtpx:hr', $act->getHeartRate($i) );
			$gpxtpx->addChild('gpxtpx:cad', $act->getCadenceTrack($i) );
			$gpxtpx->addChild('gpxtpx:speed', $act->getSpeed($i) );
		}
	}

	function GetGpx ( ) {

		return $this->gpx;
	}
}



?>
