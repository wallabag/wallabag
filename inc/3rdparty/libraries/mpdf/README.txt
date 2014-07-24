Installation
============
    * Download the .zip file and unzip it
    * Create a folder e.g. /mpdf on your server
    * Upload all of the files to the server, maintaining the folders as they are
    * Ensure that you have write permissions set (CHMOD 6xx or 7xx) for the following folders:
	 /ttfontdata/ - used to cache font data; improves performance a lot
	 /tmp/ - used for some images and ProgressBar
	 /graph_cache/ - if you are using JpGraph in conjunction with mPDF

To test the installation, point your browser to the basic example file : [path_to_mpdf_folder]/mpdf/examples/example_basic.php

If you wish to define a different folder for temporary files rather than /tmp/ see the note on 'Folder for temporary files' in 
 the section on Installation & Setup in the manual (http://mpdf1.com/manual/).

If you have problems, please read the section on troubleshooting in the manual.


Fonts
=====
I will refer to font names in 2 ways:
"CSS font-family name" - mPDF is designed primarily to read HTML and CSS. This is the name used in CSS e.g.
	<p style="font-family: 'Trebuchet MS';">

"mPDF font-family name" - the name used internally to process fonts. This could be anything you like,
	but by default mPDF will convert CSS font-family names by removing any spaces and changing
	to lowercase. Reading the name above, mPDF will look for a "mPDF font-family name" of
	'trebuchetms'.

The configurable values referred to below are set in the config_fonts.php file

When parsing HTML/CSS, mPDF will read the CSS font-family name (e.g. 'Trebuchet MS') and convert 
by removing any spaces and changing to lowercase, to look for a mPDF font-family name (trebuchetms). 

Next it will look for a translation (if set) e.g.:
$this->fonttrans = array(
	'trebuchetms' => 'trebuchet'
)

Now the mPDF font-family name to be used is 'trebuchet'

If you wish to make this font available, you need to specify the Truetype .ttf font files for each variant.
These should be defined in the array:
$this->fontdata = array(
	"trebuchet" => array(
		'R' => "trebuc.ttf",
		'B' => "trebucbd.ttf",
		'I' => "trebucit.ttf",
		'BI' => "trebucbi.ttf",
		)
)

This is the array which determines whether a font is available to mPDF. Each font-family must have a
Regular ['R'] file defined - the others (bold, italic, bold-italic) are optional.

mPDF will try to load the font-file. If you have defined _MPDF_SYSTEM_TTFONTS at the top of the 
config_fonts.php file, it will first look for the font-file there. This is useful if you are running 
mPDF on a computer which already has a folder with TTF fonts in (e.g. on Windows)

If the font-file is not there, or _MPDF_SYSTEM_TTFONTS is not defined, mPDF will look in the folder
/[your_path_to_mpdf]/ttfonts/

Note that the font-file names are case-sensitive and can contain capitals.

If the folder /ttfontdata/ is writeable (CHMOD 644 or 755), mPDF will save files there which it can 
re-use next time it accesses a particular font. This will significantly improve processing time
and is strongly recommended. 

mPDF should be able to read most TrueType Unicode font files with a .ttf extension
Truetype fonts with .ttf extension that are OpenType also work OK.
TrueType collections (.ttc) will also work if they contain TrueType Unicode fonts.


Character substitution
----------------------
Most people will have access to a Pan-Unicode font with most Unicode characters in it such as 
Arial Unicode MS. Set $this->backupSubsFont = array('arialunicodems'); at the top of the config_fonts.php file
to use this font when substituting any characters not found in the specific font being used.

Example:
You can set $mpdf->useSubstitutions = true; at runtime
or $this->useSubstitutions = true; in the config.php file

<p style="font-family: 'Comic Sans MS'">This text contains a Thai character &#3617; which does not exist
in the Comic Sans MS font file</p>

When useSubstitutions is true, mPDF will try to find substitutions for any missing characters:
1) firstly looks if the character is available in the inbuilt Symbols or ZapfDingbats fonts;
2) [If defined] looks in each of the the font(s) set by $this->backupSubsFont array

NB There is an increase in processing time when using substitutions, and even more so if
a backupSubsFont is defined.

Controlling mPDF mode
=====================
The first parameter of new mPDF('') works as follows:
new mPDF('c') - forces mPDF to only use the built-in [c]ore Adobe fonts (Helvetica, Times etc)

new mPDF('') - default - font subsetting behaviour is determined by the configurable variables
	$this->maxTTFFilesize and $this->percentSubset (see below)
	Default values are set so that: 1) very large font files are always subset
	2) Fonts are embedded as subsets if < 30% of the characters are used

new mPDF('..-x') - used together with a language or language/country code, this will cause
	mPDF to use only in-built core fonts (Helvetica, Times) if the language specified is appropiate; 
	otherwise it will force subsetting (equivalent to using "")
	e.g. new mPDF('de-x') or new mPDF('pt-BR-x') will use in-built core fonts
	and new mPDF('ru-x') will use subsets of any available TrueType fonts
	The languages that use core fonts are defined in config_cp.php (using the value $coreSuitable).

new mPDF('..+aCJK')  new mPDF('+aCJK')
new mPDF('..-aCJK')  new mPDF('-aCJK')
	 - used optionally together with a language or language/country code, +aCJK will force mPDF
	to use the Adobe non-embedded CJK fonts when a passage is marked with e.g. "lang: ja"
	This can be used at runtime to override the value set for $mpdf->useAdobeCJK in config.php
	Use in conjunction with settings in config_cp.php

For backwards compatibility, new mPDF('-s') and new mPDF('s') will force subsetting by 
	setting $this->percentSubset=100 (see below)
	new mPDF('utf-8-s') and new mPDF('ar-s') are also recognised

Language/Country (ll-cc)
------------------------
You can use a language code ('en') or language/country code ('en-GB') to control which 
mode/fonts are used. The behaviour is set up in config_cp.php file.
The default settings show some of the things you can do:
new mPDF('de') - as German is a Western European langauge, it is suitable to use the Adobe core fonts.
	Using 'de' alone will do nothing, but if you use ('de-x'), this will use core fonts.
new mPDF('th') - many fonts do not contain the characters necessary for Thai script. The value $unifonts 
	defines a restricted list of fonts available for mPDF to use.

NB <html dir="rtl"> or <body dir="rtl"> are supported.



Configuration variables changed
===============================
Configuration variables are documented in the on-line manual (http://mpdf1.com/manual/).


Font folders
============
If you wish to define your own font file folders (perhaps to share),
you can define the 2 constants in your script before including the mpdf.php script e.g.:

define('_MPDF_TTFONTPATH','your_path/ttfonts/'); 		
define('_MPDF_TTFONTDATAPATH','your_path/ttfontdata/'); 	// should be writeable

