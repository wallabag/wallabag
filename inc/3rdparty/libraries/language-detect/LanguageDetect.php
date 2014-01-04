<?php

/**
 * Detects the language of a given piece of text.
 *
 * Attempts to detect the language of a sample of text by correlating ranked
 * 3-gram frequencies to a table of 3-gram frequencies of known languages.
 *
 * Implements a version of a technique originally proposed by Cavnar & Trenkle 
 * (1994): "N-Gram-Based Text Categorization" 
 *
 * PHP versions 4 and 5
 *
 * @category   Text
 * @package    Text_LanguageDetect
 * @author     Nicholas Pisarro <infinityminusnine+pear@gmail.com>
 * @copyright  2005-2006 Nicholas Pisarro
 * @license    http://www.debian.org/misc/bsd.license BSD
 * @version    CVS: $Id: LanguageDetect.php,v 1.20 2008/07/01 02:09:15 taak Exp $
 * @link       http://pear.php.net/package/Text_LanguageDetect/
 * @link       http://langdetect.blogspot.com/
 */

//require_once 'PEAR.php';
require_once 'Parser.php';

/**
 * Language detection class
 *
 * Requires the langauge model database (lang.dat) that should have
 * accompanied this class definition in order to be instantiated.
 *
 * Example usage:
 *
 * <code>
 * require_once 'Text/LanguageDetect.php';
 *
 * $l = new Text_LanguageDetect;
 *
 * $stdin = fopen('php://stdin', 'r');
 *
 * echo "Supported languages:\n";
 *
 * $langs = $l->getLanguages();
 * if (PEAR::isError($langs)) {
 *     die($langs->getMessage());
 * }
 *
 * sort($langs);
 * echo join(', ', $langs);
 *
 * while ($line = fgets($stdin)) {
 *     print_r($l->detect($line, 4));
 * }
 * </code>
 *
 * @category   Text
 * @package    Text_LanguageDetect
 * @author     Nicholas Pisarro <infinityminusnine+pear@gmail.com>
 * @copyright  2005 Nicholas Pisarro
 * @license    http://www.debian.org/misc/bsd.license BSD
 * @version    Release: @package_version@
 * @todo       allow users to generate their own language models
 */
 
class Text_LanguageDetect
{
    /** 
     * The filename that stores the trigram data for the detector
     *
     * If this value starts with a slash (/) or a dot (.) the value of 
     * $this->_data_dir will be ignored
     * 
     * @var      string
     * @access   private
     */
    var $_db_filename = './lang.dat';

    /**
     * The filename that stores the unicode block definitions
     *
     * If this value starts with a slash (/) or a dot (.) the value of 
     * $this->_data_dir will be ignored
     * 
     * @var string
     * @access private
     */
    var $_unicode_db_filename = './unicode_blocks.dat';

    /**
     * The data directory
     *
     * Should be set by PEAR installer
     *
     * @var      string
     * @access   private
     */
    var $_data_dir = '@data_dir@';

    /**
     * The trigram data for comparison
     * 
     * Will be loaded on start from $this->_db_filename
     *
     * May be set to a PEAR_Error object if there is an error during its 
     * initialization
     *
     * @var      array
     * @access   private
     */
    var $_lang_db = array();

    /**
     * stores the map of the trigram data to unicode characters
     *
     * @access private
     * @var array
     */
    var $_unicode_map;

    /**
     * The size of the trigram data arrays
     * 
     * @var      int
     * @access   private
     */
    var $_threshold = 300;

    /**
     * the maximum possible score.
     *
     * needed for score normalization. Different depending on the
     * perl compatibility setting
     *
     * @access  private
     * @var     int
     * @see     setPerlCompatible()
     */
    var $_max_score = 0;

    /**
     * Whether or not to simulate perl's Language::Guess exactly
     * 
     * @access  private
     * @var     bool
     * @see     setPerlCompatible()
     */
    var $_perl_compatible = false;

    /**
     * Whether to use the unicode block detection to speed up processing
     *
     * @access private
     * @var bool
     */
    var $_use_unicode_narrowing = true;

    /**
     * stores the result of the clustering operation
     *
     * @access  private
     * @var     array
     * @see     clusterLanguages()
     */
    var $_clusters;

    /**
     * Constructor
     *
     * Will attempt to load the language database. If it fails, you will get
     * a PEAR_Error object returned when you try to use detect()
     *
     */
    function Text_LanguageDetect($db=null, $unicode_db=null)
    {
		if (isset($db)) $this->_db_filename = $db;
		if (isset($unicode_db)) $this->_unicode_db_filename = $unicode_db;
		
        $data = $this->_readdb($this->_db_filename);
        $this->_lang_db = $data['trigram'];

        if (isset($data['trigram-unicodemap'])) {
            $this->_unicode_map = $data['trigram-unicodemap'];
        }

        // Not yet implemented:
        if (isset($data['trigram-clusters'])) {
            $this->_clusters = $data['trigram-clusters'];
        }		
    }

    /**
     * Returns the path to the location of the database
     *
     * @access    private
     * @return    string    expected path to the language model database
     */
    function _get_data_loc($fname)
    {
        return $fname;
    }

    /**
     * Loads the language trigram database from filename
     *
     * Trigram datbase should be a serialize()'d array
     * 
     * @access    private
     * @param     string      $fname   the filename where the data is stored
     * @return    array                the language model data
     * @throws    PEAR_Error
     */
    function _readdb($fname)
    {
        // finds the correct data dir
        $fname = $this->_get_data_loc($fname);

        // input check
        if (!file_exists($fname)) {
            throw new Exception('Language database does not exist.');
        } elseif (!is_readable($fname)) {
            throw new Exception('Language database is not readable.');
        }

        if (function_exists('file_get_contents')) {
            return unserialize(file_get_contents($fname));
        } else {
            // if you don't have file_get_contents(), 
            // then this is the next fastest way
            ob_start();
            readfile($fname);
            $contents = ob_get_contents();
            ob_end_clean();
            return unserialize($contents);
        }
    }


    /**
     * Checks if this object is ready to detect languages
     * 
     * @access   private
     * @param    mixed   &$err  error object to be returned by reference, if any
     * @return   bool           true if no errors
     */
    function _setup_ok(&$err)
    {
        if (!is_array($this->_lang_db)) {
            if (ini_get('magic_quotes_runtime')) {
                throw new Exception('Error loading database. Try turning magic_quotes_runtime off.');
            } else {
                throw new Exception('Language database is not an array.');
            }
            return false;

        } elseif (empty($this->_lang_db)) {
            throw new Exception('Language database has no elements.');
            return false;

        } else {
            return true;
        }
    }

    /**
     * Omits languages
     *
     * Pass this function the name of or an array of names of 
     * languages that you don't want considered
     *
     * If you're only expecting a limited set of languages, this can greatly 
     * speed up processing
     *
     * @access   public
     * @param    mixed  $omit_list      language name or array of names to omit
     * @param    bool   $include_only   if true will include (rather than 
     *                                  exclude) only those in the list
     * @return   int                    number of languages successfully deleted
     * @throws   PEAR_Error
     */
    function omitLanguages($omit_list, $include_only = false)
    {

        // setup check
        if (!$this->_setup_ok($err)) {
            return $err;
        }

        $deleted = 0;

        // deleting the given languages
        if (!$include_only) {
            if (!is_array($omit_list)) {
                $omit_list = strtolower($omit_list); // case desensitize
                if (isset($this->_lang_db[$omit_list])) {
                    unset($this->_lang_db[$omit_list]);
                    $deleted++;
                }
            } else {
                foreach ($omit_list as $omit_lang) {
                    if (isset($this->_lang_db[$omit_lang])) {
                        unset($this->_lang_db[$omit_lang]);
                        $deleted++;
                    } 
                }
            }

        // deleting all except the given languages
        } else {
            if (!is_array($omit_list)) {
                $omit_list = array($omit_list);
            }

            // case desensitize
            foreach ($omit_list as $key => $omit_lang) {
                $omit_list[$key] = strtolower($omit_lang);
            }

            foreach (array_keys($this->_lang_db) as $lang) {
                if (!in_array($lang, $omit_list)) {
                    unset($this->_lang_db[$lang]);
                    $deleted++;
                }
            }
        }

        // reset the cluster cache if the number of languages changes
        // this will then have to be recalculated
        if (isset($this->_clusters) && $deleted > 0) {
            unset($this->_clusters);
        }

        return $deleted;
    }


    /**
     * Returns the number of languages that this object can detect
     *
     * @access public
     * @return int            the number of languages
     * @throws PEAR_Error
     */
    function getLanguageCount()
    {
        if (!$this->_setup_ok($err)) {
            return $err;
        } else {
            return count($this->_lang_db);
        }
    }

    /**
     * Returns true if a given language exists
     *
     * If passed an array of names, will return true only if all exist
     *
     * @access    public
     * @param     mixed       $lang    language name or array of language names
     * @return    bool                 true if language model exists
     * @throws    PEAR_Error
     */
    function languageExists($lang)
    {
        if (!$this->_setup_ok($err)) {
            return $err;
        } else {
            // string
            if (is_string($lang)) {
                return isset($this->_lang_db[strtolower($lang)]);

            // array
            } elseif (is_array($lang)) {
                foreach ($lang as $test_lang) {
                    if (!isset($this->_lang_db[strtolower($test_lang)])) {
                        return false;
                    } 
                }
                return true;

            // other (error)
            } else {
                throw new Exception('Unknown type passed to languageExists()');
            }
        }
    }

    /**
     * Returns the list of detectable languages
     *
     * @access public
     * @return array        the names of the languages known to this object
     * @throws PEAR_Error
     */
    function getLanguages()
    {
        if (!$this->_setup_ok($err)) {
            return $err;
        } else {
            return array_keys($this->_lang_db);
        }
    }

    /**
     * Make this object behave like Language::Guess
     * 
     * @access    public
     * @param     bool     $setting     false to turn off perl compatibility
     */
    function setPerlCompatible($setting = true)
    {
        if (is_bool($setting)) { // input check
            $this->_perl_compatible = $setting;

            if ($setting == true) {
                $this->_max_score = $this->_threshold;
            } else {
                $this->_max_score = 0;
            }
        }

    }

    /**
     * Whether to use unicode block ranges in detection
     *
     * Should speed up most detections if turned on (detault is on). In some
     * circumstances it may be slower, such as for large text samples (> 10K)
     * in languages that use latin scripts. In other cases it should speed up
     * detection noticeably.
     *
     * @access  public
     * @param   bool    $setting    false to turn off
     */
    function useUnicodeBlocks($setting = true)
    {
        if (is_bool($setting)) {
            $this->_use_unicode_narrowing = $setting;
        }
    }

    /**
     * Converts a piece of text into trigrams
     *
     * Superceded by the Text_LanguageDetect_Parser class 
     *
     * @access    private
     * @param     string    $text    text to convert
     * @return    array              array of trigram frequencies
     */
    function _trigram($text)
    {
        $s = new Text_LanguageDetect_Parser($text, $this->_db_filename, $this->_unicode_db_filename);
        $s->prepareTrigram();
        $s->prepareUnicode(false);
        $s->setPadStart(!$this->_perl_compatible);
        $s->analyze();
        return $s->getTrigramFreqs();
    }

    /**
     * Converts a set of trigrams from frequencies to ranks
     *
     * Thresholds (cuts off) the list at $this->_threshold
     *
     * @access    protected
     * @param     array     $arr     array of trgram 
     * @return    array              ranks of trigrams
     */
    function _arr_rank(&$arr)
    {

        // sorts alphabetically first as a standard way of breaking rank ties
        $this->_bub_sort($arr);

        // below might also work, but seemed to introduce errors in testing
        //ksort($arr);
        //asort($arr);

        $rank = array();

        $i = 0;
        foreach ($arr as $key => $value) {
            $rank[$key] = $i++;

            // cut off at a standard threshold
            if ($i >= $this->_threshold) {
                break;
            }
        }

        return $rank;
    }

    /**
     * Sorts an array by value breaking ties alphabetically
     * 
     * @access   private
     * @param    array     &$arr     the array to sort
     */
    function _bub_sort(&$arr)
    {
        // should do the same as this perl statement:
        // sort { $trigrams{$b} == $trigrams{$a} ?  $a cmp $b : $trigrams{$b} <=> $trigrams{$a} }

        // needs to sort by both key and value at once
        // using the key to break ties for the value

        // converts array into an array of arrays of each key and value
        // may be a better way of doing this
        $combined = array();

        foreach ($arr as $key => $value) {
            $combined[] = array($key, $value);
        }

        usort($combined, array($this, '_sort_func'));

        $replacement = array();
        foreach ($combined as $key => $value) {
            list($new_key, $new_value) = $value;
            $replacement[$new_key] = $new_value;
        }

        $arr = $replacement;
    }

    /**
     * Sort function used by bubble sort
     *
     * Callback function for usort(). 
     *
     * @access   private
     * @param    array        first param passed by usort()
     * @param    array        second param passed by usort()
     * @return   int          1 if $a is greater, -1 if not
     * @see      _bub_sort()
     */
    function _sort_func($a, $b)
    {
        // each is actually a key/value pair, so that it can compare using both
        list($a_key, $a_value) = $a;
        list($b_key, $b_value) = $b;

        // if the values are the same, break ties using the key
        if ($a_value == $b_value) {
            return strcmp($a_key, $b_key);

        // if not, just sort normally
        } else {
            if ($a_value > $b_value) {
                return -1;
            } else {
                return 1;
            }
        }

        // 0 should not be possible because keys must be unique
    }

    /**
     * Calculates a linear rank-order distance statistic between two sets of 
     * ranked trigrams
     *
     * Sums the differences in rank for each trigram. If the trigram does not 
     * appear in both, consider it a difference of $this->_threshold.
     *
     * This distance measure was proposed by Cavnar & Trenkle (1994). Despite
     * its simplicity it has been shown to be highly accurate for language
     * identification tasks.
     *
     * @access  private
     * @param   array    $arr1  the reference set of trigram ranks
     * @param   array    $arr2  the target set of trigram ranks
     * @return  int             the sum of the differences between the ranks of
     *                          the two trigram sets
     */
    function _distance(&$arr1, &$arr2)
    {
        $sumdist = 0;

        foreach ($arr2 as $key => $value) {
            if (isset($arr1[$key])) {
                $distance = abs($value - $arr1[$key]);
            } else {
                // $this->_threshold sets the maximum possible distance value
                // for any one pair of trigrams
                $distance = $this->_threshold;
            }
            $sumdist += $distance;
        }

        return $sumdist;

        // todo: there are other distance statistics to try, e.g. relative
        //       entropy, but they're probably more costly to compute
    }

    /**
     * Normalizes the score returned by _distance()
     * 
     * Different if perl compatible or not
     *
     * @access    private
     * @param     int    $score          the score from _distance()
     * @param     int    $base_count     the number of trigrams being considered
     * @return    float                  the normalized score
     * @see       _distance()
     */
    function _normalize_score($score, $base_count = null)
    {
        if ($base_count === null) {
            $base_count = $this->_threshold;
        }

        if (!$this->_perl_compatible) {
            return 1 - ($score / $base_count / $this->_threshold);
        } else {
            return floor($score / $base_count);
        }
    }


    /**
     * Detects the closeness of a sample of text to the known languages
     *
     * Calculates the statistical difference between the text and
     * the trigrams for each language, normalizes the score then
     * returns results for all languages in sorted order
     *
     * If perl compatible, the score is 300-0, 0 being most similar.
     * Otherwise, it's 0-1 with 1 being most similar.
     * 
     * The $sample text should be at least a few sentences in length;
     * should be ascii-7 or utf8 encoded, if another and the mbstring extension
     * is present it will try to detect and convert. However, experience has
     * shown that mb_detect_encoding() *does not work very well* with at least 
     * some types of encoding.
     *
     * @access  public
     * @param   string  $sample a sample of text to compare.
     * @param   int     $limit  if specified, return an array of the most likely
     *                           $limit languages and their scores.
     * @return  mixed       sorted array of language scores, blank array if no 
     *                      useable text was found, or PEAR_Error if error 
     *                      with the object setup
     * @see     _distance()
     * @throws  PEAR_Error
     */
    function detect($sample, $limit = 0)
    {
        if (!$this->_setup_ok($err)) {
            return $err;
        }

        // input check
        if (!Text_LanguageDetect_Parser::validateString($sample)) {
            return array();
        }

        // check char encoding
        // (only if mbstring extension is compiled and PHP > 4.0.6)
        if (function_exists('mb_detect_encoding') 
            && function_exists('mb_convert_encoding')) {

            // mb_detect_encoding isn't very reliable, to say the least
            // detection should still work with a sufficient sample of ascii characters
            $encoding = mb_detect_encoding($sample);

            // mb_detect_encoding() will return FALSE if detection fails
            // don't attempt conversion if that's the case
            if ($encoding != 'ASCII' && $encoding != 'UTF-8' && $encoding !== false) {
            
                if (function_exists('mb_list_encodings')) {
 
                    // verify the encoding exists in mb_list_encodings
                    if (in_array($encoding, mb_list_encodings())) {
                        $sample = mb_convert_encoding($sample, 'UTF-8', $encoding);
                    }

                    // if the previous condition failed:
                    // somehow we detected an encoding that also we don't support

                } else {
                    // php 4 doesnt have mb_list_encodings()
                    // so attempt with error suppression
                    $sample = @mb_convert_encoding($sample, 'UTF-8', $encoding);
                }
            }
        }

        $sample_obj = new Text_LanguageDetect_Parser($sample, $this->_db_filename, $this->_unicode_db_filename);
        $sample_obj->prepareTrigram();
        if ($this->_use_unicode_narrowing) {
            $sample_obj->prepareUnicode();
        }
        $sample_obj->setPadStart(!$this->_perl_compatible);
        $sample_obj->analyze();

        $trigram_freqs =& $sample_obj->getTrigramRanks();
        $trigram_count = count($trigram_freqs);

        if ($trigram_count == 0) {
            return array();
        }

        $scores = array();

        // use unicode block detection to narrow down the possibilities
        if ($this->_use_unicode_narrowing) {
            $blocks =& $sample_obj->getUnicodeBlocks();

            if (is_array($blocks)) {
                $present_blocks = array_keys($blocks);
            } else {
                throw new Exception('Error during block detection');
            }

            $possible_langs = array();

            foreach ($present_blocks as $blockname) {
                if (isset($this->_unicode_map[$blockname])) {

                    $possible_langs = array_merge(
                        $possible_langs,
                        array_keys($this->_unicode_map[$blockname])
                    );

                    // todo: faster way to do this?
                }
            }

            // could also try an intersect operation rather than a union
            // in other words, choose languages whose trigrams contain 
            // ALL of the unicode blocks found in this sample
            // would improve speed but would be completely thrown off by an
            // unexpected character, like an umlaut appearing in english text

            $possible_langs = array_intersect(
                        array_keys($this->_lang_db),
                        array_unique($possible_langs)
            );

            // needs to intersect it with the keys of _lang_db in case 
            // languages have been omitted

        // or just try 'em all
        } else {
            $possible_langs = array_keys($this->_lang_db);
        }


        foreach ($possible_langs as $lang) {
            $scores[$lang] =
                $this->_normalize_score(
                        $this->_distance($this->_lang_db[$lang], $trigram_freqs),
                        $trigram_count);
        }

        unset($sample_obj);

        if ($this->_perl_compatible) {
            asort($scores);
        } else {
            arsort($scores);
        }

        // todo: drop languages with a score of $this->_max_score?

        // limit the number of returned scores
        if ($limit && is_numeric($limit)) {
            $limited_scores = array();

            $i = 0;

            foreach ($scores as $key => $value) {
                if ($i++ >= $limit) {
                    break;
                }

                $limited_scores[$key] = $value;
            }

            return $limited_scores;
        } else {
            return $scores;
        }
    }

    /**
     * Returns only the most similar language to the text sample
     *
     * Calls $this->detect() and returns only the top result
     * 
     * @access   public
     * @param    string    $sample    text to detect the language of
     * @return   string               the name of the most likely language
     *                                or null if no language is similar
     * @see      detect()
     * @throws   PEAR_Error
     */
    function detectSimple($sample)
    {
        $scores = $this->detect($sample, 1);

        // if top language has the maximum possible score,
        // then the top score will have been picked at random
        if (    !is_array($scores) 
                || empty($scores) 
                || current($scores) == $this->_max_score) {

            return null;

        } else {
            return ucfirst(key($scores));
        }
    }

    /**
     * Returns an array containing the most similar language and a confidence
     * rating
     * 
     * Confidence is a simple measure calculated from the similarity score
     * minus the similarity score from the next most similar language
     * divided by the highest possible score. Languages that have closely
     * related cousins (e.g. Norwegian and Danish) should generally have lower
     * confidence scores.
     *
     * The similarity score answers the question "How likely is the text the
     * returned language regardless of the other languages considered?" The 
     * confidence score is one way of answering the question "how likely is the
     * text the detected language relative to the rest of the language model
     * set?"
     *
     * To see how similar languages are a priori, see languageSimilarity()
     * 
     * @access   public
     * @param    string    $sample    text for which language will be detected
     * @return   array     most similar language, score and confidence rating
     *                     or null if no language is similar
     * @see      detect()
     * @throws   PEAR_Error
     */
    function detectConfidence($sample)
    {
        $scores = $this->detect($sample, 2);

        // if most similar language has the max score, it 
        // will have been picked at random
        if (    !is_array($scores) 
                || empty($scores) 
                || current($scores) == $this->_max_score) {

            return null;
        }

        $arr['language'] = ucfirst(key($scores));
        $arr['similarity'] = current($scores);
        if (next($scores) !== false) { // if false then no next element
            // the goal is to return a higher value if the distance between
            // the similarity of the first score and the second score is high

            if ($this->_perl_compatible) {

                $arr['confidence'] =
                    (current($scores) - $arr['similarity']) / $this->_max_score;

            } else {

                $arr['confidence'] = $arr['similarity'] - current($scores);

            }

        } else {
            $arr['confidence'] = null;
        }

        return $arr;
    }

    /**
     * Returns the distribution of unicode blocks in a given utf8 string
     *
     * For the block name of a single char, use unicodeBlockName()
     * 
     * @access public
     * @param string $str input string. Must be ascii or utf8
     * @param bool $skip_symbols if true, skip ascii digits, symbols and 
     *                           non-printing characters. Includes spaces,
     *                           newlines and common punctutation characters.
     * @return array
     * @throws PEAR_Error
     */
    function detectUnicodeBlocks($str, $skip_symbols)
    {
        // input check
        if (!is_bool($skip_symbols)) {
            throw new Exception('Second parameter must be boolean');
        } 

        if (!is_string($str)) {
            throw new Exception('First parameter was not a string');
        }

        $sample_obj = new Text_LanguageDetect_Parser($str, $this->_db_filename, $this->_unicode_db_filename);
        $sample_obj->prepareUnicode();
        $sample_obj->prepareTrigram(false);
        $sample_obj->setUnicodeSkipSymbols($skip_symbols);
        $sample_obj->analyze();
        $blocks =& $sample_obj->getUnicodeBlocks();
        unset($sample_obj);
        return $blocks;
    }

    /**
     * Returns the block name for a given unicode value
     *
     * If passed a string, will assume it is being passed a UTF8-formatted 
     * character and will automatically convert. Otherwise it will assume it
     * is being passed a numeric unicode value.
     *
     * Make sure input is of the correct type!
     *
     * @access public
     * @param mixed $unicode unicode value or utf8 char
     * @return mixed the block name string or false if not found
     * @throws PEAR_Error
     */
    function unicodeBlockName($unicode) {
        if (is_string($unicode)) {
            // assume it is being passed a utf8 char, so convert it

            // input check
            if ($this->utf8strlen($unicode) > 1) {
                throw new Exception('Pass this function only a single char');
            }

            $unicode = $this->_utf8char2unicode($unicode);

            if ($unicode == -1) {
                throw new Exception('Malformatted char');
            }

        // input check
        } elseif (!is_int($unicode)) {
            throw new Exception('Input must be of type string or int.');
        }

        $blocks =& $this->_read_unicode_block_db();

        $result = $this->_unicode_block_name($unicode, $blocks);

        if ($result == -1) {
            return false;
        } else {
            return $result[2];
        }
    }

    /**
     * Searches the unicode block database
     *
     * Returns the block name for a given unicode value. unicodeBlockName() is
     * the public interface for this function, which does input checks which
     * this function omits for speed.
     *
     * @access  protected
     * @param   int     $unicode the unicode value
     * @param   array   &$blocks the block database
     * @param   int     $block_count the number of defined blocks in the database
     * @see     unicodeBlockName()
     */
    function _unicode_block_name($unicode, &$blocks, $block_count = -1) {
        // for a reference, see 
        // http://www.unicode.org/Public/UNIDATA/Blocks.txt

        // assume that ascii characters are the most common
        // so try it first for efficiency
        if ($unicode <= $blocks[0][1]) {
            return $blocks[0];
        }

        // the optional $block_count param is for efficiency
        // so we this function doesn't have to run count() every time
        if ($block_count != -1) {
            $high = $block_count - 1;
        } else {
            $high = count($blocks) - 1;
        }

        $low = 1; // start with 1 because ascii was 0

        // your average binary search algorithm
        while ($low <= $high) {
            $mid = floor(($low + $high) / 2);

            // if it's lower than the lower bound
            if ($unicode < $blocks[$mid][0]) {
                $high = $mid - 1;

            // if it's higher than the upper bound
            } elseif ($unicode > $blocks[$mid][1]) {
                $low = $mid + 1;

            // found it
            } else {
                return $blocks[$mid];
            }
        }

        // failed to find the block 
        return -1;

        // todo: differentiate when it's out of range or when it falls 
        //       into an unassigned range?
    }

    /**
     * Brings up the unicode block database
     *
     * @access protected
     * @return array the database of unicode block definitions
     * @throws PEAR_Error
     */
    function &_read_unicode_block_db() {
        // since the unicode definitions are always going to be the same,
        // might as well share the memory for the db with all other instances
        // of this class
        static $data;

        if (!isset($data)) {
            $data = $this->_readdb($this->_unicode_db_filename);
        }

        return $data;
    }

    /**
     * Calculate the similarities between the language models
     * 
     * Use this function to see how similar languages are to each other.
     *
     * If passed 2 language names, will return just those languages compared.
     * If passed 1 language name, will return that language compared to
     * all others.
     * If passed none, will return an array of every language model compared 
     * to every other one.
     *
     * @access  public
     * @param   string   $lang1   the name of the first language to be compared
     * @param   string   $lang2   the name of the second language to be compared
     * @return  array    scores of every language compared
     *                   or the score of just the provided languages
     *                   or null if one of the supplied languages does not exist
     * @throws  PEAR_Error
     */
    function languageSimilarity($lang1 = null, $lang2 = null)
    {
        if (!$this->_setup_ok($err)) {
            return $err;
        }

        if ($lang1 != null) {
            $lang1 = strtolower($lang1);

            // check if language model exists
            if (!isset($this->_lang_db[$lang1])) {
                return null;
            }

            if ($lang2 != null) {

                // can't only set the second param
                if ($lang1 == null) {
                    return null;
                // check if language model exists
                } elseif (!isset($this->_lang_db[$lang2])) {
                    return null;
                }

                $lang2 = strtolower($lang2);

                // compare just these two languages
                return $this->_normalize_score(
                    $this->_distance(
                        $this->_lang_db[$lang1],
                        $this->_lang_db[$lang2]
                    )
                );


            // compare just $lang1 to all languages
            } else {
                $return_arr = array();
                foreach ($this->_lang_db as $key => $value) {
                    if ($key != $lang1) { // don't compare a language to itself
                        $return_arr[$key] = $this->_normalize_score(
                            $this->_distance($this->_lang_db[$lang1], $value));
                    }
                }
                asort($return_arr);

                return $return_arr;
            }


        // compare all languages to each other
        } else {
            $return_arr = array();
            foreach (array_keys($this->_lang_db) as $lang1) {
                foreach (array_keys($this->_lang_db) as $lang2) {

                    // skip comparing languages to themselves
                    if ($lang1 != $lang2) { 
                    
                        // don't re-calculate what's already been done
                        if (isset($return_arr[$lang2][$lang1])) {

                            $return_arr[$lang1][$lang2] =
                                $return_arr[$lang2][$lang1];

                        // calculate
                        } else {

                            $return_arr[$lang1][$lang2] = 
                                $this->_normalize_score(
                                        $this->_distance(
                                            $this->_lang_db[$lang1],
                                            $this->_lang_db[$lang2]
                                        )
                                );

                        }
                    }
                }
            }
            return $return_arr;
        }
    }

    /**
     * Cluster known languages according to languageSimilarity()
     *
     * WARNING: this method is EXPERIMENTAL. It is not recommended for common
     * use, and it may disappear or its functionality may change in future
     * releases without notice.
     *
     * Uses a nearest neighbor technique to generate the maximum possible
     * number of dendograms from the similarity data.
     *
     * @access      public
     * @return      array language cluster data
     * @throws      PEAR_Error
     * @see         languageSimilarity()
     * @deprecated  this function will eventually be removed and placed into 
     *              the model generation class
     */
    function clusterLanguages()
    {
        // todo: set the maximum number of clusters

        // setup check
        if (!$this->_setup_ok($err)) {
            return $err;
        }

        // return cached result, if any
        if (isset($this->_clusters)) {
            return $this->_clusters;
        }

        $langs = array_keys($this->_lang_db);

        $arr = $this->languageSimilarity();

        sort($langs);

        foreach ($langs as $lang) {
            if (!isset($this->_lang_db[$lang])) {
                throw new Exception("missing $lang!\n");
            }
        }

        // http://www.psychstat.missouristate.edu/multibook/mlt04m.html
        foreach ($langs as $old_key => $lang1) {
            $langs[$lang1] = $lang1;
            unset($langs[$old_key]);
        }
        
        $i = 0;
        while (count($langs) > 2 && $i++ < 200) {
            $highest_score = -1;
            $highest_key1 = '';
            $highest_key2 = '';
            foreach ($langs as $lang1) {
                foreach ($langs as $lang2) {
                    if (    $lang1 != $lang2 
                            && $arr[$lang1][$lang2] > $highest_score) {
                        $highest_score = $arr[$lang1][$lang2];
                        $highest_key1 = $lang1;
                        $highest_key2 = $lang2;
                    }
                }
            }
            
            if (!$highest_key1) {
                // should not ever happen
                throw new Exception("no highest key? (step: $i)");
            }

            if ($highest_score == 0) {
                // languages are perfectly dissimilar
                break;
            }

            // $highest_key1 and $highest_key2 are most similar
            $sum1 = array_sum($arr[$highest_key1]);
            $sum2 = array_sum($arr[$highest_key2]);

            // use the score for the one that is most similar to the rest of 
            // the field as the score for the group
            // todo: could try averaging or "centroid" method instead
            // seems like that might make more sense
            // actually nearest neighbor may be better for binary searching


            // for "Complete Linkage"/"furthest neighbor"
            // sign should be <
            // for "Single Linkage"/"nearest neighbor" method
            // should should be >
            // results seem to be pretty much the same with either method

            // figure out which to delete and which to replace
            if ($sum1 > $sum2) {
                $replaceme = $highest_key1;
                $deleteme = $highest_key2;
            } else {
                $replaceme = $highest_key2;
                $deleteme = $highest_key1;
            }

            $newkey = $replaceme . ':' . $deleteme;

            // $replaceme is most similar to remaining languages
            // replace $replaceme with '$newkey', deleting $deleteme

            // keep a record of which fork is really which language
            $really_lang = $replaceme;
            while (isset($really_map[$really_lang])) {
                $really_lang = $really_map[$really_lang];
            } 
            $really_map[$newkey] = $really_lang;


            // replace the best fitting key, delete the other
            foreach ($arr as $key1 => $arr2) {
                foreach ($arr2 as $key2 => $value2) {
                    if ($key2 == $replaceme) {
                        $arr[$key1][$newkey] = $arr[$key1][$key2];
                        unset($arr[$key1][$key2]);
                        // replacing $arr[$key1][$key2] with $arr[$key1][$newkey]
                    } 
                    
                    if ($key1 == $replaceme) {
                        $arr[$newkey][$key2] = $arr[$key1][$key2];
                        unset($arr[$key1][$key2]);
                        // replacing $arr[$key1][$key2] with $arr[$newkey][$key2]
                    }

                    if ($key1 == $deleteme || $key2 == $deleteme) {
                        // deleting $arr[$key1][$key2]
                        unset($arr[$key1][$key2]);
                    }
                }
            }
                        

            unset($langs[$highest_key1]);
            unset($langs[$highest_key2]);
            $langs[$newkey] = $newkey;


            // some of these may be overkill
            $result_data[$newkey] = array(
                                'newkey' => $newkey,
                                'count' => $i,
                                'diff' => abs($sum1 - $sum2),
                                'score' => $highest_score,
                                'bestfit' => $replaceme,
                                'otherfit' => $deleteme,
                                'really' => $really_lang,
                            );
        }

        $return_val = array(
                'open_forks' => $langs, 
                        // the top level of clusters
                        // clusters that are mutually exclusive
                        // or specified by a specific maximum

                'fork_data' => $result_data,
                        // data for each split

                'name_map' => $really_map,
                        // which cluster is really which language
                        // using the nearest neighbor technique, the cluster
                        // inherits all of the properties of its most-similar member
                        // this keeps track
            );


        // saves the result in the object
        $this->_clusters = $return_val;

        return $return_val;
    }


    /**
     * Perform an intelligent detection based on clusterLanguages()
     *
     * WARNING: this method is EXPERIMENTAL. It is not recommended for common
     * use, and it may disappear or its functionality may change in future
     * releases without notice.
     *
     * This compares the sample text to top the top level of clusters. If the 
     * sample is similar to the cluster it will drop down and compare it to the
     * languages in the cluster, and so on until it hits a leaf node.
     *
     * this should find the language in considerably fewer compares 
     * (the equivalent of a binary search), however clusterLanguages() is costly
     * and the loss of accuracy from this technique is significant.
     *
     * This method may need to be 'fuzzier' in order to become more accurate.
     *
     * This function could be more useful if the universe of possible languages
     * was very large, however in such cases some method of Bayesian inference
     * might be more helpful.
     *
     * @see     clusterLanguages()
     * @access  public
     * @param   string $str input string
     * @return  array language scores (only those compared)
     * @throws  PEAR_Error
     */
    function clusteredSearch($str)
    {

        // input check
        if (!Text_LanguageDetect_Parser::validateString($str)) {
            return array();
        }

        // clusterLanguages() will return a cached result if possible
        // so it's safe to call it every time
        $result = $this->clusterLanguages();

        $dendogram_start = $result['open_forks'];
        $dendogram_data  = $result['fork_data'];
        $dendogram_alias = $result['name_map'];

        $sample_obj = new Text_LanguageDetect_Parser($str, $this->_db_filename, $this->_unicode_db_filename);
        $sample_obj->prepareTrigram();
        $sample_obj->setPadStart(!$this->_perl_compatible);
        $sample_obj->analyze();
        $sample_result = $sample_obj->getTrigramRanks();
        $sample_count  = count($sample_result);

        // input check
        if ($sample_count == 0) {
            return array();
        }

        $i = 0; // counts the number of steps
        
        foreach ($dendogram_start as $lang) {
            if (isset($dendogram_alias[$lang])) {
                $lang_key = $dendogram_alias[$lang];
            } else {
                $lang_key = $lang;
            }

            $scores[$lang] = $this->_normalize_score(
                $this->_distance($this->_lang_db[$lang_key], $sample_result),
                $sample_count);

            $i++;
        }

        if ($this->_perl_compatible) {
            asort($scores);
        } else {
            arsort($scores);
        }

        $top_score = current($scores);
        $top_key = key($scores);

        // of starting forks, $top_key is the most similar to the sample

        $cur_key = $top_key;
        while (isset($dendogram_data[$cur_key])) {
            $lang1 = $dendogram_data[$cur_key]['bestfit'];
            $lang2 = $dendogram_data[$cur_key]['otherfit'];
            foreach (array($lang1, $lang2) as $lang) {
                if (isset($dendogram_alias[$lang])) {
                    $lang_key = $dendogram_alias[$lang];
                } else {
                    $lang_key = $lang;
                }

                $scores[$lang] = $this->_normalize_score(
                    $this->_distance($this->_lang_db[$lang_key], $sample_result),
                    $sample_count);

                //todo: does not need to do same comparison again
            }

            $i++;

            if ($scores[$lang1] > $scores[$lang2]) {
                $cur_key = $lang1;
                $loser_key = $lang2;
            } else {
                $cur_key = $lang2;
                $loser_key = $lang1;
            }

            $diff = $scores[$cur_key] - $scores[$loser_key];

            // $cur_key ({$dendogram_alias[$cur_key]}) wins 
            // over $loser_key ({$dendogram_alias[$loser_key]}) 
            // with a difference of $diff
        }

        // found result in $i compares

        // rather than sorting the result, preserve it so that you can see
        // which paths the algorithm decided to take along the tree

        // but sometimes the last item is only the second highest
        if (   ($this->_perl_compatible  && (end($scores) > prev($scores)))
            || (!$this->_perl_compatible && (end($scores) < prev($scores)))) {

            $real_last_score = current($scores);
            $real_last_key = key($scores);

            // swaps the 2nd-to-last item for the last item
            unset($scores[$real_last_key]);
            $scores[$real_last_key] = $real_last_score;
        }
            

        if (!$this->_perl_compatible) {
            $scores = array_reverse($scores, true);
            // second param requires php > 4.0.3
        }

        return $scores;
    }

    /**
     * ut8-safe strlen()
     *
     * Returns the numbers of characters (not bytes) in a utf8 string
     *
     * @static
     * @access  public
     * @param   string $str string to get the length of
     * @return  int         number of chars
     */
    function utf8strlen($str)
    {
        // utf8_decode() will convert unknown chars to '?', which is actually
        // ideal for counting.

        return strlen(utf8_decode($str));

        // idea stolen from dokuwiki
    }

    /**
     * Returns the unicode value of a utf8 char
     *
     * @access  protected
     * @param   string $char a utf8 (possibly multi-byte) char
     * @return  int          unicode value or -1 if malformatted
     */
    function _utf8char2unicode($char) {

        // strlen() here will actually get the binary length of a single char
        switch (strlen($char)) {

            // for a reference, see http://en.wikipedia.org/wiki/UTF-8

            case 1:
                // normal ASCII-7 byte
                // 0xxxxxxx -->  0xxxxxxx
                return ord($char{0});

            case 2:
                // 2 byte unicode
                // 110zzzzx 10xxxxxx --> 00000zzz zxxxxxxx
                $z = (ord($char{0}) & 0x000001F) << 6;
                $x = (ord($char{1}) & 0x0000003F);

                return ($z | $x);

            case 3:
                // 3 byte unicode
                // 1110zzzz 10zxxxxx 10xxxxxx --> zzzzzxxx xxxxxxxx 
                $z =  (ord($char{0}) & 0x0000000F) << 12;
                $x1 = (ord($char{1}) & 0x0000003F) << 6;
                $x2 = (ord($char{2}) & 0x0000003F);

                return ($z | $x1 | $x2);

            case 4:
                // 4 byte unicode
                // 11110zzz 10zzxxxx 10xxxxxx 10xxxxxx -->
                // 000zzzzz xxxxxxxx xxxxxxxx
                $z1 = (ord($char{0}) & 0x00000007) << 18;
                $z2 = (ord($char{1}) & 0x0000003F) << 12;
                $x1 = (ord($char{2}) & 0x0000003F) << 6;
                $x2 = (ord($char{3}) & 0x0000003F);

                return ($z1 | $z2 | $x1 | $x2);

            default:
                // error: malformatted char?
                return -1;
        }
    }

    /**
     * utf8-safe fast character iterator
     *
     * Will get the next character starting from $counter, which will then be
     * incremented. If a multi-byte char the bytes will be concatenated and 
     * $counter will be incremeted by the number of bytes in the char.
     *
     * @access  private
     * @param   string  &$str        the string being iterated over
     * @param   int     &$counter    the iterator, will increment by reference
     * @param   bool    $special_convert whether to do special conversions
     * @return  char    the next (possibly multi-byte) char from $counter
     */
    function _next_char(&$str, &$counter, $special_convert = false)
    {

        $char = $str{$counter++};
        $ord = ord($char);

        // for a description of the utf8 system see
        // http://www.phpclasses.org/browse/file/5131.html

        // normal ascii one byte char
        if ($ord <= 127) {

            // special conversions needed for this package
            // (that only apply to regular ascii characters)
            // lower case, and convert all non-alphanumeric characters
            // other than "'" to space
            if ($special_convert && $char != ' ' && $char != "'") {
                if ($ord >= 65 && $ord <= 90) { // A-Z
                    $char = chr($ord + 32); // lower case
                } elseif ($ord < 97 || $ord > 122) { // NOT a-z
                    $char = ' '; // convert to space
                }
            }

            return $char;

        // multi-byte chars
        } elseif ($ord >> 5 == 6) { // two-byte char
            $nextchar = $str{$counter++}; // get next byte

            // lower-casing of non-ascii characters is still incomplete

            if ($special_convert) {
                // lower case latin accented characters
                if ($ord == 195) {
                    $nextord = ord($nextchar);
                    $nextord_adj = $nextord + 64;
                    // for a reference, see 
                    // http://www.ramsch.org/martin/uni/fmi-hp/iso8859-1.html

                    // &Agrave; - &THORN; but not &times;
                    if (    $nextord_adj >= 192
                            && $nextord_adj <= 222 
                            && $nextord_adj != 215) {

                        $nextchar = chr($nextord + 32); 
                    }

                // lower case cyrillic alphabet
                } elseif ($ord == 208) {
                    $nextord = ord($nextchar);
                    // if A - Pe
                    if ($nextord >= 144 && $nextord <= 159) {
                        // lower case
                        $nextchar = chr($nextord + 32);

                    // if Er - Ya
                    } elseif ($nextord >= 160 && $nextord <= 175) {
                        // lower case
                        $char = chr(209); // == $ord++
                        $nextchar = chr($nextord - 32);
                    }
                }
            }

            // tag on next byte
            return $char . $nextchar; 

        } elseif ($ord >> 4  == 14) { // three-byte char
            
            // tag on next 2 bytes
            return $char . $str{$counter++} . $str{$counter++}; 

        } elseif ($ord >> 3 == 30) { // four-byte char

            // tag on next 3 bytes
            return $char . $str{$counter++} . $str{$counter++} . $str{$counter++};

        } else {
            // error?
        }
    }

}

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

?>
