<?php

require_once '../bootstrap.php';

$distinctYears = $RawDataService->findDistinctYears();

$distinctCounties = $RawDataService->findDistinctCounties();

foreach($distinctCounties as $county)
{
  print "County: " . $county['COUNTY'] . "\n";

  $districts = $RawDataService->getDistrictsByCounty($county['COUNTY']);
  foreach($districts as $district)
  {
    #if($district[0] != 692 && $district[0] != 899) continue;

    print "  District: " . $district['DISTRICT'] . "\n";

    $district_info = [];
    $counts_info = [];
    foreach($distinctYears as $yearInfo)
    {
      
      #print "CREATE DATA FOR: " . $yearInfo['YEAR'] . "\n";

      $results = $RawDataService->getDataByDistrictAndYear($district['DISTRICT'], $yearInfo['YEAR']);
      if(!isset($results[0]))
      {
        print "NO RESULTS FOR: " . $district['DISTRICT'] . ", " . $yearInfo['YEAR'] .  "\n";
      }
      else
      {
        $district_info[$yearInfo['YEAR']] = $results;
        if(!isset( $counts_info[$yearInfo['YEAR']]))
        {
           $counts_info[$yearInfo['YEAR']] = [];
        }

        foreach($results as $row)
        {
          $counts_info[$yearInfo['YEAR']] = array_merge_recursive($counts_info[$yearInfo['YEAR']], array_slice($row,5));
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

    $filename = str_replace(' ', '', $district['DISTRICT']);
    $ExcelGenerator->generateFile($county['COUNTY'], $filename, $district_info, $counts_info);

  }
}

