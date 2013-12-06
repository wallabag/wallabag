<?php

/*

Copyright 2007 Jeroen van der Meer <http://jero.net/>
Copyright 2009 Edward Z. Yang <edwardzyang@thewritingpot.com>

Permission is hereby granted, free of charge, to any person obtaining a
copy of this software and associated documentation files (the
"Software"), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:

The above copyright notice and this permission notice shall be included
in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

*/

// Tags for FIX ME!!!: (in order of priority)
//      XXX - should be fixed NAO!
//      XERROR - with regards to parse errors
//      XSCRIPT - with regards to scripting mode
//      XENCODING - with regards to encoding (for reparsing tests)
//      XDOM - DOM specific code (tagName is explicitly not marked).
//          this is not (yet) in helper functions.

class HTML5_TreeBuilder {
    public $stack = array();
    public $content_model;

    private $mode;
    private $original_mode;
    private $secondary_mode;
    private $dom;
    // Whether or not normal insertion of nodes should actually foster
    // parent (used in one case in spec)
    private $foster_parent = false;
    private $a_formatting  = array();

    private $head_pointer = null;
    private $form_pointer = null;

    private $flag_frameset_ok = true;
    private $flag_force_quirks = false;
    private $ignored = false;
    private $quirks_mode = null;
    // this gets to 2 when we want to ignore the next lf character, and
    // is decrement at the beginning of each processed token (this way,
    // code can check for (bool)$ignore_lf_token, but it phases out
    // appropriately)
    private $ignore_lf_token = 0;
    private $fragment = false;
    private $root;

    private $scoping = array('applet','button','caption','html','marquee','object','table','td','th', 'svg:foreignObject');
    private $formatting = array('a','b','big','code','em','font','i','nobr','s','small','strike','strong','tt','u');
    // dl and ds are speculative
    private $special = array('address','area','article','aside','base','basefont','bgsound',
    'blockquote','body','br','center','col','colgroup','command','dc','dd','details','dir','div','dl','ds',
    'dt','embed','fieldset','figure','footer','form','frame','frameset','h1','h2','h3','h4','h5',
    'h6','head','header','hgroup','hr','iframe','img','input','isindex','li','link',
    'listing','menu','meta','nav','noembed','noframes','noscript','ol',
    'p','param','plaintext','pre','script','select','spacer','style',
    'tbody','textarea','tfoot','thead','title','tr','ul','wbr');

    private $pendingTableCharacters;
    private $pendingTableCharactersDirty;

    // Tree construction modes
    const INITIAL           = 0;
    const BEFORE_HTML       = 1;
    const BEFORE_HEAD       = 2;
    const IN_HEAD           = 3;
    const IN_HEAD_NOSCRIPT  = 4;
    const AFTER_HEAD        = 5;
    const IN_BODY           = 6;
    const IN_CDATA_RCDATA   = 7;
    const IN_TABLE          = 8;
    const IN_TABLE_TEXT     = 9;
    const IN_CAPTION        = 10;
    const IN_COLUMN_GROUP   = 11;
    const IN_TABLE_BODY     = 12;
    const IN_ROW            = 13;
    const IN_CELL           = 14;
    const IN_SELECT         = 15;
    const IN_SELECT_IN_TABLE= 16;
    const IN_FOREIGN_CONTENT= 17;
    const AFTER_BODY        = 18;
    const IN_FRAMESET       = 19;
    const AFTER_FRAMESET    = 20;
    const AFTER_AFTER_BODY  = 21;
    const AFTER_AFTER_FRAMESET = 22;

    /**
     * Converts a magic number to a readable name. Use for debugging.
     */
    private function strConst($number) {
        static $lookup;
        if (!$lookup) {
            $lookup = array();
            $r = new ReflectionClass('HTML5_TreeBuilder');
            $consts = $r->getConstants();
            foreach ($consts as $const => $num) {
                if (!is_int($num)) continue;
                $lookup[$num] = $const;
            }
        }
        return $lookup[$number];
    }

    // The different types of elements.
    const SPECIAL    = 100;
    const SCOPING    = 101;
    const FORMATTING = 102;
    const PHRASING   = 103;

    // Quirks modes in $quirks_mode
    const NO_QUIRKS             = 200;
    const QUIRKS_MODE           = 201;
    const LIMITED_QUIRKS_MODE   = 202;

    // Marker to be placed in $a_formatting
    const MARKER     = 300;

    // Namespaces for foreign content
    const NS_HTML   = null; // to prevent DOM from requiring NS on everything
    const NS_MATHML = 'http://www.w3.org/1998/Math/MathML';
    const NS_SVG    = 'http://www.w3.org/2000/svg';
    const NS_XLINK  = 'http://www.w3.org/1999/xlink';
    const NS_XML    = 'http://www.w3.org/XML/1998/namespace';
    const NS_XMLNS  = 'http://www.w3.org/2000/xmlns/';

    // Different types of scopes to test for elements
    const SCOPE = 0;
    const SCOPE_LISTITEM = 1;
    const SCOPE_TABLE = 2;

    public function __construct() {
        $this->mode = self::INITIAL;
        $this->dom = new DOMDocument;

        $this->dom->encoding = 'UTF-8';
        $this->dom->preserveWhiteSpace = true;
        $this->dom->substituteEntities = true;
        $this->dom->strictErrorChecking = false;
    }

    // Process tag tokens
    public function emitToken($token, $mode = null) {
        // XXX: ignore parse errors... why are we emitting them, again?
        if ($token['type'] === HTML5_Tokenizer::PARSEERROR) return;
        if ($mode === null) $mode = $this->mode;

        /*
        $backtrace = debug_backtrace();
        if ($backtrace[1]['class'] !== 'HTML5_TreeBuilder') echo "--\n";
        echo $this->strConst($mode);
        if ($this->original_mode) echo " (originally ".$this->strConst($this->original_mode).")";
        echo "\n  ";
        token_dump($token);
        $this->printStack();
        $this->printActiveFormattingElements();
        if ($this->foster_parent) echo "  -> this is a foster parent mode\n";
        if ($this->flag_frameset_ok) echo "  -> frameset ok\n";
        */

        if ($this->ignore_lf_token) $this->ignore_lf_token--;
        $this->ignored = false;
        // indenting is a little wonky, this can be changed later on
        switch ($mode) {

    case self::INITIAL:

        /* A character token that is one of U+0009 CHARACTER TABULATION,
         * U+000A LINE FEED (LF), U+000C FORM FEED (FF),  or U+0020 SPACE */
        if ($token['type'] === HTML5_Tokenizer::SPACECHARACTER) {
            /* Ignore the token. */
            $this->ignored = true;
        } elseif ($token['type'] === HTML5_Tokenizer::DOCTYPE) {
            if (
                $token['name'] !== 'html' || !empty($token['public']) ||
                !empty($token['system']) || $token !== 'about:legacy-compat'
            ) {
                /* If the DOCTYPE token's name is not a case-sensitive match
                 * for the string "html", or if the token's public identifier
                 * is not missing, or if the token's system identifier is
                 * neither missing nor a case-sensitive match for the string
                 * "about:legacy-compat", then there is a parse error (this
                 * is the DOCTYPE parse error). */
                // DOCTYPE parse error
            }
            /* Append a DocumentType node to the Document node, with the name
             * attribute set to the name given in the DOCTYPE token, or the
             * empty string if the name was missing; the publicId attribute
             * set to the public identifier given in the DOCTYPE token, or
             * the empty string if the public identifier was missing; the
             * systemId attribute set to the system identifier given in the
             * DOCTYPE token, or the empty string if the system identifier
             * was missing; and the other attributes specific to
             * DocumentType objects set to null and empty lists as
             * appropriate. Associate the DocumentType node with the
             * Document object so that it is returned as the value of the
             * doctype attribute of the Document object. */
            if (!isset($token['public'])) $token['public'] = null;
            if (!isset($token['system'])) $token['system'] = null;
            // XDOM
            // Yes this is hacky. I'm kind of annoyed that I can't appendChild
            // a doctype to DOMDocument. Maybe I haven't chanted the right
            // syllables.
            $impl = new DOMImplementation();
            // This call can fail for particularly pathological cases (namely,
            // the qualifiedName parameter ($token['name']) could be missing.
            if ($token['name']) {
                $doctype = $impl->createDocumentType($token['name'], $token['public'], $token['system']);
                $this->dom->appendChild($doctype);
            } else {
                // It looks like libxml's not actually *able* to express this case.
                // So... don't.
                $this->dom->emptyDoctype = true;
            }
            $public = is_null($token['public']) ? false : strtolower($token['public']);
            $system = is_null($token['system']) ? false : strtolower($token['system']);
            $publicStartsWithForQuirks = array(
             "+//silmaril//dtd html pro v0r11 19970101//",
             "-//advasoft ltd//dtd html 3.0 aswedit + extensions//",
             "-//as//dtd html 3.0 aswedit + extensions//",
             "-//ietf//dtd html 2.0 level 1//",
             "-//ietf//dtd html 2.0 level 2//",
             "-//ietf//dtd html 2.0 strict level 1//",
             "-//ietf//dtd html 2.0 strict level 2//",
             "-//ietf//dtd html 2.0 strict//",
             "-//ietf//dtd html 2.0//",
             "-//ietf//dtd html 2.1e//",
             "-//ietf//dtd html 3.0//",
             "-//ietf//dtd html 3.2 final//",
             "-//ietf//dtd html 3.2//",
             "-//ietf//dtd html 3//",
             "-//ietf//dtd html level 0//",
             "-//ietf//dtd html level 1//",
             "-//ietf//dtd html level 2//",
             "-//ietf//dtd html level 3//",
             "-//ietf//dtd html strict level 0//",
             "-//ietf//dtd html strict level 1//",
             "-//ietf//dtd html strict level 2//",
             "-//ietf//dtd html strict level 3//",
             "-//ietf//dtd html strict//",
             "-//ietf//dtd html//",
             "-//metrius//dtd metrius presentational//",
             "-//microsoft//dtd internet explorer 2.0 html strict//",
             "-//microsoft//dtd internet explorer 2.0 html//",
             "-//microsoft//dtd internet explorer 2.0 tables//",
             "-//microsoft//dtd internet explorer 3.0 html strict//",
             "-//microsoft//dtd internet explorer 3.0 html//",
             "-//microsoft//dtd internet explorer 3.0 tables//",
             "-//netscape comm. corp.//dtd html//",
             "-//netscape comm. corp.//dtd strict html//",
             "-//o'reilly and associates//dtd html 2.0//",
             "-//o'reilly and associates//dtd html extended 1.0//",
             "-//o'reilly and associates//dtd html extended relaxed 1.0//",
             "-//spyglass//dtd html 2.0 extended//",
             "-//sq//dtd html 2.0 hotmetal + extensions//",
             "-//sun microsystems corp.//dtd hotjava html//",
             "-//sun microsystems corp.//dtd hotjava strict html//",
             "-//w3c//dtd html 3 1995-03-24//",
             "-//w3c//dtd html 3.2 draft//",
             "-//w3c//dtd html 3.2 final//",
             "-//w3c//dtd html 3.2//",
             "-//w3c//dtd html 3.2s draft//",
             "-//w3c//dtd html 4.0 frameset//",
             "-//w3c//dtd html 4.0 transitional//",
             "-//w3c//dtd html experimental 19960712//",
             "-//w3c//dtd html experimental 970421//",
             "-//w3c//dtd w3 html//",
             "-//w3o//dtd w3 html 3.0//",
             "-//webtechs//dtd mozilla html 2.0//",
             "-//webtechs//dtd mozilla html//",
            );
            $publicSetToForQuirks = array(
             "-//w3o//dtd w3 html strict 3.0//",
             "-/w3c/dtd html 4.0 transitional/en",
             "html",
            );
            $publicStartsWithAndSystemForQuirks = array(
             "-//w3c//dtd html 4.01 frameset//",
             "-//w3c//dtd html 4.01 transitional//",
            );
            $publicStartsWithForLimitedQuirks = array(
             "-//w3c//dtd xhtml 1.0 frameset//",
             "-//w3c//dtd xhtml 1.0 transitional//",
            );
            $publicStartsWithAndSystemForLimitedQuirks = array(
             "-//w3c//dtd html 4.01 frameset//",
             "-//w3c//dtd html 4.01 transitional//",
            );
            // first, do easy checks
            if (
                !empty($token['force-quirks']) ||
                strtolower($token['name']) !== 'html'
            ) {
                $this->quirks_mode = self::QUIRKS_MODE;
            } else {
                do {
                    if ($system) {
                        foreach ($publicStartsWithAndSystemForQuirks as $x) {
                            if (strncmp($public, $x, strlen($x)) === 0) {
                                $this->quirks_mode = self::QUIRKS_MODE;
                                break;
                            }
                        }
                        if (!is_null($this->quirks_mode)) break;
                        foreach ($publicStartsWithAndSystemForLimitedQuirks as $x) {
                            if (strncmp($public, $x, strlen($x)) === 0) {
                                $this->quirks_mode = self::LIMITED_QUIRKS_MODE;
                                break;
                            }
                        }
                        if (!is_null($this->quirks_mode)) break;
                    }
                    foreach ($publicSetToForQuirks as $x) {
                        if ($public === $x) {
                            $this->quirks_mode = self::QUIRKS_MODE;
                            break;
                        }
                    }
                    if (!is_null($this->quirks_mode)) break;
                    foreach ($publicStartsWithForLimitedQuirks as $x) {
                        if (strncmp($public, $x, strlen($x)) === 0) {
                            $this->quirks_mode = self::LIMITED_QUIRKS_MODE;
                        }
                    }
                    if (!is_null($this->quirks_mode)) break;
                    if ($system === "http://www.ibm.com/data/dtd/v11/ibmxhtml1-transitional.dtd") {
                        $this->quirks_mode = self::QUIRKS_MODE;
                        break;
                    }
                    foreach ($publicStartsWithForQuirks as $x) {
                        if (strncmp($public, $x, strlen($x)) === 0) {
                            $this->quirks_mode = self::QUIRKS_MODE;
                            break;
                        }
                    }
                    if (is_null($this->quirks_mode)) {
                        $this->quirks_mode = self::NO_QUIRKS;
                    }
                } while (false);
            }
            $this->mode = self::BEFORE_HTML;
        } else {
            // parse error
            /* Switch the insertion mode to "before html", then reprocess the
             * current token. */
            $this->mode = self::BEFORE_HTML;
            $this->quirks_mode = self::QUIRKS_MODE;
            $this->emitToken($token);
        }
        break;

    case self::BEFORE_HTML:

        /* A DOCTYPE token */
        if($token['type'] === HTML5_Tokenizer::DOCTYPE) {
            // Parse error. Ignore the token.
            $this->ignored = true;

        /* A comment token */
        } elseif($token['type'] === HTML5_Tokenizer::COMMENT) {
            /* Append a Comment node to the Document object with the data
            attribute set to the data given in the comment token. */
            // XDOM
            $comment = $this->dom->createComment($token['data']);
            $this->dom->appendChild($comment);

        /* A character token that is one of one of U+0009 CHARACTER TABULATION,
        U+000A LINE FEED (LF), U+000B LINE TABULATION, U+000C FORM FEED (FF),
        or U+0020 SPACE */
        } elseif($token['type'] === HTML5_Tokenizer::SPACECHARACTER) {
            /* Ignore the token. */
            $this->ignored = true;

        /* A start tag whose tag name is "html" */
        } elseif($token['type'] === HTML5_Tokenizer::STARTTAG && $token['name'] == 'html') {
            /* Create an element for the token in the HTML namespace. Append it 
             * to the Document  object. Put this element in the stack of open 
             * elements. */
            // XDOM
            $html = $this->insertElement($token, false);
            $this->dom->appendChild($html);
            $this->stack[] = $html;

            $this->mode = self::BEFORE_HEAD;

        } else {
            /* Create an html element. Append it to the Document object. Put
             * this element in the stack of open elements. */
            // XDOM
            $html = $this->dom->createElementNS(self::NS_HTML, 'html');
            $this->dom->appendChild($html);
            $this->stack[] = $html;

            /* Switch the insertion mode to "before head", then reprocess the
             * current token. */
            $this->mode = self::BEFORE_HEAD;
            $this->emitToken($token);
        }
        break;

    case self::BEFORE_HEAD:

        /* A character token that is one of one of U+0009 CHARACTER TABULATION,
        U+000A LINE FEED (LF), U+000B LINE TABULATION, U+000C FORM FEED (FF),
        or U+0020 SPACE */
        if($token['type'] === HTML5_Tokenizer::SPACECHARACTER) {
            /* Ignore the token. */
            $this->ignored = true;

        /* A comment token */
        } elseif($token['type'] === HTML5_Tokenizer::COMMENT) {
            /* Append a Comment node to the current node with the data attribute
            set to the data given in the comment token. */
            $this->insertComment($token['data']);

        /* A DOCTYPE token */
        } elseif($token['type'] === HTML5_Tokenizer::DOCTYPE) {
            /* Parse error. Ignore the token */
            $this->ignored = true;
            // parse error

        /* A start tag token with the tag name "html" */
        } elseif($token['type'] === HTML5_Tokenizer::STARTTAG && $token['name'] === 'html') {
            /* Process the token using the rules for the "in body"
             * insertion mode. */
            $this->processWithRulesFor($token, self::IN_BODY);

        /* A start tag token with the tag name "head" */
        } elseif($token['type'] === HTML5_Tokenizer::STARTTAG && $token['name'] === 'head') {
            /* Insert an HTML element for the token. */
            $element = $this->insertElement($token);

            /* Set the head element pointer to this new element node. */
            $this->head_pointer = $element;

            /* Change the insertion mode to "in head". */
            $this->mode = self::IN_HEAD;

        /* An end tag whose tag name is one of: "head", "body", "html", "br" */
        } elseif(
            $token['type'] === HTML5_Tokenizer::ENDTAG && (
                $token['name'] === 'head' || $token['name'] === 'body' ||
                $token['name'] === 'html' || $token['name'] === 'br'
        )) {
            /* Act as if a start tag token with the tag name "head" and no
             * attributes had been seen, then reprocess the current token. */
            $this->emitToken(array(
                'name' => 'head',
                'type' => HTML5_Tokenizer::STARTTAG,
                'attr' => array()
            ));
            $this->emitToken($token);

        /* Any other end tag */
        } elseif($token['type'] === HTML5_Tokenizer::ENDTAG) {
            /* Parse error. Ignore the token. */
            $this->ignored = true;

        } else {
            /* Act as if a start tag token with the tag name "head" and no
             * attributes had been seen, then reprocess the current token.
             * Note: This will result in an empty head element being
             * generated, with the current token being reprocessed in the
             * "after head" insertion mode. */
            $this->emitToken(array(
                'name' => 'head',
                'type' => HTML5_Tokenizer::STARTTAG,
                'attr' => array()
            ));
            $this->emitToken($token);
        }
        break;

    case self::IN_HEAD:

        /* A character token that is one of one of U+0009 CHARACTER TABULATION,
        U+000A LINE FEED (LF), U+000B LINE TABULATION, U+000C FORM FEED (FF),
        or U+0020 SPACE. */
        if($token['type'] === HTML5_Tokenizer::SPACECHARACTER) {
            /* Insert the character into the current node. */
            $this->insertText($token['data']);

        /* A comment token */
        } elseif($token['type'] === HTML5_Tokenizer::COMMENT) {
            /* Append a Comment node to the current node with the data attribute
            set to the data given in the comment token. */
            $this->insertComment($token['data']);

        /* A DOCTYPE token */
        } elseif($token['type'] === HTML5_Tokenizer::DOCTYPE) {
            /* Parse error. Ignore the token. */
            $this->ignored = true;
            // parse error

        /* A start tag whose tag name is "html" */
        } elseif($token['type'] === HTML5_Tokenizer::STARTTAG &&
        $token['name'] === 'html') {
            $this->processWithRulesFor($token, self::IN_BODY);

        /* A start tag whose tag name is one of: "base", "command", "link" */
        } elseif($token['type'] === HTML5_Tokenizer::STARTTAG &&
        ($token['name'] === 'base' || $token['name'] === 'command' ||
        $token['name'] === 'link')) {
            /* Insert an HTML element for the token. Immediately pop the
             * current node off the stack of open elements. */
            $this->insertElement($token);
            array_pop($this->stack);

            // YYY: Acknowledge the token's self-closing flag, if it is set.

        /* A start tag whose tag name is "meta" */
        } elseif($token['type'] === HTML5_Tokenizer::STARTTAG && $token['name'] === 'meta') {
            /* Insert an HTML element for the token. Immediately pop the
             * current node off the stack of open elements. */
            $this->insertElement($token);
            array_pop($this->stack);

            // XERROR: Acknowledge the token's self-closing flag, if it is set.

            // XENCODING: If the element has a charset attribute, and its value is a
            // supported encoding, and the confidence is currently tentative,
            // then change the encoding to the encoding given by the value of
            // the charset attribute.
            //
            // Otherwise, if the element has a content attribute, and applying
            // the algorithm for extracting an encoding from a Content-Type to
            // its value returns a supported encoding encoding, and the
            // confidence is currently tentative, then change the encoding to
            // the encoding encoding.

        /* A start tag with the tag name "title" */
        } elseif($token['type'] === HTML5_Tokenizer::STARTTAG && $token['name'] === 'title') {
            $this->insertRCDATAElement($token);

        /* A start tag whose tag name is "noscript", if the scripting flag is enabled, or
         * A start tag whose tag name is one of: "noframes", "style" */
        } elseif($token['type'] === HTML5_Tokenizer::STARTTAG &&
        ($token['name'] === 'noscript' || $token['name'] === 'noframes' || $token['name'] === 'style')) {
            // XSCRIPT: Scripting flag not respected
            $this->insertCDATAElement($token);

        // XSCRIPT: Scripting flag disable not implemented

        /* A start tag with the tag name "script" */
        } elseif($token['type'] === HTML5_Tokenizer::STARTTAG && $token['name'] === 'script') {
            /* 1. Create an element for the token in the HTML namespace. */
            $node = $this->insertElement($token, false);

            /* 2. Mark the element as being "parser-inserted" */
            // Uhhh... XSCRIPT

            /* 3. If the parser was originally created for the HTML
             * fragment parsing algorithm, then mark the script element as 
             * "already executed". (fragment case) */
            // ditto... XSCRIPT

            /* 4. Append the new element to the current node  and push it onto 
             * the stack of open elements.  */
            end($this->stack)->appendChild($node);
            $this->stack[] = $node;
            // I guess we could squash these together

            /* 6. Let the original insertion mode be the current insertion mode. */
            $this->original_mode = $this->mode;
            /* 7. Switch the insertion mode to "in CDATA/RCDATA" */
            $this->mode = self::IN_CDATA_RCDATA;
            /* 5. Switch the tokeniser's content model flag to the CDATA state. */
            $this->content_model = HTML5_Tokenizer::CDATA;

        /* An end tag with the tag name "head" */
        } elseif($token['type'] === HTML5_Tokenizer::ENDTAG && $token['name'] === 'head') {
            /* Pop the current node (which will be the head element) off the stack of open elements. */
            array_pop($this->stack);

            /* Change the insertion mode to "after head". */
            $this->mode = self::AFTER_HEAD;

        // Slight logic inversion here to minimize duplication
        /* A start tag with the tag name "head". */
        /* An end tag whose tag name is not one of: "body", "html", "br" */
        } elseif(($token['type'] === HTML5_Tokenizer::STARTTAG && $token['name'] === 'head') ||
        ($token['type'] === HTML5_Tokenizer::ENDTAG && $token['name'] !== 'html' &&
        $token['name'] !== 'body' && $token['name'] !== 'br')) {
            // Parse error. Ignore the token.
            $this->ignored = true;

        /* Anything else */
        } else {
            /* Act as if an end tag token with the tag name "head" had been
             * seen, and reprocess the current token. */
            $this->emitToken(array(
                'name' => 'head',
                'type' => HTML5_Tokenizer::ENDTAG
            ));

            /* Then, reprocess the current token. */
            $this->emitToken($token);
        }
        break;

    case self::IN_HEAD_NOSCRIPT:
        if ($token['type'] === HTML5_Tokenizer::DOCTYPE) {
            // parse error
        } elseif ($token['type'] === HTML5_Tokenizer::STARTTAG && $token['name'] === 'html') {
            $this->processWithRulesFor($token, self::IN_BODY);
        } elseif ($token['type'] === HTML5_Tokenizer::ENDTAG && $token['name'] === 'noscript') {
            /* Pop the current node (which will be a noscript element) from the
             * stack of open elements; the new current node will be a head
             * element. */
            array_pop($this->stack);
            $this->mode = self::IN_HEAD;
        } elseif (
            ($token['type'] === HTML5_Tokenizer::SPACECHARACTER) ||
            ($token['type'] === HTML5_Tokenizer::COMMENT) ||
            ($token['type'] === HTML5_Tokenizer::STARTTAG && (
                $token['name'] === 'link' || $token['name'] === 'meta' ||
                $token['name'] === 'noframes' || $token['name'] === 'style'))) {
            $this->processWithRulesFor($token, self::IN_HEAD);
        // inverted logic
        } elseif (
            ($token['type'] === HTML5_Tokenizer::STARTTAG && (
                $token['name'] === 'head' || $token['name'] === 'noscript')) ||
            ($token['type'] === HTML5_Tokenizer::ENDTAG &&
                $token['name'] !== 'br')) {
            // parse error
        } else {
            // parse error
            $this->emitToken(array(
                'type' => HTML5_Tokenizer::ENDTAG,
                'name' => 'noscript',
            ));
            $this->emitToken($token);
        }
        break;

    case self::AFTER_HEAD:
        /* Handle the token as follows: */

        /* A character token that is one of one of U+0009 CHARACTER TABULATION,
        U+000A LINE FEED (LF), U+000B LINE TABULATION, U+000C FORM FEED (FF),
        or U+0020 SPACE */
        if($token['type'] === HTML5_Tokenizer::SPACECHARACTER) {
            /* Append the character to the current node. */
            $this->insertText($token['data']);

        /* A comment token */
        } elseif($token['type'] === HTML5_Tokenizer::COMMENT) {
            /* Append a Comment node to the current node with the data attribute
            set to the data given in the comment token. */
            $this->insertComment($token['data']);

        } elseif ($token['type'] === HTML5_Tokenizer::DOCTYPE) {
            // parse error

        } elseif ($token['type'] === HTML5_Tokenizer::STARTTAG && $token['name'] === 'html') {
            $this->processWithRulesFor($token, self::IN_BODY);

        /* A start tag token with the tag name "body" */
        } elseif($token['type'] === HTML5_Tokenizer::STARTTAG && $token['name'] === 'body') {
            $this->insertElement($token);

            /* Set the frameset-ok flag to "not ok". */
            $this->flag_frameset_ok = false;

            /* Change the insertion mode to "in body". */
            $this->mode = self::IN_BODY;

        /* A start tag token with the tag name "frameset" */
        } elseif($token['type'] === HTML5_Tokenizer::STARTTAG && $token['name'] === 'frameset') {
            /* Insert a frameset element for the token. */
            $this->insertElement($token);

            /* Change the insertion mode to "in frameset". */
            $this->mode = self::IN_FRAMESET;

        /* A start tag token whose tag name is one of: "base", "link", "meta",
        "script", "style", "title" */
        } elseif($token['type'] === HTML5_Tokenizer::STARTTAG && in_array($token['name'],
        array('base', 'link', 'meta', 'noframes', 'script', 'style', 'title'))) {
            // parse error
            /* Push the node pointed to by the head element pointer onto the
             * stack of open elements. */
            $this->stack[] = $this->head_pointer;
            $this->processWithRulesFor($token, self::IN_HEAD);
            array_splice($this->stack, array_search($this->head_pointer, $this->stack, true), 1);

        // inversion of specification
        } elseif(
        ($token['type'] === HTML5_Tokenizer::STARTTAG && $token['name'] === 'head') ||
        ($token['type'] === HTML5_Tokenizer::ENDTAG &&
            $token['name'] !== 'body' && $token['name'] !== 'html' &&
            $token['name'] !== 'br')) {
            // parse error

        /* Anything else */
        } else {
            $this->emitToken(array(
                'name' => 'body',
                'type' => HTML5_Tokenizer::STARTTAG,
                'attr' => array()
            ));
            $this->flag_frameset_ok = true;
            $this->emitToken($token);
        }
        break;

    case self::IN_BODY:
        /* Handle the token as follows: */

        switch($token['type']) {
            /* A character token */
            case HTML5_Tokenizer::CHARACTER:
            case HTML5_Tokenizer::SPACECHARACTER:
                /* Reconstruct the active formatting elements, if any. */
                $this->reconstructActiveFormattingElements();

                /* Append the token's character to the current node. */
                $this->insertText($token['data']);

                /* If the token is not one of U+0009 CHARACTER TABULATION,
                 * U+000A LINE FEED (LF), U+000C FORM FEED (FF),  or U+0020
                 * SPACE, then set the frameset-ok flag to "not ok". */
                // i.e., if any of the characters is not whitespace
                if (strlen($token['data']) !== strspn($token['data'], HTML5_Tokenizer::WHITESPACE)) {
                    $this->flag_frameset_ok = false;
                }
            break;

            /* A comment token */
            case HTML5_Tokenizer::COMMENT:
                /* Append a Comment node to the current node with the data
                attribute set to the data given in the comment token. */
                $this->insertComment($token['data']);
            break;

            case HTML5_Tokenizer::DOCTYPE:
                // parse error
            break;

            case HTML5_Tokenizer::EOF:
                // parse error
            break;

            case HTML5_Tokenizer::STARTTAG:
            switch($token['name']) {
                case 'html':
                    // parse error
                    /* For each attribute on the token, check to see if the
                     * attribute is already present on the top element of the
                     * stack of open elements. If it is not, add the attribute
                     * and its corresponding value to that element. */
                    foreach($token['attr'] as $attr) {
                        if(!$this->stack[0]->hasAttribute($attr['name'])) {
                            $this->stack[0]->setAttribute($attr['name'], $attr['value']);
                        }
                    }
                break;

                case 'base': case 'command': case 'link': case 'meta': case 'noframes':
                case 'script': case 'style': case 'title':
                    /* Process the token as if the insertion mode had been "in
                    head". */
                    $this->processWithRulesFor($token, self::IN_HEAD);
                break;

                /* A start tag token with the tag name "body" */
                case 'body':
                    /* Parse error. If the second element on the stack of open
                    elements is not a body element, or, if the stack of open
                    elements has only one node on it, then ignore the token.
                    (fragment case) */
                    if(count($this->stack) === 1 || $this->stack[1]->tagName !== 'body') {
                        $this->ignored = true;
                        // Ignore

                    /* Otherwise, for each attribute on the token, check to see
                    if the attribute is already present on the body element (the
                    second element)    on the stack of open elements. If it is not,
                    add the attribute and its corresponding value to that
                    element. */
                    } else {
                        foreach($token['attr'] as $attr) {
                            if(!$this->stack[1]->hasAttribute($attr['name'])) {
                                $this->stack[1]->setAttribute($attr['name'], $attr['value']);
                            }
                        }
                    }
                break;

                case 'frameset':
                    // parse error
                    /* If the second element on the stack of open elements is
                     * not a body element, or, if the stack of open elements
                     * has only one node on it, then ignore the token.
                     * (fragment case) */
                    if(count($this->stack) === 1 || $this->stack[1]->tagName !== 'body') {
                        $this->ignored = true;
                        // Ignore
                    } elseif (!$this->flag_frameset_ok) {
                        $this->ignored = true;
                        // Ignore
                    } else {
                        /* 1. Remove the second element on the stack of open 
                         * elements from its parent node, if it has one.  */
                        if($this->stack[1]->parentNode) {
                            $this->stack[1]->parentNode->removeChild($this->stack[1]);
                        }

                        /* 2. Pop all the nodes from the bottom of the stack of 
                         * open elements, from the current node up to the root 
                         * html element. */
                        array_splice($this->stack, 1);

                        $this->insertElement($token);
                        $this->mode = self::IN_FRAMESET;
                    }
                break;

                // in spec, there is a diversion here

                case 'address': case 'article': case 'aside': case 'blockquote':
                case 'center': case 'datagrid': case 'details': case 'dir':
                case 'div': case 'dl': case 'fieldset': case 'figure': case 'footer':
                case 'header': case 'hgroup': case 'menu': case 'nav':
                case 'ol': case 'p': case 'section': case 'ul':
                    /* If the stack of open elements has a p element in scope,
                    then act as if an end tag with the tag name p had been
                    seen. */
                    if($this->elementInScope('p')) {
                        $this->emitToken(array(
                            'name' => 'p',
                            'type' => HTML5_Tokenizer::ENDTAG
                        ));
                    }

                    /* Insert an HTML element for the token. */
                    $this->insertElement($token);
                break;

                /* A start tag whose tag name is one of: "h1", "h2", "h3", "h4",
                "h5", "h6" */
                case 'h1': case 'h2': case 'h3': case 'h4': case 'h5': case 'h6':
                    /* If the stack of open elements has a p  element in scope,
                    then act as if an end tag with the tag name p had been seen. */
                    if($this->elementInScope('p')) {
                        $this->emitToken(array(
                            'name' => 'p',
                            'type' => HTML5_Tokenizer::ENDTAG
                        ));
                    }

                    /* If the current node is an element whose tag name is one
                     * of "h1", "h2", "h3", "h4", "h5", or "h6", then this is a
                     * parse error; pop the current node off the stack of open
                     * elements. */
                    $peek = array_pop($this->stack);
                    if (in_array($peek->tagName, array("h1", "h2", "h3", "h4", "h5", "h6"))) {
                        // parse error
                    } else {
                        $this->stack[] = $peek;
                    }

                    /* Insert an HTML element for the token. */
                    $this->insertElement($token);
                break;

                case 'pre': case 'listing':
                    /* If the stack of open elements has a p  element in scope,
                    then act as if an end tag with the tag name p had been seen. */
                    if($this->elementInScope('p')) {
                        $this->emitToken(array(
                            'name' => 'p',
                            'type' => HTML5_Tokenizer::ENDTAG
                        ));
                    }
                    $this->insertElement($token);
                    /* If the next token is a U+000A LINE FEED (LF) character
                     * token, then ignore that token and move on to the next
                     * one. (Newlines at the start of pre blocks are ignored as
                     * an authoring convenience.) */
                    $this->ignore_lf_token = 2;
                    $this->flag_frameset_ok = false;
                break;

                /* A start tag whose tag name is "form" */
                case 'form':
                    /* If the form element pointer is not null, ignore the
                    token with a parse error. */
                    if($this->form_pointer !== null) {
                        $this->ignored = true;
                        // Ignore.

                    /* Otherwise: */
                    } else {
                        /* If the stack of open elements has a p element in
                        scope, then act as if an end tag with the tag name p
                        had been seen. */
                        if($this->elementInScope('p')) {
                            $this->emitToken(array(
                                'name' => 'p',
                                'type' => HTML5_Tokenizer::ENDTAG
                            ));
                        }

                        /* Insert an HTML element for the token, and set the
                        form element pointer to point to the element created. */
                        $element = $this->insertElement($token);
                        $this->form_pointer = $element;
                    }
                break;

                // condensed specification
                case 'li': case 'dc': case 'dd': case 'ds': case 'dt':
                    /* 1. Set the frameset-ok flag to "not ok". */
                    $this->flag_frameset_ok = false;

                    $stack_length = count($this->stack) - 1;
                    for($n = $stack_length; 0 <= $n; $n--) {
                        /* 2. Initialise node to be the current node (the
                        bottommost node of the stack). */
                        $stop = false;
                        $node = $this->stack[$n];
                        $cat  = $this->getElementCategory($node);

                        // for case 'li':
                        /* 3. If node is an li element, then act as if an end
                         * tag with the tag name "li" had been seen, then jump
                         * to the last step.  */
                        // for case 'dc': case 'dd': case 'ds': case 'dt':
                        /* If node is a dc, dd, ds or dt element, then act as if an end
                         * tag with the same tag name as node had been seen, then
                         * jump to the last step. */
                        if(($token['name'] === 'li' && $node->tagName === 'li') ||
                        ($token['name'] !== 'li' && ($node->tagName == 'dc' || $node->tagName === 'dd' || $node->tagName == 'ds' || $node->tagName === 'dt'))) { // limited conditional
                            $this->emitToken(array(
                                'type' => HTML5_Tokenizer::ENDTAG,
                                'name' => $node->tagName,
                            ));
                            break;
                        }

                        /* 4. If node is not in the formatting category, and is
                        not    in the phrasing category, and is not an address,
                        div or p element, then stop this algorithm. */
                        if($cat !== self::FORMATTING && $cat !== self::PHRASING &&
                        $node->tagName !== 'address' && $node->tagName !== 'div' &&
                        $node->tagName !== 'p') {
                            break;
                        }

                        /* 5. Otherwise, set node to the previous entry in the
                         * stack of open elements and return to step 2. */
                    }

                    /* 6. This is the last step. */

                    /* If the stack of open elements has a p  element in scope,
                    then act as if an end tag with the tag name p had been
                    seen. */
                    if($this->elementInScope('p')) {
                        $this->emitToken(array(
                            'name' => 'p',
                            'type' => HTML5_Tokenizer::ENDTAG
                        ));
                    }

                    /* Finally, insert an HTML element with the same tag
                    name as the    token's. */
                    $this->insertElement($token);
                break;

                /* A start tag token whose tag name is "plaintext" */
                case 'plaintext':
                    /* If the stack of open elements has a p  element in scope,
                    then act as if an end tag with the tag name p had been
                    seen. */
                    if($this->elementInScope('p')) {
                        $this->emitToken(array(
                            'name' => 'p',
                            'type' => HTML5_Tokenizer::ENDTAG
                        ));
                    }

                    /* Insert an HTML element for the token. */
                    $this->insertElement($token);

                    $this->content_model = HTML5_Tokenizer::PLAINTEXT;
                break;

                // more diversions

                /* A start tag whose tag name is "a" */
                case 'a':
                    /* If the list of active formatting elements contains
                    an element whose tag name is "a" between the end of the
                    list and the last marker on the list (or the start of
                    the list if there is no marker on the list), then this
                    is a parse error; act as if an end tag with the tag name
                    "a" had been seen, then remove that element from the list
                    of active formatting elements and the stack of open
                    elements if the end tag didn't already remove it (it
                    might not have if the element is not in table scope). */
                    $leng = count($this->a_formatting);

                    for($n = $leng - 1; $n >= 0; $n--) {
                        if($this->a_formatting[$n] === self::MARKER) {
                            break;

                        } elseif($this->a_formatting[$n]->tagName === 'a') {
                            $a = $this->a_formatting[$n];
                            $this->emitToken(array(
                                'name' => 'a',
                                'type' => HTML5_Tokenizer::ENDTAG
                            ));
                            if (in_array($a, $this->a_formatting)) {
                                $a_i = array_search($a, $this->a_formatting, true);
                                if($a_i !== false) array_splice($this->a_formatting, $a_i, 1);
                            }
                            if (in_array($a, $this->stack)) {
                                $a_i = array_search($a, $this->stack, true);
                                if ($a_i !== false) array_splice($this->stack, $a_i, 1);
                            }
                            break;
                        }
                    }

                    /* Reconstruct the active formatting elements, if any. */
                    $this->reconstructActiveFormattingElements();

                    /* Insert an HTML element for the token. */
                    $el = $this->insertElement($token);

                    /* Add that element to the list of active formatting
                    elements. */
                    $this->a_formatting[] = $el;
                break;

                case 'b': case 'big': case 'code': case 'em': case 'font': case 'i':
                case 's': case 'small': case 'strike':
                case 'strong': case 'tt': case 'u':
                    /* Reconstruct the active formatting elements, if any. */
                    $this->reconstructActiveFormattingElements();

                    /* Insert an HTML element for the token. */
                    $el = $this->insertElement($token);

                    /* Add that element to the list of active formatting
                    elements. */
                    $this->a_formatting[] = $el;
                break;

                case 'nobr':
                    /* Reconstruct the active formatting elements, if any. */
                    $this->reconstructActiveFormattingElements();

                    /* If the stack of open elements has a nobr element in
                     * scope, then this is a parse error; act as if an end tag
                     * with the tag name "nobr" had been seen, then once again
                     * reconstruct the active formatting elements, if any. */
                    if ($this->elementInScope('nobr')) {
                        $this->emitToken(array(
                            'name' => 'nobr',
                            'type' => HTML5_Tokenizer::ENDTAG,
                        ));
                        $this->reconstructActiveFormattingElements();
                    }

                    /* Insert an HTML element for the token. */
                    $el = $this->insertElement($token);

                    /* Add that element to the list of active formatting
                    elements. */
                    $this->a_formatting[] = $el;
                break;

                // another diversion

                /* A start tag token whose tag name is "button" */
                case 'button':
                    /* If the stack of open elements has a button element in scope,
                    then this is a parse error; act as if an end tag with the tag
                    name "button" had been seen, then reprocess the token. (We don't
                    do that. Unnecessary.) (I hope you're right! -- ezyang) */
                    if($this->elementInScope('button')) {
                        $this->emitToken(array(
                            'name' => 'button',
                            'type' => HTML5_Tokenizer::ENDTAG
                        ));
                    }

                    /* Reconstruct the active formatting elements, if any. */
                    $this->reconstructActiveFormattingElements();

                    /* Insert an HTML element for the token. */
                    $this->insertElement($token);

                    /* Insert a marker at the end of the list of active
                    formatting elements. */
                    $this->a_formatting[] = self::MARKER;

                    $this->flag_frameset_ok = false;
                break;

                case 'applet': case 'marquee': case 'object':
                    /* Reconstruct the active formatting elements, if any. */
                    $this->reconstructActiveFormattingElements();

                    /* Insert an HTML element for the token. */
                    $this->insertElement($token);

                    /* Insert a marker at the end of the list of active
                    formatting elements. */
                    $this->a_formatting[] = self::MARKER;

                    $this->flag_frameset_ok = false;
                break;

                // spec diversion

                /* A start tag whose tag name is "table" */
                case 'table':
                    /* If the Document is not set to quirks mode, and the 
                     * stack of open elements has a p element in scope, then 
                     * act as if an end tag with the tag name "p" had been 
                     * seen. */
                    if($this->quirks_mode !== self::QUIRKS_MODE &&
                    $this->elementInScope('p')) {
                        $this->emitToken(array(
                            'name' => 'p',
                            'type' => HTML5_Tokenizer::ENDTAG
                        ));
                    }

                    /* Insert an HTML element for the token. */
                    $this->insertElement($token);

                    $this->flag_frameset_ok = false;

                    /* Change the insertion mode to "in table". */
                    $this->mode = self::IN_TABLE;
                break;

                /* A start tag whose tag name is one of: "area", "basefont",
                "bgsound", "br", "embed", "img", "param", "spacer", "wbr" */
                case 'area': case 'basefont': case 'bgsound': case 'br':
                case 'embed': case 'img': case 'input': case 'keygen': case 'spacer':
                case 'wbr':
                    /* Reconstruct the active formatting elements, if any. */
                    $this->reconstructActiveFormattingElements();

                    /* Insert an HTML element for the token. */
                    $this->insertElement($token);

                    /* Immediately pop the current node off the stack of open elements. */
                    array_pop($this->stack);

                    // YYY: Acknowledge the token's self-closing flag, if it is set.

                    $this->flag_frameset_ok = false;
                break;

                case 'param': case 'source':
                    /* Insert an HTML element for the token. */
                    $this->insertElement($token);

                    /* Immediately pop the current node off the stack of open elements. */
                    array_pop($this->stack);

                    // YYY: Acknowledge the token's self-closing flag, if it is set.
                break;

                /* A start tag whose tag name is "hr" */
                case 'hr':
                    /* If the stack of open elements has a p element in scope,
                    then act as if an end tag with the tag name p had been seen. */
                    if($this->elementInScope('p')) {
                        $this->emitToken(array(
                            'name' => 'p',
                            'type' => HTML5_Tokenizer::ENDTAG
                        ));
                    }

                    /* Insert an HTML element for the token. */
                    $this->insertElement($token);

                    /* Immediately pop the current node off the stack of open elements. */
                    array_pop($this->stack);

                    // YYY: Acknowledge the token's self-closing flag, if it is set.

                    $this->flag_frameset_ok = false;
                break;

                /* A start tag whose tag name is "image" */
                case 'image':
                    /* Parse error. Change the token's tag name to "img" and
                    reprocess it. (Don't ask.) */
                    $token['name'] = 'img';
                    $this->emitToken($token);
                break;

                /* A start tag whose tag name is "isindex" */
                case 'isindex':
                    /* Parse error. */

                    /* If the form element pointer is not null,
                    then ignore the token. */
                    if($this->form_pointer === null) {
                        /* Act as if a start tag token with the tag name "form" had
                        been seen. */
                        /* If the token has an attribute called "action", set
                         * the action attribute on the resulting form
                         * element to the value of the "action" attribute of
                         * the token. */
                        $attr = array();
                        $action = $this->getAttr($token, 'action');
                        if ($action !== false) {
                            $attr[] = array('name' => 'action', 'value' => $action);
                        }
                        $this->emitToken(array(
                            'name' => 'form',
                            'type' => HTML5_Tokenizer::STARTTAG,
                            'attr' => $attr
                        ));

                        /* Act as if a start tag token with the tag name "hr" had
                        been seen. */
                        $this->emitToken(array(
                            'name' => 'hr',
                            'type' => HTML5_Tokenizer::STARTTAG,
                            'attr' => array()
                        ));

                        /* Act as if a start tag token with the tag name "label"
                        had been seen. */
                        $this->emitToken(array(
                            'name' => 'label',
                            'type' => HTML5_Tokenizer::STARTTAG,
                            'attr' => array()
                        ));

                        /* Act as if a stream of character tokens had been seen. */
                        $prompt = $this->getAttr($token, 'prompt');
                        if ($prompt === false) {
                            $prompt = 'This is a searchable index. '.
                            'Insert your search keywords here: ';
                        }
                        $this->emitToken(array(
                            'data' => $prompt,
                            'type' => HTML5_Tokenizer::CHARACTER,
                        ));

                        /* Act as if a start tag token with the tag name "input"
                        had been seen, with all the attributes from the "isindex"
                        token, except with the "name" attribute set to the value
                        "isindex" (ignoring any explicit "name" attribute). */
                        $attr = array();
                        foreach ($token['attr'] as $keypair) {
                            if ($keypair['name'] === 'name' || $keypair['name'] === 'action' ||
                                $keypair['name'] === 'prompt') continue;
                            $attr[] = $keypair;
                        }
                        $attr[] = array('name' => 'name', 'value' => 'isindex');

                        $this->emitToken(array(
                            'name' => 'input',
                            'type' => HTML5_Tokenizer::STARTTAG,
                            'attr' => $attr
                        ));

                        /* Act as if an end tag token with the tag name "label"
                        had been seen. */
                        $this->emitToken(array(
                            'name' => 'label',
                            'type' => HTML5_Tokenizer::ENDTAG
                        ));

                        /* Act as if a start tag token with the tag name "hr" had
                        been seen. */
                        $this->emitToken(array(
                            'name' => 'hr',
                            'type' => HTML5_Tokenizer::STARTTAG
                        ));

                        /* Act as if an end tag token with the tag name "form" had
                        been seen. */
                        $this->emitToken(array(
                            'name' => 'form',
                            'type' => HTML5_Tokenizer::ENDTAG
                        ));
                    } else {
                        $this->ignored = true;
                    }
                break;

                /* A start tag whose tag name is "textarea" */
                case 'textarea':
                    $this->insertElement($token);

                    /* If the next token is a U+000A LINE FEED (LF)
                     * character token, then ignore that token and move on to
                     * the next one. (Newlines at the start of textarea
                     * elements are ignored as an authoring convenience.)
                     * need flag, see also <pre> */
                    $this->ignore_lf_token = 2;

                    $this->original_mode = $this->mode;
                    $this->flag_frameset_ok = false;
                    $this->mode = self::IN_CDATA_RCDATA;

                    /* Switch the tokeniser's content model flag to the
                    RCDATA state. */
                    $this->content_model = HTML5_Tokenizer::RCDATA;
                break;

                /* A start tag token whose tag name is "xmp" */
                case 'xmp':
                    /* If the stack of open elements has a p element in
                    scope, then act as if an end tag with the tag name
                    "p" has been seen. */
                    if ($this->elementInScope('p')) {
                        $this->emitToken(array(
                            'name' => 'p',
                            'type' => HTML5_Tokenizer::ENDTAG
                        ));
                    }

                    /* Reconstruct the active formatting elements, if any. */
                    $this->reconstructActiveFormattingElements();

                    $this->flag_frameset_ok = false;

                    $this->insertCDATAElement($token);
                break;

                case 'iframe':
                    $this->flag_frameset_ok = false;
                    $this->insertCDATAElement($token);
                break;

                case 'noembed': case 'noscript':
                    // XSCRIPT: should check scripting flag
                    $this->insertCDATAElement($token);
                break;

                /* A start tag whose tag name is "select" */
                case 'select':
                    /* Reconstruct the active formatting elements, if any. */
                    $this->reconstructActiveFormattingElements();

                    /* Insert an HTML element for the token. */
                    $this->insertElement($token);

                    $this->flag_frameset_ok = false;

                    /* If the insertion mode is one of in table", "in caption",
                     * "in column group", "in table body", "in row", or "in
                     * cell", then switch the insertion mode to "in select in
                     * table". Otherwise, switch the insertion mode  to "in
                     * select". */
                    if (
                        $this->mode === self::IN_TABLE || $this->mode === self::IN_CAPTION ||
                        $this->mode === self::IN_COLUMN_GROUP || $this->mode ==+self::IN_TABLE_BODY ||
                        $this->mode === self::IN_ROW || $this->mode === self::IN_CELL
                    ) {
                        $this->mode = self::IN_SELECT_IN_TABLE;
                    } else {
                        $this->mode = self::IN_SELECT;
                    }
                break;

                case 'option': case 'optgroup':
                    if ($this->elementInScope('option')) {
                        $this->emitToken(array(
                            'name' => 'option',
                            'type' => HTML5_Tokenizer::ENDTAG,
                        ));
                    }
                    $this->reconstructActiveFormattingElements();
                    $this->insertElement($token);
                break;

                case 'rp': case 'rt':
                    /* If the stack of open elements has a ruby element in scope, then generate
                     * implied end tags. If the current node is not then a ruby element, this is
                     * a parse error; pop all the nodes from the current node up to the node
                     * immediately before the bottommost ruby element on the stack of open elements.
                     */
                    if ($this->elementInScope('ruby')) {
                        $this->generateImpliedEndTags();
                    }
                    $peek = false;
                    do {
                        if ($peek) {
                            // parse error
                        }
                        $peek = array_pop($this->stack);
                    } while ($peek->tagName !== 'ruby');
                    $this->stack[] = $peek; // we popped one too many
                    $this->insertElement($token);
                break;

                // spec diversion

                case 'math':
                    $this->reconstructActiveFormattingElements();
                    $token = $this->adjustMathMLAttributes($token);
                    $token = $this->adjustForeignAttributes($token);
                    $this->insertForeignElement($token, self::NS_MATHML);
                    if (isset($token['self-closing'])) {
                        // XERROR: acknowledge the token's self-closing flag
                        array_pop($this->stack);
                    }
                    if ($this->mode !== self::IN_FOREIGN_CONTENT) {
                        $this->secondary_mode = $this->mode;
                        $this->mode = self::IN_FOREIGN_CONTENT;
                    }
                break;

                case 'svg':
                    $this->reconstructActiveFormattingElements();
                    $token = $this->adjustSVGAttributes($token);
                    $token = $this->adjustForeignAttributes($token);
                    $this->insertForeignElement($token, self::NS_SVG);
                    if (isset($token['self-closing'])) {
                        // XERROR: acknowledge the token's self-closing flag
                        array_pop($this->stack);
                    }
                    if ($this->mode !== self::IN_FOREIGN_CONTENT) {
                        $this->secondary_mode = $this->mode;
                        $this->mode = self::IN_FOREIGN_CONTENT;
                    }
                break;

                case 'caption': case 'col': case 'colgroup': case 'frame': case 'head':
                case 'tbody': case 'td': case 'tfoot': case 'th': case 'thead': case 'tr':
                    // parse error
                break;

                /* A start tag token not covered by the previous entries */
                default:
                    /* Reconstruct the active formatting elements, if any. */
                    $this->reconstructActiveFormattingElements();

                    $this->insertElement($token);
                    /* This element will be a phrasing  element. */
                break;
            }
            break;

            case HTML5_Tokenizer::ENDTAG:
            switch($token['name']) {
                /* An end tag with the tag name "body" */
                case 'body':
                    /* If the stack of open elements does not have a body 
                     * element in scope, this is a parse error; ignore the 
                     * token. */
                    if(!$this->elementInScope('body')) {
                        $this->ignored = true;

                    /* Otherwise, if there is a node in the stack of open 
                     * elements that is not either a dc element, a dd element, 
                     * a ds element, a dt element, an li element, an optgroup 
                     * element, an option element, a p element, an rp element, 
                     * an rt element, a tbody element, a td element, a tfoot 
                     * element, a th element, a thead element, a tr element, 
                     * the body element, or the html element, then this is a 
                     * parse error.
                     */
                    } else {
                        // XERROR: implement this check for parse error
                    }

                    /* Change the insertion mode to "after body". */
                    $this->mode = self::AFTER_BODY;
                break;

                /* An end tag with the tag name "html" */
                case 'html':
                    /* Act as if an end tag with tag name "body" had been seen,
                    then, if that token wasn't ignored, reprocess the current
                    token. */
                    $this->emitToken(array(
                        'name' => 'body',
                        'type' => HTML5_Tokenizer::ENDTAG
                    ));

                    if (!$this->ignored) $this->emitToken($token);
                break;

                case 'address': case 'article': case 'aside': case 'blockquote':
                case 'center': case 'datagrid': case 'details': case 'dir':
                case 'div': case 'dl': case 'fieldset': case 'footer':
                case 'header': case 'hgroup': case 'listing': case 'menu':
                case 'nav': case 'ol': case 'pre': case 'section': case 'ul':
                    /* If the stack of open elements has an element in scope
                    with the same tag name as that of the token, then generate
                    implied end tags. */
                    if($this->elementInScope($token['name'])) {
                        $this->generateImpliedEndTags();

                        /* Now, if the current node is not an element with
                        the same tag name as that of the token, then this
                        is a parse error. */
                        // XERROR: implement parse error logic

                        /* If the stack of open elements has an element in
                        scope with the same tag name as that of the token,
                        then pop elements from this stack until an element
                        with that tag name has been popped from the stack. */
                        do {
                            $node = array_pop($this->stack);
                        } while ($node->tagName !== $token['name']);
                    } else {
                        // parse error
                    }
                break;

                /* An end tag whose tag name is "form" */
                case 'form':
                    /* Let node be the element that the form element pointer is set to. */
                    $node = $this->form_pointer;
                    /* Set the form element pointer  to null. */
                    $this->form_pointer = null;
                    /* If node is null or the stack of open elements does not 
                        * have node in scope, then this is a parse error; ignore the token. */
                    if ($node === null || !in_array($node, $this->stack)) {
                        // parse error
                        $this->ignored = true;
                    } else {
                        /* 1. Generate implied end tags. */
                        $this->generateImpliedEndTags();
                        /* 2. If the current node is not node, then this is a parse error.  */
                        if (end($this->stack) !== $node) {
                            // parse error
                        }
                        /* 3. Remove node from the stack of open elements. */
                        array_splice($this->stack, array_search($node, $this->stack, true), 1);
                    }

                break;

                /* An end tag whose tag name is "p" */
                case 'p':
                    /* If the stack of open elements has a p element in scope,
                    then generate implied end tags, except for p elements. */
                    if($this->elementInScope('p')) {
                        /* Generate implied end tags, except for elements with
                         * the same tag name as the token. */
                        $this->generateImpliedEndTags(array('p'));

                        /* If the current node is not a p element, then this is
                        a parse error. */
                        // XERROR: implement

                        /* Pop elements from the stack of open elements  until
                         * an element with the same tag name as the token has
                         * been popped from the stack. */
                        do {
                            $node = array_pop($this->stack);
                        } while ($node->tagName !== 'p');

                    } else {
                        // parse error
                        $this->emitToken(array(
                            'name' => 'p',
                            'type' => HTML5_Tokenizer::STARTTAG,
                        ));
                        $this->emitToken($token);
                    }
                break;

                /* An end tag whose tag name is "li" */
                case 'li':
                    /* If the stack of open elements does not have an element
                     * in list item scope with the same tag name as that of the
                     * token, then this is a parse error; ignore the token. */
                    if ($this->elementInScope($token['name'], self::SCOPE_LISTITEM)) {
                        /* Generate implied end tags, except for elements with the
                         * same tag name as the token. */
                        $this->generateImpliedEndTags(array($token['name']));
                        /* If the current node is not an element with the same tag
                         * name as that of the token, then this is a parse error. */
                        // XERROR: parse error
                        /* Pop elements from the stack of open elements  until an
                         * element with the same tag name as the token has been
                         * popped from the stack. */
                        do {
                            $node = array_pop($this->stack);
                        } while ($node->tagName !== $token['name']);
                    } else {
                        // XERROR: parse error
                    }
                break;

                /* An end tag whose tag name is "dc", "dd", "ds", "dt" */
                case 'dc': case 'dd': case 'ds': case 'dt':
                    if($this->elementInScope($token['name'])) {
                        $this->generateImpliedEndTags(array($token['name']));

                        /* If the current node is not an element with the same
                        tag name as the token, then this is a parse error. */
                        // XERROR: implement parse error

                        /* Pop elements from the stack of open elements  until
                         * an element with the same tag name as the token has
                         * been popped from the stack. */
                        do {
                            $node = array_pop($this->stack);
                        } while ($node->tagName !== $token['name']);

                    } else {
                        // XERROR: parse error
                    }
                break;

                /* An end tag whose tag name is one of: "h1", "h2", "h3", "h4",
                "h5", "h6" */
                case 'h1': case 'h2': case 'h3': case 'h4': case 'h5': case 'h6':
                    $elements = array('h1', 'h2', 'h3', 'h4', 'h5', 'h6');

                    /* If the stack of open elements has in scope an element whose
                    tag name is one of "h1", "h2", "h3", "h4", "h5", or "h6", then
                    generate implied end tags. */
                    if($this->elementInScope($elements)) {
                        $this->generateImpliedEndTags();

                        /* Now, if the current node is not an element with the same
                        tag name as that of the token, then this is a parse error. */
                        // XERROR: implement parse error

                        /* If the stack of open elements has in scope an element
                        whose tag name is one of "h1", "h2", "h3", "h4", "h5", or
                        "h6", then pop elements from the stack until an element
                        with one of those tag names has been popped from the stack. */
                        do {
                            $node = array_pop($this->stack);
                        } while (!in_array($node->tagName, $elements));
                    } else {
                        // parse error
                    }
                break;

                /* An end tag whose tag name is one of: "a", "b", "big", "em",
                "font", "i", "nobr", "s", "small", "strike", "strong", "tt", "u" */
                case 'a': case 'b': case 'big': case 'code': case 'em': case 'font':
                case 'i': case 'nobr': case 's': case 'small': case 'strike':
                case 'strong': case 'tt': case 'u':
                    // XERROR: generally speaking this needs parse error logic
                    /* 1. Let the formatting element be the last element in
                    the list of active formatting elements that:
                        * is between the end of the list and the last scope
                        marker in the list, if any, or the start of the list
                        otherwise, and
                        * has the same tag name as the token.
                    */
                    while(true) {
                        for($a = count($this->a_formatting) - 1; $a >= 0; $a--) {
                            if($this->a_formatting[$a] === self::MARKER) {
                                break;

                            } elseif($this->a_formatting[$a]->tagName === $token['name']) {
                                $formatting_element = $this->a_formatting[$a];
                                $in_stack = in_array($formatting_element, $this->stack, true);
                                $fe_af_pos = $a;
                                break;
                            }
                        }

                        /* If there is no such node, or, if that node is
                        also in the stack of open elements but the element
                        is not in scope, then this is a parse error. Abort
                        these steps. The token is ignored. */
                        if(!isset($formatting_element) || ($in_stack &&
                        !$this->elementInScope($token['name']))) {
                            $this->ignored = true;
                            break;

                        /* Otherwise, if there is such a node, but that node
                        is not in the stack of open elements, then this is a
                        parse error; remove the element from the list, and
                        abort these steps. */
                        } elseif(isset($formatting_element) && !$in_stack) {
                            unset($this->a_formatting[$fe_af_pos]);
                            $this->a_formatting = array_merge($this->a_formatting);
                            break;
                        }

                        /* Otherwise, there is a formatting element and that
                         * element is in the stack and is in scope. If the
                         * element is not the current node, this is a parse
                         * error. In any case, proceed with the algorithm as
                         * written in the following steps. */
                        // XERROR: implement me

                        /* 2. Let the furthest block be the topmost node in the
                        stack of open elements that is lower in the stack
                        than the formatting element, and is not an element in
                        the phrasing or formatting categories. There might
                        not be one. */
                        $fe_s_pos = array_search($formatting_element, $this->stack, true);
                        $length = count($this->stack);

                        for($s = $fe_s_pos + 1; $s < $length; $s++) {
                            $category = $this->getElementCategory($this->stack[$s]);

                            if($category !== self::PHRASING && $category !== self::FORMATTING) {
                                $furthest_block = $this->stack[$s];
                                break;
                            }
                        }

                        /* 3. If there is no furthest block, then the UA must
                        skip the subsequent steps and instead just pop all
                        the nodes from the bottom of the stack of open
                        elements, from the current node up to the formatting
                        element, and remove the formatting element from the
                        list of active formatting elements. */
                        if(!isset($furthest_block)) {
                            for($n = $length - 1; $n >= $fe_s_pos; $n--) {
                                array_pop($this->stack);
                            }

                            unset($this->a_formatting[$fe_af_pos]);
                            $this->a_formatting = array_merge($this->a_formatting);
                            break;
                        }

                        /* 4. Let the common ancestor be the element
                        immediately above the formatting element in the stack
                        of open elements. */
                        $common_ancestor = $this->stack[$fe_s_pos - 1];

                        /* 5. Let a bookmark note the position of the
                        formatting element in the list of active formatting
                        elements relative to the elements on either side
                        of it in the list. */
                        $bookmark = $fe_af_pos;

                        /* 6. Let node and last node  be the furthest block.
                        Follow these steps: */
                        $node = $furthest_block;
                        $last_node = $furthest_block;

                        while(true) {
                            for($n = array_search($node, $this->stack, true) - 1; $n >= 0; $n--) {
                                /* 6.1 Let node be the element immediately
                                prior to node in the stack of open elements. */
                                $node = $this->stack[$n];

                                /* 6.2 If node is not in the list of active
                                formatting elements, then remove node from
                                the stack of open elements and then go back
                                to step 1. */
                                if(!in_array($node, $this->a_formatting, true)) {
                                    array_splice($this->stack, $n, 1);

                                } else {
                                    break;
                                }
                            }

                            /* 6.3 Otherwise, if node is the formatting
                            element, then go to the next step in the overall
                            algorithm. */
                            if($node === $formatting_element) {
                                break;

                            /* 6.4 Otherwise, if last node is the furthest
                            block, then move the aforementioned bookmark to
                            be immediately after the node in the list of
                            active formatting elements. */
                            } elseif($last_node === $furthest_block) {
                                $bookmark = array_search($node, $this->a_formatting, true) + 1;
                            }

                            /* 6.5 Create an element for the token for which
                             * the element node was created, replace the entry
                             * for node in the list of active formatting
                             * elements with an entry for the new element,
                             * replace the entry for node in the stack of open
                             * elements with an entry for the new element, and
                             * let node be the new element. */
                            // we don't know what the token is anymore
                            // XDOM
                            $clone = $node->cloneNode();
                            $a_pos = array_search($node, $this->a_formatting, true);
                            $s_pos = array_search($node, $this->stack, true);
                            $this->a_formatting[$a_pos] = $clone;
                            $this->stack[$s_pos] = $clone;
                            $node = $clone;

                            /* 6.6 Insert last node into node, first removing
                            it from its previous parent node if any. */
                            // XDOM
                            if($last_node->parentNode !== null) {
                                $last_node->parentNode->removeChild($last_node);
                            }

                            // XDOM
                            $node->appendChild($last_node);

                            /* 6.7 Let last node be node. */
                            $last_node = $node;

                            /* 6.8 Return to step 1 of this inner set of steps. */
                        }

                        /* 7. If the common ancestor node is a table, tbody,
                         * tfoot, thead, or tr element, then, foster parent
                         * whatever last node ended up being in the previous
                         * step, first removing it from its previous parent
                         * node if any. */
                        // XDOM
                        if ($last_node->parentNode) { // common step
                            $last_node->parentNode->removeChild($last_node);
                        }
                        if (in_array($common_ancestor->tagName, array('table', 'tbody', 'tfoot', 'thead', 'tr'))) {
                            $this->fosterParent($last_node);
                        /* Otherwise, append whatever last node  ended up being
                         * in the previous step to the common ancestor node,
                         * first removing it from its previous parent node if
                         * any. */
                        } else {
                            // XDOM
                            $common_ancestor->appendChild($last_node);
                        }

                        /* 8. Create an element for the token for which the
                         * formatting element was created. */
                        // XDOM
                        $clone = $formatting_element->cloneNode();

                        /* 9. Take all of the child nodes of the furthest
                        block and append them to the element created in the
                        last step. */
                        // XDOM
                        while($furthest_block->hasChildNodes()) {
                            $child = $furthest_block->firstChild;
                            $furthest_block->removeChild($child);
                            $clone->appendChild($child);
                        }

                        /* 10. Append that clone to the furthest block. */
                        // XDOM
                        $furthest_block->appendChild($clone);

                        /* 11. Remove the formatting element from the list
                        of active formatting elements, and insert the new element
                        into the list of active formatting elements at the
                        position of the aforementioned bookmark. */
                        $fe_af_pos = array_search($formatting_element, $this->a_formatting, true);
                        array_splice($this->a_formatting, $fe_af_pos, 1);

                        $af_part1 = array_slice($this->a_formatting, 0, $bookmark - 1);
                        $af_part2 = array_slice($this->a_formatting, $bookmark);
                        $this->a_formatting = array_merge($af_part1, array($clone), $af_part2);

                        /* 12. Remove the formatting element from the stack
                        of open elements, and insert the new element into the stack
                        of open elements immediately below the position of the
                        furthest block in that stack. */
                        $fe_s_pos = array_search($formatting_element, $this->stack, true);
                        array_splice($this->stack, $fe_s_pos, 1);

                        $fb_s_pos = array_search($furthest_block, $this->stack, true);
                        $s_part1 = array_slice($this->stack, 0, $fb_s_pos + 1);
                        $s_part2 = array_slice($this->stack, $fb_s_pos + 1);
                        $this->stack = array_merge($s_part1, array($clone), $s_part2);

                        /* 13. Jump back to step 1 in this series of steps. */
                        unset($formatting_element, $fe_af_pos, $fe_s_pos, $furthest_block);
                    }
                break;

                case 'applet': case 'button': case 'marquee': case 'object':
                    /* If the stack of open elements has an element in scope whose
                    tag name matches the tag name of the token, then generate implied
                    tags. */
                    if($this->elementInScope($token['name'])) {
                        $this->generateImpliedEndTags();

                        /* Now, if the current node is not an element with the same
                        tag name as the token, then this is a parse error. */
                        // XERROR: implement logic

                        /* Pop elements from the stack of open elements  until
                         * an element with the same tag name as the token has
                         * been popped from the stack. */
                        do {
                            $node = array_pop($this->stack);
                        } while ($node->tagName !== $token['name']);

                        /* Clear the list of active formatting elements up to the
                         * last marker. */
                        $keys = array_keys($this->a_formatting, self::MARKER, true);
                        $marker = end($keys);

                        for($n = count($this->a_formatting) - 1; $n > $marker; $n--) {
                            array_pop($this->a_formatting);
                        }
                    } else {
                        // parse error
                    }
                break;

                case 'br':
                    // Parse error
                    $this->emitToken(array(
                        'name' => 'br',
                        'type' => HTML5_Tokenizer::STARTTAG,
                    ));
                break;

                /* An end tag token not covered by the previous entries */
                default:
                    for($n = count($this->stack) - 1; $n >= 0; $n--) {
                        /* Initialise node to be the current node (the bottommost
                        node of the stack). */
                        $node = $this->stack[$n];

                        /* If node has the same tag name as the end tag token,
                        then: */
                        if($token['name'] === $node->tagName) {
                            /* Generate implied end tags. */
                            $this->generateImpliedEndTags();

                            /* If the tag name of the end tag token does not
                            match the tag name of the current node, this is a
                            parse error. */
                            // XERROR: implement this

                            /* Pop all the nodes from the current node up to
                            node, including node, then stop these steps. */
                            // XSKETCHY
                            do {
                                $pop = array_pop($this->stack);
                            } while ($pop !== $node);
                            break;

                        } else {
                            $category = $this->getElementCategory($node);

                            if($category !== self::FORMATTING && $category !== self::PHRASING) {
                                /* Otherwise, if node is in neither the formatting
                                category nor the phrasing category, then this is a
                                parse error. Stop this algorithm. The end tag token
                                is ignored. */
                                $this->ignored = true;
                                break;
                                // parse error
                            }
                        }
                        /* Set node to the previous entry in the stack of open elements. Loop. */
                    }
                break;
            }
            break;
        }
        break;

    case self::IN_CDATA_RCDATA:
        if (
            $token['type'] === HTML5_Tokenizer::CHARACTER ||
            $token['type'] === HTML5_Tokenizer::SPACECHARACTER
        ) {
            $this->insertText($token['data']);
        } elseif ($token['type'] === HTML5_Tokenizer::EOF) {
            // parse error
            /* If the current node is a script  element, mark the script
             * element as "already executed". */
            // probably not necessary
            array_pop($this->stack);
            $this->mode = $this->original_mode;
            $this->emitToken($token);
        } elseif ($token['type'] === HTML5_Tokenizer::ENDTAG && $token['name'] === 'script') {
            array_pop($this->stack);
            $this->mode = $this->original_mode;
            // we're ignoring all of the execution stuff
        } elseif ($token['type'] === HTML5_Tokenizer::ENDTAG) {
            array_pop($this->stack);
            $this->mode = $this->original_mode;
        }
    break;

    case self::IN_TABLE:
        $clear = array('html', 'table');

        /* A character token */
        if ($token['type'] === HTML5_Tokenizer::CHARACTER ||
            $token['type'] === HTML5_Tokenizer::SPACECHARACTER) {
            /* Let the pending table character tokens
             * be an empty list of tokens. */
            $this->pendingTableCharacters = "";
            $this->pendingTableCharactersDirty = false;
            /* Let the original insertion mode be the current
             * insertion mode. */
            $this->original_mode = $this->mode;
            /* Switch the insertion mode to
             * "in table text" and
             * reprocess the token. */
            $this->mode = self::IN_TABLE_TEXT;
            $this->emitToken($token);

        /* A comment token */
        } elseif($token['type'] === HTML5_Tokenizer::COMMENT) {
            /* Append a Comment node to the current node with the data
            attribute set to the data given in the comment token. */
            $this->insertComment($token['data']);

        } elseif($token['type'] === HTML5_Tokenizer::DOCTYPE) {
            // parse error

        /* A start tag whose tag name is "caption" */
        } elseif($token['type'] === HTML5_Tokenizer::STARTTAG &&
        $token['name'] === 'caption') {
            /* Clear the stack back to a table context. */
            $this->clearStackToTableContext($clear);

            /* Insert a marker at the end of the list of active
            formatting elements. */
            $this->a_formatting[] = self::MARKER;

            /* Insert an HTML element for the token, then switch the
            insertion mode to "in caption". */
            $this->insertElement($token);
            $this->mode = self::IN_CAPTION;

        /* A start tag whose tag name is "colgroup" */
        } elseif($token['type'] === HTML5_Tokenizer::STARTTAG &&
        $token['name'] === 'colgroup') {
            /* Clear the stack back to a table context. */
            $this->clearStackToTableContext($clear);

            /* Insert an HTML element for the token, then switch the
            insertion mode to "in column group". */
            $this->insertElement($token);
            $this->mode = self::IN_COLUMN_GROUP;

        /* A start tag whose tag name is "col" */
        } elseif($token['type'] === HTML5_Tokenizer::STARTTAG &&
        $token['name'] === 'col') {
            $this->emitToken(array(
                'name' => 'colgroup',
                'type' => HTML5_Tokenizer::STARTTAG,
                'attr' => array()
            ));

            $this->emitToken($token);

        /* A start tag whose tag name is one of: "tbody", "tfoot", "thead" */
        } elseif($token['type'] === HTML5_Tokenizer::STARTTAG && in_array($token['name'],
        array('tbody', 'tfoot', 'thead'))) {
            /* Clear the stack back to a table context. */
            $this->clearStackToTableContext($clear);

            /* Insert an HTML element for the token, then switch the insertion
            mode to "in table body". */
            $this->insertElement($token);
            $this->mode = self::IN_TABLE_BODY;

        /* A start tag whose tag name is one of: "td", "th", "tr" */
        } elseif($token['type'] === HTML5_Tokenizer::STARTTAG &&
        in_array($token['name'], array('td', 'th', 'tr'))) {
            /* Act as if a start tag token with the tag name "tbody" had been
            seen, then reprocess the current token. */
            $this->emitToken(array(
                'name' => 'tbody',
                'type' => HTML5_Tokenizer::STARTTAG,
                'attr' => array()
            ));

            $this->emitToken($token);

        /* A start tag whose tag name is "table" */
        } elseif($token['type'] === HTML5_Tokenizer::STARTTAG &&
        $token['name'] === 'table') {
            /* Parse error. Act as if an end tag token with the tag name "table"
            had been seen, then, if that token wasn't ignored, reprocess the
            current token. */
            $this->emitToken(array(
                'name' => 'table',
                'type' => HTML5_Tokenizer::ENDTAG
            ));

            if (!$this->ignored) $this->emitToken($token);

        /* An end tag whose tag name is "table" */
        } elseif($token['type'] === HTML5_Tokenizer::ENDTAG &&
        $token['name'] === 'table') {
            /* If the stack of open elements does not have an element in table
            scope with the same tag name as the token, this is a parse error.
            Ignore the token. (fragment case) */
            if(!$this->elementInScope($token['name'], self::SCOPE_TABLE)) {
                $this->ignored = true;

            /* Otherwise: */
            } else {
                do {
                    $node = array_pop($this->stack);
                } while ($node->tagName !== 'table');

                /* Reset the insertion mode appropriately. */
                $this->resetInsertionMode();
            }

        /* An end tag whose tag name is one of: "body", "caption", "col",
        "colgroup", "html", "tbody", "td", "tfoot", "th", "thead", "tr" */
        } elseif($token['type'] === HTML5_Tokenizer::ENDTAG && in_array($token['name'],
        array('body', 'caption', 'col', 'colgroup', 'html', 'tbody', 'td',
        'tfoot', 'th', 'thead', 'tr'))) {
            // Parse error. Ignore the token.

        } elseif($token['type'] === HTML5_Tokenizer::STARTTAG &&
        ($token['name'] === 'style' || $token['name'] === 'script')) {
            $this->processWithRulesFor($token, self::IN_HEAD);

        } elseif ($token['type'] === HTML5_Tokenizer::STARTTAG && $token['name'] === 'input' &&
        // assignment is intentional
        /* If the token does not have an attribute with the name "type", or
         * if it does, but that attribute's value is not an ASCII
         * case-insensitive match for the string "hidden", then: act as
         * described in the "anything else" entry below. */
        ($type = $this->getAttr($token, 'type')) && strtolower($type) === 'hidden') {
            // I.e., if its an input with the type attribute == 'hidden'
            /* Otherwise */
            // parse error
            $this->insertElement($token);
            array_pop($this->stack);
        } elseif ($token['type'] === HTML5_Tokenizer::EOF) {
            /* If the current node is not the root html element, then this is a parse error. */
            if (end($this->stack)->tagName !== 'html') {
                // Note: It can only be the current node in the fragment case.
                // parse error
            }
            /* Stop parsing. */
        /* Anything else */
        } else {
            /* Parse error. Process the token as if the insertion mode was "in
            body", with the following exception: */

            $old = $this->foster_parent;
            $this->foster_parent = true;
            $this->processWithRulesFor($token, self::IN_BODY);
            $this->foster_parent = $old;
        }
    break;

    case self::IN_TABLE_TEXT:
        /* A character token */
        if($token['type'] === HTML5_Tokenizer::CHARACTER) {
            /* Append the character token to the pending table
             * character tokens list. */
            $this->pendingTableCharacters .= $token['data'];
            $this->pendingTableCharactersDirty = true;
        } elseif ($token['type'] === HTML5_Tokenizer::SPACECHARACTER) {
            $this->pendingTableCharacters .= $token['data'];
        /* Anything else */
        } else {
            if ($this->pendingTableCharacters !== '' && is_string($this->pendingTableCharacters)) {
                /* If any of the tokens in the pending table character tokens list 
                 * are character tokens that are not one of U+0009 CHARACTER 
                 * TABULATION, U+000A LINE FEED (LF), U+000C FORM FEED (FF), or 
                 * U+0020 SPACE, then reprocess those character tokens using the 
                 * rules given in the "anything else" entry in the in table" 
                 * insertion mode.*/
                if ($this->pendingTableCharactersDirty) {
                    /* Parse error. Process the token using the rules for the 
                     * "in body" insertion mode, except that if the current 
                     * node is a table, tbody, tfoot, thead, or tr element, 
                     * then, whenever a node would be inserted into the current 
                     * node, it must instead be foster parented. */
                    // XERROR
                    $old = $this->foster_parent;
                    $this->foster_parent = true;
                    $text_token = array(
                        'type' => HTML5_Tokenizer::CHARACTER,
                        'data' => $this->pendingTableCharacters,
                    );
                    $this->processWithRulesFor($text_token, self::IN_BODY);
                    $this->foster_parent = $old;

                /* Otherwise, insert the characters given by the pending table 
                 * character tokens list into the current node. */
                } else {
                    $this->insertText($this->pendingTableCharacters);
                }
                $this->pendingTableCharacters = null;
                $this->pendingTableCharactersNull = null;
            }

            /* Switch the insertion mode to the original insertion mode and 
             * reprocess the token.
             */
            $this->mode = $this->original_mode;
            $this->emitToken($token);
        }
    break;

    case self::IN_CAPTION:
        /* An end tag whose tag name is "caption" */
        if($token['type'] === HTML5_Tokenizer::ENDTAG && $token['name'] === 'caption') {
            /* If the stack of open elements does not have an element in table
            scope with the same tag name as the token, this is a parse error.
            Ignore the token. (fragment case) */
            if(!$this->elementInScope($token['name'], self::SCOPE_TABLE)) {
                $this->ignored = true;
                // Ignore

            /* Otherwise: */
            } else {
                /* Generate implied end tags. */
                $this->generateImpliedEndTags();

                /* Now, if the current node is not a caption element, then this
                is a parse error. */
                // XERROR: implement

                /* Pop elements from this stack until a caption element has
                been popped from the stack. */
                do {
                    $node = array_pop($this->stack);
                } while ($node->tagName !== 'caption');

                /* Clear the list of active formatting elements up to the last
                marker. */
                $this->clearTheActiveFormattingElementsUpToTheLastMarker();

                /* Switch the insertion mode to "in table". */
                $this->mode = self::IN_TABLE;
            }

        /* A start tag whose tag name is one of: "caption", "col", "colgroup",
        "tbody", "td", "tfoot", "th", "thead", "tr", or an end tag whose tag
        name is "table" */
        } elseif(($token['type'] === HTML5_Tokenizer::STARTTAG && in_array($token['name'],
        array('caption', 'col', 'colgroup', 'tbody', 'td', 'tfoot', 'th',
        'thead', 'tr'))) || ($token['type'] === HTML5_Tokenizer::ENDTAG &&
        $token['name'] === 'table')) {
            /* Parse error. Act as if an end tag with the tag name "caption"
            had been seen, then, if that token wasn't ignored, reprocess the
            current token. */
            $this->emitToken(array(
                'name' => 'caption',
                'type' => HTML5_Tokenizer::ENDTAG
            ));

            if (!$this->ignored) $this->emitToken($token);

        /* An end tag whose tag name is one of: "body", "col", "colgroup",
        "html", "tbody", "td", "tfoot", "th", "thead", "tr" */
        } elseif($token['type'] === HTML5_Tokenizer::ENDTAG && in_array($token['name'],
        array('body', 'col', 'colgroup', 'html', 'tbody', 'tfoot', 'th',
        'thead', 'tr'))) {
            // Parse error. Ignore the token.
            $this->ignored = true;

        /* Anything else */
        } else {
            /* Process the token as if the insertion mode was "in body". */
            $this->processWithRulesFor($token, self::IN_BODY);
        }
    break;

    case self::IN_COLUMN_GROUP:
        /* A character token that is one of one of U+0009 CHARACTER TABULATION,
        U+000A LINE FEED (LF), U+000B LINE TABULATION, U+000C FORM FEED (FF),
        or U+0020 SPACE */
        if($token['type'] === HTML5_Tokenizer::SPACECHARACTER) {
            /* Append the character to the current node. */
            $this->insertText($token['data']);

        /* A comment token */
        } elseif($token['type'] === HTML5_Tokenizer::COMMENT) {
            /* Append a Comment node to the current node with the data
            attribute set to the data given in the comment token. */
            $this->insertToken($token['data']);

        } elseif($token['type'] === HTML5_Tokenizer::DOCTYPE) {
            // parse error

        } elseif($token['type'] === HTML5_Tokenizer::STARTTAG && $token['name'] === 'html') {
            $this->processWithRulesFor($token, self::IN_BODY);

        /* A start tag whose tag name is "col" */
        } elseif($token['type'] === HTML5_Tokenizer::STARTTAG && $token['name'] === 'col') {
            /* Insert a col element for the token. Immediately pop the current
            node off the stack of open elements. */
            $this->insertElement($token);
            array_pop($this->stack);
            // XERROR: Acknowledge the token's self-closing flag, if it is set.

        /* An end tag whose tag name is "colgroup" */
        } elseif($token['type'] === HTML5_Tokenizer::ENDTAG &&
        $token['name'] === 'colgroup') {
            /* If the current node is the root html element, then this is a
            parse error, ignore the token. (fragment case) */
            if(end($this->stack)->tagName === 'html') {
                $this->ignored = true;

            /* Otherwise, pop the current node (which will be a colgroup
            element) from the stack of open elements. Switch the insertion
            mode to "in table". */
            } else {
                array_pop($this->stack);
                $this->mode = self::IN_TABLE;
            }

        /* An end tag whose tag name is "col" */
        } elseif($token['type'] === HTML5_Tokenizer::ENDTAG && $token['name'] === 'col') {
            /* Parse error. Ignore the token. */
            $this->ignored = true;

        /* An end-of-file token */
        /* If the current node is the root html  element */
        } elseif($token['type'] === HTML5_Tokenizer::EOF && end($this->stack)->tagName === 'html') {
            /* Stop parsing */

        /* Anything else */
        } else {
            /* Act as if an end tag with the tag name "colgroup" had been seen,
            and then, if that token wasn't ignored, reprocess the current token. */
            $this->emitToken(array(
                'name' => 'colgroup',
                'type' => HTML5_Tokenizer::ENDTAG
            ));

            if (!$this->ignored) $this->emitToken($token);
        }
    break;

    case self::IN_TABLE_BODY:
        $clear = array('tbody', 'tfoot', 'thead', 'html');

        /* A start tag whose tag name is "tr" */
        if($token['type'] === HTML5_Tokenizer::STARTTAG && $token['name'] === 'tr') {
            /* Clear the stack back to a table body context. */
            $this->clearStackToTableContext($clear);

            /* Insert a tr element for the token, then switch the insertion
            mode to "in row". */
            $this->insertElement($token);
            $this->mode = self::IN_ROW;

        /* A start tag whose tag name is one of: "th", "td" */
        } elseif($token['type'] === HTML5_Tokenizer::STARTTAG &&
        ($token['name'] === 'th' ||    $token['name'] === 'td')) {
            /* Parse error. Act as if a start tag with the tag name "tr" had
            been seen, then reprocess the current token. */
            $this->emitToken(array(
                'name' => 'tr',
                'type' => HTML5_Tokenizer::STARTTAG,
                'attr' => array()
            ));

            $this->emitToken($token);

        /* An end tag whose tag name is one of: "tbody", "tfoot", "thead" */
        } elseif($token['type'] === HTML5_Tokenizer::ENDTAG &&
        in_array($token['name'], array('tbody', 'tfoot', 'thead'))) {
            /* If the stack of open elements does not have an element in table
            scope with the same tag name as the token, this is a parse error.
            Ignore the token. */
            if(!$this->elementInScope($token['name'], self::SCOPE_TABLE)) {
                // Parse error
                $this->ignored = true;

            /* Otherwise: */
            } else {
                /* Clear the stack back to a table body context. */
                $this->clearStackToTableContext($clear);

                /* Pop the current node from the stack of open elements. Switch
                the insertion mode to "in table". */
                array_pop($this->stack);
                $this->mode = self::IN_TABLE;
            }

        /* A start tag whose tag name is one of: "caption", "col", "colgroup",
        "tbody", "tfoot", "thead", or an end tag whose tag name is "table" */
        } elseif(($token['type'] === HTML5_Tokenizer::STARTTAG && in_array($token['name'],
        array('caption', 'col', 'colgroup', 'tbody', 'tfoot', 'thead'))) ||
        ($token['type'] === HTML5_Tokenizer::ENDTAG && $token['name'] === 'table')) {
            /* If the stack of open elements does not have a tbody, thead, or
            tfoot element in table scope, this is a parse error. Ignore the
            token. (fragment case) */
            if(!$this->elementInScope(array('tbody', 'thead', 'tfoot'), self::SCOPE_TABLE)) {
                // parse error
                $this->ignored = true;

            /* Otherwise: */
            } else {
                /* Clear the stack back to a table body context. */
                $this->clearStackToTableContext($clear);

                /* Act as if an end tag with the same tag name as the current
                node ("tbody", "tfoot", or "thead") had been seen, then
                reprocess the current token. */
                $this->emitToken(array(
                    'name' => end($this->stack)->tagName,
                    'type' => HTML5_Tokenizer::ENDTAG
                ));

                $this->emitToken($token);
            }

        /* An end tag whose tag name is one of: "body", "caption", "col",
        "colgroup", "html", "td", "th", "tr" */
        } elseif($token['type'] === HTML5_Tokenizer::ENDTAG && in_array($token['name'],
        array('body', 'caption', 'col', 'colgroup', 'html', 'td', 'th', 'tr'))) {
            /* Parse error. Ignore the token. */
            $this->ignored = true;

        /* Anything else */
        } else {
            /* Process the token as if the insertion mode was "in table". */
            $this->processWithRulesFor($token, self::IN_TABLE);
        }
    break;

    case self::IN_ROW:
        $clear = array('tr', 'html');

        /* A start tag whose tag name is one of: "th", "td" */
        if($token['type'] === HTML5_Tokenizer::STARTTAG &&
        ($token['name'] === 'th' || $token['name'] === 'td')) {
            /* Clear the stack back to a table row context. */
            $this->clearStackToTableContext($clear);

            /* Insert an HTML element for the token, then switch the insertion
            mode to "in cell". */
            $this->insertElement($token);
            $this->mode = self::IN_CELL;

            /* Insert a marker at the end of the list of active formatting
            elements. */
            $this->a_formatting[] = self::MARKER;

        /* An end tag whose tag name is "tr" */
        } elseif($token['type'] === HTML5_Tokenizer::ENDTAG && $token['name'] === 'tr') {
            /* If the stack of open elements does not have an element in table
            scope with the same tag name as the token, this is a parse error.
            Ignore the token. (fragment case) */
            if(!$this->elementInScope($token['name'], self::SCOPE_TABLE)) {
                // Ignore.
                $this->ignored = true;

            /* Otherwise: */
            } else {
                /* Clear the stack back to a table row context. */
                $this->clearStackToTableContext($clear);

                /* Pop the current node (which will be a tr element) from the
                stack of open elements. Switch the insertion mode to "in table
                body". */
                array_pop($this->stack);
                $this->mode = self::IN_TABLE_BODY;
            }

        /* A start tag whose tag name is one of: "caption", "col", "colgroup",
        "tbody", "tfoot", "thead", "tr" or an end tag whose tag name is "table" */
        } elseif(($token['type'] === HTML5_Tokenizer::STARTTAG && in_array($token['name'],
        array('caption', 'col', 'colgroup', 'tbody', 'tfoot', 'thead', 'tr'))) ||
        ($token['type'] === HTML5_Tokenizer::ENDTAG && $token['name'] === 'table')) {
            /* Act as if an end tag with the tag name "tr" had been seen, then,
            if that token wasn't ignored, reprocess the current token. */
            $this->emitToken(array(
                'name' => 'tr',
                'type' => HTML5_Tokenizer::ENDTAG
            ));
            if (!$this->ignored) $this->emitToken($token);

        /* An end tag whose tag name is one of: "tbody", "tfoot", "thead" */
        } elseif($token['type'] === HTML5_Tokenizer::ENDTAG &&
        in_array($token['name'], array('tbody', 'tfoot', 'thead'))) {
            /* If the stack of open elements does not have an element in table
            scope with the same tag name as the token, this is a parse error.
            Ignore the token. */
            if(!$this->elementInScope($token['name'], self::SCOPE_TABLE)) {
                $this->ignored = true;

            /* Otherwise: */
            } else {
                /* Otherwise, act as if an end tag with the tag name "tr" had
                been seen, then reprocess the current token. */
                $this->emitToken(array(
                    'name' => 'tr',
                    'type' => HTML5_Tokenizer::ENDTAG
                ));

                $this->emitToken($token);
            }

        /* An end tag whose tag name is one of: "body", "caption", "col",
        "colgroup", "html", "td", "th" */
        } elseif($token['type'] === HTML5_Tokenizer::ENDTAG && in_array($token['name'],
        array('body', 'caption', 'col', 'colgroup', 'html', 'td', 'th'))) {
            /* Parse error. Ignore the token. */
            $this->ignored = true;

        /* Anything else */
        } else {
            /* Process the token as if the insertion mode was "in table". */
            $this->processWithRulesFor($token, self::IN_TABLE);
        }
    break;

    case self::IN_CELL:
        /* An end tag whose tag name is one of: "td", "th" */
        if($token['type'] === HTML5_Tokenizer::ENDTAG &&
        ($token['name'] === 'td' || $token['name'] === 'th')) {
            /* If the stack of open elements does not have an element in table
            scope with the same tag name as that of the token, then this is a
            parse error and the token must be ignored. */
            if(!$this->elementInScope($token['name'], self::SCOPE_TABLE)) {
                $this->ignored = true;

            /* Otherwise: */
            } else {
                /* Generate implied end tags, except for elements with the same
                tag name as the token. */
                $this->generateImpliedEndTags(array($token['name']));

                /* Now, if the current node is not an element with the same tag
                name as the token, then this is a parse error. */
                // XERROR: Implement parse error code

                /* Pop elements from this stack until an element with the same
                tag name as the token has been popped from the stack. */
                do {
                    $node = array_pop($this->stack);
                } while ($node->tagName !== $token['name']);

                /* Clear the list of active formatting elements up to the last
                marker. */
                $this->clearTheActiveFormattingElementsUpToTheLastMarker();

                /* Switch the insertion mode to "in row". (The current node
                will be a tr element at this point.) */
                $this->mode = self::IN_ROW;
            }

        /* A start tag whose tag name is one of: "caption", "col", "colgroup",
        "tbody", "td", "tfoot", "th", "thead", "tr" */
        } elseif($token['type'] === HTML5_Tokenizer::STARTTAG && in_array($token['name'],
        array('caption', 'col', 'colgroup', 'tbody', 'td', 'tfoot', 'th',
        'thead', 'tr'))) {
            /* If the stack of open elements does not have a td or th element
            in table scope, then this is a parse error; ignore the token.
            (fragment case) */
            if(!$this->elementInScope(array('td', 'th'), self::SCOPE_TABLE)) {
                // parse error
                $this->ignored = true;

            /* Otherwise, close the cell (see below) and reprocess the current
            token. */
            } else {
                $this->closeCell();
                $this->emitToken($token);
            }

        /* An end tag whose tag name is one of: "body", "caption", "col",
        "colgroup", "html" */
        } elseif($token['type'] === HTML5_Tokenizer::ENDTAG && in_array($token['name'],
        array('body', 'caption', 'col', 'colgroup', 'html'))) {
            /* Parse error. Ignore the token. */
            $this->ignored = true;

        /* An end tag whose tag name is one of: "table", "tbody", "tfoot",
        "thead", "tr" */
        } elseif($token['type'] === HTML5_Tokenizer::ENDTAG && in_array($token['name'],
        array('table', 'tbody', 'tfoot', 'thead', 'tr'))) {
            /* If the stack of open elements does not have a td or th element
            in table scope, then this is a parse error; ignore the token.
            (innerHTML case) */
            if(!$this->elementInScope(array('td', 'th'), self::SCOPE_TABLE)) {
                // Parse error
                $this->ignored = true;

            /* Otherwise, close the cell (see below) and reprocess the current
            token. */
            } else {
                $this->closeCell();
                $this->emitToken($token);
            }

        /* Anything else */
        } else {
            /* Process the token as if the insertion mode was "in body". */
            $this->processWithRulesFor($token, self::IN_BODY);
        }
    break;

    case self::IN_SELECT:
        /* Handle the token as follows: */

        /* A character token */
        if(
            $token['type'] === HTML5_Tokenizer::CHARACTER ||
            $token['type'] === HTML5_Tokenizer::SPACECHARACTER
        ) {
            /* Append the token's character to the current node. */
            $this->insertText($token['data']);

        /* A comment token */
        } elseif($token['type'] === HTML5_Tokenizer::COMMENT) {
            /* Append a Comment node to the current node with the data
            attribute set to the data given in the comment token. */
            $this->insertComment($token['data']);

        } elseif($token['type'] === HTML5_Tokenizer::DOCTYPE) {
            // parse error

        } elseif($token['type'] === HTML5_Tokenizer::STARTTAG && $token['name'] === 'html') {
            $this->processWithRulesFor($token, self::INBODY);

        /* A start tag token whose tag name is "option" */
        } elseif($token['type'] === HTML5_Tokenizer::STARTTAG &&
        $token['name'] === 'option') {
            /* If the current node is an option element, act as if an end tag
            with the tag name "option" had been seen. */
            if(end($this->stack)->tagName === 'option') {
                $this->emitToken(array(
                    'name' => 'option',
                    'type' => HTML5_Tokenizer::ENDTAG
                ));
            }

            /* Insert an HTML element for the token. */
            $this->insertElement($token);

        /* A start tag token whose tag name is "optgroup" */
        } elseif($token['type'] === HTML5_Tokenizer::STARTTAG &&
        $token['name'] === 'optgroup') {
            /* If the current node is an option element, act as if an end tag
            with the tag name "option" had been seen. */
            if(end($this->stack)->tagName === 'option') {
                $this->emitToken(array(
                    'name' => 'option',
                    'type' => HTML5_Tokenizer::ENDTAG
                ));
            }

            /* If the current node is an optgroup element, act as if an end tag
            with the tag name "optgroup" had been seen. */
            if(end($this->stack)->tagName === 'optgroup') {
                $this->emitToken(array(
                    'name' => 'optgroup',
                    'type' => HTML5_Tokenizer::ENDTAG
                ));
            }

            /* Insert an HTML element for the token. */
            $this->insertElement($token);

        /* An end tag token whose tag name is "optgroup" */
        } elseif($token['type'] === HTML5_Tokenizer::ENDTAG &&
        $token['name'] === 'optgroup') {
            /* First, if the current node is an option element, and the node
            immediately before it in the stack of open elements is an optgroup
            element, then act as if an end tag with the tag name "option" had
            been seen. */
            $elements_in_stack = count($this->stack);

            if($this->stack[$elements_in_stack - 1]->tagName === 'option' &&
            $this->stack[$elements_in_stack - 2]->tagName === 'optgroup') {
                $this->emitToken(array(
                    'name' => 'option',
                    'type' => HTML5_Tokenizer::ENDTAG
                ));
            }

            /* If the current node is an optgroup element, then pop that node
            from the stack of open elements. Otherwise, this is a parse error,
            ignore the token. */
            if(end($this->stack)->tagName === 'optgroup') {
                array_pop($this->stack);
            } else {
                // parse error
                $this->ignored = true;
            }

        /* An end tag token whose tag name is "option" */
        } elseif($token['type'] === HTML5_Tokenizer::ENDTAG &&
        $token['name'] === 'option') {
            /* If the current node is an option element, then pop that node
            from the stack of open elements. Otherwise, this is a parse error,
            ignore the token. */
            if(end($this->stack)->tagName === 'option') {
                array_pop($this->stack);
            } else {
                // parse error
                $this->ignored = true;
            }

        /* An end tag whose tag name is "select" */
        } elseif($token['type'] === HTML5_Tokenizer::ENDTAG &&
        $token['name'] === 'select') {
            /* If the stack of open elements does not have an element in table
            scope with the same tag name as the token, this is a parse error.
            Ignore the token. (fragment case) */
            if(!$this->elementInScope($token['name'], self::SCOPE_TABLE)) {
                $this->ignored = true;
                // parse error

            /* Otherwise: */
            } else {
                /* Pop elements from the stack of open elements until a select
                element has been popped from the stack. */
                do {
                    $node = array_pop($this->stack);
                } while ($node->tagName !== 'select');

                /* Reset the insertion mode appropriately. */
                $this->resetInsertionMode();
            }

        /* A start tag whose tag name is "select" */
        } elseif($token['type'] === HTML5_Tokenizer::STARTTAG && $token['name'] === 'select') {
            /* Parse error. Act as if the token had been an end tag with the
            tag name "select" instead. */
            $this->emitToken(array(
                'name' => 'select',
                'type' => HTML5_Tokenizer::ENDTAG
            ));

        } elseif($token['type'] === HTML5_Tokenizer::STARTTAG &&
        ($token['name'] === 'input' || $token['name'] === 'keygen' ||  $token['name'] === 'textarea')) {
            // parse error
            $this->emitToken(array(
                'name' => 'select',
                'type' => HTML5_Tokenizer::ENDTAG
            ));
            $this->emitToken($token);

        } elseif($token['type'] === HTML5_Tokenizer::STARTTAG && $token['name'] === 'script') {
            $this->processWithRulesFor($token, self::IN_HEAD);

        } elseif($token['type'] === HTML5_Tokenizer::EOF) {
            // XERROR: If the current node is not the root html element, then this is a parse error.
            /* Stop parsing */

        /* Anything else */
        } else {
            /* Parse error. Ignore the token. */
            $this->ignored = true;
        }
    break;

    case self::IN_SELECT_IN_TABLE:

        if($token['type'] === HTML5_Tokenizer::STARTTAG &&
        in_array($token['name'], array('caption', 'table', 'tbody',
        'tfoot', 'thead', 'tr', 'td', 'th'))) {
            // parse error
            $this->emitToken(array(
                'name' => 'select',
                'type' => HTML5_Tokenizer::ENDTAG,
            ));
            $this->emitToken($token);

        /* An end tag whose tag name is one of: "caption", "table", "tbody",
        "tfoot", "thead", "tr", "td", "th" */
        } elseif($token['type'] === HTML5_Tokenizer::ENDTAG &&
        in_array($token['name'], array('caption', 'table', 'tbody', 'tfoot', 'thead', 'tr', 'td', 'th')))  {
            /* Parse error. */
            // parse error

            /* If the stack of open elements has an element in table scope with
            the same tag name as that of the token, then act as if an end tag
            with the tag name "select" had been seen, and reprocess the token.
            Otherwise, ignore the token. */
            if($this->elementInScope($token['name'], self::SCOPE_TABLE)) {
                $this->emitToken(array(
                    'name' => 'select',
                    'type' => HTML5_Tokenizer::ENDTAG
                ));

                $this->emitToken($token);
            } else {
                $this->ignored = true;
            }
        } else {
            $this->processWithRulesFor($token, self::IN_SELECT);
        }
    break;

    case self::IN_FOREIGN_CONTENT:
        if ($token['type'] === HTML5_Tokenizer::CHARACTER ||
        $token['type'] === HTML5_Tokenizer::SPACECHARACTER) {
            $this->insertText($token['data']);
        } elseif ($token['type'] === HTML5_Tokenizer::COMMENT) {
            $this->insertComment($token['data']);
        } elseif ($token['type'] === HTML5_Tokenizer::DOCTYPE) {
            // XERROR: parse error
        } elseif ($token['type'] === HTML5_Tokenizer::ENDTAG &&
        $token['name'] === 'script' && end($this->stack)->tagName === 'script' &&
        // XDOM
        end($this->stack)->namespaceURI === self::NS_SVG) {
            array_pop($this->stack);
            // a bunch of script running mumbo jumbo
        } elseif (
            ($token['type'] === HTML5_Tokenizer::STARTTAG &&
                ((
                    $token['name'] !== 'mglyph' &&
                    $token['name'] !== 'malignmark' &&
                    // XDOM
                    end($this->stack)->namespaceURI === self::NS_MATHML &&
                    in_array(end($this->stack)->tagName, array('mi', 'mo', 'mn', 'ms', 'mtext'))
                ) ||
                (
                    $token['name'] === 'svg' &&
                    // XDOM
                    end($this->stack)->namespaceURI === self::NS_MATHML &&
                    end($this->stack)->tagName === 'annotation-xml'
                ) ||
                (
                    // XDOM
                    end($this->stack)->namespaceURI === self::NS_SVG &&
                    in_array(end($this->stack)->tagName, array('foreignObject', 'desc', 'title'))
                ) ||
                (
                    // XSKETCHY && XDOM
                    end($this->stack)->namespaceURI === self::NS_HTML
                ))
            ) || $token['type'] === HTML5_Tokenizer::ENDTAG
        ) {
            $this->processWithRulesFor($token, $this->secondary_mode);
            /* If, after doing so, the insertion mode is still "in foreign 
             * content", but there is no element in scope that has a namespace 
             * other than the HTML namespace, switch the insertion mode to the 
             * secondary insertion mode. */
            if ($this->mode === self::IN_FOREIGN_CONTENT) {
                $found = false;
                // this basically duplicates elementInScope()
                for ($i = count($this->stack) - 1; $i >= 0; $i--) {
                    // XDOM
                    $node = $this->stack[$i];
                    if ($node->namespaceURI !== self::NS_HTML) {
                        $found = true;
                        break;
                    } elseif (in_array($node->tagName, array('table', 'html',
                    'applet', 'caption', 'td', 'th', 'button', 'marquee',
                    'object')) || ($node->tagName === 'foreignObject' &&
                    $node->namespaceURI === self::NS_SVG)) {
                        break;
                    }
                }
                if (!$found) {
                    $this->mode = $this->secondary_mode;
                }
            }
        } elseif ($token['type'] === HTML5_Tokenizer::EOF || (
        $token['type'] === HTML5_Tokenizer::STARTTAG &&
        (in_array($token['name'], array('b', "big", "blockquote", "body", "br", 
        "center", "code", "dc", "dd", "div", "dl", "ds", "dt", "em", "embed", "h1", "h2", 
        "h3", "h4", "h5", "h6", "head", "hr", "i", "img", "li", "listing", 
        "menu", "meta", "nobr", "ol", "p", "pre", "ruby", "s",  "small", 
        "span", "strong", "strike",  "sub", "sup", "table", "tt", "u", "ul", 
        "var")) || ($token['name'] === 'font' && ($this->getAttr($token, 'color') ||
        $this->getAttr($token, 'face') || $this->getAttr($token, 'size')))))) {
            // XERROR: parse error
            do {
                $node = array_pop($this->stack);
                // XDOM
            } while ($node->namespaceURI !== self::NS_HTML);
            $this->stack[] = $node;
            $this->mode = $this->secondary_mode;
            $this->emitToken($token);
        } elseif ($token['type'] === HTML5_Tokenizer::STARTTAG) {
            static $svg_lookup = array(
                'altglyph' => 'altGlyph',
                'altglyphdef' => 'altGlyphDef',
                'altglyphitem' => 'altGlyphItem',
                'animatecolor' => 'animateColor',
                'animatemotion' => 'animateMotion',
                'animatetransform' => 'animateTransform',
                'clippath' => 'clipPath',
                'feblend' => 'feBlend',
                'fecolormatrix' => 'feColorMatrix',
                'fecomponenttransfer' => 'feComponentTransfer',
                'fecomposite' => 'feComposite',
                'feconvolvematrix' => 'feConvolveMatrix',
                'fediffuselighting' => 'feDiffuseLighting',
                'fedisplacementmap' => 'feDisplacementMap',
                'fedistantlight' => 'feDistantLight',
                'feflood' => 'feFlood',
                'fefunca' => 'feFuncA',
                'fefuncb' => 'feFuncB',
                'fefuncg' => 'feFuncG',
                'fefuncr' => 'feFuncR',
                'fegaussianblur' => 'feGaussianBlur',
                'feimage' => 'feImage',
                'femerge' => 'feMerge',
                'femergenode' => 'feMergeNode',
                'femorphology' => 'feMorphology',
                'feoffset' => 'feOffset',
                'fepointlight' => 'fePointLight',
                'fespecularlighting' => 'feSpecularLighting',
                'fespotlight' => 'feSpotLight',
                'fetile' => 'feTile',
                'feturbulence' => 'feTurbulence',
                'foreignobject' => 'foreignObject',
                'glyphref' => 'glyphRef',
                'lineargradient' => 'linearGradient',
                'radialgradient' => 'radialGradient',
                'textpath' => 'textPath',
            );
            // XDOM
            $current = end($this->stack);
            if ($current->namespaceURI === self::NS_MATHML) {
                $token = $this->adjustMathMLAttributes($token);
            }
            if ($current->namespaceURI === self::NS_SVG &&
            isset($svg_lookup[$token['name']])) {
                $token['name'] = $svg_lookup[$token['name']];
            }
            if ($current->namespaceURI === self::NS_SVG) {
                $token = $this->adjustSVGAttributes($token);
            }
            $token = $this->adjustForeignAttributes($token);
            $this->insertForeignElement($token, $current->namespaceURI);
            if (isset($token['self-closing'])) {
                array_pop($this->stack);
                // XERROR: acknowledge self-closing flag
            }
        }
    break;

    case self::AFTER_BODY:
        /* Handle the token as follows: */

        /* A character token that is one of one of U+0009 CHARACTER TABULATION,
        U+000A LINE FEED (LF), U+000B LINE TABULATION, U+000C FORM FEED (FF),
        or U+0020 SPACE */
        if($token['type'] === HTML5_Tokenizer::SPACECHARACTER) {
            /* Process the token as it would be processed if the insertion mode
            was "in body". */
            $this->processWithRulesFor($token, self::IN_BODY);

        /* A comment token */
        } elseif($token['type'] === HTML5_Tokenizer::COMMENT) {
            /* Append a Comment node to the first element in the stack of open
            elements (the html element), with the data attribute set to the
            data given in the comment token. */
            // XDOM
            $comment = $this->dom->createComment($token['data']);
            $this->stack[0]->appendChild($comment);

        } elseif($token['type'] === HTML5_Tokenizer::DOCTYPE) {
            // parse error

        } elseif($token['type'] === HTML5_Tokenizer::STARTTAG && $token['name'] === 'html') {
            $this->processWithRulesFor($token, self::IN_BODY);

        /* An end tag with the tag name "html" */
        } elseif($token['type'] === HTML5_Tokenizer::ENDTAG && $token['name'] === 'html') {
            /*     If the parser was originally created as part of the HTML
             *     fragment parsing algorithm, this is a parse error; ignore
             *     the token. (fragment case) */
            $this->ignored = true;
            // XERROR: implement this

            $this->mode = self::AFTER_AFTER_BODY;

        } elseif($token['type'] === HTML5_Tokenizer::EOF) {
            /* Stop parsing */

        /* Anything else */
        } else {
            /* Parse error. Set the insertion mode to "in body" and reprocess
            the token. */
            $this->mode = self::IN_BODY;
            $this->emitToken($token);
        }
    break;

    case self::IN_FRAMESET:
        /* Handle the token as follows: */

        /* A character token that is one of one of U+0009 CHARACTER TABULATION,
        U+000A LINE FEED (LF), U+000B LINE TABULATION, U+000C FORM FEED (FF),
        U+000D CARRIAGE RETURN (CR), or U+0020 SPACE */
        if($token['type'] === HTML5_Tokenizer::SPACECHARACTER) {
            /* Append the character to the current node. */
            $this->insertText($token['data']);

        /* A comment token */
        } elseif($token['type'] === HTML5_Tokenizer::COMMENT) {
            /* Append a Comment node to the current node with the data
            attribute set to the data given in the comment token. */
            $this->insertComment($token['data']);

        } elseif($token['type'] === HTML5_Tokenizer::DOCTYPE) {
            // parse error

        /* A start tag with the tag name "frameset" */
        } elseif($token['type'] === HTML5_Tokenizer::STARTTAG &&
        $token['name'] === 'frameset') {
            $this->insertElement($token);

        /* An end tag with the tag name "frameset" */
        } elseif($token['type'] === HTML5_Tokenizer::ENDTAG &&
        $token['name'] === 'frameset') {
            /* If the current node is the root html element, then this is a
            parse error; ignore the token. (fragment case) */
            if(end($this->stack)->tagName === 'html') {
                $this->ignored = true;
                // Parse error

            } else {
                /* Otherwise, pop the current node from the stack of open
                elements. */
                array_pop($this->stack);

                /* If the parser was not originally created as part of the HTML 
                 * fragment parsing algorithm  (fragment case), and the current 
                 * node is no longer a frameset element, then switch the 
                 * insertion mode to "after frameset". */
                $this->mode = self::AFTER_FRAMESET;
            }

        /* A start tag with the tag name "frame" */
        } elseif($token['type'] === HTML5_Tokenizer::STARTTAG &&
        $token['name'] === 'frame') {
            /* Insert an HTML element for the token. */
            $this->insertElement($token);

            /* Immediately pop the current node off the stack of open elements. */
            array_pop($this->stack);

            // XERROR: Acknowledge the token's self-closing flag, if it is set.

        /* A start tag with the tag name "noframes" */
        } elseif($token['type'] === HTML5_Tokenizer::STARTTAG &&
        $token['name'] === 'noframes') {
            /* Process the token using the rules for the "in head" insertion mode. */
            $this->processwithRulesFor($token, self::IN_HEAD);

        } elseif($token['type'] === HTML5_Tokenizer::EOF) {
            // XERROR: If the current node is not the root html element, then this is a parse error.
            /* Stop parsing */
        /* Anything else */
        } else {
            /* Parse error. Ignore the token. */
            $this->ignored = true;
        }
    break;

    case self::AFTER_FRAMESET:
        /* Handle the token as follows: */

        /* A character token that is one of one of U+0009 CHARACTER TABULATION,
        U+000A LINE FEED (LF), U+000B LINE TABULATION, U+000C FORM FEED (FF),
        U+000D CARRIAGE RETURN (CR), or U+0020 SPACE */
        if($token['type'] === HTML5_Tokenizer::SPACECHARACTER) {
            /* Append the character to the current node. */
            $this->insertText($token['data']);

        /* A comment token */
        } elseif($token['type'] === HTML5_Tokenizer::COMMENT) {
            /* Append a Comment node to the current node with the data
            attribute set to the data given in the comment token. */
            $this->insertComment($token['data']);

        } elseif($token['type'] === HTML5_Tokenizer::DOCTYPE) {
            // parse error

        } elseif($token['type'] === HTML5_Tokenizer::STARTTAG && $token['name'] === 'html') {
            $this->processWithRulesFor($token, self::IN_BODY);

        /* An end tag with the tag name "html" */
        } elseif($token['type'] === HTML5_Tokenizer::ENDTAG &&
        $token['name'] === 'html') {
            $this->mode = self::AFTER_AFTER_FRAMESET;

        /* A start tag with the tag name "noframes" */
        } elseif($token['type'] === HTML5_Tokenizer::STARTTAG &&
        $token['name'] === 'noframes') {
            $this->processWithRulesFor($token, self::IN_HEAD);

        } elseif($token['type'] === HTML5_Tokenizer::EOF) {
            /* Stop parsing */

        /* Anything else */
        } else {
            /* Parse error. Ignore the token. */
            $this->ignored = true;
        }
    break;

    case self::AFTER_AFTER_BODY:
        /* A comment token */
        if($token['type'] === HTML5_Tokenizer::COMMENT) {
            /* Append a Comment node to the Document object with the data
            attribute set to the data given in the comment token. */
            // XDOM
            $comment = $this->dom->createComment($token['data']);
            $this->dom->appendChild($comment);

        } elseif($token['type'] === HTML5_Tokenizer::DOCTYPE ||
        $token['type'] === HTML5_Tokenizer::SPACECHARACTER ||
        ($token['type'] === HTML5_Tokenizer::STARTTAG && $token['name'] === 'html')) {
            $this->processWithRulesFor($token, self::IN_BODY);

        /* An end-of-file token */
        } elseif($token['type'] === HTML5_Tokenizer::EOF) {
            /* OMG DONE!! */
        } else {
            // parse error
            $this->mode = self::IN_BODY;
            $this->emitToken($token);
        }
    break;

    case self::AFTER_AFTER_FRAMESET:
        /* A comment token */
        if($token['type'] === HTML5_Tokenizer::COMMENT) {
            /* Append a Comment node to the Document object with the data
            attribute set to the data given in the comment token. */
            // XDOM
            $comment = $this->dom->createComment($token['data']);
            $this->dom->appendChild($comment);

        } elseif($token['type'] === HTML5_Tokenizer::DOCTYPE ||
        $token['type'] === HTML5_Tokenizer::SPACECHARACTER ||
        ($token['type'] === HTML5_Tokenizer::STARTTAG && $token['name'] === 'html')) {
            $this->processWithRulesFor($token, self::IN_BODY);

        /* An end-of-file token */
        } elseif($token['type'] === HTML5_Tokenizer::EOF) {
            /* OMG DONE!! */
        } elseif($token['type'] === HTML5_Tokenizer::STARTTAG && $token['name'] === 'nofrmaes') {
            $this->processWithRulesFor($token, self::IN_HEAD);
        } else {
            // parse error
        }
    break;
    }
        // end funky indenting
        }

    private function insertElement($token, $append = true) {
        $el = $this->dom->createElementNS(self::NS_HTML, $token['name']);

        if (!empty($token['attr'])) {
            foreach($token['attr'] as $attr) {
                if(!$el->hasAttribute($attr['name'])) {
                    $el->setAttribute($attr['name'], $attr['value']);
                }
            }
        }
        if ($append) {
            $this->appendToRealParent($el);
            $this->stack[] = $el;
        }

        return $el;
    }

    private function insertText($data) {
        if ($data === '') return;
        if ($this->ignore_lf_token) {
            if ($data[0] === "\n") {
                $data = substr($data, 1);
                if ($data === false) return;
            }
        }
        $text = $this->dom->createTextNode($data);
        $this->appendToRealParent($text);
    }

    private function insertComment($data) {
        $comment = $this->dom->createComment($data);
        $this->appendToRealParent($comment);
    }

    private function appendToRealParent($node) {
        // this is only for the foster_parent case
        /* If the current node is a table, tbody, tfoot, thead, or tr
        element, then, whenever a node would be inserted into the current
        node, it must instead be inserted into the foster parent element. */
        if(!$this->foster_parent || !in_array(end($this->stack)->tagName,
        array('table', 'tbody', 'tfoot', 'thead', 'tr'))) {
            end($this->stack)->appendChild($node);
        } else {
            $this->fosterParent($node);
        }
    }

    private function elementInScope($el, $scope = self::SCOPE) {
        if(is_array($el)) {
            foreach($el as $element) {
                if($this->elementInScope($element, $scope)) {
                    return true;
                }
            }

            return false;
        }

        $leng = count($this->stack);

        for($n = 0; $n < $leng; $n++) {
            /* 1. Initialise node to be the current node (the bottommost node of
            the stack). */
            $node = $this->stack[$leng - 1 - $n];

            if($node->tagName === $el) {
                /* 2. If node is the target node, terminate in a match state. */
                return true;

                // We've expanded the logic for these states a little differently;
                // Hixie's refactoring into "specific scope" is more general, but
                // this "gets the job done"

            // these are the common states for all scopes
            } elseif($node->tagName === 'table' || $node->tagName === 'html') {
                return false;

            // these are valid for "in scope" and "in list item scope"
            } elseif($scope !== self::SCOPE_TABLE &&
            (in_array($node->tagName, array('applet', 'caption', 'td',
                'th', 'button', 'marquee', 'object')) ||
                $node->tagName === 'foreignObject' && $node->namespaceURI === self::NS_SVG)) {
                return false;


            // these are valid for "in list item scope"
            } elseif($scope === self::SCOPE_LISTITEM && in_array($node->tagName, array('ol', 'ul'))) {
                return false;
            }

            /* Otherwise, set node to the previous entry in the stack of open
            elements and return to step 2. (This will never fail, since the loop
            will always terminate in the previous step if the top of the stack
            is reached.) */
        }
    }

    private function reconstructActiveFormattingElements() {
        /* 1. If there are no entries in the list of active formatting elements,
        then there is nothing to reconstruct; stop this algorithm. */
        $formatting_elements = count($this->a_formatting);

        if($formatting_elements === 0) {
            return false;
        }

        /* 3. Let entry be the last (most recently added) element in the list
        of active formatting elements. */
        $entry = end($this->a_formatting);

        /* 2. If the last (most recently added) entry in the list of active
        formatting elements is a marker, or if it is an element that is in the
        stack of open elements, then there is nothing to reconstruct; stop this
        algorithm. */
        if($entry === self::MARKER || in_array($entry, $this->stack, true)) {
            return false;
        }

        for($a = $formatting_elements - 1; $a >= 0; true) {
            /* 4. If there are no entries before entry in the list of active
            formatting elements, then jump to step 8. */
            if($a === 0) {
                $step_seven = false;
                break;
            }

            /* 5. Let entry be the entry one earlier than entry in the list of
            active formatting elements. */
            $a--;
            $entry = $this->a_formatting[$a];

            /* 6. If entry is neither a marker nor an element that is also in
            thetack of open elements, go to step 4. */
            if($entry === self::MARKER || in_array($entry, $this->stack, true)) {
                break;
            }
        }

        while(true) {
            /* 7. Let entry be the element one later than entry in the list of
            active formatting elements. */
            if(isset($step_seven) && $step_seven === true) {
                $a++;
                $entry = $this->a_formatting[$a];
            }

            /* 8. Perform a shallow clone of the element entry to obtain clone. */
            $clone = $entry->cloneNode();

            /* 9. Append clone to the current node and push it onto the stack
            of open elements  so that it is the new current node. */
            $this->appendToRealParent($clone);
            $this->stack[] = $clone;

            /* 10. Replace the entry for entry in the list with an entry for
            clone. */
            $this->a_formatting[$a] = $clone;

            /* 11. If the entry for clone in the list of active formatting
            elements is not the last entry in the list, return to step 7. */
            if(end($this->a_formatting) !== $clone) {
                $step_seven = true;
            } else {
                break;
            }
        }
    }

    private function clearTheActiveFormattingElementsUpToTheLastMarker() {
        /* When the steps below require the UA to clear the list of active
        formatting elements up to the last marker, the UA must perform the
        following steps: */

        while(true) {
            /* 1. Let entry be the last (most recently added) entry in the list
            of active formatting elements. */
            $entry = end($this->a_formatting);

            /* 2. Remove entry from the list of active formatting elements. */
            array_pop($this->a_formatting);

            /* 3. If entry was a marker, then stop the algorithm at this point.
            The list has been cleared up to the last marker. */
            if($entry === self::MARKER) {
                break;
            }
        }
    }

    private function generateImpliedEndTags($exclude = array()) {
        /* When the steps below require the UA to generate implied end tags, 
         * then, while the current node is a dc element, a dd element, a ds 
         * element, a dt element, an li element, an option element, an optgroup 
         * element, a p element, an rp element, or an rt element, the UA must 
         * pop the current node off the stack of open elements. */
        $node = end($this->stack);
        $elements = array_diff(array('dc', 'dd', 'ds', 'dt', 'li', 'p', 'td', 'th', 'tr'), $exclude);

        while(in_array(end($this->stack)->tagName, $elements)) {
            array_pop($this->stack);
        }
    }

    private function getElementCategory($node) {
        if (!is_object($node)) debug_print_backtrace();
        $name = $node->tagName;
        if(in_array($name, $this->special))
            return self::SPECIAL;

        elseif(in_array($name, $this->scoping))
            return self::SCOPING;

        elseif(in_array($name, $this->formatting))
            return self::FORMATTING;

        else
            return self::PHRASING;
    }

    private function clearStackToTableContext($elements) {
        /* When the steps above require the UA to clear the stack back to a
        table context, it means that the UA must, while the current node is not
        a table element or an html element, pop elements from the stack of open
        elements. */
        while(true) {
            $name = end($this->stack)->tagName;

            if(in_array($name, $elements)) {
                break;
            } else {
                array_pop($this->stack);
            }
        }
    }

    private function resetInsertionMode($context = null) {
        /* 1. Let last be false. */
        $last = false;
        $leng = count($this->stack);

        for($n = $leng - 1; $n >= 0; $n--) {
            /* 2. Let node be the last node in the stack of open elements. */
            $node = $this->stack[$n];

            /* 3. If node is the first node in the stack of open elements, then 
             * set last to true and set node to the context  element. (fragment 
             * case) */
            if($this->stack[0]->isSameNode($node)) {
                $last = true;
                $node = $context;
            }

            /* 4. If node is a select element, then switch the insertion mode to
            "in select" and abort these steps. (fragment case) */
            if($node->tagName === 'select') {
                $this->mode = self::IN_SELECT;
                break;

            /* 5. If node is a td or th element, then switch the insertion mode
            to "in cell" and abort these steps. */
            } elseif($node->tagName === 'td' || $node->nodeName === 'th') {
                $this->mode = self::IN_CELL;
                break;

            /* 6. If node is a tr element, then switch the insertion mode to
            "in    row" and abort these steps. */
            } elseif($node->tagName === 'tr') {
                $this->mode = self::IN_ROW;
                break;

            /* 7. If node is a tbody, thead, or tfoot element, then switch the
            insertion mode to "in table body" and abort these steps. */
            } elseif(in_array($node->tagName, array('tbody', 'thead', 'tfoot'))) {
                $this->mode = self::IN_TABLE_BODY;
                break;

            /* 8. If node is a caption element, then switch the insertion mode
            to "in caption" and abort these steps. */
            } elseif($node->tagName === 'caption') {
                $this->mode = self::IN_CAPTION;
                break;

            /* 9. If node is a colgroup element, then switch the insertion mode
            to "in column group" and abort these steps. (innerHTML case) */
            } elseif($node->tagName === 'colgroup') {
                $this->mode = self::IN_COLUMN_GROUP;
                break;

            /* 10. If node is a table element, then switch the insertion mode
            to "in table" and abort these steps. */
            } elseif($node->tagName === 'table') {
                $this->mode = self::IN_TABLE;
                break;

            /* 11. If node is an element from the MathML namespace or the SVG 
             * namespace, then switch the insertion mode to "in foreign 
             * content", let the secondary insertion mode be "in body", and 
             * abort these steps. */
            } elseif($node->namespaceURI === self::NS_SVG ||
            $node->namespaceURI === self::NS_MATHML) {
                $this->mode = self::IN_FOREIGN_CONTENT;
                $this->secondary_mode = self::IN_BODY;
                break;

            /* 12. If node is a head element, then switch the insertion mode
            to "in body" ("in body"! not "in head"!) and abort these steps.
            (fragment case) */
            } elseif($node->tagName === 'head') {
                $this->mode = self::IN_BODY;
                break;

            /* 13. If node is a body element, then switch the insertion mode to
            "in body" and abort these steps. */
            } elseif($node->tagName === 'body') {
                $this->mode = self::IN_BODY;
                break;

            /* 14. If node is a frameset element, then switch the insertion
            mode to "in frameset" and abort these steps. (fragment case) */
            } elseif($node->tagName === 'frameset') {
                $this->mode = self::IN_FRAMESET;
                break;

            /* 15. If node is an html element, then: if the head element
            pointer is null, switch the insertion mode to "before head",
            otherwise, switch the insertion mode to "after head". In either
            case, abort these steps. (fragment case) */
            } elseif($node->tagName === 'html') {
                $this->mode = ($this->head_pointer === null)
                    ? self::BEFORE_HEAD
                    : self::AFTER_HEAD;

                break;

            /* 16. If last is true, then set the insertion mode to "in body"
            and    abort these steps. (fragment case) */
            } elseif($last) {
                $this->mode = self::IN_BODY;
                break;
            }
        }
    }

    private function closeCell() {
        /* If the stack of open elements has a td or th element in table scope,
        then act as if an end tag token with that tag name had been seen. */
        foreach(array('td', 'th') as $cell) {
            if($this->elementInScope($cell, self::SCOPE_TABLE)) {
                $this->emitToken(array(
                    'name' => $cell,
                    'type' => HTML5_Tokenizer::ENDTAG
                ));

                break;
            }
        }
    }

    private function processWithRulesFor($token, $mode) {
        /* "using the rules for the m insertion mode", where m is one of these
         * modes, the user agent must use the rules described under the m
         * insertion mode's section, but must leave the insertion mode
         * unchanged unless the rules in m themselves switch the insertion mode
         * to a new value. */
        return $this->emitToken($token, $mode);
    }

    private function insertCDATAElement($token) {
        $this->insertElement($token);
        $this->original_mode = $this->mode;
        $this->mode = self::IN_CDATA_RCDATA;
        $this->content_model = HTML5_Tokenizer::CDATA;
    }

    private function insertRCDATAElement($token) {
        $this->insertElement($token);
        $this->original_mode = $this->mode;
        $this->mode = self::IN_CDATA_RCDATA;
        $this->content_model = HTML5_Tokenizer::RCDATA;
    }

    private function getAttr($token, $key) {
        if (!isset($token['attr'])) return false;
        $ret = false;
        foreach ($token['attr'] as $keypair) {
            if ($keypair['name'] === $key) $ret = $keypair['value'];
        }
        return $ret;
    }

    private function getCurrentTable() {
        /* The current table is the last table  element in the stack of open 
         * elements, if there is one. If there is no table element in the stack 
         * of open elements (fragment case), then the current table is the 
         * first element in the stack of open elements (the html element). */
        for ($i = count($this->stack) - 1; $i >= 0; $i--) {
            if ($this->stack[$i]->tagName === 'table') {
                return $this->stack[$i];
            }
        }
        return $this->stack[0];
    }

    private function getFosterParent() {
        /* The foster parent element is the parent element of the last
        table element in the stack of open elements, if there is a
        table element and it has such a parent element. If there is no
        table element in the stack of open elements (innerHTML case),
        then the foster parent element is the first element in the
        stack of open elements (the html  element). Otherwise, if there
        is a table element in the stack of open elements, but the last
        table element in the stack of open elements has no parent, or
        its parent node is not an element, then the foster parent
        element is the element before the last table element in the
        stack of open elements. */
        for($n = count($this->stack) - 1; $n >= 0; $n--) {
            if($this->stack[$n]->tagName === 'table') {
                $table = $this->stack[$n];
                break;
            }
        }

        if(isset($table) && $table->parentNode !== null) {
            return $table->parentNode;

        } elseif(!isset($table)) {
            return $this->stack[0];

        } elseif(isset($table) && ($table->parentNode === null ||
        $table->parentNode->nodeType !== XML_ELEMENT_NODE)) {
            return $this->stack[$n - 1];
        }
    }

    public function fosterParent($node) {
        $foster_parent = $this->getFosterParent();
        $table = $this->getCurrentTable(); // almost equivalent to last table element, except it can be html
        /* When a node node is to be foster parented, the node node must be
         * be inserted into the foster parent element. */
        /* If the foster parent element is the parent element of the last table 
         * element in the stack of open elements, then node must be inserted 
         * immediately before the last table element in the stack of open 
         * elements in the foster parent element; otherwise, node must be 
         * appended to the foster parent element. */
        if ($table->tagName === 'table' && $table->parentNode->isSameNode($foster_parent)) {
            $foster_parent->insertBefore($node, $table);
        } else {
            $foster_parent->appendChild($node);
        }
    }

    /**
     * For debugging, prints the stack
     */
    private function printStack() {
        $names = array();
        foreach ($this->stack as $i => $element) {
            $names[] = $element->tagName;
        }
        echo "  -> stack [" . implode(', ', $names) . "]\n";
    }

    /**
     * For debugging, prints active formatting elements
     */
    private function printActiveFormattingElements() {
        if (!$this->a_formatting) return;
        $names = array();
        foreach ($this->a_formatting as $node) {
            if ($node === self::MARKER) $names[] = 'MARKER';
            else $names[] = $node->tagName;
        }
        echo "  -> active formatting [" . implode(', ', $names) . "]\n";
    }

    public function currentTableIsTainted() {
        return !empty($this->getCurrentTable()->tainted);
    }

    /**
     * Sets up the tree constructor for building a fragment.
     */
    public function setupContext($context = null) {
        $this->fragment = true;
        if ($context) {
            $context = $this->dom->createElementNS(self::NS_HTML, $context);
            /* 4.1. Set the HTML parser's tokenization  stage's content model
             * flag according to the context element, as follows: */
            switch ($context->tagName) {
            case 'title': case 'textarea':
                $this->content_model = HTML5_Tokenizer::RCDATA;
                break;
            case 'style': case 'script': case 'xmp': case 'iframe':
            case 'noembed': case 'noframes':
                $this->content_model = HTML5_Tokenizer::CDATA;
                break;
            case 'noscript':
                // XSCRIPT: assuming scripting is enabled
                $this->content_model = HTML5_Tokenizer::CDATA;
                break;
            case 'plaintext':
                $this->content_model = HTML5_Tokenizer::PLAINTEXT;
                break;
            }
            /* 4.2. Let root be a new html element with no attributes. */
            $root = $this->dom->createElementNS(self::NS_HTML, 'html');
            $this->root = $root;
            /* 4.3 Append the element root to the Document node created above. */
            $this->dom->appendChild($root);
            /* 4.4 Set up the parser's stack of open elements so that it 
             * contains just the single element root. */
            $this->stack = array($root);
            /* 4.5 Reset the parser's insertion mode appropriately. */
            $this->resetInsertionMode($context);
            /* 4.6 Set the parser's form element pointer  to the nearest node 
             * to the context element that is a form element (going straight up 
             * the ancestor chain, and including the element itself, if it is a 
             * form element), or, if there is no such form element, to null. */
            $node = $context;
            do {
                if ($node->tagName === 'form') {
                    $this->form_pointer = $node;
                    break;
                }
            } while ($node = $node->parentNode);
        }
    }

    public function adjustMathMLAttributes($token) {
        foreach ($token['attr'] as &$kp) {
            if ($kp['name'] === 'definitionurl') {
                $kp['name'] = 'definitionURL';
            }
        }
        return $token;
    }

    public function adjustSVGAttributes($token) {
        static $lookup = array(
            'attributename' => 'attributeName',
            'attributetype' => 'attributeType',
            'basefrequency' => 'baseFrequency',
            'baseprofile' => 'baseProfile',
            'calcmode' => 'calcMode',
            'clippathunits' => 'clipPathUnits',
            'contentscripttype' => 'contentScriptType',
            'contentstyletype' => 'contentStyleType',
            'diffuseconstant' => 'diffuseConstant',
            'edgemode' => 'edgeMode',
            'externalresourcesrequired' => 'externalResourcesRequired',
            'filterres' => 'filterRes',
            'filterunits' => 'filterUnits',
            'glyphref' => 'glyphRef',
            'gradienttransform' => 'gradientTransform',
            'gradientunits' => 'gradientUnits',
            'kernelmatrix' => 'kernelMatrix',
            'kernelunitlength' => 'kernelUnitLength',
            'keypoints' => 'keyPoints',
            'keysplines' => 'keySplines',
            'keytimes' => 'keyTimes',
            'lengthadjust' => 'lengthAdjust',
            'limitingconeangle' => 'limitingConeAngle',
            'markerheight' => 'markerHeight',
            'markerunits' => 'markerUnits',
            'markerwidth' => 'markerWidth',
            'maskcontentunits' => 'maskContentUnits',
            'maskunits' => 'maskUnits',
            'numoctaves' => 'numOctaves',
            'pathlength' => 'pathLength',
            'patterncontentunits' => 'patternContentUnits',
            'patterntransform' => 'patternTransform',
            'patternunits' => 'patternUnits',
            'pointsatx' => 'pointsAtX',
            'pointsaty' => 'pointsAtY',
            'pointsatz' => 'pointsAtZ',
            'preservealpha' => 'preserveAlpha',
            'preserveaspectratio' => 'preserveAspectRatio',
            'primitiveunits' => 'primitiveUnits',
            'refx' => 'refX',
            'refy' => 'refY',
            'repeatcount' => 'repeatCount',
            'repeatdur' => 'repeatDur',
            'requiredextensions' => 'requiredExtensions',
            'requiredfeatures' => 'requiredFeatures',
            'specularconstant' => 'specularConstant',
            'specularexponent' => 'specularExponent',
            'spreadmethod' => 'spreadMethod',
            'startoffset' => 'startOffset',
            'stddeviation' => 'stdDeviation',
            'stitchtiles' => 'stitchTiles',
            'surfacescale' => 'surfaceScale',
            'systemlanguage' => 'systemLanguage',
            'tablevalues' => 'tableValues',
            'targetx' => 'targetX',
            'targety' => 'targetY',
            'textlength' => 'textLength',
            'viewbox' => 'viewBox',
            'viewtarget' => 'viewTarget',
            'xchannelselector' => 'xChannelSelector',
            'ychannelselector' => 'yChannelSelector',
            'zoomandpan' => 'zoomAndPan',
        );
        foreach ($token['attr'] as &$kp) {
            if (isset($lookup[$kp['name']])) {
                $kp['name'] = $lookup[$kp['name']];
            }
        }
        return $token;
    }

    public function adjustForeignAttributes($token) {
        static $lookup = array(
            'xlink:actuate' => array('xlink', 'actuate', self::NS_XLINK),
            'xlink:arcrole' => array('xlink', 'arcrole', self::NS_XLINK),
            'xlink:href' => array('xlink', 'href', self::NS_XLINK),
            'xlink:role' => array('xlink', 'role', self::NS_XLINK),
            'xlink:show' => array('xlink', 'show', self::NS_XLINK),
            'xlink:title' => array('xlink', 'title', self::NS_XLINK),
            'xlink:type' => array('xlink', 'type', self::NS_XLINK),
            'xml:base' => array('xml', 'base', self::NS_XML),
            'xml:lang' => array('xml', 'lang', self::NS_XML),
            'xml:space' => array('xml', 'space', self::NS_XML),
            'xmlns' => array(null, 'xmlns', self::NS_XMLNS),
            'xmlns:xlink' => array('xmlns', 'xlink', self::NS_XMLNS),
        );
        foreach ($token['attr'] as &$kp) {
            if (isset($lookup[$kp['name']])) {
                $kp['name'] = $lookup[$kp['name']];
            }
        }
        return $token;
    }

    public function insertForeignElement($token, $namespaceURI) {
        $el = $this->dom->createElementNS($namespaceURI, $token['name']);
        if (!empty($token['attr'])) {
            foreach ($token['attr'] as $kp) {
                $attr = $kp['name'];
                if (is_array($attr)) {
                    $ns = $attr[2];
                    $attr = $attr[1];
                } else {
                    $ns = self::NS_HTML;
                }
                if (!$el->hasAttributeNS($ns, $attr)) {
                    // XSKETCHY: work around godawful libxml bug
                    if ($ns === self::NS_XLINK) {
                        $el->setAttribute('xlink:'.$attr, $kp['value']);
                    } elseif ($ns === self::NS_HTML) {
                        // Another godawful libxml bug
                        $el->setAttribute($attr, $kp['value']);
                    } else {
                        $el->setAttributeNS($ns, $attr, $kp['value']);
                    }
                }
            }
        }
        $this->appendToRealParent($el);
        $this->stack[] = $el;
        // XERROR: see below
        /* If the newly created element has an xmlns attribute in the XMLNS 
         * namespace  whose value is not exactly the same as the element's 
         * namespace, that is a parse error. Similarly, if the newly created 
         * element has an xmlns:xlink attribute in the XMLNS namespace whose 
         * value is not the XLink Namespace, that is a parse error. */
    }

    public function save() {
        $this->dom->normalize();
        if (!$this->fragment) {
            return $this->dom;
        } else {
            if ($this->root) {
                return $this->root->childNodes;
            } else {
                return $this->dom->childNodes;
            }
        }
    }
}

