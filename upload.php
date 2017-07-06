<?php
include "act.php";
include "tcx.php";

if(isset($_POST['action']) and $_POST['action'] == 'upload')
{
    if(isset($_FILES['user_file']))
    {
        $files = $_FILES['user_file'];

	$url = $_FILES["user_file"]["tmp_name"]; 
    	$file_act_name = $_FILES["user_file"]["name"];
	}
	$baro=0;
	$fixit=0;
	if(isset($_POST['baro'])) $baro=1;
	if(isset($_POST['fixit'])) $fixit=1;
}else{
	echo "No params";
	exit();
}

$act=simplexml_load_file($url);
$XmlAct = new act2tcx($act);
$XmlTcx = new tcx ( $XmlAct, $baro );
$PrintTcx = $XmlTcx->GetTcx();

	if ($fixit) {
			/*
				TCX
			*/
			foreach ($PrintTcx->Activities->Activity->Lap as $v) {
				foreach ($v->Track->Trackpoint as $c) {
					$cad[] = (int)$c->Cadence;
				}
			}
	
		$i=0;
		foreach ($cad as $k=>$v) {
			$freq[$v]+=1;
			$cad_avg_bef+=$v;
			if ($v>$cad_max_bef) { $cad_max_bef=$v; }
			if ($v>0) $i++;
		}
		$cad_avg_bef=round($cad_avg_bef/$i,0);
		ksort($freq);

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
		
			$k=0;
			foreach ($PrintTcx->Activities->Activity->Lap as $v) {
				foreach ($v->Track->Trackpoint as $c) {
					$c->Cadence=(string)$cad[$k];
					$k++;
				}
			}
	}

$dom = dom_import_simplexml($PrintTcx)->ownerDocument;
$dom->formatOutput = true;
$file_act_name = preg_replace ("/.act/", ".tcx", $file_act_name );

	header('Content-Description: File Transfer');
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename='.$file_act_name );
	echo $dom->saveXML();

?>
