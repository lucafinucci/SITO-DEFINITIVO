<?php
/**
 * FPDF 1.86 - http://www.fpdf.org
 * Minimal PDF library
 */

class FPDF
{
    public $page;               // current page number
    public $n;                  // current object number
    public $offsets;            // array of object offsets
    public $buffer;             // buffer holding in-memory PDF
    public $pages;              // array containing pages
    public $state;              // current document state
    public $compress;           // compression flag
    public $k;                  // scale factor (number of points in user unit)
    public $fwPt, $fhPt;        // dimensions of page format in points
    public $fw, $fh;            // dimensions of page format in user unit
    public $wPt, $hPt;          // current dimensions of page in points
    public $w, $h;              // current dimensions of page in user unit
    public $lMargin;            // left margin
    public $tMargin;            // top margin
    public $rMargin;            // right margin
    public $bMargin;            // page break margin
    public $cMargin;            // cell margin
    public $x, $y;              // current position
    public $lasth;              // height of last printed cell
    public $lineWidth;          // line width in user unit
    public $fontpath;           // path containing fonts
    public $CoreFonts;          // array of core font names
    public $fonts;              // array of used fonts
    public $FontFiles;          // array of font files
    public $diffs;              // array of encoding differences
    public $images;             // array of images
    public $FontFamily;         // current font family
    public $FontStyle;          // current font style
    public $underline;          // underlining flag
    public $CurrentFont;        // current font info
    public $FontSizePt;         // current font size in points
    public $FontSize;           // current font size in user unit
    public $DrawColor;          // commands for drawing color
    public $FillColor;          // commands for filling color
    public $TextColor;          // commands for text color
    public $ColorFlag;          // indicates whether fill and text colors are different
    public $ws;                 // word spacing
    public $AutoPageBreak;      // automatic page breaking
    public $PageBreakTrigger;   // threshold used to trigger page breaks
    public $InHeader;           // flag set when processing header
    public $InFooter;           // flag set when processing footer
    public $AliasNbPages;       // alias for total number of pages
    public $PDFVersion;         // PDF version number

    function __construct($orientation='P', $unit='mm', $size='A4')
    {
        $this->state = 0;
        $this->pages = array();
        $this->page = 0;
        $this->n = 2;
        $this->buffer = '';
        $this->offsets = array();
        $this->fonts = array();
        $this->FontFiles = array();
        $this->diffs = array();
        $this->images = array();
        $this->fontpath = '';

        $this->CoreFonts = array(
            'courier'=>'Courier','courierB'=>'Courier-Bold','courierI'=>'Courier-Oblique','courierBI'=>'Courier-BoldOblique',
            'helvetica'=>'Helvetica','helveticaB'=>'Helvetica-Bold','helveticaI'=>'Helvetica-Oblique','helveticaBI'=>'Helvetica-BoldOblique',
            'times'=>'Times-Roman','timesB'=>'Times-Bold','timesI'=>'Times-Italic','timesBI'=>'Times-BoldItalic',
            'symbol'=>'Symbol','zapfdingbats'=>'ZapfDingbats'
        );

        $this->k = ($unit=='pt') ? 1 : (($unit=='mm') ? 72/25.4 : (($unit=='cm') ? 72/2.54 : 72));

        $this->_setPageSize($size, $orientation);

        $this->lMargin = 10;
        $this->tMargin = 10;
        $this->rMargin = 10;
        $this->bMargin = 10;
        $this->cMargin = 1;
        $this->lineWidth = .2;
        $this->FontSizePt = 12;
        $this->FontSize = $this->FontSizePt / $this->k;
        $this->DrawColor = '0 G';
        $this->FillColor = '0 g';
        $this->TextColor = '0 g';
        $this->ColorFlag = false;
        $this->ws = 0;
        $this->AutoPageBreak = true;
        $this->PageBreakTrigger = $this->h - $this->bMargin;
        $this->InHeader = false;
        $this->InFooter = false;
        $this->AliasNbPages = '{nb}';
        $this->PDFVersion = '1.3';
    }

    function SetMargins($left, $top, $right=null)
    {
        $this->lMargin = $left;
        $this->tMargin = $top;
        if($right===null)
            $right = $left;
        $this->rMargin = $right;
    }

    function AddPage($orientation='', $size='')
    {
        if($this->state==0)
            $this->Open();
        $family = $this->FontFamily;
        $style = $this->FontStyle;
        $size = $this->FontSizePt;
        $lw = $this->lineWidth;
        $dc = $this->DrawColor;
        $fc = $this->FillColor;
        $tc = $this->TextColor;
        $cf = $this->ColorFlag;

        if($this->page>0)
            $this->_endpage();
        $this->_beginpage($orientation, $size);
        $this->_out(sprintf('%.2F w', $lw*$this->k));
        if($family)
            $this->SetFont($family, $style, $size);
        $this->DrawColor = $dc;
        $this->FillColor = $fc;
        $this->TextColor = $tc;
        $this->ColorFlag = $cf;
    }

    function SetFont($family, $style='', $size=0)
    {
        $family = strtolower($family);
        if($family=='arial')
            $family = 'helvetica';
        $style = strtoupper($style);
        if(strpos($style,'U')!==false)
        {
            $this->underline = true;
            $style = str_replace('U','',$style);
        }
        else
            $this->underline = false;
        if($size==0)
            $size = $this->FontSizePt;

        $fontkey = $family.$style;
        if(!isset($this->fonts[$fontkey]))
        {
            if(!isset($this->CoreFonts[$fontkey]))
                $this->Error('Undefined font: '.$family.' '.$style);
            $cw = array_fill(0, 256, 600);
            $this->fonts[$fontkey] = array('i'=>count($this->fonts)+1, 'type'=>'core', 'name'=>$this->CoreFonts[$fontkey], 'up'=>-100, 'ut'=>50, 'cw'=>$cw);
        }
        $this->FontFamily = $family;
        $this->FontStyle = $style;
        $this->FontSizePt = $size;
        $this->FontSize = $size/$this->k;
        $this->CurrentFont = $this->fonts[$fontkey];
        if($this->page>0)
            $this->_out(sprintf('BT /F%d %.2F Tf ET', $this->CurrentFont['i'], $this->FontSizePt));
    }

    function SetTextColor($r, $g=null, $b=null)
    {
        if(($r==0 && $g==0 && $b==0) || $g===null)
            $this->TextColor = sprintf('%.3F g', $r/255);
        else
            $this->TextColor = sprintf('%.3F %.3F %.3F rg', $r/255, $g/255, $b/255);
        $this->ColorFlag = ($this->FillColor != $this->TextColor);
    }

    function SetFillColor($r, $g=null, $b=null)
    {
        if(($r==0 && $g==0 && $b==0) || $g===null)
            $this->FillColor = sprintf('%.3F g', $r/255);
        else
            $this->FillColor = sprintf('%.3F %.3F %.3F rg', $r/255, $g/255, $b/255);
        $this->ColorFlag = ($this->FillColor != $this->TextColor);
    }

    function Image($file, $x=null, $y=null, $w=0, $h=0)
    {
        if(!file_exists($file))
            $this->Error('Image file does not exist: '.$file);

        if(!isset($this->images[$file]))
        {
            $info = @getimagesize($file);
            if(!$info)
                $this->Error('Invalid image file: '.$file);

            $type = $info[2];
            if($type == IMAGETYPE_JPEG)
                $data = $this->_parsejpg($file);
            elseif($type == IMAGETYPE_PNG)
            {
                if(function_exists('imagecreatefrompng'))
                {
                    $img = @imagecreatefrompng($file);
                    if(!$img)
                        $this->Error('Cannot read PNG file: '.$file);
                    $wpx = imagesx($img);
                    $hpx = imagesy($img);
                    $bg = imagecreatetruecolor($wpx, $hpx);
                    $white = imagecolorallocate($bg, 255, 255, 255);
                    imagefill($bg, 0, 0, $white);
                    imagecopy($bg, $img, 0, 0, 0, 0, $wpx, $hpx);
                    $tmp = tempnam(sys_get_temp_dir(), 'fpdf');
                    imagejpeg($bg, $tmp, 90);
                    imagedestroy($img);
                    imagedestroy($bg);
                    $data = $this->_parsejpg($tmp);
                    @unlink($tmp);
                }
                else
                    $this->Error('PNG support requires GD');
            }
            else
                $this->Error('Image type not supported');

            $data['i'] = count($this->images) + 1;
            $this->images[$file] = $data;
        }

        $info = $this->images[$file];
        if($w==0 && $h==0)
        {
            $w = $info['w'] / $this->k;
            $h = $info['h'] / $this->k;
        }
        if($w==0)
            $w = $h * $info['w'] / $info['h'];
        if($h==0)
            $h = $w * $info['h'] / $info['w'];

        if($x===null)
            $x = $this->x;
        if($y===null)
        {
            $y = $this->y;
            $this->y += $h;
        }

        $this->_out(sprintf('q %.2F 0 0 %.2F %.2F %.2F cm /I%d Do Q',
            $w*$this->k, $h*$this->k, $x*$this->k, ($this->h-($y+$h))*$this->k, $info['i']));
    }

    function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='')
    {
        if($this->y+$h > $this->PageBreakTrigger && !$this->InHeader && !$this->InFooter && $this->AcceptPageBreak())
        {
            $x = $this->x;
            $ws = $this->ws;
            if($ws>0)
            {
                $this->ws = 0;
                $this->_out('0 Tw');
            }
            $this->AddPage($this->CurOrientation);
            $this->x = $x;
            if($ws>0)
            {
                $this->ws = $ws;
                $this->_out(sprintf('%.3F Tw', $ws*$this->k));
            }
        }
        if($w==0)
            $w = $this->w-$this->rMargin-$this->x;
        $s = '';
        if($fill || $border==1)
        {
            $op = $fill ? ($border==1 ? 'B' : 'f') : 'S';
            $s = sprintf('%.2F %.2F %.2F %.2F re %s ', $this->x*$this->k, ($this->h-$this->y)*$this->k, $w*$this->k, -$h*$this->k, $op);
        }
        if(is_string($border))
        {
            $x = $this->x;
            $y = $this->y;
            if(strpos($border,'L')!==false)
                $s .= sprintf('%.2F %.2F m %.2F %.2F l S ', $x*$this->k, ($this->h-$y)*$this->k, $x*$this->k, ($this->h-($y+$h))*$this->k);
            if(strpos($border,'T')!==false)
                $s .= sprintf('%.2F %.2F m %.2F %.2F l S ', $x*$this->k, ($this->h-$y)*$this->k, ($x+$w)*$this->k, ($this->h-$y)*$this->k);
            if(strpos($border,'R')!==false)
                $s .= sprintf('%.2F %.2F m %.2F %.2F l S ', ($x+$w)*$this->k, ($this->h-$y)*$this->k, ($x+$w)*$this->k, ($this->h-($y+$h))*$this->k);
            if(strpos($border,'B')!==false)
                $s .= sprintf('%.2F %.2F m %.2F %.2F l S ', $x*$this->k, ($this->h-($y+$h))*$this->k, ($x+$w)*$this->k, ($this->h-($y+$h))*$this->k);
        }
        if($txt!=='')
        {
            if($align=='R')
                $dx = $w-$this->cMargin-$this->GetStringWidth($txt);
            elseif($align=='C')
                $dx = ($w-$this->GetStringWidth($txt))/2;
            else
                $dx = $this->cMargin;
            if($this->ColorFlag)
                $s .= 'q '.$this->TextColor.' ';
            $s .= sprintf('BT %.2F %.2F Td (%s) Tj ET', ($this->x+$dx)*$this->k, ($this->h-($this->y+.5*$h+.3*$this->FontSize))*$this->k, $this->_escape($txt));
            if($this->underline)
                $s .= ' '.$this->_dounderline($this->x+$dx,$this->y+.5*$h+.3*$this->FontSize,$txt);
            if($this->ColorFlag)
                $s .= ' Q';
        }
        if($s)
            $this->_out($s);
        $this->lasth = $h;
        if($ln>0)
        {
            $this->y += $h;
            if($ln==1)
                $this->x = $this->lMargin;
        }
        else
            $this->x += $w;
    }

    function Ln($h=null)
    {
        $this->x = $this->lMargin;
        $this->y += ($h===null ? $this->lasth : $h);
    }

    function Output($dest='', $name='')
    {
        if($this->state==0)
            $this->Open();
        if($this->page==0)
            $this->AddPage();
        $this->_enddoc();
        if($dest=='S')
            return $this->buffer;
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="'.$name.'"');
        echo $this->buffer;
        return '';
    }

    function Open()
    {
        $this->state = 1;
    }

    function AcceptPageBreak()
    {
        return $this->AutoPageBreak;
    }

    function GetStringWidth($s)
    {
        $cw = $this->CurrentFont['cw'];
        $w = 0;
        $l = strlen($s);
        for($i=0;$i<$l;$i++)
            $w += $cw[$s[$i]];
        return $w*$this->FontSize/1000;
    }

    function _setPageSize($size, $orientation)
    {
        if(is_string($size))
        {
            $size = strtolower($size);
            if($size=='a4')
                $size = array(210, 297);
        }
        $this->fw = $size[0];
        $this->fh = $size[1];
        if($orientation=='P')
        {
            $this->w = $this->fw;
            $this->h = $this->fh;
        }
        else
        {
            $this->w = $this->fh;
            $this->h = $this->fw;
        }
        $this->wPt = $this->w*$this->k;
        $this->hPt = $this->h*$this->k;
        $this->PageBreakTrigger = $this->h-$this->bMargin;
        $this->CurOrientation = $orientation;
    }

    function _beginpage($orientation, $size)
    {
        $this->page++;
        $this->pages[$this->page] = '';
        $this->state = 2;
        $this->x = $this->lMargin;
        $this->y = $this->tMargin;
        $this->lasth = 0;
        if($orientation=='' || $orientation==$this->CurOrientation)
        {
            if($size=='' || $size==$this->CurPageSize)
                return;
        }
        $this->_setPageSize($size, $orientation);
        $this->CurPageSize = $size;
    }

    function _endpage()
    {
        $this->state = 1;
    }

    function _escape($s)
    {
        $s = str_replace('\\','\\\\',$s);
        $s = str_replace('(','\\(',$s);
        $s = str_replace(')','\\)',$s);
        $s = str_replace("\r",'',$s);
        return $s;
    }

    function _dounderline($x, $y, $txt)
    {
        $up = $this->CurrentFont['up'];
        $ut = $this->CurrentFont['ut'];
        $w = $this->GetStringWidth($txt)+$this->ws*substr_count($txt,' ');
        return sprintf('%.2F %.2F %.2F %.2F re f', $x*$this->k, ($this->h-($y-$up/1000*$this->FontSize))*$this->k, $w*$this->k, -$ut/1000*$this->FontSizePt);
    }

    function _out($s)
    {
        if($this->state==2)
            $this->pages[$this->page] .= $s."\n";
        else
            $this->buffer .= $s."\n";
    }

    function _newobj()
    {
        $this->n++;
        $this->offsets[$this->n] = strlen($this->buffer);
        $this->_out($this->n.' 0 obj');
    }

    function _enddoc()
    {
        $this->_putpages();
        $this->_putresources();
        $this->_putinfo();
        $this->_putcatalog();
        $o = strlen($this->buffer);
        $this->_out('xref');
        $this->_out('0 '.($this->n+1));
        $this->_out('0000000000 65535 f ');
        for($i=1;$i<=$this->n;$i++)
            $this->_out(sprintf('%010d 00000 n ', $this->offsets[$i]));
        $this->_out('trailer');
        $this->_out('<< /Size '.($this->n+1).' /Root '.$this->n.' 0 R /Info '.($this->n-1).' 0 R >>');
        $this->_out('startxref');
        $this->_out($o);
        $this->_out('%%EOF');
        $this->state = 3;
    }

    function _putpages()
    {
        $nb = $this->page;
        if(!empty($this->AliasNbPages))
        {
            for($n=1;$n<=$nb;$n++)
                $this->pages[$n] = str_replace($this->AliasNbPages, $nb, $this->pages[$n]);
        }
        for($n=1;$n<=$nb;$n++)
        {
            $this->_newobj();
            $this->_out('<</Type /Page');
            $this->_out('/Parent 1 0 R');
            $this->_out('/Resources 2 0 R');
            $this->_out('/MediaBox [0 0 '.$this->wPt.' '.$this->hPt.']');
            $this->_out('/Contents '.($this->n+1).' 0 R>>');
            $this->_out('endobj');
            $p = $this->pages[$n];
            $this->_newobj();
            $this->_out('<< /Length '.strlen($p).' >>');
            $this->_out('stream');
            $this->_out($p);
            $this->_out('endstream');
            $this->_out('endobj');
        }
        $this->_newobj();
        $this->_out('<</Type /Pages');
        $kids = '';
        for($i=0;$i<$nb;$i++)
            $kids .= (3+2*$i).' 0 R ';
        $this->_out('/Kids ['.$kids.']');
        $this->_out('/Count '.$nb);
        $this->_out('>>');
        $this->_out('endobj');
    }

    function _putresources()
    {
        $this->_putimages();
        $this->_newobj();
        $this->_out('<< /ProcSet [/PDF /Text]');
        $this->_out('/Font <<');
        foreach($this->fonts as $font)
            $this->_out('/F'.$font['i'].' << /Type /Font /Subtype /Type1 /BaseFont /'.$font['name'].' >>');
        $this->_out('>>');
        if(!empty($this->images))
        {
            $this->_out('/XObject <<');
            foreach($this->images as $info)
                $this->_out('/I'.$info['i'].' '.$info['n'].' 0 R');
            $this->_out('>>');
        }
        $this->_out('>>');
        $this->_out('endobj');
    }

    function _putinfo()
    {
        $this->_newobj();
        $this->_out('<< /Producer (FPDF) >>');
        $this->_out('endobj');
    }

    function _putcatalog()
    {
        $this->_newobj();
        $this->_out('<< /Type /Catalog /Pages 1 0 R >>');
        $this->_out('endobj');
    }

    function _parsejpg($file)
    {
        $info = getimagesize($file);
        if(!$info)
            $this->Error('Invalid JPEG file: '.$file);
        if($info[2] != IMAGETYPE_JPEG)
            $this->Error('Not a JPEG file: '.$file);
        $data = file_get_contents($file);
        return array(
            'w' => $info[0],
            'h' => $info[1],
            'cs' => 'DeviceRGB',
            'bpc' => 8,
            'f' => 'DCTDecode',
            'data' => $data
        );
    }

    function _putimages()
    {
        foreach($this->images as $file => $info)
        {
            $this->_newobj();
            $this->images[$file]['n'] = $this->n;
            $this->_out('<</Type /XObject');
            $this->_out('/Subtype /Image');
            $this->_out('/Width '.$info['w']);
            $this->_out('/Height '.$info['h']);
            $this->_out('/ColorSpace /'.$info['cs']);
            $this->_out('/BitsPerComponent '.$info['bpc']);
            $this->_out('/Filter /'.$info['f']);
            $this->_out('/Length '.strlen($info['data']).'>>');
            $this->_out('stream');
            $this->_out($info['data']);
            $this->_out('endstream');
            $this->_out('endobj');
        }
    }

    function Error($msg)
    {
        throw new Exception('FPDF error: '.$msg);
    }
}
