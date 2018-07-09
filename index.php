<?php
/*
	*** UI ***
	Copyright (C) 2017 Julian Cenkier <julian.cenkier@wp.eu>
*/

$version = "2.0";

include "act.php";
include "tcx.php";
include "gpx.php";

if(isset($_POST['action']) and $_POST['action'] == 'upload')
{
    if(isset($_FILES['user_file']))
    {
        $files = $_FILES['user_file'];
		$url = $_FILES["user_file"]["tmp_name"]; 
    	$file_act_name = $_FILES["user_file"]["name"];
	}
	
	$type=1;
	if(isset($_POST['baro'])) $baro=(int)$_POST['baro'];
	if(isset($_POST['fixit'])) $fixit=(int)$_POST['fixit'];
	if(isset($_POST['indoor'])) $indoor=(int)$_POST['indoor'];
	//if(isset($_POST['power'])) $power=(int)$_POST['power'];
	if(isset($_POST['fileformat'])) $type=(int)$_POST['fileformat'];
	if(isset($_POST['inlaps'])) $inlaps=(int)$_POST['inlaps'];
	if(isset($_POST['strava'])) $strava=(int)$_POST['strava'];

	$act=simplexml_load_file($url);
	$XmlAct = new act ( $act );

	if ($fixit) {
		
		$total=$XmlAct->getTracks();
		for ( $i = 0; $i < $total; $i++ ) {
			$cad[$i]=(int)$XmlAct->getCadenceTrack($i);
		}
		
	if (max($cad)>0) {
		/*
			findTripple and zero
			
			search 3x255, np.
			255,255,255,0
			130,130,130,0
			...
		*/
		foreach ($cad as $k=>$val) {
		$z=$cad[$k-1];
		$a=$val;
		$b=$cad[$k+1];
		$c=$cad[$k+2];
		$d=$cad[$k+3];
			if ($a>0 && $b>0 && $c>0 && $z>0) {
				if ($b==$a && $c==$a && $d==0) {
				$cor_a[$k]="*A-255";
				}
			}
		}
		if (count($cor_a)){
		foreach ($cor_a as $k=>$val) {
			$z=$cad[$k-1];
			$a=$val;
			$b=$cad[$k+1];
			$c=$cad[$k+2];
				$a=round($z/2,0);
				$b=round($a/2,0);
				$c=round($b/2,0);
			$cad[$k]=$a;
			$cad[$k+1]=$b;
			$cad[$k+2]=$c;
		}
		}
		/*
			search only 255
		*/
		foreach ($cad as $k=>$val) {
		$z=$cad[$k-1];
		$a=$val;
		$b=$cad[$k+1];
		$c=$cad[$k+2];
			if ($a==255) {
				$cor_b[$k]="*B-255";
				if ($b==255) {
					$cor_b[$k+1]="*B";
					$i=round(abs($z-$c)/3,0);
					if ($z>$c) {
						$a = $z-$i;
						$b = $a-$i;
					}else{
						$a = $z+$i;
						$b = $a+$i;						
					}
				}else{
					$i=round(abs($z-$b)/2,0);
					$a = $z>$b?$z-$i:$z+$i;
				}
					$cad[$k]=$a;
					$cad[$k+1]=$b;
			}
		}
		/*
			Additional anomalies
			unfavorable values and sudden jumps
		*/
		foreach ($cad as $k=>$val) {
		$z=$cad[$k-1];
		$a=$val;
		$b=$cad[$k+1];
		$c=$cad[$k+2];
		$d=$cad[$k+3];
		$at = $a*1.1;
			if ($a>0 && $at<$b && ($c<$b || $d<$c)) {
				$cor_c[$k+1]="*C-".$b;
				if ($b==$c) {
					$cor_c[$k+2]="*C-".$c;
					$i=round(abs($a-$d)/3,0);
					if ($a>$d) {
						$b = $a-$i;
						$c = $b-$i;
					}else{
						$b = $a+$i;
						$c = $b+$i;						
					}				
				}else{
					$i=round(abs($a-$c)/2,0);
					$b = $a>$c?$a-$i:$a+$i;
				}
					$cad[$k+1]=$b;
					$cad[$k+2]=$c;
			}
		}
		/*
			Search for triggers beginning or ending non-zero
		*/
		foreach ($cad as $k=>$val) {
		$z=$cad[$k-1];
		$a=$val;
		$b=$cad[$k+1];
		$c=$cad[$k+2];
		$d=$cad[$k+3];
			if ( ( $a==$b && $a==$c && $b==$c && (($a+$b+$c)>0) ) && ($z>0 || $d>0) && ($z<=$a || $d<=$c) && $a!=$d) {
				$i=round(abs($z-$d)/4,0);
				if ( ($z<$d && ($d-$i)<$c) || ($z>$d && ($z-$i)<$a) ) {
				//	if ( round($d*1.1,0)>=$c || round($z*1.1,0)>=$a ) {
				$cor_d[$k]="*D-".$a;
				$cor_d[$k+1]="*D";
				$cor_d[$k+2]="*D";
				if ($z<$d) {
					$a=$z+$i;
					$b=$a+$i;
					$c=$b+$i;
				}else{
					$a=$z-$i;
					$b=$a-$i;
					$c=$b-$i;				
				}
					
				$cad[$k]=$a;
				$cad[$k+1]=$b;
				$cad[$k+2]=$c;
				//	}
				}
			}
		}
		/*
			Additional anomalies second pass
			unfavorable values and sudden jumps
		*/
		foreach ($cad as $k=>$val) {
		$z=$cad[$k-1];
		$a=$val;
		$b=$cad[$k+1];
		$c=$cad[$k+2];
		$d=$cad[$k+3];
		$at = $a*1.1;
			if ($a>0 && $at<$b && ($c<$b || $d<$c)) {
				$cor_e[$k+1]="*E-".$b;
				if ($b==$c) {
					$cor_e[$k+2]="*E-".$c;
					$i=round(abs($a-$d)/3,0);
					if ($a>$d) {
						$b = $a-$i;
						$c = $b-$i;
					}else{
						$b = $a+$i;
						$c = $b+$i;						
					}				
				}else{
					$i=round(abs($a-$c)/2,0);
					$b = $a>$c?$a-$i:$a+$i;
				}
					$cad[$k+1]=$b;
					$cad[$k+2]=$c;
			}
		}
		/*
			Repair start
		*/
		$k=0;
		$i=1;
		$a=$cad[$k];
		$b=$cad[$k+1];
		$c=$cad[$k+2];
		$d=$cad[$k+3];
		if ($a==$b) {
			$i++;
			if ($b==$c) {
				$i++;
				if ($c==$d) {
					$i++;
				}
			}
		}
		$z=$cad[$i];
		$i--;
		while ($i>=0) {
			$z=round($z/2,0);
			if ($i==0) $z=0;
			$cor_f[$i]="*F-".$cad[$i];
			$cad[$i]=$z;
			$i--;
		}
		
		$cad_fixed=(count($cor_a)*3)+count($cor_b)+count($cor_c)+count($cor_d)+count($cor_e)+count($cor_f);
		
		/*
			renew Cadence table and it's avg and max
		*/
			$XmlAct->setMaxCadenceVal( max($cad) );
			$cadavg = 0;
			$i=1;
			foreach ($cad as $v) {
				if ($v) {
					// all none zero cad counts
					$cadavg += (int)$v;
					$i++;
				}
			}
			$XmlAct->setAvgCadenceVal( number_format($cadavg/$i,0,'','') );
			for ( $i = 0; $i < $total; $i++ ) {
				$XmlAct->setCadenceTrack($i, $cad[$i]);
			}
			
			$laps = $XmlAct->getNoOfLaps();
			// are laps?
			if ($laps>1){
				$l = $XmlAct->getLaps();
				$s=0;
				for ( $i=0; $i < $laps; $i++ ) {
					$e = $l[$i]['lap'];
					$y=0;
					$cadlap = array();
					for ($x=$s; $x <= $e; $x++) {
						if ($cad[$x]){
							$cadlap[] = (int)$cad[$x];
							$y++;
						}
					}
					$l[$i]['cadavg'] = number_format( round(array_sum($cadlap)/$y) ,0,'','' );
					$l[$i]['cadmax'] = max($cadlap);
					$s = $e+1;
				}
				$XmlAct->setLapsMod($l);
			}
	}
	}
	
	if ($type) {
		$XmlTcx = new tcx ( $XmlAct, $baro, $indoor, $inlaps, $strava );
		$xml = $XmlTcx->GetTcx();
	}else{
		$XmlGpx = new gpx ( $XmlAct, $baro );
		$xml = $XmlGpx->GetGpx();
	}
	
	$dom = dom_import_simplexml($xml)->ownerDocument;
	$dom->formatOutput = false;
	$file_act_name = str_replace (".act", ($type?".tcx":".gpx"), $file_act_name );

	header('Content-Description: File Transfer');
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename='.$file_act_name );
	echo $dom->saveXML();
		
	exit();
}
?>
<html>
<head>
	<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" />
	<script type="text/javascript" src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
	<script type="text/javascript" src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

	<style type="text/css">
	body{width: 100%;height: 100%}
	.row{display: flex;justify-content: center; align-items: center;height:100%}
	.row>div{text-align:center}
	h3, .color{color:#3cd}
	span.label{line-height:2;position: relative;top: -.2em}
	.inputfile{width:.1px;height:.1px;opacity:0;overflow:hidden;position:absolute;z-index:-1}
	.inputfile-1 + label{color:#fff;background-color:#3cd}
	.inputfile + label{max-width:80%;font-size:1.25rem;font-weight:700;text-overflow:ellipsis;white-space:nowrap;cursor:pointer;display:inline-block;overflow:hidden;padding:.625rem 1.25rem;margin-bottom:1.5rem}
	.inputfile + label svg{width:1em;height:1em;vertical-align:middle;fill:currentColor;margin-top:-.25em;margin-right:.25em}
	.txt-normal{font-weight:normal}
	.btn-default.btn-on.active, .btn-default.btn-off.active{background-color: #3cd;color: white}	
	.btn-switch .btn-default.btn-off.active{background-color: #777}
	.btn-switch.small label:not([class*="btn"]) {font-size:0.85em}
	.btn-switch.small label[class*="btn"] {font-size:0.7em}
	#footer{margin:auto}
	.txt-normal{font-weight:normal}
	.btn-default.btn-on.active, .btn-default.btn-off.active{background-color: #3cd;color: white}	
	.btn-switch .btn-default.btn-off.active{background-color: #777}
	.btn-switch-grp .btn-switch{margin-bottom:10px}
	.btn-switch.small{margin-bottom:5px}
	.btn-switch-grp{margin:20px 0 15px 0}
	</style>
	<script type="text/javascript">
	var ff = {
		labelval : 'Choose a file...',
		input:"file-1",
		labelchange : function(label){
			$('label[for="'+ff.input+'"] span').html(label);
		}
	};
	
		$(function(){
		ff.labelchange(ff.labelval);
			$('#'+ff.input).on('change', function(){
				var file = document.forms['form'][ff.input].files[0];
				//file.name == "photo.png"
				//file.type == "image/png"
				//file.size == 300821
				ff.labelchange(file.name);
				$('input[type="submit"]').removeClass('hidden');
				$('input[type="reset"]').removeClass('hidden');
			});
			$('input[type="reset"]').on('click', function(){
				$('input[type="submit"]').addClass('hidden');
				$('input[type="reset"]').addClass('hidden');
				ff.labelchange(ff.labelval);
			});
		});
	</script>
	<title>ACT : Convert ACT ( GlobalSite ) to TCX/GPX ( Garmin)</title>
</head>
<body>
	<div class="container">
	<div class="row">
	<div class="col-xs-12">
	<h3>ACT to TCX/GPX <span class="badge"><?=$version?></span></h3>
	<p class="small">ACT <span class="label label-default">GlobalSite</span> convert to TCX/GPX <span class="label label-default">Garmin</span></p>
	<hr />
		<form id="form" method="post" action="index.php" enctype="multipart/form-data">
			<input type="hidden" name="action" value="upload" />

			<div class="form-group">
			<div class="btn-group" data-toggle="buttons">
				<label class="btn btn-default btn-on btn-lg active">
				<input type="radio" name="fileformat" value="1"  checked="checked" />TCX</label>
				<label class="btn btn-default btn-off btn-lg ">
				<input type="radio" name="fileformat" value="0" />GPX</label>
            </div>
            </div>
            
            <div class="btn-switch-grp">
<!--
			<div class="form-group btn-switch">
			<div class="btn-group" data-toggle="buttons">
				<label class="txt-normal">&nbsp;calculate power</label>
				<label class="btn btn-default btn-off btn-xs active">
				<input type="radio" name="power" value="0"  checked="checked" />OFF</label>
				<label class="btn btn-default btn-on btn-xs ">
				<input type="radio" name="power" value="1" />ON</label>
            </div>
            </div>
 -->			
			<div class="form-group btn-switch">
			<div class="btn-group" data-toggle="buttons">
				<label class="txt-normal">&nbsp;indoor workout</label>
				<label class="btn btn-default btn-off btn-xs active">
				<input type="radio" name="indoor" value="0"  checked="checked" />NO</label>
				<label class="btn btn-default btn-on btn-xs ">
				<input type="radio" name="indoor" value="1" />YES</label>
            </div>
            </div>
            
            <div class="form-group btn-switch">
			<div class="btn-group" data-toggle="buttons">
				<label class="txt-normal">&nbsp;include laps</label>
				<label class="btn btn-default btn-off btn-xs ">
				<input type="radio" name="inlaps" value="0" />NO</label>
				<label class="btn btn-default btn-on btn-xs active">
				<input type="radio" name="inlaps" value="1" checked="checked" />YES</label>
            </div>
            </div>
            		
			<div class="form-group btn-switch small">
			<div class="btn-group" data-toggle="buttons">
				<label class="txt-normal">&nbsp;with baromater</label>
				<label class="btn btn-default btn-off btn-xs ">
				<input type="radio" name="baro" value="0" />NO</label>
				<label class="btn btn-default btn-on btn-xs active">
				<input type="radio" name="baro" value="1" checked="checked" />YES</label>
            </div>
            </div>

			<div class="form-group btn-switch small">
			<div class="btn-group" data-toggle="buttons">
				<label class="txt-normal">&nbsp;fix cadence</label>
				<label class="btn btn-default btn-off btn-xs ">
				<input type="radio" name="fixit" value="0" />NO</label>
				<label class="btn btn-default btn-on btn-xs active">
				<input type="radio" name="fixit" value="1"  checked="checked" />YES</label>
            </div>
            </div>
            
            <div class="form-group btn-switch small">
			<div class="btn-group" data-toggle="buttons">
				<label class="txt-normal">&nbsp;fix strava distance</label>
				<label class="btn btn-default btn-off btn-xs ">
				<input type="radio" name="strava" value="0" />NO</label>
				<label class="btn btn-default btn-on btn-xs active">
				<input type="radio" name="strava" value="1"  checked="checked" />YES</label>
            </div>
            </div>
     
            </div>
			<input id="file-1" class="inputfile inputfile-1" type="file" name="user_file" />
				<label for="file-1" class="btn btn-default"><svg xmlns="#" width="20" height="17" viewBox="0 0 20 17">
					<path d="M10 0l-5.2 4.9h3.3v5.1h3.8v-5.1h3.3l-5.2-4.9zm9.3 11.5l-3.2-2.1h-2l3.4 2.6h-3.5c-.1 0-.2.1-.2.1l-.8 2.3h-6l-.8-2.2c-.1-.1-.1-.2-.2-.2h-3.6l3.4-2.6h-2l-3.2 2.1c-.4.3-.7 1-.6 1.5l.6 3.1c.1.5.7.9 1.2.9h16.3c.6 0 1.1-.4 1.3-.9l.6-3.1c.1-.5-.2-1.2-.7-1.5z"></path>
					</svg>&nbsp;<span></span></label>
					<br />
			<input class="btn btn-default hidden" type="reset" value="Clear" />
			<input class="btn btn-success hidden" type="submit" value="Convert" />
		</form>
		<hr />
		<div id="footer" class="small">powered by <a class="btn btn-default btn-xs" href="http://salsan.github.io/act/">ACT</a> and <a class="btn btn-default btn-xs" href="https://github.com/exa18/act/tree/update-2.0">GitHub update</a></div>
	</div>
	</div>
	</div>
</body>
</html>

