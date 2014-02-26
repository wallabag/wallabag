<?php

// this script compile all twig templates and put it in cahce to get Poedit (or xgettext) to extract phrases fron chached templates.

// gettext command line tools:
// msgunfmt - get po from mo
// msgfmt - get mo from po
// xgettext - extract phrases from files


		$siteRoot = dirname(__FILE__) . '/../..';

		require_once  $siteRoot . '/vendor/twig/twig/lib/Twig/Autoloader.php';
		Twig_Autoloader::register();

		require_once  $siteRoot . '/vendor/twig/extensions/lib/Twig/Extensions/Autoloader.php';
		Twig_Extensions_Autoloader::register();

		//$tplDir = $siteRoot.'/themes/default';
		$tplDirRoot = $siteRoot.'/themes/';
		$tmpDir =  $siteRoot. '/cache/';

		foreach (new IteratorIterator(new DirectoryIterator($tplDirRoot)) as $tplDir) {

			if ($tplDir->isDir() and $tplDir!='.' and $tplDir!='..') {
				echo "\n$tplDir\n";

				$loader = new Twig_Loader_Filesystem($tplDirRoot.$tplDir);

				// force auto-reload to always have the latest version of the template
				$twig = new Twig_Environment($loader, array(
						'cache' => $tmpDir,
						'auto_reload' => true
				));

				$twig->addExtension(new Twig_Extensions_Extension_I18n());

				$filter = new Twig_SimpleFilter('getDomain', 'Tools::getDomain');
				$twig->addFilter($filter);

				$filter = new Twig_SimpleFilter('getReadingTime', 'Tools::getReadingTime');
				$twig->addFilter($filter);

				$filter = new Twig_SimpleFilter('getPrettyFilename', function($string) { return str_replace($siteRoot, '', $string); });
				$twig->addFilter($filter);

// 				// iterate over all your templates
				foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($tplDirRoot.$tplDir), RecursiveIteratorIterator::LEAVES_ONLY) as $file) {
						// force compilation
					if ($file->isFile() and pathinfo($file, PATHINFO_EXTENSION)=='twig') {
							echo "\t$file\n";
							$twig->loadTemplate(str_replace($tplDirRoot.$tplDir.'/', '', $file));
					}
				}

			}

		}

