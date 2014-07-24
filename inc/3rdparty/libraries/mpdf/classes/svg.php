<?php
//	svg class modified for mPDF version 4.4.003 by Ian Back: based on -
//	svg2pdf fpdf class
//	sylvain briand (syb@godisaduck.com), modified by rick trevino (rtrevino1@yahoo.com)
//	http://www.godisaduck.com/svg2pdf_with_fpdf
//	http://rhodopsin.blogspot.com
//	
//	cette class etendue est open source, toute modification devra cependant etre repertoriée~


// NB UNITS - Works in pixels as main units - converting to PDF units when outputing to PDF string
// and on returning size

class SVG {

	var $svg_gradient;	//	array - contient les infos sur les gradient fill du svg classé par id du svg
	var $svg_shadinglist;	//	array - contient les ids des objet shading
	var $svg_info;		//	array contenant les infos du svg voulue par l'utilisateur
	var $svg_attribs;		//	array - holds all attributes of root <svg> tag
	var $svg_style;		//	array contenant les style de groupes du svg
	var $svg_string;		//	String contenant le tracage du svg en lui même.
	var $txt_data;		//    array - holds string info to write txt to image
	var $txt_style;		// 	array - current text style
	var $mpdf_ref;
	var $xbase;		// mPDF 4.4.003
	var $ybase;		// mPDF 4.4.003
	var $svg_error;	// mPDF 4.4.003
	var $subPathInit;	// mPDF 4.4.003
	var $spxstart;	// mPDF 4.4.003
	var $spystart;	// mPDF 4.4.003
	var $kp;		// mPDF 4.4.003  convert pixels to PDF units
	var $pathBBox;	// mPDF 5.0.039

	function SVG(&$mpdf){
		$this->svg_gradient = array();
		$this->svg_shadinglist = array();
		$this->txt_data = array();
		$this->svg_string = '';
		$this->svg_info = array();
		$this->svg_attribs = array();
		$this->xbase = 0;
		$this->ybase = 0;
		$this->svg_error = false;
		$this->subPathInit = false;	// mPDF 4.4.003
		$this->dashesUsed = false;	// mPDF 5.0
		$this->mpdf_ref =& $mpdf;

		$this->kp = 72 / $mpdf->img_dpi;	// mPDF 4.4.003  constant To convert pixels to pts/PDF units
		$this->kf = 1;				// mPDF 5.0.039	constant To convert font size if re-mapped
		$this->pathBBox = array(); // mPDF 5.0.039

		$this->svg_style = array(
			array(
			'fill'		=> 'black',			//	mPDF 4.4.008
			'fill-opacity'	=> 1,				//	remplissage opaque par defaut
			'fill-rule'		=> 'nonzero',		//	mode de remplissage par defaut
			'stroke'		=> 'none',			//	pas de trait par defaut
			'stroke-linecap'	=> 'butt',			//	style de langle par defaut
			'stroke-linejoin'	=> 'miter',			//
			'stroke-miterlimit' => 4,			//	limite de langle par defaut
			'stroke-opacity'	=> 1,				//	trait opaque par defaut
			'stroke-width'	=> 1,				//	mPDF 4.4.011
			'stroke-dasharray' => 0,			//	mPDF 4.4.003
			'stroke-dashoffset' => 0,			//	mPDF 4.4.003
			'color' => ''					//	mPDF 4.4.005
			)
		);

		$this->txt_style = array(
			array(
			'fill'		=> 'black',		//	pas de remplissage par defaut
			'font-family' 	=> $mpdf->default_font,
			'font-size'		=> $mpdf->default_font_size,		// 	****** this is pts
			'font-weight'	=> 'normal',	//	normal | bold
			'font-style'	=> 'normal',	//	italic | normal
			'text-anchor'	=> 'start',		// alignment: start, middle, end
/* mPDF 5.0.041 */
			'fill-opacity'	=> 1,				//	remplissage opaque par defaut
			'fill-rule'		=> 'nonzero',		//	mode de remplissage par defaut
			'stroke'		=> 'none',			//	pas de trait par defaut
			'stroke-opacity'	=> 1,				//	trait opaque par defaut
			'stroke-width'	=> 1,				//	mPDF 4.4.011
			'color' => ''					//	mPDF 4.4.005
			)
		);



	}

	function svgGradient($gradient_info, $attribs, $element){
		$n = count($this->mpdf_ref->gradients)+1;

		// Get bounding dimensions of element
		$w = 100;
		$h = 100;
		$x_offset = 0;
		$y_offset = 0;
		if ($element=='rect') {
			$w = $attribs['width'];
			$h = $attribs['height'];
			$x_offset = $attribs['x'];
			$y_offset = $attribs['y'];
		}
		else if ($element=='ellipse') {
			$w = $attribs['rx']*2;
			$h = $attribs['ry']*2;
			$x_offset = $attribs['cx']-$attribs['rx'];
			$y_offset = $attribs['cy']-$attribs['ry'];
		}
		else if ($element=='circle') {
			$w = $attribs['r']*2;
			$h = $attribs['r']*2;
			$x_offset = $attribs['cx']-$attribs['r'];
			$y_offset = $attribs['cy']-$attribs['r'];
		}
		else if ($element=='polygon') {
			$pts = preg_split('/[ ,]+/', trim($attribs['points']));
			$maxr=$maxb=0;
			$minl=$mint=999999;
			for ($i=0;$i<count($pts); $i++) {
				if ($i % 2 == 0) {	// x values
					$minl = min($minl,$pts[$i]);
					$maxr = max($maxr,$pts[$i]);
				}
				else {	// y values
					$mint = min($mint,$pts[$i]);
					$maxb = max($maxb,$pts[$i]);
				}
			}
			$w = $maxr-$minl;
			$h = $maxb-$mint;
			$x_offset = $minl;
			$y_offset = $mint;
		}
		else if ($element=='path') {
		  // mPDF 5.0.039
		  if (is_array($this->pathBBox) && $this->pathBBox[2]>0) {
			$w = $this->pathBBox[2];
			$h = $this->pathBBox[3];
			$x_offset = $this->pathBBox[0];
			$y_offset = $this->pathBBox[1];
		  }
		  else {
			preg_match_all('/([a-z]|[A-Z])([ ,\-.\d]+)*/', $attribs['d'], $commands, PREG_SET_ORDER);
			$maxr=$maxb=0;
			$minl=$mint=999999;
			foreach($commands as $c){
				if(count($c)==3){
					list($tmp, $cmd, $arg) = $c;
					if ($cmd=='M' || $cmd=='L' || $cmd=='C' || $cmd=='S' || $cmd=='Q' || $cmd=='T') {
						$pts = preg_split('/[ ,]+/', trim($arg));
						for ($i=0;$i<count($pts); $i++) {
							if ($i % 2 == 0) {	// x values
								$minl = min($minl,$pts[$i]);
								$maxr = max($maxr,$pts[$i]);
							}
							else {	// y values
								$mint = min($mint,$pts[$i]);
								$maxb = max($maxb,$pts[$i]);
							}
						}
					}
					if ($cmd=='H') { // sets new x
						$minl = min($minl,$arg);
						$maxr = max($maxr,$arg);
					}
					if ($cmd=='V') { // sets new y
						$mint = min($mint,$arg);
						$maxb = max($maxb,$arg);
					}
				}
			}
			$w = $maxr-$minl;
			$h = $maxb-$mint;
			$x_offset = $minl;
			$y_offset = $mint;
		  }
		}
		if (!$w || $w==-999999) { $w = 100; }
		if (!$h || $h==-999999) { $h = 100; }
		if ($x_offset==999999) { $x_offset = 0; }
		if ($y_offset==999999) { $y_offset = 0; }

		// mPDF 4.5.010
		// TRANSFORMATIONS
		$transformations = '';
		if (isset($gradient_info['transform'])){
			preg_match_all('/(matrix|translate|scale|rotate|skewX|skewY)\((.*?)\)/is',$gradient_info['transform'],$m);
			if (count($m[0])) {
				for($i=0; $i<count($m[0]); $i++) {
					$c = strtolower($m[1][$i]);
					$v = trim($m[2][$i]);
					$vv = preg_split('/[ ,]+/',$v);
					if ($c=='matrix' && count($vv)==6) {
						// mPDF 5.0.039
						// Note angle of rotation is reversed (from SVG to PDF), so vv[1] and vv[2] are negated
						// cf svgDefineStyle()
						$transformations .= sprintf(' %.3F %.3F %.3F %.3F %.3F %.3F cm ', $vv[0], -$vv[1], -$vv[2], $vv[3], $vv[4]*$this->kp, -$vv[5]*$this->kp);	
					}
					else if ($c=='translate' && count($vv)) {
						$tm[4] = $vv[0];
						if (count($vv)==2) { $t_y = -$vv[1]; }
						else { $t_y = 0; }
						$tm[5] = $t_y;
						$transformations .= sprintf(' 1 0 0 1 %.3F %.3F cm ', $tm[4]*$this->kp, $tm[5]*$this->kp);
					}
					else if ($c=='scale' && count($vv)) {
						if (count($vv)==2) { $s_y = $vv[1]; }
						else { $s_y = $vv[0]; }
						$tm[0] = $vv[0];
						$tm[3] = $s_y;
						$transformations .= sprintf(' %.3F 0 0 %.3F 0 0 cm ', $tm[0], $tm[3]);
					}
					else if ($c=='rotate' && count($vv)) {
						$tm[0] = cos(deg2rad(-$vv[0]));
						$tm[1] = sin(deg2rad(-$vv[0]));
						$tm[2] = -$tm[1];
						$tm[3] = $tm[0];
						if (count($vv)==3) {
							$transformations .= sprintf(' 1 0 0 1 %.3F %.3F cm ', $vv[1]*$this->kp, -$vv[2]*$this->kp);
						}
						$transformations .= sprintf(' %.3F %.3F %.3F %.3F 0 0 cm ', $tm[0], $tm[1], $tm[2], $tm[3]);
						if (count($vv)==3) {
							$transformations .= sprintf(' 1 0 0 1 %.3F %.3F cm ', -$vv[1]*$this->kp, $vv[2]*$this->kp);
						}
					}
					else if ($c=='skewx' && count($vv)) {
						$tm[2] = tan(deg2rad(-$vv[0]));
						$transformations .= sprintf(' 1 0 %.3F 1 0 0 cm ', $tm[2]);
					}
					else if ($c=='skewy' && count($vv)) {
						$tm[1] = tan(deg2rad(-$vv[0]));
						$transformations .= sprintf(' 1 %.3F 0 1 0 0 cm ', $tm[1]);
					}

				}
			}
		}


		$return = "";

		// mPDF 5.0.039
		if (isset($gradient_info['units']) && strtolower($gradient_info['units'])=='userspaceonuse') {
			if ($transformations) { $return .= $transformations; }
		}
		// mPDF 5.0.040
		$spread = 'P';  // pad
		if (isset($gradient_info['spread'])) {
			if (strtolower($gradient_info['spread'])=='reflect') { $spread = 'F'; } // reflect
			else if (strtolower($gradient_info['spread'])=='repeat') { $spread = 'R'; } // repeat
		}	


		for ($i=0; $i<(count($gradient_info['color'])); $i++) {
			if (stristr($gradient_info['color'][$i]['offset'], '%')!== false) { $gradient_info['color'][$i]['offset'] = ($gradient_info['color'][$i]['offset']+0)/100; }
			if (stristr($gradient_info['color'][($i+1)]['offset'], '%')!== false) { $gradient_info['color'][($i+1)]['offset'] = ($gradient_info['color'][($i+1)]['offset']+0)/100; }
			if ($gradient_info['color'][$i]['offset']<0) { $gradient_info['color'][$i]['offset'] = 0; }
			if ($gradient_info['color'][$i]['offset']>1) { $gradient_info['color'][$i]['offset'] = 1; }
			if ($i>0) {
				if ($gradient_info['color'][$i]['offset']<$gradient_info['color'][($i-1)]['offset']) { 
					$gradient_info['color'][$i]['offset']=$gradient_info['color'][($i-1)]['offset']; 
				}
			}
		}

		if ($gradient_info['color'][0]['offset']>0) { 
			array_unshift($gradient_info['color'], $gradient_info['color'][0]);
			$gradient_info['color'][0]['offset'] = 0; 
		}
		$ns = count($gradient_info['color']);
		if ($gradient_info['color'][($ns-1)]['offset']<1) { 
			$gradient_info['color'][] = $gradient_info['color'][($ns-1)];
			$gradient_info['color'][($ns)]['offset'] = 1; 
		}
		$ns = count($gradient_info['color']);




		if ($gradient_info['type'] == 'linear'){
			// mPDF 4.4.003
			if (isset($gradient_info['units']) && strtolower($gradient_info['units'])=='userspaceonuse') {
				if (isset($gradient_info['info']['x1'])) { $gradient_info['info']['x1'] = ($gradient_info['info']['x1']-$x_offset) / $w; }
				if (isset($gradient_info['info']['y1'])) { $gradient_info['info']['y1'] = ($gradient_info['info']['y1']-$y_offset) / $h; }
				if (isset($gradient_info['info']['x2'])) { $gradient_info['info']['x2'] = ($gradient_info['info']['x2']-$x_offset) / $w; }
				if (isset($gradient_info['info']['y2'])) { $gradient_info['info']['y2'] = ($gradient_info['info']['y2']-$y_offset) / $h; }
			}
			if (isset($gradient_info['info']['x1'])) { $x1 = $gradient_info['info']['x1']; }
			else { $x1 = 0; }
			if (isset($gradient_info['info']['y1'])) { $y1 = $gradient_info['info']['y1']; }
			else { $y1 = 0; }
			if (isset($gradient_info['info']['x2'])) { $x2 = $gradient_info['info']['x2']; }
			else { $x2 = 1; }
			if (isset($gradient_info['info']['y2'])) { $y2 = $gradient_info['info']['y2']; }
			else { $y2 = 0; }

			if (stristr($x1, '%')!== false) { $x1 = ($x1+0)/100; }
			if (stristr($x2, '%')!== false) { $x2 = ($x2+0)/100; }
			if (stristr($y1, '%')!== false) { $y1 = ($y1+0)/100; }
			if (stristr($y2, '%')!== false) { $y2 = ($y2+0)/100; }

			// mPDF 5.0.042
			$bboxw = $w;
			$bboxh = $h;
			$usex = $x_offset;
			$usey = $y_offset;
			$usew = $bboxw;
			$useh = $bboxh;
			if (isset($gradient_info['units']) && strtolower($gradient_info['units'])=='userspaceonuse') {
				$angle = rad2deg(atan2(($gradient_info['info']['y2']-$gradient_info['info']['y1']), ($gradient_info['info']['x2']-$gradient_info['info']['x1'])));
				if ($angle < 0) { $angle += 360; }
				else if ($angle > 360) { $angle -= 360; }
				if ($angle!=0 && $angle!=360 && $angle!=90 && $angle!=180 && $angle!=270) { 
				    if ($w >= $h) {
					$y1 *= $h/$w ;
					$y2 *= $h/$w ;
					$usew = $useh = $bboxw;
				    }
				    else {
					$x1 *= $w/$h ;
					$x2 *= $w/$h ;
					$usew = $useh = $bboxh;
				    }
				}
			}
			$a = $usew;		// width
			$d = -$useh;	// height
			$e = $usex;		// x- offset
			$f = -$usey;	// -y-offset

			$return .= sprintf('%.3F 0 0 %.3F %.3F %.3F cm ', $a*$this->kp, $d*$this->kp, $e*$this->kp, $f*$this->kp);

			// mPDF 5.0.039
			if (isset($gradient_info['units']) && strtolower($gradient_info['units'])=='objectboundingbox') {
				if ($transformations) { $return .= $transformations; }
			}

			// mPDF 5.0.020
			$trans = false;

			// mPDF 5.0.040
			if ($spread=='R' || $spread=='F') {	// Repeat  /  Reflect
				$offs = array();
				for($i=0;$i<$ns;$i++) {
					$offs[$i] = $gradient_info['color'][$i]['offset'];
				}
				$gp = 0;
				$inside=true;
				while($inside) {
				   $gp++;
				   for($i=0;$i<$ns;$i++) {
					if ($spread=='F' && ($gp % 2) == 1) {	// Reflect
						$gradient_info['color'][(($ns*$gp)+$i)] = $gradient_info['color'][(($ns*($gp-1))+($ns-$i-1))];
						$tmp = $gp+(1-$offs[($ns-$i-1)]) ;
						$gradient_info['color'][(($ns*$gp)+$i)]['offset'] = $tmp; 
					}
					else {	// Reflect
						$gradient_info['color'][(($ns*$gp)+$i)] = $gradient_info['color'][$i];
						$tmp = $gp+$offs[$i] ;
						$gradient_info['color'][(($ns*$gp)+$i)]['offset'] = $tmp; 
					}
					// IF STILL INSIDE BOX OR STILL VALID 
					// Point on axis to test
					$px1 = $x1 + ($x2-$x1)*$tmp;
					$py1 = $y1 + ($y2-$y1)*$tmp;
					// Get perpendicular axis
					$alpha = atan2($y2-$y1, $x2-$x1);
					$alpha += M_PI/2;	// rotate 90 degrees
					// Get arbitrary point to define line perpendicular to axis
					$px2 = $px1+cos($alpha);
					$py2 = $py1+sin($alpha);

					$res1 = _testIntersect($px1, $py1, $px2, $py2, 0, 0, 0, 1);	// $x=0 vert axis
					$res2 = _testIntersect($px1, $py1, $px2, $py2, 1, 0, 1, 1);	// $x=1 vert axis
					$res3 = _testIntersect($px1, $py1, $px2, $py2, 0, 0, 1, 0);	// $y=0 horiz axis
					$res4 = _testIntersect($px1, $py1, $px2, $py2, 0, 1, 1, 1);	// $y=1 horiz axis
					if (!$res1 && !$res2 && !$res3 && !$res4) { $inside = false; }
				   }
				}

				$inside=true;
				$gp = 0;
				while($inside) {
				   $gp++;
				   $newarr = array();
				   for($i=0;$i<$ns;$i++) {
					if ($spread=='F') {	// Reflect
					    $newarr[$i] = $gradient_info['color'][($ns-$i-1)];
					    if (($gp % 2) == 1) {
						$tmp = -$gp+(1-$offs[($ns-$i-1)]);
							$newarr[$i]['offset'] = $tmp; 
					   }
					   else {
						$tmp = -$gp+$offs[$i];
						$newarr[$i]['offset'] = $tmp; 
					   }
					}
					else {	// Reflect
						$newarr[$i] = $gradient_info['color'][$i];
						$tmp = -$gp+$offs[$i];
						$newarr[$i]['offset'] = $tmp; 
					}

					// IF STILL INSIDE BOX OR STILL VALID 
					// Point on axis to test
					$px1 = $x1 + ($x2-$x1)*$tmp;
					$py1 = $y1 + ($y2-$y1)*$tmp;
					// Get perpendicular axis
					$alpha = atan2($y2-$y1, $x2-$x1);
					$alpha += M_PI/2;	// rotate 90 degrees
					// Get arbitrary point to define line perpendicular to axis
					$px2 = $px1+cos($alpha);
					$py2 = $py1+sin($alpha);

					$res1 = _testIntersect($px1, $py1, $px2, $py2, 0, 0, 0, 1);	// $x=0 vert axis
					$res2 = _testIntersect($px1, $py1, $px2, $py2, 1, 0, 1, 1);	// $x=1 vert axis
					$res3 = _testIntersect($px1, $py1, $px2, $py2, 0, 0, 1, 0);	// $y=0 horiz axis
					$res4 = _testIntersect($px1, $py1, $px2, $py2, 0, 1, 1, 1);	// $y=1 horiz axis
					if (!$res1 && !$res2 && !$res3 && !$res4) { $inside = false; }
				   }
				   for($i=($ns-1);$i>=0;$i--) { 
					if (isset($newarr[$i]['offset'])) array_unshift($gradient_info['color'], $newarr[$i]); 
				   }
				}
			}

			// mPDF 4.4.007   Gradient STOPs
			$stops = count($gradient_info['color']);
			if ($stops < 2) { return ''; }

			// mPDF 5.0.042
			$range = $gradient_info['color'][count($gradient_info['color'])-1]['offset']-$gradient_info['color'][0]['offset'];
			$min = $gradient_info['color'][0]['offset'];

			for ($i=0; $i<($stops); $i++) {
				// mPDF 5.0.051
				if (!$gradient_info['color'][$i]['color']) { 
					if ($gradient_info['colorspace']=='RGB') $gradient_info['color'][$i]['color'] = '0 0 0'; 
					else if ($gradient_info['colorspace']=='Gray') $gradient_info['color'][$i]['color'] = '0'; 
					else if ($gradient_info['colorspace']=='CMYK') $gradient_info['color'][$i]['color'] = '1 1 1 1'; 
				}
				$offset = ($gradient_info['color'][$i]['offset'] - $min)/$range;
				$this->mpdf_ref->gradients[$n]['stops'][] = array(
					'col' => $gradient_info['color'][$i]['color'],
					'opacity' => $gradient_info['color'][$i]['opacity'],
					'offset' => $offset);
				if ($gradient_info['color'][$i]['opacity']<1) { $trans = true; }
			}
			$grx1 = $x1 + ($x2-$x1)*$gradient_info['color'][0]['offset'];
			$gry1 = $y1 + ($y2-$y1)*$gradient_info['color'][0]['offset'];
			$grx2 = $x1 + ($x2-$x1)*$gradient_info['color'][count($gradient_info['color'])-1]['offset'];
			$gry2 = $y1 + ($y2-$y1)*$gradient_info['color'][count($gradient_info['color'])-1]['offset'];

			$this->mpdf_ref->gradients[$n]['coords']=array($grx1, $gry1, $grx2, $gry2);

			$this->mpdf_ref->gradients[$n]['colorspace'] = $gradient_info['colorspace'];	// mPDF 5.0.051

			$this->mpdf_ref->gradients[$n]['type'] = 2;
			$this->mpdf_ref->gradients[$n]['fo'] = true;

			$this->mpdf_ref->gradients[$n]['extend']=array('true','true');
			if ($trans) { 
				$this->mpdf_ref->gradients[$n]['trans'] = true;	
				$return .= ' /TGS'.($n).' gs ';
			}
			$return .= ' /Sh'.($n).' sh ';
			$return .= " Q\n";
		}
		else if ($gradient_info['type'] == 'radial'){
			// mPDF 4.4.003
			if (isset($gradient_info['units']) && strtolower($gradient_info['units'])=='userspaceonuse') {
				if ($w > $h) { $h = $w; }
				else { $w = $h; }
				if (isset($gradient_info['info']['x0'])) { $gradient_info['info']['x0'] = ($gradient_info['info']['x0']-$x_offset) / $w; }
				if (isset($gradient_info['info']['y0'])) { $gradient_info['info']['y0'] = ($gradient_info['info']['y0']-$y_offset) / $h; }
				if (isset($gradient_info['info']['x1'])) { $gradient_info['info']['x1'] = ($gradient_info['info']['x1']-$x_offset) / $w; }
				if (isset($gradient_info['info']['y1'])) { $gradient_info['info']['y1'] = ($gradient_info['info']['y1']-$y_offset) / $h; }
				if (isset($gradient_info['info']['r'])) { $gradient_info['info']['rx'] = $gradient_info['info']['r'] / $w; }
				if (isset($gradient_info['info']['r'])) { $gradient_info['info']['ry'] = $gradient_info['info']['r'] / $h; }
			}

			if ($gradient_info['info']['x0'] || $gradient_info['info']['x0']===0) { $x0 = $gradient_info['info']['x0']; }
			else { $x0 = 0.5; }
			if ($gradient_info['info']['y0'] || $gradient_info['info']['y0']===0) { $y0 = $gradient_info['info']['y0']; }
			else { $y0 = 0.5; }
			if ($gradient_info['info']['rx'] || $gradient_info['info']['rx']===0) { $rx = $gradient_info['info']['rx']; }
			else if ($gradient_info['info']['r'] || $gradient_info['info']['r']===0) { $rx = $gradient_info['info']['r']; }
			else { $rx = 0.5; }
			if ($gradient_info['info']['ry'] || $gradient_info['info']['ry']===0) { $ry = $gradient_info['info']['ry']; }
			else if ($gradient_info['info']['r'] || $gradient_info['info']['r']===0) { $ry = $gradient_info['info']['r']; }
			else { $ry = 0.5; }
			if ($gradient_info['info']['x1'] || $gradient_info['info']['x1']===0) { $x1 = $gradient_info['info']['x1']; }
			else { $x1 = $x0; }
			if ($gradient_info['info']['y1'] || $gradient_info['info']['y1']===0) { $y1 = $gradient_info['info']['y1']; }
			else { $y1 = $y0; }

			if (stristr($x1, '%')!== false) { $x1 = ($x1+0)/100; }
			if (stristr($x0, '%')!== false) { $x0 = ($x0+0)/100; }
			if (stristr($y1, '%')!== false) { $y1 = ($y1+0)/100; }
			if (stristr($y0, '%')!== false) { $y0 = ($y0+0)/100; }
			if (stristr($rx, '%')!== false) { $rx = ($rx+0)/100; }
			if (stristr($ry, '%')!== false) { $ry = ($ry+0)/100; }

			// mPDF 5.0.043
			$bboxw = $w;
			$bboxh = $h;
			$usex = $x_offset;
			$usey = $y_offset;
			$usew = $bboxw;
			$useh = $bboxh;
			if (isset($gradient_info['units']) && strtolower($gradient_info['units'])=='userspaceonuse') {
				$angle = rad2deg(atan2(($gradient_info['info']['y0']-$gradient_info['info']['y1']), ($gradient_info['info']['x0']-$gradient_info['info']['x1'])));
				if ($angle < 0) { $angle += 360; }
				else if ($angle > 360) { $angle -= 360; }
				if ($angle!=0 && $angle!=360 && $angle!=90 && $angle!=180 && $angle!=270) { 
				    if ($w >= $h) {
					$y1 *= $h/$w ;
					$y0 *= $h/$w ;
					$rx *= $h/$w ;
					$ry *= $h/$w ;
					$usew = $useh = $bboxw;
				    }
				    else {
					$x1 *= $w/$h ;
					$x0 *= $w/$h ;
					$rx *= $w/$h ;
					$ry *= $w/$h ;
					$usew = $useh = $bboxh;
				    }
				}
			}
			$a = $usew;		// width
			$d = -$useh;	// height
			$e = $usex;		// x- offset
			$f = -$usey;	// -y-offset

			$r = $rx;


			$return .= sprintf('%.3F 0 0 %.3F %.3F %.3F cm ', $a*$this->kp, $d*$this->kp, $e*$this->kp, $f*$this->kp);

			// mPDF 5.0.039
			if (isset($gradient_info['units']) && strtolower($gradient_info['units'])=='objectboundingbox') {
				if ($transformations) { $return .= $transformations; }
			}

			// x1 and y1 (fx, fy) should be inside the circle defined by x0 y0 and r else error in mPDF
			while (pow(($x1-$x0),2) + pow(($y1 - $y0),2) >= pow($r,2)) { $r += 0.05; }

			// mPDF 5.0.040
			if ($spread=='R' || $spread=='F') {	// Repeat  /  Reflect
				$offs = array();
				for($i=0;$i<$ns;$i++) {
					$offs[$i] = $gradient_info['color'][$i]['offset'];
				}
				$gp = 0;
				$inside=true;
				while($inside) {
				   $gp++;
				   for($i=0;$i<$ns;$i++) {
					if ($spread=='F' && ($gp % 2) == 1) {	// Reflect
						$gradient_info['color'][(($ns*$gp)+$i)] = $gradient_info['color'][(($ns*($gp-1))+($ns-$i-1))];
						$tmp = $gp+(1-$offs[($ns-$i-1)]) ;
						$gradient_info['color'][(($ns*$gp)+$i)]['offset'] = $tmp; 
					}
					else {	// Reflect
						$gradient_info['color'][(($ns*$gp)+$i)] = $gradient_info['color'][$i];
						$tmp = $gp+$offs[$i] ;
						$gradient_info['color'][(($ns*$gp)+$i)]['offset'] = $tmp; 
					}
					// IF STILL INSIDE BOX OR STILL VALID 
					// TEST IF circle (perimeter) intersects with 
					// or is enclosed
					// Point on axis to test
					$px = $x1 + ($x0-$x1)*$tmp;
					$py = $y1 + ($y0-$y1)*$tmp;
					$pr = $r*$tmp;
					$res = _testIntersectCircle($px, $py, $pr);
					if (!$res) { $inside = false; }
				   }
				}
			}

			// mPDF 4.4.007   Gradient STOPs
			$stops = count($gradient_info['color']);
			if ($stops < 2) { return ''; }

			// mPDF 5.0.043
			$range = $gradient_info['color'][count($gradient_info['color'])-1]['offset']-$gradient_info['color'][0]['offset'];
			$min = $gradient_info['color'][0]['offset'];

			for ($i=0; $i<($stops); $i++) {
				// mPDF 5.0.051
				if (!$gradient_info['color'][$i]['color']) { 
					if ($gradient_info['colorspace']=='RGB') $gradient_info['color'][$i]['color'] = '0 0 0'; 
					else if ($gradient_info['colorspace']=='Gray') $gradient_info['color'][$i]['color'] = '0'; 
					else if ($gradient_info['colorspace']=='CMYK') $gradient_info['color'][$i]['color'] = '1 1 1 1'; 
				}
				$offset = ($gradient_info['color'][$i]['offset'] - $min)/$range;
				$this->mpdf_ref->gradients[$n]['stops'][] = array(
					'col' => $gradient_info['color'][$i]['color'],
					'opacity' => $gradient_info['color'][$i]['opacity'],
					'offset' => $offset);
				if ($gradient_info['color'][$i]['opacity']<1) { $trans = true; }
			}
			$grx1 = $x1 + ($x0-$x1)*$gradient_info['color'][0]['offset'];
			$gry1 = $y1 + ($y0-$y1)*$gradient_info['color'][0]['offset'];
			$grx2 = $x1 + ($x0-$x1)*$gradient_info['color'][count($gradient_info['color'])-1]['offset'];
			$gry2 = $y1 + ($y0-$y1)*$gradient_info['color'][count($gradient_info['color'])-1]['offset'];
			$grir = $r*$gradient_info['color'][0]['offset'];
			$grr = $r*$gradient_info['color'][count($gradient_info['color'])-1]['offset'];

			$this->mpdf_ref->gradients[$n]['coords']=array($grx1, $gry1, $grx2, $gry2, abs($grr), abs($grir)  );

			$grx1 = $x1 + ($x0-$x1)*$first_stop;
			$gry1 = $y1 + ($y0-$y1)*$first_stop;
			$grx2 = $x1 + ($x0-$x1)*$last_stop;
			$gry2 = $y1 + ($y0-$y1)*$last_stop;
			$grir = $r*$first_stop;
			$grr = $r*$last_stop;
			$this->mpdf_ref->gradients[$n]['colorspace'] = $gradient_info['colorspace'];	// mPDF 5.0.051

			$this->mpdf_ref->gradients[$n]['type'] = 3;
			$this->mpdf_ref->gradients[$n]['fo'] = true;

			$this->mpdf_ref->gradients[$n]['extend']=array('true','true');
			if ($trans) { 
				$this->mpdf_ref->gradients[$n]['trans'] = true;	
				$return .= ' /TGS'.($n).' gs ';
			}
			$return .= ' /Sh'.($n).' sh ';
			$return .= " Q\n";


		}

		return $return;
	}


	function svgOffset ($attribs){
		// save all <svg> tag attributes
		$this->svg_attribs = $attribs;
		if(isset($this->svg_attribs['viewBox'])) {
			$vb = preg_split('/\s+/is', trim($this->svg_attribs['viewBox']));
			if (count($vb)==4) {
				$this->svg_info['x'] = $vb[0];
				$this->svg_info['y'] = $vb[1];
				$this->svg_info['w'] = $vb[2];
				$this->svg_info['h'] = $vb[3];
//				return;						// mPDF 5.0.005
			}
		}

		$svg_w = $this->mpdf_ref->ConvertSize($attribs['width']);	// mm (interprets numbers as pixels)
		$svg_h = $this->mpdf_ref->ConvertSize($attribs['height']);	// mm

///*
		// mPDF 5.0.005
		if ($this->svg_info['w']) {	// if 'w' set by viewBox
			if ($svg_w) {	// if width also set, use these values to determine to set size of "pixel"
				$this->kp *= ($svg_w/0.2645) / $this->svg_info['w'];
				$this->kf = ($svg_w/0.2645) / $this->svg_info['w'];	// mPDF 5.0.039
			}
			else if ($svg_h) {
				$this->kp *= ($svg_h/0.2645) / $this->svg_info['h'];
				$this->kf = ($svg_h/0.2645) / $this->svg_info['h'];	// mPDF 5.0.039
			}
			return;
		}
//*/

		// Added to handle file without height or width specified
		if (!$svg_w && !$svg_h) { $svg_w = $svg_h = $this->mpdf_ref->blk[$this->mpdf_ref->blklvl]['inner_width'] ; }	// DEFAULT
		if (!$svg_w) { $svg_w = $svg_h; }
		if (!$svg_h) { $svg_h = $svg_w; }

		$this->svg_info['x'] = 0;
		$this->svg_info['y'] = 0;
		$this->svg_info['w'] = $svg_w/0.2645;	// mm->pixels
		$this->svg_info['h'] = $svg_h/0.2645;	// mm->pixels

	}


	//
	// check if points are within svg, if not, set to max
	function svg_overflow($x,$y)
	{
		$x2 = $x;
		$y2 = $y;
		if(isset($this->svg_attribs['overflow']))
		{
			if($this->svg_attribs['overflow'] == 'hidden')
			{
				// Not sure if this is supposed to strip off units, but since I dont use any I will omlt this step
				$svg_w = preg_replace("/([0-9\.]*)(.*)/i","$1",$this->svg_attribs['width']);
				$svg_h = preg_replace("/([0-9\.]*)(.*)/i","$1",$this->svg_attribs['height']);
				
				// $xmax = floor($this->svg_attribs['width']);
				$xmax = floor($svg_w);
				$xmin = 0;
				// $ymax = floor(($this->svg_attribs['height'] * -1));
				$ymax = floor(($svg_h * -1));
				$ymin = 0;

				if($x > $xmax) $x2 = $xmax; // right edge
				if($x < $xmin) $x2 = $xmin; // left edge
				if($y < $ymax) $y2 = $ymax; // bottom 
				if($y > $ymin) $y2 = $ymin; // top 

			}
		}


		return array( 'x' => $x2, 'y' => $y2);
	}



	function svgDefineStyle($critere_style){

		$tmp = count($this->svg_style)-1;
		$current_style = $this->svg_style[$tmp];

		unset($current_style['transformations']);

		// TRANSFORM SCALE
		$transformations = '';
		if (isset($critere_style['transform'])){
			preg_match_all('/(matrix|translate|scale|rotate|skewX|skewY)\((.*?)\)/is',$critere_style['transform'],$m);
			if (count($m[0])) {
				for($i=0; $i<count($m[0]); $i++) {
					$c = strtolower($m[1][$i]);
					$v = trim($m[2][$i]);
					$vv = preg_split('/[ ,]+/',$v);
					if ($c=='matrix' && count($vv)==6) {
					// mPDF 5.0.039
						// Note angle of rotation is reversed (from SVG to PDF), so vv[1] and vv[2] are negated
						$transformations .= sprintf(' %.3F %.3F %.3F %.3F %.3F %.3F cm ', $vv[0], -$vv[1], -$vv[2], $vv[3], $vv[4]*$this->kp, -$vv[5]*$this->kp);

/*
// The long way of doing this??
// need to reverse angle of rotation from SVG to PDF
$sx=sqrt(pow($vv[0],2)+pow($vv[2],2));
if ($vv[0] < 0) { $sx *= -1; } // change sign
$sy=sqrt(pow($vv[1],2)+pow($vv[3],2));
if ($vv[3] < 0) { $sy *= -1; } // change sign

// rotation angle is 
$t=atan2($vv[1],$vv[3]);
$t=atan2(-$vv[2],$vv[0]);	// Should be the same value or skew has been applied

// Reverse angle
$t *= -1;

// Rebuild matrix
$ma = $sx * cos($t);
$mb = $sy * sin($t);
$mc = -$sx * sin($t);
$md = $sy * cos($t);

// $transformations .= sprintf(' %.3F %.3F %.3F %.3F %.3F %.3F cm ', $ma, $mb, $mc, $md, $vv[4]*$this->kp, -$vv[5]*$this->kp);
*/

					}
					else if ($c=='translate' && count($vv)) {
						$tm[4] = $vv[0];
						if (count($vv)==2) { $t_y = -$vv[1]; }
						else { $t_y = 0; }
						$tm[5] = $t_y;
						$transformations .= sprintf(' 1 0 0 1 %.3F %.3F cm ', $tm[4]*$this->kp, $tm[5]*$this->kp);
					}
					else if ($c=='scale' && count($vv)) {
						if (count($vv)==2) { $s_y = $vv[1]; }
						else { $s_y = $vv[0]; }
						$tm[0] = $vv[0];
						$tm[3] = $s_y;
						$transformations .= sprintf(' %.3F 0 0 %.3F 0 0 cm ', $tm[0], $tm[3]);
					}
					else if ($c=='rotate' && count($vv)) {
						$tm[0] = cos(deg2rad(-$vv[0]));
						$tm[1] = sin(deg2rad(-$vv[0]));
						$tm[2] = -$tm[1];
						$tm[3] = $tm[0];
						if (count($vv)==3) {
							$transformations .= sprintf(' 1 0 0 1 %.3F %.3F cm ', $vv[1]*$this->kp, -$vv[2]*$this->kp);
						}
						$transformations .= sprintf(' %.3F %.3F %.3F %.3F 0 0 cm ', $tm[0], $tm[1], $tm[2], $tm[3]);
						if (count($vv)==3) {
							$transformations .= sprintf(' 1 0 0 1 %.3F %.3F cm ', -$vv[1]*$this->kp, $vv[2]*$this->kp);
						}
					}
					else if ($c=='skewx' && count($vv)) {
						$tm[2] = tan(deg2rad(-$vv[0]));
						$transformations .= sprintf(' 1 0 %.3F 1 0 0 cm ', $tm[2]);
					}
					else if ($c=='skewy' && count($vv)) {
						$tm[1] = tan(deg2rad(-$vv[0]));
						$transformations .= sprintf(' 1 %.3F 0 1 0 0 cm ', $tm[1]);
					}

				}
			}
			$current_style['transformations'] = $transformations;
		}

		if (isset($critere_style['style'])){
			if (preg_match('/fill:\s*rgb\((\d+),\s*(\d+),\s*(\d+)\)/i',$critere_style['style'], $m)) {	// mPDF 5.7.2
				$current_style['fill'] = '#'.str_pad(dechex($m[1]), 2, "0", STR_PAD_LEFT).str_pad(dechex($m[2]), 2, "0", STR_PAD_LEFT).str_pad(dechex($m[3]), 2, "0", STR_PAD_LEFT);
			}
			else { $tmp = preg_replace("/(.*)fill:\s*([a-z0-9#_()]*|none)(.*)/i","$2",$critere_style['style']);	// mPDF 4.4.003
				if ($tmp != $critere_style['style']){ $current_style['fill'] = $tmp; }
			}

			// mPDF 5.7.2
			if (preg_match("/[^-]opacity:\s*([a-z0-9.]*|none)/i",$critere_style['style'], $m) ||
				preg_match("/^opacity:\s*([a-z0-9.]*|none)/i",$critere_style['style'], $m)) {
				$current_style['fill-opacity'] = $m[1];
				$current_style['stroke-opacity'] = $m[1];
			}

			$tmp = preg_replace("/(.*)fill-opacity:\s*([a-z0-9.]*|none)(.*)/i","$2",$critere_style['style']);
			if ($tmp != $critere_style['style']){ $current_style['fill-opacity'] = $tmp;}

			$tmp = preg_replace("/(.*)fill-rule:\s*([a-z0-9#]*|none)(.*)/i","$2",$critere_style['style']);
			if ($tmp != $critere_style['style']){ $current_style['fill-rule'] = $tmp;}

			if (preg_match('/stroke:\s*rgb\((\d+),\s*(\d+),\s*(\d+)\)/',$critere_style['style'], $m)) {
				$current_style['stroke'] = '#'.str_pad(dechex($m[1]), 2, "0", STR_PAD_LEFT).str_pad(dechex($m[2]), 2, "0", STR_PAD_LEFT).str_pad(dechex($m[3]), 2, "0", STR_PAD_LEFT);
			}
			else { $tmp = preg_replace("/(.*)stroke:\s*([a-z0-9#]*|none)(.*)/i","$2",$critere_style['style']);
				if ($tmp != $critere_style['style']){ $current_style['stroke'] = $tmp; }
			}
			
			$tmp = preg_replace("/(.*)stroke-linecap:\s*([a-z0-9#]*|none)(.*)/i","$2",$critere_style['style']);
			if ($tmp != $critere_style['style']){ $current_style['stroke-linecap'] = $tmp;}

			$tmp = preg_replace("/(.*)stroke-linejoin:\s*([a-z0-9#]*|none)(.*)/i","$2",$critere_style['style']);
			if ($tmp != $critere_style['style']){ $current_style['stroke-linejoin'] = $tmp;}
			
			$tmp = preg_replace("/(.*)stroke-miterlimit:\s*([a-z0-9#]*|none)(.*)/i","$2",$critere_style['style']);
			if ($tmp != $critere_style['style']){ $current_style['stroke-miterlimit'] = $tmp;}
			
			$tmp = preg_replace("/(.*)stroke-opacity:\s*([a-z0-9.]*|none)(.*)/i","$2",$critere_style['style']);
			if ($tmp != $critere_style['style']){ $current_style['stroke-opacity'] = $tmp; }
			
			$tmp = preg_replace("/(.*)stroke-width:\s*([a-z0-9.]*|none)(.*)/i","$2",$critere_style['style']);
			if ($tmp != $critere_style['style']){ $current_style['stroke-width'] = $tmp;}

			// mPDF 4.4.003
			$tmp = preg_replace("/(.*)stroke-dasharray:\s*([a-z0-9., ]*|none)(.*)/i","$2",$critere_style['style']);
			if ($tmp != $critere_style['style']){ $current_style['stroke-dasharray'] = $tmp;}

			// mPDF 4.4.003
			$tmp = preg_replace("/(.*)stroke-dashoffset:\s*([a-z0-9.]*|none)(.*)/i","$2",$critere_style['style']);
			if ($tmp != $critere_style['style']){ $current_style['stroke-dashoffset'] = $tmp;}

		}
		// mPDF 5.7.2
		if(isset($critere_style['opacity'])){
			$current_style['fill-opacity'] = $critere_style['opacity'];
			$current_style['stroke-opacity'] = $critere_style['opacity'];
		}

		if(isset($critere_style['fill'])){
			$current_style['fill'] = $critere_style['fill'];
		}

		if(isset($critere_style['fill-opacity'])){
			$current_style['fill-opacity'] = $critere_style['fill-opacity'];
		}

		if(isset($critere_style['fill-rule'])){
			$current_style['fill-rule'] = $critere_style['fill-rule'];
		}

		if(isset($critere_style['stroke'])){
			$current_style['stroke'] = $critere_style['stroke'];
		}

		if(isset($critere_style['stroke-linecap'])){
			$current_style['stroke-linecap'] = $critere_style['stroke-linecap'];
		}

		if(isset($critere_style['stroke-linejoin'])){
			$current_style['stroke-linejoin'] = $critere_style['stroke-linejoin'];
		}

		if(isset($critere_style['stroke-miterlimit'])){
			$current_style['stroke-miterlimit'] = $critere_style['stroke-miterlimit'];
		}

		if(isset($critere_style['stroke-opacity'])){
			$current_style['stroke-opacity'] = $critere_style['stroke-opacity'];
		}

		if(isset($critere_style['stroke-width'])){
			$current_style['stroke-width'] = $critere_style['stroke-width'];
		}

		// mPDF 4.4.003
		if(isset($critere_style['stroke-dasharray'])){
			$current_style['stroke-dasharray'] = $critere_style['stroke-dasharray'];
		}
		if(isset($critere_style['stroke-dashoffset'])){
			$current_style['stroke-dashoffset'] = $critere_style['stroke-dashoffset'];
		}

		// mPDF 4.4.005   Used as indirect setting for currentColor
		if(isset($critere_style['color']) && $critere_style['color'] != 'inherit'){
			$current_style['color'] = $critere_style['color'];
		}

		return $current_style;

	}

	//
	//	Cette fonction ecrit le style dans le stream svg.
	function svgStyle($critere_style, $attribs, $element){
		$path_style = '';
		if (substr_count($critere_style['fill'],'url')>0 && $element != 'line'){
			//
			// couleur degradé
			$id_gradient = preg_replace("/url\(#([\w_]*)\)/i","$1",$critere_style['fill']);
			if ($id_gradient != $critere_style['fill']) {
			   if (isset($this->svg_gradient[$id_gradient])) {
				$fill_gradient = $this->svgGradient($this->svg_gradient[$id_gradient], $attribs, $element);
				if ($fill_gradient) {	// mPDF 4.4.003
					$path_style = "q ";
					$w = "W";
					$style .= 'N';
				}
			   }
			}

		}
		// mPDF 4.4.005   Used as indirect setting for currentColor
		else if (strtolower($critere_style['fill']) == 'currentcolor' && $element != 'line'){
			$col = $this->mpdf_ref->ConvertColor($critere_style['color']);
			if ($col) {
				// mPDF 5.0.051
				// mPDF 5.3.74
				if ($col{0}==5) {	$critere_style['fill-opacity'] = ord($col{4}/100); }	// RGBa
				if ($col{0}==6) {	$critere_style['fill-opacity'] = ord($col{5}/100); }	// CMYKa
				$path_style .= $this->mpdf_ref->SetFColor($col, true).' ';	// mPDF 5.0.051
				$style .= 'F';
			}
		}
		else if ($critere_style['fill'] != 'none' && $element != 'line'){
			$col = $this->mpdf_ref->ConvertColor($critere_style['fill']);
			if ($col) {
				// mPDF 5.0.051
				// mPDF 5.3.74
				if ($col{0}==5) {	$critere_style['fill-opacity'] = ord($col{4}/100); }	// RGBa
				if ($col{0}==6) {	$critere_style['fill-opacity'] = ord($col{5}/100); }	// CMYKa
				$path_style .= $this->mpdf_ref->SetFColor($col, true).' ';	// mPDF 5.0.051
				$style .= 'F';
			}
		}

		// mPDF 5.0.040
		if (substr_count($critere_style['stroke'],'url')>0){
/*
			// Cannot put a gradient on a "stroke" in PDF?
			$id_gradient = preg_replace("/url\(#([\w_]*)\)/i","$1",$critere_style['stroke']);
			if ($id_gradient != $critere_style['stroke']) {
			   if (isset($this->svg_gradient[$id_gradient])) {
				$fill_gradient = $this->svgGradient($this->svg_gradient[$id_gradient], $attribs, $element);
				if ($fill_gradient) {
					$path_style = "q ";
					$w = "W";
					$style .= 'D';
				}
			   }
			}
*/
		}
		// mPDF 4.4.005   Used as indirect setting for currentColor
		else if (strtolower($critere_style['stroke']) == 'currentcolor'){
			$col = $this->mpdf_ref->ConvertColor($critere_style['color']);
			if ($col) {
				// mPDF 5.0.051
				// mPDF 5.3.74
				if ($col{0}==5) {	$critere_style['stroke-opacity'] = ord($col{4}/100); }	// RGBa
				if ($col{0}==6) {	$critere_style['stroke-opacity'] = ord($col{5}/100); }	// CMYKa
				$path_style .= $this->mpdf_ref->SetDColor($col, true).' ';	// mPDF 5.0.051
				$style .= 'D';
				$lw = $this->ConvertSVGSizePixels($critere_style['stroke-width']);
				$path_style .= sprintf('%.3F w ',$lw*$this->kp);
			}
		}
		else if ($critere_style['stroke'] != 'none'){
			$col = $this->mpdf_ref->ConvertColor($critere_style['stroke']);
			if ($col) {
				// mPDF 5.0.051
				// mPDF 5.3.74
				if ($col{0}==5) {	$critere_style['stroke-opacity'] = ord($col{4}/100); }	// RGBa
				if ($col{0}==6) {	$critere_style['stroke-opacity'] = ord($col{5}/100); }	// CMYKa
				$path_style .= $this->mpdf_ref->SetDColor($col, true).' ';	// mPDF 5.0.051
				$style .= 'D';
				$lw = $this->ConvertSVGSizePixels($critere_style['stroke-width']);	// mPDF 4.4.003 
				$path_style .= sprintf('%.3F w ',$lw*$this->kp);
			}
		}


	if ($critere_style['stroke'] != 'none'){
		if ($critere_style['stroke-linejoin'] == 'miter'){
			$path_style .= ' 0 j ';
		}
		else if ($critere_style['stroke-linejoin'] == 'round'){
			$path_style .= ' 1 j ';
		}
		else if ($critere_style['stroke-linejoin'] == 'bevel'){
			$path_style .= ' 2 j ';
		}

		if ($critere_style['stroke-linecap'] == 'butt'){
			$path_style .= ' 0 J ';
		}
		else if ($critere_style['stroke-linecap'] == 'round'){
			$path_style .= ' 1 J ';
		}
		else if ($critere_style['stroke-linecap'] == 'square'){
			$path_style .= ' 2 J ';
		}

		if (isset($critere_style['stroke-miterlimit'])){
		   if ($critere_style['stroke-miterlimit'] == 'none'){
		   }
		   else if (preg_match('/^[\d.]+$/',$critere_style['stroke-miterlimit'])) {
			$path_style .= sprintf('%.2F M ',$critere_style['stroke-miterlimit']);
		   }
		}
		// mPDF 4.4.003
		if (isset($critere_style['stroke-dasharray'])){
			$off = 0;
			$d = preg_split('/[ ,]/',$critere_style['stroke-dasharray']);
			if (count($d) == 1 && $d[0]==0) {
				$path_style .= '[] 0 d '; 
			}
			else {
			  if (count($d) % 2 == 1) { $d = array_merge($d, $d); }	// 5, 3, 1 => 5,3,1,5,3,1  OR 3 => 3,3
			  $arr = '';
			  for($i=0; $i<count($d); $i+=2) {
				$arr .= sprintf('%.3F %.3F ', $d[$i]*$this->kp, $d[$i+1]*$this->kp);
			  }
			  if (isset($critere_style['stroke-dashoffset'])){ $off = $critere_style['stroke-dashoffset'] + 0; }
			  $path_style .= sprintf('[%s] %.3F d ', $arr, $off*$this->kp);
			}
		}
	}

		// mPDF 4.4.003
		if ($critere_style['fill-rule']=='evenodd') { $fr = '*'; }
		else { $fr = ''; }

		// mPDF 4.4.003
		if (isset($critere_style['fill-opacity'])) {
			$opacity = 1;
			if ($critere_style['fill-opacity'] == 0) { $opacity = 0; }
			else if ($critere_style['fill-opacity'] > 1) { $opacity = 1; }
			else if ($critere_style['fill-opacity'] > 0) { $opacity = $critere_style['fill-opacity']; }
			else if ($critere_style['fill-opacity'] < 0) { $opacity = 0; }
			$gs = $this->mpdf_ref->AddExtGState(array('ca'=>$opacity, 'BM'=>'/Normal'));
			$this->mpdf_ref->extgstates[$gs]['fo'] = true;	// mPDF 5.0.039
			$path_style .= sprintf(' /GS%d gs ', $gs);
		}

		// mPDF 4.4.003
		if (isset($critere_style['stroke-opacity'])) {
			$opacity = 1;
			if ($critere_style['stroke-opacity'] == 0) { $opacity = 0; }
			else if ($critere_style['stroke-opacity'] > 1) { $opacity = 1; }
			else if ($critere_style['stroke-opacity'] > 0) { $opacity = $critere_style['stroke-opacity']; }
			else if ($critere_style['stroke-opacity'] < 0) { $opacity = 0; }
			$gs = $this->mpdf_ref->AddExtGState(array('CA'=>$opacity, 'BM'=>'/Normal'));
			$this->mpdf_ref->extgstates[$gs]['fo'] = true;	// mPDF 5.0.039
			$path_style .= sprintf(' /GS%d gs ', $gs);
		}

		switch ($style){
			case 'F':
				$op = 'f';
			break;
			case 'FD':
				$op = 'B';
			break;
			case 'ND':
				$op = 'S';
			break;
			case 'D':
				$op = 'S';
			break;
			default:
				$op = 'n';
		}

		// mPDF 5.0
		$prestyle = $path_style.' ';
		$poststyle = $w.' '. $op.$fr.' '.$fill_gradient."\n";
		return array($prestyle,$poststyle);

	}

	//
	//	fonction retracant les <path />
	function svgPath($command, $arguments){
		$path_cmd = '';
		$newsubpath = false;	// mPDF 4.4.003
		// mPDF 5.0.039
		$minl = $this->pathBBox[0];
		$mint = $this->pathBBox[1];
		$maxr = $this->pathBBox[2]+$this->pathBBox[0];
		$maxb = $this->pathBBox[3]+$this->pathBBox[1];
		// mPDF 5.0.040
		$start = array($this->xbase, -$this->ybase);

		// mPDF 4.4.003
		preg_match_all('/[\-^]?[\d.]+(e[\-]?[\d]+){0,1}/i', $arguments, $a, PREG_SET_ORDER);

		//	if the command is a capital letter, the coords go absolute, otherwise relative
		if(strtolower($command) == $command) $relative = true;
		else $relative = false;


		$ile_argumentow = count($a);

		//	each command may have different needs for arguments [1 to 8]

		switch(strtolower($command)){
			case 'm': // move
				for($i = 0; $i<$ile_argumentow; $i+=2){
					$x = $a[$i][0]; 
					$y = $a[$i+1][0]; 
					if($relative){
						$pdfx = ($this->xbase + $x);
						$pdfy = ($this->ybase - $y);
						$this->xbase += $x;
						$this->ybase += -$y;
					}
					else{
						$pdfx = $x;
						$pdfy =  -$y ;
						$this->xbase = $x;
						$this->ybase = -$y;
					}
					$pdf_pt = $this->svg_overflow($pdfx,$pdfy);
					// mPDF 5.0.039
					$minl = min($minl,$pdf_pt['x']);
					$maxr = max($maxr,$pdf_pt['x']);
					$mint = min($mint,-$pdf_pt['y']);
					$maxb = max($maxb,-$pdf_pt['y']);
					if($i == 0) $path_cmd .= sprintf('%.3F %.3F m ', $pdf_pt['x']*$this->kp, $pdf_pt['y']*$this->kp);
					else $path_cmd .= sprintf('%.3F %.3F l ',  $pdf_pt['x']*$this->kp, $pdf_pt['y']*$this->kp);
					// mPDF 4.4.003  Save start points of subpath
					if ($this->subPathInit) { 
						$this->spxstart = $this->xbase;
						$this->spystart = $this->ybase;
						$this->subPathInit = false;
					}
				}
			break;
			case 'l': // a simple line
				for($i = 0; $i<$ile_argumentow; $i+=2){
					$x = ($a[$i][0]); 
					$y = ($a[$i+1][0]); 
					if($relative){
						$pdfx = ($this->xbase + $x);
						$pdfy = ($this->ybase - $y);
						$this->xbase += $x;
						$this->ybase += -$y;
					}
					else{
						$pdfx = $x ;
						$pdfy =  -$y ;
						$this->xbase = $x;
						$this->ybase = -$y;
					}
					$pdf_pt = $this->svg_overflow($pdfx,$pdfy);
					// mPDF 5.0.039
					$minl = min($minl,$pdf_pt['x']);
					$maxr = max($maxr,$pdf_pt['x']);
					$mint = min($mint,-$pdf_pt['y']);
					$maxb = max($maxb,-$pdf_pt['y']);
					$path_cmd .= sprintf('%.3F %.3F l ',  $pdf_pt['x']*$this->kp, $pdf_pt['y']*$this->kp);
				}
			break;
			case 'h': // a very simple horizontal line
				for($i = 0; $i<$ile_argumentow; $i++){
					$x = ($a[$i][0]); 
					if($relative){
						$y = 0;
						$pdfx = ($this->xbase + $x) ;
						$pdfy = ($this->ybase - $y) ;
						$this->xbase += $x;
						$this->ybase += -$y;
					}
					else{
						$y = -$this->ybase;
						$pdfx = $x;
						$pdfy =  -$y;
						$this->xbase = $x;
						$this->ybase = -$y;
					}
					$pdf_pt = $this->svg_overflow($pdfx,$pdfy);
					// mPDF 5.0.039
					$minl = min($minl,$pdf_pt['x']);
					$maxr = max($maxr,$pdf_pt['x']);
					$mint = min($mint,-$pdf_pt['y']);
					$maxb = max($maxb,-$pdf_pt['y']);
					$path_cmd .= sprintf('%.3F %.3F l ', $pdf_pt['x']*$this->kp, $pdf_pt['y']*$this->kp);
				}
			break;
			case 'v': // the simplest line, vertical
				for($i = 0; $i<$ile_argumentow; $i++){
					$y = ($a[$i][0]); 
					if($relative){
						$x = 0;
						$pdfx = ($this->xbase + $x);
						$pdfy = ($this->ybase - $y);
						$this->xbase += $x;
						$this->ybase += -$y;
					}
					else{
						$x = $this->xbase;
						$pdfx = $x;
						$pdfy =  -$y;
						$this->xbase = $x;
						$this->ybase = -$y;
					}
					$pdf_pt = $this->svg_overflow($pdfx,$pdfy);
					// mPDF 5.0.039
					$minl = min($minl,$pdf_pt['x']);
					$maxr = max($maxr,$pdf_pt['x']);
					$mint = min($mint,-$pdf_pt['y']);
					$maxb = max($maxb,-$pdf_pt['y']);
					$path_cmd .= sprintf('%.3F %.3F l ', $pdf_pt['x']*$this->kp, $pdf_pt['y']*$this->kp);
				}
			break;
			case 's': // bezier with first vertex equal first control
			   // mPDF 4.4.003 
			   if (!($this->lastcommand == 'C' || $this->lastcommand == 'c' || $this->lastcommand == 'S' || $this->lastcommand == 's')) {
 				$this->lastcontrolpoints = array(0,0);
			   }
				for($i = 0; $i<$ile_argumentow; $i += 4){
					$x1 = $this->lastcontrolpoints[0];
					$y1 = $this->lastcontrolpoints[1];
					$x2 = ($a[$i][0]); 
					$y2 = ($a[$i+1][0]); 
					$x = ($a[$i+2][0]); 
					$y = ($a[$i+3][0]); 
					if($relative){
						$pdfx1 = ($this->xbase + $x1);
						$pdfy1 = ($this->ybase - $y1);
						$pdfx2 = ($this->xbase + $x2);
						$pdfy2 = ($this->ybase - $y2);
						$pdfx = ($this->xbase + $x);
						$pdfy = ($this->ybase - $y);
						$this->xbase += $x;
						$this->ybase += -$y;
					}
					else{
						$pdfx1 = $this->xbase + $x1;
						$pdfy1 = $this->ybase -$y1;
						$pdfx2 = $x2;
						$pdfy2 = -$y2;
						$pdfx = $x;
						$pdfy =  -$y;
						$this->xbase = $x;
						$this->ybase = -$y;
					}
					$this->lastcontrolpoints = array(($pdfx-$pdfx2),-($pdfy-$pdfy2));	// mPDF 4.4.003 always relative

					$pdf_pt = $this->svg_overflow($pdfx,$pdfy);

					// mPDF 5.0.040
					$curves = array($pdfx1,-$pdfy1,$pdfx2,-$pdfy2,$pdfx,-$pdfy);
					$bx = calc_bezier_bbox($start, $curves);
					$minl = min($minl,$bx[0]);
					$maxr = max($maxr,$bx[2]);
					$mint = min($mint,$bx[1]);
					$maxb = max($maxb,$bx[3]);

					if( ($pdf_pt['x'] != $pdfx) || ($pdf_pt['y'] != $pdfy) )
					{
						$path_cmd .= sprintf('%.3F %.3F l ',  $pdf_pt['x']*$this->kp, $pdf_pt['y']*$this->kp);
					}
					else
					{
						$path_cmd .= sprintf('%.3F %.3F %.3F %.3F %.3F %.3F c ', $pdfx1*$this->kp, $pdfy1*$this->kp, $pdfx2*$this->kp, $pdfy2*$this->kp, $pdfx*$this->kp, $pdfy*$this->kp);
					}

			   }
			break;
			case 'c': // bezier with second vertex equal second control
			for($i = 0; $i<$ile_argumentow; $i += 6){
					$x1 = ($a[$i][0]); 
					$y1 = ($a[$i+1][0]); 
					$x2 = ($a[$i+2][0]); 
					$y2 = ($a[$i+3][0]); 
					$x = ($a[$i+4][0]); 
					$y = ($a[$i+5][0]); 


					if($relative){
						$pdfx1 = ($this->xbase + $x1);
						$pdfy1 = ($this->ybase - $y1);
						$pdfx2 = ($this->xbase + $x2);
						$pdfy2 = ($this->ybase - $y2);
						$pdfx = ($this->xbase + $x);
						$pdfy = ($this->ybase - $y);
						$this->xbase += $x;
						$this->ybase += -$y;
					}
					else{
						$pdfx1 = $x1;
						$pdfy1 = -$y1;
						$pdfx2 = $x2;
						$pdfy2 = -$y2;
						$pdfx = $x;
						$pdfy =  -$y;
						$this->xbase = $x;
						$this->ybase = -$y;
					}
					$this->lastcontrolpoints = array(($pdfx-$pdfx2),-($pdfy-$pdfy2));	// mPDF 4.4.003 always relative
					// $pdf_pt2 = $this->svg_overflow($pdfx2,$pdfy2);
					// $pdf_pt1 = $this->svg_overflow($pdfx1,$pdfy1);
					$pdf_pt = $this->svg_overflow($pdfx,$pdfy);

					// mPDF 5.0.040
					$curves = array($pdfx1,-$pdfy1,$pdfx2,-$pdfy2,$pdfx,-$pdfy);
					$bx = calc_bezier_bbox($start, $curves);
					$minl = min($minl,$bx[0]);
					$maxr = max($maxr,$bx[2]);
					$mint = min($mint,$bx[1]);
					$maxb = max($maxb,$bx[3]);

					if( ($pdf_pt['x'] != $pdfx) || ($pdf_pt['y'] != $pdfy) )
					{
						$path_cmd .= sprintf('%.3F %.3F l ',  $pdf_pt['x']*$this->kp, $pdf_pt['y']*$this->kp);
					}
					else
					{
						$path_cmd .= sprintf('%.3F %.3F %.3F %.3F %.3F %.3F c ', $pdfx1*$this->kp, $pdfy1*$this->kp, $pdfx2*$this->kp, $pdfy2*$this->kp, $pdfx*$this->kp, $pdfy*$this->kp);
					}

				}
			break;

			case 'q': // bezier quadratic avec point de control
				for($i = 0; $i<$ile_argumentow; $i += 4){
					$x1 = ($a[$i][0]); 
					$y1 = ($a[$i+1][0]); 
					$x = ($a[$i+2][0]); 
					$y = ($a[$i+3][0]); 
					if($relative){
						$pdfx = ($this->xbase + $x);
						$pdfy = ($this->ybase - $y);

						$pdfx1 = ($this->xbase + ($x1*2/3));
						$pdfy1 = ($this->ybase - ($y1*2/3));
						// mPDF 4.4.003 
    						$pdfx2 = $pdfx1 + 1/3 *($x); 
    						$pdfy2 = $pdfy1 + 1/3 *(-$y) ;

						$this->xbase += $x;
						$this->ybase += -$y;
					}
					else{
						$pdfx = $x;
						$pdfy =  -$y;

						$pdfx1 = ($this->xbase+(($x1-$this->xbase)*2/3));
						$pdfy1 = ($this->ybase-(($y1+$this->ybase)*2/3));

						$pdfx2 = ($x+(($x1-$x)*2/3));
						$pdfy2 = (-$y-(($y1-$y)*2/3));

						// mPDF 4.4.003 
    						$pdfx2 = $pdfx1 + 1/3 *($x - $this->xbase); 
    						$pdfy2 = $pdfy1 + 1/3 *(-$y - $this->ybase) ;

						$this->xbase = $x;
						$this->ybase = -$y;
					}
					$this->lastcontrolpoints = array(($pdfx-$pdfx2),-($pdfy-$pdfy2));	// mPDF 4.4.003 always relative

					$pdf_pt = $this->svg_overflow($pdfx,$pdfy);

					// mPDF 5.0.040
					$curves = array($pdfx1,-$pdfy1,$pdfx2,-$pdfy2,$pdfx,-$pdfy);
					$bx = calc_bezier_bbox($start, $curves);
					$minl = min($minl,$bx[0]);
					$maxr = max($maxr,$bx[2]);
					$mint = min($mint,$bx[1]);
					$maxb = max($maxb,$bx[3]);

					if( ($pdf_pt['x'] != $pdfx) || ($pdf_pt['y'] != $pdfy) )
					{
						$path_cmd .= sprintf('%.3F %.3F l ',  $pdf_pt['x']*$this->kp, $pdf_pt['y']*$this->kp);
					}
					else
					{
						$path_cmd .= sprintf('%.3F %.3F %.3F %.3F %.3F %.3F c ', $pdfx1*$this->kp, $pdfy1*$this->kp, $pdfx2*$this->kp, $pdfy2*$this->kp, $pdfx*$this->kp, $pdfy*$this->kp);
					}
				}
			break;
			case 't': // bezier quadratic avec point de control simetrique a lancien point de control
			   // mPDF 4.4.003 
			   if (!($this->lastcommand == 'Q' || $this->lastcommand == 'q' || $this->lastcommand == 'T' || $this->lastcommand == 't')) {
 				$this->lastcontrolpoints = array(0,0);
			   }
				for($i = 0; $i<$ile_argumentow; $i += 2){
					$x = ($a[$i][0]); 
					$y = ($a[$i+1][0]); 

					$x1 = $this->lastcontrolpoints[0];
					$y1 = $this->lastcontrolpoints[1];

					if($relative){
						$pdfx = ($this->xbase + $x);
						$pdfy = ($this->ybase - $y);

						$pdfx1 = ($this->xbase + ($x1));	// mPDF 4.4.003 
						$pdfy1 = ($this->ybase - ($y1));	// mPDF 4.4.003 
						// mPDF 4.4.003 
    						$pdfx2 = $pdfx1 + 1/3 *($x); 
    						$pdfy2 = $pdfy1 + 1/3 *(-$y) ;

						$this->xbase += $x;
						$this->ybase += -$y;
					}
					else{
						$pdfx = $x;
						$pdfy =  -$y;

						$pdfx1 = ($this->xbase + ($x1));	// mPDF 4.4.003 
						$pdfy1 = ($this->ybase - ($y1));	// mPDF 4.4.003 
						// mPDF 4.4.003 
    						$pdfx2 = $pdfx1 + 1/3 *($x - $this->xbase); 
    						$pdfy2 = $pdfy1 + 1/3 *(-$y - $this->ybase) ;

						$this->xbase = $x;
						$this->ybase = -$y;
					}

					$this->lastcontrolpoints = array(($pdfx-$pdfx2),-($pdfy-$pdfy2));	// mPDF 4.4.003 always relative

					// mPDF 5.0.040
					$curves = array($pdfx1,-$pdfy1,$pdfx2,-$pdfy2,$pdfx,-$pdfy);
					$bx = calc_bezier_bbox($start, $curves);
					$minl = min($minl,$bx[0]);
					$maxr = max($maxr,$bx[2]);
					$mint = min($mint,$bx[1]);
					$maxb = max($maxb,$bx[3]);

					$path_cmd .= sprintf('%.3F %.3F %.3F %.3F %.3F %.3F c ', $pdfx1*$this->kp, $pdfy1*$this->kp, $pdfx2*$this->kp, $pdfy2*$this->kp, $pdfx*$this->kp, $pdfy*$this->kp);
				}

			break;
			case 'a':	// Elliptical arc
				for($i = 0; $i<$ile_argumentow; $i += 7){
					$rx = ($a[$i][0]); 
					$ry = ($a[$i+1][0]); 
					$angle = ($a[$i+2][0]); //x-axis-rotation 
					$largeArcFlag = ($a[$i+3][0]); 
					$sweepFlag = ($a[$i+4][0]); 
					$x2 = ($a[$i+5][0]); 
					$y2 = ($a[$i+6][0]); 
					$x1 = $this->xbase;
					$y1 = -$this->ybase;
					if($relative){
						$x2 = $this->xbase + $x2;
						$y2 = -$this->ybase + $y2;
						$this->xbase += ($a[$i+5][0]); 
						$this->ybase += -($a[$i+6][0]); 
					}
					else{
						$this->xbase = $x2;
						$this->ybase = -$y2;
					}
					// mPDF 5.0.039 // mPDF 5.0.040
					list($pcmd, $bounds) = $this->Arcto($x1, $y1, $x2, $y2, $rx, $ry, $angle, $largeArcFlag, $sweepFlag);
					$minl = min($minl,$x2,min($bounds[0]));
					$maxr = max($maxr,$x2,max($bounds[0]));
					$mint = min($mint,$y2,min($bounds[1]));
					$maxb = max($maxb,$y2,max($bounds[1]));
					$path_cmd .= $pcmd;

				}
			break;
			case'z':
				$path_cmd .= 'h ';
				// mPDF 4.4.003
				$this->subPathInit = true;
				$newsubpath = true;
				$this->xbase = $this->spxstart;
				$this->ybase = $this->spystart;
			break;
			default:
			break;
			}

		if (!$newsubpath) { $this->subPathInit = false; }	// mPDF 4.4.003
		$this->lastcommand = $command;
		// mPDF 5.0.039
		$this->pathBBox[0] = $minl;
		$this->pathBBox[1] = $mint;
		$this->pathBBox[2] = $maxr - $this->pathBBox[0];
		$this->pathBBox[3] = $maxb - $this->pathBBox[1];
		return $path_cmd;

	}

function Arcto($x1, $y1, $x2, $y2, $rx, $ry, $angle, $largeArcFlag, $sweepFlag) {

	// mPDF 5.0.040
	$bounds = array(0=>array($x1,$x2),1=>array($y1,$y2));
	// 1. Treat out-of-range parameters as described in
	// http://www.w3.org/TR/SVG/implnote.html#ArcImplementationNotes
	// If the endpoints (x1, y1) and (x2, y2) are identical, then this
	// is equivalent to omitting the elliptical arc segment entirely
	if ($x1 == $x2 && $y1 == $y2) return array('', $bounds); // mPD 5.0.040

	// If rX = 0 or rY = 0 then this arc is treated as a straight line
	// segment (a "lineto") joining the endpoints.
	if ($rx == 0.0 || $ry == 0.0) {
	//   return array(Lineto(x2, y2), $bounds); // mPD 5.0.040
	}

	// If rX or rY have negative signs, these are dropped; the absolute
	// value is used instead.
	if ($rx<0.0) $rx = -$rx;
	if ($ry<0.0) $ry = -$ry;

	// 2. convert to center parameterization as shown in
	// http://www.w3.org/TR/SVG/implnote.html
	$sinPhi = sin(deg2rad($angle));
	$cosPhi = cos(deg2rad($angle));

	$x1dash =  $cosPhi * ($x1-$x2)/2.0 + $sinPhi * ($y1-$y2)/2.0;
	$y1dash = -$sinPhi * ($x1-$x2)/2.0 + $cosPhi * ($y1-$y2)/2.0;


	$numerator = $rx*$rx*$ry*$ry - $rx*$rx*$y1dash*$y1dash - $ry*$ry*$x1dash*$x1dash;

	if ($numerator < 0.0) { 
		//  If rX , rY and are such that there is no solution (basically,
		//  the ellipse is not big enough to reach from (x1, y1) to (x2,
		//  y2)) then the ellipse is scaled up uniformly until there is
		//  exactly one solution (until the ellipse is just big enough).

		// -> find factor s, such that numerator' with rx'=s*rx and
		//    ry'=s*ry becomes 0 :
		$s = sqrt(1.0 - $numerator/($rx*$rx*$ry*$ry));

		$rx *= $s;
		$ry *= $s;
		$root = 0.0;

	}
	else {
		$root = ($largeArcFlag == $sweepFlag ? -1.0 : 1.0) * sqrt( $numerator/($rx*$rx*$y1dash*$y1dash+$ry*$ry*$x1dash*$x1dash) );
	}

	$cxdash = $root*$rx*$y1dash/$ry;
	$cydash = -$root*$ry*$x1dash/$rx;

	$cx = $cosPhi * $cxdash - $sinPhi * $cydash + ($x1+$x2)/2.0;
	$cy = $sinPhi * $cxdash + $cosPhi * $cydash + ($y1+$y2)/2.0;


	$theta1 = $this->CalcVectorAngle(1.0, 0.0, ($x1dash-$cxdash)/$rx, ($y1dash-$cydash)/$ry);
	$dtheta = $this->CalcVectorAngle(($x1dash-$cxdash)/$rx, ($y1dash-$cydash)/$ry, (-$x1dash-$cxdash)/$rx, (-$y1dash-$cydash)/$ry);
	if (!$sweepFlag && $dtheta>0)
		$dtheta -= 2.0*M_PI;
	else if ($sweepFlag && $dtheta<0)
		$dtheta += 2.0*M_PI;

	// 3. convert into cubic bezier segments <= 90deg
	$segments = ceil(abs($dtheta/(M_PI/2.0)));
	$delta = $dtheta/$segments;
	$t = 8.0/3.0 * sin($delta/4.0) * sin($delta/4.0) / sin($delta/2.0);
	$coords = array();
	for ($i = 0; $i < $segments; $i++) {
		$cosTheta1 = cos($theta1);
		$sinTheta1 = sin($theta1);
		$theta2 = $theta1 + $delta;
		$cosTheta2 = cos($theta2);
		$sinTheta2 = sin($theta2);

		// a) calculate endpoint of the segment:
		$xe = $cosPhi * $rx*$cosTheta2 - $sinPhi * $ry*$sinTheta2 + $cx;
		$ye = $sinPhi * $rx*$cosTheta2 + $cosPhi * $ry*$sinTheta2 + $cy;

		// b) calculate gradients at start/end points of segment:
		$dx1 = $t * ( - $cosPhi * $rx*$sinTheta1 - $sinPhi * $ry*$cosTheta1);
		$dy1 = $t * ( - $sinPhi * $rx*$sinTheta1 + $cosPhi * $ry*$cosTheta1);

		$dxe = $t * ( $cosPhi * $rx*$sinTheta2 + $sinPhi * $ry*$cosTheta2);
		$dye = $t * ( $sinPhi * $rx*$sinTheta2 - $cosPhi * $ry*$cosTheta2);

		// c) draw the cubic bezier:
		$coords[$i] = array(($x1+$dx1), ($y1+$dy1), ($xe+$dxe), ($ye+$dye), $xe, $ye);

		// do next segment
		$theta1 = $theta2;
		$x1 = $xe;
		$y1 = $ye;
	}
	$path = ' ';
	foreach($coords AS $c) {
		$cpx1 = $c[0];
		$cpy1 = $c[1];
		$cpx2 = $c[2];
		$cpy2 = $c[3];
		$x2 = $c[4];
		$y2 = $c[5];
		$path .= sprintf('%.3F %.3F %.3F %.3F %.3F %.3F c ', $cpx1*$this->kp, -$cpy1*$this->kp, $cpx2*$this->kp, -$cpy2*$this->kp, $x2*$this->kp, -$y2*$this->kp)  ."\n";

		// mPDF 5.0.040
		$bounds[0][] = $c[4];
		$bounds[1][] = $c[5];
	}
	return array($path, $bounds); // mPD 5.0.040
}


	function CalcVectorAngle($ux, $uy, $vx, $vy) {
		$ta = atan2($uy, $ux);
		$tb = atan2($vy, $vx);
		if ($tb >= $ta)
			return ($tb-$ta);
		return (6.28318530718 - ($ta-$tb));
	}


	// mPDF 4.4.003
	function ConvertSVGSizePixels($size=5,$maxsize='x'){
	// maxsize in pixels (user units) or 'y' or 'x'
	// e.g. $w = $this->ConvertSVGSizePixels($arguments['w'],$this->svg_info['w']*(25.4/$this->mpdf_ref->dpi));
	// usefontsize - setfalse for e.g. margins - will ignore fontsize for % values
	// Depends of maxsize value to make % work properly. Usually maxsize == pagewidth
	// For text $maxsize = Fontsize
	// Setting e.g. margin % will use maxsize (pagewidth) and em will use fontsize

		if ($maxsize == 'y') { $maxsize = $this->svg_info['h']; }
		else if ($maxsize == 'x') { $maxsize = $this->svg_info['w']; }
		$maxsize *= (25.4/$this->mpdf_ref->dpi);	// convert pixels to mm
		$fontsize=$this->mpdf_ref->FontSize;
		//Return as pixels
		$size = $this->mpdf_ref->ConvertSize($size,$maxsize,$fontsize,false) * 1/(25.4/$this->mpdf_ref->dpi);
		return $size;
	}

	// mPDF 4.4.003
	function ConvertSVGSizePts($size=5){
	// usefontsize - setfalse for e.g. margins - will ignore fontsize for % values
	// Depends of maxsize value to make % work properly. Usually maxsize == pagewidth
	// For text $maxsize = Fontsize
	// Setting e.g. margin % will use maxsize (pagewidth) and em will use fontsize
		$maxsize=$this->mpdf_ref->FontSize;
		//Return as pts
		$size = $this->mpdf_ref->ConvertSize($size,$maxsize,false,true) * 72/25.4;
		return $size;
	}


	//
	//	fonction retracant les <rect />
	function svgRect($arguments){

		if ($arguments['h']==0 || $arguments['w']==0) { return ''; }	// mPDF 4.4.003

		$x = $this->ConvertSVGSizePixels($arguments['x'],'x');	// mPDF 4.4.003 
		$y = $this->ConvertSVGSizePixels($arguments['y'],'y');	// mPDF 4.4.003 
		$h = $this->ConvertSVGSizePixels($arguments['h'],'y');	// mPDF 4.4.003 
		$w = $this->ConvertSVGSizePixels($arguments['w'],'x');	// mPDF 4.4.003 
		$rx = $this->ConvertSVGSizePixels($arguments['rx'],'x');	// mPDF 4.4.003 
		$ry = $this->ConvertSVGSizePixels($arguments['ry'],'y');	// mPDF 4.4.003 

		if ($rx > $w/2) { $rx = $w/2; }	// mPDF 4.4.003
		if ($ry > $h/2) { $ry = $h/2; }	// mPDF 4.4.003

		if ($rx>0 and $ry == 0){$ry = $rx;}
		if ($ry>0 and $rx == 0){$rx = $ry;}

		if ($rx == 0 and $ry == 0){
			//	trace un rectangle sans angle arrondit
			$path_cmd = sprintf('%.3F %.3F m ', ($x*$this->kp), -($y*$this->kp));
			$path_cmd .= sprintf('%.3F %.3F l ', (($x+$w)*$this->kp), -($y*$this->kp));
			$path_cmd .= sprintf('%.3F %.3F l ', (($x+$w)*$this->kp), -(($y+$h)*$this->kp));
			$path_cmd .= sprintf('%.3F %.3F l ', ($x)*$this->kp, -(($y+$h)*$this->kp));
			$path_cmd .= sprintf('%.3F %.3F l h ', ($x*$this->kp), -($y*$this->kp));

			
		}
		else {
			//	trace un rectangle avec les arrondit
			//	les points de controle du bezier sont deduis grace a la constante kappa
			$kappa = 4*(sqrt(2)-1)/3;

			$kx = $kappa*$rx;
			$ky = $kappa*$ry;

			$path_cmd = sprintf('%.3F %.3F m ', ($x+$rx)*$this->kp, -$y*$this->kp);
			$path_cmd .= sprintf('%.3F %.3F l ', ($x+($w-$rx))*$this->kp, -$y*$this->kp);
			$path_cmd .= sprintf('%.3F %.3F %.3F %.3F %.3F %.3F c ', ($x+($w-$rx+$kx))*$this->kp, -$y*$this->kp, ($x+$w)*$this->kp, (-$y+(-$ry+$ky))*$this->kp, ($x+$w)*$this->kp, (-$y+(-$ry))*$this->kp );
			$path_cmd .= sprintf('%.3F %.3F l ', ($x+$w)*$this->kp, (-$y+(-$h+$ry))*$this->kp);
		 	$path_cmd .= sprintf('%.3F %.3F %.3F %.3F %.3F %.3F c ', ($x+$w)*$this->kp, (-$y+(-$h-$ky+$ry))*$this->kp, ($x+($w-$rx+$kx))*$this->kp, (-$y+(-$h))*$this->kp, ($x+($w-$rx))*$this->kp, (-$y+(-$h))*$this->kp );

			$path_cmd .= sprintf('%.3F %.3F l ', ($x+$rx)*$this->kp, (-$y+(-$h))*$this->kp);
			$path_cmd .= sprintf('%.3F %.3F %.3F %.3F %.3F %.3F c ', ($x+($rx-$kx))*$this->kp, (-$y+(-$h))*$this->kp, $x*$this->kp, (-$y+(-$h-$ky+$ry))*$this->kp, $x*$this->kp, (-$y+(-$h+$ry))*$this->kp );
			$path_cmd .= sprintf('%.3F %.3F l ', $x*$this->kp, (-$y+(-$ry))*$this->kp);
			$path_cmd .= sprintf('%.3F %.3F %.3F %.3F %.3F %.3F c h ', $x*$this->kp, (-$y+(-$ry+$ky))*$this->kp, ($x+($rx-$kx))*$this->kp, -$y*$this->kp, ($x+$rx)*$this->kp, -$y*$this->kp );


		}
		return $path_cmd;
	}

	//
	//	fonction retracant les <ellipse /> et <circle />
	//	 le cercle est tracé grave a 4 bezier cubic, les poitn de controles
	//	sont deduis grace a la constante kappa * rayon
	function svgEllipse($arguments){
		if ($arguments['rx']==0 || $arguments['ry']==0) { return ''; }	// mPDF 4.4.003

		$kappa = 4*(sqrt(2)-1)/3;

		$cx = $this->ConvertSVGSizePixels($arguments['cx'],'x');	// mPDF 4.4.003 
		$cy = $this->ConvertSVGSizePixels($arguments['cy'],'y');	// mPDF 4.4.003 
		$rx = $this->ConvertSVGSizePixels($arguments['rx'],'x');	// mPDF 4.4.003 
		$ry = $this->ConvertSVGSizePixels($arguments['ry'],'y');	// mPDF 4.4.003 

		$x1 = $cx;
		$y1 = -$cy+$ry;

		$x2 = $cx+$rx;
		$y2 = -$cy;

		$x3 = $cx;
		$y3 = -$cy-$ry;

		$x4 = $cx-$rx;
		$y4 = -$cy;

		$path_cmd = sprintf('%.3F %.3F m ', $x1*$this->kp, $y1*$this->kp);
		$path_cmd .= sprintf('%.3F %.3F %.3F %.3F %.3F %.3F c ', ($x1+($rx*$kappa))*$this->kp, $y1*$this->kp, $x2*$this->kp, ($y2+($ry*$kappa))*$this->kp, $x2*$this->kp, $y2*$this->kp);
		$path_cmd .= sprintf('%.3F %.3F %.3F %.3F %.3F %.3F c ', $x2*$this->kp, ($y2-($ry*$kappa))*$this->kp, ($x3+($rx*$kappa))*$this->kp, $y3*$this->kp, $x3*$this->kp, $y3*$this->kp);
		$path_cmd .= sprintf('%.3F %.3F %.3F %.3F %.3F %.3F c ', ($x3-($rx*$kappa))*$this->kp, $y3*$this->kp, $x4*$this->kp, ($y4-($ry*$kappa))*$this->kp, $x4*$this->kp, $y4*$this->kp);
		$path_cmd .= sprintf('%.3F %.3F %.3F %.3F %.3F %.3F c ', $x4*$this->kp, ($y4+($ry*$kappa))*$this->kp, ($x1-($rx*$kappa))*$this->kp, $y1*$this->kp, $x1*$this->kp, $y1*$this->kp);
		$path_cmd .= 'h ';

		return $path_cmd;

	}

	//
	//	fonction retracant les <polyline /> et les <line />
	function svgPolyline($arguments,$ispolyline=true){
		if ($ispolyline) {
			$xbase = $arguments[0] ;
			$ybase = - $arguments[1] ;
		}
		else {
			if ($arguments[0]==$arguments[2] && $arguments[1]==$arguments[3]) { return ''; }	// mPDF 4.4.003  Zero length line
			$xbase = $this->ConvertSVGSizePixels($arguments[0],'x');	// mPDF 4.4.003 
			$ybase = - $this->ConvertSVGSizePixels($arguments[1],'y');	// mPDF 4.4.003 
		}
		$path_cmd = sprintf('%.3F %.3F m ', $xbase*$this->kp, $ybase*$this->kp);
		for ($i = 2; $i<count($arguments);$i += 2) {
			if ($ispolyline) {
				$tmp_x = $arguments[$i] ;
				$tmp_y = - $arguments[($i+1)] ;
			}
			else {
				$tmp_x = $this->ConvertSVGSizePixels($arguments[$i],'x') ;	// mPDF 4.4.003 
				$tmp_y = - $this->ConvertSVGSizePixels($arguments[($i+1)],'y') ;	// mPDF 4.4.003 
			}
			$path_cmd .= sprintf('%.3F %.3F l ', $tmp_x*$this->kp, $tmp_y*$this->kp);
		}

	//	$path_cmd .= 'h '; // ?? In error - don't close subpath here
		return $path_cmd;

	}

	//
	//	fonction retracant les <polygone />
	function svgPolygon($arguments){
		$xbase = $arguments[0] ;
		$ybase = - $arguments[1] ;
		$path_cmd = sprintf('%.3F %.3F m ', $xbase*$this->kp, $ybase*$this->kp);
		for ($i = 2; $i<count($arguments);$i += 2) {
			$tmp_x = $arguments[$i] ;
			$tmp_y = - $arguments[($i+1)] ;

			$path_cmd .= sprintf('%.3F %.3F l ', $tmp_x*$this->kp, $tmp_y*$this->kp);

		}
		$path_cmd .= sprintf('%.3F %.3F l ', $xbase*$this->kp, $ybase*$this->kp);
		$path_cmd .= 'h ';
		return $path_cmd;

	}

	//
	//	write string to image
	function svgText() {
		// $tmp = count($this->txt_style)-1;
		$current_style = array_pop($this->txt_style);
		$style = '';
		$render = -1;
		if(isset($this->txt_data[2]))
		{
			// select font
			$style .= ($current_style['font-weight'] == 'bold')?'B':'';
			$style .= ($current_style['font-style'] == 'italic')?'I':'';
			$size = $current_style['font-size']*$this->kf;	// mPDF 5.0.039

			// mPDF 5.0
			$current_style['font-family'] = $this->mpdf_ref->SetFont($current_style['font-family'],$style,$size,false);
			$this->mpdf_ref->CurrentFont['fo'] = true;	// mPDF 5.0.039


			// mPDF 5.0.041
			$opacitystr = '';
			$opacity = 1;
			if (isset($current_style['fill-opacity'])) {
				if ($current_style['fill-opacity'] == 0) { $opacity = 0; }
				else if ($current_style['fill-opacity'] > 1) { $opacity = 1; }
				else if ($current_style['fill-opacity'] > 0) { $opacity = $current_style['fill-opacity']; }
				else if ($current_style['fill-opacity'] < 0) { $opacity = 0; }
			}
			$gs = $this->mpdf_ref->AddExtGState(array('ca'=>$opacity, 'BM'=>'/Normal'));
			$this->mpdf_ref->extgstates[$gs]['fo'] = true;	// mPDF 5.0.039
			$opacitystr = sprintf(' /GS%d gs ', $gs);

			// mPDF 5.0.051
			$fillstr = '';
			if (isset($current_style['fill']) && $current_style['fill']!='none') {
				$col = $this->mpdf_ref->ConvertColor($current_style['fill']);
				// mPDF 5.0.051
				$fillstr = $this->mpdf_ref->SetFColor($col, true);
				$render = "0";	// Fill (only)
			}
			$strokestr = '';
			if (isset($current_style['stroke-width']) && $current_style['stroke-width']>0 && $current_style['stroke']!='none') {
				$scol = $this->mpdf_ref->ConvertColor($current_style['stroke']);
				if ($scol) { 
					$strokestr .= $this->mpdf_ref->SetDColor($scol, true).' ';	// mPDF 5.0.051
				}
				$linewidth = $this->ConvertSVGSizePixels($current_style['stroke-width']);
				if ($linewidth > 0) { 
					$strokestr .= sprintf('%.3F w 1 J 1 j ',$linewidth*$this->kp); 
					if ($render == -1) { $render = "1"; }	// stroke only
					else { $render = "2"; } 	// fill and stroke
				}
			}
			if ($render == -1) { return ''; }	

			$x = $this->ConvertSVGSizePixels($this->txt_data[0],'x');	// mPDF 4.4.003 
			$y = $this->ConvertSVGSizePixels($this->txt_data[1],'y');	// mPDF 4.4.003
			$txt = $this->txt_data[2];

			// mPDF 4.4.003
			$txt = preg_replace('/\f/','',$txt); 
			$txt = preg_replace('/\r/','',$txt); 
			$txt = preg_replace('/\n/',' ',$txt); 
			$txt = preg_replace('/\t/',' ',$txt); 
			$txt = preg_replace("/[ ]+/u",' ',$txt);

			$txt = trim($txt);

			$txt = $this->mpdf_ref->purify_utf8_text($txt);
			if ($this->mpdf_ref->text_input_as_HTML) {
				$txt = $this->mpdf_ref->all_entities_to_utf8($txt);
			}

			// mPDF 5.0
			if ($this->mpdf_ref->usingCoreFont) { $txt = mb_convert_encoding($txt,$this->mpdf_ref->mb_enc,'UTF-8'); }
			if (preg_match("/([".$this->mpdf_ref->pregRTLchars."])/u", $txt)) { $this->mpdf_ref->biDirectional = true; } // mPDF 4.4.003

			$this->mpdf_ref->magic_reverse_dir($txt, true, 'ltr');	// mPDF 5.0.054
			$this->mpdf_ref->ConvertIndic($txt);


			if ($current_style['text-anchor']=='middle') {
				$tw = $this->mpdf_ref->GetStringWidth($txt)*_MPDFK/2;	// mPDF 4.4.003	// mPDF 5.4.09
			}
			else if ($current_style['text-anchor']=='end') {
				$tw = $this->mpdf_ref->GetStringWidth($txt)*_MPDFK;	// mPDF 4.4.003	// mPDF 5.4.09
			}
			else $tw = 0;

			if (!$this->mpdf_ref->usingCoreFont) {
				$this->mpdf_ref->UTF8StringToArray($txt);	// mPDF 5.0 adds chars to subset list
				$txt= $this->mpdf_ref->UTF8ToUTF16BE($txt, false);
			}
			$txt='('.$this->mpdf_ref->_escape($txt).')'; 
			$this->mpdf_ref->CurrentFont['used']= true;

			$pdfx = $x - $tw/$this->kp;	// mPDF 4.4.009
			$pdfy =  -$y  ;
			$xbase = $x;
			$ybase = -$y;

			// mPDF 5.7.2
			$path_cmd =  sprintf('q BT /F%d %.3F Tf %s %.3F %.3F Td %s Tr %s %s %s Tj ET Q ',$this->mpdf_ref->CurrentFont['i'], $this->mpdf_ref->FontSizePt, $opacitystr, $pdfx*$this->kp,$pdfy*$this->kp,$render,$fillstr,$strokestr,$txt)."\n";
			unset($this->txt_data[0], $this->txt_data[1],$this->txt_data[2]);

			if (isset($current_style['font-size-parent'])) {
				$this->mpdf_ref->SetFontSize($current_style['font-size-parent']);
			}
		}
		else
		{
			return ' ';
		}
		return $path_cmd;
	}


function svgDefineTxtStyle($critere_style)
{
		// get copy of current/default txt style, and modify it with supplied attributes
		$tmp = count($this->txt_style)-1;
		$current_style = $this->txt_style[$tmp];
		if (isset($critere_style['style'])){
			if (preg_match('/fill:\s*rgb\((\d+),\s*(\d+),\s*(\d+)\)/',$critere_style['style'], $m)) {
				$current_style['fill'] = '#'.str_pad(dechex($m[1]), 2, "0", STR_PAD_LEFT).str_pad(dechex($m[2]), 2, "0", STR_PAD_LEFT).str_pad(dechex($m[3]), 2, "0", STR_PAD_LEFT);
			}
			else { $tmp = preg_replace("/(.*)fill:\s*([a-z0-9#_()]*|none)(.*)/i","$2",$critere_style['style']);
				if ($tmp != $critere_style['style']){ $current_style['fill'] = $tmp; }
			}

			$tmp = preg_replace("/(.*)fill-opacity:\s*([a-z0-9.]*|none)(.*)/i","$2",$critere_style['style']);
			if ($tmp != $critere_style['style']){ $current_style['fill-opacity'] = $tmp;}

			$tmp = preg_replace("/(.*)fill-rule:\s*([a-z0-9#]*|none)(.*)/i","$2",$critere_style['style']);
			if ($tmp != $critere_style['style']){ $current_style['fill-rule'] = $tmp;}

			if (preg_match('/stroke:\s*rgb\((\d+),\s*(\d+),\s*(\d+)\)/',$critere_style['style'], $m)) {
				$current_style['stroke'] = '#'.str_pad(dechex($m[1]), 2, "0", STR_PAD_LEFT).str_pad(dechex($m[2]), 2, "0", STR_PAD_LEFT).str_pad(dechex($m[3]), 2, "0", STR_PAD_LEFT);
			}
			else { $tmp = preg_replace("/(.*)stroke:\s*([a-z0-9#]*|none)(.*)/i","$2",$critere_style['style']);
				if ($tmp != $critere_style['style']){ $current_style['stroke'] = $tmp; }
			}
			
			$tmp = preg_replace("/(.*)stroke-linecap:\s*([a-z0-9#]*|none)(.*)/i","$2",$critere_style['style']);
			if ($tmp != $critere_style['style']){ $current_style['stroke-linecap'] = $tmp;}

			$tmp = preg_replace("/(.*)stroke-linejoin:\s*([a-z0-9#]*|none)(.*)/i","$2",$critere_style['style']);
			if ($tmp != $critere_style['style']){ $current_style['stroke-linejoin'] = $tmp;}
			
			$tmp = preg_replace("/(.*)stroke-miterlimit:\s*([a-z0-9#]*|none)(.*)/i","$2",$critere_style['style']);
			if ($tmp != $critere_style['style']){ $current_style['stroke-miterlimit'] = $tmp;}
			
			$tmp = preg_replace("/(.*)stroke-opacity:\s*([a-z0-9.]*|none)(.*)/i","$2",$critere_style['style']);
			if ($tmp != $critere_style['style']){ $current_style['stroke-opacity'] = $tmp; }
			
			$tmp = preg_replace("/(.*)stroke-width:\s*([a-z0-9.]*|none)(.*)/i","$2",$critere_style['style']);
			if ($tmp != $critere_style['style']){ $current_style['stroke-width'] = $tmp;}

			$tmp = preg_replace("/(.*)stroke-dasharray:\s*([a-z0-9., ]*|none)(.*)/i","$2",$critere_style['style']);
			if ($tmp != $critere_style['style']){ $current_style['stroke-dasharray'] = $tmp;}

			$tmp = preg_replace("/(.*)stroke-dashoffset:\s*([a-z0-9.]*|none)(.*)/i","$2",$critere_style['style']);
			if ($tmp != $critere_style['style']){ $current_style['stroke-dashoffset'] = $tmp;}

			// mPDF 5.7.2
			$tmp = preg_replace("/(.*)font-family:\s*([a-z0-9.\"' ,\-]*|none)(.*)/i","$2",$critere_style['style']);
			if ($tmp != $critere_style['style']){ $critere_style['font-family'] = $tmp;}

			$tmp = preg_replace("/(.*)font-size:\s*([a-z0-9.]*|none)(.*)/i","$2",$critere_style['style']);
			if ($tmp != $critere_style['style']){ $critere_style['font-size'] = $tmp;}

			$tmp = preg_replace("/(.*)font-weight:\s*([a-z0-9.]*|none)(.*)/i","$2",$critere_style['style']);
			if ($tmp != $critere_style['style']){ $critere_style['font-weight'] = $tmp;}

			$tmp = preg_replace("/(.*)font-style:\s*([a-z0-9.]*|none)(.*)/i","$2",$critere_style['style']);
			if ($tmp != $critere_style['style']){ $critere_style['font-style'] = $tmp;}

		}

		if (isset($critere_style['font'])){

			// [ [ <'font-style'> || <'font-variant'> || <'font-weight'> ]?<'font-size'> [ / <'line-height'> ]? <'font-family'> ]

			$tmp = preg_replace("/(.*)(italic|oblique)(.*)/i","$2",$critere_style['font']);
			if ($tmp != $critere_style['font']){ 
				if($tmp == 'oblique'){
					$tmp = 'italic';
				}
				$current_style['font-style'] = $tmp;
			}
			$tmp = preg_replace("/(.*)(bold|bolder)(.*)/i","$2",$critere_style['font']);
			if ($tmp != $critere_style['font']){ 
				if($tmp == 'bolder'){
					$tmp = 'bold';
				}
				$current_style['font-weight'] = $tmp;
			}
			
			// select digits not followed by percent sign nor preceeded by forward slash
			$tmp = preg_replace("/(.*)\b(\d+)[\b|\/](.*)/i","$2",$critere_style['font']);
			if ($tmp != $critere_style['font']){ 
				$current_style['font-size'] = $this->ConvertSVGSizePts($tmp); 
				$this->mpdf_ref->SetFont('','',$current_style['font-size'],false);
			}
			
		}

		if(isset($critere_style['fill'])){
			$current_style['fill'] = $critere_style['fill'];
		}
		if(isset($critere_style['stroke'])){
			$current_style['stroke'] = $critere_style['stroke'];
		}
		if(isset($critere_style['stroke-width'])){
			$current_style['stroke-width'] = $critere_style['stroke-width'];
		}
		
		if(isset($critere_style['font-style'])){
			if(strtolower($critere_style['font-style']) == 'oblique') 
			{
				$critere_style['font-style'] = 'italic';
			}
			$current_style['font-style'] = $critere_style['font-style'];
		}
		
		if(isset($critere_style['font-weight'])){
			if(strtolower($critere_style['font-weight']) == 'bolder')
			{
				$critere_style['font-weight'] = 'bold';
			}
			$current_style['font-weight'] = $critere_style['font-weight'];
		}
		
		if(isset($critere_style['font-size'])){
			// mPDF 5.4.12
			if (strpos($critere_style['font-size'], '%')!==false) {
				$current_style['font-size-parent'] = $current_style['font-size'];
			}
			$current_style['font-size'] = $this->ConvertSVGSizePts($critere_style['font-size']);
			$this->mpdf_ref->SetFont('','',$current_style['font-size'],false);
		}

		if(isset($critere_style['font-family'])){
			$v = $critere_style['font-family'];
			$aux_fontlist = explode(",",$v);
			$found = 0;
			foreach($aux_fontlist AS $f) {
				$fonttype = trim($f);
				$fonttype = preg_replace('/["\']*(.*?)["\']*/','\\1',$fonttype);
				$fonttype = preg_replace('/ /','',$fonttype);
				$v = strtolower(trim($fonttype));
				if (isset($this->mpdf_ref->fonttrans[$v]) && $this->mpdf_ref->fonttrans[$v]) { $v = $this->mpdf_ref->fonttrans[$v]; }
				if ((!$this->mpdf_ref->usingCoreFont && in_array($v,$this->mpdf_ref->available_unifonts)) || 
					($this->mpdf_ref->usingCoreFont && in_array($v,array('courier','times','helvetica','arial'))) || 
					in_array($v, array('sjis','uhc','big5','gb'))) { 
					$current_style['font-family'] = $v; 
					$found = 1;
					break;
				}
			}
			if (!$found) {
			   foreach($aux_fontlist AS $f) {
				$fonttype = trim($f);
				$fonttype = preg_replace('/["\']*(.*?)["\']*/','\\1',$fonttype);
				$fonttype = preg_replace('/ /','',$fonttype);
				$v = strtolower(trim($fonttype));
				if (isset($this->mpdf_ref->fonttrans[$v]) && $this->mpdf_ref->fonttrans[$v]) { $v = $this->mpdf_ref->fonttrans[$v]; }
				if (in_array($v,$this->mpdf_ref->sans_fonts) || in_array($v,$this->mpdf_ref->serif_fonts) || in_array($v,$this->mpdf_ref->mono_fonts) ) { 
					$current_style['font-family'] = $v; 
					break;
				}
			   }
			}
		}
	
		if(isset($critere_style['text-anchor'])){
			$current_style['text-anchor'] = $critere_style['text-anchor'];
		}
	
	// add current style to text style array (will remove it later after writing text to svg_string)
	array_push($this->txt_style,$current_style);
}



	//
	//	fonction ajoutant un gradient
	function svgAddGradient($id,$array_gradient){

		$this->svg_gradient[$id] = $array_gradient;

	}
	//
	//	Ajoute une couleur dans le gradient correspondant

	//
	//	function ecrivant dans le svgstring
	function svgWriteString($content){

		$this->svg_string .= $content;

	}



	//	analise le svg et renvoie aux fonctions precedente our le traitement
	function ImageSVG($data){
		$this->svg_info = array();

		// mPDF 4.4.006
		if (preg_match('/<!ENTITY/si',$data)) {
			// Get User-defined entities
			preg_match_all('/<!ENTITY\s+([a-z]+)\s+\"(.*?)\">/si',$data, $ent);
			// Replace entities
			for ($i=0; $i<count($ent[0]); $i++) {
				$data = preg_replace('/&'.preg_quote($ent[1][$i],'/').';/is', $ent[2][$i], $data);
			}
		}


		if (preg_match('/xlink:href=/si',$data)) {
			// GRADIENTS
			// Get links
			preg_match_all('/(<(linearGradient|radialgradient)[^>]*)xlink:href=["\']#(.*?)["\'](.*?)\/>/si',$data, $links);
			if (count($links[0])) { $links[5] = array(); }
			// Delete links from data - keeping in $links
			for ($i=0; $i<count($links[0]); $i++) {
				$links[5][$i] = 'tmpLink'.RAND(100000,9999999);
				$data = preg_replace('/'.preg_quote($links[0][$i],'/').'/is', '<MYLINKS'.$links[5][$i].'>' , $data);
			}
			// Get targets
			preg_match_all('/<(linearGradient|radialgradient)([^>]*)id=["\'](.*?)["\'](.*?)>(.*?)<\/(linearGradient|radialgradient)>/si',$data, $m);
			$targets = array();
			$stops = array();
			// keeping in $targets
			for ($i=0; $i<count($m[0]); $i++) {
				$stops[$m[3][$i]] = $m[5][$i];
			}
			// Add back links this time as targets (gradients)
			for ($i=0; $i<count($links[0]); $i++) {
				$def = $links[1][$i] .' '.$links[4][$i].'>'. $stops[$links[3][$i]].'</'.$links[2][$i] .'>' ;
				$data = preg_replace('/<MYLINKS'.$links[5][$i].'>/is', $def , $data);
			}

			// mPDF 5.7.2
			// <USE>
			preg_match_all('/<use ([^>]*)xlink:href=["\']#([^>]*?)["\']([^>]*)\/>/si',$data, $links);
			for ($i=0; $i<count($links[0]); $i++) {

				// Get the item to use from defs
				$insert = '';
				if (preg_match('/<([a-zA-Z]*) [^>]*id=["\']'.$links[2][$i].'["\'][^>]*\/>/si',$data, $m)) {
					$insert = $m[0]; 
				}
				if (!$insert && preg_match('/<([a-zA-Z]*) [^>]*id=["\']'.$links[2][$i].'["\']/si',$data, $m)) {

					if (preg_match('/<'.$m[1].'[^>]*id=["\']'.$links[2][$i].'["\'][^>]*>.*?<\/'.$m[1].'>/si',$data, $m)) {
						$insert = $m[0]; 
					}
				}

				if ($insert) {

					$inners = $links[1][$i] . ' ' . $links[3][$i];
					// Change x,y coords to translate()
					if (preg_match('/y=["\']([^>]*?)["\']/', $inners, $m)) { $y = $m[1]; }
					else { $y = 0; }
					if (preg_match('/x=["\']([^>]*?)["\']/', $inners, $m)) { $x = $m[1]; }
					else { $x = 0; }
					if ($x || $y) {
						$inners = preg_replace('/(y|x)=["\']([^>]*?)["\']/', '', $inners);
						if (preg_match('/transform=["\']([^>]*?)["\']/', $inners, $m)) {
							if (preg_match('/translate\(\s*([0-9\.]+)\s*,\s*([0-9\.]+)\s*\)/', $m[1], $mm)) {
								$transform = $m[1];	// transform="...."
								$x += $mm[1];
								$y += $mm[2];
								$transform = preg_replace('/'.preg_quote($mm[0],'/').'/', '', $transform);
								$transform = 'transform="'.$transform.' translate('.$x.', '.$y.')"';
								$inners = preg_replace('/'.preg_quote($m[0],'/').'/is', $transform, $inners);
							}
							else {
								$inners = preg_replace('/'.preg_quote($m[0],'/').'/is', 'transform="'.$m[1].' translate('.$x.', '.$y.')"', $inners);
							}
						}
						else {
							$inners .= ' transform="translate('.$x.', '.$y.')"';
						}
					}
				}
				$replacement = '<g '.$inners.'>'.$insert.'</g>';
				$data = preg_replace('/'.preg_quote($links[0][$i],'/').'/is', $replacement, $data);
			}
			preg_match_all('/<use ([^>]*)xlink:href=["\']#([^>]*?)["\']([^>]*)>\s*<\/use>/si',$data, $links);
			for ($i=0; $i<count($links[0]); $i++) {

				// Get the item to use from defs
				$insert = '';
				if (preg_match('/<([a-zA-Z]*) [^>]*id=["\']'.$links[2][$i].'["\'][^>]*\/>/si',$data, $m)) {
					$insert = $m[0]; 
				}
				if (!$insert && preg_match('/<([a-zA-Z]*) [^>]*id=["\']'.$links[2][$i].'["\']/si',$data, $m)) {

					if (preg_match('/<'.$m[1].'[^>]*id=["\']'.$links[2][$i].'["\'][^>]*>.*?<\/'.$m[1].'>/si',$data, $m)) {
						$insert = $m[0]; 
					}
				}

				if ($insert) {

					$inners = $links[1][$i] . ' ' . $links[3][$i];
					// Change x,y coords to translate()
					if (preg_match('/y=["\']([^>]*?)["\']/', $inners, $m)) { $y = $m[1]; }
					else { $y = 0; }
					if (preg_match('/x=["\']([^>]*?)["\']/', $inners, $m)) { $x = $m[1]; }
					else { $x = 0; }
					if ($x || $y) {
						$inners = preg_replace('/(y|x)=["\']([^>]*?)["\']/', '', $inners);
						if (preg_match('/transform=["\']([^>]*?)["\']/', $inners, $m)) {
							if (preg_match('/translate\(\s*([0-9\.]+)\s*,\s*([0-9\.]+)\s*\)/', $m[1], $mm)) {
								$transform = $m[1];	// transform="...."
								$x += $mm[1];
								$y += $mm[2];
								$transform = preg_replace('/'.preg_quote($mm[0],'/').'/', '', $transform);
								$transform = 'transform="'.$transform.' translate('.$x.', '.$y.')"';
								$inners = preg_replace('/'.preg_quote($m[0],'/').'/is', $transform, $inners);
							}
							else {
								$inners = preg_replace('/'.preg_quote($m[0],'/').'/is', 'transform="'.$m[1].' translate('.$x.', '.$y.')"', $inners);
							}
						}
						else {
							$inners .= ' transform="translate('.$x.', '.$y.')"';
						}
					}
					$replacement = '<g '.$inners.'>'.$insert.'</g>';
					$data = preg_replace('/'.preg_quote($links[0][$i],'/').'/is', $replacement, $data);


				}
			}
		}
		// Removes <pattern>
		$data = preg_replace('/<pattern.*?<\/pattern>/is', '', $data);
		// Removes <marker>
		$data = preg_replace('/<marker.*?<\/marker>/is', '', $data);

		$this->svg_info['data'] = $data;

		$this->svg_string = '';
		
		//
		//	chargement unique des fonctions
		if(!function_exists("xml_svg2pdf_start")){

			function xml_svg2pdf_start($parser, $name, $attribs){
				//
				//	definition
				global $svg_class, $last_gradid;

				// mPDF 5.7.2
				if (strtolower($name) == 'lineargradient'){
						$tmp_gradient = array(
							'type' => 'linear',
							'info' => array(
								'x1' => $attribs['x1'],
								'y1' => $attribs['y1'],
								'x2' => $attribs['x2'],
								'y2' => $attribs['y2']
							),
							'transform' => $attribs['gradientTransform'],
							'units' => $attribs['gradientUnits'],
							'spread' => $attribs['spreadMethod'],
							'color' => array()
						);
						$last_gradid = $attribs['id'];
						$svg_class->svgAddGradient($attribs['id'],$tmp_gradient);
					return;
				}
				else if (strtolower($name) == 'radialgradient'){
						$tmp_gradient = array(
							'type' => 'radial',
							'info' => array(
								'x0' => $attribs['cx'],
								'y0' => $attribs['cy'],
								'x1' => $attribs['fx'],
								'y1' => $attribs['fy'],
								'r' => $attribs['r']
							),
							'transform' => $attribs['gradientTransform'],
							'units' => $attribs['gradientUnits'],
							'spread' => $attribs['spreadMethod'],
							'color' => array()
						);
						$last_gradid = $attribs['id'];
						$svg_class->svgAddGradient($attribs['id'],$tmp_gradient);
					return;
				}
				else if (strtolower($name) == 'stop'){
						if (!$last_gradid) break;
						if (isset($attribs['style']) AND preg_match('/stop-color:\s*([^;]*)/i',$attribs['style'],$m)) {
							$color = trim($m[1]);
						} else if (isset($attribs['stop-color'])) {
							$color = $attribs['stop-color'];
						}
						$col = $svg_class->mpdf_ref->ConvertColor($color);

						if ($col{0}==3 || $col{0}==5) {	// RGB
							$color_final = sprintf('%.3F %.3F %.3F',ord($col{1})/255,ord($col{2})/255,ord($col{3})/255);
							$svg_class->svg_gradient[$last_gradid]['colorspace']='RGB';
						}
						else if ($col{0}==4 || $col{0}==6) {	// CMYK
							$color_final = sprintf('%.3F %.3F %.3F %.3F',ord($col{1})/100,ord($col{2})/100,ord($col{3})/100,ord($col{4})/100);
							$svg_class->svg_gradient[$last_gradid]['colorspace']='CMYK';
						}
						else if ($col{0}==1) {	// Grayscale
							$color_final = sprintf('%.3F',ord($col{1})/255);
							$svg_class->svg_gradient[$last_gradid]['colorspace']='Gray';
						}

						$stop_opacity = 1;
						if (isset($attribs['style']) AND preg_match('/stop-opacity:\s*([0-9.]*)/i',$attribs['style'],$m)) {
							$stop_opacity = $m[1];
						} else if (isset($attribs['stop-opacity'])) {
							$stop_opacity = $attribs['stop-opacity'];
						}
						else if ($col{0}==5) {	// RGBa
							$stop_opacity = ord($col{4}/100);
						}
						else if ($col{0}==6) {	// CMYKa
							$stop_opacity = ord($col{5}/100);
						}

						$tmp_color = array(
							'color' => $color_final,
							'offset' => $attribs['offset'],
							'opacity' => $stop_opacity
						);
						array_push($svg_class->svg_gradient[$last_gradid]['color'],$tmp_color);
					return;
				}
				if ($svg_class->inDefs) { return; }

				$svg_class->xbase = 0;
				$svg_class->ybase = 0;
				switch (strtolower($name)){

		 		// mPDF 5.0.039	- Don't output stuff inside <defs>
				case 'defs':
					$svg_class->inDefs = true;
					return;

				case 'svg':
					$svg_class->svgOffset($attribs);
					break;

				case 'path':
					$path = $attribs['d'];
					// mPDF 5.6.65
					preg_match_all('/([MZLHVCSQTAmzlhvcsqta])([eE ,\-.\d]+)*/', $path, $commands, PREG_SET_ORDER);
					$path_cmd = '';
					$svg_class->subPathInit = true;
					// mPDF 5.0.039
					$svg_class->pathBBox = array(999999,999999,-999999,-999999);
					foreach($commands as $c){
						if(count($c)==3 || $c[2]==''){
							list($tmp, $command, $arguments) = $c;
						}
						else{
							list($tmp, $command) = $c;
							$arguments = '';
						}

						$path_cmd .= $svg_class->svgPath($command, $arguments);
					}
					// mPDF 5.0.039
					if ($svg_class->pathBBox[2]==-1999998) { $svg_class->pathBBox[2] = 100; }
					if ($svg_class->pathBBox[3]==-1999998) { $svg_class->pathBBox[3] = 100; }
					if ($svg_class->pathBBox[0]==999999) { $svg_class->pathBBox[0] = 0; }
					if ($svg_class->pathBBox[1]==999999) { $svg_class->pathBBox[1] = 0; }
					$critere_style = $attribs;
					unset($critere_style['d']);
					$path_style = $svg_class->svgDefineStyle($critere_style);
					break;

				case 'rect':
					if (!isset($attribs['x'])) {$attribs['x'] = 0;}
					if (!isset($attribs['y'])) {$attribs['y'] = 0;}
					if (!isset($attribs['rx'])) {$attribs['rx'] = 0;}
					if (!isset($attribs['ry'])) {$attribs['ry'] = 0;}
					$arguments = array(
						'x' => $attribs['x'],
						'y' => $attribs['y'],
						'w' => $attribs['width'],
						'h' => $attribs['height'],
						'rx' => $attribs['rx'],
						'ry' => $attribs['ry']
					);
					$path_cmd =  $svg_class->svgRect($arguments);
					$critere_style = $attribs;
					unset($critere_style['x'],$critere_style['y'],$critere_style['rx'],$critere_style['ry'],$critere_style['height'],$critere_style['width']);
					$path_style = $svg_class->svgDefineStyle($critere_style);
					break;

				case 'circle':
					if (!isset($attribs['cx'])) {$attribs['cx'] = 0;}
					if (!isset($attribs['cy'])) {$attribs['cy'] = 0;}
					$arguments = array(
						'cx' => $attribs['cx'],
						'cy' => $attribs['cy'],
						'rx' => $attribs['r'],
						'ry' => $attribs['r']
					);
					$path_cmd =  $svg_class->svgEllipse($arguments);
					$critere_style = $attribs;
					unset($critere_style['cx'],$critere_style['cy'],$critere_style['r']);
					$path_style = $svg_class->svgDefineStyle($critere_style);
					break;

				case 'ellipse':
					if (!isset($attribs['cx'])) {$attribs['cx'] = 0;}
					if (!isset($attribs['cy'])) {$attribs['cy'] = 0;}
					$arguments = array(
						'cx' => $attribs['cx'],
						'cy' => $attribs['cy'],
						'rx' => $attribs['rx'],
						'ry' => $attribs['ry']
					);
					$path_cmd =  $svg_class->svgEllipse($arguments);
					$critere_style = $attribs;
					unset($critere_style['cx'],$critere_style['cy'],$critere_style['rx'],$critere_style['ry']);
					$path_style = $svg_class->svgDefineStyle($critere_style);
					break;

				case 'line':
					$arguments = array($attribs['x1'],$attribs['y1'],$attribs['x2'],$attribs['y2']);
					$path_cmd =  $svg_class->svgPolyline($arguments,false);	//  mPDF 4.4.003
					$critere_style = $attribs;
					unset($critere_style['x1'],$critere_style['y1'],$critere_style['x2'],$critere_style['y2']);
					$path_style = $svg_class->svgDefineStyle($critere_style);
					break;

				case 'polyline':
					$path = $attribs['points'];
					preg_match_all('/[0-9\-\.]*/',$path, $tmp, PREG_SET_ORDER);
					$arguments = array();
					for ($i=0;$i<count($tmp);$i++){
						if ($tmp[$i][0] !=''){
							array_push($arguments, $tmp[$i][0]);
						}
					}
					$path_cmd =  $svg_class->svgPolyline($arguments);
					$critere_style = $attribs;
					unset($critere_style['points']);
					$path_style = $svg_class->svgDefineStyle($critere_style);
					break;

				case 'polygon':
					$path = $attribs['points'];
					preg_match_all('/([\-]*[0-9\.]+)/',$path, $tmp);
					$arguments = array();
					for ($i=0;$i<count($tmp[0]);$i++){
						if ($tmp[0][$i] !=''){
							array_push($arguments, $tmp[0][$i]);
						}
					}
					$path_cmd =  $svg_class->svgPolygon($arguments);
					//	definition du style de la forme:
					$critere_style = $attribs;
					unset($critere_style['points']);
					$path_style = $svg_class->svgDefineStyle($critere_style);
					break;

				case 'a':
						if (isset($attribs['xlink:href'])) {
							unset($attribs['xlink:href']);	// this should be a hyperlink
								// not handled like a xlink:href in other elements
						}		// then continue like a <g>
				case 'g':
						$array_style = $svg_class->svgDefineStyle($attribs);
						if ($array_style['transformations']) {
							$svg_class->svgWriteString(' q '.$array_style['transformations']);
						}
						array_push($svg_class->svg_style,$array_style);

						$svg_class->svgDefineTxtStyle($attribs);	// mPDF 4.4.003

					break;

				case 'text':
						$array_style = $svg_class->svgDefineStyle($attribs);
						if ($array_style['transformations']) {
							$svg_class->svgWriteString(' q '.$array_style['transformations']);
						}
						array_push($svg_class->svg_style,$array_style);

						$svg_class->txt_data = array();
						$svg_class->txt_data[0] = $attribs['x'];
						$svg_class->txt_data[1] = $attribs['y'];
						$critere_style = $attribs;
						unset($critere_style['x'], $critere_style['y']);
						$svg_class->svgDefineTxtStyle($critere_style);
					break;
				}

				//
				//insertion des path et du style dans le flux de donné general.
				if (isset($path_cmd) && $path_cmd) {	// mPDF 4.4.003
					// mPDF 5.0
					list($prestyle,$poststyle) = $svg_class->svgStyle($path_style, $attribs, strtolower($name));
					if ($path_style['transformations']) {	// transformation on an element
						$svg_class->svgWriteString(" q ".$path_style['transformations']. " $prestyle $path_cmd $poststyle" . " Q\n");
					}
					else {
						$svg_class->svgWriteString("$prestyle $path_cmd $poststyle\n");
					}
				}
			}

			function characterData($parser, $data)
			{
				global $svg_class;
				if ($svg_class->inDefs) { return; }		// mPDF 5.7.2
				if(isset($svg_class->txt_data[2])) {
					$svg_class->txt_data[2] .= $data;
				}
				else {
					$svg_class->txt_data[2] = $data;
				}
			}


			function xml_svg2pdf_end($parser, $name){
				global $svg_class;
		 		// Don't output stuff inside <defs>
				// mPDF 5.7.2
				if ($name == 'defs') {
					$svg_class->inDefs = false;
					return;
				}
				if ($svg_class->inDefs) { return; }
				switch($name){

					case "g":
					case "a":
						$tmp = count($svg_class->svg_style)-1;
						$current_style = $svg_class->svg_style[$tmp];
						if ($current_style['transformations']) {
							$svg_class->svgWriteString(" Q\n");
						}
						array_pop($svg_class->svg_style);

						array_pop($svg_class->txt_style);	// mPDF 4.4.003

						break;
					case 'radialgradient':
					case 'lineargradient':
						$last_gradid = '';
						break;
					case "text":
						$path_cmd = $svg_class->svgText();
						// echo 'path >> '.$path_cmd."<br><br>";
						// echo "style >> ".$get_style[1]."<br><br>";
						$svg_class->svgWriteString($path_cmd);
						// mPDF 4.4.003
						$tmp = count($svg_class->svg_style)-1;
						$current_style = $svg_class->svg_style[$tmp];
						if ($current_style['transformations']) {
							$svg_class->svgWriteString(" Q\n");
						}
						array_pop($svg_class->svg_style);

						break;
				}
		 		// mPDF 5.0.039	- Don't output stuff inside <defs>
				if ($name == 'defs') {
					$svg_class->inDefs = false;
				}

			}

		}

		$svg2pdf_xml='';
		global $svg_class;
		$svg_class = $this;
		// mPDF 5.0.039	- Don't output stuff inside <defs>
		$svg_class->inDefs = false;
 		$svg2pdf_xml_parser = xml_parser_create("utf-8");
		xml_parser_set_option($svg2pdf_xml_parser, XML_OPTION_CASE_FOLDING, false);
		xml_set_element_handler($svg2pdf_xml_parser, "xml_svg2pdf_start", "xml_svg2pdf_end");
		xml_set_character_data_handler($svg2pdf_xml_parser, "characterData");
		xml_parse($svg2pdf_xml_parser, $data);
		// mPDF 4.4.003
		if ($this->svg_error) { return false; }
		else {
			return array('x'=>$this->svg_info['x']*$this->kp,'y'=>-$this->svg_info['y']*$this->kp,'w'=>$this->svg_info['w']*$this->kp,'h'=>-$this->svg_info['h']*$this->kp,'data'=>$svg_class->svg_string);
		}

	}

}

// END OF CLASS


// mPDF 5.0.040
function calc_bezier_bbox($start, $c) {
	$P0 = array($start[0],$start[1]);
	$P1 = array($c[0],$c[1]);
	$P2 = array($c[2],$c[3]);
	$P3 = array($c[4],$c[5]);
	$bounds = array();
	$bounds[0][] = $P0[0];
	$bounds[1][] = $P0[1];
	$bounds[0][] = $P3[0];
	$bounds[1][] = $P3[1];
	for ($i=0;$i<=1;$i++) {
            $b = 6 * $P0[$i] - 12 * $P1[$i] + 6 * $P2[$i];
            $a = -3 * $P0[$i] + 9 * $P1[$i] - 9 * $P2[$i] + 3 * $P3[$i];
            $c = 3 * $P1[$i] - 3 * $P0[$i];
		if ($a == 0) {
			if ($b == 0) { continue; }
			$t = -$c / $b;
			if ($t>0 && $t<1) {
				$bounds[$i][] = (pow((1-$t),3) * $P0[$i] + 3 * pow((1-$t),2) * $t * $P1[$i] + 3 * (1-$t) * pow($t,2) * $P2[$i] + pow($t,3) * $P3[$i]);
			}
			continue;
		}
		$b2ac = pow($b, 2) - 4 * $c * $a;
		if ($b2ac < 0) { continue; }
		$t1 = (-$b + sqrt($b2ac))/(2 * $a);
		if ($t1>0 && $t1<1) { 
			$bounds[$i][] = (pow((1-$t1),3) * $P0[$i] + 3 * pow((1-$t1),2) * $t1 * $P1[$i] + 3 * (1-$t1) * pow($t1,2) * $P2[$i] + pow($t1,3) * $P3[$i]);
		}
		$t2 = (-$b - sqrt($b2ac))/(2 * $a);
		if ($t2>0 && $t2<1) { 
			$bounds[$i][] = (pow((1-$t2),3) * $P0[$i] + 3 * pow((1-$t2),2) * $t2 * $P1[$i] + 3 * (1-$t2) * pow($t2,2) * $P2[$i] + pow($t2,3) * $P3[$i]);
		}
	}
	$x = min($bounds[0]);
	$x2 = max($bounds[0]);
	$y = min($bounds[1]);
	$y2 = max($bounds[1]);
	return array($x, $y, $x2, $y2);
}

// mPDF 5.0.040
function _testIntersectCircle($cx, $cy, $cr) {
	// Tests whether a circle fully encloses a rectangle 0,0,1,1
	// to see if any further radial gradients need adding (SVG)
	// If centre of circle is inside 0,0,1,1 square
	if ($cx >= 0 && $cx <= 1 && $cy >= 0 && $cy <= 1) {
		$maxd = 1.5;
	}
	// distance to four corners
	else {
		$d1 = sqrt(pow(($cy-0),2) + pow(($cx-0),2));
		$d2 = sqrt(pow(($cy-1),2) + pow(($cx-0),2));
		$d3 = sqrt(pow(($cy-0),2) + pow(($cx-1),2));
		$d4 = sqrt(pow(($cy-1),2) + pow(($cx-1),2));
		$maxd = max($d1,$d2,$d3,$d4);
	}
	if ($cr < $maxd) { return true; }
	else { return false; }
}

// mPDF 5.0.040
function _testIntersect($x1, $y1, $x2, $y2, $x3, $y3, $x4, $y4) {
	// Tests whether line (x1, y1) and (x2, y2) [a gradient axis (perpendicular)]
	// intersects with a specific line segment (x3, y3) and (x4, y4)
	$a1 = $y2-$y1;
	$b1 = $x1-$x2;
	$c1 = $a1*$x1+$b1*$y1;
	$a2 = $y4-$y3;
	$b2 = $x3-$x4;
	$c2 = $a2*$x3+$b2*$y3;
	$det = $a1*$b2 - $a2*$b1;
	if($det == 0){	//Lines are parallel
		return false;
	}
	else{
		$x = ($b2*$c1 - $b1*$c2)/$det;
		$y = ($a1*$c2 - $a2*$c1)/$det;
		if ($x >= $x3 && $x <= $x4 && $y >= $y3 && $y <= $y4) { return true; }
	}
	return false;
}



?>