<?php


// mPDF 4.5.009
define("FF_USERFONT", 15);	// See jpgraph_ttf.inc.php for font IDs
global $JpgUseSVGFormat;
$JpgUseSVGFormat = true;

//======================================================================================================
// DELETE OLD GRAPH FILES FIRST - Housekeeping
// First clear any files in directory that are >1 hrs old
	$interval = 3600;
	if ($handle = opendir(_MPDF_PATH.'graph_cache')) {
	   while (false !== ($file = readdir($handle))) { 
		if (((filemtime(_MPDF_PATH.'graph_cache/'.$file)+$interval) < time()) && ($file != "..") && ($file != ".")) { 
			@unlink(_MPDF_PATH.'graph_cache/'.$file); 	// mPDF 4.0
		}
	   }
	   closedir($handle); 
	}
//==============================================================================================================
// LOAD GRAPHS

	include_once(_JPGRAPH_PATH.'jpgraph.php'); 
	include_once(_JPGRAPH_PATH.'jpgraph_line.php' ); 
	include_once(_JPGRAPH_PATH.'jpgraph_log.php' ); 
	include_once(_JPGRAPH_PATH.'jpgraph_scatter.php' ); 
	include_once(_JPGRAPH_PATH.'jpgraph_regstat.php' ); 
	include_once(_JPGRAPH_PATH.'jpgraph_pie.php'); 
	include_once(_JPGRAPH_PATH.'jpgraph_pie3d.php'); 
	include_once(_JPGRAPH_PATH.'jpgraph_bar.php'); 
	include_once(_JPGRAPH_PATH.'jpgraph_radar.php'); 


//======================================================================================================
//*****************************************************************************************************
//*****************************************************************************************************
//*****************************************************************************************************
//*****************************************************************************************************
//*****************************************************************************************************
//*****************************************************************************************************
//======================================================================================================
//======================================================================================================

//======================================================================================================
//======================================================================================================

//======================================================================================================
function print_graph($g,$pgwidth) {
	$splines = false;
	$bandw = false;
	$percent = false;
	$show_percent = false;
	$stacked = false;
	$h = false;
	$show_values = false;
	$hide_grid = false;
	$hide_y_axis = false;

	if (isset($g['attr']['TYPE']) && $g['attr']['TYPE']) { $type = strtolower($g['attr']['TYPE']); }
	if (!in_array($type,array('bar','horiz_bar','line','radar','pie','pie3d','xy','scatter'))) { $type = 'bar'; } // Default=bar

	if (isset($g['attr']['STACKED']) && $g['attr']['STACKED']) { $stacked = true; }	// stacked for bar or horiz_bar
	if (isset($g['attr']['SPLINES']) && $g['attr']['SPLINES'] && $type=='xy') { $splines = true; }	// splines for XY line graphs
	if (isset($g['attr']['BANDW']) && $g['attr']['BANDW']) { $bandw = true; }	// black and white
	if (isset($g['attr']['LEGEND-OVERLAP']) && $g['attr']['LEGEND-OVERLAP']) { $overlap = true; } // avoid overlap of Legends over graph (line, bar, horiz_bar only)
	if (isset($g['attr']['PERCENT']) && $g['attr']['PERCENT'] && $type != 'xy' && $type != 'scatter') { $percent = true; }	// Show data series as percent of total in series
	if (isset($g['attr']['SHOW-VALUES']) && $g['attr']['SHOW-VALUES']) { $show_values = true; }	// Show the individual data values
	if (isset($g['attr']['HIDE-GRID']) && $g['attr']['HIDE-GRID']) { $hide_grid = true; }	// Hide the y-axis gridlines
	if (isset($g['attr']['HIDE-Y-AXIS']) && $g['attr']['HIDE-Y-AXIS']) { $hide_y_axis = true; }	// Hide the y-axis


	// Antialias: If true - better quality curves, but graph line will only be 1px even in PDF 300dpi 
	// default=true for most except line and radar
	if (isset($g['attr']['ANTIALIAS']) && ($g['attr']['ANTIALIAS']=='' || $g['attr']['ANTIALIAS']==0)) { $antialias = false; }
	else if (isset($g['attr']['ANTIALIAS']) && $g['attr']['ANTIALIAS'] > 0) { $antialias = true; }
	else if ($type=='line' || $type=='radar') { $antialias = false; }
	else { $antialias = true; }

	if ($g['attr']['DPI']) { $dpi = intval($g['attr']['DPI']); }
	if (!$dpi || $dpi < 50 || $dpi > 2400) { $dpi = 150; } 	// Default dpi 150
	$k = (0.2645/25.4 * $dpi); 

	// mPDF 4.5.009
	global $JpgUseSVGFormat;
	if (isset($JpgUseSVGFormat) && $JpgUseSVGFormat) {
		$img_type = 'svg';
		$k = 1;	// Overrides as Vector scale does not need DPI
	}
	else {
		$img_type = 'png';
	}

	if (isset($g['attr']['TITLE']) && $g['attr']['TITLE']) { $title = $g['attr']['TITLE']; }

	if (isset($g['attr']['LABEL-X']) && $g['attr']['LABEL-X']) { $xlabel = $g['attr']['LABEL-X']; }		// NOT IMPLEMENTED??????
	if (isset($g['attr']['LABEL-Y']) && $g['attr']['LABEL-Y']) { $ylabel = $g['attr']['LABEL-Y']; }

	if (isset($g['attr']['AXIS-X']) && $g['attr']['AXIS-X']) { $xaxis = strtolower($g['attr']['AXIS-X']); }
	if (!in_array($xaxis,array('text','lin','linear','log'))) { $xaxis = 'text'; }	// Default=text
	if ($xaxis == 'linear') { $xaxis = 'lin'; }

	if (isset($g['attr']['AXIS-Y']) && $g['attr']['AXIS-Y']) { $yaxis = strtolower($g['attr']['AXIS-Y']); }
	if (!in_array($yaxis,array('lin','linear','log','percent'))) { $yaxis = 'lin'; }			// Default=lin
	if ($yaxis == 'percent') { $show_percent = true; $yaxis = 'lin'; }	// Show percent sign on scales
	if ($yaxis == 'linear') { $yaxis = 'lin'; }

	if ($splines) { $xaxis = 'lin'; }
	$axes = $xaxis.$yaxis;	// e.g.textlin, textlog, loglog, loglin, linlog (XY)

	// mPDF 4.0
	if (isset($g['attr']['cWIDTH']) && $g['attr']['cWIDTH']) { $w=($g['attr']['cWIDTH'] / 0.2645); }	// pixels
	if (isset($g['attr']['cHEIGHT']) && $g['attr']['cHEIGHT']) { $h=($g['attr']['cHEIGHT'] / 0.2645); }


	if (isset($g['attr']['SERIES']) && strtolower($g['attr']['SERIES']) == 'rows') { $dataseries = 'rows'; }
	else { $dataseries = 'cols'; }

	// Defaults - define data
	$rowbegin = 2;
	$colbegin = 2;
	if($type=='scatter' || $type=='xy') { 
		if ($dataseries == 'rows') { $rowbegin = 1; }
		else { $colbegin = 1; }
	}
	$rowend = 0;
	$colend = 0;

	if (isset($g['attr']['DATA-ROW-BEGIN']) && ($g['attr']['DATA-ROW-BEGIN'] === '0' || $g['attr']['DATA-ROW-BEGIN'] > 0)) { $rowbegin = $g['attr']['DATA-ROW-BEGIN']; }

	if (isset($g['attr']['DATA-COL-BEGIN']) && ($g['attr']['DATA-COL-BEGIN'] === '0' || $g['attr']['DATA-COL-BEGIN'] > 0)) { $colbegin = $g['attr']['DATA-COL-BEGIN']; }

	if (isset($g['attr']['DATA-ROW-END']) && ($g['attr']['DATA-ROW-END'] === '0' || $g['attr']['DATA-ROW-END'] <> 0)) { $rowend = $g['attr']['DATA-ROW-END']; }
	if (isset($g['attr']['DATA-COL-END']) && ($g['attr']['DATA-COL-END'] === '0' || $g['attr']['DATA-COL-END'] <> 0)) { $colend = $g['attr']['DATA-COL-END']; }

	$nr = count($g['data']);
	$nc = 0;
	foreach($g['data'] AS $r) {
		$cc=0;
		foreach($r AS $c) { $cc++; }
		$nc = max($nc,$cc);
	}
	if ($colend == 0) { $colend = $nc; }
	else if ($colend < 0) { $colend = $nc+$colend; }

	if ($rowend == 0) { $rowend = $nr; }
	else if ($rowend < 0) { $rowend = $nr+$rowend; }

	if ($colend < $colbegin) { $colend = $colbegin; }
	if ($rowend < $rowbegin) { $rowend = $rowbegin; }

//	if ($type == 'xy' || $type=='scatter') { $colstart=0; }

	// Get Data + Totals
	$data = array();
	$totals = array();
	for ($r=($rowbegin-1);$r<$rowend;$r++) {
		for ($c=($colbegin-1);$c<$colend;$c++) {
		    if (isset($g['data'][$r][$c])) { $g['data'][$r][$c] = floatval($g['data'][$r][$c] ); }
		    else { $g['data'][$r][$c] = 0; }
		    if ($dataseries=='rows') { 
			$data[($r+1-$rowbegin)][($c+1-$colbegin)] = $g['data'][$r][$c] ; 
			$totals[($r+1-$rowbegin)] += $g['data'][$r][$c] ; 
		    }
		    else { 
			$data[($c+1-$colbegin)][($r+1-$rowbegin)] = $g['data'][$r][$c] ; 
			if (isset($totals[($c+1-$colbegin)])) { $totals[($c+1-$colbegin)] += $g['data'][$r][$c] ; }
			else { $totals[($c+1-$colbegin)] = $g['data'][$r][$c] ; }
		    }
		}
	}
	// PERCENT
	if ($percent && $type != 'pie' && $type != 'pie3d') {
		for ($r=0;$r<count($data);$r++) {
			for ($c=0;$c<count($data[$r]);$c++) {
		    		$data[$r][$c] = $data[$r][$c]/$totals[$r]  * 100;
			}
		}
	}
	// Get Legends and labels
	$legends = array();
	$labels = array();
	$longestlegend = 0;
	$longestlabel = 0;
	if ($dataseries=='cols') { 
		if ($colbegin>1) {
			for ($r=($rowbegin-1);$r<$rowend;$r++) { 
				$legends[($r+1-$rowbegin)] = $g['data'][$r][0] ;
				$longestlegend = max($longestlegend, strlen( $g['data'][$r][0] ));
			}
		}
		if ($rowbegin>1) {
			for ($c=($colbegin-1);$c<$colend;$c++) { 
				$labels[($c+1-$colbegin)] = $g['data'][0][$c] ; 
				$longestlabel = max($longestlabel , strlen( $g['data'][0][$c] ));
			}
		}
	}
	else if ($dataseries=='rows') { 
		if ($colbegin>1) {
			for ($r=($rowbegin-1);$r<$rowend;$r++) { 
				$labels[($r+1-$rowbegin)] = $g['data'][$r][0] ; 
				$longestlabel = max($longestlabel , strlen( $g['data'][$r][0] ));
			}
		}
		if ($rowbegin>1) {
			for ($c=($colbegin-1);$c<$colend;$c++) { 
				$legends[($c+1-$colbegin)] = $g['data'][0][$c] ; 
				$longestlegend = max($longestlegend, strlen( $g['data'][0][$c] ));
			}
		}
	}
   // Default sizes
   $defsize = array();
   $defsize['pie'] = array('w' => 600, 'h' => 300);
   $defsize['pie3d'] = array('w' => 600, 'h' => 300);
   $defsize['radar'] = array('w' => 600, 'h' => 300);
   $defsize['line'] = array('w' => 600, 'h' => 400);
   $defsize['xy'] = array('w' => 600, 'h' => 400);
   $defsize['scatter'] = array('w' => 600, 'h' => 400);
   $defsize['bar'] = array('w' => 600, 'h' => 400);
   $defsize['horiz_bar'] = array('w' => 600, 'h' => 500);


   // Use default ratios
   if ($w && !$h) { $h = $w*$defsize[$type]['h']/$defsize[$type]['w']; }
   if ($h && !$w) { $w = $h*$defsize[$type]['w']/$defsize[$type]['h']; }
   if (!$h && !$w) { $w = $defsize[$type]['w']; $h = $defsize[$type]['h']; }


   if (count($data)>0 && $type) {
	$figure_file = "graph_cache/".rand(11111,999999999).".".$img_type;
	if ($bandw) { $colours = array('snow1','black','snow4','snow3','snow2','cadetblue4','cadetblue3','cadetblue1','bisque4','bisque2','beige'); }
	else { $colours = array('cyan','darkorchid4','cadetblue3','khaki1','darkolivegreen2','cadetblue4','coral','cyan4','rosybrown3','wheat1'); }
	$fills = array('navy','orange','red','yellow','purple','navy','orange','red','yellow','purple');
	$patterns = array(PATTERN_DIAG1,PATTERN_CROSS1,PATTERN_STRIPE1,PATTERN_DIAG3,PATTERN_CROSS2,PATTERN_DIAG2,PATTERN_DIAG4,PATTERN_CROSS3, PATTERN_CROSS4,PATTERN_STRIPE1);
	$markers = array(MARK_DIAMOND, MARK_SQUARE, MARK_CIRCLE, MARK_UTRIANGLE, MARK_DTRIANGLE, MARK_FILLEDCIRCLE, MARK_CROSS, MARK_STAR, MARK_X);

	// LEGENDS
	if ($type == 'pie' || $type == 'pie3d') { 
		$graph = new PieGraph (($w*$k),($h*$k));  
	}
	else if ($type == 'radar') { 
		$graph = new RadarGraph(($w*$k),($h*$k));
	}
	else {
		$graph = new Graph(($w*$k),($h*$k));
	}

// mPDF 4.5.009
//	$graph->img->SetImgFormat($img_type) ;
//	if (strtoupper($img_type)=='JPEG') { $graph->img->SetQuality(90); }
	if ($antialias) { $graph->img->SetAntiAliasing(); }
	$graph->SetShadow(true, 2*$k); 
	$graph->SetMarginColor("white");
	// TITLE
	$graph->title->Set($title); 
	$graph->title->SetMargin(10*$k);	
	$graph->title->SetFont(FF_USERFONT,FS_BOLD,11*$k);
	$graph->title->SetColor("black");
	$graph->legend->SetLineSpacing(3*$k); 
	$graph->legend->SetMarkAbsSize(6*$k); 
	$graph->legend->SetFont(FF_USERFONT,FS_NORMAL,8*$k);

	// Set GRAPH IMAGE MARGINS
	if ($type == 'pie' || $type == 'pie3d') { 
		$psize = 0.3;
		$pposxabs = ($w/2);
		$pposy = 0.55;
		if ($longestlegend) {	// if legend showing
			$pposxabs -= ((($longestlegend * 5) + 20) / 2);
		}
		$pposx = ($pposxabs / $w);
		$graph->legend->Pos(0.02,0.5,'right','center'); 
	}
	else if ($type == 'radar') { 
		$psize = 0.5;
		$pposxabs = ($w/2);
		$pposy = 0.55;
		if ($longestlabel) {	// if legend showing
			$pposxabs -= ((($longestlabel * 5) + 20) / 2);
		}
		$pposx = ($pposxabs / $w);
		$graph->legend->Pos(0.02,0.5,'right','center'); 
	}
	else if ($type == 'xy' || $type == 'scatter') {
		$pml = 50;
		$pmr = 20;
		$pmt = 60;
		$pmb = 50;
		$xaxislblmargin = $pmb - 30;
		$yaxislblmargin = $pml - 15;
		$graph->legend->Pos(0.02,0.1,'right','top'); 
	}
	else if ($type == 'line' || $type == 'bar') {
		$pml = 50;
		$pmr = 20;
		$pmt = 60;
		$pmb = 50;
		$xlangle = 0;
		$ll = ($longestlegend * 5);	// 45 degrees 8pt fontsize
		if ($ll > 5 || ($ll>3 && count($data)>10)) {
			$pmb = max($pmb, $ll + 30);
			$xlangle = 50;
		}
		$xaxislblmargin = $pmb - 30;
		$yaxislblmargin = $pml - 15;
		if ($longestlabel && !$overlap) {	// if legend showing
			$pmr = ((($longestlabel * 5) + 40));
		}
		$graph->legend->Pos(0.02,0.1,'right','top'); 
	}
	else if ($type == 'horiz_bar') {
		$pml = 50;
		$pmr = 20;
		$pmt = 50;
		$pmb = 45;
		$ll = ($longestlegend * 6.5);	// 8pt fontsize
		$pml = max($pml, $ll + 20);
		$xaxislblmargin = $pml - 20;
		$yaxislblmargin = $pmb - 15;
		if ($longestlabel && !$overlap) {	// if legend showing
			$pmr = ((($longestlabel * 5) + 40));
		}
		$graph->legend->Pos(0.02,0.1,'right','top'); 
	}


	// DRAW THE GRAPHS
	if ($type == 'pie') { 
			$p1 = new PiePlot($data[0]); 
			$p1->SetSliceColors($colours); 

			if ($show_values) {
				$p1->value->SetFont(FF_USERFONT,FS_NORMAL,8*$k);
				if ($percent) { $p1->SetLabelType(PIE_VALUE_PERADJ); }   //PIE_VAL_PER = default
				else { $p1->SetLabelType(PIE_VALUE_ABS); }
				if ($percent || $show_percent) { $p1->value->SetFormat("%d%%"); }
				else { $p1->value->SetFormat("%s"); }
				// Enable and set policy for guide-lines. Make labels line up vertically
				$p1->SetGuideLines(true);
				$p1->SetGuideLinesAdjust(1.5);
			}
			else { $p1->value->Show(false); }
			$p1->SetLegends($legends);
			$p1->SetSize($psize);
			$p1->SetCenter($pposx, $pposy);
			if ($labels[0]) { 
				$graph->subtitle->Set($labels[0]); 
				$graph->subtitle->SetMargin(10*$k);	
				$graph->subtitle->SetFont(FF_USERFONT,FS_BOLD,11*$k);
				$graph->subtitle->SetColor("black");
			}
			$graph->Add($p1);
	}
	else if ($type == 'pie3d') { 
			$p1 = new PiePlot3d($data[0]); 
			$p1->SetSliceColors($colours); 
			if ($show_values) {
				$p1->value->SetFont(FF_USERFONT,FS_NORMAL,8*$k);
				if ($percent) { $p1->SetLabelType(PIE_VALUE_PERADJ); }   //PIE_VAL_PER = default
				else { $p1->SetLabelType(PIE_VALUE_ABS); }
				if ($percent || $show_percent) { $p1->value->SetFormat("%d%%"); }
				else { $p1->value->SetFormat("%s"); }
			}
			else { $p1->value->Show(false); }
			$p1->SetLegends($legends);
			$p1->SetEdge();
			$p1->SetSize($psize);
			$p1->SetCenter($pposx, $pposy);

			if ($labels[0]) { 
				$graph->subtitle->Set($labels[0]); 
				$graph->subtitle->SetMargin(10*$k);	
				$graph->subtitle->SetFont(FF_USERFONT,FS_BOLD,11*$k);
				$graph->subtitle->SetColor("black");
			}

			$graph->Add( $p1); 
	}
	// RADAR
	else if ($type == 'radar') { 
			$graph->SetSize($psize);
			$graph->SetPos($pposx, $pposy);

			$graph->SetTitles( $legends);	// labels each axis

			$graph->axis->title->SetFont(FF_USERFONT,FS_NORMAL,8*$k);
			$graph->axis->title->SetMargin(5*$k);
			$graph->axis->SetWeight(1*$k);
			$graph->axis->HideLabels();
			$graph->axis->SetFont(FF_USERFONT,FS_NORMAL,6*$k);
			$graph->HideTickMarks(); 

			$group = array();
			foreach($data AS $series => $dat) { 
				$rdata = array();
				foreach($data[$series] AS $row) { $rdata[] = $row;  }
				if (count($rdata)<3) { die("ERROR::Graph::Cannot create a Radar Plot with less than 3 data points."); }
				// Create the radar plot
				$bplot = new RadarPlot($rdata);
				$bplot->mark->SetType($markers[$series]);
				$bplot->mark->SetFillColor($colours[$series]);
				$bplot->mark->SetWidth(3*$k);
				$bplot->SetColor($colours[$series]);
				if ($series == 0) { $bplot->SetFillColor('lightred'); }
				else { $bplot->SetFill(false); }
				$bplot->SetLineWeight(1*$k);
				$bplot->SetLegend($labels[$series]);
				if ($bandw) { $bplot->SetShadow("gray5"); }
				$graph->Add($bplot);
			}
	}
	// LINE
	else if ($type == 'line') {
			// Setup the graph. 
			$graph->img->SetMargin($pml*$k,$pmr*$k,$pmt*$k,$pmb*$k);	// LRTB
			$graph->SetScale($axes);
			$graph->yaxis->SetFont(FF_USERFONT,FS_NORMAL,8*$k);

			if ($ylabel) {
				$graph->yaxis->title->SetFont(FF_USERFONT,FS_NORMAL,8*$k);
				$graph->yaxis->SetTitle($ylabel,'middle');
				$graph->yaxis->SetTitleMargin($yaxislblmargin*$k); 
			}

			$graph->yaxis->SetLabelMargin(4*$k); 
			if ($percent || $show_percent) { $graph->yaxis->SetLabelFormat('%d%%'); }	// Percent

			// Show 0 label on Y-axis (default is not to show)
			$graph->yscale->ticks->SupressZeroLabel(true);
			if ($hide_y_axis) { $graph->yaxis->Hide(); }
			if ($hide_grid) { $graph->ygrid->Show(false); }

			// Setup X-axis labels
			$graph->xaxis->SetFont(FF_USERFONT,FS_NORMAL,8*$k);
			$graph->xaxis->SetTickLabels($legends);
			$graph->xaxis->SetLabelAngle($xlangle);
			$graph->xaxis->SetLabelMargin(4*$k); 
			// X-axis title
			if ($xlabel) {
				$graph->xaxis->title->SetFont(FF_USERFONT,FS_NORMAL,8*$k);
				$graph->xaxis->SetTitle($xlabel,'middle');
				$graph->xaxis->SetTitleMargin($xaxislblmargin*$k); 
			}
			foreach($data AS $series => $rdata) { 
				$bplot = new LinePlot($rdata);
				$bplot->mark->SetType($markers[$series]);
				$bplot->mark->SetFillColor($colours[$series]);
				$bplot->mark->SetWidth(4*$k);
				if ($show_values) {
					$bplot->value-> Show();	// Not if scatter
					$bplot->value->SetMargin(6*$k); 
					$bplot->value->SetColor("darkred");
					$bplot->value->SetFont( FF_USERFONT, FS_NORMAL, 8*$k);
					if ($percent || $show_percent) { $bplot->value->SetFormat( '%d%%'); }
					else { $bplot->value->SetFormat("%s"); }
				}
				// Set color for each line
				$bplot->SetColor($colours[$series]);
				$bplot->SetWeight(2*$k);
				$bplot->SetLegend($labels[$series]);
				if ($bandw) { $bplot->SetShadow("gray5"); }
				// Indent the X-scale so the first and last point doesn't fall on the edges
				$bplot->SetCenter();
				$graph->Add($bplot);
			}

	}
	// XY or SCATTER
	else if ($type == 'xy' || $type == 'scatter') {
			// Setup the graph. 
			$graph->img->SetMargin($pml*$k,$pmr*$k,$pmt*$k,$pmb*$k);	// LRTB
			$graph->SetScale($axes);
			// Setup font for axis
			$graph->yaxis->SetFont(FF_USERFONT,FS_NORMAL,8*$k);
			// Y-axis title
			if ($labels[1]) {
				$graph->yaxis->title->SetFont(FF_USERFONT,FS_NORMAL,8*$k);
				$graph->yaxis->SetTitleMargin($yaxislblmargin*$k); 
				$graph->yaxis->SetTitle($labels[1],'middle');
			}


			$graph->yaxis->SetLabelMargin(4*$k); 
			if ($percent || $show_percent) { $graph->yaxis->SetLabelFormat('%d%%'); }	// Percent

			// Show 0 label on Y-axis (default is not to show)
			$graph->yscale->ticks->SupressZeroLabel(true);
			// Just let the maximum be autoscaled
			$graph->yaxis->scale->SetAutoMin(0); 
			if ($hide_y_axis) { $graph->yaxis->Hide(); }
			if ($hide_grid) { $graph->ygrid->Show(false); }

			// Setup X-axis labels
			$graph->xaxis->SetFont(FF_USERFONT,FS_NORMAL,8*$k);
// mPDF 2.5 Corrects labelling of x-axis
//			$graph->xaxis->SetTickLabels($legends);
			$graph->xaxis->SetLabelAngle(50);
			$graph->xaxis->SetLabelMargin(4*$k); 
			// X-axis title
			if ($labels[0]) {
				$graph->xaxis->title->SetFont(FF_USERFONT,FS_NORMAL,8*$k);
				$graph->xaxis->SetTitleMargin($xaxislblmargin*$k); 
				$graph->xaxis->SetTitle($labels[0],'middle');
			}

			// Create the bar plot
			// SPLINES
			if ($splines && $type=='xy') {
				$spline = new Spline($data[0],$data[1]);
				list($newx,$newy) = $spline->Get(100);
			}
			else {
				$newx = $data[0];
				$newy = $data[1];
			}

			if ($type=='xy') {
				// LINE PLOT
				$bplot = new LinePlot($newy, $newx);
				// Set color for each line
				$bplot->SetColor($fills[0]);
				$bplot->SetWeight(4*$k);
				if ($bandw) { $bplot->SetShadow("gray5"); }
				$graph->Add($bplot);
			}

			// SCATTER PLOT
			$cplot = new ScatterPlot($data[1], $data[0]);

			$cplot->mark->SetType($markers[0]);
			$cplot->mark->SetFillColor($fills[0]);
			$cplot->mark->SetWidth(8*$k);
			if ($show_values) {
// mPDF 2.5 
				if ($type=='xy') { $cplot->value->Show(); }	// Not if scatter
				$cplot->value->SetMargin(8*$k); 
				$cplot->value->SetColor("darkred");
				$cplot->value->SetFont( FF_USERFONT, FS_NORMAL, 6*$k);

				if ($percent || $show_percent) { $cplot->value->SetFormat( '%d%%'); }
				else { $cplot->value->SetFormat("%s"); }
			}

			// Set color for each line
			$cplot->SetColor($fills[0]);
			$cplot->SetWeight(4*$k);
			if ($bandw) { $cplot->SetShadow("gray5"); }
			$graph->Add($cplot);

	}
	// BAR
	else if ($type == 'bar') {
			// Setup the graph. 
			$graph->img->SetMargin($pml*$k,$pmr*$k,$pmt*$k,$pmb*$k);	// LRTB
			$graph->SetScale($axes);
			// Setup y-axis
			$graph->yaxis->SetFont(FF_USERFONT,FS_NORMAL,8*$k);
			if ($hide_y_axis) { $graph->yaxis->Hide(); }
			if ($hide_grid) { $graph->ygrid->Show(false); }
			$graph->yaxis->SetLabelMargin(4*$k); 
			if ($ylabel) {
				$graph->yaxis->title->SetFont(FF_USERFONT,FS_NORMAL,8*$k);
				$graph->yaxis->SetTitle($ylabel,'middle');
				$graph->yaxis->SetTitleMargin($yaxislblmargin*$k); 
			}
			// Show 0 label on Y-axis (default is not to show)
			$graph->yscale->ticks->SupressZeroLabel(false);
			// Setup X-axis labels
			$graph->xaxis->SetFont(FF_USERFONT,FS_NORMAL,8*$k);
			$graph->xaxis->SetTickLabels($legends);
			$graph->xaxis->SetLabelAngle($xlangle);
			$graph->xaxis->SetLabelMargin(4*$k); 
			// X-axis title
			if ($xlabel) {
				$graph->xaxis->title->SetFont(FF_USERFONT,FS_NORMAL,8*$k);
				$graph->xaxis->SetTitle($xlabel,'middle');
				$graph->xaxis->SetTitleMargin($xaxislblmargin*$k); 
			}

			$group = array();
			foreach($data AS $series => $dat) { 
				$rdata = array();
				foreach($data[$series] AS $row) { $rdata[] = $row;  }

				// Create the bar plot
				$bplot = new BarPlot($rdata);
				$bplot->SetWidth(0.6);	// for SINGLE??
				// Setup color for gradient fill style 
				if ($bandw) { $bplot->SetPattern( $patterns[$series]); }
				else { $bplot->SetFillGradient($fills[$series],"#EEEEEE",GRAD_LEFT_REFLECTION); }

				// Set color for the frame of each bar
				$bplot->SetColor("darkgray");
				$bplot->SetLegend($labels[$series]);
				if ($bandw) { $bplot->SetShadow("gray5"); }
				if ($show_values) {
					$bplot->value->Show();
					$bplot->value->SetMargin(6*$k); 
					$bplot->value->SetColor("darkred");
					$bplot->value->SetFont( FF_USERFONT, FS_NORMAL, 8*$k);
					if ($percent || $show_percent) { $bplot->value->SetFormat( '%d%%'); }
					else { $bplot->value->SetFormat("%s"); }
				}

				$group[] = $bplot;
			}
			if (count($data)==1) {
				$graph->Add($group[0]);
			}
			else {
				// Create the grouped bar plot 
				if ($stacked) {
					$gbplot = new AccBarPlot ($group); 
				}
				else {
					$gbplot = new GroupBarPlot ($group); 
				}
				$graph->Add($gbplot);
			}
	}
	else if ($type == 'horiz_bar') {
			$graph->SetScale($axes);
			$graph->Set90AndMargin($pml*$k,$pmr*$k,$pmt*$k,$pmb*$k);	// LRTB

			// Setup y-axis
			$graph->yaxis->SetFont(FF_USERFONT,FS_NORMAL,8*$k);
			$graph->yaxis->SetLabelMargin(4*$k); 

			$graph->yaxis->SetPos('max');	// Intersect at top of x-axis i.e. y axis is at bottom
			// First make the labels look right
			$graph->yaxis->SetLabelAlign('center','top');
			if ($percent || $show_percent) { $graph->yaxis->SetLabelFormat('%d%%'); }
			$graph->yaxis->SetLabelSide(SIDE_RIGHT);
			$graph->yaxis->scale->SetGrace(10); 	// sets 10% headroom
			if ($hide_y_axis) { $graph->yaxis->Hide(); }
			if ($hide_grid) { $graph->ygrid->Show(false); }

			// The fix the tick marks
			$graph->yaxis->SetTickSide(SIDE_LEFT);
			if ($ylabel) {
				$graph->yaxis->title->SetFont(FF_USERFONT,FS_NORMAL,8*$k);
				$graph->yaxis->SetTitle($ylabel,'middle');
				$graph->yaxis->SetTitleMargin($yaxislblmargin*$k); 
				// Finally setup the title
				$graph->yaxis->SetTitleSide(SIDE_RIGHT);
				// To align the title to the right use :
				$graph->yaxis->title->Align('right');
				$graph->yaxis->title->SetAngle(0);

			}

			// Show 0 label on Y-axis (default is not to show)
			$graph->yscale->ticks->SupressZeroLabel(false);
			// Setup X-axis labels
			$graph->xaxis->SetFont(FF_USERFONT,FS_NORMAL,8*$k);
			$graph->xaxis->title->SetAngle(90);
			$graph->xaxis->SetTickLabels($legends);
			$graph->xaxis->SetLabelMargin(4*$k); 
			// X-axis title
			if ($xlabel) {
				$graph->xaxis->title->SetFont(FF_USERFONT,FS_NORMAL,8*$k);
				$graph->xaxis->SetTitleMargin($xaxislblmargin*$k); 
				$graph->xaxis->SetTitle($xlabel,'middle');
			}
			$group = array();
			foreach($data AS $series => $dat) { 
				$rdata = array();
				foreach($data[$series] AS $row) { $rdata[] = $row;  }
				// Create the bar pot
				$bplot = new BarPlot($rdata);
				$bplot->SetWidth(0.6);	// for SINGLE??
				// Setup color for gradient fill style 
				if ($bandw) { $bplot->SetPattern( $patterns[$series]); }
				else { $bplot->SetFillGradient($fills[$series],"#EEEEEE",GRAD_LEFT_REFLECTION); }

				// Set color for the frame of each bar
				$bplot->SetColor("darkgray");
				$bplot->SetLegend($labels[$series]);
				if ($bandw) { $bplot->SetShadow("gray5"); }
				if ($show_values) {
					$bplot->value-> Show();
					$bplot->value->SetMargin(6*$k); 
					$bplot->value->SetColor("darkred");
					$bplot->value->SetFont( FF_USERFONT, FS_NORMAL, 8*$k);
					if ($percent || $show_percent) { $bplot->value->SetFormat( '%d%%'); }
					else { $bplot->value->SetFormat("%s"); }
				}

				$group[] = $bplot;
			}
			if (count($data)==1) {
				$graph->Add($group[0]);
			}
			else {
				// Create the grouped bar plot 
				if ($stacked) {
					$gbplot = new AccBarPlot ($group); 
				}
				else {
					$gbplot = new GroupBarPlot ($group); 
				}
				$graph->Add($gbplot);
			}
	}
	if ($graph) {
		$graph->Stroke( _MPDF_PATH.$figure_file);
		$srcpath = str_replace("\\","/",dirname(__FILE__)) . "/";
		$srcpath .= $figure_file;
		return array('file'=>$srcpath, 'w'=>$w, 'h'=>$h);
	}
   }
   return false;
}
//======================================================================================================
//======================================================================================================
//======================================================================================================
//======================================================================================================

?>