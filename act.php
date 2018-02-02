<?php

/*
	Copyright (C) 2014 Salvatore Santagati <salvatore.santagati@gmail.com>
	Copyright (C) 2017 Julian Cenkier <julian.cenkier@wp.eu>
*/

class act {
	
	private $Sport_t;
	private $Id;
	private $dateTime;
	private $ttseconds;
	private $distancemeters;
	private $AvgHeartRate;
	private $MinHeartRate;
	private $MaxHearRate;
	private $Cadence;
	private $Tracks;
	private $TimeTrack;
	private $track;
	private $LatitudeDegrees;
	private $LongitudeDegrees;
	private $AltitudeMeters;
	private $HeartRateBpm;
	private	$CadenceTrack;
	private $Device;
	private $Distance;
	private $IntervalTime;
	private $IntervalTimeDiff;
	private $utc_offset;
	private $Temperature;
	private $AvgTemp;
	private $MinTemp;
	private $MaxTemp;
	private $Power;
	private $AvgPower;
	private $MaxPower;
	private $Speed;
	private $AvgSpeed;
	private $MaxSpeed;
	private $MaxCadence;
	private $AUpheight;
	private $ADownheight;
	private $MinAlt;
	private $MaxAlt;
	private $NoOfLaps;
	private $Laps;

	function __construct ( $act ) {
		$this->setActivitySport ( $act );
		$this->setUTC ( $act );
		$this->setId ( $act );
		$this->setStarttime ( $act );
		$this->setTotalTimeSeconds ( $act );
		$this->setDistanceMeters ( $act );
		$this->setCalories ( $act );
		$this->setAverageHeartRateBpm ($act);
		$this->setMaxHearRate ($act);
		$this->setAvgCadence ($act);
		$this->setMaxCadence ($act);
		$this->setMaxSpeed ($act);
		$this->setMinTemp ($act);
		$this->setMaxTemp ($act);
		$this->setAvgTemp ($act);
		$this->setAvgPower ($act);
		$this->setMaxPower ($act);
		$this->setMinAltitude ($act);
		$this->setMaxAltitude ($act);
		$this->setEleUp ($act);
		$this->setEleDown ($act);
		$this->setTracks ($act);
		$this->setTrackPoints ($act);
		$this->setLaps ($act);
		$this->setDeviceName ($act);
	}

	function setDistance ( $lat1, $lon1, $lat2, $lon2, $speed, $interval ) {
		$this->distance = 0;

		if (( $lat1 != $lat2 ) && ( $lon1 != $lon2 ))
		{
			// Get distance from longitude and latitude
			// Haversine formula

    			$earth_radius = 6371;  
      
    			$dLat = deg2rad($lat2 - $lat1);  
    			$dLon = deg2rad($lon2 - $lon1);  
      
    			$a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);  
			$c = 2 * asin(sqrt($a));  

			$DistHaversine = $earth_radius * $c;

			// Get distance from speed and interval 
			$DistInterval  = ( ( $speed / 60 ) / 60 ) * $interval;

			// Media distance result
			if ( $DistInterval == 0 )
				$this->distance = $DistHaversine * 1000;
			else 	$this->distance = ( ( $DistHaversine + $DistInterval ) / 2 ) * 1000;
				
		}
		return $this->distance;  
	}

	function hoursToSeconds ($hour) { 
		$hour_fixed = strtotime(str_replace(".", ":" , $hour ));

		$hours 	= date('H', $hour_fixed);
		$mins= date('i', $hour_fixed);
		$secs= date('s', $hour_fixed);

		return $hours * 3600 + $mins * 60 + $secs;
	}

	function setActivitySport ( $act ) 	{
		switch ($act->trackmaster->Sport1) {
		case	0:
		case	1:
		case	2: 
				$this->Sport_t = "Running";
				break;
		case	3:	$this->Sport_t = "Biking";
				break;
		case	4:
		case	5:	$this->Sport_t = "Other";
				break;
		default :
				$this->Sport_t = "";
		}
	}

	function setDeviceName ( $act ) {
		$this->Device = $act->getName();
	}

	function setId ( $act )		{
		$this->current_Id = date("Y-m-d", strtotime ( $act->trackmaster->TrackName ) ) 
			. "T" . date('H:i:s', strtotime($act->trackmaster->StartTime)) . "Z";
	
		$this->Id = new DateTime ($this->current_Id);

		if ( $this->getUTC() < 0 )
		{
			$this->tmpUTC = $this->getUTC() * -1 ;
			$this->Id->add(new DateInterval('PT' . $this->tmpUTC . 'S'));	
		}
		else $this->Id->sub(new DateInterval('PT' . $this->getUTC() . 'S'));
	}

	function setStarttime ( $act )	{
		$this->current_dateTime =  date("Y-m-d", strtotime( $act->trackmaster->TrackName ) ) 
			. "T" . date('H:i:s', strtotime($act->trackmaster->StartTime)) . "Z";

		$this->dateTime = new DateTime ($this->current_dateTime );

		if ( $this->getUTC() < 0 )
		{
			$this->tmpUTC = $this->getUTC() * -1 ;
			$this->dateTime->add(new DateInterval('PT' . $this->tmpUTC . 'S'));	
		}
		else $this->dateTime->sub(new DateInterval('PT' . $this->getUTC() . 'S'));
	}
	
	function setTotalTimeSeconds ( $act ) {
		$this->ttseconds =  ( $this->hoursToSeconds ( $act->trackmaster->Duration ) );
	}

	function setDistanceMeters ( $act )	{
		$this->Distancemeters = $act->trackmaster->TotalDist;
	}

	function setCalories ( $act ) {
		$this->Calories = $act->trackmaster->Calories;
	}	


	function setAverageHeartRateBpm ( $act ) {
		$this->AvgHeartRate = $act->trackmaster->AvgHeartRate;
	}

	function setMaxHearRate ($act) {
		$this->MaxHearRate = $act->trackmaster->MaxHearRate;
	}

	function setAvgCadence ( $act ) {
		$this->Cadence	=  $act->trackmaster->AvgCadence;
	}

	function setMaxCadence ( $act ) {
		$this->MaxCadence	=  $act->trackmaster->MaxCadence;
	}
	
	function setMaxTemp ( $act ) {
		$this->MaxTemp	=  str_replace(",", ".",$act->trackmaster->MaxTemp);
	}
	function setMinTemp ( $act ) {
		$this->MinTemp	=  str_replace(",", ".",$act->trackmaster->MinTemp);
	}
	function setAvgTemp ( $act ) {
		$this->AvgTemp	=  str_replace(",", ".",$act->trackmaster->AvgTemp);
	}
	function setAvgPower ( $act ) {
		$this->AvgPower	=  $act->trackmaster->AvgPower;
	}
	function setMaxPower ( $act ) {
		$this->MaxPower	=  $act->trackmaster->MaxPower;
	}
	function setMaxSpeed ( $act ) {
		$this->MaxSpeed	=  str_replace(",", ".",$act->trackmaster->MaxSpeed);
	}
	function setTracks ( $act ){
		$this->Tracks = count ($act->TrackPoints );
	}

	function setTimeTrack ( $value , $track ){
 		$this->TimeTrack[$track] = $value;
	}

	function setIntervalTime ( $timediff, $track, $interval ){
		$this->IntervalTime[$track] =  round ( $interval + $timediff );
	}
		
	function setIntervalTimeDiff ( $timediff, $track, $interval ) {
		$this->getIntervalTimeDiff[$track] = $this->getIntervalTime($track) -  $interval + $timediff;
	}

	function setUTC ( $act ){
		$this->localTime =  date("Y-m-d", strtotime( $act->trackmaster->TrackName ) ) 
			. "T" . date('H:i:s', strtotime($act->trackmaster->StartTime)) . "Z";

		$this->CurrentTime = new DateTime ( $this->localTime );

		$this->lat = str_replace(",", "." , $act->TrackPoints[0]->Latitude );
		$this->lon = str_replace(",", "." , $act->TrackPoints[0]->Longitude ) ;
		$this->timestamp = $this->CurrentTime->getTimestamp();
	
		/*  Google Maps Api */
		$this->url_api = "https://maps.googleapis.com/maps/api/timezone/json?location=";
		$this->url_timezone = $this->url_api.$this->lat.",".$this->lon."&timestamp=".$this->timestamp;

		$this->obj_tz = file_get_contents($this->url_timezone);

		$this->tz = json_decode($this->obj_tz);
		
		$this->utc_offset = $this->tz->dstOffset + $this->tz->rawOffset;
	}
	
	function setLaps( $act ) {
		$laps = $act->trackmaster->NoOfLaps;
		$this->NoOfLaps = $laps;
		if ($laps>1){
			$s = 0;
			for ( $i=0; $i < $laps; $i++ ) {
				// lap: 1 lap is from 0 to < endIndex[0], and 2 lap is from endIndex[0] to < endIndex
				$e = $act->trackmaster[$i]->endIndex;
				$this->Laps[$i]['lap'] = $e;
				$hr = array();
				for ($x=$s; $x <= $e; $x++) {
					$speed = $this->Speed[$x];
					$speed += (float)$speed;
					$hr[] = $this->HeartRateBpm[$x];
				}
				$this->Laps[$i]['starttime'] = $this->TimeTrack[$s];
				$this->Laps[$i]['totaltime'] = ( $this->hoursToSeconds ( $act->trackmaster[$i]->TotalTime ) );
				$this->Laps[$i]['totaldistance'] = $act->trackmaster[$i]->TotalDistance;
				$this->Laps[$i]['calory'] = $act->trackmaster[$i]->Calory;
				$this->Laps[$i]['cadavg'] = $act->trackmaster[$i]->LapAvgCad;
				$this->Laps[$i]['cadmax'] = $act->trackmaster[$i]->LapMaxCad;
				$this->Laps[$i]['pwravg'] = $act->trackmaster[$i]->LapAvgPwr;
				$this->Laps[$i]['pwrmax'] = $act->trackmaster[$i]->LapMaxPwr;
				$this->Laps[$i]['speedmax'] = str_replace (",",".",$act->trackmaster[$i]->LapMaxSpeed);
				$this->Laps[$i]['speedavg'] = number_format( round($speed/($e-$s-1),2) , 2, '.', '' );
				$this->Laps[$i]['hrmax'] = $act->trackmaster[$i]->LapMaxHR;
				$this->Laps[$i]['hravg'] = $act->trackmaster[$i]->LapAvgHR;
				$this->Laps[$i]['hrmin'] = min($hr);
				$s = $e+1;
			}
		}
	}
	
	function setTrackPoints( $act ){
		$this->CurrentTime = new DateTime ($this->getStarttime()) ;

		$this->Distance[0] = 0;
		$total=$this->getTracks();
		$s=0;
		
		for ( $this->track = 0; $this->track < $total; $this->track++) {
			
			/* TIME */
		       $this->TimeTrack[$this->track] = $this->CurrentTime->format('Y-m-d\TH:i:s\Z');
		       $i=str_replace (",","." , $act->TrackPoints[$this->track]->IntervalTime);
		       $d=$this->IntervalTimeDiff[$this->track];
			   $speed=$act->TrackPoints[$this->track]->Speed;
			   $s += (float)$speed;
			   $speed=str_replace (",",".",$speed);
		       
		       $this->IntervalTime[$this->track] =  round ( $d + $i );
		       /*
		       $this->setIntervalTime( 
				$d,
			       	$this->track, 
				//str_replace(",","." , $act->TrackPoints[$this->track]->IntervalTime)
				$i
			);
			*/
			
				//$this->IntervalTimeDiff[$this->track] = $this->IntervalTime[$this->track] -  $d + $i;
				/*
		       $this->setIntervalTimeDiff( 
			       	$d,
				$this->track,
				//str_replace (",","." , $act->TrackPoints[$this->track]->IntervalTime)
				$i
			);
			*/
		       //$this->CurrentTime->add(new DateInterval('PT' . $this->getIntervalTime($this->track) . 'S'));
		       $this->CurrentTime->add(new DateInterval('PT' . $this->IntervalTime[$this->track] . 'S'));

		       /* Latitude */
		       $this->LatitudeDegrees[$this->track] = ( str_replace(",", "." , $act->TrackPoints[$this->track]->Latitude ) );

		       /* Longitude */
		       $this->LongitudeDegrees[$this->track] = ( str_replace(",", "." , $act->TrackPoints[$this->track]->Longitude ) );

		       /* Altitude */
		       $this->AltitudeMeters[$this->track] = $act->TrackPoints[$this->track]->Altitude;
		     
		       /* Distance */
		       if ( $this->track > 0 ) {
					$this->Distance[$this->track] = $this->Distance[$this->track-1] + 
					$this->setDistance ( 
						$this->LatitudeDegrees[$this->track],
						$this->LongitudeDegrees[$this->track],
						$this->LatitudeDegrees[$this->track-1],
						$this->LongitudeDegrees[$this->track-1],
						$speed,
						$i
					) ;
				}
		
		       /* HeartRate */
		       $this->HeartRateBpm[$this->track] = $act->TrackPoints[$this->track]->HeartRate;

		       /* Cadence */
		       $this->CadenceTrack[$this->track] = $act->TrackPoints[$this->track]->Cadence;
			   
			   /* Temperature */
		       $this->Temperature[$this->track] = str_replace (",",".",$act->TrackPoints[$this->track]->Temperature);
			   
			   /* Power */
		       $this->Power[$this->track] = $act->TrackPoints[$this->track]->Power;
			   /*
					WIGHT = biker weight
					CRR = is the dimensionless rolling resistance coefficient or coefficient of rolling friction (CRF)
					DF = DragFactor : frontal area in Meters squared * drag coefficient
					A = acceleration
					TW = total weight
					N =  9.81 m/s2
					P1 = speed * WIGHT * ( CRR * N )
					P2 = speed * DF
					P3 = A * TW
					P = P1 + P2 + P3
					
					
					// power formula
					
					P[W] = F[N] * l[mm] * w[rpm] * 2 * pi / 60
					P[W] -> power in watts
					F[N] -> force in newtons
					l[mm] -> crank length in mm
					w[rpm] -> cadence in rpm
			   
			   */
			   
			   /* Speed */
		       $this->Speed[$this->track] = $speed;

		}
				// here set avgspeed and minHR for each lap too
		       $this->AvgSpeed = number_format($s/$total, 2, '.', '');
		       $this->MinHeartRate = min($this->HeartRateBpm);
	}

	function setMinAltitude ( $act ) {
		$this->MinAlt = $act->trackmaster->MinAltitude;
	}
	
	function setMaxAltitude ( $act ) {
		$this->MaxAlt = $act->trackmaster->MaxAltitude;
	}
	
	function setEleUp ( $act ) {
		$this->AUpheight = $act->trackmaster->AUpheight;
	}
	
	function setEleDown ( $act ) {
		$this->ADownheight = $act->trackmaster->ADownheight;
	}
	
	function getMinAltitude() {
		return $this->MinAlt;
	}
	
	function getMaxAltitude() {
		return $this->MaxAlt;
	}
	
	function getEleUp() {
		return $this->AUpheight;
	}
	
	function getEleDown() {
		return $this->ADownheight;
	}
		
	function setLatitude( $act, $track , $value) {
		$this->LatitudeDegrees[$track] = $value;
	}
	
	function setLongitude( $act, $track , $value){
		$this->LongitudeDegrees[$track] = $value;
	}

	function setAltitude($act, $track , $value){
		$this->AltitudeMeters[$track] = $value;
	}

	function setHeartRate($act, $track , $value){
		$this->HeartRate[$track] = $value;
	}

	function setCadenceTrack ($track, $value) {
		$this->CadenceTrack[$track] = $value;
	}

	function getIntervalTime ( $track ){
		return $this->IntervalTime[$track];
	}

	function getIntervalTimeDiff ( $track ) {
		return $this->IntervalTimeDiff[$track];
	}

	function getDeviceName() {
		return $this->Device;
	}
	
	function getCadenceTrack ($track) {
		return $this->CadenceTrack[$track];
	}

	function getHeartRate ($track){
		return $this->HeartRateBpm[$track];
	}
	
	function getMinHeartRate(){
		return $this->MinHeartRate;
	}
	
	function speedKPHtoMS ($s) {
		$s = round( $s * 0.277777778 , 3);
		$s = number_format($s, 3, '.', '');
		return $s;
	}
	
	function getSpeed ($track, $ms=false){
		$s = $this->Speed[$track];
		if ($ms) {
			$s = $this->speedKPHtoMS($s);
		}
		return $s;
	}
	
	function getMaxSpeed(){
		$s = $this->MaxSpeed;
		if ($ms) {
			$s = $this->speedKPHtoMS($s);
		}
		return $s;	
	}
	
	function getPower ($track){
		return $this->Power[$track];
	}
	
	function getAvgPower(){
		return $this->AvgPower;
	}
	
	function getMaxPower(){
		return $this->MaxPower;
	}
	function getTemp ($track){
		return $this->Temperature[$track];
	}
	
	function getMaxTemp() {
		return $this->MaxTemp;
	}
	
	function getMinTemp() {
		return $this->MinTempp;
	}
	
	function getAvgTemp() {
		return $this->AvgTemp;
	}
	
	function getAvgSpeed($ms=false){
		$s = $this->AvgSpeed;
		if ($ms) {
			$s = $this->speedKPHtoMS($s);
		}
		return $s;
	}
	
	function getAltitude($track){
		return $this->AltitudeMeters[$track];
	}
	
	function getLongitude($track){
		return $this->LongitudeDegrees[$track];
	}
	
	function getDistance($track){
		//$precision = 2;     // 1cm
		$precision = 1;     // 10cm
		//$precision = 0;     // 100cm
		$d = round( $this->Distance[$track] , $precision);
		$d = number_format($d, $precision, '.', '');
		return $d;
	}

	function getLatitude($track) {
		return $this->LatitudeDegrees[$track];
	}

	function getTimeTrack ( $track ){
		return $this->TimeTrack[$track];
	}

	function getTracks() {
		return $this->Tracks;
	}

	function getAvgCadence() {
		return $this->Cadence;
	}
	
	function getMaxCadence() {
		return $this->MaxCadence;
	}
	function setMaxCadenceVal ($val) {
		$this->MaxCadence = $val;
	}
	
	function setAvgCadenceVal ($val) {
		$this->Cadence = $val;
	}
	
	function getMaxHearRate() {
		return $this->MaxHearRate;
	}

	function getAverageHeartRateBpm()  {
		return $this->AvgHeartRate;
	}

	function getCalories() {
		return $this->Calories;
	}

	function getDistanceMeters() {
		return $this->Distancemeters;
	}

	function getTotalTimeSeconds() {
		return $this->ttseconds;
	}

	function getStarttime() {
		return $this->dateTime->format('Y-m-d\TH:i:s\Z');
	}
	
	function getId() {
		return $this->Id->format('Y-m-d\TH:i:s\Z');
	}

	function getActivitySport()	{
		return $this->Sport_t;
	}

	function getUTC() {
		return $this->utc_offset; 
	}

	function getNoOfLaps(){
		return $this->NoOfLaps;
	}
	
	function getLaps(){
		return $this->Laps;
	}
	
	function setLapsMod($laps){
		$this->Laps = $laps;
	}
	
}
?>
