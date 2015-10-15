<?php

namespace Wallabag\CoreBundle\Helper;

use PHPePub\Core\EPub;
use PHPePub\Core\Structure\OPF\DublinCore;

class EntriesExport
{
    private $format;
    private $method;
    private $title;
    private $entries;
    private $authors = array('wallabag');
    private $language;
    private $tags;

    public function __construct($entries)
    {
        $this->entries = $entries;

        foreach ($entries as $entry) {
            $this->tags[] = $entry->getTags();
        }
        if (count($entries) === 1) {
            $this->language = $entries[0]->getLanguage();
        }
    }

    /**
     * Sets the category of which we want to get articles, or just one entry.
     *
     * @param string $method Method to get articles
     */
    public function setMethod($method)
    {
        $this->method = $method;

        switch ($this->method) {
            case 'All':
                $this->title = 'All Articles';
                break;
            case 'Unread':
                $this->title = 'Unread articles';
                break;
            case 'Starred':
                $this->title = 'Starred articles';
                break;
            case 'Archive':
                $this->title = 'Archived articles';
                break;
            case 'entry':
                $this->title = $this->entries[0]->getTitle();
                break;
            default:
                break;
        }
    }

    /**
     * Sets the output format.
     *
     * @param string $format
     */
    public function exportAs($format)
    {
        $this->format = $format;

        switch ($this->format) {
            case 'epub':
                $this->produceEpub();
                break;

            case 'mobi':
                $this->produceMobi();
                break;

            case 'pdf':
                $this->producePDF();
                break;

            case 'csv':
                $this->produceCSV();
                break;

            default:
                break;
        }
    }

    private function produceEpub()
    {
        /*
         * Start and End of the book
         */
        $content_start =
            "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"
            ."<html xmlns=\"http://www.w3.org/1999/xhtml\" xmlns:epub=\"http://www.idpf.org/2007/ops\">\n"
            .'<head>'
            ."<meta http-equiv=\"Default-Style\" content=\"text/html; charset=utf-8\" />\n"
            .'<title>'._('wallabag articles book')."</title>\n"
            ."</head>\n"
            ."<body>\n";

        $bookEnd = "</body>\n</html>\n";

        $book = new EPub(EPub::BOOK_VERSION_EPUB3);

        /*
         * Book metadata
         */

        $book->setTitle($this->title);
        $book->setIdentifier($this->title, EPub::IDENTIFIER_URI); // Could also be the ISBN number, prefered for published books, or a UUID.
        $book->setLanguage($this->language); // Not needed, but included for the example, Language is mandatory, but EPub defaults to "en". Use RFC3066 Language codes, such as "en", "da", "fr" etc.
        $book->setDescription(_('Some articles saved on my wallabag'));

        foreach ($this->authors as $author) {
            $book->setAuthor($author, $author);
        }

        $book->setPublisher('wallabag', 'wallabag'); // I hope this is a non existant address :)
        $book->setDate(time()); // Strictly not needed as the book date defaults to time().
        $book->setSourceURL("http://$_SERVER[HTTP_HOST]");

        $book->addDublinCoreMetadata(DublinCore::CONTRIBUTOR, 'PHP');
        $book->addDublinCoreMetadata(DublinCore::CONTRIBUTOR, 'wallabag');

        /*
         * Front page
         */

        $book->setCoverImage('Cover.png', file_get_contents('themes/_global/img/appicon/apple-touch-icon-152.png'), 'image/png');

        $cover = $content_start.'<div style="text-align:center;"><p>'._('Produced by wallabag with PHPePub').'</p><p>'._('Please open <a href="https://github.com/wallabag/wallabag/issues" >an issue</a> if you have trouble with the display of this E-Book on your device.').'</p></div>'.$bookEnd;

        $book->addChapter('Notices', 'Cover2.html', $cover);

        $book->buildTOC();

        /*
         * Adding actual entries
         */

        foreach ($this->entries as $entry) { //set tags as subjects
                foreach ($this->tags as $tag) {
                    $book->setSubject($tag['value']);
                }

            $chapter = $content_start.$entry->getContent().$bookEnd;
            $book->addChapter($entry->getTitle(), htmlspecialchars($entry->getTitle()).'.html', $chapter, true, EPub::EXTERNAL_REF_ADD);
        }
        $book->finalize();
        $book->sendBook($this->title);
    }

    private function produceMobi()
    {
        $mobi = new \MOBI();
        $content = new \MOBIFile();

        /*
         * Book metadata
         */

        $content->set('title', $this->title);
        $content->set('author', implode($this->authors));
        $content->set('subject', $this->title);

        /*
         * Front page
         */

        $content->appendParagraph('<div style="text-align:center;" ><p>'._('Produced by wallabag with PHPMobi').'</p><p>'._('Please open <a href="https://github.com/wallabag/wallabag/issues" >an issue</a> if you have trouble with the display of this E-Book on your device.').'</p></div>');
        $content->appendImage(imagecreatefrompng('themes/_global/img/appicon/apple-touch-icon-152.png'));
        $content->appendPageBreak();

        /*
         * Adding actual entries
         */

        foreach ($this->entries as $entry) {
            $content->appendChapterTitle($entry->getTitle());
            $content->appendParagraph($entry->getContent());
            $content->appendPageBreak();
        }
        $mobi->setContentProvider($content);

        // the browser inside Kindle Devices doesn't likes special caracters either, we limit to A-z/0-9
        $this->title = preg_replace('/[^A-Za-z0-9\-]/', '', $this->title);

        // we offer file to download
        $mobi->download($this->title.'.mobi');
    }

    private function producePDF()
    {
        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        /*
         * Book metadata
         */

        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('wallabag');
        $pdf->SetTitle($this->title);
        $pdf->SetSubject('Articles via wallabag');
        $pdf->SetKeywords('wallabag');

        /*
         * Front page
         */

        $pdf->AddPage();
        $intro = '<h1>'.$this->title.'</h1><div style="text-align:center;" >
        <p>'._('Produced by wallabag with tcpdf').'</p>
        <p>'._('Please open <a href="https://github.com/wallabag/wallabag/issues" >an issue</a> if you have trouble with the display of this E-Book on your device.').'</p>
        <img src="themes/_global/img/appicon/apple-touch-icon-152.png" /></div>';

        $pdf->writeHTMLCell(0, 0, '', '', $intro, 0, 1, 0, true, '', true);

        /*
         * Adding actual entries
         */

        foreach ($this->entries as $entry) {
            foreach ($this->tags as $tag) {
                $pdf->SetKeywords($tag['value']);
            }

            $pdf->AddPage();
            $html = '<h1>'.$entry->getTitle().'</h1>';
            $html .= $entry->getContent();
            $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
        }

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        $pdf->Output($this->title.'.pdf', 'D');
    }

    private function produceCSV()
    {
        header('Content-type: application/csv');
        header('Content-Disposition: attachment; filename="'.$this->title.'.csv"');
        header('Content-Transfer-Encoding: UTF-8');

        $output = fopen('php://output', 'a');

        fputcsv($output, array('Title', 'URL', 'Content', 'Tags', 'MIME Type', 'Language'));
        foreach ($this->entries as $entry) {
            fputcsv($output, array($entry->getTitle(),
                                   $entry->getURL(),
                                   $entry->getContent(),
                                   implode(', ', $entry->getTags()->toArray()),
                                   $entry->getMimetype(),
                                   $entry->getLanguage(), ));
        }
        fclose($output);
        exit();
    }
}
