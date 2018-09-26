<?php

require_once('db.php');
require_once('lib.php');
require_once "PHPExcel/Classes/PHPExcel.php";
require_once 'PHPExcel/Classes/PHPExcel/IOFactory.php';

$db = new DBSql();
$dbYears = $db->getYears();

$counties = $db->getCounties();

foreach($counties as $county)
{
  print "County: " . $county[1] . "\n";

  $districts = $db->getDistrictsByCounty($county[0]);
  foreach($districts as $district)
  {
    #if($district[0] != 692 && $district[0] != 899) continue;

    print "  District: " . $district[1] . "\n";

    $district_info = [];
    $counts_info = [];
    foreach(array_reverse($dbYears) as $yearInfo)
    {
      
      #print "CREATE DATA FOR: " . $yearInfo['year'] . "\n";

      $results = $db->getDataByDistrictAndYear($district[1], $yearInfo['year']);
      if(!isset($results[0]))
      {
        #print "NO RESULTS FOR: " . $district[1] . ", " . $yearInfo['year'] .  "\n";
      }
      else
      {
        $district_info[$yearInfo['year']] = $results;
        if(!isset( $counts_info[$yearInfo['year']]))
        {
           $counts_info[$yearInfo['year']] = [];
        }

        foreach($results as $row)
        {
          $counts_info[$yearInfo['year']] = array_merge_recursive($counts_info[$yearInfo['year']], array_slice($row,5));
        }
      }
    }

    foreach($counts_info as $year=>$counts)
    {
      foreach($counts as $grade=>$grade_counts)
      {
        if(is_array($counts_info[$year][$grade]))
        {
          $counts_info[$year][$grade]['sum'] = array_sum($grade_counts);
        }
        else
        {
          $tempGradeCounts = [];
          $tempGradeCounts[] = $grade_counts;
          $tempGradeCounts['sum'] = $grade_counts;
          $counts_info[$year][$grade] = $tempGradeCounts;
        }
      }
    }

#print_r($counts_info);
#exit;
#print_r($district_info);
#exit;

    $filename = str_replace(' ', '', $district[1]);
    generateFile($county[1], $filename, $district_info, $counts_info);

  }
}

