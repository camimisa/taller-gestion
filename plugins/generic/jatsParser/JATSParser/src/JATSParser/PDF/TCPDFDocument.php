<?php

namespace JATSParser\PDF;



use JATSParser\Body\Document as JATSDocument;
use JATSParser\HTML\Document as HTMLDocument;

require_once(__DIR__ . '/../../../vendor/tecnickcom/tcpdf/tcpdf.php');


class TCPDFDocument extends \TCPDF
{

	protected $doiDocument = '';

	function __construct()
	{

		// setting up PDF
		parent::__construct(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	}

	function setFooterDataFromOrigin(string $doi)
	{
		$this->doiDocument = $doi;
	}

	public function Header()
	{
		if ($this->header_xobjid === false) {
			// start a new XObject Template
			$this->header_xobjid = $this->startTemplate($this->w, $this->tMargin);
			//$this->SetTextColor(255,0,0);
			$headerfont = $this->getHeaderFont();
			$headerdata = $this->getHeaderData();
			$this->y = $this->header_margin;
			if ($this->rtl) {
				$this->x = $this->w - $this->original_rMargin;
			} else {
				$this->x = $this->original_lMargin;
			}
			if (($headerdata['logo']) and ($headerdata['logo'] != K_BLANK_IMAGE)) {
				$imgtype = \TCPDF_IMAGES::getImageFileType($headerdata['logo']);
				$headerdata['logo_width'] = 12;
				if (($imgtype == 'eps') or ($imgtype == 'ai')) {
					$this->ImageEps($headerdata['logo'], '', '', $headerdata['logo_width']);
				} elseif ($imgtype == 'svg') {
					$this->ImageSVG($headerdata['logo'], '', '', $headerdata['logo_width']);
				} else {
					$this->Image($headerdata['logo'], '', 5, $headerdata['logo_width'], 12);
				}
				$imgy = $this->getImageRBY();
			} else {
				$imgy = $this->y;
			}
			$cell_height = $this->getCellHeight($headerfont[2] / $this->k);
			// set starting margin for text data cell
			if ($this->getRTL()) {
				$header_x = $this->original_rMargin + ($headerdata['logo_width'] * 1.2);
			} else {
				$header_x = $this->original_lMargin + ($headerdata['logo_width'] * 1.2);
			}
			$cw = $this->w - $this->original_lMargin - $this->original_rMargin - ($headerdata['logo_width'] * 1.2);
			$this->SetTextColorArray($this->header_text_color);
			// header title
			$this->SetFont('helvetica', 'BI', 20);
			$x = $this->GetX();
			$y = $this->GetY();
			$this->SetXY($x, $y);
			$this->SetTextColor(255, 0, 0);
			$this->Cell(70, -5, $headerdata['title'], '', 0,  'l', 0);
			// header string
			$this->SetFont('dejavuserif', '', 9);
			$this->SetX($header_x);
			$this->MultiCell($cw, $cell_height, $headerdata['string'], 0, '', 0, 1, '', '', true, 0, false, true, 0, 'T', false);
			// print an ending header line
			$this->SetLineStyle(array('width' => 0.85 / $this->k, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => $headerdata['line_color']));
			$this->SetY((2.835 / $this->k) + max($imgy, $this->y));
			if ($this->rtl) {
				$this->SetX($this->original_rMargin);
			} else {
				$this->SetX($this->original_lMargin);
			}
			$this->Cell(($this->w - $this->original_lMargin - $this->original_rMargin), 0, '', 'T', 0, 'C');
			$this->endTemplate();
		}
		// print header template
		$x = 0;
		$dx = 0;
		if (!$this->header_xobj_autoreset and $this->booklet and (($this->page % 2) == 0)) {
			// adjust margins for booklet mode
			$dx = ($this->original_lMargin - $this->original_rMargin);
		}
		if ($this->rtl) {
			$x = $this->w + $dx;
		} else {
			$x = 0 + $dx;
		}
		$this->printTemplate($this->header_xobjid, $x, 0, 0, 0, '', '', false);
		if ($this->header_xobj_autoreset) {
			// reset header xobject template at each page
			$this->header_xobjid = false;
		}
	}

	// Page footer
	public function Footer()
	{
		$data = $this->getHeaderData();
		$title = $data['title'];

		$doi = $this->doiDocument;
		// Position at 15 mm from bottom

		$this->SetY(-15);
		$this->Cell(($this->w - $this->original_lMargin - $this->original_rMargin), 0, '', 'T', 2, 'C');
		// Set font
		$this->SetFont('helvetica', 'I', 8);
		// Title
		$this->Cell(0, 10, $title . ' | ' . $doi, 0, false, 'L', 0, '', 0, false, 'T', 'M');

		// Page number
		$this->Cell(0, 10, $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
	}
}