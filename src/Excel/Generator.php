<?php

namespace KingConsulting\Excel;

use PHPExcel;
use PHPExcel\IOFactory;

class Generator {

  private $PHPExcel;
  private $outputDirectory;

  public function __construct(PHPExcel $PHPExcel, $outputDirectory)
  {
    $this->PHPExcel = $PHPExcel;
    $this->outputDirectory = $outputDirectory;
  }

  function generateFile($county, $outputFileName,$data, $countsData) 
  {
    // I DONT LIKE DOING THIS; WANT TO USE DEPENDENCY INJECTION
    // BUT SOMETHING IS GOING WRONG WHEN TRYING TO REUSE THE OBJ
    $this->PHPExcel = new PHPExcel();

    $ignore = ['COUNTY', 'DISTRICT', 'CDS_CODE', 'YEAR'];
    $ignoreIndex = [];

    $outputFileName= preg_replace("/[^\s\da-z]/i","",$outputFileName);
    #print "Generating $outputFileName\n";

    $sheet_ctr = 1;

    # Do the first page which is totals and sums

    // Create the first sheet which is a total counts sheet
    $this->PHPExcel->setActiveSheetIndex();

    // Rename sheet
    $this->PHPExcel->getActiveSheet()->setTitle('All Years');

    $count_row_ctr = 1;
    $countYears = array_keys($countsData);
    $alphas = range('B', 'Z');

    $this->PHPExcel->getActiveSheet()->setCellValue("A1", "Grade");
    $this->PHPExcel->getActiveSheet()->getStyle("A1")->getFont()->setBold( true );

    foreach($countYears as $k=>$v)
    {
      $cell_name = array_shift($alphas) . "1";
      $this->PHPExcel->getActiveSheet()->setCellValue($cell_name, "$v");
      $this->PHPExcel->getActiveSheet()->getStyle($cell_name)->getFont()->setBold(true);
    }

    $alphaIndex = range('B', 'Z');
    foreach($countsData as $year=>$counts)
    {
      $count_row_ctr = 1;
      $cellAlpha = array_shift($alphaIndex);
      foreach($counts as $grade=>$gradeCounts)
      {
        if($cellAlpha == 'B')
        {
          $gradeCell = "A" . ($count_row_ctr + 1);
          $this->PHPExcel->getActiveSheet()->setCellValue($gradeCell, $grade);
        }

        $cell_name = $cellAlpha . ($count_row_ctr + 1);
        $this->PHPExcel->getActiveSheet()->setCellValue($cell_name, $gradeCounts['sum']);
        $count_row_ctr++;
      }
    }

    foreach ($data as $year => $schools) 
    {
      $next_year = $year + 1;
      $sheet_name = "$year-$next_year";

      // Create a new worksheet, after the default sheet
      $this->PHPExcel->createSheet();
      $this->PHPExcel->setActiveSheetIndex($sheet_ctr);

      // Rename sheet
      $this->PHPExcel->getActiveSheet()->setTitle($sheet_name);

      $row_ctr = 0;
      foreach ($schools as $key=>$school_data) {
        $alphas = range('A', 'Z');
        $alphaIndex = range('A', 'Z');
        if($row_ctr == 0) {
          $keys = array_keys($school_data);
          foreach ($keys as $k=>$v) {
            if(in_array($v, $ignore))
            {
              $ignoreIndex[] = $k;
              continue;
            }

            $cell_name = array_shift($alphas) . ($row_ctr + 1);
            $this->PHPExcel->getActiveSheet()->setCellValue($cell_name, "$v");
            $this->PHPExcel->getActiveSheet()->getStyle($cell_name)->getFont()->setBold( true );
          }
        }

        $values = array_values($school_data);
        foreach ($values as $k=>$v) {
          if(in_array($k, $ignoreIndex)) continue;

          $cell_name = array_shift($alphaIndex) . ($row_ctr + 2);
          $this->PHPExcel->getActiveSheet()->setCellValue($cell_name, "$v");
        }

        $row_ctr++;
      }

      $sheet_ctr++;
    }

    $objWriter = \PHPExcel_IOFactory::createWriter($this->PHPExcel, 'Excel2007');

    if(!is_dir($this->outputDirectory . '/' . $county))
    {
      mkdir($this->outputDirectory . '/' . $county);
    }

    $objWriter->save($this->outputDirectory . '/' . $county . '/' . $outputFileName . '.xls');
    $this->PHPExcel->disconnectWorksheets();
    unset($this->PHPExcel);
  }

}

