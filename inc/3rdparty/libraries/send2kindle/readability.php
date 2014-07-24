<?php
/**
 * PHP Readability
 *
 * Readability PHP 版本，详见
 *      http://code.google.com/p/arc90labs-readability/
 *
 * ChangeLog:
 *
 *      [+] 2011-02-17 初始化版本
 *
 * @author mingcheng<i.feelinglucky#gmail.com>
 * @date   2011-02-17
 * @link   http://www.gracecode.com/
 */

define("READABILITY_VERSION", 0.12);

class Readability2 {
    // 保存判定结果的标记位名称
    const ATTR_CONTENT_SCORE = "contentScore";

    // DOM 解析类目前只支持 UTF-8 编码
    const DOM_DEFAULT_CHARSET = "utf-8";

    // 当判定失败时显示的内容
    const MESSAGE_CAN_NOT_GET = "Sorry, readability was unable to parse this page for content.  \n
            If you feel like it should have been able to, 
            please let me know by mail: lucky[at]gracecode.com";

    // DOM 解析类（PHP5 已内置）
    protected $DOM = null;

    // 需要解析的源代码
    protected $source = "";

    // 章节的父元素列表
    private $parentNodes = array();

    // 需要删除的标签
    private $junkTags = Array("style", "form", "iframe", "script", "button", "input", "textarea");

    // 需要删除的属性
    private $junkAttrs = Array("style", "class", "onclick", "onmouseover", "align", "border", "margin");


    /**
     * 构造函数
     *      @param $input_char 字符串的编码。默认 utf-8，可以省略
     */
    function __construct($source, $input_char = "utf-8") {
        $this->source = $source;

        // DOM 解析类只能处理 UTF-8 格式的字符
        $source = mb_convert_encoding($source, 'HTML-ENTITIES', $input_char);

        // 预处理 HTML 标签，剔除冗余的标签等
        $source = $this->preparSource($source);

        // 生成 DOM 解析类
        $this->DOM = new DOMDocument('1.0', $input_char);
        try {
            //libxml_use_internal_errors(true);
            // 会有些错误信息，不过不要紧 :^)
            if (!@$this->DOM->loadHTML('<?xml encoding="'.Readability2::DOM_DEFAULT_CHARSET.'">'.$source)) {
                throw new Exception("Parse HTML Error!");
            }

            foreach ($this->DOM->childNodes as $item) {
                if ($item->nodeType == XML_PI_NODE) {
                    $this->DOM->removeChild($item); // remove hack
                }
            }

            // insert proper
            $this->DOM->encoding = Readability2::DOM_DEFAULT_CHARSET;
        } catch (Exception $e) {
            // ...
        }
    }


    /**
     * 预处理 HTML 标签，使其能够准确被 DOM 解析类处理
     *
     * @return String
     */
    private function preparSource($string) {
        // 剔除多余的 HTML 编码标记，避免解析出错
        preg_match("/charset=([\w|\-]+);?/", $string, $match);
        if (isset($match[1])) {
            $string = preg_replace("/charset=([\w|\-]+);?/", "", $string, 1);
        }

        // Replace all doubled-up <BR> tags with <P> tags, and remove fonts.
        $string = preg_replace("/<br\/?>[ \r\n\s]*<br\/?>/i", "</p><p>", $string);
        $string = preg_replace("/<\/?font[^>]*>/i", "", $string);

        return trim($string);
    }


    /**
     * 删除 DOM 元素中所有的 $TagName 标签
     *
     * @return DOMDocument
     */
    private function removeJunkTag($RootNode, $TagName) {
        $Tags = $RootNode->getElementsByTagName($TagName);

        $i = 0;
        while($Tag = $Tags->item($i++)) {
            $parentNode = $Tag->parentNode;
            $parentNode->removeChild($Tag);
        }

        return $RootNode;
    }

    /**
     * 删除元素中所有不需要的属性
     */
    private function removeJunkAttr($RootNode, $Attr) {
        $Tags = $RootNode->getElementsByTagName("*");

        $i = 0;
        while($Tag = $Tags->item($i++)) {
            $Tag->removeAttribute($Attr);
        }

        return $RootNode;
    }

    /**
     * 根据评分获取页面主要内容的盒模型
     *      判定算法来自：http://code.google.com/p/arc90labs-readability/
     *
     * @return DOMNode
     */
    private function getTopBox() {
        // 获得页面所有的章节
        $allParagraphs = $this->DOM->getElementsByTagName("p");

        // Study all the paragraphs and find the chunk that has the best score.
        // A score is determined by things like: Number of <p>'s, commas, special classes, etc.
        $i = 0;
        while($paragraph = $allParagraphs->item($i++)) {
            $parentNode   = $paragraph->parentNode;
            $contentScore = intval($parentNode->getAttribute(Readability2::ATTR_CONTENT_SCORE));
            $className    = $parentNode->getAttribute("class");
            $id           = $parentNode->getAttribute("id");

            // Look for a special classname
            if (preg_match("/(comment|meta|footer|footnote)/i", $className)) {
                $contentScore -= 50;
            } else if(preg_match(
                "/((^|\\s)(post|hentry|entry[-]?(content|text|body)?|article[-]?(content|text|body)?)(\\s|$))/i",
                $className)) {
                $contentScore += 25;
            }

            // Look for a special ID
            if (preg_match("/(comment|meta|footer|footnote)/i", $id)) {
                $contentScore -= 50;
            } else if (preg_match(
                "/^(post|hentry|entry[-]?(content|text|body)?|article[-]?(content|text|body)?)$/i",
                $id)) {
                $contentScore += 25;
            }

            // Add a point for the paragraph found
            // Add points for any commas within this paragraph
            if (strlen($paragraph->nodeValue) > 10) {
                $contentScore += strlen($paragraph->nodeValue);
            }

            // 保存父元素的判定得分
            $parentNode->setAttribute(Readability2::ATTR_CONTENT_SCORE, $contentScore);

            // 保存章节的父元素，以便下次快速获取
            array_push($this->parentNodes, $parentNode);
        }

        $topBox = $this->DOM->createElement('div', Readability2::MESSAGE_CAN_NOT_GET);
        // Assignment from index for performance. 
        //     See http://www.peachpit.com/articles/article.aspx?p=31567&seqNum=5 
        for ($i = 0, $len = sizeof($this->parentNodes); $i < $len; $i++) {
            $parentNode      = $this->parentNodes[$i];
            $contentScore    = intval($parentNode->getAttribute(Readability2::ATTR_CONTENT_SCORE));
            $orgContentScore = intval($topBox->getAttribute(Readability2::ATTR_CONTENT_SCORE));

            if ($contentScore && $contentScore > $orgContentScore) {
                $topBox = $parentNode;
            }
        }

        // 此时，$topBox 应为已经判定后的页面内容主元素
        return $topBox;
    }


    /**
     * 获取 HTML 页面标题
     *
     * @return String
     */
    public function getTitle() {
        $title = $this->DOM->getElementsByTagName("title");
        return $title->item(0);
    }


    /**
     * 获取页面的主要内容（Readability 以后的内容）
     *
     * @return Array
     */
    public function getContent() {
        if (!$this->DOM) return false;

        // 获取页面标题
        $ContentTitle = $this->getTitle();

        // 获取页面主内容
        $ContentBox = $this->getTopBox();

        // 复制内容到新的 DOMDocument
        $Target = new DOMDocument;
        $Target->appendChild($Target->importNode($ContentBox, true));

        // 删除不需要的标签
        foreach ($this->junkTags as $tag) {
            $Target = $this->removeJunkTag($Target, $tag);
        }

        // 删除不需要的属性
        foreach ($this->junkAttrs as $attr) {
            $Target = $this->removeJunkAttr($Target, $attr);
        }

        // 多个数据，以数组的形式返回
        return Array(
            'title'   => $ContentTitle ? $ContentTitle->nodeValue : "",
            'content' => $Target->saveHTML()
        );
    }

    function __destruct() { }
}
