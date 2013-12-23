<?php

/*

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

class HTML5_InputStream {
    /**
     * The string data we're parsing.
     */
    private $data;

    /**
     * The current integer byte position we are in $data
     */
    private $char;

    /**
     * Length of $data; when $char === $data, we are at the end-of-file.
     */
    private $EOF;

    /**
     * Parse errors.
     */
    public $errors = array();

    /**
     * @param $data Data to parse
     */
    public function __construct($data) {

        /* Given an encoding, the bytes in the input stream must be
        converted to Unicode characters for the tokeniser, as
        described by the rules for that encoding, except that the
        leading U+FEFF BYTE ORDER MARK character, if any, must not
        be stripped by the encoding layer (it is stripped by the rule below).

        Bytes or sequences of bytes in the original byte stream that
        could not be converted to Unicode characters must be converted
        to U+FFFD REPLACEMENT CHARACTER code points. */

        // XXX currently assuming input data is UTF-8; once we
        // build encoding detection this will no longer be the case
        //
        // We previously had an mbstring implementation here, but that
        // implementation is heavily non-conforming, so it's been
        // omitted.
        if (extension_loaded('iconv')) {
            // non-conforming
            $data = @iconv('UTF-8', 'UTF-8//IGNORE', $data);
        } else {
            // we can make a conforming native implementation
            throw new Exception('Not implemented, please install mbstring or iconv');
        }

        /* One leading U+FEFF BYTE ORDER MARK character must be
        ignored if any are present. */
        if (substr($data, 0, 3) === "\xEF\xBB\xBF") {
            $data = substr($data, 3);
        }

        /* All U+0000 NULL characters in the input must be replaced
        by U+FFFD REPLACEMENT CHARACTERs. Any occurrences of such
        characters is a parse error. */
        for ($i = 0, $count = substr_count($data, "\0"); $i < $count; $i++) {
            $this->errors[] = array(
                'type' => HTML5_Tokenizer::PARSEERROR,
                'data' => 'null-character'
            );
        }
        /* U+000D CARRIAGE RETURN (CR) characters and U+000A LINE FEED
        (LF) characters are treated specially. Any CR characters
        that are followed by LF characters must be removed, and any
        CR characters not followed by LF characters must be converted
        to LF characters. Thus, newlines in HTML DOMs are represented
        by LF characters, and there are never any CR characters in the
        input to the tokenization stage. */
        $data = str_replace(
            array(
                "\0",
                "\r\n",
                "\r"
            ),
            array(
                "\xEF\xBF\xBD",
                "\n",
                "\n"
            ),
            $data
        );

        /* Any occurrences of any characters in the ranges U+0001 to
        U+0008, U+000B,  U+000E to U+001F,  U+007F  to U+009F,
        U+D800 to U+DFFF , U+FDD0 to U+FDEF, and
        characters U+FFFE, U+FFFF, U+1FFFE, U+1FFFF, U+2FFFE, U+2FFFF,
        U+3FFFE, U+3FFFF, U+4FFFE, U+4FFFF, U+5FFFE, U+5FFFF, U+6FFFE,
        U+6FFFF, U+7FFFE, U+7FFFF, U+8FFFE, U+8FFFF, U+9FFFE, U+9FFFF,
        U+AFFFE, U+AFFFF, U+BFFFE, U+BFFFF, U+CFFFE, U+CFFFF, U+DFFFE,
        U+DFFFF, U+EFFFE, U+EFFFF, U+FFFFE, U+FFFFF, U+10FFFE, and
        U+10FFFF are parse errors. (These are all control characters
        or permanently undefined Unicode characters.) */
        // Check PCRE is loaded.
        if (extension_loaded('pcre')) {
            $count = preg_match_all(
                '/(?:
                    [\x01-\x08\x0B\x0E-\x1F\x7F] # U+0001 to U+0008, U+000B,  U+000E to U+001F and U+007F
                |
                    \xC2[\x80-\x9F] # U+0080 to U+009F
                |
                    \xED(?:\xA0[\x80-\xFF]|[\xA1-\xBE][\x00-\xFF]|\xBF[\x00-\xBF]) # U+D800 to U+DFFFF
                |
                    \xEF\xB7[\x90-\xAF] # U+FDD0 to U+FDEF
                |
                    \xEF\xBF[\xBE\xBF] # U+FFFE and U+FFFF
                |
                    [\xF0-\xF4][\x8F-\xBF]\xBF[\xBE\xBF] # U+nFFFE and U+nFFFF (1 <= n <= 10_{16})
                )/x',
                $data,
                $matches
            );
            for ($i = 0; $i < $count; $i++) {
                $this->errors[] = array(
                    'type' => HTML5_Tokenizer::PARSEERROR,
                    'data' => 'invalid-codepoint'
                );
            }
        } else {
            // XXX: Need non-PCRE impl, probably using substr_count
        }

        $this->data = $data;
        $this->char = 0;
        $this->EOF  = strlen($data);
    }

    /**
     * Returns the current line that the tokenizer is at.
     */
    public function getCurrentLine() {
        // Check the string isn't empty
        if($this->EOF) {
            // Add one to $this->char because we want the number for the next
            // byte to be processed.
            return substr_count($this->data, "\n", 0, min($this->char, $this->EOF)) + 1;
        } else {
            // If the string is empty, we are on the first line (sorta).
            return 1;
        }
    }

    /**
     * Returns the current column of the current line that the tokenizer is at.
     */
    public function getColumnOffset() {
        // strrpos is weird, and the offset needs to be negative for what we
        // want (i.e., the last \n before $this->char). This needs to not have
        // one (to make it point to the next character, the one we want the
        // position of) added to it because strrpos's behaviour includes the
        // final offset byte.
        $lastLine = strrpos($this->data, "\n", $this->char - 1 - strlen($this->data));

        // However, for here we want the length up until the next byte to be
        // processed, so add one to the current byte ($this->char).
        if($lastLine !== false) {
            $findLengthOf = substr($this->data, $lastLine + 1, $this->char - 1 - $lastLine);
        } else {
            $findLengthOf = substr($this->data, 0, $this->char);
        }

        // Get the length for the string we need.
        if(extension_loaded('iconv')) {
            return iconv_strlen($findLengthOf, 'utf-8');
        } elseif(extension_loaded('mbstring')) {
            return mb_strlen($findLengthOf, 'utf-8');
        } elseif(extension_loaded('xml')) {
            return strlen(utf8_decode($findLengthOf));
        } else {
            $count = count_chars($findLengthOf);
            // 0x80 = 0x7F - 0 + 1 (one added to get inclusive range)
            // 0x33 = 0xF4 - 0x2C + 1 (one added to get inclusive range)
            return array_sum(array_slice($count, 0, 0x80)) +
                   array_sum(array_slice($count, 0xC2, 0x33));
        }
    }

    /**
     * Retrieve the currently consume character.
     * @note This performs bounds checking
     */
    public function char() {
        return ($this->char++ < $this->EOF)
            ? $this->data[$this->char - 1]
            : false;
    }

    /**
     * Get all characters until EOF.
     * @note This performs bounds checking
     */
    public function remainingChars() {
        if($this->char < $this->EOF) {
            $data = substr($this->data, $this->char);
            $this->char = $this->EOF;
            return $data;
        } else {
            return false;
        }
    }

    /**
     * Matches as far as possible until we reach a certain set of bytes
     * and returns the matched substring.
     * @param $bytes Bytes to match.
     */
    public function charsUntil($bytes, $max = null) {
        if ($this->char < $this->EOF) {
            if ($max === 0 || $max) {
                $len = strcspn($this->data, $bytes, $this->char, $max);
            } else {
                $len = strcspn($this->data, $bytes, $this->char);
            }
            $string = (string) substr($this->data, $this->char, $len);
            $this->char += $len;
            return $string;
        } else {
            return false;
        }
    }

    /**
     * Matches as far as possible with a certain set of bytes
     * and returns the matched substring.
     * @param $bytes Bytes to match.
     */
    public function charsWhile($bytes, $max = null) {
        if ($this->char < $this->EOF) {
            if ($max === 0 || $max) {
                $len = strspn($this->data, $bytes, $this->char, $max);
            } else {
                $len = strspn($this->data, $bytes, $this->char);
            }
            $string = (string) substr($this->data, $this->char, $len);
            $this->char += $len;
            return $string;
        } else {
            return false;
        }
    }

    /**
     * Unconsume one character.
     */
    public function unget() {
        if ($this->char <= $this->EOF) {
            $this->char--;
        }
    }
}
