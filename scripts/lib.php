<?php

ini_set('memory_limit','556M');

date_default_timezone_set('America/Los_Angeles');

$format_years = array(
	"2013" => "2013",
	"2012" =>  "2013",
	"2011" => "2013",
	"2010" => "2013",
	"2009" => "2013",
	"2008" => "2008",
	"2007" => "2008",
	"2006" => "2008",
	"2005" => "2008",
	"2004" => "2008",
	"2003" => "2008",
	"2002" => "2008",
	"2001" => "2008",
	"2000" => "2008",
	"1999" => "2008",
	"1998" => "2008",
	"1997" => "1997",
	"1996" => "1997",
	"1995" => "1997",
	"1994" => "1997",
	"1993" => "1997",
);

$ethnic_2013 = array(
	"0" => "Not reported",
	"1" => "American Indian or Alaska Native",
	"2" => "Asian",
	"3" => "Pacific Islander",
	"4" => "Filipino",
	"5" => "Hispanic or Latino",
	"6" => "African American",
	"7" => "White",
	"9" => "Two or More Races",
);

$ethnic_2008 = array(
	"1" => "American Indian or Alaska Native",
	"2" => "Asian",
	"3" => "Pacific Islander",
	"4" => "Filipino",
	"5" => "Hispanic or Latino",
	"6" => "African American",
	"7" => "White, not Hispanic",
	"8" => "Multiple or No Response",
);

$ethnic_1997 = array(
	"1" => "American Indian or Alaska Native",
	"2" => "Asian",
	"3" => "Pacific Islander",
	"4" => "Filipino",
	"5" => "Hispanic or Latino",
	"6" => "African American",
	"7" => "White",
);

$years = array(
"2013" => "2013-14",
"2012" => "2012-13",
"2011" => "2011-12",
"2010" => "2010-11",
"2009" => "2009-10",
"2008" => "2008-09",
"2007" => "2007-08",
"2006" => "0607",
"2005" => "0506",
"2004" => "0405",
"2003" => "0304",
"2002" => "0203",
"2001" => "0102",
"2000" => "0001",
"1999" => "9900",
"1998" => "9899",
"1997" => "9798",
"1996" => "9697",
"1995" => "9596",
"1994" => "9495",
"1993" => "9394",
);

function parseFileSum($year,$filename) {
        global $format_years, $ethnic_2013, $ethnic_2008;

        $contents = file($filename);
        #$contents = array_slice($contents,0,3);

        $headers = $contents[0];
        unset($contents[0]);
        $header_fields = explode("\t",$headers);

        $data = array();

        if ($format_years[$year] == '2013') {
                $grade_fields = array_slice($header_fields,6);

                foreach ($contents as $line) {
                        $line = trim($line);
                        $bits = explode("\t",$line);

                        $grades = array_slice($bits,6);

                        $grade_array = array();
                        foreach($grades as $index=>$student_number) {
                                $grade_array[trim($grade_fields[$index])] = $student_number;
                        }

                        if (is_array($data[$bits[1]][$bits[2]][$bits[3]])) {
                                $add = function($a, $b) { return $a + $b; };
                                $summedArray = array_map($add, $data[$bits[1]][$bits[2]][$bits[3]], $grade_array);

                                $data[$bits[1]][$bits[2]][$bits[3]] = $summedArray;
                        }
                        else {
                                $data[$bits[1]][$bits[2]][$bits[3]] = $grade_array;
                        }
                }
        }

        return array($grade_array,$data);
}

function generateFile($county, $outputFileName,$data, $countsData) {

        $ignore = ['COUNTY', 'DISTRICT', 'CDS_CODE', 'YEAR'];
        $ignoreIndex = [];

        $outputFileName= preg_replace("/[^\s\da-z]/i","",$outputFileName);
        #print "Generating $outputFileName\n";

        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        $sheet_ctr = 1;

        # Do the first page which is totals and sums

        // Create the first sheet which is a total counts sheet
        $objPHPExcel->setActiveSheetIndex();

        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle('All Years');

        $count_row_ctr = 1;
        $countYears = array_keys($countsData);
        $alphas = range('B', 'Z');

        $objPHPExcel->getActiveSheet()->setCellValue("A1", "Grade");
        $objPHPExcel->getActiveSheet()->getStyle("A1")->getFont()->setBold( true );

        foreach($countYears as $k=>$v)
        {
          $cell_name = array_shift($alphas) . "1";
          $objPHPExcel->getActiveSheet()->setCellValue($cell_name, "$v");         
          $objPHPExcel->getActiveSheet()->getStyle($cell_name)->getFont()->setBold(true);
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
              $objPHPExcel->getActiveSheet()->setCellValue($gradeCell, $grade);
            }

            $cell_name = $cellAlpha . ($count_row_ctr + 1);
            $objPHPExcel->getActiveSheet()->setCellValue($cell_name, $gradeCounts['sum']);
            $count_row_ctr++;
          }
        }

        foreach ($data as $year => $schools) {
                $next_year = $year + 1;
                $sheet_name = "$year-$next_year";

                // Create a new worksheet, after the default sheet
                $objPHPExcel->createSheet();
                $objPHPExcel->setActiveSheetIndex($sheet_ctr);

                // Rename sheet
                $objPHPExcel->getActiveSheet()->setTitle($sheet_name);

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
		                        $objPHPExcel->getActiveSheet()->setCellValue($cell_name, "$v");
                                        $objPHPExcel->getActiveSheet()->getStyle($cell_name)->getFont()->setBold( true );
        		        }
			}

	                $values = array_values($school_data);
        	        foreach ($values as $k=>$v) {
                                if(in_array($k, $ignoreIndex)) continue;

                        	$cell_name = array_shift($alphaIndex) . ($row_ctr + 2);
	                        $objPHPExcel->getActiveSheet()->setCellValue($cell_name, "$v");
        	        }

			$row_ctr++;
		}

                $sheet_ctr++;
        }

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

        if(!is_dir('output/' . $county))
        {
          mkdir('output/' . $county);
        }

        $objWriter->save('output/'. $county . '/' . $outputFileName . '.xls');
        $objPHPExcel->disconnectWorksheets();
        unset($objPHPExcel);

}
