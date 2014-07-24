<?php
/**
 * wallabag, self hostable application allowing you to not miss any content anymore
 *
 * @category   wallabag
 * @author     Nicolas Lœuillet <nicolas@loeuillet.org>
 * @copyright  2013
 * @license    http://opensource.org/licenses/MIT see COPYING file
 */

class WallabagEBooks
{
	protected $wallabag;
    protected $method;
    protected $value;
    protected $entries;
    protected $bookTitle;
    protected $bookFileName;

    public function __construct(Poche $wallabag, $method, $value)
    {
        $this->wallabag = $wallabag;
        $this->method   = $method;
        $this->value    = $value;
    }

    public function prepareData()
    {
        switch ($this->method) {
            case 'id':
                $entryID = filter_var($this->value, FILTER_SANITIZE_NUMBER_INT);
                $entry = $this->wallabag->store->retrieveOneById($entryID, $this->wallabag->user->getId());
                $this->entries = array($entry);
                $this->bookTitle = $entry['title'];
                $this->bookFileName = substr($this->bookTitle, 0, 200);
                break;
            case 'all':
                $this->entries = $this->wallabag->store->retrieveAll($this->wallabag->user->getId());
                $this->bookTitle = sprintf(_('All my articles on %s'), date(_('d.m.y'))); #translatable because each country has it's own date format system
                $this->bookFileName = _('Allarticles') . date(_('dmY'));
                break;
            case 'tag':
                $tag = filter_var($this->value, FILTER_SANITIZE_STRING);
                $tags_id = $this->wallabag->store->retrieveAllTags($this->wallabag->user->getId(), $tag);
                $tag_id = $tags_id[0]["id"]; // we take the first result, which is supposed to match perfectly. There must be a workaround.
                $this->entries = $this->wallabag->store->retrieveEntriesByTag($tag_id, $this->wallabag->user->getId());
                $this->bookTitle = sprintf(_('Articles tagged %s'), $tag);
                $this->bookFileName = substr(sprintf(_('Tag %s'), $tag), 0, 200);
                break;
            case 'category':
                $category = filter_var($this->value, FILTER_SANITIZE_STRING);
                $this->entries = $this->wallabag->store->getEntriesByView($category, $this->wallabag->user->getId());
                $this->bookTitle = sprintf(_('All articles in category %s'), $category);
                $this->bookFileName = substr(sprintf(_('Category %s'), $category), 0, 200);
                break;
            case 'search':
                $search = filter_var($this->value, FILTER_SANITIZE_STRING);
                Tools::logm($search);
                $this->entries = $this->wallabag->store->search($search, $this->wallabag->user->getId());
                $this->bookTitle = sprintf(_('All articles for search %s'), $search);
                $this->bookFileName = substr(sprintf(_('Search %s'), $search), 0, 200);
                break;
            case 'default':
                die(_('Uh, there is a problem while generating epub.'));
        }
    }
}

class WallabagEpub extends WallabagEBooks
{
    /**
     * handle ePub
     */
    public function produceEpub()
    {
        $content_start =
            "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"
            . "<html xmlns=\"http://www.w3.org/1999/xhtml\" xmlns:epub=\"http://www.idpf.org/2007/ops\">\n"
            . "<head>"
            . "<meta http-equiv=\"Default-Style\" content=\"text/html; charset=utf-8\" />\n"
            . "<title>wallabag articles book</title>\n"
            . "</head>\n"
            . "<body>\n";

        $bookEnd = "</body>\n</html>\n";

        $log = new Logger("wallabag", TRUE);
        $fileDir = CACHE;

        $book = new EPub(EPub::BOOK_VERSION_EPUB3, DEBUG_POCHE);
        $log->logLine("new EPub()");
        $log->logLine("EPub class version: " . EPub::VERSION);
        $log->logLine("EPub Req. Zip version: " . EPub::REQ_ZIP_VERSION);
        $log->logLine("Zip version: " . Zip::VERSION);
        $log->logLine("getCurrentServerURL: " . $book->getCurrentServerURL());
        $log->logLine("getCurrentPageURL..: " . $book->getCurrentPageURL());

        $book->setTitle($this->bookTitle);
        $book->setIdentifier("http://$_SERVER[HTTP_HOST]", EPub::IDENTIFIER_URI); // Could also be the ISBN number, prefered for published books, or a UUID.
        //$book->setLanguage("en"); // Not needed, but included for the example, Language is mandatory, but EPub defaults to "en". Use RFC3066 Language codes, such as "en", "da", "fr" etc.
        $book->setDescription(_("Some articles saved on my wallabag"));
        $book->setAuthor("wallabag", "wallabag");
        $book->setPublisher("wallabag", "wallabag"); // I hope this is a non existant address :)
        $book->setDate(time()); // Strictly not needed as the book date defaults to time().
        //$book->setRights("Copyright and licence information specific for the book."); // As this is generated, this _could_ contain the name or licence information of the user who purchased the book, if needed. If this is used that way, the identifier must also be made unique for the book.
        $book->setSourceURL("http://$_SERVER[HTTP_HOST]");

        $book->addDublinCoreMetadata(DublinCore::CONTRIBUTOR, "PHP");
        $book->addDublinCoreMetadata(DublinCore::CONTRIBUTOR, "wallabag");

        $cssData = "body {\n margin-left: .5em;\n margin-right: .5em;\n text-align: justify;\n}\n\np {\n font-family: serif;\n font-size: 10pt;\n text-align: justify;\n text-indent: 1em;\n margin-top: 0px;\n margin-bottom: 1ex;\n}\n\nh1, h2 {\n font-family: sans-serif;\n font-style: italic;\n text-align: center;\n background-color: #6b879c;\n color: white;\n width: 100%;\n}\n\nh1 {\n margin-bottom: 2px;\n}\n\nh2 {\n margin-top: -2px;\n margin-bottom: 2px;\n}\n";

        $log->logLine("Add Cover");

        $fullTitle = "<h1> " . $this->bookTitle . "</h1>\n";

        $book->setCoverImage("Cover.png", file_get_contents("themes/baggy/img/apple-touch-icon-152.png"), "image/png", $fullTitle);

        $cover = $content_start . '<div style="text-align:center;"><p>' . _('Produced by wallabag with PHPePub') . '</p><p>'. _('Please open <a href="https://github.com/wallabag/wallabag/issues" >an issue</a> if you have trouble with the display of this E-Book on your device.') . '</p></div>' . $bookEnd;

        //$book->addChapter("Table of Contents", "TOC.xhtml", NULL, false, EPub::EXTERNAL_REF_IGNORE);
        $book->addChapter("Notices", "Cover2.html", $cover);

        $book->buildTOC();

        foreach ($this->entries as $entry) { //set tags as subjects
            $tags = $this->wallabag->store->retrieveTagsByEntry($entry['id']);
            foreach ($tags as $tag) {
                $book->setSubject($tag['value']);
            }

            $log->logLine("Set up parameters");

            $chapter = $content_start . $entry['content'] . $bookEnd;
            $book->addChapter($entry['title'], htmlspecialchars($entry['title']) . ".html", $chapter, true, EPub::EXTERNAL_REF_ADD);
            $log->logLine("Added chapter " . $entry['title']);
        }

        if (DEBUG_POCHE) {
            $book->addChapter("Log", "Log.html", $content_start . $log->getLog() . "\n</pre>" . $bookEnd); // log generation
        }
        $book->finalize();
        $zipData = $book->sendBook($this->bookFileName);
    }
} 

class WallabagMobi extends WallabagEBooks
{
	/**
	* MOBI Class
	* @author Sander Kromwijk
	*/

    private $_kindle_email;

	public function produceMobi($sendByMail = FALSE)
	{

        $mobi = new MOBI();
        $content = new MOBIFile();

        $messages = new Messages(); // for later
            
        $content->set("title", $this->bookTitle);
        $content->set("author", "wallabag");
        $content->set("subject", $this->bookTitle);

        # introduction
        $content->appendParagraph('<div style="text-align:center;" ><p>' . _('Produced by wallabag with PHPMobi') . '</p><p>'. _('Please open <a href="https://github.com/wallabag/wallabag/issues" >an issue</a> if you have trouble with the display of this E-Book on your device.') . '</p></div>');
        $content->appendImage(imagecreatefrompng("themes/baggy/img/apple-touch-icon-152.png"));
        $content->appendPageBreak();

        foreach ($this->entries as $item) {
            $content->appendChapterTitle($item['title']);
            $content->appendParagraph($item['content']);
            $content->appendPageBreak();
        }
        $mobi->setContentProvider($content);

        if (!$sendByMail) {
            // we offer file to download
            $mobi->download($this->bookFileName.'.mobi');
        }
        else {
            // we send file to kindle

            $char_in = array('/', '.', ',', ':', '|'); # we sanitize filename to avoid conflicts with special characters (for instance, / goes for a directory)
            $mobiExportName = preg_replace('/\s+/', '-', str_replace($char_in, '-', $this->bookFileName . '.mobi'));
            
            $file = 'cache/' . $mobiExportName;
            $mobi->save($file);

            $file_size = filesize($file);
            $filename = basename($file);
            $handle = fopen($file, "r");
            $content = fread($handle, $file_size);
            fclose($handle);
            $content = chunk_split(base64_encode($content));

            $uid = md5(uniqid(time())); 

            //generate header for mail
            $header  = "From: wallabag <". $this->wallabag->user->email .">\r\n";        
            $header .= "MIME-Version: 1.0\r\n";
            $header .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n";
            $header .= "This is a multi-part message in MIME format.\r\n";
            $header .= "--".$uid."\r\n";
            $header .= "Content-type:text/plain; charset=iso-8859-1\r\n";
            $header .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
            $header .= "send via wallabag\r\n\r\n";
            $header .= "--".$uid."\r\n";
            $header .= "Content-Type: application/x-mobipocket-ebook; name=\"".$filename."\"\r\n";
            $header .= "Content-Transfer-Encoding: base64\r\n";
            $header .= "Content-Disposition: attachment; filename=\"".$filename."\"\r\n\r\n";
            $header .= $content."\r\n\r\n";
            $header .= "--".$uid."--";

            # trying to get the kindle email adress
            if ($this->wallabag->user->getConfigValue('kindleemail')) 
            {
                #do a try...exeption here
                mail( $this->wallabag->user->getConfigValue('kindleemail'), '[wallabag] ' . $this->bookTitle, "", $header );
                $messages->add('s', _('The email has been sent to your kindle !'));
            }
            else
            {
                $messages->add('e', _('You didn\'t set your kindle\'s email adress !'));
            }
        }
    }
}

class WallabagPDF extends WallabagEbooks
{
	public function producePDF()
	{
		$mpdf = new mPDF('c'); 

        # intro

        $html = '<h1>' . $this->bookTitle . '<bookmark content="Cover" /></h1><div style="text-align:center;" >
        <p>' . _('Produced by wallabag with mPDF') . '</p>
        <p>'. _('Please open <a href="https://github.com/wallabag/wallabag/issues" >an issue</a> if you have trouble with the display of this E-Book on your device.') . '</p>
        <img src="themes/baggy/img/apple-touch-icon-152.png" /></div>';
        $html .= '<pagebreak type="next-odd" />';
        $i = 1;

        foreach ($this->entries as $item) {
            $html .= '<h1>' . $item['title'] . '<bookmark content="' . $item['title'] . '" /></h1>';
            $html .= '<indexentry content="'. $item['title'] .'" />';
            $html .= $item['content'];
            $html .= '<pagebreak type="next-odd" />';
            $i = $i+1;
        }


        # headers
        $mpdf->SetHeader('{DATE j-m-Y}|{PAGENO}/{nb}|Produced with wallabag');
        $mpdf->SetFooter('{PAGENO}');
		
        $mpdf->WriteHTML($html);

        # remove characters that make mpdf bug
        $char_in = array('/', '.', ',', ':', '|');
        $pdfExportName = preg_replace('/\s+/', '-', str_replace($char_in, '-', $this->bookFileName . '.pdf'));

        # index
        $html = '<h2>Index<bookmark content="Index" /></h2>
        <indexinsert cols="2" offset="5" usedivletters="on" div-font-size="15" gap="5" font="Trebuchet" div-font="sans-serif" links="on" />
        ';

        $mpdf->WriteHTML($html);
		
        $mpdf->Output('cache/' . $pdfExportName);
		
        header('Content-Disposition: attachment; filename="' . $pdfExportName . '"');

        header('Content-Transfer-Encoding: base64');
        header('Content-Type: application/pdf');
        echo file_get_contents('cache/' . $pdfExportName);

        //exit;
	}
}