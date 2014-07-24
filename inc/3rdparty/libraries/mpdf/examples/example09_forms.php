<?php




define('_MPDF_PATH','../');
include("../mpdf.php");


$html = '
<form>

<b>Textarea</b>
<textarea name="authors" rows="5" cols="80" wrap="virtual">Quisque viverra. Etiam id libero at magna pellentesque aliquet. Nulla sit amet ipsum id enim tempus dictum. Quisque viverra. Etiam id libero at magna pellentesque aliquet. Nulla sit amet ipsum id enim tempus dictum. Quisque viverra. Etiam id libero at magna pellentesque aliquet. Nulla sit amet ipsum id enim tempus dictum. Quisque viverra. Etiam id libero at magna pellentesque aliquet. Nulla sit amet ipsum id enim tempus dictum. Quisque viverra. Etiam id libero at magna pellentesque aliquet. Nulla sit amet ipsum id enim tempus dictum. Quisque viverra. Etiam id libero at magna pellentesque aliquet. Nulla sit amet ipsum id enim tempus dictum. Quisque viverra. Etiam id libero at magna pellentesque aliquet. Nulla sit amet ipsum id enim tempus dictum. Quisque viverra. Etiam id libero at magna pellentesque aliquet. Nulla sit amet ipsum id enim tempus dictum. </textarea>
<br /><br />

<b>Select</b>
<select size="1" name="status"><option value="A">Active</option><option value="W" >New item from auto_manager: pending validation</option><option value="I" selected="selected">Incomplete record - pending</option><option value="X" >Flagged for Deletion</option> </select> followed by text
<br /><br />



<b>Input Radio</b>
<input type="radio" name="pre_publication" value="0" checked="checked" > No &nbsp;&nbsp;&nbsp;&nbsp; <input type="radio" name="pre_publication" value="1" > Yes 
<br /><br />


<b>Input Radio</b>
<input type="radio" name="recommended" value="0" > No &nbsp;&nbsp;&nbsp;&nbsp; <input type="radio" name="recommended" value="1" > Keep &nbsp;&nbsp;&nbsp;&nbsp; <input type="radio" name="recommended" value="2"  checked="checked" > Choice 
<br /><br />


<b>Input Text</b>
<input type="text" size="190" name="doi" value="10.1258/jrsm.100.5.211"> 
<br /><br />

<b>Input Password</b>
<input type="password" size="40" name="password" value="secret"> 
<br /><br />


<input type="checkbox" name="QPC" value="ON" /> Checkboxes<br>
<input type="checkbox" name="QPA" value="ON" disabled="disabled" /> Disabled<br>
<input type="checkbox" name="QLY" value="ON" checked="checked" /> Selected
<br /><br />

<input type="submit" name="submit" value="Submit" /><br /><br />

</form>

<hr style="width:80%;" />

<div>
<form>

<b>Textarea</b>
<textarea name="authors" rows="5" cols="50" wrap="virtual">Quisque viverra. Etiam id libero at magna pellentesque aliquet. Nulla sit amet ipsum id enim tempus dictum. Quisque viverra. Etiam id libero at magna pellentesque aliquet. Nulla sit amet ipsum id enim tempus dictum. Quisque viverra. Etiam id libero at magna pellentesque aliquet. Nulla sit amet ipsum id enim tempus dictum. Quisque viverra. Etiam id libero at magna pellentesque aliquet. Nulla sit amet ipsum id enim tempus dictum. Quisque viverra. Etiam id libero at magna pellentesque aliquet. Nulla sit amet ipsum id enim tempus dictum. Quisque viverra. Etiam id libero at magna pellentesque aliquet. Nulla sit amet ipsum id enim tempus dictum. Quisque viverra. Etiam id libero at magna pellentesque aliquet. Nulla sit amet ipsum id enim tempus dictum. Quisque viverra. Etiam id libero at magna pellentesque aliquet. Nulla sit amet ipsum id enim tempus dictum. </textarea>
<br /><br />



<b>Select</b>
<select size="1" name="status"><option value="A">Active</option><option value="W" >New item from auto_manager: pending validation</option><option value="I" selected="selected">Incomplete record - pending</option><option value="X" >Flagged for Deletion</option> </select>
<br /><br />

<b>Input Radio</b>
<input type="radio" name="pre_publication" value="0" checked="checked" > No &nbsp;&nbsp;&nbsp;&nbsp; <input type="radio" name="pre_publication" value="1" > Yes 
<br /><br />


<b>Input Radio</b>
<input type="radio" name="recommended" value="0" > No &nbsp;&nbsp;&nbsp;&nbsp; <input type="radio" name="recommended" value="1" > Keep &nbsp;&nbsp;&nbsp;&nbsp; <input type="radio" name="recommended" value="2"  checked="checked" > Choice 
<br /><br />


<b>Input Text</b>
<input type="text" size="40" name="doi" value="10.1258/jrsm.100.5.211" /> 
<br />

<b>Input Password</b>
<input type="password" size="40" name="password" value="secret"> 
<br /><br />

<input type="checkbox" name="QPC" value="ON" /> Checkboxes<br>
<input type="checkbox" name="QPA" value="ON" disabled="disabled" /> Disabled<br>
<input type="checkbox" name="QLY" value="ON" checked="checked" /> Selected
<br /><br />

<input type="submit" name="submit" value="Submit" /><br /><br />

</form>

</div>
<hr style="width:100%;" />


<form>

<table border="1" style="padding:2px;" >

<tr><td valign="top" align="right"><b>Textarea</b></td><td><textarea name="title" rows="5" cols="50" wrap="virtual">TEXTINTABLE Quisque viverra. Etiam id libero at magna pellentesque aliquet. Nulla sit amet ipsum id enim tempus dictum. Quisque viverra. Etiam id libero at magna pellentesque aliquet. Nulla sit amet ipsum id enim tempus dictum. Quisque viverra. Etiam id libero at magna pellentesque aliquet. Nulla sit amet ipsum id enim tempus dictum. Quisque viverra. Etiam id libero at magna pellentesque aliquet. Nulla sit amet ipsum id enim tempus dictum. Quisque viverra. Etiam id libero at magna pellentesque aliquet. Nulla sit amet ipsum id enim tempus dictum. Quisque viverra. Etiam id libero at magna pellentesque aliquet. Nulla sit amet ipsum id enim tempus dictum. Quisque viverra. Etiam id libero at magna pellentesque aliquet. Nulla sit amet ipsum id enim tempus dictum. Quisque viverra. Etiam id libero at magna pellentesque aliquet. Nulla sit amet ipsum id enim tempus dictum. </textarea></td></tr>




<tr><td valign="top" align="right"><b>Select</b></td><td><select size="1" name="status"><option value="A">Active</option><option value="W" >New item from auto_manager: pending validation</option><option value="I" selected="selected">Incomplete record - pending</option><option value="X" >Flagged for Deletion</option> </select> <input type="hidden" name="old_status" value="A" /> </td></tr>

<tr><td valign="bottom" align="right"><b>Input Radio</b></td><td><input type="radio" name="pre_publication" value="0"  checked > No &nbsp;&nbsp;&nbsp;&nbsp; <input type="radio" name="pre_publication" value="1" > Yes </td></tr>


<tr><td valign="top" align="right"><b>Input Radio</b></td><td><input type="radio" name="recommended" value="0" > No &nbsp;&nbsp;&nbsp;&nbsp; <input type="radio" name="recommended" value="1" > Keep &nbsp;&nbsp;&nbsp;&nbsp; <input type="radio" name="recommended" value="2"  checked="checked" > Choice </td></tr>

<tr><td valign="top" align="right"><b>Input Text</b></td><td><input type="text" size="40" name="doi" value="10.1258/jrsm.100.5.211"> </td>
</tr>
</table>

<br />
<table border="1" style="padding:8px;">

<tr><td valign="top" align="right"><b>Checkbox</b></td><td></td></tr>

<tr><td><input type="checkbox" name="QPC" value="ON" > Checkboxes<br></td><td><input type="checkbox" name="QSC" value="ON" > Gardening</td></tr>

<tr><td><input type="checkbox" name="QPA" value="ON" > Holidays<br></td><td><input type="checkbox" name="QPD" value="ON" > Motoring<br></td></tr>

<tr><td><input type="checkbox" name="QLY" value="ON" checked="checked" > Books</td><td><input type="checkbox" name="QCA" value="ON" > Theatre</td></tr>

<tr><td><input type="checkbox" name="QNU" value="ON" checked="checked" > Selected option</td><td><input type="checkbox" name="QET" value="ON" > Musicals</td></tr><tr><td><input type="checkbox" name="QBE" value="ON" > Eating out</td><td><input type="checkbox" name="QPY" value="ON" > Events</td></tr>



<tr><td>
<input type="submit" name="submit" value="Submit" /><br />
</td><td></td></tr></table>
</form>


<form>

<b>Input Radio</b>
<input type="radio" name="pre_publication" value="0" checked="checked" > No &nbsp;&nbsp;&nbsp;&nbsp; <input type="radio" name="pre_publication" value="1" > Yes  

<br /><br />

<input type="hidden" name="doi" value="10.1258/jrsm.100.5.211" /> 

<b>Input Radio</b>
<input type="radio" name="recommended" value="0" > No &nbsp;&nbsp;&nbsp;&nbsp; <input type="radio" name="recommended" value="1" > Keep &nbsp;&nbsp;&nbsp;&nbsp; <input type="radio" name="recommended" value="2"  checked="checked" > Choice   

<br /><br />


<b>Input Text</b><br />

Input text: <input type="text" size="40" name="doi" value="10.1258/jrsm.100.5.211" /> <br />

Password: <input type="password" size="40" name="doi" value="pallcare" /> 
<br /><br />

Checkboxes: <br />
<input type="checkbox" name="QPC" value="ON" /> Checkboxes<br>
<input type="checkbox" name="QPA" value="ON" disabled="disabled" /> Disabled<br>
<input type="checkbox" name="QLY" value="ON" checked="checked" /> Selected
<br /><br />

<input type="image" name="submit" src="goto.gif" />
<input type="button" name="submit" value="Button" />
<input type="reset" name="submit" value="Reset" />
<input type="submit" name="submit" value="Submit" /> 
</form>

';

//==============================================================
//==============================================================
//==============================================================
if (isset($_REQUEST['html'])) { echo '<html><head><style>'.file_get_contents('mpdfstyletables.css').'</style></head><body>'.$html.'</body></html>'; exit; }
if (isset($_REQUEST['source'])) { 
	$file = __FILE__;
	header("Content-Type: text/plain");
	header("Content-Length: ". filesize($file));
	header("Content-Disposition: attachment; filename='".$file."'");
	readfile($file);
	exit; 
}
//==============================================================
//==============================================================
//==============================================================

$mpdf=new mPDF('c'); 

// LOAD a stylesheet
$stylesheet = file_get_contents('mpdfstyletables.css');
$mpdf->WriteHTML($stylesheet,1);	// The parameter 1 tells that this is css/style only and no body/html/text

$mpdf->SetColumns(2,'J');

$mpdf->WriteHTML($html);

$mpdf->Output();
exit;


?>