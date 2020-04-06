<?php

namespace Wallabag\CoreBundle\Helper;

use Html2Text\Html2Text;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerBuilder;
use PHPePub\Core\EPub;
use PHPePub\Core\Structure\OPF\DublinCore;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;
use Wallabag\CoreBundle\Entity\Entry;

/**
 * This class doesn't have unit test BUT it's fully covered by a functional test with ExportControllerTest.
 */
class EntriesExport
{
    private $wallabagUrl;
    private $logoPath;
    private $translator;
    private $title = '';
    private $entries = [];
    private $author = 'wallabag';
    private $language = '';

    /**
     * @param TranslatorInterface $translator  Translator service
     * @param string              $wallabagUrl Wallabag instance url
     * @param string              $logoPath    Path to the logo FROM THE BUNDLE SCOPE
     */
    public function __construct(TranslatorInterface $translator, $wallabagUrl, $logoPath)
    {
        $this->translator = $translator;
        $this->wallabagUrl = $wallabagUrl;
        $this->logoPath = $logoPath;
    }

    /**
     * Define entries.
     *
     * @param array|Entry $entries An array of entries or one entry
     *
     * @return EntriesExport
     */
    public function setEntries($entries)
    {
        if (!\is_array($entries)) {
            $this->language = $entries->getLanguage();
            $entries = [$entries];
        }

        $this->entries = $entries;

        return $this;
    }

    /**
     * Sets the category of which we want to get articles, or just one entry.
     *
     * @param string $method Method to get articles
     *
     * @return EntriesExport
     */
    public function updateTitle($method)
    {
        $this->title = $method . ' articles';

        if ('entry' === $method) {
            $this->title = $this->entries[0]->getTitle();
        }

        return $this;
    }

    /**
     * Sets the author for one entry or category.
     *
     * The publishers are used, or the domain name if empty.
     *
     * @param string $method Method to get articles
     *
     * @return EntriesExport
     */
    public function updateAuthor($method)
    {
        if ('entry' !== $method) {
            $this->author = 'Various authors';

            return $this;
        }

        $this->author = $this->entries[0]->getDomainName();

        $publishedBy = $this->entries[0]->getPublishedBy();
        if (!empty($publishedBy)) {
            $this->author = implode(', ', $publishedBy);
        }

        return $this;
    }

    /**
     * Sets the output format.
     *
     * @param string $format
     *
     * @return Response
     */
    public function exportAs($format)
    {
        $functionName = 'produce' . ucfirst($format);
        if (method_exists($this, $functionName)) {
            return $this->$functionName();
        }

        throw new \InvalidArgumentException(sprintf('The format "%s" is not yet supported.', $format));
    }

    public function exportJsonData()
    {
        return $this->prepareSerializingContent('json');
    }

    /**
     * Use PHPePub to dump a .epub file.
     *
     * @return Response
     */
    private function produceEpub()
    {
        /*
         * Start and End of the book
         */
        $content_start =
            "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"
            . "<html xmlns=\"http://www.w3.org/1999/xhtml\" xmlns:epub=\"http://www.idpf.org/2007/ops\">\n"
            . '<head>'
            . "<meta http-equiv=\"Default-Style\" content=\"text/html; charset=utf-8\" />\n"
            . "<title>wallabag articles book</title>\n"
            . "</head>\n"
            . "<body>\n";

        $bookEnd = "</body>\n</html>\n";

        $book = new EPub(EPub::BOOK_VERSION_EPUB3);

        /*
         * Book metadata
         */

        $book->setTitle($this->title);
        // Not needed, but included for the example, Language is mandatory, but EPub defaults to "en". Use RFC3066 Language codes, such as "en", "da", "fr" etc.
        $book->setLanguage($this->language);
        $book->setDescription('Some articles saved on my wallabag');

        $book->setAuthor($this->author, $this->author);

        // I hope this is a non existant address :)
        $book->setPublisher('wallabag', 'wallabag');
        // Strictly not needed as the book date defaults to time().
        $book->setDate(time());
        $book->setSourceURL($this->wallabagUrl);

        $book->addDublinCoreMetadata(DublinCore::CONTRIBUTOR, 'PHP');
        $book->addDublinCoreMetadata(DublinCore::CONTRIBUTOR, 'wallabag');

        $entryIds = [];
        $entryCount = \count($this->entries);
        $i = 0;

        /*
         * Adding actual entries
         */

        // set tags as subjects
        foreach ($this->entries as $entry) {
            ++$i;

            /*
             * Front page
             * Set if there's only one entry in the given set
             */
            if (1 === $entryCount && null !== $entry->getPreviewPicture()) {
                $book->setCoverImage($entry->getPreviewPicture());
            }

            foreach ($entry->getTags() as $tag) {
                $book->setSubject($tag->getLabel());
            }
            $filename = sha1(sprintf('%s:%s', $entry->getUrl(), $entry->getTitle()));

            $publishedBy = $entry->getPublishedBy();
            $authors = $this->translator->trans('export.unknown');
            if (!empty($publishedBy)) {
                $authors = implode(',', $publishedBy);
            }

            $publishedAt = $entry->getPublishedAt();
            $publishedDate = $this->translator->trans('export.unknown');
            if (!empty($publishedAt)) {
                $publishedDate = $entry->getPublishedAt()->format('Y-m-d');
            }

            $titlepage = $content_start .
                '<h1>' . $entry->getTitle() . '</h1>' .
                '<dl>' .
                '<dt>' . $this->translator->trans('entry.view.published_by') . '</dt><dd>' . $authors . '</dd>' .
                '<dt>' . $this->translator->trans('entry.metadata.published_on') . '</dt><dd>' . $publishedDate . '</dd>' .
                '<dt>' . $this->translator->trans('entry.metadata.reading_time') . '</dt><dd>' . $this->translator->trans('entry.metadata.reading_time_minutes_short', ['%readingTime%' => $entry->getReadingTime()]) . '</dd>' .
                '<dt>' . $this->translator->trans('entry.metadata.added_on') . '</dt><dd>' . $entry->getCreatedAt()->format('Y-m-d') . '</dd>' .
                '<dt>' . $this->translator->trans('entry.metadata.address') . '</dt><dd><a href="' . $entry->getUrl() . '">' . $entry->getUrl() . '</a></dd>' .
                '</dl>' .
                $bookEnd;
            $book->addChapter("Entry {$i} of {$entryCount}", "{$filename}_cover.html", $titlepage, true, EPub::EXTERNAL_REF_ADD);
            $chapter = $content_start . $entry->getContent() . $bookEnd;

            $entryIds[] = $entry->getId();
            $book->addChapter($entry->getTitle(), "{$filename}.html", $chapter, true, EPub::EXTERNAL_REF_ADD);
        }

        $book->addChapter('Notices', 'Cover2.html', $content_start . $this->getExportInformation('PHPePub') . $bookEnd);

        // Could also be the ISBN number, prefered for published books, or a UUID.
        $hash = sha1(sprintf('%s:%s', $this->wallabagUrl, implode(',', $entryIds)));
        $book->setIdentifier(sprintf('urn:wallabag:%s', $hash), EPub::IDENTIFIER_URI);

        return Response::create(
            $book->getBook(),
            200,
            [
                'Content-Description' => 'File Transfer',
                'Content-type' => 'application/epub+zip',
                'Content-Disposition' => 'attachment; filename="' . $this->getSanitizedFilename() . '.epub"',
                'Content-Transfer-Encoding' => 'binary',
            ]
        );
    }

    /**
     * Use PHPMobi to dump a .mobi file.
     *
     * @return Response
     */
    private function produceMobi()
    {
        $mobi = new \MOBI();
        $content = new \MOBIFile();

        /*
         * Book metadata
         */
        $content->set('title', $this->title);
        $content->set('author', $this->author);
        $content->set('subject', $this->title);

        /*
         * Front page
         */
        $content->appendParagraph($this->getExportInformation('PHPMobi'));
        if (file_exists($this->logoPath)) {
            $content->appendImage(imagecreatefrompng($this->logoPath));
        }
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

        return Response::create(
            $mobi->toString(),
            200,
            [
                'Accept-Ranges' => 'bytes',
                'Content-Description' => 'File Transfer',
                'Content-type' => 'application/x-mobipocket-ebook',
                'Content-Disposition' => 'attachment; filename="' . $this->getSanitizedFilename() . '.mobi"',
                'Content-Transfer-Encoding' => 'binary',
            ]
        );
    }

    /**
     * Use TCPDF to dump a .pdf file.
     *
     * @return Response
     */
    private function producePdf()
    {
        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        /*
         * Book metadata
         */
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor($this->author);
        $pdf->SetTitle($this->title);
        $pdf->SetSubject('Articles via wallabag');
        $pdf->SetKeywords('wallabag');

        /*
         * Adding actual entries
         */
        foreach ($this->entries as $entry) {
            foreach ($entry->getTags() as $tag) {
                $pdf->SetKeywords($tag->getLabel());
            }

            $publishedBy = $entry->getPublishedBy();
            $authors = $this->translator->trans('export.unknown');
            if (!empty($publishedBy)) {
                $authors = implode(',', $publishedBy);
            }

            $pdf->addPage();
            $html = '<h1>' . $entry->getTitle() . '</h1>' .
                '<dl>' .
                '<dt>' . $this->translator->trans('entry.view.published_by') . '</dt><dd>' . $authors . '</dd>' .
                '<dt>' . $this->translator->trans('entry.metadata.reading_time') . '</dt><dd>' . $this->translator->trans('entry.metadata.reading_time_minutes_short', ['%readingTime%' => $entry->getReadingTime()]) . '</dd>' .
                '<dt>' . $this->translator->trans('entry.metadata.added_on') . '</dt><dd>' . $entry->getCreatedAt()->format('Y-m-d') . '</dd>' .
                '<dt>' . $this->translator->trans('entry.metadata.address') . '</dt><dd><a href="' . $entry->getUrl() . '">' . $entry->getUrl() . '</a></dd>' .
                '</dl>';
            $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

            $pdf->AddPage();
            $html = '<h1>' . $entry->getTitle() . '</h1>';
            $html .= $entry->getContent();

            $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
        }

        /*
         * Last page
         */
        $pdf->AddPage();
        $html = $this->getExportInformation('tcpdf');

        $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        return Response::create(
            $pdf->Output('', 'S'),
            200,
            [
                'Content-Description' => 'File Transfer',
                'Content-type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $this->getSanitizedFilename() . '.pdf"',
                'Content-Transfer-Encoding' => 'binary',
            ]
        );
    }

    /**
     * Inspired from CsvFileDumper.
     *
     * @return Response
     */
    private function produceCsv()
    {
        $delimiter = ';';
        $enclosure = '"';
        $handle = fopen('php://memory', 'b+r');

        fputcsv($handle, ['Title', 'URL', 'Content', 'Tags', 'MIME Type', 'Language', 'Creation date'], $delimiter, $enclosure);

        foreach ($this->entries as $entry) {
            fputcsv(
                $handle,
                [
                    $entry->getTitle(),
                    $entry->getURL(),
                    // remove new line to avoid crazy results
                    str_replace(["\r\n", "\r", "\n"], '', $entry->getContent()),
                    implode(', ', $entry->getTags()->toArray()),
                    $entry->getMimetype(),
                    $entry->getLanguage(),
                    $entry->getCreatedAt()->format('d/m/Y h:i:s'),
                ],
                $delimiter,
                $enclosure
            );
        }

        rewind($handle);
        $output = stream_get_contents($handle);
        fclose($handle);

        return Response::create(
            $output,
            200,
            [
                'Content-type' => 'application/csv',
                'Content-Disposition' => 'attachment; filename="' . $this->getSanitizedFilename() . '.csv"',
                'Content-Transfer-Encoding' => 'UTF-8',
            ]
        );
    }

    /**
     * Dump a JSON file.
     *
     * @return Response
     */
    private function produceJson()
    {
        return Response::create(
            $this->prepareSerializingContent('json'),
            200,
            [
                'Content-type' => 'application/json',
                'Content-Disposition' => 'attachment; filename="' . $this->getSanitizedFilename() . '.json"',
                'Content-Transfer-Encoding' => 'UTF-8',
            ]
        );
    }

    /**
     * Dump a XML file.
     *
     * @return Response
     */
    private function produceXml()
    {
        return Response::create(
            $this->prepareSerializingContent('xml'),
            200,
            [
                'Content-type' => 'application/xml',
                'Content-Disposition' => 'attachment; filename="' . $this->getSanitizedFilename() . '.xml"',
                'Content-Transfer-Encoding' => 'UTF-8',
            ]
        );
    }

    /**
     * Dump a TXT file.
     *
     * @return Response
     */
    private function produceTxt()
    {
        $content = '';
        $bar = str_repeat('=', 100);
        foreach ($this->entries as $entry) {
            $content .= "\n\n" . $bar . "\n\n" . $entry->getTitle() . "\n\n" . $bar . "\n\n";
            $html = new Html2Text($entry->getContent(), ['do_links' => 'none', 'width' => 100]);
            $content .= $html->getText();
        }

        return Response::create(
            $content,
            200,
            [
                'Content-type' => 'text/plain',
                'Content-Disposition' => 'attachment; filename="' . $this->getSanitizedFilename() . '.txt"',
                'Content-Transfer-Encoding' => 'UTF-8',
            ]
        );
    }

    /**
     * Return a Serializer object for producing processes that need it (JSON & XML).
     *
     * @param string $format
     *
     * @return string
     */
    private function prepareSerializingContent($format)
    {
        $serializer = SerializerBuilder::create()->build();

        return $serializer->serialize(
            $this->entries,
            $format,
            SerializationContext::create()->setGroups(['entries_for_user'])
        );
    }

    /**
     * Return a kind of footer / information for the epub.
     *
     * @param string $type Generator of the export, can be: tdpdf, PHPePub, PHPMobi
     *
     * @return string
     */
    private function getExportInformation($type)
    {
        $info = $this->translator->trans('export.footer_template', [
            '%method%' => $type,
        ]);

        if ('tcpdf' === $type) {
            return str_replace('%IMAGE%', '<img src="' . $this->logoPath . '" />', $info);
        }

        return str_replace('%IMAGE%', '', $info);
    }

    /**
     * Return a sanitized version of the title by applying translit iconv
     * and removing non alphanumeric characters, - and space.
     *
     * @return string Sanitized filename
     */
    private function getSanitizedFilename()
    {
        return preg_replace('/[^A-Za-z0-9\- \']/', '', iconv('utf-8', 'us-ascii//TRANSLIT', $this->title));
    }
}
