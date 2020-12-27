<?php
require __DIR__ . '/vendor/autoload.php';

$parameters = array(
    'host'     => 'redis'
);
$client = new Predis\Client($parameters);

$indexarr = array(
	"BTC"=>"Bitcoin",
	"ETH"=>"Ethereum",
	"LTC"=>"Litecoin",
	"XMR"=>"Monero",
	"ZEC"=>"ZCash",
	"BCH"=>"Bitcoin Cash",
	"XRP"=>"Ripple"
);
if ( isset( $_GET['coin'] ) && $_GET['coin'] ) {
	$coin = $_GET['coin'];

	$coin = strtoupper($coin);
	// if input not listed change to BTC
	if (!array_key_exists($coin, $indexarr)){
		$coin ='BTC';
	}
	
}else{
	$coin ='BTC';
}
// get variable from docker setting
$cache_secs = getenv("CACHE_SECS");
$value = $client->get('Binance'.$coin);

if (!empty($value)) {
	// Get Cache
	header('Content-type:image/png');	
	echo base64_decode($value);
}
else
{	
	$width = 700;
	$height = 464;
	$nextRight = 30;
	$nextButtom = 60;

	$im=imagecreatetruecolor(700,464);
	$black=imagecolorallocate($im,38,38,38);
	$white=imagecolorallocate($im,255,255,255);
	$blue =imagecolorallocate($im,101,181,177);
	$blueblue =imagecolorallocate($im,0,130,254);
	// $red=imagecolorallocate($im,255,0,0);
	$green=imagecolorallocate($im,255,0,0);
	$yellow = imagecolorallocate($im, 251, 250, 124);

	// $green = imagecolorallocate($im, 61, 255, 61);
	$red = imagecolorallocate($im, 61, 255, 61);
	$grey = imagecolorallocate($im, 140, 137, 126);

	$graphHeight = 275;
	$graphHeight = 180;
	$nextButtom  = 340-$graphHeight;
	$lowline = $height - $nextButtom;

	imagefill($im,0,0,$black);
	$url = 'https://api.binance.com/api/v3/klines?symbol='.$coin.'USDT&interval=5m&limit=200';

	$jsond = file_get_contents($url);
	$json_a = json_decode($jsond, true);
	$volumeArr = array();

	foreach ($json_a as $key => $value) {
		$minmax[] =$value[4];
		$volumeArr[] = $value[5];
	}
	// ========== Upper graph ==================
	$high = max($minmax);
	$low  = min($minmax);
	$mid  = ($high - $low)/2;
	$height_ratio = 180/($high - $low);
	$width_ratio = 400/200;

	foreach($minmax as $key=>$value){
		$adjusted = $lowline - (($value - $low))*$height_ratio ;
		$point[]=array(($key)*$width_ratio,$adjusted);	
	}

	$highline = $height - $nextButtom -$graphHeight;
	$lowline = $height - $nextButtom;
	$deepline = $height - 60;
	$graph_buttom = 270;

	imageline($im,$graph_buttom,$highline-5,670,$highline-5,$grey);
	imageline($im,$graph_buttom,$lowline+5,670,$lowline+5,$grey);

	for($i=0,$j=count($point);$i<$j-1;$i++){
		imageline($im,$point[$i][0]+$graph_buttom,$point[$i][1],$point[$i+1][0]+$graph_buttom,$point[$i+1][1],$blue);
		$values = array(
			$point[$i][0]  +$graph_buttom,  $point[$i][1],   // Point 1 (x, y)
			$point[$i+1][0]+$graph_buttom,  $point[$i+1][1], // Point 2 (x, y)
			$point[$i+1][0]+$graph_buttom,  $lowline,        // Point 3 (x, y)
			$point[$i][0]  +$graph_buttom,  $lowline         // Point 4 (x, y)
		);
		// Make blue cover under chart line
		imagefilledpolygon($im, $values, 4, $blueblue);
	}
	// ============= lower graph =========================

	$maxVol = max($volumeArr);
	if ($maxVol == 0) {
		$maxVol = 1;
	}
	$height_ratio = (265-$graphHeight)/$maxVol;

	$tmpValue = $volumeArr[0];	
	foreach ($volumeArr as $key => $value) {
		imageline($im,$key*$width_ratio+270 ,$deepline ,$key*$width_ratio+270,$deepline - $value*$height_ratio ,$yellow);
		imageline($im,$key*$width_ratio+270+1 ,$deepline ,$key*$width_ratio+270+1,$deepline - $value*$height_ratio ,$yellow);
	}

	
	$url = 'https://api.binance.com/api/v3/ticker/24hr?symbol='.$coin.'USDT';

	$jsond = file_get_contents($url);
	$json_a = json_decode($jsond, true);

	$rate = $json_a["priceChangePercent"];
	$close = $json_a['lastPrice'];
	$high = $json_a['highPrice'];
	$low = $json_a['lowPrice'];
	$Volume = $json_a['volume'];
	$change = $json_a['priceChange'];

	$font = './font/Roboto-Bold.ttf';
	$boldfont = './font/Roboto-Bold.ttf';

	$fontsize = 36;
	$text = $coin.'/USDT';
	$textX = 30;
	$textY = 70 ;
	imagettftext( $im, $fontsize, 0, $textX, $textY, $white, $font, $text );

	$font = './font/Roboto-Regular.ttf';


	$text = '|';
	$fontsize = 25;
	$textX = 140 ;
	$textY = 65;
	// imagettftext( $im, $fontsize, 0, $textX, $textY, $white, $font, $text );

	$close = number_format((float)$close, 2, '.', '');
	$change = number_format((float)$change, 2, '.', '');

	$Mfont = './font/Roboto-Medium.ttf';

	$fontsize = 48;
	$textX = 30;
	$textY = 180 ;
	if ($rate == 0) {
		# code...
		$text = $rate.'%';
		imagettftext( $im, $fontsize, 0, $textX, $textY, $white, $Mfont, $text );
		$text = '$'.$close.'  '.$change;

		$fontsize = 20;
		$textX =  30;
		$textY = 220;
		imagettftext( $im, $fontsize, 0, $textX, $textY, $white, $font, $text );
	}
	elseif ($rate <0) {
		# code...
		$rate = abs( $rate );
		#$rate = floor($rate * 100) / 100;
		$rate = number_format((float)$rate, 2, '.', '');

		$text = $rate.'%';
		imagettftext( $im, $fontsize, 0, $textX, $textY, $green, $Mfont, $text );

		$text = '$'.$close.'  '.$change;
		$fontsize = 20;
		$textX =  30;
		$textY = 220;
		imagettftext( $im, $fontsize, 0, $textX, $textY, $green, $font, $text );
	}
	else{
		$rate = number_format((float)$rate, 2, '.', '');
		$text =  $rate .'%';
		imagettftext( $im, $fontsize, 0, $textX, $textY, $red, $Mfont, $text );

		$text = '$'.$close.'  '.$change;
		$fontsize = 20;
		$textX =  30;
		$textY = 220;
		imagettftext( $im, $fontsize, 0, $textX, $textY, $red, $font, $text );
	}


	$text = 'Volume';
	$fontsize = 20;
	$textX =  30;
	$textY = 280 ;
	imagettftext( $im, $fontsize, 0, $textX, $textY, $grey, $font, $text );


	$text = number_format($Volume, 3, '.' ,',');
	$fontsize = 20;
	$textX =  30;
	$textY = 315;
	imagettftext( $im, $fontsize, 0, $textX, $textY, $white, $font, $text );



	$text = '1 Day Range';
	$fontsize = 20;
	$textX =  30;
	$textY = 365;
	imagettftext( $im, $fontsize, 0, $textX, $textY, $grey, $font, $text );


	// $low = floor($low * 100) / 100;
	// $high = floor($high * 100) / 100;


	$low = number_format((float)$low, 2, '.', '');
	$high = number_format((float)$high, 2, '.', '');

	$text = $low.' - '.$high;
	$fontsize = 18;
	$textX =  30;
	$textY = 400;
	imagettftext( $im, $fontsize, 0, $textX, $textY, $white, $font, $text );

	
	
	$coords = imagettfbbox( 18, 0, $boldfont, $high );
	imagettftext( $im, 18, 0, 665-$coords[4], $highline-7, $white , $boldfont,  $high);

	$coords = imagettfbbox( 18, 0, $boldfont, $low );
	imagettftext( $im, 18, 0, 665-$coords[4], $lowline-2, $white, $boldfont,  $low);

	date_default_timezone_set("Asia/Taipei");
	$fontsize = 16;
	$text =  date('A h:i');
	#$textBox = imagettfbbox_t($fontsize, 0, $font, $text);
	$textX = 580;
	$textY = 65;
	imagettftext( $im, $fontsize, 0, $textX, $textY, $grey, $font, $text );
	#$base64 = base64_encode(imagejpeg($im));
	ob_start (); 

	imagepng ($im);
	$base64 = ob_get_contents (); 
	ob_end_clean (); 
	$base64 = base64_encode($base64);

	$client->set("Binance".$coin, $base64);
	$client->expire("Binance".$coin, $cache_secs);

	header('Content-type:image/png');
	// header("Cache-Control: private, max-age=3600");
	imagepng($im);
	imagedestroy($im);

}

?>