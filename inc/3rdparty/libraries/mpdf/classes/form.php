<?php

class form {

var $mpdf = null;

var $forms;
var $formn;

//Active Forms
var $formSubmitNoValueFields;
var $formExportType;
var $formSelectDefaultOption;
var $formUseZapD;
/* Form Styles */
var $form_border_color; 
var $form_background_color; 
var $form_border_width;
var $form_border_style;
var $form_button_border_color; 
var $form_button_background_color;
var $form_button_border_width;
var $form_button_border_style;
var $form_radio_color;
var $form_radio_background_color; 

var $form_element_spacing;

// Active forms
var $formMethod;
var $formAction;
var $form_fonts;
var $form_radio_groups;
var $form_checkboxes;
var $pdf_acro_array;

var $pdf_array_co;
var $array_form_button_js;
var $array_form_choice_js;
var $array_form_text_js;

/* Button Text */
var $form_button_text;
var $form_button_text_over;
var $form_button_text_click;
var $form_button_icon;


// FORMS
var $textarea_lineheight;

function form(&$mpdf) {
	$this->mpdf = $mpdf;

	// ACTIVE FORMS
	$this->formExportType = 'xfdf'; // 'xfdf' or 'html'
	$this->formSubmitNoValueFields = true;	// Whether to include blank fields when submitting data
	$this->formSelectDefaultOption = true;	// for Select drop down box; if no option is explicitly maked as selected,
							// this determines whether to select 1st option (as per browser)
							// - affects whether "required" attribute is relevant
	$this->formUseZapD = true;		// Determine whether to use ZapfDingbat icons for radio/checkboxes

	// FORM STYLES
	// These can alternatively use a 4 number string to represent CMYK colours
	$this->form_border_color = '0.6 0.6 0.72'; 		// RGB
	$this->form_background_color = '0.975 0.975 0.975'; 	// RGB
	$this->form_border_width = '1';		// 0 doesn't seem to work as it should
	$this->form_border_style = 'S';		// B - Bevelled; D - Double
	$this->form_button_border_color = '0.2 0.2 0.55'; 
	$this->form_button_background_color = '0.941 0.941 0.941';
	$this->form_button_border_width = '1';
	$this->form_button_border_style = 'S';
	$this->form_radio_color = '0.0 0.0 0.4'; 	// radio and checkbox
	$this->form_radio_background_color = '0.9 0.9 0.9'; 

	// FORMS
	$this->textarea_lineheight = 1.25;

	// FORM ELEMENT SPACING
	$this->form_element_spacing['select']['outer']['h'] = 0.5;	// Horizontal spacing around SELECT
	$this->form_element_spacing['select']['outer']['v'] = 0.5;	// Vertical spacing around SELECT
	$this->form_element_spacing['select']['inner']['h'] = 0.7;	// Horizontal padding around SELECT
	$this->form_element_spacing['select']['inner']['v'] = 0.7;	// Vertical padding around SELECT
	$this->form_element_spacing['input']['outer']['h'] = 0.5;
	$this->form_element_spacing['input']['outer']['v'] = 0.5;
	$this->form_element_spacing['input']['inner']['h'] = 0.7;
	$this->form_element_spacing['input']['inner']['v'] = 0.7;
	$this->form_element_spacing['textarea']['outer']['h'] = 0.5;
	$this->form_element_spacing['textarea']['outer']['v'] = 0.5;
	$this->form_element_spacing['textarea']['inner']['h'] = 1;
	$this->form_element_spacing['textarea']['inner']['v'] = 0.5;
	$this->form_element_spacing['button']['outer']['h'] = 0.5;
	$this->form_element_spacing['button']['outer']['v'] = 0.5;
	$this->form_element_spacing['button']['inner']['h'] = 2;
	$this->form_element_spacing['button']['inner']['v'] = 1;

	// INITIALISE non-configurable
	$this->formMethod = 'POST';
	$this->formAction = '';
	$this->form_fonts = array();
	$this->form_radio_groups = array();
	$this->form_checkboxes = false;
	$this->forms = array();
	$this->pdf_array_co = '';


}


function print_ob_text($objattr,$w,$h,$texto,$rtlalign,$k,$blockdir) {
		// TEXT/PASSWORD INPUT
			if ($this->mpdf->useActiveForms) {
				// Flags: 1 - Readonly; 2 - Required; 3 - No export; 13 - textarea; 14 - Password
				$flags = array();
				if ($objattr['disabled'] || $objattr['readonly']) { $flags[] = 1; }	// readonly
				if ($objattr['disabled']) { $flags[] = 3; }	// no export
				if ($objattr['disabled']) { $objattr['color'] = array(3,128,128,128); }	// gray out disabled 
				if ($objattr['required']) { $flags[] = 2; }	// required
				if (!$objattr['spellcheck']) { $flags[] = 23; }	// DoNotSpellCheck
				if ($objattr['subtype']=='PASSWORD') { $flags[] = 14; }
				$this->mpdf->SetTColor($objattr['color']);
				$fieldalign = $rtlalign;
				if ($objattr['text_align']) { $fieldalign = $objattr['text_align']; }
				if ($objattr['subtype']=='PASSWORD') { $val = $objattr['value']; }
				else { $val = $objattr['text']; }
				// mPDF 5.3.25
				$js = array();
				if ($objattr['onCalculate']) { $js[] = array('C', $objattr['onCalculate']); }
				if ($objattr['onValidate']) { $js[] = array('V', $objattr['onValidate']); }
				if ($objattr['onFormat']) { $js[] = array('F', $objattr['onFormat']); }
				if ($objattr['onKeystroke']) { $js[] = array('K', $objattr['onKeystroke']); }
				$this->SetFormText( $w, $h, $objattr['fieldname'], $val, $val, $objattr['title'], $flags, $fieldalign, false, $objattr['maxlength'], $js, $objattr['background-col'], $objattr['border-col']  );
				$this->mpdf->SetTColor($this->mpdf->ConvertColor(0));
			}
			else {
				$w -= $this->form_element_spacing['input']['outer']['h']*2  /$k;
				$h -= $this->form_element_spacing['input']['outer']['v']*2 /$k;
				$this->mpdf->x += $this->form_element_spacing['input']['outer']['h'] /$k;
				$this->mpdf->y += $this->form_element_spacing['input']['outer']['v'] /$k;
				// Chop texto to max length $w-inner-padding
				while ($this->mpdf->GetStringWidth($texto) > $w-($this->form_element_spacing['input']['inner']['h']*2)) {
					$texto = mb_substr($texto,0,mb_strlen($texto,$this->mpdf->mb_enc)-1,$this->mpdf->mb_enc);
				}
				$save_linemaxfontsize = $this->mpdf->linemaxfontsize;
				$this->mpdf->linemaxfontsize = $this->mpdf->FontSize;	
			  	$this->mpdf->SetLineWidth(0.2 /$k );
				$this->mpdf->magic_reverse_dir($texto, false, $blockdir);	// *RTL*
				if (isset($objattr['disabled']) && $objattr['disabled']) { 
					$this->mpdf->SetFColor($this->mpdf->ConvertColor(225));
					$this->mpdf->SetTColor($this->mpdf->ConvertColor(127));
				}
				else if (isset($objattr['readonly']) && $objattr['readonly']) { 
					$this->mpdf->SetFColor($this->mpdf->ConvertColor(225));
					$this->mpdf->SetTColor($this->mpdf->ConvertColor(0));
				}
				else {
					$this->mpdf->SetFColor($this->mpdf->ConvertColor(250));
					$this->mpdf->SetTColor($this->mpdf->ConvertColor(0));
				}
				$this->mpdf->Cell($w,$h,$texto,1,0,$rtlalign,1,'',0,$this->form_element_spacing['input']['inner']['h'] /$k ,$this->form_element_spacing['input']['inner']['h'] /$k , 'M');
				$this->mpdf->SetFColor($this->mpdf->ConvertColor(255));
				$this->mpdf->SetTColor($this->mpdf->ConvertColor(0));
				$this->mpdf->linemaxfontsize = $save_linemaxfontsize;
			}
}

function print_ob_textarea($objattr,$w,$h,$texto,$rtlalign,$k,$blockdir) {
		   // TEXTAREA
		    if ($this->mpdf->useActiveForms) {
				// Flags: 1 - Readonly; 2 - Required; 3 - No export; 13 - textarea; 14 - Password
				$flags = array();
				$flags = array(13);	// textarea
				if ($objattr['disabled'] || $objattr['readonly']) { $flags[] = 1; }	// readonly
				if ($objattr['disabled']) { $flags[] = 3; }	// no export
				if ($objattr['disabled']) { $objattr['color'] = array(3,128,128,128); }	// gray out disabled
				if ($objattr['required']) { $flags[] = 2; }	// required
				if (!$objattr['spellcheck']) { $flags[] = 23; }	// DoNotSpellCheck
				if ($objattr['donotscroll']) { $flags[] = 24; }	// DoNotScroll
				$this->mpdf->SetTColor($objattr['color']);
				$fieldalign = $rtlalign;
				if ($texto == ' ') { $texto = ''; }	// mPDF 5.3.24
				if ($objattr['text_align']) { $fieldalign = $objattr['text_align']; }
				// mPDF 5.3.25
				$js = array();
				if ($objattr['onCalculate']) { $js[] = array('C', $objattr['onCalculate']); }
				if ($objattr['onValidate']) { $js[] = array('V', $objattr['onValidate']); }
				if ($objattr['onFormat']) { $js[] = array('F', $objattr['onFormat']); }
				if ($objattr['onKeystroke']) { $js[] = array('K', $objattr['onKeystroke']); }
				$this->SetFormText( $w, $h, $objattr['fieldname'], $texto, $texto, $objattr['title'], $flags, $fieldalign , false, -1, $js, $objattr['background-col'], $objattr['border-col']  );
				$this->mpdf->SetTColor($this->mpdf->ConvertColor(0));
		    }
		    else {
			$w -= $this->form_element_spacing['textarea']['outer']['h']*2 /$k ;
			$h -= $this->form_element_spacing['textarea']['outer']['v']*2 /$k ;
         		$this->mpdf->x += $this->form_element_spacing['textarea']['outer']['h'] /$k ;
         		$this->mpdf->y += $this->form_element_spacing['textarea']['outer']['v'] /$k ;
			$this->mpdf->SetLineWidth(0.2 /$k );
			if (isset($objattr['disabled']) && $objattr['disabled']) { 
				$this->mpdf->SetFColor($this->mpdf->ConvertColor(225));
				$this->mpdf->SetTColor($this->mpdf->ConvertColor(127));
			}
			else if (isset($objattr['readonly']) && $objattr['readonly']) { 
				$this->mpdf->SetFColor($this->mpdf->ConvertColor(225));
				$this->mpdf->SetTColor($this->mpdf->ConvertColor(0));
			}
			else {
				$this->mpdf->SetFColor($this->mpdf->ConvertColor(250));
				$this->mpdf->SetTColor($this->mpdf->ConvertColor(0));
			}
			$this->mpdf->Rect($this->mpdf->x,$this->mpdf->y,$w,$h,'DF');
			$w -= $this->form_element_spacing['textarea']['inner']['h']*2 /$k ;
			$this->mpdf->x += $this->form_element_spacing['textarea']['inner']['h'] /$k ;
			$this->mpdf->y += $this->form_element_spacing['textarea']['inner']['v'] /$k ;
			$linesneeded = $this->mpdf->WordWrap($texto,$w);
			if ($linesneeded > $objattr['rows']) { //Too many words inside textarea
				$textoaux = explode("\n",$texto);
                        $texto = '';
                        for($i=0;$i<$objattr['rows'];$i++) {
                          if ($i == ($objattr['rows']-1)) $texto .= $textoaux[$i];
                          else $texto .= $textoaux[$i] . "\n";
                        }
				$texto = mb_substr($texto,0,mb_strlen($texto,$this->mpdf->mb_enc)-4,$this->mpdf->mb_enc) . "...";
			}
			if ($texto != '') $this->mpdf->MultiCell($w,$this->mpdf->FontSize*$this->textarea_lineheight,$texto,0,'',0,'',$blockdir,true);
			$this->mpdf->SetFColor($this->mpdf->ConvertColor(255));
			$this->mpdf->SetTColor($this->mpdf->ConvertColor(0));
		    }
}

function print_ob_select($objattr,$w,$h,$texto,$rtlalign,$k,$blockdir) {
		// SELECT
			if ($this->mpdf->useActiveForms) {
				// Flags: 1 - Readonly; 2 - Required; 3 - No export; 19 - edit (only if combo)
				$flags = array();
				if ($objattr['disabled']) { $flags[] = 1; }	// readonly
				if ($objattr['disabled']) { $flags[] = 3; }	// no export
				if ($objattr['disabled']) { $objattr['color'] = array(3,128,128,128); }	// gray out disabled
				if ($objattr['required']) { $flags[] = 2; }	// required
				if ($objattr['multiple'] && $objattr['size']>1) { $flags[] = 22; }   //flag 22 = multiselect (listbox)
				if ($objattr['size']<2) { 
					$flags[] = 18;    //flag 18 = combobox (else a listbox)
					if ($objattr['editable']) { $flags[] = 19; }	// editable
				}
				// only spellcheck if combo and editable
				if (!$objattr['spellcheck'] || $objattr['size']>1 || !$objattr['editable']) { $flags[] = 23; }	// DoNotSpellCheck
				if ($objattr['subtype']=='PASSWORD') { $flags[] = 14; }
				if ($objattr['onChange']) { $js = $objattr['onChange']; }
				else { $js = ''; }	// mPDF 5.3.37
				$data = array('VAL' => array(), 'OPT' => array(), 'SEL' => array(), );
				for($i=0; $i<count($objattr['items']); $i++) {
					$item = $objattr['items'][$i];
					$data['VAL'][] = $item['exportValue'];
					$data['OPT'][] = $item['content'];
					if ($item['selected']) { $data['SEL'][] = $i; }
				}
				if (count($data['SEL'])==0 && $this->formSelectDefaultOption) {$data['SEL'][] = 0; }
				$this->mpdf->SetTColor($objattr['color']);
				$this->SetFormChoice( $w, $h, $objattr['fieldname'], $flags, $data, $rtlalign, $js );
				$this->mpdf->SetTColor($this->mpdf->ConvertColor(0));
			}
			else {
			  $save_linemaxfontsize = $this->mpdf->linemaxfontsize;
			  $this->mpdf->linemaxfontsize = $this->mpdf->FontSize;
			  $this->mpdf->magic_reverse_dir($texto, false, $blockdir);	// *RTL*
			  $this->mpdf->SetLineWidth(0.2 /$k );
				if (isset($objattr['disabled']) && $objattr['disabled']) { 
					$this->mpdf->SetFColor($this->mpdf->ConvertColor(225));
					$this->mpdf->SetTColor($this->mpdf->ConvertColor(127));
				}
				else {
					$this->mpdf->SetFColor($this->mpdf->ConvertColor(250));
					$this->mpdf->SetTColor($this->mpdf->ConvertColor(0));
				}
				$w -= $this->form_element_spacing['select']['outer']['h']*2 /$k ;
				$h -= $this->form_element_spacing['select']['outer']['v']*2 /$k ;
				$this->mpdf->x += $this->form_element_spacing['select']['outer']['h'] /$k ;
				$this->mpdf->y += $this->form_element_spacing['select']['outer']['v'] /$k ;
			  $this->mpdf->Cell($w-($this->mpdf->FontSize*1.4),$h,$texto,1,0,$rtlalign,1,'',0,$this->form_element_spacing['select']['inner']['h'] /$k,$this->form_element_spacing['select']['inner']['h'] /$k , 'M') ;
			  $this->mpdf->SetFColor($this->mpdf->ConvertColor(190));
			  $save_font = $this->mpdf->FontFamily;
           		  $save_currentfont = $this->mpdf->currentfontfamily;
			  if ($this->mpdf->PDFA || $this->mpdf->PDFX) {
				if (($this->mpdf->PDFA && !$this->mpdf->PDFAauto) || ($this->mpdf->PDFX && !$this->mpdf->PDFXauto)) { $this->mpdf->PDFAXwarnings[] = "Core Adobe font Zapfdingbats cannot be embedded in mPDF - used in Form element: Select - which is required for PDFA1-b or PDFX/1-a. (Different character/font will be substituted.)"; }
			   	$this->mpdf->SetFont('sans');
				if ($this->mpdf->_charDefined($this->mpdf->CurrentFont['cw'], 9660)) { $down = "\xe2\x96\xbc"; }
				else { $down = '='; }
				$this->mpdf->Cell(($this->mpdf->FontSize*1.4),$h,$down,1,0,'C',1,'',0,0,0, 'M') ;
			  }
			  else {
			  	$this->mpdf->SetFont('czapfdingbats','',0);
			  	$this->mpdf->Cell(($this->mpdf->FontSize*1.4),$h,chr(116),1,0,'C',1,'',0,0,0, 'M') ;
			  }
			  $this->mpdf->SetFont($save_font,'',0);
           		  $this->mpdf->currentfontfamily = $save_currentfont;
			  $this->mpdf->linemaxfontsize = $save_linemaxfontsize;
			  $this->mpdf->SetFColor($this->mpdf->ConvertColor(255));
			  $this->mpdf->SetTColor($this->mpdf->ConvertColor(0));
			}
}

function print_ob_imageinput($objattr,$w,$h,$texto,$rtlalign,$k,$blockdir) {
		// INPUT/BUTTON as IMAGE
		     if ($this->mpdf->useActiveForms) {
				// Flags: 1 - Readonly; 3 - No export; 
				$flags = array();
				if ($objattr['disabled']) { $flags[] = 1; }	// readonly
				if ($objattr['disabled']) { $flags[] = 3; }	// no export
				if ($objattr['onClick']) { $js = $objattr['onClick']; }
				else { $js = ''; }
				$this->SetJSButton( $w, $h, $objattr['fieldname'],  $objattr['value'], $js, $objattr['ID'], $objattr['title'], $flags, $objattr['Indexed']);
		     }
		     else {
			$this->mpdf->y = $objattr['INNER-Y'];
			$this->mpdf->_out( sprintf("q %.3F 0 0 %.3F %.3F %.3F cm /I%d Do Q",$objattr['INNER-WIDTH'] *_MPDFK,$objattr['INNER-HEIGHT'] *_MPDFK,$objattr['INNER-X'] *_MPDFK,($this->mpdf->h-($objattr['INNER-Y'] +$objattr['INNER-HEIGHT'] ))*_MPDFK,$objattr['ID'] ) );
			if (isset($objattr['BORDER-WIDTH']) && $objattr['BORDER-WIDTH']) { $this->mpdf->PaintImgBorder($objattr,$is_table); }
		     }
}

function print_ob_button($objattr,$w,$h,$texto,$rtlalign,$k,$blockdir) {
		// BUTTON
		     if ($this->mpdf->useActiveForms) {
				// Flags: 1 - Readonly; 3 - No export; 
				$flags = array();
				if ($objattr['disabled']) { $flags[] = 1; }	// readonly
				if ($objattr['disabled']) { $flags[] = 3; }	// no export
				if ($objattr['disabled']) { $objattr['color'] = array(3,128,128,128); }	
				$this->mpdf->SetTColor($objattr['color']);
				if ($objattr['subtype'] == 'RESET') {
					$this->SetFormButtonText( $objattr['value'] );
					$this->SetFormReset( $w, $h, $objattr['fieldname'], $objattr['value'], $objattr['title'], $flags, $objattr['background-col'], $objattr['border-col'], $objattr['noprint'] );	
				}
				else if ($objattr['subtype'] == 'SUBMIT') {
					$url = $this->formAction;
					$type = $this->formExportType;
					$method = $this->formMethod;
					$this->SetFormButtonText( $objattr['value'] );
					$this->SetFormSubmit( $w, $h, $objattr['fieldname'],  $objattr['value'], $url, $objattr['title'], $type, $method, $flags, $objattr['background-col'], $objattr['border-col'], $objattr['noprint'] );
				}
				else if ($objattr['subtype'] == 'BUTTON') {
					$this->SetFormButtonText( $objattr['value'] );
					if ($objattr['onClick']) { $js = $objattr['onClick']; }
					else { $js = ''; }
					$this->SetJSButton( $w, $h, $objattr['fieldname'],  $objattr['value'], $js, 0, $objattr['title'], $flags, false, $objattr['background-col'], $objattr['border-col'], $objattr['noprint'] );
				}
				$this->mpdf->SetTColor($this->mpdf->ConvertColor(0));
		     }
		     else {
			   $this->mpdf->SetLineWidth(0.2 /$k );
				$this->mpdf->SetFColor($this->mpdf->ConvertColor(190));
				$w -= $this->form_element_spacing['button']['outer']['h']*2 /$k ;
				$h -= $this->form_element_spacing['button']['outer']['v']*2 /$k ;
				$this->mpdf->x += $this->form_element_spacing['button']['outer']['h'] /$k ;
				$this->mpdf->y += $this->form_element_spacing['button']['outer']['v'] /$k ;
			   $this->mpdf->RoundedRect($this->mpdf->x, $this->mpdf->y, $w, $h, 0.5 /$k , 'DF');
				$w -= $this->form_element_spacing['button']['inner']['h']*2 /$k ;
				$h -= $this->form_element_spacing['button']['inner']['v']*2 /$k ;
				$this->mpdf->x += $this->form_element_spacing['button']['inner']['h'] /$k ;
				$this->mpdf->y += $this->form_element_spacing['button']['inner']['v'] /$k ;
			$save_linemaxfontsize = $this->mpdf->linemaxfontsize;	
			$this->mpdf->linemaxfontsize = $this->mpdf->FontSize;	
			   $this->mpdf->magic_reverse_dir($texto, false, $blockdir);	// *RTL*
			   $this->mpdf->Cell($w,$h,$texto,'',0,'C',0,'',0,0,0, 'M') ;
			   $this->mpdf->SetFColor($this->mpdf->ConvertColor(0));
			$this->mpdf->linemaxfontsize = $save_linemaxfontsize;
		     }
}

function print_ob_checkbox($objattr,$w,$h,$texto,$rtlalign,$k,$blockdir,$x,$y) {
		// CHECKBOX
		     if ($this->mpdf->useActiveForms) {
				// Flags: 1 - Readonly; 2 - Required; 3 - No export; 
				$flags = array();
				if ($objattr['disabled']) { $flags[] = 1; }	// readonly
				if ($objattr['disabled']) { $flags[] = 3; }	// no export
				$checked = false;
				if ($objattr['checked']) { $checked = true; }
				if ($this->formUseZapD) {
					$save_font = $this->mpdf->FontFamily;
           				$save_currentfont = $this->mpdf->currentfontfamily;
					$this->mpdf->SetFont('czapfdingbats','',0);
				}
				$this->SetCheckBox( $w, $h, $objattr['fieldname'], $objattr['value'], $objattr['title'], $checked, $flags, $objattr['disabled'] );
				if ($this->formUseZapD) {
					$this->mpdf->SetFont($save_font,'',0);
           				$this->mpdf->currentfontfamily = $save_currentfont;
				}
		     }
		     else {
			$iw = $w * 0.7;
			$ih = $h * 0.7;
			$lx = $x + (($w-$iw)/2); 
			$ty = $y + (($h-$ih)/2);
			$rx = $lx + $iw;
			$by = $ty + $ih;
			$this->mpdf->SetLineWidth(0.2 /$k );
			if (isset($objattr['disabled']) && $objattr['disabled']) { 
				$this->mpdf->SetFColor($this->mpdf->ConvertColor(225));
				$this->mpdf->SetDColor($this->mpdf->ConvertColor(127));
			}
			else {
				$this->mpdf->SetFColor($this->mpdf->ConvertColor(250));
				$this->mpdf->SetDColor($this->mpdf->ConvertColor(0));
			}
			$this->mpdf->Rect($lx,$ty,$iw,$ih,'DF');
			if (isset($objattr['checked']) && $objattr['checked']) {
				//Round join and cap
				$this->mpdf->SetLineCap(1);
				$this->mpdf->Line($lx,$ty,$rx,$by);
				$this->mpdf->Line($lx,$by,$rx,$ty);
				//Set line cap style back to square
				$this->mpdf->SetLineCap(2);
			}
			$this->mpdf->SetFColor($this->mpdf->ConvertColor(255));
			$this->mpdf->SetDColor($this->mpdf->ConvertColor(0));
		    }
}

function print_ob_radio($objattr,$w,$h,$texto,$rtlalign,$k,$blockdir,$x,$y) {
		// RADIO
			if ($this->mpdf->useActiveForms) {
				// Flags: 1 - Readonly; 2 - Required; 3 - No export; 
				$flags = array();
				if ($objattr['disabled']) { $flags[] = 1; }	// readonly
				if ($objattr['disabled']) { $flags[] = 3; }	// no export
				$checked = false;
				if ($objattr['checked']) { $checked = true; }
				if ($this->formUseZapD) {
					$save_font = $this->mpdf->FontFamily;
           				$save_currentfont = $this->mpdf->currentfontfamily;
					$this->mpdf->SetFont('czapfdingbats','',0);
				}
				$this->SetRadio( $w, $h, $objattr['fieldname'], $objattr['value'], $objattr['title'], $checked, $flags, $objattr['disabled'] ); 
				if ($this->formUseZapD) {
					$this->mpdf->SetFont($save_font,'',0);
           				$this->mpdf->currentfontfamily = $save_currentfont;
				}
			}
			else {
				$this->mpdf->SetLineWidth(0.2 /$k );
				$radius = $this->mpdf->FontSize *0.35;
				$cx = $x + ($w/2); 
				$cy = $y + ($h/2);
				if (isset($objattr['disabled']) && $objattr['disabled']) { 
					$this->mpdf->SetFColor($this->mpdf->ConvertColor(127));
					$this->mpdf->SetDColor($this->mpdf->ConvertColor(127));
				}
				else {
					$this->mpdf->SetFColor($this->mpdf->ConvertColor(0));
					$this->mpdf->SetDColor($this->mpdf->ConvertColor(0));
				}
				$this->mpdf->Circle($cx,$cy,$radius,'D');
				if (isset($objattr['checked']) && $objattr['checked']) {
					$this->mpdf->Circle($cx,$cy,$radius*0.4,'DF');
				}
				$this->mpdf->SetFColor($this->mpdf->ConvertColor(255));
				$this->mpdf->SetDColor($this->mpdf->ConvertColor(0));

			}
}


// In _putpages
function countPageForms($n, &$totaladdnum) {
			foreach( $this->forms as $form ) {
				if ( $form['page'] == $n ) {
					$totaladdnum++;
					if ( $form['typ'] == 'Tx' ) {
						if ( isset($this->array_form_text_js[$form['T']]) ) {
							if ( isset($this->array_form_text_js[$form['T']]['F']) ) { $totaladdnum++; }
							if ( isset($this->array_form_text_js[$form['T']]['K']) ) { $totaladdnum++; }
							if ( isset($this->array_form_text_js[$form['T']]['V']) ) { $totaladdnum++; }
							if ( isset($this->array_form_text_js[$form['T']]['C']) ) { $totaladdnum++; }
						}
					}
					if ( $form['typ'] == 'Bt' ) {
						if ( isset($this->array_form_button_js[$form['T']]) ) { $totaladdnum++; }
						if ( isset($this->form_button_icon[$form['T']]) ) { 
							$totaladdnum++; 
							if ( $this->form_button_icon[$form['T']]['Indexed'] ) { $totaladdnum++; }
						}
						if ( $form['subtype'] == 'radio' ) { $totaladdnum+=2; }
						else if ( $form['subtype'] == 'checkbox' && $this->formUseZapD ) { $totaladdnum++; }
						else if ( $form['subtype'] == 'checkbox' && !$this->formUseZapD ) { $totaladdnum+=2; }
					}
					if ( $form['typ'] == 'Ch' ) {
						if ( isset($this->array_form_choice_js[$form['T']]) ) { $totaladdnum++; }
					}
				}
			}
}

// In _putpages
function addFormIds($n, &$s, &$annotid) {
				foreach( $this->forms as $form ) {
					if ( $form['page'] == $n ) {
						$s .= ($annotid) . ' 0 R ';
						$annotid++;
						if ( $form['typ'] == 'Tx' ) {
							if ( isset($this->array_form_text_js[$form['T']]) ) {
								if ( isset($this->array_form_text_js[$form['T']]['F']) ) { $annotid++; }
								if ( isset($this->array_form_text_js[$form['T']]['K']) ) { $annotid++; }
								if ( isset($this->array_form_text_js[$form['T']]['V']) ) { $annotid++; }
								if ( isset($this->array_form_text_js[$form['T']]['C']) ) { $annotid++; }
							}
						}
						if ( $form['typ'] == 'Bt' ) {
							if ( isset($this->array_form_button_js[$form['T']]) ) { $annotid++; }
							if ( isset($this->form_button_icon[$form['T']]) ) { 
								$annotid++; 
								if ( $this->form_button_icon[$form['T']]['Indexed'] ) { $annotid++; }
							}
							if ( $form['subtype'] == 'radio' ) { $annotid+=2; }
							else if ( $form['subtype'] == 'checkbox' && $this->formUseZapD ) { $annotid++; }
							else if ( $form['subtype'] == 'checkbox' && !$this->formUseZapD ) { $annotid+=2; }
						}
						if ( $form['typ'] == 'Ch' ) {
							if ( isset($this->array_form_choice_js[$form['T']]) ) { $annotid++; }
						}
					}
				}
}

// In _putannots
function _putFormItems($n, $hPt) {
				foreach( $this->forms as $val) {
					if ( $val['page'] == $n ) {
						if ( $val['typ'] == 'Tx' ) $this->_putform_tx( $val, $hPt );
						if ( $val['typ'] == 'Ch' ) $this->_putform_ch( $val, $hPt );
						if ( $val['typ'] == 'Bt' ) $this->_putform_bt( $val, $hPt );
					}
				}
}

// In _putannots
function _putRadioItems($n) {
		// Output Radio Groups
		$key = 1;
		foreach($this->form_radio_groups AS $name=>$frg) {
			$this->mpdf->_newobj();
			$this->pdf_acro_array .= $this->mpdf->n.' 0 R ';
			$this->mpdf->_out('<<');
			$this->mpdf->_out('/Type /Annot ');
			$this->mpdf->_out('/Subtype /Widget');
			$this->mpdf->_out('/NM '.$this->mpdf->_textstring(sprintf('%04u-%04u', $n, (3000 + $key++))));
			$this->mpdf->_out('/M '.$this->mpdf->_textstring('D:'.date('YmdHis')));
			$this->mpdf->_out('/Rect [0 0 0 0] ');
			$this->mpdf->_out('/FT /Btn ');
			if ($frg['disabled']) { $flags=array(1,3,15,16); }	// NoExport and readonly
			else { $flags=array(15,16); }	// Flags for Radiobutton, and NoToggleToOff
			$this->mpdf->_out('/Ff '.$this->_setflag($flags) );
			$kstr = '';
			$optstr = '';
			foreach($frg['kids'] AS $kid) {
				$kstr .= $this->forms[$kid['n']]['obj'].' 0 R ';
		//		$optstr .= ' '.$this->mpdf->_textstring($kid['OPT']).' ';
			}
			$this->mpdf->_out('/Kids [ '.$kstr.' ] ');	// 11 0 R 12 0 R etc.
		//	$this->mpdf->_out('/Opt [ '.$optstr.' ] ');

			//V entry holds index corresponding to the appearance state of
			//whichever child field is currently in the on state = or Off
			if (isset($frg['on'])) { $state = $frg['on']; }
			else { $state = 'Off'; }
         		$this->mpdf->_out('/V /'.$state.' ');
         		$this->mpdf->_out('/DV /'.$state.' ');
         		$this->mpdf->_out('/T '.$this->mpdf->_textstring($name).' ');
			$this->mpdf->_out('>>');
			$this->mpdf->_out('endobj');
		}
}

function _putFormsCatalog() {
		if (isset($this->pdf_acro_array) ) {
			$this->mpdf->_out('/AcroForm << /DA (/F1 0 Tf 0 g )');
			$this->mpdf->_out('/Q 0');
			$this->mpdf->_out('/Fields ['.$this->pdf_acro_array.']');
			$f = '';
			foreach($this->form_fonts AS $fn) {
				if (is_array($this->mpdf->fonts[$fn]['n'])) { $this->mpdf->Error("Cannot use fonts with SMP or SIP characters for interactive Form elements"); }
				$f .= '/F'.$this->mpdf->fonts[$fn]['i'].' '.$this->mpdf->fonts[$fn]['n'].' 0 R ';
			}
			$this->mpdf->_out('/DR << /Font << '.$f.' >> >>');
			// CO Calculation Order
			if ( $this->pdf_array_co ) {
				$this->mpdf->_out('/CO ['.$this->pdf_array_co.']'); 
			}
			$this->mpdf->_out('/NeedAppearances true');
			$this->mpdf->_out('>>');
		}
}



function SetFormButtonJS( $name, $js ) {
	$js = str_replace("\t",' ', trim($js) );
	if ( isset($name) && isset($js) ) {
		$this->array_form_button_js[$this->mpdf->_escape($name)] = array(
				'js' => $js
				);
	}
}

function SetFormChoiceJS( $name, $js ) {
	$js = str_replace("\t",' ', trim($js) );
	if ( isset($name) && isset($js) ) {
		$this->array_form_choice_js[$this->mpdf->_escape($name)] = array(
				'js' => $js
				);
	}
}

function SetFormTextJS( $name, $js) {
   for ($i=0; $i<count($js); $i++) {
	$j = str_replace("\t",' ', trim($js[$i][1]) );
	$format = $js[$i][0];
	if ($name) {
		$this->array_form_text_js[$this->mpdf->_escape($name)][$format] = array('js' => $j);
	}
   }
}


function Win1252ToPDFDocEncoding($txt) {
	$Win1252ToPDFDocEncoding = array(
		chr(0200) => chr(0240), chr(0214) => chr(0226), chr(0212) => chr(0227), chr(0237) => chr(0230), 
		chr(0225) => chr(0200), chr(0210) => chr(0032), chr(0206) => chr(0201), chr(0207) => chr(0202),
		chr(0205) => chr(0203), chr(0227) => chr(0204), chr(0226) => chr(0205), chr(0203) => chr(0206),
		chr(0213) => chr(0210), chr(0233) => chr(0211), chr(0211) => chr(0213), chr(0204) => chr(0214),
		chr(0223) => chr(0215), chr(0224) => chr(0216), chr(0221) => chr(0217), chr(0222) => chr(0220),
		chr(0202) => chr(0221), chr(0232) => chr(0235), chr(0230) => chr(0037), chr(0231) => chr(0222),
		chr(0216) => chr(0231), chr(0240) => chr(0040)
	); // mPDF 5.3.46
	return strtr($txt, $Win1252ToPDFDocEncoding );
}


function SetFormText( $w, $h, $name, $value = '', $default = '', $title = '', $flags = array(), $align='L', $hidden = false, $maxlen=-1, $js='', $background_col=false, $border_col=false ) {
	// Flags: 1 - Readonly; 2 - Required; 3 - No export; 13 - textarea; 14 - Password
	$this->formn++;
	if( $align == 'C' ) { $align = '1'; }
	else if( $align == 'R' ) { $align = '2'; }
	else { $align = '0'; }
	if ($maxlen < 1) { $maxlen = false; }
	if (!preg_match('/^[a-zA-Z0-9_:\-]+$/', $name)) {
			$this->mpdf->Error("Field [".$name."] must have a name attribute, which can only contain letters, numbers, colon(:), undersore(_) or hyphen(-)"); 
	}
	if ($this->mpdf->onlyCoreFonts) { 
		$value = $this->Win1252ToPDFDocEncoding($value); 
		$default = $this->Win1252ToPDFDocEncoding($default); 
		$title = $this->Win1252ToPDFDocEncoding($title); 
	}
	else { 
		if (isset($this->mpdf->CurrentFont['subset'])) {
			$this->mpdf->UTF8StringToArray($value, true);	// Add characters to font subset
			$this->mpdf->UTF8StringToArray($default, true);	// Add characters to font subset
			$this->mpdf->UTF8StringToArray($title, true);	// Add characters to font subset
		}
		if ($value) $value = $this->mpdf->UTF8ToUTF16BE($value, true);
		if ($default ) $default = $this->mpdf->UTF8ToUTF16BE($default, true);
		$title = $this->mpdf->UTF8ToUTF16BE($title, true); 
	}
	if ($background_col) { $bg_c = $this->mpdf->SetColor($background_col, 'CodeOnly'); }
	else { $bg_c = $this->form_background_color; }
	if ($border_col) { $bc_c = $this->mpdf->SetColor($border_col, 'CodeOnly'); }
	else { $bc_c = $this->form_border_color; }
	$f = array(	'n'	 => $this->formn,
			'typ' 	 => 'Tx',
			'page'	 => $this->mpdf->page,
			'x'	 => $this->mpdf->x,
			'y'	 => $this->mpdf->y,
			'w'	 => $w,
			'h'	 => $h,
			'T'	 => $name,
			'FF'	 => $flags,
			'V'	 => $value,
			'DV'	 => $default,
			'TU'	 => $title,
			'hidden' => $hidden,
			'Q' 	 => $align,
			'maxlen' 	 => $maxlen,
			'BS_W'   => $this->form_border_width,
			'BS_S'   => $this->form_border_style,
			'BC_C'   => $bc_c,
			'BG_C'   => $bg_c,
			'style'	=> array(
				'font'	=> $this->mpdf->FontFamily,
				'fontsize'	=> $this->mpdf->FontSizePt,
				'fontcolor'	=> $this->mpdf->TextColor,
			)
	);
	if (is_array($js) && count($js)>0) { $this->SetFormTextJS( $name, $js); }	// mPDF 5.3.25
	if ($this->mpdf->keep_block_together) { $this->mpdf->ktForms[]= $f; }
	else if ($this->mpdf->writingHTMLheader || $this->mpdf->writingHTMLfooter) { $this->mpdf->HTMLheaderPageForms[]= $f; }
	else { 
			if ($this->mpdf->ColActive) { 
				$this->mpdf->columnbuffer[] = array('s' => 'ACROFORM', 'col' => $this->mpdf->CurrCol, 'x' => $this->mpdf->x, 'y' => $this->mpdf->y,
					'h' => $h);
				$this->mpdf->columnForms[$this->mpdf->CurrCol][INTVAL($this->mpdf->x)][INTVAL($this->mpdf->y)] = $this->formn; 
			}
		$this->forms[$this->formn] = $f; 
	}
	if (!in_array($this->mpdf->FontFamily, $this->form_fonts)) { 
		$this->form_fonts[] = $this->mpdf->FontFamily; 
		$this->mpdf->fonts[$this->mpdf->FontFamily]['used'] = true;
	}
	if ( !$hidden ) 	$this->mpdf->x += $w;

}


	function SetFormChoice( $w, $h, $name, $flags, $array, $align='L', $js = '' ) {
		$this->formn++;
		if( $this->mpdf->blk[$this->mpdf->blklvl]['direction'] == 'rtl' ) { $align = '2'; }
		else { $align = '0'; }
		if (!preg_match('/^[a-zA-Z0-9_:\-]+$/', $name)) { 
			$this->mpdf->Error("Field [".$name."] must have a name attribute, which can only contain letters, numbers, colon(:), undersore(_) or hyphen(-)"); 
		}
		if ($this->mpdf->onlyCoreFonts) { 
			for($i=0;$i<count($array['VAL']);$i++) {
				$array['VAL'][$i] = $this->Win1252ToPDFDocEncoding($array['VAL'][$i]); 
				$array['OPT'][$i] = $this->Win1252ToPDFDocEncoding($array['OPT'][$i]); 
			}
		}
		else { 
			for($i=0;$i<count($array['VAL']);$i++) {
				if (isset($this->mpdf->CurrentFont['subset'])) {
					$this->mpdf->UTF8StringToArray($array['VAL'][$i], true);	// Add characters to font subset
					$this->mpdf->UTF8StringToArray($array['OPT'][$i], true);	// Add characters to font subset
				}
				if ($array['VAL'][$i] ) $array['VAL'][$i] = $this->mpdf->UTF8ToUTF16BE($array['VAL'][$i], true);
				if ($array['OPT'][$i] ) $array['OPT'][$i] = $this->mpdf->UTF8ToUTF16BE($array['OPT'][$i], true);
			}
		}
		$f = array(	'n'	=> $this->formn,
			'typ' 	=> 'Ch',
			'page'	=> $this->mpdf->page,
			'x'	=> $this->mpdf->x,
			'y'	=> $this->mpdf->y,
			'w'	=> $w,
			'h'	=> $h,
			'T'	=> $name,
			'OPT'	=> $array,
			'FF'	=> $flags,
			'Q' 	 => $align,
			'BS_W'   => $this->form_border_width,
			'BS_S'   => $this->form_border_style,
			'BC_C'   => $this->form_border_color,
			'BG_C'   => $this->form_background_color,
			'style' => array(
				'font'	=> $this->mpdf->FontFamily,
				'fontsize'	=> $this->mpdf->FontSizePt,
				'fontcolor'	=> $this->mpdf->TextColor,
			)
		);
		if ($js) { $this->SetFormChoiceJS( $name, $js ); }
		if ($this->mpdf->keep_block_together) { $this->mpdf->ktForms[]= $f; }
		else if ($this->mpdf->writingHTMLheader || $this->mpdf->writingHTMLfooter) { $this->mpdf->HTMLheaderPageForms[]= $f; }
		else { 
			if ($this->mpdf->ColActive) { 
				$this->mpdf->columnbuffer[] = array('s' => 'ACROFORM', 'col' => $this->mpdf->CurrCol, 'x' => $this->mpdf->x, 'y' => $this->mpdf->y,
					'h' => $h);
				$this->mpdf->columnForms[$this->mpdf->CurrCol][INTVAL($this->mpdf->x)][INTVAL($this->mpdf->y)] = $this->formn; 
			}
			$this->forms[$this->formn] = $f; 
		}
		if (!in_array($this->mpdf->FontFamily, $this->form_fonts)) { 
			$this->form_fonts[] = $this->mpdf->FontFamily; 
			$this->mpdf->fonts[$this->mpdf->FontFamily]['used'] = true;
		}
		$this->mpdf->x += $w;
	}

	// CHECKBOX
	function SetCheckBox( $w, $h, $name, $value, $title = '', $checked = false, $flags = array(), $disabled=false  ) {
		$this->SetFormButton( $w, $h, $name, $value, 'checkbox', $title, $flags, $checked, $disabled );
		$this->mpdf->x += $w;
	}


	// RADIO
	function SetRadio( $w, $h, $name, $value, $title = '', $checked = false, $flags = array(), $disabled=false ) {
		$this->SetFormButton( $w, $h, $name, $value, 'radio', $title, $flags, $checked, $disabled );
		$this->mpdf->x += $w;
	}


	function SetFormReset( $w, $h, $name, $value = 'Reset', $title = '', $flags = array(), $background_col=false, $border_col=false, $noprint=false ) {
		if (!$name) { $name = 'Reset'; }
		$this->SetFormButton( $w, $h, $name, $value, 'reset', $title, $flags, false, false, $background_col, $border_col, $noprint);
		$this->mpdf->x += $w;
	}


	function SetJSButton( $w, $h, $name, $value, $js, $image_id = 0, $title = '', $flags = array(), $indexed=false , $background_col=false, $border_col=false, $noprint=false ) {
		$this->SetFormButton( $w, $h, $name, $value, 'js_button', $title, $flags, false, false, $background_col, $border_col, $noprint);
		// pos => 1 = no caption, icon only; 0 = caption only 
		if ($image_id) {
			$this->form_button_icon[$this->mpdf->_escape($name)] = array(
				'pos' => 1, 	
				'image_id' => $image_id, 
				'Indexed' => $indexed,
				);
		}
		if ($js) { $this->SetFormButtonJS( $name, $js ); }
		$this->mpdf->x += $w;
	}


	function SetFormSubmit( $w, $h, $name, $value = 'Submit', $url, $title = '', $typ = 'html', $method = 'POST', $flags = array(), $background_col=false, $border_col=false, $noprint=false) {
		if (!$name) { $name = 'Submit'; }
		$this->SetFormButton( $w, $h, $name, $value, 'submit', $title, $flags, false, false, $background_col, $border_col, $noprint);
		$this->forms[$this->formn]['URL'] = $url;
		$this->forms[$this->formn]['method'] = $method;
		$this->forms[$this->formn]['exporttype'] = $typ;
		$this->mpdf->x += $w;
	}


	function SetFormButtonText( $ca, $rc = '', $ac = '' ) {
		if ($this->mpdf->onlyCoreFonts) { 
			$ca = $this->Win1252ToPDFDocEncoding($ca); 
			if ($rc) $rc = $this->Win1252ToPDFDocEncoding($rc); 
			if ($ac) $ac = $this->Win1252ToPDFDocEncoding($ac); 
		}
		else { 
			if (isset($this->mpdf->CurrentFont['subset'])) {
				$this->mpdf->UTF8StringToArray($ca, true);	// Add characters to font subset
			}
			$ca = $this->mpdf->UTF8ToUTF16BE($ca, true); 
			if ($rc) { 
				if (isset($this->mpdf->CurrentFont['subset'])) { $this->mpdf->UTF8StringToArray($rc, true); }
				$rc = $this->mpdf->UTF8ToUTF16BE($rc, true); 
			}
			if ($ac) { 
				if (isset($this->mpdf->CurrentFont['subset'])) { $this->mpdf->UTF8StringToArray($ac, true); }
				$ac = $this->mpdf->UTF8ToUTF16BE($ac, true); 
			}
		}
		$this->form_button_text = $ca;
		$this->form_button_text_over = $rc ? $rc : $ca;
		$this->form_button_text_click = $ac ? $ac : $ca;
	}


	function SetFormButton( $bb, $hh, $name, $value, $type, $title = '', $flags = array(), $checked=false, $disabled=false, $background_col=false, $border_col=false, $noprint=false ) {
		$this->formn++;
		if (!preg_match('/^[a-zA-Z0-9_:\-]+$/', $name)) {
			$this->mpdf->Error("Field [".$name."] must have a name attribute, which can only contain letters, numbers, colon(:), undersore(_) or hyphen(-)"); 
		}
		if (!$this->mpdf->onlyCoreFonts) {
		   if (isset($this->mpdf->CurrentFont['subset'])) {
			$this->mpdf->UTF8StringToArray($title, true);	// Add characters to font subset
			$this->mpdf->UTF8StringToArray($value, true);	// Add characters to font subset
		   }
		   $title = $this->mpdf->UTF8ToUTF16BE($title, true); 
		   if ($type == 'checkbox') { 
			$uvalue = $this->mpdf->UTF8ToUTF16BE($value, true); 
		   }
		   else if ($type == 'radio') { 
			$uvalue = $this->mpdf->UTF8ToUTF16BE($value, true); 
			$value = mb_convert_encoding($value, 'Windows-1252', 'UTF-8'); 
		   }
		   else { 
			$value = $this->mpdf->UTF8ToUTF16BE($value, true); 
			$uvalue = $value;
		   }
		}
		else { 
			$title = $this->Win1252ToPDFDocEncoding($title); 
			$value = $this->Win1252ToPDFDocEncoding($value); 				//// ??? not needed
			$uvalue = mb_convert_encoding($value, 'UTF-8', 'Windows-1252'); 
			$uvalue = $this->mpdf->UTF8ToUTF16BE($uvalue, true); 
		}
		if ($type == 'radio' || $type == 'checkbox') {
			if (!preg_match('/^[a-zA-Z0-9_:\-\.]+$/', $value)) {
				$this->mpdf->Error("Field '".$name."' must have a value, which can only contain letters, numbers, colon(:), undersore(_), hyphen(-) or period(.)"); 
			}
		}
		if ($type == 'radio') {
			if (!isset($this->form_radio_groups[$name])) { 
				$this->form_radio_groups[$name] = array(
					'page' => $this->mpdf->page,
					'kids' => array(),
				);
			}
			$this->form_radio_groups[$name]['kids'][] = array(
					'n' => $this->formn, 'V'=> $value, 'OPT'=>$uvalue, 'disabled'=>$disabled
			);
			if ( $checked ) { $this->form_radio_groups[$name]['on'] = $value; }
			// Disable the whole radio group if one is disabled, because of inconsistency in PDF readers
			if ( $disabled ) { $this->form_radio_groups[$name]['disabled'] = true; }
		}
		if ($type == 'checkbox') {
			$this->form_checkboxes = true; 
		}
		if ( $checked ) { $activ = 1; }
		else { $activ = 0; }
		if ($background_col) { $bg_c = $this->mpdf->SetColor($background_col, 'CodeOnly'); }
		else { $bg_c = $this->form_button_background_color; }
		if ($border_col) { $bc_c = $this->mpdf->SetColor($border_col, 'CodeOnly'); }
		else { $bc_c = $this->form_button_border_color; }
		$f = array(	'n'	=> $this->formn,
			'typ' 	=> 'Bt',
			'page'	=> $this->mpdf->page,
			'subtype'	=> $type,
			'x'	=> $this->mpdf->x,
			'y'	=> $this->mpdf->y,
			'w'	=> $bb,
			'h'	=> $hh,
			'T'	=> $name,
			'V'	=> $value,
			'OPT'	=> $uvalue,
			'TU'	=> $title,
			'FF'	=> $flags,
			'CA'	=> $this->form_button_text,
			'RC'	=> $this->form_button_text_over,
			'AC'	=> $this->form_button_text_click,
			'BS_W'   => $this->form_button_border_width,
			'BS_S'   => $this->form_button_border_style,
			'BC_C'   => $bc_c,
			'BG_C'   => $bg_c,
			'activ'  => $activ,
			'disabled'  => $disabled,
			'noprint'  => $noprint,
			'style'  => array(
				'font'	=> $this->mpdf->FontFamily,
				'fontsize'	=> $this->mpdf->FontSizePt,
				'fontcolor'	=> $this->mpdf->TextColor,
			)
		);
		if ($this->mpdf->keep_block_together) { $this->mpdf->ktForms[]= $f; }
		else if ($this->mpdf->writingHTMLheader || $this->mpdf->writingHTMLfooter) { $this->mpdf->HTMLheaderPageForms[]= $f; }
		else { 
			if ($this->mpdf->ColActive) { 
				$this->mpdf->columnbuffer[] = array('s' => 'ACROFORM', 'col' => $this->mpdf->CurrCol, 'x' => $this->mpdf->x, 'y' => $this->mpdf->y,
					'h' => $hh);
				$this->mpdf->columnForms[$this->mpdf->CurrCol][INTVAL($this->mpdf->x)][INTVAL($this->mpdf->y)] = $this->formn; 
			}
			$this->forms[$this->formn] = $f; 
		}
		if (!in_array($this->mpdf->FontFamily, $this->form_fonts)) { 
			$this->form_fonts[] = $this->mpdf->FontFamily; 
			$this->mpdf->fonts[$this->mpdf->FontFamily]['used'] = true;
		}

		$this->form_button_text = NULL;
		$this->form_button_text_over = NULL;
		$this->form_button_text_click = NULL;
	}



function SetFormBorderWidth ( $string ) {
	switch( $string ) {
		case 'S': $this->form_border_width = '1'; 
			break;
		case 'M': $this->form_border_width = '2'; 
			break;
		case 'B': $this->form_border_width = '3'; 
			break;
		case '0': $this->form_border_width = '0'; 
			break;
		default: $this->form_border_width = '0'; 
			break;
	}
}


function SetFormBorderStyle ( $string ) {
	switch( $string ) { 
		case 'S': $this->form_border_style = 'S'; 
			break;
		case 'D': $this->form_border_style = 'D /D [3]'; 
			break;
		case 'B': $this->form_border_style = 'B'; 
			break;
		case 'I': $this->form_border_style = 'I'; 
			break;
		case 'U': $this->form_border_style = 'U'; 
			break;
		default: $this->form_border_style = 'B'; 
			break;
	}
}

function SetFormBorderColor ( $r, $g=-1, $b=-1 ) {
	if ( ($r==0 and $g==0 and $b==0) || $g==-1 )
		$this->form_border_color = sprintf('%.3F', $r/255);
	else
		$this->form_border_color = sprintf('%.3F %.3F %.3F', $r/255, $g/255, $b/255);
}

function SetFormBackgroundColor ( $r, $g=-1, $b=-1 ) {
	if ( ($r==0 and $g==0 and $b==0) || $g==-1 )
		$this->form_background_color = sprintf('%.3F', $r/255);
	else
		$this->form_background_color = sprintf('%.3F %.3F %.3F', $r/255, $g/255, $b/255);
}

function SetFormD ( $W, $S, $BC, $BG ) {
	$this->SetFormBorderWidth ( $W );
	$this->SetFormBorderStyle ( $S );
	$this->SetFormBorderColor ( $BC );
	$this->SetFormBackgroundColor ( $BG );
}

function _setflag( $array ) {
	$flag = 0;
	foreach($array as $val) { $flag += 1 << ($val-1); }
	return $flag;
}

function _form_rect( $x, $y, $w, $h, $hPt ) {
	$x = $x * _MPDFK;
	$y = $hPt - ($y  * _MPDFK);
	$x2 = $x + ($w * _MPDFK);
	$y2 = $y - ($h * _MPDFK);
	$rect = sprintf('%.3F %.3F %.3F %.3F', $x, $y2, $x2, $y );
	return $rect;
}


function _put_button_icon( $array , $w, $h ) {
	if (isset($array['image_id'])) {
		$info = false;
		foreach($this->mpdf->images AS $iid=>$img) {
			if ($img['i'] == $array['image_id']) { 
				$info = $this->mpdf->images[$iid];
				break; 
			}
		}
	}
	if (!$info) { die("Cannot find Button image"); }
	$this->mpdf->_newobj();
	$this->mpdf->_out('<<');
	$this->mpdf->_out('/Type /XObject');
	$this->mpdf->_out('/Subtype /Image');
	$this->mpdf->_out('/BBox [0 0 1 1]');
	$this->mpdf->_out('/Length '.strlen($info['data']));
	$this->mpdf->_out('/BitsPerComponent '.$info['bpc']);
	if ($info['cs']=='Indexed') {
		$this->mpdf->_out('/ColorSpace [/Indexed /DeviceRGB '.(strlen($info['pal'])/3-1).' '.($this->mpdf->n+1).' 0 R]');
	}
	else {
		$this->mpdf->_out('/ColorSpace /'.$info['cs']);
		if ($info['cs']=='DeviceCMYK')
			if($info['type']=='jpg') { $this->mpdf->_out('/Decode [1 0 1 0 1 0 1 0]'); }
	}
	if ( isset($info['f']) ) 
		$this->mpdf->_out('/Filter /'.$info['f']);
	if ( isset($info['parms']) ) 
		$this->mpdf->_out($info['parms']);
	$this->mpdf->_out('/Width '.$info['w']);
	$this->mpdf->_out('/Height '.$info['h']);
	$this->mpdf->_out('>>');
	$this->mpdf->_putstream($info['data']);
	$this->mpdf->_out('endobj');
	unset($array);
	//Palette
	if($info['cs']=='Indexed') {
		$filter=($this->mpdf->compress) ? '/Filter /FlateDecode ' : '';
		$this->mpdf->_newobj();
		$pal=($this->mpdf->compress) ? gzcompress($info['pal']) : $info['pal'];
		$this->mpdf->_out('<<'.$filter.'/Length '.strlen($pal).'>>');
		$this->mpdf->_putstream($pal);
		$this->mpdf->_out('endobj');
	}

}


function _putform_bt( $form, $hPt ) {
	$cc = 0;
	$put_xobject = 0;
	$put_js = 0;
	$put_icon = 0;
	$this->mpdf->_newobj();
	$n = $this->mpdf->n;
	if ($form['subtype'] != 'radio') $this->pdf_acro_array .= $n.' 0 R ';	// Add to /Field element
	$this->forms[ $form['n'] ]['obj'] = $n;
	$this->mpdf->_out('<<');
	$this->mpdf->_out('/Type /Annot ');
	$this->mpdf->_out('/Subtype /Widget');
	$this->mpdf->_out('/NM '.$this->mpdf->_textstring(sprintf('%04u-%04u', $n, (7000 + $form['n']))));
	$this->mpdf->_out('/M '.$this->mpdf->_textstring('D:'.date('YmdHis')));	
	$this->mpdf->_out('/Rect [ '.$this->_form_rect($form['x'],$form['y'],$form['w'],$form['h'], $hPt).' ]');
	$form['noprint'] ? $this->mpdf->_out('/F 0 ') : $this->mpdf->_out('/F 4 ');
	$this->mpdf->_out('/FT /Btn ');
	$this->mpdf->_out('/H /P ');
	if ( $form['subtype'] != 'radio' ) 	// mPDF 5.3.23
		$this->mpdf->_out('/T '.$this->mpdf->_textstring($form['T']) );
	$this->mpdf->_out('/TU '.$this->mpdf->_textstring($form['TU']) );
	if ( isset( $this->form_button_icon[ $form['T'] ] ) ) { $form['BS_W'] = 0; }
	if ($form['BS_W'] == 0) { $form['BC_C'] = $form['BG_C']; }
	$bstemp = '';
	$bstemp .= '/W '.$form['BS_W'].' ';
	$bstemp .= '/S /'.$form['BS_S'].' ';
	$temp = '';
	$temp .= '/BC [ '.$form['BC_C']." ] ";
	$temp .= '/BG [ '.$form['BG_C']." ] ";
	if ( $form['subtype'] == 'checkbox' ) {
		if ($form['disabled']) { 
			$radio_color = '0.5 0.5 0.5'; 
			$radio_background_color = '0.9 0.9 0.9'; 
		}
		else { 
			$radio_color = $this->form_radio_color; 
			$radio_background_color = $this->form_radio_background_color;
		}
		$temp = '';
		$temp .= '/BC [ '.$radio_color." ] ";
		$temp .= '/BG [ '.$radio_background_color." ] ";
		$this->mpdf->_out("/BS << /W 1 /S /S >>");
		$this->mpdf->_out("/MK << $temp >>");
		$this->mpdf->_out('/Ff '.$this->_setflag($form['FF']) );
		if ( $form['activ'] ) {
       			$this->mpdf->_out('/V /'.$this->mpdf->_escape($form['V']).' ');
       			$this->mpdf->_out('/DV /'.$this->mpdf->_escape($form['V']).' ');
       			$this->mpdf->_out('/AS /'.$this->mpdf->_escape($form['V']).' ');
		} else {
      			$this->mpdf->_out('/AS /Off ');
		}
		if ($this->formUseZapD) {
			$this->mpdf->_out('/DA (/F'.$this->mpdf->fonts['czapfdingbats']['i'].' 0 Tf '.$radio_color.' rg)');
			$this->mpdf->_out("/AP << /N << /".$this->mpdf->_escape($form['V'])." ".($this->mpdf->n+1)." 0 R /Off /Off >> >>");
		}
		else {
			$this->mpdf->_out('/DA (/F'.$this->mpdf->fonts[$this->mpdf->CurrentFont['fontkey']]['i'].' 0 Tf '.$radio_color.' rg)');	
			$this->mpdf->_out("/AP << /N << /".$this->mpdf->_escape($form['V'])." ".($this->mpdf->n+1)." 0 R /Off ".($this->mpdf->n+2)." 0 R >> >>");
		}
      	$this->mpdf->_out('/Opt [ '.$this->mpdf->_textstring($form['OPT']).' '.$this->mpdf->_textstring($form['OPT']).' ]');
	}


	if ( $form['subtype'] == 'radio' ) {
		if ($form['disabled'] || $this->form_radio_groups[$form['T']]['disabled']) { 
			$radio_color = '0.5 0.5 0.5'; 
			$radio_background_color = '0.9 0.9 0.9'; 
		}
		else { 
			$radio_color = $this->form_radio_color; 
			$radio_background_color = $this->form_radio_background_color;
		}
		$this->mpdf->_out('/Parent '.$this->form_radio_groups[$form['T']]['obj_id'].' 0 R ');
		$temp = '';
		$temp .= '/BC [ '.$radio_color." ] ";
		$temp .= '/BG [ '.$radio_background_color." ] ";
		$this->mpdf->_out("/BS << /W 1 /S /S >>");
		$this->mpdf->_out('/MK << '.$temp.' >> ');
		$form['FF'][] = 16;	// Radiobutton
		$form['FF'][] = 15;	// NoToggleOff - must be same as radio button group setting?
		$this->mpdf->_out('/Ff '.$this->_setflag($form['FF']) );
		if ($this->formUseZapD)
			$this->mpdf->_out('/DA (/F'.$this->mpdf->fonts['czapfdingbats']['i'].' 0 Tf '.$radio_color.' rg)');
		else
			$this->mpdf->_out('/DA (/F'.$this->mpdf->fonts[$this->mpdf->CurrentFont['fontkey']]['i'].' 0 Tf '.$radio_color.' rg)');
		$this->mpdf->_out("/AP << /N << /".$this->mpdf->_escape($form['V'])." ".($this->mpdf->n+1)." 0 R /Off ".($this->mpdf->n+2)." 0 R >> >>");
		if ( $form['activ'] ) {
			$this->mpdf->_out('/V /'.$this->mpdf->_escape($form['V']).' ');
			$this->mpdf->_out('/DV /'.$this->mpdf->_escape($form['V']).' ');
			$this->mpdf->_out('/AS /'.$this->mpdf->_escape($form['V']).' ');
		}
		else {
			$this->mpdf->_out('/AS /Off ');
		}
		$this->mpdf->_out("/AP << /N << /".$this->mpdf->_escape($form['V'])." ".($this->mpdf->n+1)." 0 R /Off ".($this->mpdf->n+2)." 0 R >> >>");
	//	$this->mpdf->_out('/Opt [ '.$this->mpdf->_textstring($form['OPT']).' '.$this->mpdf->_textstring($form['OPT']).' ]');
	}

	if ( $form['subtype'] == 'reset' ) {
		$temp .= $form['CA'] ? '/CA '.$this->mpdf->_textstring($form['CA']).' ' : '/CA '.$this->mpdf->_textstring($form['T']).' ';
		$temp .= $form['RC'] ? '/RC '.$this->mpdf->_textstring($form['RC']).' ' : '/RC '.$this->mpdf->_textstring($form['T']).' ';
		$temp .= $form['AC'] ? '/AC '.$this->mpdf->_textstring($form['AC']).' ' : '/AC '.$this->mpdf->_textstring($form['T']).' ';
		$this->mpdf->_out("/BS << $bstemp >>");
		$this->mpdf->_out('/MK << '.$temp.' >>');
		$this->mpdf->_out('/DA (/F'.$this->mpdf->fonts[$form['style']['font']]['i'].' '.$form['style']['fontsize'].' Tf '.$form['style']['fontcolor'].')');
		$this->mpdf->_out('/AA << /D << /S /ResetForm /Flags 1 >> >>');
		$form['FF'][] = 17;
		$this->mpdf->_out('/Ff '.$this->_setflag($form['FF']) );
	}


	if ( $form['subtype'] == 'submit' ) {
		$temp .= $form['CA'] ? '/CA '.$this->mpdf->_textstring($form['CA']).' ' : '/CA '.$this->mpdf->_textstring($form['T']).' ';
		$temp .= $form['RC'] ? '/RC '.$this->mpdf->_textstring($form['RC']).' ' : '/RC '.$this->mpdf->_textstring($form['T']).' ';
		$temp .= $form['AC'] ? '/AC '.$this->mpdf->_textstring($form['AC']).' ' : '/AC '.$this->mpdf->_textstring($form['T']).' ';
		$this->mpdf->_out("/BS << $bstemp >>");
		$this->mpdf->_out("/MK << $temp >>");
		$this->mpdf->_out('/DA (/F'.$this->mpdf->fonts[$form['style']['font']]['i'].' '.$form['style']['fontsize'].' Tf '.$form['style']['fontcolor'].')');
		// Bit 4 (8) = useGETmethod else use POST
		// Bit 3 (4) = HTML export format (charset chosen by Adobe)--- OR ---
		// Bit 6 (32) = XFDF export format (form of XML in UTF-8)
		if ($form['exporttype'] == 'xfdf') { $flag = 32; }	// 'xfdf' or 'html'
		else {
			if ($form['method'] == 'GET') { $flag = 12; }
			else { $flag = 4; }
		}
		// Bit 2 (2) = IncludeNoValueFields
		if ($this->formSubmitNoValueFields) $flag += 2;
		// To submit a value, needs to be in /AP dictionary, AND this object must contain a /Fields entry
		// listing all fields to output
		$this->mpdf->_out('/AA << /D << /S /SubmitForm /F ('.$form['URL'].') /Flags '.$flag.' >> >>');
		$form['FF'][] = 17;
		$this->mpdf->_out('/Ff '.$this->_setflag($form['FF']) );
	}

	if ( $form['subtype'] == 'js_button' ) {
		// Icon / image
		if ( isset( $this->form_button_icon[ $form['T'] ] ) ) {
			$cc++;
			$temp .= '/TP '.$this->form_button_icon[$form['T']]['pos'].' ';
			$temp .= '/I '.($cc + $this->mpdf->n).' 0 R ';		// Normal icon
			$temp .= '/RI '.($cc + $this->mpdf->n).' 0 R ';		// onMouseOver
			$temp .= '/IX '.($cc + $this->mpdf->n).' 0 R ';		// onClick / onMouseDown
			$temp .= '/IF << /SW /A /S /A /A [0.0 0.0] >> ';	// Icon fit dictionary
			if ($this->form_button_icon[ $form['T'] ]['Indexed']) { $cc++; }
			$put_icon = 1;
		}
		$temp .= $form['CA'] ? '/CA '.$this->mpdf->_textstring($form['CA']).' ' : '/CA '.$this->mpdf->_textstring($form['T']).' ';
		$temp .= $form['RC'] ? '/RC '.$this->mpdf->_textstring($form['RC']).' ' : '/RC '.$this->mpdf->_textstring($form['T']).' ';
		$temp .= $form['AC'] ? '/AC '.$this->mpdf->_textstring($form['AC']).' ' : '/AC '.$this->mpdf->_textstring($form['T']).' ';
		$this->mpdf->_out("/BS << $bstemp >>");
		$this->mpdf->_out("/MK << $temp >>");
		$this->mpdf->_out('/DA (/F'.$this->mpdf->fonts[$form['style']['font']]['i'].' '.$form['style']['fontsize'].' Tf '.$form['style']['fontcolor'].')');
		$form['FF'][] = 17;
		$this->mpdf->_out('/Ff '.$this->_setflag($form['FF']) );
		// Javascript
		if ( isset($this->array_form_button_js[$form['T']]) ) {
			$cc++;
			$this->mpdf->_out("/AA << /D ".($cc + $this->mpdf->n)." 0 R >>"); 
			$put_js = 1;
		}
	}

	$this->mpdf->_out('>>');
	$this->mpdf->_out('endobj');

	// additional objects
	// obj icon
	if ( $put_icon == 1 ) { 
		$this->_put_button_icon( $this->form_button_icon[ $form['T'] ], $form['w'], $form['h'] ); 
		$put_icon = NULL; 
	}
	// obj + 1
	if ( $put_js == 1 ) {
		$this->mpdf->_set_object_javascript( $this->array_form_button_js[$form['T']]['js'] );
		unset( $this->array_form_button_js[$form['T']] );
		$put_js = NULL;
	}

	// RADIO and CHECK BOX appearance streams
	$filter=($this->mpdf->compress) ? '/Filter /FlateDecode ' : '';
	if ( $form['subtype'] == 'radio' ) {
		// output 2 appearance streams for radio buttons on/off
		if ($this->formUseZapD) {
			$fs = sprintf('%.3F', $form['style']['fontsize']*1.25);
			$fi = 'czapfdingbats';
			$r_on = 'q '.$radio_color .' rg BT /F'.$this->mpdf->fonts[$fi]['i'].' '.$fs.' Tf 0 0 Td (4) Tj ET Q';
			$r_off = 'q '.$radio_color .' rg BT /F'.$this->mpdf->fonts[$fi]['i'].' '.$fs.' Tf 0 0 Td (8) Tj ET Q';
		}
		else {
			$matrix = sprintf('%.3F 0 0 %.3F 0 %.3F', $form['style']['fontsize']*1.33/10, $form['style']['fontsize']*1.25/10, $form['style']['fontsize']);
			$fill = $radio_background_color.' rg 3.778 -7.410 m 2.800 -7.410 1.947 -7.047 1.225 -6.322 c 0.500 -5.600 0.138 -4.747 0.138 -3.769 c 0.138 -2.788 0.500 -1.938 1.225 -1.213 c 1.947 -0.491 2.800 -0.128 3.778 -0.128 c 4.757 -0.128 5.610 -0.491 6.334 -1.213 c 7.056 -1.938 7.419 -2.788 7.419 -3.769 c 7.419 -4.747 7.056 -5.600 6.334 -6.322 c 5.610 -7.047 4.757 -7.410 3.778 -7.410 c h f ';
			$circle = '3.778 -6.963 m 4.631 -6.963 5.375 -6.641 6.013 -6.004 c 6.653 -5.366 6.972 -4.619 6.972 -3.769 c 6.972 -2.916 6.653 -2.172 6.013 -1.532 c 5.375 -0.894 4.631 -0.576 3.778 -0.576 c 2.928 -0.576 2.182 -0.894 1.544 -1.532 c 0.904 -2.172 0.585 -2.916 0.585 -3.769 c 0.585 -4.619 0.904 -5.366 1.544 -6.004 c 2.182 -6.641 2.928 -6.963 3.778 -6.963 c h 3.778 -7.410 m 2.800 -7.410 1.947 -7.047 1.225 -6.322 c 0.500 -5.600 0.138 -4.747 0.138 -3.769 c 0.138 -2.788 0.500 -1.938 1.225 -1.213 c 1.947 -0.491 2.800 -0.128 3.778 -0.128 c 4.757 -0.128 5.610 -0.491 6.334 -1.213 c 7.056 -1.938 7.419 -2.788 7.419 -3.769 c 7.419 -4.747 7.056 -5.600 6.334 -6.322 c 5.610 -7.047 4.757 -7.410 3.778 -7.410 c h f ';
			$r_on = 'q '.$matrix.' cm '.$fill .$radio_color.' rg '.$circle.'  '.$radio_color.' rg
5.184 -5.110 m 4.800 -5.494 4.354 -5.685 3.841 -5.685 c 3.331 -5.685 2.885 -5.494 2.501 -5.110 c 2.119 -4.725 1.925 -4.279 1.925 -3.769 c 1.925 -3.257 2.119 -2.810 2.501 -2.429 c 2.885 -2.044 3.331 -1.853 3.841 -1.853 c 4.354 -1.853 4.800 -2.044 5.184 -2.429 c 5.566 -2.810 5.760 -3.257 5.760 -3.769 c 5.760 -4.279 5.566 -4.725 5.184 -5.110 c h 
f Q ';
			$r_off = 'q '.$matrix.' cm '.$fill .$radio_color.' rg '.$circle.'  Q ';
		}

		$this->mpdf->_newobj();
		$p=($this->mpdf->compress) ? gzcompress($r_on) : $r_on;
		$this->mpdf->_out('<<'.$filter.'/Length '.strlen($p).' /Resources 2 0 R>>');
		$this->mpdf->_putstream($p);
		$this->mpdf->_out('endobj');

		$this->mpdf->_newobj();
		$p=($this->mpdf->compress) ? gzcompress($r_off) : $r_off;
		$this->mpdf->_out('<<'.$filter.'/Length '.strlen($p).' /Resources 2 0 R>>');
		$this->mpdf->_putstream($p);
		$this->mpdf->_out('endobj');
	}
	if ( $form['subtype'] == 'checkbox' ) {
		// First output appearance stream for check box on
		if ($this->formUseZapD) {
			$fs = sprintf('%.3F', $form['style']['fontsize']*1.25);
			$fi = 'czapfdingbats';
			$cb_on = 'q '.$radio_color .' rg BT /F'.$this->mpdf->fonts[$fi]['i'].' '.$fs.' Tf 0 0 Td (4) Tj ET Q';
			$cb_off = 'q '.$radio_color .' rg BT /F'.$this->mpdf->fonts[$fi]['i'].' '.$fs.' Tf 0 0 Td (8) Tj ET Q';
		}
		else {
			$matrix = sprintf('%.3F 0 0 %.3F 0 %.3F', $form['style']['fontsize']*1.33/10, $form['style']['fontsize']*1.25/10, $form['style']['fontsize']);
			$fill = $radio_background_color.' rg 7.395 -0.070 m 7.395 -7.344 l 0.121 -7.344 l 0.121 -0.070 l 7.395 -0.070 l h  f ';
			$square = '0.508 -6.880 m 6.969 -6.880 l 6.969 -0.534 l 0.508 -0.534 l 0.508 -6.880 l h 7.395 -0.070 m 7.395 -7.344 l 0.121 -7.344 l 0.121 -0.070 l 7.395 -0.070 l h ';
			$cb_on = 'q '.$matrix.' cm '.$fill. $radio_color.' rg '.$square.' f '.$radio_color.' rg
6.321 -1.352 m 5.669 -2.075 5.070 -2.801 4.525 -3.532 c 3.979 -4.262 3.508 -4.967 3.112 -5.649 c 3.080 -5.706 3.039 -5.779 2.993 -5.868 c 2.858 -6.118 2.638 -6.243 2.334 -6.243 c 2.194 -6.243 2.100 -6.231 2.052 -6.205 c 2.003 -6.180 1.954 -6.118 1.904 -6.020 c 1.787 -5.788 1.688 -5.523 1.604 -5.226 c 1.521 -4.930 1.480 -4.721 1.480 -4.600 c 1.480 -4.535 1.491 -4.484 1.512 -4.447 c 1.535 -4.410 1.579 -4.367 1.647 -4.319 c 1.733 -4.259 1.828 -4.210 1.935 -4.172 c 2.040 -4.134 2.131 -4.115 2.205 -4.115 c 2.267 -4.115 2.341 -4.232 2.429 -4.469 c 2.437 -4.494 2.444 -4.511 2.448 -4.522 c 2.451 -4.531 2.456 -4.546 2.465 -4.568 c 2.546 -4.795 2.614 -4.910 2.668 -4.910 c 2.714 -4.910 2.898 -4.652 3.219 -4.136 c 3.539 -3.620 3.866 -3.136 4.197 -2.683 c 4.426 -2.367 4.633 -2.103 4.816 -1.889 c 4.998 -1.676 5.131 -1.544 5.211 -1.493 c 5.329 -1.426 5.483 -1.368 5.670 -1.319 c 5.856 -1.271 6.066 -1.238 6.296 -1.217 c 6.321 -1.352 l h  f  Q ';
			$cb_off = 'q '.$matrix.' cm '.$fill. $radio_color.' rg '.$square.' f Q ';

		}
		$this->mpdf->_newobj();
		$p=($this->mpdf->compress) ? gzcompress($cb_on) : $cb_on;
		$this->mpdf->_out('<<'.$filter.'/Length '.strlen($p).' /Resources 2 0 R>>');
		$this->mpdf->_putstream($p);
		$this->mpdf->_out('endobj');

		// output appearance stream for check box off (only if not using ZapfDingbats)
		if (!$this->formUseZapD) {
			$this->mpdf->_newobj();
			$p=($this->mpdf->compress) ? gzcompress($cb_off) : $cb_off;
			$this->mpdf->_out('<<'.$filter.'/Length '.strlen($p).' /Resources 2 0 R>>');
			$this->mpdf->_putstream($p);
			$this->mpdf->_out('endobj');
		}

	}
	return $n;
}


function _putform_ch( $form, $hPt ) {
	$put_js = 0;
	$this->mpdf->_newobj();
	$n = $this->mpdf->n;
	$this->pdf_acro_array .= $n.' 0 R ';
	$this->forms[ $form['n'] ]['obj'] = $n;

	$this->mpdf->_out('<<');
	$this->mpdf->_out('/Type /Annot ');
	$this->mpdf->_out('/Subtype /Widget');
	$this->mpdf->_out('/Rect [ '.$this->_form_rect($form['x'],$form['y'],$form['w'],$form['h'], $hPt).' ]');
	$this->mpdf->_out('/F 4');
	$this->mpdf->_out('/FT /Ch');
	if ($form['Q']) $this->mpdf->_out('/Q '.$form['Q'].'');
	$temp = '';
	$temp .= '/W '.$form['BS_W'].' ';
	$temp .= '/S /'.$form['BS_S'].' ';
	$this->mpdf->_out("/BS << $temp >>");

	$temp = '';
	$temp .= '/BC [ '.$form['BC_C']." ] ";
	$temp .= '/BG [ '.$form['BG_C']." ] ";
	$this->mpdf->_out('/MK << '.$temp.' >>');

	$this->mpdf->_out('/NM '.$this->mpdf->_textstring(sprintf('%04u-%04u', $n, (6000 + $form['n']))));
	$this->mpdf->_out('/M '.$this->mpdf->_textstring('D:'.date('YmdHis')));

	$this->mpdf->_out('/T '.$this->mpdf->_textstring($form['T']) );
	$this->mpdf->_out('/DA (/F'.$this->mpdf->fonts[$form['style']['font']]['i'].' '.$form['style']['fontsize'].' Tf '.$form['style']['fontcolor'].')');

	$opt = '';
	for( $i = 0; $i < count($form['OPT']['VAL']) ; $i++ ) {
		$opt .= '[ '.$this->mpdf->_textstring($form['OPT']['VAL'][$i]).' '.$this->mpdf->_textstring($form['OPT']['OPT'][$i]).' ] ';
	}
	$this->mpdf->_out('/Opt [ '.$opt.']');

	// selected
	$selectItem = false;
	$selectIndex = false;
	foreach ( $form['OPT']['SEL'] as $selectKey => $selectVal ) {
      	$selectName = $this->mpdf->_textstring($form['OPT']['VAL'][$selectVal]);
      	$selectItem .= ' '.$selectName.' ';
      	$selectIndex .= ' '.$selectVal.' ';
	}
	if ( $selectItem ) {
		if (count($form['OPT']['SEL']) < 2) {
	      	$this->mpdf->_out('/V '.$selectItem.' ');
	      	$this->mpdf->_out('/DV '.$selectItem.' ');
		}
		else {
	      	$this->mpdf->_out('/V ['.$selectItem.'] ');
	      	$this->mpdf->_out('/DV ['.$selectItem.'] ');
		}
	      $this->mpdf->_out('/I ['.$selectIndex.'] ');
	}
    
	if ( is_array($form['FF']) && count($form['FF'])>0 ) {
		$this->mpdf->_out('/Ff '.$this->_setflag($form['FF']).' ');
	}
	// Javascript
	if ( isset($this->array_form_choice_js[$form['T']]) ) {
		$this->mpdf->_out("/AA << /V ".($this->mpdf->n+1)." 0 R >>"); 
		$put_js = 1;
	}

	$this->mpdf->_out('>>');
	$this->mpdf->_out('endobj');
	// obj + 1
	if ( $put_js == 1 ) {
		$this->mpdf->_set_object_javascript( $this->array_form_choice_js[$form['T']]['js'] );
		unset( $this->array_form_choice_js[$form['T']] );
		$put_js = NULL;
	}

	return $n;
}


function _putform_tx( $form, $hPt ) {
	$put_js = 0;
	$this->mpdf->_newobj();
	$n = $this->mpdf->n;
	$this->pdf_acro_array .= $n.' 0 R ';
	$this->forms[ $form['n'] ]['obj'] = $n;

	$this->mpdf->_out('<<');
	$this->mpdf->_out('/Type /Annot ');
	$this->mpdf->_out('/Subtype /Widget ');

	$this->mpdf->_out('/Rect [ '.$this->_form_rect($form['x'],$form['y'],$form['w'],$form['h'], $hPt).' ] ');
	$form['hidden'] ? $this->mpdf->_out('/F 2 ') : $this->mpdf->_out('/F 4 ');
	$this->mpdf->_out('/FT /Tx ');

	$this->mpdf->_out('/H /N ');
	$this->mpdf->_out('/R 0 ');

	if ( is_array($form['FF']) && count($form['FF'])>0 ) {
		$this->mpdf->_out('/Ff '.$this->_setflag($form['FF']).' ');
	}
	if ( isset($form['maxlen']) && $form['maxlen']>0 ) {
		$this->mpdf->_out('/MaxLen '.$form['maxlen']);
	}

	$temp = '';
	$temp .= '/W '.$form['BS_W'].' ';
	$temp .= '/S /'.$form['BS_S'].' ';
	$this->mpdf->_out("/BS << $temp >>");

	$temp = '';
	$temp .= '/BC [ '.$form['BC_C']." ] ";
	$temp .= '/BG [ '.$form['BG_C']." ] ";
	$this->mpdf->_out('/MK <<'.$temp.' >>');

	$this->mpdf->_out('/T '.$this->mpdf->_textstring($form['T']) );
	$this->mpdf->_out('/TU '.$this->mpdf->_textstring($form['TU']) );
	if ($form['V'] || $form['V']==='0')
		$this->mpdf->_out('/V '.$this->mpdf->_textstring($form['V']) );
	$this->mpdf->_out('/DV '.$this->mpdf->_textstring($form['DV']) );
	$this->mpdf->_out('/DA (/F'.$this->mpdf->fonts[$form['style']['font']]['i'].' '.$form['style']['fontsize'].' Tf '.$form['style']['fontcolor'].')');
	if ( $form['Q'] ) $this->mpdf->_out('/Q '.$form['Q'].'');

	$this->mpdf->_out('/NM '.$this->mpdf->_textstring(sprintf('%04u-%04u', $n, (5000 + $form['n']))));
	$this->mpdf->_out('/M '.$this->mpdf->_textstring('D:'.date('YmdHis')));


	if ( isset($this->array_form_text_js[$form['T']]) ) {
		$put_js = 1;
		$cc = 0;
		$js_str = '';

		if ( isset($this->array_form_text_js[$form['T']]['F']) ) { 
			$cc++; 
			$js_str .= '/F '.($cc + $this->mpdf->n).' 0 R '; 
		}
		if ( isset($this->array_form_text_js[$form['T']]['K']) ) { 
			$cc++; 
			$js_str .= '/K '.($cc + $this->mpdf->n).' 0 R '; 
		}
		if ( isset($this->array_form_text_js[$form['T']]['V']) ) { 
			$cc++; 
			$js_str .= '/V '.($cc + $this->mpdf->n).' 0 R '; 
		}
		if ( isset($this->array_form_text_js[$form['T']]['C']) ) { 
			$cc++; 
			$js_str .= '/C '.($cc + $this->mpdf->n).' 0 R '; 
			$this->pdf_array_co .= $this->mpdf->n.' 0 R ';
		}
		$this->mpdf->_out('/AA << '.$js_str.' >>');
	}

	$this->mpdf->_out('>>');
	$this->mpdf->_out('endobj');

	if ( $put_js == 1 ) {
		if ( isset($this->array_form_text_js[$form['T']]['F']) ) { 
			$this->mpdf->_set_object_javascript( $this->array_form_text_js[$form['T']]['F']['js'] ); 
			unset( $this->array_form_text_js[$form['T']]['F'] );
		}
		if ( isset($this->array_form_text_js[$form['T']]['K']) ) { 
			$this->mpdf->_set_object_javascript( $this->array_form_text_js[$form['T']]['K']['js'] ); 
			unset( $this->array_form_text_js[$form['T']]['K'] );
		}
		if ( isset($this->array_form_text_js[$form['T']]['V']) ) { 
			$this->mpdf->_set_object_javascript( $this->array_form_text_js[$form['T']]['V']['js'] ); 
			unset( $this->array_form_text_js[$form['T']]['V'] );
		}
		if ( isset($this->array_form_text_js[$form['T']]['C']) ) { 
			$this->mpdf->_set_object_javascript( $this->array_form_text_js[$form['T']]['C']['js'] ); 
			unset( $this->array_form_text_js[$form['T']]['C'] );
		}
	}
	return $n;
}



}

?>