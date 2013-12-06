<?php

/*

Copyright 2007 Jeroen van der Meer <http://jero.net/>
Copyright 2008 Edward Z. Yang <http://htmlpurifier.org/>
Copyright 2009 Geoffrey Sneddon <http://gsnedders.com/>

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

// Some conventions:
// /* */ indicates verbatim text from the HTML 5 specification
// // indicates regular comments

// all flags are in hyphenated form

class HTML5_Tokenizer {
    /**
     * Points to an InputStream object.
     */
    protected $stream;

    /**
     * Tree builder that the tokenizer emits token to.
     */
    private $tree;

    /**
     * Current content model we are parsing as.
     */
    protected $content_model;

    /**
     * Current token that is being built, but not yet emitted. Also
     * is the last token emitted, if applicable.
     */
    protected $token;

    // These are constants describing the content model
    const PCDATA    = 0;
    const RCDATA    = 1;
    const CDATA     = 2;
    const PLAINTEXT = 3;

    // These are constants describing tokens
    // XXX should probably be moved somewhere else, probably the
    // HTML5 class.
    const DOCTYPE        = 0;
    const STARTTAG       = 1;
    const ENDTAG         = 2;
    const COMMENT        = 3;
    const CHARACTER      = 4;
    const SPACECHARACTER = 5;
    const EOF            = 6;
    const PARSEERROR     = 7;

    // These are constants representing bunches of characters.
    const ALPHA       = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    const UPPER_ALPHA = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const LOWER_ALPHA = 'abcdefghijklmnopqrstuvwxyz';
    const DIGIT       = '0123456789';
    const HEX         = '0123456789ABCDEFabcdef';
    const WHITESPACE  = "\t\n\x0c ";

    /**
     * @param $data Data to parse
     */
    public function __construct($data, $builder = null) {
        $this->stream = new HTML5_InputStream($data);
        if (!$builder) $this->tree = new HTML5_TreeBuilder;
        else $this->tree = $builder;
        $this->content_model = self::PCDATA;
    }

    public function parseFragment($context = null) {
        $this->tree->setupContext($context);
        if ($this->tree->content_model) {
            $this->content_model = $this->tree->content_model;
            $this->tree->content_model = null;
        }
        $this->parse();
    }

    // XXX maybe convert this into an iterator? regardless, this function
    // and the save function should go into a Parser facade of some sort
    /**
     * Performs the actual parsing of the document.
     */
    public function parse() {
        // Current state
        $state = 'data';
        // This is used to avoid having to have look-behind in the data state.
        $lastFourChars = '';
        /**
         * Escape flag as specified by the HTML5 specification: "used to
         * control the behavior of the tokeniser. It is either true or
         * false, and initially must be set to the false state."
         */
        $escape = false;
        //echo "\n\n";
        while($state !== null) {
            
            /*echo $state . ' ';
            switch ($this->content_model) {
                case self::PCDATA: echo 'PCDATA'; break;
                case self::RCDATA: echo 'RCDATA'; break;
                case self::CDATA: echo 'CDATA'; break;
                case self::PLAINTEXT: echo 'PLAINTEXT'; break;
            }
            if ($escape) echo " escape";
            echo "\n";*/
            
            switch($state) {
                case 'data':

                    /* Consume the next input character */
                    $char = $this->stream->char();
                    $lastFourChars .= $char;
                    if (strlen($lastFourChars) > 4) $lastFourChars = substr($lastFourChars, -4);

                    // see below for meaning
                    $hyp_cond = 
                        !$escape &&
                        (
                            $this->content_model === self::RCDATA ||
                            $this->content_model === self::CDATA
                        );
                    $amp_cond =
                        !$escape &&
                        (
                            $this->content_model === self::PCDATA ||
                            $this->content_model === self::RCDATA
                        );
                    $lt_cond =
                        $this->content_model === self::PCDATA ||
                        (
                            (
                                $this->content_model === self::RCDATA ||
                                $this->content_model === self::CDATA
                             ) &&
                             !$escape
                        );
                    $gt_cond = 
                        $escape &&
                        (
                            $this->content_model === self::RCDATA ||
                            $this->content_model === self::CDATA
                        );

                    if($char === '&' && $amp_cond) {
                        /* U+0026 AMPERSAND (&)
                        When the content model flag is set to one of the PCDATA or RCDATA
                        states and the escape flag is false: switch to the
                        character reference data state. Otherwise: treat it as per
                        the "anything else" entry below. */
                        $state = 'character reference data';

                    } elseif(
                        $char === '-' &&
                        $hyp_cond &&
                        $lastFourChars === '<!--'
                    ) {
                        /*
                        U+002D HYPHEN-MINUS (-)
                        If the content model flag is set to either the RCDATA state or
                        the CDATA state, and the escape flag is false, and there are at
                        least three characters before this one in the input stream, and the
                        last four characters in the input stream, including this one, are
                        U+003C LESS-THAN SIGN, U+0021 EXCLAMATION MARK, U+002D HYPHEN-MINUS,
                        and U+002D HYPHEN-MINUS ("<!--"), then set the escape flag to true. */
                        $escape = true;

                        /* In any case, emit the input character as a character token. Stay
                        in the data state. */
                        $this->emitToken(array(
                            'type' => self::CHARACTER,
                            'data' => '-'
                        ));
                        // We do the "any case" part as part of "anything else".

                    /* U+003C LESS-THAN SIGN (<) */
                    } elseif($char === '<' && $lt_cond) {
                        /* When the content model flag is set to the PCDATA state: switch
                        to the tag open state.

                        When the content model flag is set to either the RCDATA state or
                        the CDATA state and the escape flag is false: switch to the tag
                        open state.

                        Otherwise: treat it as per the "anything else" entry below. */
                        $state = 'tag open';

                    /* U+003E GREATER-THAN SIGN (>) */
                    } elseif(
                        $char === '>' &&
                        $gt_cond &&
                        substr($lastFourChars, 1) === '-->'
                    ) {
                        /* If the content model flag is set to either the RCDATA state or
                        the CDATA state, and the escape flag is true, and the last three
                        characters in the input stream including this one are U+002D
                        HYPHEN-MINUS, U+002D HYPHEN-MINUS, U+003E GREATER-THAN SIGN ("-->"),
                        set the escape flag to false. */
                        $escape = false;

                        /* In any case, emit the input character as a character token.
                        Stay in the data state. */
                        $this->emitToken(array(
                            'type' => self::CHARACTER,
                            'data' => '>'
                        ));
                        // We do the "any case" part as part of "anything else".

                    } elseif($char === false) {
                        /* EOF
                        Emit an end-of-file token. */
                        $state = null;
                        $this->tree->emitToken(array(
                            'type' => self::EOF
                        ));
                    
                    } elseif($char === "\t" || $char === "\n" || $char === "\x0c" || $char === ' ') {
                        // Directly after emitting a token you switch back to the "data
                        // state". At that point spaceCharacters are important so they are
                        // emitted separately.
                        $chars = $this->stream->charsWhile(self::WHITESPACE);
                        $this->emitToken(array(
                            'type' => self::SPACECHARACTER,
                            'data' => $char . $chars
                        ));
                        $lastFourChars .= $chars;
                        if (strlen($lastFourChars) > 4) $lastFourChars = substr($lastFourChars, -4);

                    } else {
                        /* Anything else
                        THIS IS AN OPTIMIZATION: Get as many character that
                        otherwise would also be treated as a character token and emit it
                        as a single character token. Stay in the data state. */
                        
                        $mask = '';
                        if ($hyp_cond) $mask .= '-';
                        if ($amp_cond) $mask .= '&';
                        if ($lt_cond)  $mask .= '<';
                        if ($gt_cond)  $mask .= '>';

                        if ($mask === '') {
                            $chars = $this->stream->remainingChars();
                        } else {
                            $chars = $this->stream->charsUntil($mask);
                        }

                        $this->emitToken(array(
                            'type' => self::CHARACTER,
                            'data' => $char . $chars
                        ));

                        $lastFourChars .= $chars;
                        if (strlen($lastFourChars) > 4) $lastFourChars = substr($lastFourChars, -4);

                        $state = 'data';
                    }
                break;

                case 'character reference data':
                    /* (This cannot happen if the content model flag
                    is set to the CDATA state.) */

                    /* Attempt to consume a character reference, with no
                    additional allowed character. */
                    $entity = $this->consumeCharacterReference();

                    /* If nothing is returned, emit a U+0026 AMPERSAND
                    character token. Otherwise, emit the character token that
                    was returned. */
                    // This is all done when consuming the character reference.
                    $this->emitToken(array(
                        'type' => self::CHARACTER,
                        'data' => $entity
                    ));

                    /* Finally, switch to the data state. */
                    $state = 'data';
                break;

                case 'tag open':
                    $char = $this->stream->char();

                    switch($this->content_model) {
                        case self::RCDATA:
                        case self::CDATA:
                            /* Consume the next input character. If it is a
                            U+002F SOLIDUS (/) character, switch to the close
                            tag open state. Otherwise, emit a U+003C LESS-THAN
                            SIGN character token and reconsume the current input
                            character in the data state. */
                            // We consumed above.

                            if($char === '/') {
                                $state = 'close tag open';

                            } else {
                                $this->emitToken(array(
                                    'type' => self::CHARACTER,
                                    'data' => '<'
                                ));

                                $this->stream->unget();

                                $state = 'data';
                            }
                        break;

                        case self::PCDATA:
                            /* If the content model flag is set to the PCDATA state
                            Consume the next input character: */
                            // We consumed above.

                            if($char === '!') {
                                /* U+0021 EXCLAMATION MARK (!)
                                Switch to the markup declaration open state. */
                                $state = 'markup declaration open';

                            } elseif($char === '/') {
                                /* U+002F SOLIDUS (/)
                                Switch to the close tag open state. */
                                $state = 'close tag open';

                            } elseif('A' <= $char && $char <= 'Z') {
                                /* U+0041 LATIN LETTER A through to U+005A LATIN LETTER Z
                                Create a new start tag token, set its tag name to the lowercase
                                version of the input character (add 0x0020 to the character's code
                                point), then switch to the tag name state. (Don't emit the token
                                yet; further details will be filled in before it is emitted.) */
                                $this->token = array(
                                    'name'  => strtolower($char),
                                    'type'  => self::STARTTAG,
                                    'attr'  => array()
                                );

                                $state = 'tag name';

                            } elseif('a' <= $char && $char <= 'z') {
                                /* U+0061 LATIN SMALL LETTER A through to U+007A LATIN SMALL LETTER Z
                                Create a new start tag token, set its tag name to the input
                                character, then switch to the tag name state. (Don't emit
                                the token yet; further details will be filled in before it
                                is emitted.) */
                                $this->token = array(
                                    'name'  => $char,
                                    'type'  => self::STARTTAG,
                                    'attr'  => array()
                                );

                                $state = 'tag name';

                            } elseif($char === '>') {
                                /* U+003E GREATER-THAN SIGN (>)
                                Parse error. Emit a U+003C LESS-THAN SIGN character token and a
                                U+003E GREATER-THAN SIGN character token. Switch to the data state. */
                                $this->emitToken(array(
                                    'type' => self::PARSEERROR,
                                    'data' => 'expected-tag-name-but-got-right-bracket'
                                ));
                                $this->emitToken(array(
                                    'type' => self::CHARACTER,
                                    'data' => '<>'
                                ));

                                $state = 'data';

                            } elseif($char === '?') {
                                /* U+003F QUESTION MARK (?)
                                Parse error. Switch to the bogus comment state. */
                                $this->emitToken(array(
                                    'type' => self::PARSEERROR,
                                    'data' => 'expected-tag-name-but-got-question-mark'
                                ));
                                $this->token = array(
                                    'data' => '?',
                                    'type' => self::COMMENT
                                );
                                $state = 'bogus comment';

                            } else {
                                /* Anything else
                                Parse error. Emit a U+003C LESS-THAN SIGN character token and
                                reconsume the current input character in the data state. */
                                $this->emitToken(array(
                                    'type' => self::PARSEERROR,
                                    'data' => 'expected-tag-name'
                                ));
                                $this->emitToken(array(
                                    'type' => self::CHARACTER,
                                    'data' => '<'
                                ));

                                $state = 'data';
                                $this->stream->unget();
                            }
                        break;
                    }
                break;

                case 'close tag open':
                    if (
                        $this->content_model === self::RCDATA ||
                        $this->content_model === self::CDATA
                    ) {
                        /* If the content model flag is set to the RCDATA or CDATA
                        states... */
                        $name = strtolower($this->stream->charsWhile(self::ALPHA));
                        $following = $this->stream->char();
                        $this->stream->unget();
                        if (
                            !$this->token ||
                            $this->token['name'] !== $name ||
                            $this->token['name'] === $name && !in_array($following, array("\x09", "\x0A", "\x0C", "\x20", "\x3E", "\x2F", false))
                        ) {
                            /* if no start tag token has ever been emitted by this instance
                            of the tokenizer (fragment case), or, if the next few
                            characters do not match the tag name of the last start tag
                            token emitted (compared in an ASCII case-insensitive manner),
                            or if they do but they are not immediately followed by one of
                            the following characters:

                                * U+0009 CHARACTER TABULATION
                                * U+000A LINE FEED (LF)
                                * U+000C FORM FEED (FF)
                                * U+0020 SPACE
                                * U+003E GREATER-THAN SIGN (>)
                                * U+002F SOLIDUS (/)
                                * EOF

                            ...then emit a U+003C LESS-THAN SIGN character token, a
                            U+002F SOLIDUS character token, and switch to the data
                            state to process the next input character. */
                            // XXX: Probably ought to replace in_array with $following === x ||...

                            // We also need to emit $name now we've consumed that, as we
                            // know it'll just be emitted as a character token.
                            $this->emitToken(array(
                                'type' => self::CHARACTER,
                                'data' => '</' . $name
                            ));

                            $state = 'data';
                        } else {
                            // This matches what would happen if we actually did the
                            // otherwise below (but we can't because we've consumed too
                            // much).

                            // Start the end tag token with the name we already have.
                            $this->token = array(
                                'name'  => $name,
                                'type'  => self::ENDTAG
                            );

                            // Change to tag name state.
                            $state = 'tag name';
                        }
                    } elseif ($this->content_model === self::PCDATA) {
                        /* Otherwise, if the content model flag is set to the PCDATA
                        state [...]: */
                        $char = $this->stream->char();

                        if ('A' <= $char && $char <= 'Z') {
                            /* U+0041 LATIN LETTER A through to U+005A LATIN LETTER Z
                            Create a new end tag token, set its tag name to the lowercase version
                            of the input character (add 0x0020 to the character's code point), then
                            switch to the tag name state. (Don't emit the token yet; further details
                            will be filled in before it is emitted.) */
                            $this->token = array(
                                'name'  => strtolower($char),
                                'type'  => self::ENDTAG
                            );

                            $state = 'tag name';

                        } elseif ('a' <= $char && $char <= 'z') {
                            /* U+0061 LATIN SMALL LETTER A through to U+007A LATIN SMALL LETTER Z
                            Create a new end tag token, set its tag name to the
                            input character, then switch to the tag name state.
                            (Don't emit the token yet; further details will be
                            filled in before it is emitted.) */
                            $this->token = array(
                                'name'  => $char,
                                'type'  => self::ENDTAG
                            );

                            $state = 'tag name';

                        } elseif($char === '>') {
                            /* U+003E GREATER-THAN SIGN (>)
                            Parse error. Switch to the data state. */
                            $this->emitToken(array(
                                'type' => self::PARSEERROR,
                                'data' => 'expected-closing-tag-but-got-right-bracket'
                            ));
                            $state = 'data';

                        } elseif($char === false) {
                            /* EOF
                            Parse error. Emit a U+003C LESS-THAN SIGN character token and a U+002F
                            SOLIDUS character token. Reconsume the EOF character in the data state. */
                            $this->emitToken(array(
                                'type' => self::PARSEERROR,
                                'data' => 'expected-closing-tag-but-got-eof'
                            ));
                            $this->emitToken(array(
                                'type' => self::CHARACTER,
                                'data' => '</'
                            ));

                            $this->stream->unget();
                            $state = 'data';

                        } else {
                            /* Parse error. Switch to the bogus comment state. */
                            $this->emitToken(array(
                                'type' => self::PARSEERROR,
                                'data' => 'expected-closing-tag-but-got-char'
                            ));
                            $this->token = array(
                                'data' => $char,
                                'type' => self::COMMENT
                            );
                            $state = 'bogus comment';
                        }
                    }
                break;

                case 'tag name':
                    /* Consume the next input character: */
                    $char = $this->stream->char();

                    if($char === "\t" || $char === "\n" || $char === "\x0c" || $char === ' ') {
                        /* U+0009 CHARACTER TABULATION
                        U+000A LINE FEED (LF)
                        U+000C FORM FEED (FF)
                        U+0020 SPACE
                        Switch to the before attribute name state. */
                        $state = 'before attribute name';

                    } elseif($char === '/') {
                        /* U+002F SOLIDUS (/)
                        Switch to the self-closing start tag state. */
                        $state = 'self-closing start tag';

                    } elseif($char === '>') {
                        /* U+003E GREATER-THAN SIGN (>)
                        Emit the current tag token. Switch to the data state. */
                        $this->emitToken($this->token);
                        $state = 'data';

                    } elseif('A' <= $char && $char <= 'Z') {
                        /* U+0041 LATIN CAPITAL LETTER A through to U+005A LATIN CAPITAL LETTER Z
                        Append the lowercase version of the current input
                        character (add 0x0020 to the character's code point) to
                        the current tag token's tag name. Stay in the tag name state. */
                        $chars = $this->stream->charsWhile(self::UPPER_ALPHA);

                        $this->token['name'] .= strtolower($char . $chars);
                        $state = 'tag name';

                    } elseif($char === false) {
                        /* EOF
                        Parse error. Reconsume the EOF character in the data state. */
                        $this->emitToken(array(
                            'type' => self::PARSEERROR,
                            'data' => 'eof-in-tag-name'
                        ));

                        $this->stream->unget();
                        $state = 'data';

                    } else {
                        /* Anything else
                        Append the current input character to the current tag token's tag name.
                        Stay in the tag name state. */
                        $chars = $this->stream->charsUntil("\t\n\x0C />" . self::UPPER_ALPHA);

                        $this->token['name'] .= $char . $chars;
                        $state = 'tag name';
                    }
                break;

                case 'before attribute name':
                    /* Consume the next input character: */
                    $char = $this->stream->char();

                    // this conditional is optimized, check bottom
                    if($char === "\t" || $char === "\n" || $char === "\x0c" || $char === ' ') {
                        /* U+0009 CHARACTER TABULATION
                        U+000A LINE FEED (LF)
                        U+000C FORM FEED (FF)
                        U+0020 SPACE
                        Stay in the before attribute name state. */
                        $state = 'before attribute name';

                    } elseif($char === '/') {
                        /* U+002F SOLIDUS (/)
                        Switch to the self-closing start tag state. */
                        $state = 'self-closing start tag';

                    } elseif($char === '>') {
                        /* U+003E GREATER-THAN SIGN (>)
                        Emit the current tag token. Switch to the data state. */
                        $this->emitToken($this->token);
                        $state = 'data';

                    } elseif('A' <= $char && $char <= 'Z') {
                        /* U+0041 LATIN CAPITAL LETTER A through to U+005A LATIN CAPITAL LETTER Z
                        Start a new attribute in the current tag token. Set that
                        attribute's name to the lowercase version of the current
                        input character (add 0x0020 to the character's code
                        point), and its value to the empty string. Switch to the
                        attribute name state.*/
                        $this->token['attr'][] = array(
                            'name'  => strtolower($char),
                            'value' => ''
                        );

                        $state = 'attribute name';

                    } elseif($char === false) {
                        /* EOF
                        Parse error. Reconsume the EOF character in the data state. */
                        $this->emitToken(array(
                            'type' => self::PARSEERROR,
                            'data' => 'expected-attribute-name-but-got-eof'
                        ));

                        $this->stream->unget();
                        $state = 'data';

                    } else {
                        /* U+0022 QUOTATION MARK (")
                           U+0027 APOSTROPHE (')
                           U+003C LESS-THAN SIGN (<)
                           U+003D EQUALS SIGN (=)
                        Parse error. Treat it as per the "anything else" entry
                        below. */
                        if($char === '"' || $char === "'" || $char === '<' || $char === '=') {
                            $this->emitToken(array(
                                'type' => self::PARSEERROR,
                                'data' => 'invalid-character-in-attribute-name'
                            ));
                        }

                        /* Anything else
                        Start a new attribute in the current tag token. Set that attribute's
                        name to the current input character, and its value to the empty string.
                        Switch to the attribute name state. */
                        $this->token['attr'][] = array(
                            'name'  => $char,
                            'value' => ''
                        );

                        $state = 'attribute name';
                    }
                break;

                case 'attribute name':
                    // Consume the next input character:
                    $char = $this->stream->char();

                    // this conditional is optimized, check bottom
                    if($char === "\t" || $char === "\n" || $char === "\x0c" || $char === ' ') {
                        /* U+0009 CHARACTER TABULATION
                        U+000A LINE FEED (LF)
                        U+000C FORM FEED (FF)
                        U+0020 SPACE
                        Switch to the after attribute name state. */
                        $state = 'after attribute name';

                    } elseif($char === '/') {
                        /* U+002F SOLIDUS (/)
                        Switch to the self-closing start tag state. */
                        $state = 'self-closing start tag';

                    } elseif($char === '=') {
                        /* U+003D EQUALS SIGN (=)
                        Switch to the before attribute value state. */
                        $state = 'before attribute value';

                    } elseif($char === '>') {
                        /* U+003E GREATER-THAN SIGN (>)
                        Emit the current tag token. Switch to the data state. */
                        $this->emitToken($this->token);
                        $state = 'data';

                    } elseif('A' <= $char && $char <= 'Z') {
                        /* U+0041 LATIN CAPITAL LETTER A through to U+005A LATIN CAPITAL LETTER Z
                        Append the lowercase version of the current input
                        character (add 0x0020 to the character's code point) to
                        the current attribute's name. Stay in the attribute name
                        state. */
                        $chars = $this->stream->charsWhile(self::UPPER_ALPHA);

                        $last = count($this->token['attr']) - 1;
                        $this->token['attr'][$last]['name'] .= strtolower($char . $chars);

                        $state = 'attribute name';

                    } elseif($char === false) {
                        /* EOF
                        Parse error. Reconsume the EOF character in the data state. */
                        $this->emitToken(array(
                            'type' => self::PARSEERROR,
                            'data' => 'eof-in-attribute-name'
                        ));

                        $this->stream->unget();
                        $state = 'data';

                    } else {
                        /* U+0022 QUOTATION MARK (")
                           U+0027 APOSTROPHE (')
                           U+003C LESS-THAN SIGN (<)
                        Parse error. Treat it as per the "anything else"
                        entry below. */
                        if($char === '"' || $char === "'" || $char === '<') {
                            $this->emitToken(array(
                                'type' => self::PARSEERROR,
                                'data' => 'invalid-character-in-attribute-name'
                            ));
                        }

                        /* Anything else
                        Append the current input character to the current attribute's name.
                        Stay in the attribute name state. */
                        $chars = $this->stream->charsUntil("\t\n\x0C /=>\"'" . self::UPPER_ALPHA);

                        $last = count($this->token['attr']) - 1;
                        $this->token['attr'][$last]['name'] .= $char . $chars;

                        $state = 'attribute name';
                    }

                    /* When the user agent leaves the attribute name state
                    (and before emitting the tag token, if appropriate), the
                    complete attribute's name must be compared to the other
                    attributes on the same token; if there is already an
                    attribute on the token with the exact same name, then this
                    is a parse error and the new attribute must be dropped, along
                    with the value that gets associated with it (if any). */
                    // this might be implemented in the emitToken method
                break;

                case 'after attribute name':
                    // Consume the next input character:
                    $char = $this->stream->char();

                    // this is an optimized conditional, check the bottom
                    if($char === "\t" || $char === "\n" || $char === "\x0c" || $char === ' ') {
                        /* U+0009 CHARACTER TABULATION
                        U+000A LINE FEED (LF)
                        U+000C FORM FEED (FF)
                        U+0020 SPACE
                        Stay in the after attribute name state. */
                        $state = 'after attribute name';

                    } elseif($char === '/') {
                        /* U+002F SOLIDUS (/)
                        Switch to the self-closing start tag state. */
                        $state = 'self-closing start tag';

                    } elseif($char === '=') {
                        /* U+003D EQUALS SIGN (=)
                        Switch to the before attribute value state. */
                        $state = 'before attribute value';

                    } elseif($char === '>') {
                        /* U+003E GREATER-THAN SIGN (>)
                        Emit the current tag token. Switch to the data state. */
                        $this->emitToken($this->token);
                        $state = 'data';

                    } elseif('A' <= $char && $char <= 'Z') {
                        /* U+0041 LATIN CAPITAL LETTER A through to U+005A LATIN CAPITAL LETTER Z
                        Start a new attribute in the current tag token. Set that
                        attribute's name to the lowercase version of the current
                        input character (add 0x0020 to the character's code
                        point), and its value to the empty string. Switch to the
                        attribute name state. */
                        $this->token['attr'][] = array(
                            'name'  => strtolower($char),
                            'value' => ''
                        );

                        $state = 'attribute name';

                    } elseif($char === false) {
                        /* EOF
                        Parse error. Reconsume the EOF character in the data state. */
                        $this->emitToken(array(
                            'type' => self::PARSEERROR,
                            'data' => 'expected-end-of-tag-but-got-eof'
                        ));

                        $this->stream->unget();
                        $state = 'data';

                    } else {
                        /* U+0022 QUOTATION MARK (")
                           U+0027 APOSTROPHE (')
                           U+003C LESS-THAN SIGN(<)
                        Parse error. Treat it as per the "anything else"
                        entry below. */
                        if($char === '"' || $char === "'" || $char === "<") {
                            $this->emitToken(array(
                                'type' => self::PARSEERROR,
                                'data' => 'invalid-character-after-attribute-name'
                            ));
                        }

                        /* Anything else
                        Start a new attribute in the current tag token. Set that attribute's
                        name to the current input character, and its value to the empty string.
                        Switch to the attribute name state. */
                        $this->token['attr'][] = array(
                            'name'  => $char,
                            'value' => ''
                        );

                        $state = 'attribute name';
                    }
                break;

                case 'before attribute value':
                    // Consume the next input character:
                    $char = $this->stream->char();

                    // this is an optimized conditional
                    if($char === "\t" || $char === "\n" || $char === "\x0c" || $char === ' ') {
                        /* U+0009 CHARACTER TABULATION
                        U+000A LINE FEED (LF)
                        U+000C FORM FEED (FF)
                        U+0020 SPACE
                        Stay in the before attribute value state. */
                        $state = 'before attribute value';

                    } elseif($char === '"') {
                        /* U+0022 QUOTATION MARK (")
                        Switch to the attribute value (double-quoted) state. */
                        $state = 'attribute value (double-quoted)';

                    } elseif($char === '&') {
                        /* U+0026 AMPERSAND (&)
                        Switch to the attribute value (unquoted) state and reconsume
                        this input character. */
                        $this->stream->unget();
                        $state = 'attribute value (unquoted)';

                    } elseif($char === '\'') {
                        /* U+0027 APOSTROPHE (')
                        Switch to the attribute value (single-quoted) state. */
                        $state = 'attribute value (single-quoted)';

                    } elseif($char === '>') {
                        /* U+003E GREATER-THAN SIGN (>)
                        Parse error. Emit the current tag token. Switch to the data state. */
                        $this->emitToken(array(
                            'type' => self::PARSEERROR,
                            'data' => 'expected-attribute-value-but-got-right-bracket'
                        ));
                        $this->emitToken($this->token);
                        $state = 'data';

                    } elseif($char === false) {
                        /* EOF
                        Parse error. Reconsume the EOF character in the data state. */
                        $this->emitToken(array(
                            'type' => self::PARSEERROR,
                            'data' => 'expected-attribute-value-but-got-eof'
                        ));
                        $this->stream->unget();
                        $state = 'data';

                    } else {
                        /* U+003D EQUALS SIGN (=)
                         * U+003C LESS-THAN SIGN (<)
                        Parse error. Treat it as per the "anything else" entry below. */
                        if($char === '=' || $char === '<') {
                            $this->emitToken(array(
                                'type' => self::PARSEERROR,
                                'data' => 'equals-in-unquoted-attribute-value'
                            ));
                        }

                        /* Anything else
                        Append the current input character to the current attribute's value.
                        Switch to the attribute value (unquoted) state. */
                        $last = count($this->token['attr']) - 1;
                        $this->token['attr'][$last]['value'] .= $char;

                        $state = 'attribute value (unquoted)';
                    }
                break;

                case 'attribute value (double-quoted)':
                    // Consume the next input character:
                    $char = $this->stream->char();

                    if($char === '"') {
                        /* U+0022 QUOTATION MARK (")
                        Switch to the after attribute value (quoted) state. */
                        $state = 'after attribute value (quoted)';

                    } elseif($char === '&') {
                        /* U+0026 AMPERSAND (&)
                        Switch to the character reference in attribute value
                        state, with the additional allowed character
                        being U+0022 QUOTATION MARK ("). */
                        $this->characterReferenceInAttributeValue('"');

                    } elseif($char === false) {
                        /* EOF
                        Parse error. Reconsume the EOF character in the data state. */
                        $this->emitToken(array(
                            'type' => self::PARSEERROR,
                            'data' => 'eof-in-attribute-value-double-quote'
                        ));

                        $this->stream->unget();
                        $state = 'data';

                    } else {
                        /* Anything else
                        Append the current input character to the current attribute's value.
                        Stay in the attribute value (double-quoted) state. */
                        $chars = $this->stream->charsUntil('"&');

                        $last = count($this->token['attr']) - 1;
                        $this->token['attr'][$last]['value'] .= $char . $chars;

                        $state = 'attribute value (double-quoted)';
                    }
                break;

                case 'attribute value (single-quoted)':
                    // Consume the next input character:
                    $char = $this->stream->char();

                    if($char === "'") {
                        /* U+0022 QUOTATION MARK (')
                        Switch to the after attribute value state. */
                        $state = 'after attribute value (quoted)';

                    } elseif($char === '&') {
                        /* U+0026 AMPERSAND (&)
                        Switch to the entity in attribute value state. */
                        $this->characterReferenceInAttributeValue("'");

                    } elseif($char === false) {
                        /* EOF
                        Parse error. Reconsume the EOF character in the data state. */
                        $this->emitToken(array(
                            'type' => self::PARSEERROR,
                            'data' => 'eof-in-attribute-value-single-quote'
                        ));

                        $this->stream->unget();
                        $state = 'data';

                    } else {
                        /* Anything else
                        Append the current input character to the current attribute's value.
                        Stay in the attribute value (single-quoted) state. */
                        $chars = $this->stream->charsUntil("'&");

                        $last = count($this->token['attr']) - 1;
                        $this->token['attr'][$last]['value'] .= $char . $chars;

                        $state = 'attribute value (single-quoted)';
                    }
                break;

                case 'attribute value (unquoted)':
                    // Consume the next input character:
                    $char = $this->stream->char();

                    if($char === "\t" || $char === "\n" || $char === "\x0c" || $char === ' ') {
                        /* U+0009 CHARACTER TABULATION
                        U+000A LINE FEED (LF)
                        U+000C FORM FEED (FF)
                        U+0020 SPACE
                        Switch to the before attribute name state. */
                        $state = 'before attribute name';

                    } elseif($char === '&') {
                        /* U+0026 AMPERSAND (&)
                        Switch to the entity in attribute value state, with the 
                        additional allowed character  being U+003E 
                        GREATER-THAN SIGN (>). */
                        $this->characterReferenceInAttributeValue('>');

                    } elseif($char === '>') {
                        /* U+003E GREATER-THAN SIGN (>)
                        Emit the current tag token. Switch to the data state. */
                        $this->emitToken($this->token);
                        $state = 'data';

                    } elseif ($char === false) {
                        /* EOF
                        Parse error. Reconsume the EOF character in the data state. */
                        $this->emitToken(array(
                            'type' => self::PARSEERROR,
                            'data' => 'eof-in-attribute-value-no-quotes'
                        ));
                        $this->stream->unget();
                        $state = 'data';

                    } else {
                        /* U+0022 QUOTATION MARK (")
                           U+0027 APOSTROPHE (')
                           U+003C LESS-THAN SIGN (<)
                           U+003D EQUALS SIGN (=)
                        Parse error. Treat it as per the "anything else"
                        entry below. */
                        if($char === '"' || $char === "'" || $char === '=' || $char == '<') {
                            $this->emitToken(array(
                                'type' => self::PARSEERROR,
                                'data' => 'unexpected-character-in-unquoted-attribute-value'
                            ));
                        }

                        /* Anything else
                        Append the current input character to the current attribute's value.
                        Stay in the attribute value (unquoted) state. */
                        $chars = $this->stream->charsUntil("\t\n\x0c &>\"'=");

                        $last = count($this->token['attr']) - 1;
                        $this->token['attr'][$last]['value'] .= $char . $chars;

                        $state = 'attribute value (unquoted)';
                    }
                break;

                case 'after attribute value (quoted)':
                    /* Consume the next input character: */
                    $char = $this->stream->char();

                    if($char === "\t" || $char === "\n" || $char === "\x0c" || $char === ' ') {
                        /* U+0009 CHARACTER TABULATION
                           U+000A LINE FEED (LF)
                           U+000C FORM FEED (FF)
                           U+0020 SPACE
                        Switch to the before attribute name state. */
                        $state = 'before attribute name';

                    } elseif ($char === '/') {
                        /* U+002F SOLIDUS (/)
                        Switch to the self-closing start tag state. */
                        $state = 'self-closing start tag';

                    } elseif ($char === '>') {
                        /* U+003E GREATER-THAN SIGN (>)
                        Emit the current tag token. Switch to the data state. */
                        $this->emitToken($this->token);
                        $state = 'data';

                    } elseif ($char === false) {
                        /* EOF
                        Parse error. Reconsume the EOF character in the data state. */
                        $this->emitToken(array(
                            'type' => self::PARSEERROR,
                            'data' => 'unexpected-EOF-after-attribute-value'
                        ));
                        $this->stream->unget();
                        $state = 'data';

                    } else {
                        /* Anything else
                        Parse error. Reconsume the character in the before attribute
                        name state. */
                        $this->emitToken(array(
                            'type' => self::PARSEERROR,
                            'data' => 'unexpected-character-after-attribute-value'
                        ));
                        $this->stream->unget();
                        $state = 'before attribute name';
                    }
                break;

                case 'self-closing start tag':
                    /* Consume the next input character: */
                    $char = $this->stream->char();

                    if ($char === '>') {
                        /* U+003E GREATER-THAN SIGN (>)
                        Set the self-closing flag of the current tag token.
                        Emit the current tag token. Switch to the data state. */
                        // not sure if this is the name we want
                        $this->token['self-closing'] = true;
                        $this->emitToken($this->token);
                        $state = 'data';

                    } elseif ($char === false) {
                        /* EOF
                        Parse error. Reconsume the EOF character in the data state. */
                        $this->emitToken(array(
                            'type' => self::PARSEERROR,
                            'data' => 'unexpected-eof-after-self-closing'
                        ));
                        $this->stream->unget();
                        $state = 'data';

                    } else {
                        /* Anything else
                        Parse error. Reconsume the character in the before attribute name state. */
                        $this->emitToken(array(
                            'type' => self::PARSEERROR,
                            'data' => 'unexpected-character-after-self-closing'
                        ));
                        $this->stream->unget();
                        $state = 'before attribute name';
                    }
                break;

                case 'bogus comment':
                    /* (This can only happen if the content model flag is set to the PCDATA state.) */
                    /* Consume every character up to the first U+003E GREATER-THAN SIGN
                    character (>) or the end of the file (EOF), whichever comes first. Emit
                    a comment token whose data is the concatenation of all the characters
                    starting from and including the character that caused the state machine
                    to switch into the bogus comment state, up to and including the last
                    consumed character before the U+003E character, if any, or up to the
                    end of the file otherwise. (If the comment was started by the end of
                    the file (EOF), the token is empty.) */
                    $this->token['data'] .= (string) $this->stream->charsUntil('>');
                    $this->stream->char();

                    $this->emitToken($this->token);

                    /* Switch to the data state. */
                    $state = 'data';
                break;

                case 'markup declaration open':
                    // Consume for below
                    $hyphens = $this->stream->charsWhile('-', 2);
                    if ($hyphens === '-') {
                        $this->stream->unget();
                    }
                    if ($hyphens !== '--') {
                        $alpha = $this->stream->charsWhile(self::ALPHA, 7);
                    }

                    /* If the next two characters are both U+002D HYPHEN-MINUS (-)
                    characters, consume those two characters, create a comment token whose
                    data is the empty string, and switch to the comment state. */
                    if($hyphens === '--') {
                        $state = 'comment start';
                        $this->token = array(
                            'data' => '',
                            'type' => self::COMMENT
                        );

                    /* Otherwise if the next seven characters are a case-insensitive match
                    for the word "DOCTYPE", then consume those characters and switch to the
                    DOCTYPE state. */
                    } elseif(strtoupper($alpha) === 'DOCTYPE') {
                        $state = 'DOCTYPE';

                    // XXX not implemented
                    /* Otherwise, if the insertion mode is "in foreign content"
                    and the current node is not an element in the HTML namespace
                    and the next seven characters are an ASCII case-sensitive
                    match for the string "[CDATA[" (the five uppercase letters
                    "CDATA" with a U+005B LEFT SQUARE BRACKET character before
                    and after), then consume those characters and switch to the
                    CDATA section state (which is unrelated to the content model
                    flag's CDATA state). */

                    /* Otherwise, is is a parse error. Switch to the bogus comment state.
                    The next character that is consumed, if any, is the first character
                    that will be in the comment. */
                    } else {
                        $this->emitToken(array(
                            'type' => self::PARSEERROR,
                            'data' => 'expected-dashes-or-doctype'
                        ));
                        $this->token = array(
                            'data' => (string) $alpha,
                            'type' => self::COMMENT
                        );
                        $state = 'bogus comment';
                    }
                break;

                case 'comment start':
                    /* Consume the next input character: */
                    $char = $this->stream->char();

                    if ($char === '-') {
                        /* U+002D HYPHEN-MINUS (-)
                        Switch to the comment start dash state. */
                        $state = 'comment start dash';
                    } elseif ($char === '>') {
                        /* U+003E GREATER-THAN SIGN (>)
                        Parse error. Emit the comment token. Switch to the
                        data state. */
                        $this->emitToken(array(
                            'type' => self::PARSEERROR,
                            'data' => 'incorrect-comment'
                        ));
                        $this->emitToken($this->token);
                        $state = 'data';
                    } elseif ($char === false) {
                        /* EOF
                        Parse error. Emit the comment token. Reconsume the
                        EOF character in the data state. */
                        $this->emitToken(array(
                            'type' => self::PARSEERROR,
                            'data' => 'eof-in-comment'
                        ));
                        $this->emitToken($this->token);
                        $this->stream->unget();
                        $state = 'data';
                    } else {
                        /* Anything else
                        Append the input character to the comment token's
                        data. Switch to the comment state. */
                        $this->token['data'] .= $char;
                        $state = 'comment';
                    }
                break;

                case 'comment start dash':
                    /* Consume the next input character: */
                    $char = $this->stream->char();
                    if ($char === '-') {
                        /* U+002D HYPHEN-MINUS (-)
                        Switch to the comment end state */
                        $state = 'comment end';
                    } elseif ($char === '>') {
                        /* U+003E GREATER-THAN SIGN (>)
                        Parse error. Emit the comment token. Switch to the
                        data state. */
                        $this->emitToken(array(
                            'type' => self::PARSEERROR,
                            'data' => 'incorrect-comment'
                        ));
                        $this->emitToken($this->token);
                        $state = 'data';
                    } elseif ($char === false) {
                        /* Parse error. Emit the comment token. Reconsume the
                        EOF character in the data state. */
                        $this->emitToken(array(
                            'type' => self::PARSEERROR,
                            'data' => 'eof-in-comment'
                        ));
                        $this->emitToken($this->token);
                        $this->stream->unget();
                        $state = 'data';
                    } else {
                        $this->token['data'] .= '-' . $char;
                        $state = 'comment';
                    }
                break;

                case 'comment':
                    /* Consume the next input character: */
                    $char = $this->stream->char();

                    if($char === '-') {
                        /* U+002D HYPHEN-MINUS (-)
                        Switch to the comment end dash state */
                        $state = 'comment end dash';

                    } elseif($char === false) {
                        /* EOF
                        Parse error. Emit the comment token. Reconsume the EOF character
                        in the data state. */
                        $this->emitToken(array(
                            'type' => self::PARSEERROR,
                            'data' => 'eof-in-comment'
                        ));
                        $this->emitToken($this->token);
                        $this->stream->unget();
                        $state = 'data';

                    } else {
                        /* Anything else
                        Append the input character to the comment token's data. Stay in
                        the comment state. */
                        $chars = $this->stream->charsUntil('-');

                        $this->token['data'] .= $char . $chars;
                    }
                break;

                case 'comment end dash':
                    /* Consume the next input character: */
                    $char = $this->stream->char();

                    if($char === '-') {
                        /* U+002D HYPHEN-MINUS (-)
                        Switch to the comment end state  */
                        $state = 'comment end';

                    } elseif($char === false) {
                        /* EOF
                        Parse error. Emit the comment token. Reconsume the EOF character
                        in the data state. */
                        $this->emitToken(array(
                            'type' => self::PARSEERROR,
                            'data' => 'eof-in-comment-end-dash'
                        ));
                        $this->emitToken($this->token);
                        $this->stream->unget();
                        $state = 'data';

                    } else {
                        /* Anything else
                        Append a U+002D HYPHEN-MINUS (-) character and the input
                        character to the comment token's data. Switch to the comment state. */
                        $this->token['data'] .= '-'.$char;
                        $state = 'comment';
                    }
                break;

                case 'comment end':
                    /* Consume the next input character: */
                    $char = $this->stream->char();

                    if($char === '>') {
                        /* U+003E GREATER-THAN SIGN (>)
                        Emit the comment token. Switch to the data state. */
                        $this->emitToken($this->token);
                        $state = 'data';

                    } elseif($char === '-') {
                        /* U+002D HYPHEN-MINUS (-)
                        Parse error. Append a U+002D HYPHEN-MINUS (-) character
                        to the comment token's data. Stay in the comment end
                        state. */
                        $this->emitToken(array(
                            'type' => self::PARSEERROR,
                            'data' => 'unexpected-dash-after-double-dash-in-comment'
                        ));
                        $this->token['data'] .= '-';

                    } elseif($char === "\t" || $char === "\n" || $char === "\x0a" || $char === ' ') {
                        $this->emitToken(array(
                            'type' => self::PARSEERROR,
                            'data' => 'unexpected-space-after-double-dash-in-comment'
                        ));
                        $this->token['data'] .= '--' . $char;
                        $state = 'comment end space';

                    } elseif($char === '!') {
                        $this->emitToken(array(
                            'type' => self::PARSEERROR,
                            'data' => 'unexpected-bang-after-double-dash-in-comment'
                        ));
                        $state = 'comment end bang';

                    } elseif($char === false) {
                        /* EOF
                        Parse error. Emit the comment token. Reconsume the
                        EOF character in the data state. */
                        $this->emitToken(array(
                            'type' => self::PARSEERROR,
                            'data' => 'eof-in-comment-double-dash'
                        ));
                        $this->emitToken($this->token);
                        $this->stream->unget();
                        $state = 'data';

                    } else {
                        /* Anything else
                        Parse error. Append two U+002D HYPHEN-MINUS (-)
                        characters and the input character to the comment token's
                        data. Switch to the comment state. */
                        $this->emitToken(array(
                            'type' => self::PARSEERROR,
                            'data' => 'unexpected-char-in-comment'
                        ));
                        $this->token['data'] .= '--'.$char;
                        $state = 'comment';
                    }
                break;

                case 'comment end bang':
                    $char = $this->stream->char();
                    if ($char === '>') {
                        $this->emitToken($this->token);
                        $state = 'data';
                    } elseif ($char === "-") {
                        $this->token['data'] .= '--!';
                        $state = 'comment end dash';
                    } elseif ($char === false) {
                        $this->emitToken(array(
                            'type' => self::PARSEERROR,
                            'data' => 'eof-in-comment-end-bang'
                        ));
                        $this->emitToken($this->token);
                        $this->stream->unget();
                        $state = 'data';
                    } else {
                        $this->token['data'] .= '--!' . $char;
                        $state = 'comment';
                    }
                break;

                case 'comment end space':
                    $char = $this->stream->char();
                    if ($char === '>') {
                        $this->emitToken($this->token);
                        $state = 'data';
                    } elseif ($char === '-') {
                        $state = 'comment end dash';
                    } elseif ($char === "\t" || $char === "\n" || $char === "\x0c" || $char === ' ') {
                        $this->token['data'] .= $char;
                    } elseif ($char === false) {
                        $this->emitToken(array(
                            'type' => self::PARSEERROR,
                            'data' => 'unexpected-eof-in-comment-end-space',
                        ));
                        $this->emitToken($this->token);
                        $this->stream->unget();
                        $state = 'data';
                    } else {
                        $this->token['data'] .= $char;
                        $state = 'comment';
                    }
                break;

                case 'DOCTYPE':
                    /* Consume the next input character: */
                    $char = $this->stream->char();

                    if($char === "\t" || $char === "\n" || $char === "\x0c" || $char === ' ') {
                        /* U+0009 CHARACTER TABULATION
                           U+000A LINE FEED (LF)
                           U+000C FORM FEED (FF)
                           U+0020 SPACE
                        Switch to the before DOCTYPE name state. */
                        $state = 'before DOCTYPE name';
                    
                    } elseif($char === false) {
                        /* EOF
                        Parse error. Create a new DOCTYPE token. Set its
                        force-quirks flag to on. Emit the token. Reconsume the
                        EOF character in the data state. */
                        $this->emitToken(array(
                            'type' => self::PARSEERROR,
                            'data' => 'need-space-after-doctype-but-got-eof'
                        ));
                        $this->emitToken(array(
                            'name' => '',
                            'type' => self::DOCTYPE,
                            'force-quirks' => true,
                            'error' => true
                        ));
                        $this->stream->unget();
                        $state = 'data';

                    } else {
                        /* Anything else
                        Parse error. Reconsume the current character in the
                        before DOCTYPE name state. */
                        $this->emitToken(array(
                            'type' => self::PARSEERROR,
                            'data' => 'need-space-after-doctype'
                        ));
                        $this->stream->unget();
                        $state = 'before DOCTYPE name';
                    }
                break;

                case 'before DOCTYPE name':
                    /* Consume the next input character: */
                    $char = $this->stream->char();

                    if($char === "\t" || $char === "\n" || $char === "\x0c" || $char === ' ') {
                        /* U+0009 CHARACTER TABULATION
                           U+000A LINE FEED (LF)
                           U+000C FORM FEED (FF)
                           U+0020 SPACE
                        Stay in the before DOCTYPE name state. */

                    } elseif($char === '>') {
                        /* U+003E GREATER-THAN SIGN (>)
                        Parse error. Create a new DOCTYPE token. Set its
                        force-quirks flag to on. Emit the token. Switch to the
                        data state. */
                        $this->emitToken(array(
                            'type' => self::PARSEERROR,
                            'data' => 'expected-doctype-name-but-got-right-bracket'
                        ));
                        $this->emitToken(array(
                            'name' => '',
                            'type' => self::DOCTYPE,
                            'force-quirks' => true,
                            'error' => true
                        ));

                        $state = 'data';

                    } elseif('A' <= $char && $char <= 'Z') {
                        /* U+0041 LATIN CAPITAL LETTER A through to U+005A LATIN CAPITAL LETTER Z
                        Create a new DOCTYPE token. Set the token's name to the
                        lowercase version of the input character (add 0x0020 to
                        the character's code point). Switch to the DOCTYPE name
                        state. */
                        $this->token = array(
                            'name' => strtolower($char),
                            'type' => self::DOCTYPE,
                            'error' => true
                        );

                        $state = 'DOCTYPE name';

                    } elseif($char === false) {
                        /* EOF
                        Parse error. Create a new DOCTYPE token. Set its
                        force-quirks flag to on. Emit the token. Reconsume the
                        EOF character in the data state. */
                        $this->emitToken(array(
                            'type' => self::PARSEERROR,
                            'data' => 'expected-doctype-name-but-got-eof'
                        ));
                        $this->emitToken(array(
                            'name' => '',
                            'type' => self::DOCTYPE,
                            'force-quirks' => true,
                            'error' => true
                        ));

                        $this->stream->unget();
                        $state = 'data';

                    } else {
                        /* Anything else
                        Create a new DOCTYPE token. Set the token's name to the
                        current input character. Switch to the DOCTYPE name state. */
                        $this->token = array(
                            'name' => $char,
                            'type' => self::DOCTYPE,
                            'error' => true
                        );

                        $state = 'DOCTYPE name';
                    }
                break;

                case 'DOCTYPE name':
                    /* Consume the next input character: */
                    $char = $this->stream->char();

                    if($char === "\t" || $char === "\n" || $char === "\x0c" || $char === ' ') {
                        /* U+0009 CHARACTER TABULATION
                           U+000A LINE FEED (LF)
                           U+000C FORM FEED (FF)
                           U+0020 SPACE
                        Switch to the after DOCTYPE name state. */
                        $state = 'after DOCTYPE name';

                    } elseif($char === '>') {
                        /* U+003E GREATER-THAN SIGN (>)
                        Emit the current DOCTYPE token. Switch to the data state. */
                        $this->emitToken($this->token);
                        $state = 'data';

                    } elseif('A' <= $char && $char <= 'Z') {
                        /* U+0041 LATIN CAPITAL LETTER A through to U+005A LATIN CAPITAL LETTER Z
                        Append the lowercase version of the input character
                        (add 0x0020 to the character's code point) to the current
                        DOCTYPE token's name. Stay in the DOCTYPE name state. */
                        $this->token['name'] .= strtolower($char);

                    } elseif($char === false) {
                        /* EOF
                        Parse error. Set the DOCTYPE token's force-quirks flag
                        to on. Emit that DOCTYPE token. Reconsume the EOF
                        character in the data state. */
                        $this->emitToken(array(
                            'type' => self::PARSEERROR,
                            'data' => 'eof-in-doctype-name'
                        ));
                        $this->token['force-quirks'] = true;
                        $this->emitToken($this->token);
                        $this->stream->unget();
                        $state = 'data';

                    } else {
                        /* Anything else
                        Append the current input character to the current
                        DOCTYPE token's name. Stay in the DOCTYPE name state. */
                        $this->token['name'] .= $char;
                    }

                    // XXX this is probably some sort of quirks mode designation,
                    // check tree-builder to be sure. In general 'error' needs
                    // to be specc'ified, this probably means removing it at the end
                    $this->token['error'] = ($this->token['name'] === 'HTML')
                        ? false
                        : true;
                break;

                case 'after DOCTYPE name':
                    /* Consume the next input character: */
                    $char = $this->stream->char();

                    if($char === "\t" || $char === "\n" || $char === "\x0c" || $char === ' ') {
                        /* U+0009 CHARACTER TABULATION
                           U+000A LINE FEED (LF)
                           U+000C FORM FEED (FF)
                           U+0020 SPACE
                        Stay in the after DOCTYPE name state. */

                    } elseif($char === '>') {
                        /* U+003E GREATER-THAN SIGN (>)
                        Emit the current DOCTYPE token. Switch to the data state. */
                        $this->emitToken($this->token);
                        $state = 'data';

                    } elseif($char === false) {
                        /* EOF
                        Parse error. Set the DOCTYPE token's force-quirks flag
                        to on. Emit that DOCTYPE token. Reconsume the EOF
                        character in the data state. */
                        $this->emitToken(array(
                            'type' => self::PARSEERROR,
                            'data' => 'eof-in-doctype'
                        ));
                        $this->token['force-quirks'] = true;
                        $this->emitToken($this->token);
                        $this->stream->unget();
                        $state = 'data';

                    } else {
                        /* Anything else */

                        $nextSix = strtoupper($char . $this->stream->charsWhile(self::ALPHA, 5));
                        if ($nextSix === 'PUBLIC') {
                            /* If the next six characters are an ASCII
                            case-insensitive match for the word "PUBLIC", then
                            consume those characters and switch to the before
                            DOCTYPE public identifier state. */
                            $state = 'before DOCTYPE public identifier';

                        } elseif ($nextSix === 'SYSTEM') {
                            /* Otherwise, if the next six characters are an ASCII
                            case-insensitive match for the word "SYSTEM", then
                            consume those characters and switch to the before
                            DOCTYPE system identifier state. */
                            $state = 'before DOCTYPE system identifier';

                        } else {
                            /* Otherwise, this is the parse error. Set the DOCTYPE
                            token's force-quirks flag to on. Switch to the bogus
                            DOCTYPE state. */
                            $this->emitToken(array(
                                'type' => self::PARSEERROR,
                                'data' => 'expected-space-or-right-bracket-in-doctype'
                            ));
                            $this->token['force-quirks'] = true;
                            $this->token['error'] = true;
                            $state = 'bogus DOCTYPE';
                        }
                    }
                break;

                case 'before DOCTYPE public identifier':
                    /* Consume the next input character: */
                    $char = $this->stream->char();

                    if($char === "\t" || $char === "\n" || $char === "\x0c" || $char === ' ') {
                        /* U+0009 CHARACTER TABULATION
                           U+000A LINE FEED (LF)
                           U+000C FORM FEED (FF)
                           U+0020 SPACE
                        Stay in the before DOCTYPE public identifier state. */
                    } elseif ($char === '"') {
                        /* U+0022 QUOTATION MARK (")
                        Set the DOCTYPE token's public identifier to the empty
                        string (not missing), then switch to the DOCTYPE public
                        identifier (double-quoted) state. */
                        $this->token['public'] = '';
                        $state = 'DOCTYPE public identifier (double-quoted)';
                    } elseif ($char === "'") {
                        /* U+0027 APOSTROPHE (')
                        Set the DOCTYPE token's public identifier to the empty
                        string (not missing), then switch to the DOCTYPE public
                        identifier (single-quoted) state. */
                        $this->token['public'] = '';
                        $state = 'DOCTYPE public identifier (single-quoted)';
                    } elseif ($char === '>') {
                        /* Parse error. Set the DOCTYPE token's force-quirks flag
                        to on. Emit that DOCTYPE token. Switch to the data state. */
                        $this->emitToken(array(
                            'type' => self::PARSEERROR,
                            'data' => 'unexpected-end-of-doctype'
                        ));
                        $this->token['force-quirks'] = true;
                        $this->emitToken($this->token);
                        $state = 'data';
                    } elseif ($char === false) {
                        /* Parse error. Set the DOCTYPE token's force-quirks
                        flag to on. Emit that DOCTYPE token. Reconsume the EOF
                        character in the data state. */
                        $this->emitToken(array(
                            'type' => self::PARSEERROR,
                            'data' => 'eof-in-doctype'
                        ));
                        $this->token['force-quirks'] = true;
                        $this->emitToken($this->token);
                        $this->stream->unget();
                        $state = 'data';
                    } else {
                        /* Parse error. Set the DOCTYPE token's force-quirks flag
                        to on. Switch to the bogus DOCTYPE state. */
                        $this->emitToken(array(
                            'type' => self::PARSEERROR,
                            'data' => 'unexpected-char-in-doctype'
                        ));
                        $this->token['force-quirks'] = true;
                        $state = 'bogus DOCTYPE';
                    }
                break;

                case 'DOCTYPE public identifier (double-quoted)':
                    /* Consume the next input character: */
                    $char = $this->stream->char();

                    if ($char === '"') {
                        /* U+0022 QUOTATION MARK (")
                        Switch to the after DOCTYPE public identifier state. */
                        $state = 'after DOCTYPE public identifier';
                    } elseif ($char === '>') {
                        /* U+003E GREATER-THAN SIGN (>)
                        Parse error. Set the DOCTYPE token's force-quirks flag
                        to on. Emit that DOCTYPE token. Switch to the data state. */
                        $this->emitToken(array(
                            'type' => self::PARSEERROR,
                            'data' => 'unexpected-end-of-doctype'
                        ));
                        $this->token['force-quirks'] = true;
                        $this->emitToken($this->token);
                        $state = 'data';
                    } elseif ($char === false) {
                        /* EOF
                        Parse error. Set the DOCTYPE token's force-quirks flag
                        to on. Emit that DOCTYPE token. Reconsume the EOF
                        character in the data state. */
                        $this->emitToken(array(
                            'type' => self::PARSEERROR,
                            'data' => 'eof-in-doctype'
                        ));
                        $this->token['force-quirks'] = true;
                        $this->emitToken($this->token);
                        $this->stream->unget();
                        $state = 'data';
                    } else {
                        /* Anything else
                        Append the current input character to the current
                        DOCTYPE token's public identifier. Stay in the DOCTYPE
                        public identifier (double-quoted) state. */
                        $this->token['public'] .= $char;
                    }
                break;

                case 'DOCTYPE public identifier (single-quoted)':
                    /* Consume the next input character: */
                    $char = $this->stream->char();

                    if ($char === "'") {
                        /* U+0027 APOSTROPHE (')
                        Switch to the after DOCTYPE public identifier state. */
                        $state = 'after DOCTYPE public identifier';
                    } elseif ($char === '>') {
                        /* U+003E GREATER-THAN SIGN (>)
                        Parse error. Set the DOCTYPE token's force-quirks flag
                        to on. Emit that DOCTYPE token. Switch to the data state. */
                        $this->emitToken(array(
                            'type' => self::PARSEERROR,
                            'data' => 'unexpected-end-of-doctype'
                        ));
                        $this->token['force-quirks'] = true;
                        $this->emitToken($this->token);
                        $state = 'data';
                    } elseif ($char === false) {
                        /* EOF
                        Parse error. Set the DOCTYPE token's force-quirks flag
                        to on. Emit that DOCTYPE token. Reconsume the EOF
                        character in the data state. */
                        $this->emitToken(array(
                            'type' => self::PARSEERROR,
                            'data' => 'eof-in-doctype'
                        ));
                        $this->token['force-quirks'] = true;
                        $this->emitToken($this->token);
                        $this->stream->unget();
                        $state = 'data';
                    } else {
                        /* Anything else
                        Append the current input character to the current
                        DOCTYPE token's public identifier. Stay in the DOCTYPE
                        public identifier (double-quoted) state. */
                        $this->token['public'] .= $char;
                    }
                break;

                case 'after DOCTYPE public identifier':
                    /* Consume the next input character: */
                    $char = $this->stream->char();

                    if($char === "\t" || $char === "\n" || $char === "\x0c" || $char === ' ') {
                        /* U+0009 CHARACTER TABULATION
                           U+000A LINE FEED (LF)
                           U+000C FORM FEED (FF)
                           U+0020 SPACE
                        Stay in the after DOCTYPE public identifier state. */
                    } elseif ($char === '"') {
                        /* U+0022 QUOTATION MARK (")
                        Set the DOCTYPE token's system identifier to the
                        empty string (not missing), then switch to the DOCTYPE
                        system identifier (double-quoted) state. */
                        $this->token['system'] = '';
                        $state = 'DOCTYPE system identifier (double-quoted)';
                    } elseif ($char === "'") {
                        /* U+0027 APOSTROPHE (')
                        Set the DOCTYPE token's system identifier to the
                        empty string (not missing), then switch to the DOCTYPE
                        system identifier (single-quoted) state. */
                        $this->token['system'] = '';
                        $state = 'DOCTYPE system identifier (single-quoted)';
                    } elseif ($char === '>') {
                        /* U+003E GREATER-THAN SIGN (>)
                        Emit the current DOCTYPE token. Switch to the data state. */
                        $this->emitToken($this->token);
                        $state = 'data';
                    } elseif ($char === false) {
                        /* Parse error. Set the DOCTYPE token's force-quirks
                        flag to on. Emit that DOCTYPE token. Reconsume the EOF
                        character in the data state. */
                        $this->emitToken(array(
                            'type' => self::PARSEERROR,
                            'data' => 'eof-in-doctype'
                        ));
                        $this->token['force-quirks'] = true;
                        $this->emitToken($this->token);
                        $this->stream->unget();
                        $state = 'data';
                    } else {
                        /* Anything else
                        Parse error. Set the DOCTYPE token's force-quirks flag
                        to on. Switch to the bogus DOCTYPE state. */
                        $this->emitToken(array(
                            'type' => self::PARSEERROR,
                            'data' => 'unexpected-char-in-doctype'
                        ));
                        $this->token['force-quirks'] = true;
                        $state = 'bogus DOCTYPE';
                    }
                break;

                case 'before DOCTYPE system identifier':
                    /* Consume the next input character: */
                    $char = $this->stream->char();

                    if($char === "\t" || $char === "\n" || $char === "\x0c" || $char === ' ') {
                        /* U+0009 CHARACTER TABULATION
                           U+000A LINE FEED (LF)
                           U+000C FORM FEED (FF)
                           U+0020 SPACE
                        Stay in the before DOCTYPE system identifier state. */
                    } elseif ($char === '"') {
                        /* U+0022 QUOTATION MARK (")
                        Set the DOCTYPE token's system identifier to the empty
                        string (not missing), then switch to the DOCTYPE system
                        identifier (double-quoted) state. */
                        $this->token['system'] = '';
                        $state = 'DOCTYPE system identifier (double-quoted)';
                    } elseif ($char === "'") {
                        /* U+0027 APOSTROPHE (')
                        Set the DOCTYPE token's system identifier to the empty
                        string (not missing), then switch to the DOCTYPE system
                        identifier (single-quoted) state. */
                        $this->token['system'] = '';
                        $state = 'DOCTYPE system identifier (single-quoted)';
                    } elseif ($char === '>') {
                        /* Parse error. Set the DOCTYPE token's force-quirks flag
                        to on. Emit that DOCTYPE token. Switch to the data state. */
                        $this->emitToken(array(
                            'type' => self::PARSEERROR,
                            'data' => 'unexpected-char-in-doctype'
                        ));
                        $this->token['force-quirks'] = true;
                        $this->emitToken($this->token);
                        $state = 'data';
                    } elseif ($char === false) {
                        /* Parse error. Set the DOCTYPE token's force-quirks
                        flag to on. Emit that DOCTYPE token. Reconsume the EOF
                        character in the data state. */
                        $this->emitToken(array(
                            'type' => self::PARSEERROR,
                            'data' => 'eof-in-doctype'
                        ));
                        $this->token['force-quirks'] = true;
                        $this->emitToken($this->token);
                        $this->stream->unget();
                        $state = 'data';
                    } else {
                        /* Parse error. Set the DOCTYPE token's force-quirks flag
                        to on. Switch to the bogus DOCTYPE state. */
                        $this->emitToken(array(
                            'type' => self::PARSEERROR,
                            'data' => 'unexpected-char-in-doctype'
                        ));
                        $this->token['force-quirks'] = true;
                        $state = 'bogus DOCTYPE';
                    }
                break;

                case 'DOCTYPE system identifier (double-quoted)':
                    /* Consume the next input character: */
                    $char = $this->stream->char();

                    if ($char === '"') {
                        /* U+0022 QUOTATION MARK (")
                        Switch to the after DOCTYPE system identifier state. */
                        $state = 'after DOCTYPE system identifier';
                    } elseif ($char === '>') {
                        /* U+003E GREATER-THAN SIGN (>)
                        Parse error. Set the DOCTYPE token's force-quirks flag
                        to on. Emit that DOCTYPE token. Switch to the data state. */
                        $this->emitToken(array(
                            'type' => self::PARSEERROR,
                            'data' => 'unexpected-end-of-doctype'
                        ));
                        $this->token['force-quirks'] = true;
                        $this->emitToken($this->token);
                        $state = 'data';
                    } elseif ($char === false) {
                        /* EOF
                        Parse error. Set the DOCTYPE token's force-quirks flag
                        to on. Emit that DOCTYPE token. Reconsume the EOF
                        character in the data state. */
                        $this->emitToken(array(
                            'type' => self::PARSEERROR,
                            'data' => 'eof-in-doctype'
                        ));
                        $this->token['force-quirks'] = true;
                        $this->emitToken($this->token);
                        $this->stream->unget();
                        $state = 'data';
                    } else {
                        /* Anything else
                        Append the current input character to the current
                        DOCTYPE token's system identifier. Stay in the DOCTYPE
                        system identifier (double-quoted) state. */
                        $this->token['system'] .= $char;
                    }
                break;

                case 'DOCTYPE system identifier (single-quoted)':
                    /* Consume the next input character: */
                    $char = $this->stream->char();

                    if ($char === "'") {
                        /* U+0027 APOSTROPHE (')
                        Switch to the after DOCTYPE system identifier state. */
                        $state = 'after DOCTYPE system identifier';
                    } elseif ($char === '>') {
                        /* U+003E GREATER-THAN SIGN (>)
                        Parse error. Set the DOCTYPE token's force-quirks flag
                        to on. Emit that DOCTYPE token. Switch to the data state. */
                        $this->emitToken(array(
                            'type' => self::PARSEERROR,
                            'data' => 'unexpected-end-of-doctype'
                        ));
                        $this->token['force-quirks'] = true;
                        $this->emitToken($this->token);
                        $state = 'data';
                    } elseif ($char === false) {
                        /* EOF
                        Parse error. Set the DOCTYPE token's force-quirks flag
                        to on. Emit that DOCTYPE token. Reconsume the EOF
                        character in the data state. */
                        $this->emitToken(array(
                            'type' => self::PARSEERROR,
                            'data' => 'eof-in-doctype'
                        ));
                        $this->token['force-quirks'] = true;
                        $this->emitToken($this->token);
                        $this->stream->unget();
                        $state = 'data';
                    } else {
                        /* Anything else
                        Append the current input character to the current
                        DOCTYPE token's system identifier. Stay in the DOCTYPE
                        system identifier (double-quoted) state. */
                        $this->token['system'] .= $char;
                    }
                break;

                case 'after DOCTYPE system identifier':
                    /* Consume the next input character: */
                    $char = $this->stream->char();

                    if($char === "\t" || $char === "\n" || $char === "\x0c" || $char === ' ') {
                        /* U+0009 CHARACTER TABULATION
                           U+000A LINE FEED (LF)
                           U+000C FORM FEED (FF)
                           U+0020 SPACE
                        Stay in the after DOCTYPE system identifier state. */
                    } elseif ($char === '>') {
                        /* U+003E GREATER-THAN SIGN (>)
                        Emit the current DOCTYPE token. Switch to the data state. */
                        $this->emitToken($this->token);
                        $state = 'data';
                    } elseif ($char === false) {
                        /* Parse error. Set the DOCTYPE token's force-quirks
                        flag to on. Emit that DOCTYPE token. Reconsume the EOF
                        character in the data state. */
                        $this->emitToken(array(
                            'type' => self::PARSEERROR,
                            'data' => 'eof-in-doctype'
                        ));
                        $this->token['force-quirks'] = true;
                        $this->emitToken($this->token);
                        $this->stream->unget();
                        $state = 'data';
                    } else {
                        /* Anything else
                        Parse error. Switch to the bogus DOCTYPE state.
                        (This does not set the DOCTYPE token's force-quirks
                        flag to on.) */
                        $this->emitToken(array(
                            'type' => self::PARSEERROR,
                            'data' => 'unexpected-char-in-doctype'
                        ));
                        $state = 'bogus DOCTYPE';
                    }
                break;

                case 'bogus DOCTYPE':
                    /* Consume the next input character: */
                    $char = $this->stream->char();

                    if ($char === '>') {
                        /* U+003E GREATER-THAN SIGN (>)
                        Emit the DOCTYPE token. Switch to the data state. */
                        $this->emitToken($this->token);
                        $state = 'data';

                    } elseif($char === false) {
                        /* EOF
                        Emit the DOCTYPE token. Reconsume the EOF character in
                        the data state. */
                        $this->emitToken($this->token);
                        $this->stream->unget();
                        $state = 'data';

                    } else {
                        /* Anything else
                        Stay in the bogus DOCTYPE state. */
                    }
                break;

                // case 'cdataSection':

            }
        }
    }

    /**
     * Returns a serialized representation of the tree.
     */
    public function save() {
        return $this->tree->save();
    }

    /**
     * Returns the input stream.
     */
    public function stream() {
        return $this->stream;
    }

    private function consumeCharacterReference($allowed = false, $inattr = false) {
        // This goes quite far against spec, and is far closer to the Python
        // impl., mainly because we don't do the large unconsuming the spec
        // requires.

        // All consumed characters.
        $chars = $this->stream->char();

        /* This section defines how to consume a character
        reference. This definition is used when parsing character
        references in text and in attributes.

        The behavior depends on the identity of the next character
        (the one immediately after the U+0026 AMPERSAND character): */

        if (
            $chars[0] === "\x09" ||
            $chars[0] === "\x0A" ||
            $chars[0] === "\x0C" ||
            $chars[0] === "\x20" ||
            $chars[0] === '<' ||
            $chars[0] === '&' ||
            $chars === false ||
            $chars[0] === $allowed
        ) {
            /* U+0009 CHARACTER TABULATION
               U+000A LINE FEED (LF)
               U+000C FORM FEED (FF)
               U+0020 SPACE
               U+003C LESS-THAN SIGN
               U+0026 AMPERSAND
               EOF
               The additional allowed character, if there is one
            Not a character reference. No characters are consumed,
            and nothing is returned. (This is not an error, either.) */
            // We already consumed, so unconsume.
            $this->stream->unget();
            return '&';
        } elseif ($chars[0] === '#') {
            /* Consume the U+0023 NUMBER SIGN. */
            // Um, yeah, we already did that.
            /* The behavior further depends on the character after
            the U+0023 NUMBER SIGN: */
            $chars .= $this->stream->char();
            if (isset($chars[1]) && ($chars[1] === 'x' || $chars[1] === 'X')) {
                /* U+0078 LATIN SMALL LETTER X
                   U+0058 LATIN CAPITAL LETTER X */
                /* Consume the X. */
                // Um, yeah, we already did that.
                /* Follow the steps below, but using the range of
                characters U+0030 DIGIT ZERO through to U+0039 DIGIT
                NINE, U+0061 LATIN SMALL LETTER A through to U+0066
                LATIN SMALL LETTER F, and U+0041 LATIN CAPITAL LETTER
                A, through to U+0046 LATIN CAPITAL LETTER F (in other
                words, 0123456789, ABCDEF, abcdef). */
                $char_class = self::HEX;
                /* When it comes to interpreting the
                number, interpret it as a hexadecimal number. */
                $hex = true;
            } else {
                /* Anything else */
                // Unconsume because we shouldn't have consumed this.
                $chars = $chars[0];
                $this->stream->unget();
                /* Follow the steps below, but using the range of
                characters U+0030 DIGIT ZERO through to U+0039 DIGIT
                NINE (i.e. just 0123456789). */
                $char_class = self::DIGIT;
                /* When it comes to interpreting the number,
                interpret it as a decimal number. */
                $hex = false;
            }

            /* Consume as many characters as match the range of characters given above. */
            $consumed = $this->stream->charsWhile($char_class);
            if ($consumed === '' || $consumed === false) {
                /* If no characters match the range, then don't consume
                any characters (and unconsume the U+0023 NUMBER SIGN
                character and, if appropriate, the X character). This
                is a parse error; nothing is returned. */
                $this->emitToken(array(
                    'type' => self::PARSEERROR,
                    'data' => 'expected-numeric-entity'
                ));
                return '&' . $chars;
            } else {
                /* Otherwise, if the next character is a U+003B SEMICOLON,
                consume that too. If it isn't, there is a parse error. */
                if ($this->stream->char() !== ';') {
                    $this->stream->unget();
                    $this->emitToken(array(
                        'type' => self::PARSEERROR,
                        'data' => 'numeric-entity-without-semicolon'
                    ));
                }

                /* If one or more characters match the range, then take
                them all and interpret the string of characters as a number
                (either hexadecimal or decimal as appropriate). */
                $codepoint = $hex ? hexdec($consumed) : (int) $consumed;

                /* If that number is one of the numbers in the first column
                of the following table, then this is a parse error. Find the
                row with that number in the first column, and return a
                character token for the Unicode character given in the
                second column of that row. */
                $new_codepoint = HTML5_Data::getRealCodepoint($codepoint);
                if ($new_codepoint) {
                    $this->emitToken(array(
                        'type' => self::PARSEERROR,
                        'data' => 'illegal-windows-1252-entity'
                    ));
                    return HTML5_Data::utf8chr($new_codepoint);
                } else {
                    /* Otherwise, if the number is greater than 0x10FFFF, then 
                     * this is a parse error. Return a U+FFFD REPLACEMENT 
                     * CHARACTER. */
                    if ($codepoint > 0x10FFFF) {
                        $this->emitToken(array(
                            'type' => self::PARSEERROR,
                            'data' => 'overlong-character-entity' // XXX probably not correct
                        ));
                        return "\xEF\xBF\xBD";
                    }
                    /* Otherwise, return a character token for the Unicode 
                     * character whose code point is that number.  If the 
                     * number is in the range 0x0001 to 0x0008,    0x000E to 
                     * 0x001F,  0x007F  to 0x009F, 0xD800 to 0xDFFF, 0xFDD0 to 
                     * 0xFDEF, or is one of 0x000B, 0xFFFE, 0xFFFF, 0x1FFFE, 
                     * 0x1FFFF, 0x2FFFE, 0x2FFFF, 0x3FFFE, 0x3FFFF, 0x4FFFE, 
                     * 0x4FFFF, 0x5FFFE, 0x5FFFF, 0x6FFFE, 0x6FFFF, 0x7FFFE, 
                     * 0x7FFFF, 0x8FFFE, 0x8FFFF, 0x9FFFE, 0x9FFFF, 0xAFFFE, 
                     * 0xAFFFF, 0xBFFFE, 0xBFFFF, 0xCFFFE, 0xCFFFF, 0xDFFFE, 
                     * 0xDFFFF, 0xEFFFE, 0xEFFFF, 0xFFFFE, 0xFFFFF, 0x10FFFE, 
                     * or 0x10FFFF, then this is a parse error. */
                    // && has higher precedence than ||
                    if (
                        $codepoint >= 0x0000 && $codepoint <= 0x0008 ||
                        $codepoint === 0x000B ||
                        $codepoint >= 0x000E && $codepoint <= 0x001F ||
                        $codepoint >= 0x007F && $codepoint <= 0x009F ||
                        $codepoint >= 0xD800 && $codepoint <= 0xDFFF ||
                        $codepoint >= 0xFDD0 && $codepoint <= 0xFDEF ||
                        ($codepoint & 0xFFFE) === 0xFFFE ||
                        $codepoint == 0x10FFFF || $codepoint == 0x10FFFE
                    ) {
                        $this->emitToken(array(
                            'type' => self::PARSEERROR,
                            'data' => 'illegal-codepoint-for-numeric-entity'
                        ));
                    }
                    return HTML5_Data::utf8chr($codepoint);
                }
            }

        } else {
            /* Anything else */

            /* Consume the maximum number of characters possible,
            with the consumed characters matching one of the
            identifiers in the first column of the named character
            references table (in a case-sensitive manner). */
            // What we actually do here is consume as much as we can while it
            // matches the start of one of the identifiers in the first column.

            $refs = HTML5_Data::getNamedCharacterReferences();
            
            // Get the longest string which is the start of an identifier
            // ($chars) as well as the longest identifier which matches ($id)
            // and its codepoint ($codepoint).
            $codepoint = false;
            $char = $chars;
            while ($char !== false && isset($refs[$char])) {
                $refs = $refs[$char];
                if (isset($refs['codepoint'])) {
                    $id = $chars;
                    $codepoint = $refs['codepoint'];
                }
                $chars .= $char = $this->stream->char();
            }
            
            // Unconsume the one character we just took which caused the while
            // statement to fail. This could be anything and could cause state
            // changes (as if it matches the while loop it must be
            // alphanumeric so we can just concat it to whatever we get later).
            $this->stream->unget();
            if ($char !== false) {
                $chars = substr($chars, 0, -1);
            }

            /* If no match can be made, then this is a parse error.
            No characters are consumed, and nothing is returned. */
            if (!$codepoint) {
                $this->emitToken(array(
                    'type' => self::PARSEERROR,
                    'data' => 'expected-named-entity'
                ));
                return '&' . $chars;
            }

            /* If the last character matched is not a U+003B SEMICOLON
            (;), there is a parse error. */
            $semicolon = true;
            if (substr($id, -1) !== ';') {
                $this->emitToken(array(
                    'type' => self::PARSEERROR,
                    'data' => 'named-entity-without-semicolon'
                ));
                $semicolon = false;
            }

            /* If the character reference is being consumed as part of
            an attribute, and the last character matched is not a
            U+003B SEMICOLON (;), and the next character is in the
            range U+0030 DIGIT ZERO to U+0039 DIGIT NINE, U+0041
            LATIN CAPITAL LETTER A to U+005A LATIN CAPITAL LETTER Z,
            or U+0061 LATIN SMALL LETTER A to U+007A LATIN SMALL LETTER Z,
            then, for historical reasons, all the characters that were
            matched after the U+0026 AMPERSAND (&) must be unconsumed,
            and nothing is returned. */
            if ($inattr && !$semicolon) {
                // The next character is either the next character in $chars or in the stream.
                if (strlen($chars) > strlen($id)) {
                    $next = substr($chars, strlen($id), 1);
                } else {
                    $next = $this->stream->char();
                    $this->stream->unget();
                }
                if (
                    '0' <= $next && $next <= '9' ||
                    'A' <= $next && $next <= 'Z' ||
                    'a' <= $next && $next <= 'z'
                ) {
                    return '&' . $chars;
                }
            }

            /* Otherwise, return a character token for the character
            corresponding to the character reference name (as given
            by the second column of the named character references table). */
            return HTML5_Data::utf8chr($codepoint) . substr($chars, strlen($id));
        }
    }

    private function characterReferenceInAttributeValue($allowed = false) {
        /* Attempt to consume a character reference. */
        $entity = $this->consumeCharacterReference($allowed, true);

        /* If nothing is returned, append a U+0026 AMPERSAND
        character to the current attribute's value.

        Otherwise, append the returned character token to the
        current attribute's value. */
        $char = (!$entity)
            ? '&'
            : $entity;

        $last = count($this->token['attr']) - 1;
        $this->token['attr'][$last]['value'] .= $char;

        /* Finally, switch back to the attribute value state that you
        were in when were switched into this state. */
    }

    /**
     * Emits a token, passing it on to the tree builder.
     */
    protected function emitToken($token, $checkStream = true, $dry = false) {
        if ($checkStream) {
            // Emit errors from input stream.
            while ($this->stream->errors) {
                $this->emitToken(array_shift($this->stream->errors), false);
            }
        }
        if($token['type'] === self::ENDTAG && !empty($token['attr'])) {
            for ($i = 0; $i < count($token['attr']); $i++) {
                $this->emitToken(array(
                    'type' => self::PARSEERROR,
                    'data' => 'attributes-in-end-tag'
                ));
            }
        }
        if($token['type'] === self::ENDTAG && !empty($token['self-closing'])) {
            $this->emitToken(array(
                'type' => self::PARSEERROR,
                'data' => 'self-closing-flag-on-end-tag',
            ));
        }
        if($token['type'] === self::STARTTAG) {
            // This could be changed to actually pass the tree-builder a hash
            $hash = array();
            foreach ($token['attr'] as $keypair) {
                if (isset($hash[$keypair['name']])) {
                    $this->emitToken(array(
                        'type' => self::PARSEERROR,
                        'data' => 'duplicate-attribute',
                    ));
                } else {
                    $hash[$keypair['name']] = $keypair['value'];
                }
            }
        }

        if(!$dry) {
            // the current structure of attributes is not a terribly good one
            $this->tree->emitToken($token);
        }

        if(!$dry && is_int($this->tree->content_model)) {
            $this->content_model = $this->tree->content_model;
            $this->tree->content_model = null;

        } elseif($token['type'] === self::ENDTAG) {
            $this->content_model = self::PCDATA;
        }
    }
}

