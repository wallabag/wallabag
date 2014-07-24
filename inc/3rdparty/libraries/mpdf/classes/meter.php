<?php

class meter {


function __construct() {

}

function makeSVG($tag, $type, $value, $max, $min, $optimum, $low, $high) {
  $svg = '';
  if ($tag == 'meter') {

    if ($type=='2') {
	/////////////////////////////////////////////////////////////////////////////////////
	///////// CUSTOM <meter type="2">
	/////////////////////////////////////////////////////////////////////////////////////
	$h = 10;
	$w = 160;
	$border_radius = 0.143;		// Factor of Height

	$svg = '<?xml version="1.0" encoding="UTF-8"?>
	<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
	<svg width="'.$w.'px" height="'.$h.'px" viewBox="0 0 '.$w.' '.$h.'" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" ><g>


	<defs>
	<linearGradient id="GrGRAY" x1="0" y1="0" x2="0" y2="1" gradientUnits="boundingBox">
	<stop offset="0%" stop-color="rgb(222, 222, 222)" />
	<stop offset="20%" stop-color="rgb(232, 232, 232)" />
	<stop offset="25%" stop-color="rgb(232, 232, 232)" />
	<stop offset="100%" stop-color="rgb(182, 182, 182)" />
	</linearGradient>

	</defs>
';
	$svg .= '<rect x="0" y="0" width="'.$w.'" height="'.$h.'" fill="#f4f4f4" stroke="none" />';

	// LOW to HIGH region
	//if ($low && $high && ($low != $min || $high != $max)) {
	if ($low && $high) {
	  $barx = (($low-$min) / ($max-$min) ) * $w;
	  $barw = (($high-$low) / ($max-$min) ) * $w;
	  $svg .= '<rect x="'.$barx.'" y="0" width="'.$barw.'" height="'.$h.'" fill="url(#GrGRAY)" stroke="#888888" stroke-width="0.5px" />';
	}

	// OPTIMUM Marker (? AVERAGE)
	if ($optimum) {
	  $barx = (($optimum-$min) / ($max-$min) ) * $w;
	  $barw = $h/2;
	  $barcol = '#888888';
	  $svg .= '<rect x="'.$barx.'" y="0" rx="'.($h*$border_radius).'px" ry="'.($h*$border_radius).'px" width="'.$barw.'" height="'.$h.'" fill="'.$barcol.'" stroke="none" />';
	}

	// VALUE Marker
	if ($value) {
	  if ($min != $low && $value < $low) { $col = 'orange'; }
	  else if ($max != $high && $value > $high) { $col = 'orange'; }
	  else { $col = '#008800'; }
	  $cx = (($value-$min) / ($max-$min) ) * $w;
	  $cy = $h/2;
	  $rx = $h/3.5;
	  $ry = $h/2.2;
	  $svg .= '<ellipse fill="'.$col.'" stroke="#000000" stroke-width="0.5px" cx="'.$cx.'" cy="'.$cy.'" rx="'.$rx.'" ry="'.$ry.'"/>';
	}

	// BoRDER
	$svg .= '<rect x="0" y="0" width="'.$w.'" height="'.$h.'" fill="none" stroke="#888888" stroke-width="0.5px" />';

	$svg .= '</g></svg>';
    }
    else {
	/////////////////////////////////////////////////////////////////////////////////////
	///////// DEFAULT <meter>
	/////////////////////////////////////////////////////////////////////////////////////
	$h = 10;
	$w = 50;
	$border_radius = 0.143;		// Factor of Height

	$svg = '<?xml version="1.0" encoding="UTF-8"?>
	<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
	<svg width="'.$w.'px" height="'.$h.'px" viewBox="0 0 '.$w.' '.$h.'" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" ><g>

	<defs>
	<linearGradient id="GrGRAY" x1="0" y1="0" x2="0" y2="1" gradientUnits="boundingBox">
	<stop offset="0%" stop-color="rgb(222, 222, 222)" />
	<stop offset="20%" stop-color="rgb(232, 232, 232)" />
	<stop offset="25%" stop-color="rgb(232, 232, 232)" />
	<stop offset="100%" stop-color="rgb(182, 182, 182)" />
	</linearGradient>

	<linearGradient id="GrRED" x1="0" y1="0" x2="0" y2="1" gradientUnits="boundingBox">
	<stop offset="0%" stop-color="rgb(255, 162, 162)" />
	<stop offset="20%" stop-color="rgb(255, 218, 218)" />
	<stop offset="25%" stop-color="rgb(255, 218, 218)" />
	<stop offset="100%" stop-color="rgb(255, 0, 0)" />
	</linearGradient>

	<linearGradient id="GrGREEN" x1="0" y1="0" x2="0" y2="1" gradientUnits="boundingBox">
	<stop offset="0%" stop-color="rgb(102, 230, 102)" />
	<stop offset="20%" stop-color="rgb(218, 255, 218)" />
	<stop offset="25%" stop-color="rgb(218, 255, 218)" />
	<stop offset="100%" stop-color="rgb(0, 148, 0)" />
	</linearGradient>

	<linearGradient id="GrBLUE" x1="0" y1="0" x2="0" y2="1" gradientUnits="boundingBox">
	<stop offset="0%" stop-color="rgb(102, 102, 230)" />
	<stop offset="20%" stop-color="rgb(238, 238, 238)" />
	<stop offset="25%" stop-color="rgb(238, 238, 238)" />
	<stop offset="100%" stop-color="rgb(0, 0, 128)" />
	</linearGradient>

	<linearGradient id="GrORANGE" x1="0" y1="0" x2="0" y2="1" gradientUnits="boundingBox">
	<stop offset="0%" stop-color="rgb(255, 186, 0)" />
	<stop offset="20%" stop-color="rgb(255, 238, 168)" />
	<stop offset="25%" stop-color="rgb(255, 238, 168)" />
	<stop offset="100%" stop-color="rgb(255, 155, 0)" />
	</linearGradient>
	</defs>

	<rect x="0" y="0" rx="'.($h*$border_radius).'px" ry="'.($h*$border_radius).'px" width="'.$w.'" height="'.$h.'" fill="url(#GrGRAY)" stroke="none" />
';

	if ($value) {
	  $barw = (($value-$min) / ($max-$min) ) * $w;
	  if ($optimum < $low) {
		if ($value < $low) { $barcol = 'url(#GrGREEN)'; }
		else if ($value > $high) { $barcol = 'url(#GrRED)'; }
		else { $barcol = 'url(#GrORANGE)'; }
	  }
	  else if ($optimum > $high) {
		if ($value < $low) { $barcol = 'url(#GrRED)'; }
		else if ($value > $high) { $barcol = 'url(#GrGREEN)'; }
		else { $barcol = 'url(#GrORANGE)'; }
	  }
	  else {
		if ($value < $low) { $barcol = 'url(#GrORANGE)'; }
		else if ($value > $high) { $barcol = 'url(#GrORANGE)'; }
		else { $barcol = 'url(#GrGREEN)'; }
	  }
	  $svg .= '<rect x="0" y="0" rx="'.($h*$border_radius).'px" ry="'.($h*$border_radius).'px" width="'.$barw.'" height="'.$h.'" fill="'.$barcol.'" stroke="none" />';
	}


	// Borders
	//$svg .= '<rect x="0" y="0" rx="'.($h*$border_radius).'px" ry="'.($h*$border_radius).'px" width="'.$w.'" height="'.$h.'" fill="none" stroke="#888888" stroke-width="0.5px" />';
	if ($value) {
	//  $svg .= '<rect x="0" y="0" rx="'.($h*$border_radius).'px" ry="'.($h*$border_radius).'px" width="'.$barw.'" height="'.$h.'" fill="none" stroke="#888888" stroke-width="0.5px" />';
	}


	$svg .= '</g></svg>';
    }
  }
  else {	// $tag == 'progress'

    if ($type=='2') {
	/////////////////////////////////////////////////////////////////////////////////////
	///////// CUSTOM <progress type="2">
	/////////////////////////////////////////////////////////////////////////////////////
    }
    else {
	/////////////////////////////////////////////////////////////////////////////////////
	///////// DEFAULT <progress>
	/////////////////////////////////////////////////////////////////////////////////////
	$h = 10;
	$w = 100;
	$border_radius = 0.143;		// Factor of Height

	if ($value or $value==='0') {
		$fill = 'url(#GrGRAY)';
	}
	else {
		$fill = '#f8f8f8';
	}

	$svg = '<svg width="'.$w.'px" height="'.$h.'px" viewBox="0 0 '.$w.' '.$h.'"><g>

	<defs>
	<linearGradient id="GrGRAY" x1="0" y1="0" x2="0" y2="1" gradientUnits="boundingBox">
	<stop offset="0%" stop-color="rgb(222, 222, 222)" />
	<stop offset="20%" stop-color="rgb(232, 232, 232)" />
	<stop offset="25%" stop-color="rgb(232, 232, 232)" />
	<stop offset="100%" stop-color="rgb(182, 182, 182)" />
	</linearGradient>

	<linearGradient id="GrGREEN" x1="0" y1="0" x2="0" y2="1" gradientUnits="boundingBox">
	<stop offset="0%" stop-color="rgb(102, 230, 102)" />
	<stop offset="20%" stop-color="rgb(218, 255, 218)" />
	<stop offset="25%" stop-color="rgb(218, 255, 218)" />
	<stop offset="100%" stop-color="rgb(0, 148, 0)" />
	</linearGradient>

	</defs>

	<rect x="0" y="0" rx="'.($h*$border_radius).'px" ry="'.($h*$border_radius).'px" width="'.$w.'" height="'.$h.'" fill="'.$fill.'" stroke="none" />
';

	if ($value) {
	  $barw = (($value-$min) / ($max-$min) ) * $w;
	  $barcol = 'url(#GrGREEN)';
	  $svg .= '<rect x="0" y="0" rx="'.($h*$border_radius).'px" ry="'.($h*$border_radius).'px" width="'.$barw.'" height="'.$h.'" fill="'.$barcol.'" stroke="none" />';
	}


	// Borders
	$svg .= '<rect x="0" y="0" rx="'.($h*$border_radius).'px" ry="'.($h*$border_radius).'px" width="'.$w.'" height="'.$h.'" fill="none" stroke="#888888" stroke-width="0.5px" />';
	if ($value) {
	//  $svg .= '<rect x="0" y="0" rx="'.($h*$border_radius).'px" ry="'.($h*$border_radius).'px" width="'.$barw.'" height="'.$h.'" fill="none" stroke="#888888" stroke-width="0.5px" />';
	}


	$svg .= '</g></svg>';

    }
  }

  return $svg;
}


}	// end of class

?>