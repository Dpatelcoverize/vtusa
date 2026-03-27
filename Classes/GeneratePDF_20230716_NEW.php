<?php
namespace Classes;

use mikehaertl\pdftk\Pdf;

class GeneratePDF {

public function generate($data , $pdfFileName , $type , $isQuote, $isWrap)
{

try {

$filename = $pdfFileName;
$fillableForm ='';
$upload_path = '';

$upload_path = 'uploads/warranty_pdf/';

if($isWrap == 'N'){
        if($type == '1')
        {
        $fillableForm = 'Classes/warranty_type1.pdf';
        }
        else if($type == '2')
        {
        $fillableForm = 'Classes/warranty_type2.pdf';
        }
        else
        {
        $fillableForm = 'Classes/warranty_type3.pdf';
        }
}else{

        $fillableForm = 'Classes/TNG EMV WRAP WA ENG v0323 _ Flllable.pdf';
}

//Dynamic path for pdftk library
$currentDirectory = dirname(dirname(__FILE__));
$pdftkLibrary_path = dirname($currentDirectory);

$pdf = new Pdf($fillableForm, [
    'command' => $pdftkLibrary_path.'/pdftk'
]);

$pdf->fillForm($data)
->flatten()
->saveAs($upload_path . $filename);

return $filename;

}
catch(Exception $e)
{
return $e->getMessage();
}

}


public function generateSgSummary($data, $fileName)
{

    try {

   
        $filename = $fileName;
        //Dynamic path for pdftk library
        $currentDirectory = dirname(dirname(__FILE__));
        $pdftkLibrary_path = dirname($currentDirectory);
        $pdf = new Pdf('Classes/small_goods_summary.pdf',  [
                'command' => $pdftkLibrary_path.'/pdftk'
        ]);
       $response =  $pdf->fillForm($data)
        ->flatten()
        ->saveAs( 'uploads/small_goods_summary_pdf/' . $filename);
        
        return $filename;
        
        }
        catch(Exception $e)
        {
        return $e->getMessage();
        }

}



public function generateSgDetail($data , $filename)
{
    try {

        //Dynamic path for pdftk library
        $currentDirectory = dirname(dirname(__FILE__));
        $pdftkLibrary_path = dirname($currentDirectory);
        $pdf = new Pdf('Classes/small_goods_detail.pdf', [
                'command' => $pdftkLibrary_path.'/pdftk'
        ]);
       $response =  $pdf->fillForm($data)
        ->flatten()
        ->saveAs( 'uploads/small_goods_detail_pdf_for_merging/' . $filename);
        return $filename;
        
        }
        catch(Exception $e)
        {
        return $e->getMessage();
        }
}



//This functions is for the Dealer agreeement fillable PDF generation.
public function generateSgDetailDealer($data , $filename)
{
    try {

        //Dynamic path for pdftk library
        $currentDirectory = dirname(dirname(__FILE__));
        $pdftkLibrary_path = dirname($currentDirectory);
    
        $pdf = new Pdf('uploads/fillable_documents/VT TNG GARA v0622 F Fillable.pdf', [
                'command' =>  $pdftkLibrary_path.'/pdftk'
        ]);
       $response =  $pdf->fillForm($data)
        ->flatten()
        ->saveAs( 'uploads/dealer_agreement_pdf/' . $filename);

        return $filename;
        
        }
        catch(Exception $e)
        {
        return $e->getMessage();
        }
}




public function generateMultipageSgDetail($data, $fileName)
{
       try {

        $filename = $fileName;
        //Dynamic path for pdftk library
        $currentDirectory = dirname(dirname(__FILE__));
        $pdftkLibrary_path = dirname($currentDirectory);
        $pdf = new Pdf('Classes/custom_small_goods_detail.pdf', [
                'command' => $pdftkLibrary_path.'/pdftk'
        ]);
       $response =  $pdf->fillForm($data)
        ->flatten()
        // ->saveAs( 'uploads/small_goods_detail_pdf/' . $filename);
        ->saveAs( 'uploads/small_goods_detail_pdf_for_merging/' . $filename);
        return $filename;
        
        }
        catch(Exception $e)
        {
        return $e->getMessage();
        }
}





public function convertToRegularPDF($data, $file, $fileName)
{
       try {

        $filename = $fileName;
        //Dynamic path for pdftk library
        $currentDirectory = dirname(dirname(__FILE__));
        $pdftkLibrary_path = dirname($currentDirectory);
        $RegularPDFPath = 'uploads/warranty_pdf/RegularPDF/' . $filename;
        $pdf = new Pdf($file, [
                'command' => $pdftkLibrary_path.'/pdftk'
        ]);
       $response =  $pdf->fillForm($data)
        ->flatten()
        // ->saveAs( 'uploads/small_goods_detail_pdf/' . $filename);
        ->saveAs($RegularPDFPath);
        return $RegularPDFPath;
        
        }
        catch(Exception $e)
        {
        return $e->getMessage();
        }
    
}


}