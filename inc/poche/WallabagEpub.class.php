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
                $entries = array($entry);
                $bookTitle = $entry['title'];
                $bookFileName = substr($bookTitle, 0, 200);
                break;
            case 'all':
                $entries = $this->wallabag->store->retrieveAll($this->wallabag->user->getId());
                $bookTitle = sprintf(_('All my articles on '), date(_('d.m.y'))); #translatable because each country has it's own date format system
                $bookFileName = _('Allarticles') . date(_('dmY'));
                break;
            case 'tag':
                $tag = filter_var($this->value, FILTER_SANITIZE_STRING);
                $tags_id = $this->wallabag->store->retrieveAllTags($this->wallabag->user->getId(), $tag);
                $tag_id = $tags_id[0]["id"]; // we take the first result, which is supposed to match perfectly. There must be a workaround.
                $entries = $this->wallabag->store->retrieveEntriesByTag($tag_id, $this->wallabag->user->getId());
                $bookTitle = sprintf(_('Articles tagged %s'), $tag);
                $bookFileName = substr(sprintf(_('Tag %s'), $tag), 0, 200);
                break;
            case 'category':
                $category = filter_var($this->value, FILTER_SANITIZE_STRING);
                $entries = $this->wallabag->store->getEntriesByView($category, $this->wallabag->user->getId());
                $bookTitle = sprintf(_('All articles in category %s'), $category);
                $bookFileName = substr(sprintf(_('Category %s'), $category), 0, 200);
                break;
            case 'search':
                $search = filter_var($this->value, FILTER_SANITIZE_STRING);
                $entries = $this->store->search($search, $this->wallabag->user->getId());
                $bookTitle = sprintf(_('All articles for search %s'), $search);
                $bookFileName = substr(sprintf(_('Search %s'), $search), 0, 200);
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

        $book->setTitle($bookTitle);
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

        $fullTitle = "<h1> " . $bookTitle . "</h1>\n";

        $book->setCoverImage("Cover.png", file_get_contents("themes/baggy/img/apple-touch-icon-152.png"), "image/png", $fullTitle);

        $cover = $content_start . '<div style="text-align:center;"><p>' . _('Produced by wallabag with PHPePub') . '</p><p>'. _('Please open <a href="https://github.com/wallabag/wallabag/issues" >an issue</a> if you have trouble with the display of this E-Book on your device.') . '</p></div>' . $bookEnd;

        //$book->addChapter("Table of Contents", "TOC.xhtml", NULL, false, EPub::EXTERNAL_REF_IGNORE);
        $book->addChapter("Notices", "Cover2.html", $cover);

        $book->buildTOC();

        foreach ($entries as $entry) { //set tags as subjects
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
        $zipData = $book->sendBook($bookFileName);
    }
} 

class WallabagMobi extends WallabagEBooks
{
	/**
	* Adapted from News2Kindle
	* @author Jakub Westfalewski <jwest@jwest.pl>
	*
	*/

	public function produceMobi()
	{
		$storage = new Storage('static');
		$this->prepareData();
		foreach ($entries as $i => $item) {
			$content = $item['content'];
			$images = new Images($storage, $content);
            $content = $images->convert();
			$storage->add_content
            (
                md5($item['title']),
                mb_convert_encoding($item['title'], 'HTML-ENTITIES', 'utf-8'), 
                $content, 
                $item['url']], 
                ""
            );
		}
		$articles = $storage->get_contents();
		$toc = array();
        $articles_count = count($articles);

        foreach($articles as $article){
            if(array_key_exists($article->website->title, $toc)){
                $toc[$article->website->title]->articles[] = $article;             
            }else{
                $toc[$article->website->title] = (object)array(
                    'articles' => array($article),
                    'title' => $article->website->title,
                    'streamId' => $article->website->streamId,
                    'url' => $article->website->htmlUrl,
                );
            }           
        }

        $mobi = new MOBI(); 
        $mobi->setData($content);
        $mobi->setOptions(array( 
            'title' => 'Articles from '.date('Y-m-d'), 
            'author' => 'wallabag', 
            'subject' => 'Articles from '.date('Y-m-d'),
        ));

        $images = array();

        //prepare images for mobi format
        foreach ( $storage->info('images') as $n => $image )
        {
            $images[$n] = new FileRecord(new Record(file_get_contents($storage->get_path() . $image)));
        }

        $mobi->setImages($images);
        $mobi->save( $storage->get_path(FALSE) . 'articles-' . date('Y-m-d') . '.mobi');

        $storage->clean();

        if ($send) {
        	$files = glob($storage->get_path(FALSE).'*.mobi');
        	$mail = new Send(KINDLEMAIL,MAIL);
        	foreach ( $files as $file_mobi )
        	{
            	$mail->send( $file_mobi );
        	}
        	// clean cache 
        	foreach ( $files as $file_mobi )
        	{
           		unlink( $file_mobi );
        	}
        }
	}
}

class WallabagPDF extends WallabagEbooks
{
	public function producePDF()
	{
		$mpdf = new mPDF('c'); 

		$mpdf->WriteHTML($html);
		$mpdf->Output();
		exit;
	}
}