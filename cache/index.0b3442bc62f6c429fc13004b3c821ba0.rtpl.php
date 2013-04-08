<?php if(!class_exists('raintpl')){exit;}?><!DOCTYPE html>
<!--[if lte IE 6]> <html class="no-js ie6 ie67 ie678" lang="en"> <![endif]-->
<!--[if lte IE 7]> <html class="no-js ie7 ie67 ie678" lang="en"> <![endif]-->
<!--[if IE 8]> <html class="no-js ie8 ie678" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<html>
    <head>
        <meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0">
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=10">
        <link rel="stylesheet" href="./css/knacss.css" media="all">
        <link rel="stylesheet" href="./css/style.css" media="all">
        <title><?php echo $title;?></title>
    </head>
    <body class="article">
        <div id="article" class="w600p">
        	<div id="backhome">
        		<a href="index.php" title="back to home">&larr;</a>
        	</div>
            <header class="mbm">
                <h1><a href="<?php echo $url;?>"><?php echo $title;?></a></h1>
                <div class="vieworiginal txtright small"><a href="<?php echo $url;?>" target="_blank">view original</a></div>
            </header>
            <article>
                <div id="readityourselfcontent">
                    <?php echo $content;?>
                </div>
            </article>
        </div>
        <?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("footer") . ( substr("footer",-1,1) != "/" ? "/" : "" ) . basename("footer") );?>
    </body>
</html>
