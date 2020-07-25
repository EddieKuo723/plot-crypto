<?php
require __DIR__ . '/vendor/autoload.php';
$parameters = array(
    'host'     => 'redis'
);
$indexarr = array(
	"BTC"=>"Bitcoin",
	"ETH"=>"Ethereum",
	"LTC"=>"Litecoin",
	"XMR"=>"Monero",
	"ZEC"=>"ZCash",
	"BCH"=>"Bitcoin Cash",
	"XRP"=>"Ripple"
);

$client = new Predis\Client($parameters);
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
$value = $client->get('Polo'.$coin);

if (!empty($value)) {
	// Display cache image
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
	$green=imagecolorallocate($im,255,0,0);
	$red = imagecolorallocate($im, 61, 255, 61);
	$grey = imagecolorallocate($im, 140, 137, 126);


	imagefill($im,0,0,$black);

	$today = date("Y-m-d");
	$yesterday = date('Y-m-d',strtotime("-1 days"));
	$timestamp = time() - 60*60*24;
	$url = 'https://poloniex.com/public?command=returnChartData&currencyPair=USDT_'.$coin.'&start='.$timestamp.'&end=9999999999&period=900';

	$jsond = file_get_contents($url);
	$json_a = json_decode($jsond, true);

	foreach ($json_a as $key => $value) {
		$minmax[] =$value['close'];
	}

	$high = max($minmax);
	$low = min($minmax);
	$mid = ($high - $low)/2;

	$height_ratio = 265/($high - $low);
	$width_ratio = 400/count($minmax);

	foreach($minmax as $key=>$value){
		// adjust ratio for image
		$value = $value - $low;
		$adjusted = (($value - $mid))*$height_ratio ;
		$point[]=array(($key)*$width_ratio,271.5-$adjusted);
	}

	$open = $minmax[0];
	$close = end($minmax);

	$change = ($close - $open);
	$change = floor($change * 100) / 100;;

	$highline = $height - $nextButtom -265;
	$lowline = $height - $nextButtom;

	imageline($im,270,$highline,670,$highline,$grey);
	imageline($im,270,$lowline,670,$lowline,$grey);


	for($i=0,$j=count($point);$i<$j-1;$i++){
		// Connect dots twice to draw thick line
		imageline($im,$point[$i][0]+270,$point[$i][1],$point[$i+1][0]+270,$point[$i+1][1],$blue);
		imageline($im,$point[$i][0]+270+1,$point[$i][1],$point[$i+1][0]+270+1,$point[$i+1][1],$blue);
	}

	$url = 'https://poloniex.com/public?command=returnTicker';

	$jsond = file_get_contents($url);
	$json_a = json_decode($jsond, true);
	$header = 'USDT_'.$coin;

	$rate = ($json_a[$header]["percentChange"]*100);
	$rate = floor($rate * 100) / 100;

	$close = $json_a[$header]["last"];
	$high = $json_a[$header]["high24hr"];
	$low = $json_a[$header]["low24hr"];
	$Volume = $json_a[$header]["baseVolume"];


	$font = './font/Roboto-Bold.ttf';
	$fontsize = 36;
	$text = $coin;
	$textX = 30;
	$textY = 70 ;
	imagettftext( $im, $fontsize, 0, $textX, $textY, $white, $font, $text );

	$font = './font/Roboto-Regular.ttf';
	$text = $indexarr[$coin].' (USD)';
	$fontsize = 30;
	$textX = 180;
	$textY = 65;
	imagettftext( $im, $fontsize, 0, $textX, $textY, $white, $font, $text );

	$text = '|';


	$fontsize = 25;
	$textX = 140 ;
	$textY = 65;
	imagettftext( $im, $fontsize, 0, $textX, $textY, $white, $font, $text );

	$close = number_format((float)$close, 2, '.', '');
	$Mfont = './font/Roboto-Medium.ttf';

	$fontsize = 48;
	$textX = 30;
	$textY = 180 ;
	if ($rate == 0) {
		$text = $rate.'%';
		imagettftext( $im, $fontsize, 0, $textX, $textY, $white, $Mfont, $text );
		$text = '$'.$close.'  '.$change;

		$fontsize = 20;
		$textX =  30;
		$textY = 220;
		imagettftext( $im, $fontsize, 0, $textX, $textY, $white, $font, $text );
	}
	elseif ($rate <0) {
		$rate = abs( $rate );
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


	$low = floor($low * 100) / 100;
	$high = floor($high * 100) / 100;

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

	$low = floor($low * 100) / 100;
	$high = floor($high * 100) / 100;

	$low = number_format((float)$low, 2, '.', '');
	$high = number_format((float)$high, 2, '.', '');

	$text = $low.' - '.$high;
	$fontsize = 20;
	$textX =  30;
	$textY = 400;
	imagettftext( $im, $fontsize, 0, $textX, $textY, $white, $font, $text );

	// date_default_timezone_set("Asia/Taipei");
	$fontsize = 16;
	$text =  date('A h:i');
	$textX = 580;
	$textY = 65;
	imagettftext( $im, $fontsize, 0, $textX, $textY, $grey, $font, $text );
	ob_start (); 

	imagejpeg ($im);
	$base64 = ob_get_contents (); 
	ob_end_clean (); 
	$base64 = base64_encode($base64);

	$client->set("Polo".$coin, $base64);
	$client->expire("Polo".$coin, $cache_secs);

	header('Content-type:image/png');
	imagepng($im);
	imagedestroy($im);

}

?>