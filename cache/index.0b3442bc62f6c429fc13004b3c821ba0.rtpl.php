<?php if(!class_exists('raintpl')){exit;}?><html>
	<head>
		<link rel='stylesheet' href='./css/reset.css' type='text/css' media='all' />
		<link rel='stylesheet' href='./css/typography.css' type='text/css' media='all' />
	
		<title><?php echo $title;?></title>
	</head>
	<body>
		<article>
			<h1><a href="<?php echo $url;?>"><?php echo $title;?></a></h1>
			<div id="readityourselfcontent">
				<?php echo $content;?>

			</div>
			<span class="comeFrom">Come From : <a href="<?php echo $url;?>"><?php echo $url;?></a>
		</article>
		<?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("footer") . ( substr("footer",-1,1) != "/" ? "/" : "" ) . basename("footer") );?>

	</body>
</html>
