<?php
/**
 * ePub NCX file structure
 *
 * @author A. Grandt <php@grandt.com>
 * @copyright 2009-2014 A. Grandt
 * @license GNU LGPL, Attribution required for commercial implementations, requested for everything else.
 * @version 3.20
 */
class Ncx {
    const _VERSION = 3.20;

    const MIMETYPE = "application/x-dtbncx+xml";

    private $bookVersion = EPub::BOOK_VERSION_EPUB2;

    private $navMap = NULL;
    private $uid = NULL;
    private $meta = array();
    private $docTitle = NULL;
    private $docAuthor = NULL;

    private $currentLevel = NULL;
    private $lastLevel = NULL;

    private $languageCode = "en";
    private $writingDirection = EPub::DIRECTION_LEFT_TO_RIGHT;

    public $chapterList = array();
    public $referencesTitle = "Guide";
    public $referencesClass = "references";
	public $referencesId = "references";
	public $referencesList = array();
    public $referencesName = array();
    public $referencesOrder = NULL;

    /**
     * Class constructor.
	 *
	 * @param string $uid
	 * @param string $docTitle
	 * @param string $docAuthor
	 * @param string $languageCode
	 * @param string $writingDirection
	 */
    function __construct($uid = NULL, $docTitle = NULL, $docAuthor = NULL, $languageCode = "en", $writingDirection = EPub::DIRECTION_LEFT_TO_RIGHT) {
        $this->navMap = new NavMap($writingDirection);
        $this->currentLevel = $this->navMap;
        $this->setUid($uid);
        $this->setDocTitle($docTitle);
        $this->setDocAuthor($docAuthor);
		$this->setLanguageCode($languageCode);
		$this->setWritingDirection($writingDirection);
    }

    /**
     * Class destructor
     *
     * @return void
     */
    function __destruct() {
        unset($this->bookVersion, $this->navMap, $this->uid, $this->meta);
        unset($this->docTitle, $this->docAuthor, $this->currentLevel, $this->lastLevel);
		unset($this->languageCode, $this->writingDirection, $this->chapterList, $this->referencesTitle);
		unset($this->referencesClass, $this->referencesId, $this->referencesList, $this->referencesName);
		unset($this->referencesOrder);
	}

    /**
     *
     * Enter description here ...
     *
     * @param string $bookVersion
     */
    function setVersion($bookVersion) {
        $this->bookVersion = is_string($bookVersion) ? trim($bookVersion) : EPub::BOOK_VERSION_EPUB2;
    }

	/**
	 *
	 * @return bool TRUE if the book is set to type ePub 2
	 */
    function isEPubVersion2() {
        return $this->bookVersion === EPub::BOOK_VERSION_EPUB2;
   }

    /**
     *
     * Enter description here ...
     *
     * @param string $uid
     */
    function setUid($uid) {
        $this->uid = is_string($uid) ? trim($uid) : NULL;
    }

    /**
     *
     * Enter description here ...
     *
     * @param string $docTitle
     */
    function setDocTitle($docTitle) {
        $this->docTitle = is_string($docTitle) ? trim($docTitle) : NULL;
    }

    /**
     *
     * Enter description here ...
     *
     * @param string $docAuthor
     */
    function setDocAuthor($docAuthor) {
        $this->docAuthor = is_string($docAuthor) ? trim($docAuthor) : NULL;
    }

    /**
     *
     * Enter description here ...
     *
     * @param string $languageCode
     */
    function setLanguageCode($languageCode) {
        $this->languageCode = is_string($languageCode) ? trim($languageCode) : "en";
    }

    /**
     *
     * Enter description here ...
     *
     * @param string $writingDirection
     */
    function setWritingDirection($writingDirection) {
        $this->writingDirection = is_string($writingDirection) ? trim($writingDirection) : EPub::DIRECTION_LEFT_TO_RIGHT;
    }

    /**
     *
     * Enter description here ...
     *
     * @param NavMap $navMap
     */
    function setNavMap($navMap) {
        if ($navMap != NULL && is_object($navMap) && get_class($navMap) === "NavMap") {
            $this->navMap = $navMap;
        }
    }

    /**
     * Add one chapter level.
     *
     * Subsequent chapters will be added to this level.
	 *
	 * @param string $navTitle
	 * @param string $navId
	 * @param string $navClass
	 * @param string $isNavHidden
	 * @param string $writingDirection
	 * @return NavPoint
	 */
    function subLevel($navTitle = NULL, $navId = NULL, $navClass = NULL, $isNavHidden = FALSE, $writingDirection = NULL) {
		$navPoint = FALSE;
		if (isset($navTitle) && isset($navClass)) {
			$navPoint = new NavPoint($navTitle, NULL, $navId, $navClass, $isNavHidden, $writingDirection);
			$this->addNavPoint($navPoint);
		}
        if ($this->lastLevel !== NULL) {
            $this->currentLevel = $this->lastLevel;
        }
		return $navPoint;
    }

    /**
     * Step back one chapter level.
     *
     * Subsequent chapters will be added to this chapters parent level.
     */
    function backLevel() {
        $this->lastLevel = $this->currentLevel;
        $this->currentLevel = $this->currentLevel->getParent();
    }

    /**
     * Step back to the root level.
     *
     * Subsequent chapters will be added to the rooot NavMap.
     */
    function rootLevel() {
        $this->lastLevel = $this->currentLevel;
        $this->currentLevel = $this->navMap;
    }

    /**
     * Step back to the given level.
     * Useful for returning to a previous level from deep within the structure.
     * Values below 2 will have the same effect as rootLevel()
     *
     * @param int $newLevel
     */
    function setCurrentLevel($newLevel) {
        if ($newLevel <= 1) {
            $this->rootLevel();
        } else {
            while ($this->currentLevel->getLevel() > $newLevel) {
                $this->backLevel();
            }
        }
    }

    /**
     * Get current level count.
     * The indentation of the current structure point.
     *
     * @return current level count;
     */
    function getCurrentLevel() {
        return $this->currentLevel->getLevel();
    }

    /**
     * Add child NavPoints to current level.
     *
     * @param NavPoint $navPoint
     */
    function addNavPoint($navPoint) {
        $this->lastLevel = $this->currentLevel->addNavPoint($navPoint);
    }

    /**
     *
     * Enter description here ...
     *
     * @return NavMap
     */
    function getNavMap() {
        return $this->navMap;
    }

    /**
     *
     * Enter description here ...
     *
     * @param string $name
     * @param string $content
     */
    function addMetaEntry($name, $content) {
        $name = is_string($name) ? trim($name) : NULL;
        $content = is_string($content) ? trim($content) : NULL;

        if ($name != NULL && $content != NULL) {
            $this->meta[] = array($name => $content);
        }
    }

    /**
     *
     * Enter description here ...
     *
     * @return string
     */
    function finalize() {
        $nav = $this->navMap->finalize();

        $ncx = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        if ($this->isEPubVersion2()) {
            $ncx .= "<!DOCTYPE ncx PUBLIC \"-//NISO//DTD ncx 2005-1//EN\"\n"
            . "  \"http://www.daisy.org/z3986/2005/ncx-2005-1.dtd\">\n";
        }
        $ncx .= "<ncx xmlns=\"http://www.daisy.org/z3986/2005/ncx/\" version=\"2005-1\" xml:lang=\"" . $this->languageCode . "\" dir=\"" . $this->writingDirection . "\">\n"
        . "\t<head>\n"
        . "\t\t<meta name=\"dtb:uid\" content=\"" . $this->uid . "\" />\n"
        . "\t\t<meta name=\"dtb:depth\" content=\"" . $this->navMap->getNavLevels() . "\" />\n"
        . "\t\t<meta name=\"dtb:totalPageCount\" content=\"0\" />\n"
        . "\t\t<meta name=\"dtb:maxPageNumber\" content=\"0\" />\n";

        if (sizeof($this->meta)) {
            foreach ($this->meta as $metaEntry) {
                list($name, $content) = each($metaEntry);
                $ncx .= "\t\t<meta name=\"" . $name . "\" content=\"" . $content . "\" />\n";
            }
        }

        $ncx .= "\t</head>\n\n\t<docTitle>\n\t\t<text>"
        . $this->docTitle
        . "</text>\n\t</docTitle>\n\n\t<docAuthor>\n\t\t<text>"
        . $this->docAuthor
        . "</text>\n\t</docAuthor>\n\n"
        . $nav;

        return $ncx . "</ncx>\n";
    }

	/**
	 *
	 * @param string $title
	 * @param string $cssFileName
	 * @return string
	 */
    function finalizeEPub3($title = "Table of Contents", $cssFileName = NULL) {
		$end = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"
            . "<html xmlns=\"http://www.w3.org/1999/xhtml\"\n"
            . "      xmlns:epub=\"http://www.idpf.org/2007/ops\"\n"
            . "      xml:lang=\"" . $this->languageCode . "\" lang=\"" . $this->languageCode . "\" dir=\"" . $this->writingDirection . "\">\n"
            . "\t<head>\n"
            . "\t\t<title>" . $this->docTitle . "</title>\n"
            . "\t\t<meta http-equiv=\"default-style\" content=\"text/html; charset=utf-8\"/>\n";
        if ($cssFileName !== NULL) {
            $end .= "\t\t<link rel=\"stylesheet\" href=\"" . $cssFileName . "\" type=\"text/css\"/>\n";
        }
        $end .= "\t</head>\n"
            . "\t<body epub:type=\"frontmatter toc\">\n"
            . "\t\t<header>\n"
            . "\t\t\t<h1>" . $title . "</h1>\n"
            . "\t\t</header>\n"
            . $this->navMap->finalizeEPub3()
            . $this->finalizeEPub3Landmarks()
            . "\t</body>\n"
            . "</html>\n";

        return $end;
    }

	/**
	 * Build the references for the ePub 2 toc.
	 * These are merely reference pages added to the end of the navMap though.
	 *
	 * @return string
	 */
	function finalizeReferences() {
		if (isset($this->referencesList) && sizeof($this->referencesList) > 0) {
			$this->rootLevel();
			$this->subLevel($this->referencesTitle, $this->referencesId, $this->referencesClass);
			$refId = 1;
			while (list($item, $descriptive) = each($this->referencesOrder)) {
				if (array_key_exists($item, $this->referencesList)) {
					$name = (empty($this->referencesName[$item]) ? $descriptive : $this->referencesName[$item]);
					$navPoint = new NavPoint($name, $this->referencesList[$item], "ref-" . $refId++);
					$this->addNavPoint($navPoint);
				}
			}
		}
	}

	/**
	 * Build the landmarks for the ePub 3 toc.
	 * @return string
	 */
	function finalizeEPub3Landmarks() {
		$lm = "";
		if (isset($this->referencesList) && sizeof($this->referencesList) > 0) {
			$lm = "\t\t\t<nav epub:type=\"landmarks\">\n"
					. "\t\t\t\t<h2"
					. ($this->writingDirection === EPub::DIRECTION_RIGHT_TO_LEFT ? " dir=\"rtl\"" : "")
					. ">" . $this->referencesTitle . "</h2>\n"
					. "\t\t\t\t<ol>\n";

			$li = "";
			while (list($item, $descriptive) = each($this->referencesOrder)) {
				if (array_key_exists($item, $this->referencesList)) {
					$li .= "\t\t\t\t\t<li><a epub:type=\""
							. $item
							. "\" href=\"" . $this->referencesList[$item] . "\">"
							. (empty($this->referencesName[$item]) ? $descriptive : $this->referencesName[$item])
							. "</a></li>\n";
				}
			}
			if (empty($li)) {
				return "";
			}

			$lm .= $li
					. "\t\t\t\t</ol>\n"
					. "\t\t\t</nav>\n";
		}
		return $lm;
	}
}

/**
 * ePub NavMap class
 */
class NavMap {
    const _VERSION = 3.00;

    private $navPoints = array();
    private $navLevels = 0;
	private $writingDirection = NULL;

    /**
     * Class constructor.
     *
     * @return void
     */
    function __construct($writingDirection = NULL) {
		$this->setWritingDirection($writingDirection);
    }

    /**
     * Class destructor
     *
     * @return void
     */
    function __destruct() {
        unset($this->navPoints, $this->navLevels, $this->writingDirection);
    }

    /**
     * Set the writing direction to be used for this NavPoint.
     *
     * @param string $writingDirection
     */
    function setWritingDirection($writingDirection) {
        $this->writingDirection = isset($writingDirection) && is_string($writingDirection) ? trim($writingDirection) : NULL;
    }

    function getWritingDirection() {
        return $this->writingDirection;
    }

    /**
     * Add a navPoint to the root of the NavMap.
     *
     * @param NavPoint $navPoint
     * @return NavMap
     */
    function addNavPoint($navPoint) {
        if ($navPoint != NULL && is_object($navPoint) && get_class($navPoint) === "NavPoint") {
            $navPoint->setParent($this);
			if ($navPoint->getWritingDirection() == NULL) {
				$navPoint->setWritingDirection($this->writingDirection);
			}
            $this->navPoints[] = $navPoint;
            return $navPoint;
        }
        return $this;
    }

    /**
     * The final max depth for the "dtb:depth" meta attribute
     * Only available after finalize have been called.
     *
     * @return number
     */
    function getNavLevels() {
        return $this->navLevels+1;
    }

    function getLevel() {
        return 1;
    }

    function getParent() {
        return $this;
    }

    /**
     * Finalize the navMap, the final max depth for the "dtb:depth" meta attribute can be retrieved with getNavLevels after finalization
     *
     */
    function finalize() {
        $playOrder = 0;
        $this->navLevels = 0;

        $nav = "\t<navMap>\n";
        if (sizeof($this->navPoints) > 0) {
            $this->navLevels++;
            foreach ($this->navPoints as $navPoint) {
                $retLevel = $navPoint->finalize($nav, $playOrder, 0);
                if ($retLevel > $this->navLevels) {
                    $this->navLevels = $retLevel;
                }
            }
        }
        return $nav . "\t</navMap>\n";
    }

    /**
     * Finalize the navMap, the final max depth for the "dtb:depth" meta attribute can be retrieved with getNavLevels after finalization
     *
     */
    function finalizeEPub3() {
        $playOrder = 0;
        $level = 0;
        $this->navLevels = 0;

        $nav = "\t\t<nav epub:type=\"toc\" id=\"toc\">\n";

        if (sizeof($this->navPoints) > 0) {
            $this->navLevels++;

            $nav .= str_repeat("\t", $level) . "\t\t\t<ol epub:type=\"list\">\n";
            foreach ($this->navPoints as $navPoint) {
                $retLevel = $navPoint->finalizeEPub3($nav, $playOrder, 0);
                if ($retLevel > $this->navLevels) {
                    $this->navLevels = $retLevel;
                }
            }
            $nav .= str_repeat("\t", $level) . "\t\t\t</ol>\n";
        }

        return $nav . "\t\t</nav>\n";
    }
}

/**
 * ePub NavPoint class
 */
class NavPoint {
    const _VERSION = 3.00;

    private $label = NULL;
    private $contentSrc = NULL;
    private $id = NULL;
    private $navClass = NULL;
    private $isNavHidden = FALSE;
	private $navPoints = array();
	private $parent = NULL;

    /**
     * Class constructor.
     *
     * All three attributes are mandatory, though if ID is set to null (default) the value will be generated.
     *
     * @param string $label
     * @param string $contentSrc
     * @param string $id
	 * @param string $navClass
	 * @param bool   $isNavHidden
	 * @param string $writingDirection
	 */
    function __construct($label, $contentSrc = NULL, $id = NULL, $navClass = NULL, $isNavHidden = FALSE, $writingDirection = NULL) {
        $this->setLabel($label);
        $this->setContentSrc($contentSrc);
        $this->setId($id);
        $this->setNavClass($navClass);
        $this->setNavHidden($isNavHidden);
		$this->setWritingDirection($writingDirection);
    }

    /**
     * Class destructor
     *
     * @return void
     */
    function __destruct() {
        unset($this->label, $this->contentSrc, $this->id, $this->navClass);
        unset($this->isNavHidden, $this->navPoints, $this->parent);
    }

    /**
     * Set the Text label for the NavPoint.
     *
     * The label is mandatory.
     *
     * @param string $label
     */
    function setLabel($label) {
        $this->label = is_string($label) ? trim($label) : NULL;
    }

    /**
     * Get the Text label for the NavPoint.
     *
     * @return string Label
     */
    function getLabel() {
        return $this->label;
    }

    /**
     * Set the src reference for the NavPoint.
     *
     * The src is mandatory for ePub 2.
     *
     * @param string $contentSrc
     */
    function setContentSrc($contentSrc) {
        $this->contentSrc =  isset($contentSrc) && is_string($contentSrc) ? trim($contentSrc) : NULL;
    }

    /**
     * Get the src reference for the NavPoint.
     *
     * @return string content src url.
     */
    function getContentSrc() {
        return $this->contentSrc;
    }
    /**
     * Set the parent for this NavPoint.
     *
     * @param NavPoint or NavMap $parent
     */
    function setParent($parent) {
        if ($parent != NULL && is_object($parent) &&
                (get_class($parent) === "NavPoint" || get_class($parent) === "NavMap") ) {
            $this->parent = $parent;
        }
    }

    /**
     * Get the parent to this NavPoint.
     *
     * @return NavPoint, or NavMap if the parent is the root.
     */
    function getParent() {
        return $this->parent;
    }

    /**
     * Get the current level. 1 = document root.
     *
     * @return int level
     */
    function getLevel() {
        return $this->parent === NULL ? 1 : $this->parent->getLevel()+1;
    }

    /**
     * Set the id for the NavPoint.
     *
     * The id must be unique, and is mandatory.
     *
     * @param string $id
     */
    function setId($id) {
        $this->id = is_string($id) ? trim($id) : NULL;
    }

    /**
     * Set the class to be used for this NavPoint.
     *
     * @param string $navClass
     */
    function setNavClass($navClass) {
        $this->navClass = isset($navClass) && is_string($navClass) ? trim($navClass) : NULL;
    }

    /**
     * Set the class to be used for this NavPoint.
     *
     * @param string $navClass
     */
    function setNavHidden($isNavHidden) {
        $this->isNavHidden = $isNavHidden === TRUE;
    }

    /**
     * Set the writing direction to be used for this NavPoint.
     *
     * @param string $writingDirection
     */
    function setWritingDirection($writingDirection) {
        $this->writingDirection = isset($writingDirection) && is_string($writingDirection) ? trim($writingDirection) : NULL;
    }

    function getWritingDirection() {
        return $this->writingDirection;
    }

	/**
     * Add child NavPoints for multi level NavMaps.
     *
     * @param NavPoint $navPoint
     */
    function addNavPoint($navPoint) {
        if ($navPoint != NULL && is_object($navPoint) && get_class($navPoint) === "NavPoint") {
            $navPoint->setParent($this);
			if ($navPoint->getWritingDirection() == NULL) {
				$navPoint->setWritingDirection($this->writingDirection);
			}
            $this->navPoints[] = $navPoint;
            return $navPoint;
        }
        return $this;
    }

    /**
     *
     * Enter description here ...
     *
     * @param string $nav
     * @param int    $playOrder
     * @param int    $level
     * @return int
     */
    function finalize(&$nav = "", &$playOrder = 0, $level = 0) {
        $maxLevel = $level;
        $levelAdjust = 0;

		if ($this->isNavHidden) {
			return $maxLevel;
		}

		if (isset($this->contentSrc)) {
			$playOrder++;

			if ($this->id == NULL) {
				$this->id = "navpoint-" . $playOrder;
			}
			$nav .= str_repeat("\t", $level) . "\t\t<navPoint id=\"" . $this->id . "\" playOrder=\"" . $playOrder . "\">\n"
			. str_repeat("\t", $level) . "\t\t\t<navLabel>\n"
			. str_repeat("\t", $level) . "\t\t\t\t<text>" . $this->label . "</text>\n"
			. str_repeat("\t", $level) . "\t\t\t</navLabel>\n"
			. str_repeat("\t", $level) . "\t\t\t<content src=\"" . $this->contentSrc . "\" />\n";
		} else {
			$levelAdjust++;
		}

        if (sizeof($this->navPoints) > 0) {
            $maxLevel++;
            foreach ($this->navPoints as $navPoint) {
                $retLevel = $navPoint->finalize($nav, $playOrder, ($level+1+$levelAdjust));
                if ($retLevel > $maxLevel) {
                    $maxLevel = $retLevel;
                }
            }
        }

		if (isset($this->contentSrc)) {
	        $nav .= str_repeat("\t", $level) . "\t\t</navPoint>\n";
		}

        return $maxLevel;
    }

    /**
     *
     * Enter description here ...
     *
     * @param string $nav
     * @param int    $playOrder
     * @param int    $level
     * @return int
     */
    function finalizeEPub3(&$nav = "", &$playOrder = 0, $level = 0, $subLevelClass = NULL, $subLevelHidden = FALSE) {
        $maxLevel = $level;

        if ($this->id == NULL) {
            $this->id = "navpoint-" . $playOrder;
        }
		$indent = str_repeat("\t", $level) . "\t\t\t\t";

        $nav .= $indent . "<li id=\"" . $this->id . "\"";
		if (isset($this->writingDirection)) {
			$nav .= " dir=\"" . $this->writingDirection . "\"";
		}
		$nav .=  ">\n";

		if (isset($this->contentSrc)) {
			$nav .= $indent . "\t<a href=\"" . $this->contentSrc . "\">" . $this->label . "</a>\n";
		} else {
			$nav .= $indent . "\t<span>" . $this->label . "</span>\n";
		}

        if (sizeof($this->navPoints) > 0) {
            $maxLevel++;

			$nav .= $indent . "\t<ol epub:type=\"list\"";
            if (isset($subLevelClass)) {
				$nav .= " class=\"" . $subLevelClass . "\"";
			}
            if ($subLevelHidden) {
				$nav .= " hidden=\"hidden\"";
			}
            $nav .= ">\n";

            foreach ($this->navPoints as $navPoint) {
                $retLevel = $navPoint->finalizeEPub3($nav, $playOrder, ($level+2), $subLevelClass, $subLevelHidden);
                if ($retLevel > $maxLevel) {
                    $maxLevel = $retLevel;
                }
            }
            $nav .= $indent . "\t</ol>\n";
        }

        $nav .= $indent . "</li>\n";

        return $maxLevel;
    }
}
?>