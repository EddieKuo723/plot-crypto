<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);

if ( isset( $_GET['type'] ) && $_GET['type'] ) {
	$type = $_GET['type'];
	$type = strtoupper($type);
	
}else{
	$type ='BTC';
}

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
	$nextButtom = 340-$graphHeight;
	$lowline = $height - $nextButtom;

	imagefill($im,0,0,$black);
	$url = 'https://api.binance.com/api/v3/klines?symbol='.$type.'USDT&interval=5m&limit=200';

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	$jsond = curl_exec($ch);
	curl_close($ch);
	$json_a = json_decode($jsond, true);

	if (!is_array($json_a) || isset($json_a['code'])) {
		die('Binance klines API error: ' . ($jsond ?: 'empty response'));
	}
	$volumeArr = array();

	foreach ($json_a as $key => $value) {
		$minmax[] =$value[4];
		$volumeArr[] = $value[5];
	}
	// ========== Upper graph ==================
	$high = max($minmax);
	$low = min($minmax);
	$mid = ($high - $low)/2;
	$height_ratio = 180/($high - $low);
	$width_ratio = 400/200;

	foreach($minmax as $key=>$value){
		$adjusted = $lowline - (($value - $low))*$height_ratio ;
		$point[]=array(($key)*$width_ratio,$adjusted);	
	}

	$highline = $height - $nextButtom -$graphHeight;
	$lowline = $height - $nextButtom;
	$deepline = $height - 60;

	imageline($im,270,$highline-5,670,$highline-5,$grey);
	imageline($im,270,$lowline+5,670,$lowline+5,$grey);

	for($i=0,$j=count($point);$i<$j-1;$i++){//连接前后坐标
		imageline($im,$point[$i][0]+270,$point[$i][1],$point[$i+1][0]+270,$point[$i+1][1],$blue);
		$values = array(
			$point[$i][0]+270,  $point[$i][1],  // Point 1 (x, y)
			$point[$i+1][0]+270,  $point[$i+1][1], // Point 2 (x, y)
			$point[$i+1][0]+270,  $lowline,  // Point 3 (x, y)
			$point[$i][0]+270, $lowline  // Point 4 (x, y)
		);
		imagefilledpolygon($im, $values, $blueblue);
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

	
	$url = 'https://api.binance.com/api/v3/ticker/24hr?symbol='.$type.'USDT';
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	$jsond = curl_exec($ch);
	curl_close($ch);
	$json_a = json_decode($jsond, true);

	if (!is_array($json_a) || isset($json_a['code'])) {
		die('Binance ticker API error: ' . ($jsond ?: 'empty response'));
	}

	$rate = $json_a["priceChangePercent"];
	$close = $json_a['lastPrice'];
	$high = $json_a['highPrice'];
	$low = $json_a['lowPrice'];
	$Volume = $json_a['volume'];
	$change = $json_a['priceChange'];

	

	$font = './font/Roboto-Bold.ttf';
	$boldfont = './font/Roboto-Bold.ttf';

	$fontsize = 36;
	$text = $type.'/USDT';

	#$textX = 35;
	#$textY = 70;
	$textX = 30;
	$textY = 70 ;
	imagettftext( $im, $fontsize, 0, $textX, $textY, $white, $font, $text );

	$font = './font/Roboto-Regular.ttf';


	$text = '|';
	$fontsize = 25;
	$textX = 140 ;
	$textY = 65;
	// imagettftext( $im, $fontsize, 0, $textX, $textY, $white, $font, $text );

	if ($close > 100) {
		$dec = 2;
	}
	elseif ($close > 10) {
		$dec = 3;
	}
	else{
		$dec = 5;
	}

	$close = number_format((float)$close, $dec, '.', '');
	$change = number_format((float)$change, $dec, '.', '');

	$Mfont = './font/Roboto-Medium.ttf';

	$fontsize = 48;
	$textX = 30;
	$textY = 180 ;

	$price_font_size = 20;
	$price_text = '$'.$close.'  '.$change;
	$bbox = imagettfbbox($price_font_size, 0, $font, $price_text);
	$text_width = $bbox[2] - $bbox[0];

	while ($text_width > 235) {
		$price_font_size--;
		$bbox = imagettfbbox($price_font_size, 0, $font, $price_text);
		$text_width = $bbox[2] - $bbox[0];
	}

	if ($rate == 0) {
		# code...
		$text = $rate.'%';
		imagettftext( $im, $fontsize, 0, $textX, $textY, $white, $Mfont, $text );

		$textX =  30;
		$textY = 220;
		imagettftext( $im, $price_font_size, 0, $textX, $textY, $white, $font, $price_text );
	}
	elseif ($rate <0) {
		# code...
		$rate = abs( $rate );
		#$rate = floor($rate * 100) / 100;
		$rate = number_format((float)$rate, 2, '.', '');

		$text = $rate.'%';
		imagettftext( $im, $fontsize, 0, $textX, $textY, $green, $Mfont, $text );

		$textX =  30;
		$textY = 220;
		imagettftext( $im, $price_font_size, 0, $textX, $textY, $green, $font, $price_text );
	}
	else{
		$rate = number_format((float)$rate, 2, '.', '');
		$text =  $rate .'%';
		imagettftext( $im, $fontsize, 0, $textX, $textY, $red, $Mfont, $text );

		$textX =  30;
		$textY = 220;
		imagettftext( $im, $price_font_size, 0, $textX, $textY, $red, $font, $price_text );
	}


	$text = 'Volume';
	$fontsize = 20;
	$textX =  30;
	$textY = 280 ;
	imagettftext( $im, $fontsize, 0, $textX, $textY, $grey, $font, $text );


	$text = number_format($Volume, $dec, '.' ,',');
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


	$low = number_format((float)$low, $dec, '.', '');
	$high = number_format((float)$high, $dec, '.', '');

	$text = $low.' - '.$high;
	$fontsize = 18;
	$textX =  30;
	$textY = 400;
	// Initial font size
	$font_size = 20;

	// Calculate the width of the text
	$bbox = imagettfbbox($font_size, 0, $font, $text);
	$text_width = $bbox[2] - $bbox[0];

	while ($text_width > 235) {
		$font_size--;
		$bbox = imagettfbbox($font_size, 0, $font, $text);
		$text_width = $bbox[2] - $bbox[0];
	}


	imagettftext($im, $font_size, 0, $textX, $textY, $white, $font, $text);
	
	
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

	// header("cache-control: public");
	header('Content-type:image/png');

	header("Cache-Control: max-age=184, public");

	imagepng($im);
	imagedestroy($im);

?>