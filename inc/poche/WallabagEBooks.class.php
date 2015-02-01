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
    protected $author = 'wallabag';

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
                $this->bookFileName = str_replace('/', '_', substr($this->bookTitle, 0, 200));
                $this->author = preg_replace('#^w{3}.#', '', Tools::getdomain($entry["url"])); # if only one article, set author to domain name (we strip the eventual www part)
                Tools::logm('Producing ebook from article ' . $this->bookTitle);
                break;
            case 'all':
                $this->entries = $this->wallabag->store->retrieveAll($this->wallabag->user->getId());
                $this->bookTitle = sprintf(_('All my articles on %s'), date(_('d.m.y'))); #translatable because each country has it's own date format system
                $this->bookFileName = _('Allarticles') . date(_('dmY'));
                Tools::logm('Producing ebook from all articles');
                break;
            case 'tag':
                $tag = filter_var($this->value, FILTER_SANITIZE_STRING);
                $tags_id = $this->wallabag->store->retrieveAllTags($this->wallabag->user->getId(), $tag);
                $tag_id = $tags_id[0]["id"]; // we take the first result, which is supposed to match perfectly. There must be a workaround.
                $this->entries = $this->wallabag->store->retrieveEntriesByTag($tag_id, $this->wallabag->user->getId());
                $this->bookTitle = sprintf(_('Articles tagged %s'), $tag);
                $this->bookFileName = substr(sprintf(_('Tag %s'), $tag), 0, 200);
                Tools::logm('Producing ebook from tag ' . $tag);
                break;
            case 'category':
                $category = filter_var($this->value, FILTER_SANITIZE_STRING);
                $this->entries = $this->wallabag->store->getEntriesByView($category, $this->wallabag->user->getId());
                $this->bookTitle = sprintf(_('Articles in category %s'), $category);
                $this->bookFileName = substr(sprintf(_('Category %s'), $category), 0, 200);
                Tools::logm('Producing ebook from category ' . $category);
                break;
            case 'search':
                $search = filter_var($this->value, FILTER_SANITIZE_STRING);
                Tools::logm($search);
                $this->entries = $this->wallabag->store->search($search, $this->wallabag->user->getId());
                $this->bookTitle = sprintf(_('Articles for search %s'), $search);
                $this->bookFileName = substr(sprintf(_('Search %s'), $search), 0, 200);
                Tools::logm('Producing ebook from search ' . $search);
                break;
            case 'default':
                die(_('Uh, there is a problem while generating eBook.'));
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
        Tools::logm('Starting to produce ePub 3 file');

        try {

        $content_start =
            "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"
            . "<html xmlns=\"http://www.w3.org/1999/xhtml\" xmlns:epub=\"http://www.idpf.org/2007/ops\">\n"
            . "<head>"
            . "<meta http-equiv=\"Default-Style\" content=\"text/html; charset=utf-8\" />\n"
            . "<title>" . _("wallabag articles book") . "</title>\n"
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

        Tools::logm('Filling metadata for ePub...');

        $book->setTitle($this->bookTitle);
        $book->setIdentifier("http://$_SERVER[HTTP_HOST]", EPub::IDENTIFIER_URI); // Could also be the ISBN number, prefered for published books, or a UUID.
        //$book->setLanguage("en"); // Not needed, but included for the example, Language is mandatory, but EPub defaults to "en". Use RFC3066 Language codes, such as "en", "da", "fr" etc.
        $book->setDescription(_("Some articles saved on my wallabag"));
        $book->setAuthor($this->author,$this->author);
        $book->setPublisher("wallabag", "wallabag"); // I hope this is a non existant address :)
        $book->setDate(time()); // Strictly not needed as the book date defaults to time().
        //$book->setRights("Copyright and licence information specific for the book."); // As this is generated, this _could_ contain the name or licence information of the user who purchased the book, if needed. If this is used that way, the identifier must also be made unique for the book.
        $book->setSourceURL("http://$_SERVER[HTTP_HOST]");

        $book->addDublinCoreMetadata(DublinCore::CONTRIBUTOR, "PHP");
        $book->addDublinCoreMetadata(DublinCore::CONTRIBUTOR, "wallabag");

        $cssData = "body {\n margin-left: .5em;\n margin-right: .5em;\n text-align: justify;\n}\n\np {\n font-family: serif;\n font-size: 10pt;\n text-align: justify;\n text-indent: 1em;\n margin-top: 0px;\n margin-bottom: 1ex;\n}\n\nh1, h2 {\n font-family: sans-serif;\n font-style: italic;\n text-align: center;\n background-color: #6b879c;\n color: white;\n width: 100%;\n}\n\nh1 {\n margin-bottom: 2px;\n}\n\nh2 {\n margin-top: -2px;\n margin-bottom: 2px;\n}\n";

        $log->logLine("Add Cover");

        $fullTitle = "<h1> " . $this->bookTitle . "</h1>\n";

        $book->setCoverImage("Cover.png", file_get_contents("themes/_global/img/appicon/apple-touch-icon-152.png"), "image/png", $fullTitle);

        $cover = $content_start . '<div style="text-align:center;"><p>' . _('Produced by wallabag with PHPePub') . '</p><p>'. _('Please open <a href="https://github.com/wallabag/wallabag/issues" >an issue</a> if you have trouble with the display of this E-Book on your device.') . '</p></div>' . $bookEnd;

        //$book->addChapter("Table of Contents", "TOC.xhtml", NULL, false, EPub::EXTERNAL_REF_IGNORE);
        $book->addChapter("Notices", "Cover2.html", $cover);

        $book->buildTOC();

        Tools::logm('Adding actual content...');

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
            Tools::logm('Production log available in produced file');
        }
        $book->finalize();
        $zipData = $book->sendBook($this->bookFileName);
        Tools::logm('Ebook produced');
    	}
        catch (Exception $e) {
            Tools::logm('PHPePub has encountered an error : '.$e->getMessage());
            $this->wallabag->messages->add('e', $e->getMessage());
        }
    }
} 

class WallabagMobi extends WallabagEBooks
{
	/**
	* MOBI Class
	* @author Sander Kromwijk
	*/

	public function produceMobi()
	{
		try {
        Tools::logm('Starting to produce Mobi file');
        $mobi = new MOBI();
        $content = new MOBIFile();

        $messages = new Messages(); // for later
        
        Tools::logm('Filling metadata for Mobi...');

        $content->set("title", $this->bookTitle);
        $content->set("author", $this->author);
        $content->set("subject", $this->bookTitle);

        # introduction
        $content->appendParagraph('<div style="text-align:center;" ><p>' . _('Produced by wallabag with PHPMobi') . '</p><p>'. _('Please open <a href="https://github.com/wallabag/wallabag/issues" >an issue</a> if you have trouble with the display of this E-Book on your device.') . '</p></div>');
        $content->appendImage(imagecreatefrompng("themes/_global/img/appicon/apple-touch-icon-152.png"));
        $content->appendPageBreak();

        Tools::logm('Adding actual content...');

        foreach ($this->entries as $item) {
            $content->appendChapterTitle($item['title']);
            $content->appendParagraph($item['content']);
            $content->appendPageBreak();
        }
        $mobi->setContentProvider($content);

        // the browser inside Kindle Devices doesn't likes special caracters either, we limit to A-z/0-9
        $this->bookFileName = preg_replace('/[^A-Za-z0-9\-]/', '', $this->bookFileName);

        // we offer file to download
        $mobi->download($this->bookFileName.'.mobi');
        Tools::logm('Mobi file produced');
    	}
        catch (Exception $e) {
            Tools::logm('PHPMobi has encountered an error : '.$e->getMessage());
            $this->wallabag->messages->add('e', $e->getMessage());
        }
    }
}

class WallabagPDF extends WallabagEbooks
{
	public function producePDF()
	{

        Tools::logm('Starting to produce PDF file');
        @define ('K_TCPDF_THROW_EXCEPTION_ERROR', TRUE);
        try {
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        Tools::logm('Filling metadata for PDF...');
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('wallabag');
        $pdf->SetTitle($this->bookTitle);
        $pdf->SetSubject('Articles via wallabag');
        $pdf->SetKeywords('wallabag');
		
        Tools::logm('Adding introduction...');
        $pdf->AddPage();
        $intro = '<h1>' . $this->bookTitle . '</h1><div style="text-align:center;" >
        <p>' . _('Produced by wallabag with tcpdf') . '</p>
        <p>'. _('Please open <a href="https://github.com/wallabag/wallabag/issues" >an issue</a> if you have trouble with the display of this E-Book on your device.') . '</p>
        <img src="themes/_global/img/appicon/apple-touch-icon-152.png" /></div>';


        $pdf->writeHTMLCell(0, 0, '', '', $intro, 0, 1, 0, true, '', true);

        $i = 1;
        Tools::logm('Adding actual content...');
        foreach ($this->entries as $item) {
        	$tags = $this->wallabag->store->retrieveTagsByEntry($entry['id']);
        	foreach ($tags as $tag) {
                $pdf->SetKeywords($tag['value']);
            }
            $pdf->AddPage();
            $html = '<h1>' . $item['title'] . '</h1>';
            $html .= $item['content'];
            $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
        }

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        
        
        $pdf->Output($this->bookFileName . '.pdf', 'FD');
        }
        catch (Exception $e) {
            Tools::logm('TCPDF has encountered an error : '.$e->getMessage());
            $this->wallabag->messages->add('e', $e->getMessage());
            }

	}
}
