<?php

class bmp {

var $mpdf = null;

function bmp(&$mpdf) {
	$this->mpdf = $mpdf;
}


function _getBMPimage($data, $file) {
	$info = array();
		// Adapted from script by Valentin Schmidt
		// http://staff.dasdeck.de/valentin/fpdf/fpdf_bmp/
		$bfOffBits=$this->_fourbytes2int_le(substr($data,10,4));
		$width=$this->_fourbytes2int_le(substr($data,18,4));
		$height=$this->_fourbytes2int_le(substr($data,22,4));
		$flip = ($height<0);
		if ($flip) $height =-$height;
		$biBitCount=$this->_twobytes2int_le(substr($data,28,2));
		$biCompression=$this->_fourbytes2int_le(substr($data,30,4)); 
		$info = array('w'=>$width, 'h'=>$height);
		if ($biBitCount<16){
			$info['cs'] = 'Indexed';
			$info['bpc'] = $biBitCount;
			$palStr = substr($data,54,($bfOffBits-54));
			$pal = '';
			$cnt = strlen($palStr)/4;
			for ($i=0;$i<$cnt;$i++){
				$n = 4*$i;
				$pal .= $palStr[$n+2].$palStr[$n+1].$palStr[$n];
			}
			$info['pal'] = $pal;
		}
		else{
			$info['cs'] = 'DeviceRGB';
			$info['bpc'] = 8;
		}

		if ($this->mpdf->restrictColorSpace==1 || $this->mpdf->PDFX || $this->mpdf->restrictColorSpace==3) {
			if (($this->mpdf->PDFA && !$this->mpdf->PDFAauto) || ($this->mpdf->PDFX && !$this->mpdf->PDFXauto)) { $this->mpdf->PDFAXwarnings[] = "Image cannot be converted to suitable colour space for PDFA or PDFX file - ".$file." - (Image replaced by 'no-image'.)"; }
			return array('error' => "BMP Image cannot be converted to suitable colour space - ".$file." - (Image replaced by 'no-image'.)"); 
		}

		$biXPelsPerMeter=$this->_fourbytes2int_le(substr($data,38,4));	// horizontal pixels per meter, usually set to zero
		//$biYPelsPerMeter=$this->_fourbytes2int_le(substr($data,42,4));	// vertical pixels per meter, usually set to zero
		$biXPelsPerMeter=round($biXPelsPerMeter/1000 *25.4);
		//$biYPelsPerMeter=round($biYPelsPerMeter/1000 *25.4);
		$info['set-dpi'] = $biXPelsPerMeter; 

		switch ($biCompression){
		  case 0:
			$str = substr($data,$bfOffBits);
			break;
		  case 1: # BI_RLE8
			$str = $this->rle8_decode(substr($data,$bfOffBits), $width);
			break;
		  case 2: # BI_RLE4
			$str = $this->rle4_decode(substr($data,$bfOffBits), $width);
			break;
		}
		$bmpdata = '';
		$padCnt = (4-ceil(($width/(8/$biBitCount)))%4)%4;
		switch ($biBitCount){
		  case 1:
		  case 4:
		  case 8:
			$w = floor($width/(8/$biBitCount)) + ($width%(8/$biBitCount)?1:0);
			$w_row = $w + $padCnt;
			if ($flip){
				for ($y=0;$y<$height;$y++){
					$y0 = $y*$w_row;
					for ($x=0;$x<$w;$x++)
						$bmpdata .= $str[$y0+$x];
				}
			}else{
				for ($y=$height-1;$y>=0;$y--){
					$y0 = $y*$w_row;
					for ($x=0;$x<$w;$x++)
						$bmpdata .= $str[$y0+$x];
				}
			}
			break;

		  case 16:
			$w_row = $width*2 + $padCnt;
			if ($flip){
				for ($y=0;$y<$height;$y++){
					$y0 = $y*$w_row;
					for ($x=0;$x<$width;$x++){
						$n = (ord( $str[$y0 + 2*$x + 1])*256 +    ord( $str[$y0 + 2*$x]));
						$b = ($n & 31)<<3; $g = ($n & 992)>>2; $r = ($n & 31744)>>7128;
						$bmpdata .= chr($r) . chr($g) . chr($b);
					}
				}
			}else{
				for ($y=$height-1;$y>=0;$y--){
					$y0 = $y*$w_row;
					for ($x=0;$x<$width;$x++){
						$n = (ord( $str[$y0 + 2*$x + 1])*256 +    ord( $str[$y0 + 2*$x]));
						$b = ($n & 31)<<3; $g = ($n & 992)>>2; $r = ($n & 31744)>>7;
						$bmpdata .= chr($r) . chr($g) . chr($b);
					}
				}
			}
			break;

		  case 24:
		  case 32:
			$byteCnt = $biBitCount/8;
			$w_row = $width*$byteCnt + $padCnt;

			if ($flip){
				for ($y=0;$y<$height;$y++){
					$y0 = $y*$w_row;
					for ($x=0;$x<$width;$x++){
						$i = $y0 + $x*$byteCnt ; # + 1
						$bmpdata .= $str[$i+2].$str[$i+1].$str[$i];
					}
				}
			}else{
				for ($y=$height-1;$y>=0;$y--){
					$y0 = $y*$w_row;
					for ($x=0;$x<$width;$x++){
						$i = $y0 + $x*$byteCnt ; # + 1
						$bmpdata .= $str[$i+2].$str[$i+1].$str[$i];
					}
				}
			}
			break;

		  default:
			return array('error' => 'Error parsing BMP image - Unsupported image biBitCount'); 
		}
		if ($this->mpdf->compress) {
			$bmpdata=gzcompress($bmpdata);
			$info['f']='FlateDecode';
		} 
		$info['data']=$bmpdata;
		$info['type']='bmp';
		return $info;
}

function _fourbytes2int_le($s) {
	//Read a 4-byte integer from string
	return (ord($s[3])<<24) + (ord($s[2])<<16) + (ord($s[1])<<8) + ord($s[0]);
}

function _twobytes2int_le($s) {
	//Read a 2-byte integer from string
	return (ord(substr($s, 1, 1))<<8) + ord(substr($s, 0, 1));
}


# Decoder for RLE8 compression in windows bitmaps
# see http://msdn.microsoft.com/library/default.asp?url=/library/en-us/gdi/bitmaps_6x0u.asp
function rle8_decode ($str, $width){
    $lineWidth = $width + (3 - ($width-1) % 4);
    $out = '';
    $cnt = strlen($str);
    for ($i=0;$i<$cnt;$i++){
        $o = ord($str[$i]);
        switch ($o){
            case 0: # ESCAPE
                $i++;
                switch (ord($str[$i])){
                    case 0: # NEW LINE
                         $padCnt = $lineWidth - strlen($out)%$lineWidth;
                        if ($padCnt<$lineWidth) $out .= str_repeat(chr(0), $padCnt); # pad line
                        break;
                    case 1: # END OF FILE
                        $padCnt = $lineWidth - strlen($out)%$lineWidth;
                        if ($padCnt<$lineWidth) $out .= str_repeat(chr(0), $padCnt); # pad line
                         break 3;
                    case 2: # DELTA
                        $i += 2;
                        break;
                    default: # ABSOLUTE MODE
                        $num = ord($str[$i]);
                        for ($j=0;$j<$num;$j++)
                            $out .= $str[++$i];
                        if ($num % 2) $i++;
             }
                break;
            default:
                $out .= str_repeat($str[++$i], $o);
        }
    }
    return $out;
}

# Decoder for RLE4 compression in windows bitmaps
# see http://msdn.microsoft.com/library/default.asp?url=/library/en-us/gdi/bitmaps_6x0u.asp
function rle4_decode ($str, $width){
    $w = floor($width/2) + ($width % 2);
    $lineWidth = $w + (3 - ( ($width-1) / 2) % 4);    
    $pixels = array();
    $cnt = strlen($str);
    for ($i=0;$i<$cnt;$i++){
        $o = ord($str[$i]);
        switch ($o){
            case 0: # ESCAPE
                $i++;
                switch (ord($str[$i])){
                    case 0: # NEW LINE                        
                        while (count($pixels)%$lineWidth!=0)
                            $pixels[]=0;
                        break;
                    case 1: # END OF FILE
                        while (count($pixels)%$lineWidth!=0)
                            $pixels[]=0;
                        break 3;
                    case 2: # DELTA
                        $i += 2;
                        break;
                    default: # ABSOLUTE MODE
                        $num = ord($str[$i]);
                        for ($j=0;$j<$num;$j++){
                            if ($j%2==0){
                                $c = ord($str[++$i]);
                              $pixels[] = ($c & 240)>>4;
                             } else
                              $pixels[] = $c & 15;
                        }
                        if ($num % 2) $i++;
             }
                break;
            default:
                $c = ord($str[++$i]);
                for ($j=0;$j<$o;$j++)
                    $pixels[] = ($j%2==0 ? ($c & 240)>>4 : $c & 15);
        }
    }
    
    $out = '';
    if (count($pixels)%2) $pixels[]=0;
    $cnt = count($pixels)/2;
    for ($i=0;$i<$cnt;$i++)
        $out .= chr(16*$pixels[2*$i] + $pixels[2*$i+1]);
    return $out;
} 



}

?>