<?php

namespace Export2pdf;

use \PicoFarad\Session;
use \Model;


function prepare_document()
{
    $date = date('Ymd');
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('inthepoche.com');
    $pdf->SetTitle('poche export ' . $date);
    $pdf->SetSubject('poche export ' . $date);

    $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, 'poche export', 'inthepoche.com', array(0,64,255), array(0,64,128));
    $pdf->setFooterData(array(0,64,0), array(0,64,128));
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    $pdf->setFontSubsetting(true);
    $pdf->SetFont('dejavusans', '', 14, '', true);

    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    return array(
        'document' => $pdf,
        'filename' => 'poche-' . $date . '.pdf'
        );
}

function export_items_to_pdf($items)
{
    $document = prepare_document();
    $pdf  = $document['document'];

    foreach ($items as $item) {
        $pdf->AddPage();
        $pdf->Cell(0, 12, $item['title'], 1, 1, 'C');
        $pdf->writeHTMLCell(0, 0, '', '', $item['content'], 0, 1, 0, true, '', true);
    }

    $pdf->Output($document['filename'], 'D');
}
