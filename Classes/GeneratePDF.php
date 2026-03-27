<?php

namespace Classes;

use mikehaertl\pdftk\Pdf;
use Exception;

class GeneratePDF
{

        //Dynamic path for pdftk library
        private $currentDirectory;
        private $pdftkLibrary_path;

        public function __construct()
        {
                $this->currentDirectory = dirname(__FILE__);
                $this->pdftkLibrary_path = dirname($this->currentDirectory);
        }
        public function generate($data, $pdfFileName, $type, $isQuote, $isWrap, $Mon6_12 = false)
        {

                try {

                        $filename = $pdfFileName;
                        $fillableForm = '';
                        $upload_path = '';

                        $upload_path = 'uploads/warranty_pdf/';
                        // if ($Mon6_12 == true) {
                        //         if ($type == '1') {
                        //                 $fillableForm = 'Classes/Fillable6_12_T1.pdf';
                        //         } else if ($type == '2') {
                        //                 $fillableForm = 'Classes/Fillable6_12_T2.pdf';
                        //         } else if ($type == '3') {
                        //                 $fillableForm = 'Classes/Fillable6_12_T3.pdf';
                        //         } else {
                        //                 $fillableForm = 'Classes/Fillable6_12_T4.pdf';
                        //         }
                        // } else {
                        //         if ($isWrap == 'N') {
                        //                 if ($type == '1') {
                        //                         $fillableForm = 'Classes/warranty_type1.pdf';
                        //                 } else if ($type == '2') {
                        //                         $fillableForm = 'Classes/warranty_type2.pdf';
                        //                 }  else if ($type == '3') {
                        //                         $fillableForm = 'Classes/warranty_type3.pdf';
                        //                 } else {
                        //                         $fillableForm = 'Classes/warranty_type4.pdf';
                        //                 }
                        //         } else {

                        //                 $fillableForm = 'Classes/TNG EMV WRAP WA ENG v0323 _ Flllable.pdf';
                        //         }
                        // }
                        $fillableForm = 'Classes/Vital Trends Agreement Fillable v0425.pdf';


                        $pdf = new Pdf($fillableForm, [
                                'command' => $this->pdftkLibrary_path . '/pdftk'
                        ]);


                        $pdf->fillForm($data)
                                ->flatten()
                                ->saveAs($upload_path . $filename);
                        return $filename;
                } catch (Exception $e) {
                        return $e->getMessage();
                }
        }

        public function generateAddendum($data, $filename)
        {
                $fillableForm = 'Classes/Vital Trends Wearables Coverage Addendum v0324 _ Fillable.pdf';
                try {
                        $pdf = new Pdf($fillableForm, [
                                'command' => $this->pdftkLibrary_path . '/pdftk'
                        ]);


                        $pdf->fillForm($data)
                                ->flatten()
                                ->saveAs($filename);
                        return $filename;
                } catch (\Throwable $th) {
                        throw $th;
                }
        }

        public function generateWearForm($data, $filename)
        {
                $fillableForm = 'Classes/VT TNG EMV MWR v0622 F Fillable.pdf';
                try {
                        $pdf = new Pdf($fillableForm, [
                                'command' => $this->pdftkLibrary_path . '/pdftk'
                        ]);


                        $pdf->fillForm($data)
                                ->flatten()
                                ->saveAs($filename);
                        return $filename;
                } catch (\Throwable $th) {
                        throw $th;
                }
        }


        public function generateSgSummary($data, $fileName)
        {

                try {

                        $filename = $fileName;
                        $pdf = new Pdf('Classes/small_goods_summary.pdf',  [
                                'command' => $this->pdftkLibrary_path . '/pdftk'
                        ]);
                        $response =  $pdf->fillForm($data)
                                ->flatten()
                                ->saveAs('uploads/small_goods_summary_pdf/' . $filename);

                        return $filename;
                } catch (Exception $e) {
                        return $e->getMessage();
                }
        }



        public function generateSgDetail($data, $filename)
        {
                try {

                        $pdf = new Pdf('Classes/small_goods_detail.pdf', [
                                'command' => $this->pdftkLibrary_path . '/pdftk'
                        ]);
                        $response =  $pdf->fillForm($data)
                                ->flatten()
                                ->saveAs('uploads/small_goods_detail_pdf_for_merging/' . $filename);
                        return $filename;
                } catch (Exception $e) {
                        return $e->getMessage();
                }
        }



        //This functions is for the Dealer agreeement fillable PDF generation.
        public function generateSgDetailDealer($data, $filename)
        {
                try {

                        $pdf = new Pdf('uploads/fillable_documents/VT TNG GARA v0622 F Fillable.pdf', [
                                'command' =>  $this->pdftkLibrary_path . '/pdftk'
                        ]);
                        $response =  $pdf->fillForm($data)
                                ->flatten()
                                ->saveAs('uploads/dealer_agreement_pdf/' . $filename);

                        return $filename;
                } catch (Exception $e) {
                        return $e->getMessage();
                }
        }




        public function generateMultipageSgDetail($data, $fileName)
        {
                try {

                        $filename = $fileName;
                        $pdf = new Pdf('Classes/custom_small_goods_detail.pdf', [
                                'command' => $this->pdftkLibrary_path . '/pdftk'
                        ]);
                        $response =  $pdf->fillForm($data)
                                ->flatten()
                                // ->saveAs( 'uploads/small_goods_detail_pdf/' . $filename);
                                ->saveAs('uploads/small_goods_detail_pdf_for_merging/' . $filename);
                        return $filename;
                } catch (Exception $e) {
                        return $e->getMessage();
                }
        }





        public function convertToRegularPDF($data, $file, $fileName)
        {
                try {

                        $filename = $fileName;
                        $RegularPDFPath = 'uploads/warranty_pdf/RegularPDF/' . $filename;
                        $pdf = new Pdf($file, [
                                'command' => $this->pdftkLibrary_path . '/pdftk'
                        ]);
                        $response =  $pdf->fillForm($data)
                                ->flatten()
                                // ->saveAs( 'uploads/small_goods_detail_pdf/' . $filename);
                                ->saveAs($RegularPDFPath);
                        return $RegularPDFPath;
                } catch (Exception $e) {
                        return $e->getMessage();
                }
        }
}
