<?php

class DBSql {

  var $conn = null;

  function __construct()
  {
    # mysql+pymysql://root:&$#$JFl23asfjA)8wfLFr29&^@localhost/CaliforniaEnrollment

    $mysqli = new mysqli("localhost", "root", '&$#$JFl23asfjA)8wfLFr29&^', "CaliforniaEnrollment");
    if ($mysqli->connect_errno) {
      echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
    }
    #echo $mysqli->host_info . "\n";
    $this->conn = $mysqli;
  }

  function getCounties()
  {
    $res = $this->conn->query("SELECT * FROM Counties ORDER BY name");
    #$res = $this->conn->query("SELECT * FROM Counties WHERE id = 45 ORDER BY name");
    return $res->fetch_all();
  }

  function getDistricts()
  {
    $res = $this->conn->query("SELECT * FROM Districts ORDER BY name");
    return $res->fetch_all();
  }

  function getDistrictsByCounty($id)
  {
    $res = $this->conn->query("SELECT * FROM Districts WHERE county_id = $id ORDER BY name");
    return $res->fetch_all();
  }

  function getSchoolsByDistrictId($id)
  {
    $res = $this->conn->query("SELECT * FROM Schools WHERE district_id = $id ORDER BY name");
    return $res->fetch_all();
  }

  function getSchoolDataBySchoolId($id)
  {
    $res = $this->conn->query("SELECT * FROM SchoolGradeCounts WHERE school_id = $id");
    return $res->fetch_all(MYSQLI_ASSOC);
  }

  function getCountsByDistrict($district_id)
  {
    $sql = "SELECT year, sum(kdgn) as kdgn, sum(gr_1) as gr_1, sum(gr_2) as gr_2, 
	sum(gr_3) as gr_3, sum(gr_4) as gr_4, sum(gr_5) as gr_5, 
	sum(gr_6) as gr_6, sum(gr_7) as gr_7, sum(gr_8) as gr_8, 
	sum(gr_9) as gr_9, sum(gr_10) as gr_10, sum(gr_11) as gr_11, sum(gr_12) as gr_12
FROM Schools s
JOIN SchoolGradeCounts gc ON gc.school_id = s.id
WHERE s.district_id = $district_id
GROUP BY year";
    $res = $this->conn->query($sql);
    return $res->fetch_all(MYSQLI_ASSOC);
  }

  function getDataByDistrictAndYear($district, $year)
  {
    $sql = '
SELECT 
	COUNTY, DISTRICT, SCHOOL, CDS_CODE, YEAR, 
	sum(KDGN) as KD, sum(GR_1) as "1st", sum(GR_2) as "2nd", 
	sum(GR_3) as "3rd", sum(GR_4) as "4th", sum(GR_5) as "5th", 
	sum(GR_6) as "6th", sum(GR_7) as "7th", sum(GR_8) as "8th", 
	sum(GR_9) as "9th", sum(GR_10) as "10th", sum(GR_11) as "11th", 
	sum(GR_12) as "12th", sum(ENR_TOTAL) as Total, 
	sum(UNGR_ELM) as "Ungr Elem", sum(UNGR_SEC) as "Ungr Sec"
FROM EnrollmentRawData
WHERE DISTRICT = "' . $district . '"
AND YEAR = ' . $year . '
AND SCHOOL NOT IN ("Nonpublic, Nonsectarian Schools")
GROUP BY CDS_CODE, YEAR
ORDER BY SCHOOL
    ';
    #print $sql ."\n";
    $res = $this->conn->query($sql);
    return $res->fetch_all(MYSQLI_ASSOC);
  }

  function getYears()
  {
    $sql ="SELECT DISTINCT year FROM SchoolGradeCounts";
    $res = $this->conn->query($sql);
    return $res->fetch_all(MYSQLI_ASSOC);
  }
}

