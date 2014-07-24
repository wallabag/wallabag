<?php

class directw {

var $mpdf = null;

function directw(&$mpdf) {
	$this->mpdf = $mpdf;
}


function Write($h,$txt,$currentx=0,$link='',$directionality='ltr',$align='') {
	if (!$align) { 
		if ($directionality=='rtl') { $align = 'R'; }
		else { $align = 'L'; }
	}
	if ($h == 0) { $this->mpdf->SetLineHeight(); $h = $this->mpdf->lineheight; }
	//Output text in flowing mode
	$w = $this->mpdf->w - $this->mpdf->rMargin - $this->mpdf->x; 

	$wmax = ($w - ($this->mpdf->cMarginL+$this->mpdf->cMarginR));
	$s=str_replace("\r",'',$txt);
	if ($this->mpdf->usingCoreFont)  { $nb=strlen($s); }
	else {
		$nb=mb_strlen($s, $this->mpdf->mb_enc );
		// handle single space character
		if(($nb==1) && $s == " ") {
			$this->mpdf->x += $this->mpdf->GetStringWidth($s);
			return;
		}
	}
	$sep=-1;
	$i=0;
	$j=0;
	$l=0;
	$nl=1;
	if (!$this->mpdf->usingCoreFont) {
		if (preg_match("/([".$this->mpdf->pregRTLchars."])/u", $txt)) { $this->mpdf->biDirectional = true; }	// *RTL*
		$checkCursive=false;
		if ($this->mpdf->biDirectional) {  $checkCursive=true; }	// *RTL*
		else if (isset($this->mpdf->CurrentFont['indic']) && $this->mpdf->CurrentFont['indic']) {  $checkCursive=true; }	// *INDIC*
		while($i<$nb) {
			//Get next character
			$c = mb_substr($s,$i,1,$this->mpdf->mb_enc );
			if($c == "\n") {
				// WORD SPACING
				$this->mpdf->ResetSpacing();
				//Explicit line break
				$tmp = rtrim(mb_substr($s,$j,$i-$j,$this->mpdf->mb_enc));
				if ($directionality == 'rtl' && $align == 'J') { $align = 'R'; }	// *RTL*
				$this->mpdf->magic_reverse_dir($tmp, true, $directionality);	// *RTL*
				$this->mpdf->Cell($w, $h, $tmp, 0, 2, $align, $fill, $link);
				$i++;
				$sep = -1;
				$j = $i;
				$l = 0;
				if($nl == 1) {
					if ($currentx != 0) $this->mpdf->x=$currentx;
					else $this->mpdf->x=$this->mpdf->lMargin;
					$w = $this->mpdf->w - $this->mpdf->rMargin - $this->mpdf->x;
					$wmax = ($w - ($this->mpdf->cMarginL+$this->mpdf->cMarginR));
				}
				$nl++;
				continue;
			}
			if($c == " ") { $sep= $i; }
			$l += $this->mpdf->GetCharWidthNonCore($c);	// mPDF 5.3.04
			if($l > $wmax) {
				//Automatic line break (word wrapping)
				if($sep == -1) {
					// WORD SPACING
					$this->mpdf->ResetSpacing();
					if($this->mpdf->x > $this->mpdf->lMargin) {
						//Move to next line
						if ($currentx != 0) $this->mpdf->x=$currentx;
						else $this->mpdf->x=$this->mpdf->lMargin;
						$this->mpdf->y+=$h;
						$w=$this->mpdf->w-$this->mpdf->rMargin-$this->mpdf->x;
						$wmax = ($w - ($this->mpdf->cMarginL+$this->mpdf->cMarginR));
						$i++;
						$nl++;
						continue;
					}
					if($i==$j) { $i++; }
					$tmp = rtrim(mb_substr($s,$j,$i-$j,$this->mpdf->mb_enc));
					if ($directionality == 'rtl' && $align == 'J') { $align = 'R'; }	// *RTL*
					$this->mpdf->magic_reverse_dir($tmp, true, $directionality);	// *RTL*
					$this->mpdf->Cell($w, $h, $tmp, 0, 2, $align, $fill, $link);
				}
				else {
					$tmp = rtrim(mb_substr($s,$j,$sep-$j,$this->mpdf->mb_enc));
					if ($directionality == 'rtl' && $align == 'J') { $align = 'R'; }	// *RTL*
					$this->mpdf->magic_reverse_dir($tmp, true, $directionality);	// *RTL*

					if($align=='J') {
						//////////////////////////////////////////
						// JUSTIFY J using Unicode fonts (Word spacing doesn't work)
						// WORD SPACING
						// Change NON_BREAKING SPACE to spaces so they are 'spaced' properly
					      $tmp = str_replace(chr(194).chr(160),chr(32),$tmp ); 
						$len_ligne = $this->mpdf->GetStringWidth($tmp );
						$nb_carac = mb_strlen( $tmp , $this->mpdf->mb_enc ) ;  
						$nb_spaces = mb_substr_count( $tmp ,' ', $this->mpdf->mb_enc ) ;  
						$inclCursive=false;
						if ($checkCursive) {
							if (preg_match("/([".$this->mpdf->pregRTLchars."])/u", $tmp)) { $inclCursive = true; }	// *RTL*
							if (preg_match("/([".$this->mpdf->pregHIchars.$this->mpdf->pregBNchars.$this->mpdf->pregPAchars."])/u", $tmp)) { $inclCursive = true; }	// *INDIC*
						}
						list($charspacing,$ws) = $this->mpdf->GetJspacing($nb_carac,$nb_spaces,((($w-2) - $len_ligne) * _MPDFK),$inclCursive);
						$this->mpdf->SetSpacing($charspacing,$ws);
						//////////////////////////////////////////
					}
					$this->mpdf->Cell($w, $h, $tmp, 0, 2, $align, $fill, $link);
					$i=$sep+1;
				}
				$sep = -1;
				$j = $i;
				$l = 0;
				if($nl==1) {
					if ($currentx != 0) $this->mpdf->x=$currentx;
					else $this->mpdf->x=$this->mpdf->lMargin;
					$w=$this->mpdf->w-$this->mpdf->rMargin-$this->mpdf->x;
					$wmax = ($w - ($this->mpdf->cMarginL+$this->mpdf->cMarginR));
				}
				$nl++;
			}
			else { $i++; }
		}
		//Last chunk
		// WORD SPACING
		$this->mpdf->ResetSpacing();
	}
	else {
		while($i<$nb) {
			//Get next character
			$c=$s[$i];
			if($c == "\n") {
				//Explicit line break
				// WORD SPACING
				$this->mpdf->ResetSpacing();
				$this->mpdf->Cell($w, $h, substr($s, $j, $i-$j), 0, 2, $align, $fill, $link);
				$i++;
				$sep = -1;
				$j = $i;
				$l = 0;
				if($nl == 1) {
					if ($currentx != 0) $this->mpdf->x=$currentx;
					else $this->mpdf->x=$this->mpdf->lMargin;
					$w = $this->mpdf->w - $this->mpdf->rMargin - $this->mpdf->x;
					$wmax=$w-($this->mpdf->cMarginL+$this->mpdf->cMarginR);
				}
				$nl++;
				continue;
			}
			if($c == " ") { $sep= $i; }
			$l += $this->mpdf->GetCharWidthCore($c);	// mPDF 5.3.04
			if($l > $wmax) {
				//Automatic line break (word wrapping)
				if($sep == -1) {
					// WORD SPACING
					$this->mpdf->ResetSpacing();
					if($this->mpdf->x > $this->mpdf->lMargin) {
						//Move to next line
						if ($currentx != 0) $this->mpdf->x=$currentx;
						else $this->mpdf->x=$this->mpdf->lMargin;
						$this->mpdf->y+=$h;
						$w=$this->mpdf->w-$this->mpdf->rMargin-$this->mpdf->x;
						$wmax=$w-($this->mpdf->cMarginL+$this->mpdf->cMarginR);
						$i++;
						$nl++;
						continue;
					}
					if($i==$j) { $i++; }
					$this->mpdf->Cell($w, $h, substr($s, $j, $i-$j), 0, 2, $align, $fill, $link);
				}
				else {
					$tmp = substr($s, $j, $sep-$j);
					if($align=='J') {
						//////////////////////////////////////////
						// JUSTIFY J using Unicode fonts (Word spacing doesn't work)
						// WORD SPACING
						// Change NON_BREAKING SPACE to spaces so they are 'spaced' properly
					      $tmp = str_replace(chr(160),chr(32),$tmp );
						$len_ligne = $this->mpdf->GetStringWidth($tmp );
						$nb_carac = strlen( $tmp ) ;  
						$nb_spaces = substr_count( $tmp ,' ' ) ;  
						list($charspacing,$ws) = $this->mpdf->GetJspacing($nb_carac,$nb_spaces,((($w-2) - $len_ligne) * _MPDFK),$false);
						$this->mpdf->SetSpacing($charspacing,$ws);
						//////////////////////////////////////////
					}
					$this->mpdf->Cell($w, $h, $tmp, 0, 2, $align, $fill, $link);
					$i=$sep+1;
				}
				$sep = -1;
				$j = $i;
				$l = 0;
				if($nl==1) {
					if ($currentx != 0) $this->mpdf->x=$currentx;
					else $this->mpdf->x=$this->mpdf->lMargin;
					$w=$this->mpdf->w-$this->mpdf->rMargin-$this->mpdf->x;
					$wmax=$w-($this->mpdf->cMarginL+$this->mpdf->cMarginR);
				}
				$nl++;
			}
			else {
				$i++;
			}
		}
		// WORD SPACING
		$this->mpdf->ResetSpacing();
	}
	//Last chunk
	if($i!=$j) {
		if ($currentx != 0) $this->mpdf->x=$currentx;
		else $this->mpdf->x=$this->mpdf->lMargin;	
		if ($this->mpdf->usingCoreFont) { $tmp = substr($s,$j,$i-$j); }
		else {
			$tmp = mb_substr($s,$j,$i-$j,$this->mpdf->mb_enc);
			if ($directionality == 'rtl' && $align == 'J') { $align = 'R'; }	// *RTL*
			$this->mpdf->magic_reverse_dir($tmp, true, $directionality);	// *RTL*
		}
		$this->mpdf->Cell($w,$h,$tmp,0,0,$align,$fill,$link);
	}
}


function CircularText($x, $y, $r, $text, $align='top', $fontfamily='', $fontsizePt=0, $fontstyle='', $kerning=120, $fontwidth=100, $divider='') {	// mPDF 5.5.23
	if ($font || $fontstyle || $fontsizePt) $this->mpdf->SetFont($fontfamily,$fontstyle,$fontsizePt);
	$kerning/=100;
	$fontwidth/=100;        
	if($kerning==0) $this->mpdf->Error('Please use values unequal to zero for kerning (CircularText)');
	if($fontwidth==0) $this->mpdf->Error('Please use values unequal to zero for font width (CircularText)');
	$text=str_replace("\r",'',$text);
	//circumference
	$u=($r*2)*M_PI;
	// mPDF 5.5.23
	$checking = true;
	$autoset = false;
	while($checking) {
	   $t=0;
	   $w = array();
	   if ($this->mpdf->usingCoreFont)  {
		$nb=strlen($text); 
		for($i=0; $i<$nb; $i++){
			$w[$i]=$this->mpdf->GetStringWidth($text[$i]);
			$w[$i]*=$kerning*$fontwidth;
			$t+=$w[$i];
		}
	   }
	   else { 
		$nb=mb_strlen($text, $this->mpdf->mb_enc ); 
		$lastchar = '';
		$unicode = $this->mpdf->UTF8StringToArray($text);
		for($i=0; $i<$nb; $i++){
			$c = mb_substr($text,$i,1,$this->mpdf->mb_enc );
			$w[$i]=$this->mpdf->GetStringWidth($c);
			$w[$i]*=$kerning*$fontwidth;
			$char = $unicode[$i];
			if ($this->mpdf->useKerning && $lastchar) {
				if (isset($this->mpdf->CurrentFont['kerninfo'][$lastchar][$char])) { 
					$tk = $this->mpdf->CurrentFont['kerninfo'][$lastchar][$char] * ($this->mpdf->FontSize/ 1000) * $kerning * $fontwidth; 
					$w[$i] += $tk/2;
					$w[$i-1] += $tk/2;
					$t+=$tk;
				}
			}
			$lastchar = $char;
			$t+=$w[$i];
		}
	   }
	   if ($fontsizePt>=0 || $autoset) { $checking = false; }
	   else {
		$t+=$this->mpdf->GetStringWidth('  ');
		if ($divider)
			$t+=$this->mpdf->GetStringWidth('  ');
		if ($fontsizePt==-2)
			$fontsizePt = $this->mpdf->FontSizePt * 0.5  *  $u/$t;
		else 
			$fontsizePt = $this->mpdf->FontSizePt * $u/$t;
		$this->mpdf->SetFontSize($fontsizePt);
		$autoset = true;
	   }
	}

	//total width of string in degrees
	$d=($t/$u)*360;

	$this->mpdf->StartTransform();
	// rotate matrix for the first letter to center the text
	// (half of total degrees)
	if($align=='top'){
		$this->mpdf->transformRotate(-$d/2, $x, $y);
	}
	else{
		$this->mpdf->transformRotate($d/2, $x, $y);
	}
	//run through the string
	for($i=0; $i<$nb; $i++){
		if($align=='top'){
			//rotate matrix half of the width of current letter + half of the width of preceding letter
			if($i==0){
				$this->mpdf->transformRotate((($w[$i]/2)/$u)*360, $x, $y);
			}
			else{
				$this->mpdf->transformRotate((($w[$i]/2+$w[$i-1]/2)/$u)*360, $x, $y);
			}
			if($fontwidth!=1){
				$this->mpdf->StartTransform();
				$this->mpdf->transformScale($fontwidth*100, 100, $x, $y);
			}
			$this->mpdf->SetXY($x-$w[$i]/2, $y-$r);
		}
		else{
			//rotate matrix half of the width of current letter + half of the width of preceding letter
			if($i==0){
				$this->mpdf->transformRotate(-(($w[$i]/2)/$u)*360, $x, $y);
			}
			else{
				$this->mpdf->transformRotate(-(($w[$i]/2+$w[$i-1]/2)/$u)*360, $x, $y);
			}
			if($fontwidth!=1){
				$this->mpdf->StartTransform();
				$this->mpdf->transformScale($fontwidth*100, 100, $x, $y);
			}
			$this->mpdf->SetXY($x-$w[$i]/2, $y+$r-($this->mpdf->FontSize));
		}
		if ($this->mpdf->usingCoreFont) { $c=$text[$i]; }
		else { $c = mb_substr($text,$i,1,$this->mpdf->mb_enc ); }
		$this->mpdf->Cell(($w[$i]),$this->mpdf->FontSize,$c,0,0,'C');	// mPDF 5.3.53
		if($fontwidth!=1){
			$this->mpdf->StopTransform();
		}
	}
	$this->mpdf->StopTransform();

	// mPDF 5.5.23
	if($align=='top' && $divider!=''){
		$wc=$this->mpdf->GetStringWidth($divider);
		$wc*=$kerning*$fontwidth;

		$this->mpdf->StartTransform();
		$this->mpdf->transformRotate(90, $x, $y);
		$this->mpdf->SetXY($x-$wc/2, $y-$r);
		$this->mpdf->Cell(($wc),$this->mpdf->FontSize,$divider,0,0,'C');
		$this->mpdf->StopTransform();

		$this->mpdf->StartTransform();
		$this->mpdf->transformRotate(-90, $x, $y);
		$this->mpdf->SetXY($x-$wc/2, $y-$r);
		$this->mpdf->Cell(($wc),$this->mpdf->FontSize,$divider,0,0,'C');
		$this->mpdf->StopTransform();
	}
}

function Shaded_box( $text,$font='',$fontstyle='B',$szfont='',$width='70%',$style='DF',$radius=2.5,$fill='#FFFFFF',$color='#000000',$pad=2 )
{
// F (shading - no line),S (line, no shading),DF (both)
	if (!$font) { $font= $this->mpdf->default_font; }
	if (!$szfont) { $szfont = ($this->mpdf->default_font_size * 1.8); }

	$text = $this->mpdf->purify_utf8_text($text);
	if ($this->mpdf->text_input_as_HTML) {
		$text = $this->mpdf->all_entities_to_utf8($text);
	}
	if ($this->mpdf->usingCoreFont) { $text = mb_convert_encoding($text,$this->mpdf->mb_enc,'UTF-8'); }
	// DIRECTIONALITY
	$this->mpdf->magic_reverse_dir($text, true, $this->mpdf->directionality);	// *RTL*
	// Font-specific ligature substitution for Indic fonts
	if (isset($this->mpdf->CurrentFont['indic']) && $this->mpdf->CurrentFont['indic']) $this->mpdf->ConvertIndic($text);	// *INDIC*
	$text = ' '.$text.' ';
	if (!$width) { $width = $this->mpdf->pgwidth; } else { $width=$this->mpdf->ConvertSize($width,$this->mpdf->pgwidth); }
	$midpt = $this->mpdf->lMargin+($this->mpdf->pgwidth/2);
	$r1  = $midpt-($width/2);	//($this->mpdf->w / 2) - 40;
	$r2  = $r1 + $width; 		//$r1 + 80;
	$y1  = $this->mpdf->y;


	$mid = ($r1 + $r2 ) / 2;
	$loop   = 0;
    
	while ( $loop == 0 )
	{
		$this->mpdf->SetFont( $font, $fontstyle, $szfont );
		$sz = $this->mpdf->GetStringWidth( $text );
		if ( ($r1+$sz) > $r2 )
			$szfont --;
		else
			$loop ++;
	}

	$y2  = $this->mpdf->FontSize+($pad*2);

	$this->mpdf->SetLineWidth(0.1);
	$fc = $this->mpdf->ConvertColor($fill);
	$tc = $this->mpdf->ConvertColor($color);
	$this->mpdf->SetFColor($fc);
	$this->mpdf->SetTColor($tc);
	$this->mpdf->RoundedRect($r1, $y1, ($r2 - $r1), $y2, $radius, $style);
	$this->mpdf->SetX( $r1);
	$this->mpdf->Cell($r2-$r1, $y2, $text, 0, 1, "C" );
	$this->mpdf->SetY($y1+$y2+2);	// +2 = mm margin below shaded box
	$this->mpdf->Reset();
}


}

?>