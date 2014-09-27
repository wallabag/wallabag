<?php
/**
 * wallabag, self hostable application allowing you to not miss any content anymore
 *
 * @category   wallabag
 * @author     Nicolas Lœuillet <nicolas@loeuillet.org>
 * @copyright  2013
 * @license    http://opensource.org/licenses/MIT see COPYING file
 */

$app_name = 'wallabag';

$php_ok = (function_exists('version_compare') && version_compare(phpversion(), '5.3.3', '>='));
$pcre_ok = extension_loaded('pcre');
$zlib_ok = extension_loaded('zlib');
$mbstring_ok = extension_loaded('mbstring');
$iconv_ok = extension_loaded('iconv');
$tidy_ok = function_exists('tidy_parse_string');
$curl_ok = function_exists('curl_exec');
$parse_ini_ok = function_exists('parse_ini_file');
$parallel_ok = ((extension_loaded('http') && class_exists('HttpRequestPool')) || ($curl_ok && function_exists('curl_multi_init')));
$allow_url_fopen_ok = (bool)ini_get('allow_url_fopen');
$filter_ok = extension_loaded('filter');
$gettext_ok = function_exists("gettext");
$gd_ok = extension_loaded('gd');

if (extension_loaded('xmlreader')) {
	$xml_ok = true;
} elseif (extension_loaded('xml')) {
	$parser_check = xml_parser_create();
	xml_parse_into_struct($parser_check, '<foo>&amp;</foo>', $values);
	xml_parser_free($parser_check);
	$xml_ok = isset($values[0]['value']);
} else {
	$xml_ok = false;
}

header('Content-type: text/html; charset=UTF-8');

?><!DOCTYPE html>

<html lang="en">
<head>
<title><?php echo $app_name; ?>: Server Compatibility Test</title>

<style type="text/css">
body {
	font:14px/1.4em "Lucida Grande", Verdana, Arial, Helvetica, Clean, Sans, sans-serif;
	letter-spacing:0px;
	color:#333;
	margin:0;
	padding:0;
	background:#fff;
}

div#site {
	width:550px;
	margin:20px auto 0 auto;
}

a {
	color:#000;
	text-decoration:underline;
	padding:0 1px;
}

a:hover {
	color:#fff;
	background-color:#333;
	text-decoration:none;
	padding:0 1px;
}

p {
	margin:0;
	padding:5px 0;
}

em {
	font-style:normal;
	background-color:#ffc;
	padding: 0.1em 0;
}

ul, ol {
	margin:10px 0 10px 20px;
	padding:0 0 0 15px;
}

ul li, ol li {
	margin:0 0 7px 0;
	padding:0 0 0 3px;
}

h2 {
	font-size:18px;
	padding:0;
	margin:30px 0 20px 0;
}

h3 {
	font-size:16px;
	padding:0;
	margin:20px 0 5px 0;
}

h4 {
	font-size:14px;
	padding:0;
	margin:15px 0 5px 0;
}

code {
	font-size:1.1em;
	background-color:#f3f3ff;
	color:#000;
}

em strong {
    text-transform: uppercase;
}

table#chart {
	border-collapse:collapse;
}

table#chart th {
	background-color:#eee;
	padding:2px 3px;
	border:1px solid #fff;
}

table#chart td {
	text-align:center;
	padding:2px 3px;
	border:1px solid #eee;
}

table#chart tr.enabled td {
	/* Leave this alone */
}

table#chart tr.disabled td, 
table#chart tr.disabled td a {
}

table#chart tr.disabled td a {
	text-decoration:underline;
}

div.chunk {
	margin:20px 0 0 0;
	padding:0 0 10px 0;
	border-bottom:1px solid #ccc;
}

.footnote,
.footnote a {
	font:10px/12px verdana, sans-serif;
	color:#aaa;
}

.footnote em {
	background-color:transparent;
	font-style:italic;
}

.good{
background-color:#52CC5B;
}
.bad{
background-color:#F74343;
font-style:italic;
font-weight: bold;
}
.pass{
background-color:#FF9500;
}

</style>

</head>

<body>
<?php
$frominstall = false;
if (isset($_GET['from'])){
	if ($_GET['from'] == 'install'){
		$frominstall = true;
		}}
?>
<div id="site">
	<div id="content">

		<div class="chunk">
			<h2 style="text-align:center;"><?php echo $app_name; ?>: Compatibility Test</h2>
			<table cellpadding="0" cellspacing="0" border="0" width="100%" id="chart">
				<thead>
					<tr>
						<th>Test</th>
						<th>Should Be</th>
						<th>What You Have</th>
					</tr>
				</thead>
				<tbody>
					<tr class="<?php echo ($php_ok) ? 'enabled' : 'disabled'; ?>">
						<td>PHP</td>
						<td>5.3.3 or higher</td>
						<td class="<?php echo ($php_ok) ? 'good' : 'disabled'; ?>"><?php echo phpversion(); ?></td>
					</tr>
					<tr class="<?php echo ($xml_ok) ? 'enabled' : 'disabled'; ?>">
						<td><a href="http://php.net/xml">XML</a></td>
						<td>Enabled</td>
						<?php echo ($xml_ok) ? '<td class="good">Enabled, and sane</span>' : '<td class="bad">Disabled, or broken'; ?></td>
					</tr>
					<tr class="<?php echo ($pcre_ok) ? 'enabled' : 'disabled'; ?>">
						<td><a href="http://php.net/pcre">PCRE</a></td>
						<td>Enabled</td>
						<?php echo ($pcre_ok) ? '<td class="good">Enabled' : '<td class="bad">Disabled'; ?></td>
					</tr>
<!-- 					<tr class="<?php echo ($zlib_ok) ? 'enabled' : 'disabled'; ?>">
						<td><a href="http://php.net/zlib">Zlib</a></td>
						<td>Enabled</td>
						<?php echo ($zlib_ok) ? '<td class="good">Enabled' : '<td class="bad">Disabled'; ?></td>
					</tr> -->
<!-- 					<tr class="<?php echo ($mbstring_ok) ? 'enabled' : 'disabled'; ?>">
						<td><a href="http://php.net/mbstring">mbstring</a></td>
						<td>Enabled</td>
						<?php echo ($mbstring_ok) ? '<td class="good">Enabled' : '<td class="bad">Disabled'; ?></td>
					</tr> -->
<!-- 					<tr class="<?php echo ($iconv_ok) ? 'enabled' : 'disabled'; ?>">
						<td><a href="http://php.net/iconv">iconv</a></td>
						<td>Enabled</td>
						<?php echo ($iconv_ok) ? '<td class="good">Enabled' : '<td class="bad">Disabled'; ?></td>
					</tr> -->
					<tr class="<?php echo ($filter_ok) ? 'enabled' : 'disabled'; ?>">
						<td><a href="http://uk.php.net/manual/en/book.filter.php">Data filtering</a></td>
						<td>Enabled</td>
						<?php echo ($filter_ok) ? '<td class="good">Enabled' : '<td class="pass">Disabled'; ?></td>
					</tr>
					<tr class="<?php echo ($gd_ok) ? 'enabled' : 'disabled'; ?>">
						<td><a href="http://php.net/manual/en/book.image.php">GD</a></td>
						<td>Enabled</td>
						<?php echo ($gd_ok) ? '<td class="good">Enabled' : '<td class="pass">Disabled'; ?></td>
					</tr>					
					<tr class="<?php echo ($tidy_ok) ? 'enabled' : 'disabled'; ?>">
						<td><a href="http://php.net/tidy">Tidy</a></td>
						<td>Enabled</td>
						<?php echo ($tidy_ok) ? '<td class="good">Enabled' : '<td class="pass">Disabled'; ?></td>
					</tr>
					<tr class="<?php echo ($curl_ok) ? 'enabled' : 'disabled'; ?>">
						<td><a href="http://php.net/curl">cURL</a></td>
						<td>Enabled</td>
						<?php echo (extension_loaded('curl')) ? '<td class="good">Enabled' : '<td class="pass">Disabled'; ?></td>
					</tr>
                    <tr class="<?php echo ($parse_ini_ok) ? 'enabled' : 'disabled'; ?>">
                        <td><a href="http://uk.php.net/manual/en/function.parse-ini-file.php">Parse ini file</td>
                        <td>Enabled</td>
                        <?php echo ($parse_ini_ok) ? '<td class="good">Enabled' : '<td class="bad">Disabled'; ?></td>
                    </tr>
					<tr class="<?php echo ($parallel_ok) ? 'enabled' : 'disabled'; ?>">
						<td>Parallel URL fetching</td>
						<td>Enabled</td>
						<?php echo ($parallel_ok) ? '<td class="good">Enabled' : '<td class="pass">Disabled'; ?></td>
					</tr>
					<tr class="<?php echo ($allow_url_fopen_ok) ? 'enabled' : 'disabled'; ?>">
						<td><a href="http://www.php.net/manual/en/filesystem.configuration.php#ini.allow-url-fopen">allow_url_fopen</a></td>
						<td>Enabled</td>
						<?php echo ($allow_url_fopen_ok) ? '<td class="good">Enabled' : '<td class="bad">Disabled'; ?></td>
					</tr>
					<tr class="<?php echo ($gettext_ok) ? 'enabled' : 'disabled'; ?>">
						<td><a href="http://php.net/manual/en/book.gettext.php">gettext</a></td>
						<td>Enabled</td>
						<?php echo ($gettext_ok) ? '<td class="good">Enabled' : '<td class="bad">Disabled'; ?></td>
					</tr>
				</tbody>
			</table>
		</div>

		<div class="chunk">
			<h3>What does this mean?</h3>
			<ol>
				<?php //if ($php_ok && $xml_ok && $pcre_ok && $mbstring_ok && $iconv_ok && $filter_ok && $zlib_ok && $tidy_ok && $curl_ok && $parallel_ok && $allow_url_fopen_ok): ?>
				<?php if ($php_ok && $xml_ok && $pcre_ok && $filter_ok && $gd_ok && $tidy_ok && $curl_ok && $parallel_ok && $allow_url_fopen_ok && $gettext_ok && $parse_ini_ok): ?>
				<li><em>You have everything you need to run <?php echo $app_name; ?> properly!  Congratulations!</em></li>
				<?php else: ?>
					<?php if ($php_ok): ?>
						<li><strong>PHP:</strong> You are running a supported version of PHP.  <em>No problems here.</em></li>
						<?php if ($xml_ok): ?>
							<li><strong>XML:</strong> You have XMLReader support or a version of XML support that isn't broken installed.  <em>No problems here.</em></li>
							<?php if ($pcre_ok): ?>
								<li><strong>PCRE:</strong> You have PCRE support installed. <em>No problems here.</em></li>
								
								<?php if ($allow_url_fopen_ok): ?>
									<li><strong>allow_url_fopen:</strong> You have allow_url_fopen enabled. <em>No problems here.</em></li>
									
									<?php if ($gettext_ok): ?>
										<li><strong>Gettext:</strong> You have <code>gettext</code> enabled. <em>No problems here.</em></li>
                                        
                                        <?php if ($parse_ini_ok): ?>
                                        
                                            <?php if ($filter_ok): ?>
                                                <li><strong>Data filtering:</strong> You have the PHP filter extension enabled. <em>No problems here.</em></li>
        
                                                <?php if ($zlib_ok): ?>
                                                    <li><strong>Zlib:</strong> You have <code>Zlib</code> enabled.  This allows SimplePie to support GZIP-encoded feeds.  <em>No problems here.</em></li>
                                                <?php else: ?>
                                                    <li><strong>Zlib:</strong> The <code>Zlib</code> extension is not available.  SimplePie will ignore any GZIP-encoding, and instead handle feeds as uncompressed text.</li>
                                                <?php endif; ?>
                                                
                                                <?php if ($mbstring_ok && $iconv_ok): ?>
                                                    <li><strong>mbstring and iconv:</strong> You have both <code>mbstring</code> and <code>iconv</code> installed!  This will allow <?php echo $app_name; ?> to handle the greatest number of languages. <em>No problems here.</em></li>
                                                <?php elseif ($mbstring_ok): ?>
                                                    <li><strong>mbstring:</strong> <code>mbstring</code> is installed, but <code>iconv</code> is not.</li>
                                                <?php elseif ($iconv_ok): ?>
                                                    <li><strong>iconv:</strong> <code>iconv</code> is installed, but <code>mbstring</code> is not.</li>
                                                <?php else: ?>
                                                    <li><strong>mbstring and iconv:</strong> <em>You do not have either of the extensions installed.</em> This will significantly impair your ability to read non-English feeds, as well as even some English ones.</li>
                                                <?php endif; ?>

                                                <?php if ($gd_ok): ?>
                                                    <li><strong>GD:</strong> You have <code>GD</code> support installed.  <em>No problems here.</em></li>
                                                <?php else: ?>
                                                    <li><strong>GD:</strong> The <code>GD</code> extension is not available.  <?php echo $app_name; ?> will not be able to download pictures locally on your server.</li>
                                                <?php endif; ?>

                                                <?php if ($tidy_ok): ?>
                                                    <li><strong>Tidy:</strong> You have <code>Tidy</code> support installed.  <em>No problems here.</em></li>
                                                <?php else: ?>
                                                    <li><strong>Tidy:</strong> The <code>Tidy</code> extension is not available.  <?php echo $app_name; ?> should still work with most feeds, but you may experience problems with some.</li>
                                                <?php endif; ?>
                                            
                                                <?php if ($curl_ok): ?>
                                                    <li><strong>cURL:</strong> You have <code>cURL</code> support installed.  <em>No problems here.</em></li>
                                                <?php else: ?>
                                                    <li><strong>cURL:</strong> The <code>cURL</code> extension is not available.  SimplePie will use <code>fsockopen()</code> instead.</li>
                                                <?php endif; ?>
                
                                                <?php if ($parallel_ok): ?>
                                                    <li><strong>Parallel URL fetching:</strong> You have <code>HttpRequestPool</code> or <code>curl_multi</code> support installed.  <em>No problems here.</em></li>
                                                <?php else: ?>
                                                    <li><strong>Parallel URL fetching:</strong> <code>HttpRequestPool</code> or <code>curl_multi</code> support is not available.  <?php echo $app_name; ?> will use <code>file_get_contents()</code> instead to fetch URLs sequentially rather than in parallel.</li>
                                                <?php endif; ?>

                                            <?php else: ?>
                                                <li><strong>Data filtering:</strong> Your PHP configuration has the filter extension disabled.  <strong><?php echo $app_name; ?> will not work here.</strong></li>
                                            <?php endif; ?>
                                        
                                        <?php else : ?>
                                            <li><strong>Parse ini files function :</strong> Bad luck : your webhost has decided to block the use of the <em>parse_ini_file</em> function. <strong><?php echo $app_name; ?> will not work here.</strong>
                                        <?php endif; ?>
										
									<?php else: ?>
										<li><strong>GetText:</strong> The <code>gettext</code> extension is not available. The system we use to display wallabag in various languages is not available. <strong><?php echo $app_name; ?> will not work here.</strong></li>
									<?php endif; ?>
										
								<?php else: ?>
									<li><strong>allow_url_fopen:</strong> Your PHP configuration has allow_url_fopen disabled.  <strong><?php echo $app_name; ?> will not work here.</strong></li>
								<?php endif; ?>
									
							<?php else: ?>
								<li><strong>PCRE:</strong> Your PHP installation doesn't support Perl-Compatible Regular Expressions.  <strong><?php echo $app_name; ?> will not work here.</strong></li>
							<?php endif; ?>
						<?php else: ?>
							<li><strong>XML:</strong> Your PHP installation doesn't support XML parsing.  <strong><?php echo $app_name; ?> will not work here.</strong></li>
						<?php endif; ?>
					<?php else: ?>
						<li><strong>PHP:</strong> You are running an unsupported version of PHP.  <strong><?php echo $app_name; ?> will not work here.</strong></li>
					<?php endif; ?>
				<?php endif; ?>
			</ol>
		</div>

		<div class="chunk">
			<?php //if ($php_ok && $xml_ok && $pcre_ok && $mbstring_ok && $iconv_ok && $filter_ok && $allow_url_fopen_ok) { ?>
			<?php if ($php_ok && $xml_ok && $pcre_ok && $filter_ok && $allow_url_fopen_ok && $gettext_ok && $parse_ini_ok) { ?>
				<h3>Bottom Line: Yes, you can!</h3>
				<p><em>Your webhost has its act together!</em></p>
				<?php if (!$frominstall) { ?>
				<p>You can download the latest version of <?php echo $app_name; ?> from <a href="http://wallabag.org/download">wallabag.org</a>.</p>
				<p>If you already have done that, you should access <a href="index.php">the index.php file</a> of your installation to configure and/or start using wallabag</p>
				<?php } else { ?>
				<p>You can now <a href="index.php">return to the installation section</a>.</p>
				<?php } ?>
				<p><strong>Note</strong>: Passing this test does not guarantee that <?php echo $app_name; ?> will run on your webhost &mdash; it only ensures that the basic requirements have been addressed. If you experience any problems, please let us know.</p>
			<?php //} else if ($php_ok && $xml_ok && $pcre_ok && $mbstring_ok && $allow_url_fopen_ok && $filter_ok) { ?>
			<?php } else if ($php_ok && $xml_ok && $pcre_ok && $allow_url_fopen_ok && $filter_ok && $gettext_ok && $parse_ini_ok) { ?>
				<h3>Bottom Line: Yes, you can!</h3>
				<p><em>For most feeds, it'll run with no problems.</em> There are certain languages that you might have a hard time with though.</p>
				<?php if (!$frominstall) { ?>
				<p>You can download the latest version of <?php echo $app_name; ?> from <a href="http://wallabag.org/download">wallabag.org</a>.</p>
				<p>If you already have done that, you should access <a href="index.php">the index.php file</a> of your installation to configure and/or start using wallabag</p>
				<?php } else { ?>
				<p>You can now <a href="index.php">return to the installation section</a>.</p>
				<?php } ?>
				<p><strong>Note</strong>: Passing this test does not guarantee that <?php echo $app_name; ?> will run on your webhost &mdash; it only ensures that the basic requirements have been addressed. If you experience any problems, please let us know.</p>
			<?php } else { ?>
				<h3>Bottom Line: We're sorry…</h3>
				<p><em>Your webhost does not support the minimum requirements for <?php echo $app_name; ?>.</em>  It may be a good idea to contact your webhost and point them to the results of this test. They may be able to enable/install the required components.</p>
			<?php } ?>
		</div>

		<div class="chunk">
			<p class="footnote">This compatibility test has been borrowed (and slightly adapted by <a href="http://fivefilters.org/content-only/">fivefilters.org</a>) from the one supplied by <a href="http://simplepie.org/">SimplePie.org</a>.</a></p>
		</div>

	</div>

</div>

</body>
</html>