<?php header('Access-Control-Allow-Origin: *'); ?>
<?php
 
// get the HTTP method, path and body of the request
$method = $_SERVER['REQUEST_METHOD'];
header('Content-Type: application/json');
$request = explode('/', trim($_SERVER['PATH_INFO'],'/'));
$input = json_decode(file_get_contents('php://input'),true);

// connect to the mysql database
$user = 'root';
$password = 'MkHHyQHzajQ6U.';
$db = 'dbISATest';
$host = 'localhost';
$link = mysql_connect($host,$user,$password) or die($connect_error);
mysql_select_db($db)or die($connect_error);
// retrieve the table and key from the path
$table = preg_replace('/[^a-z0-9_]+/i','',array_shift($request));
$key = array_shift($request)+0;

// escape the columns and values from the input object
$columns = preg_replace('/[^a-z0-9_]+/i','',array_keys($input));
$values = array_map(function ($value) use ($link) {
  if ($value===null) return null;
  return mysql_real_escape_string($link,(string)$value);
},array_values($input));

// build the SET part of the SQL command
$set = '';
for ($i=0;$i<count($columns);$i++) {
  $set.=($i>0?',':'').'`'.$columns[$i].'`=';
  $set.=($values[$i]===null?'NULL':'"'.$values[$i].'"');
}

// create SQL based on HTTP method
switch ($method) {
  case 'GET':
    $sql = "SELECT t3.Power , t3.NomenclatureTower, t2.NameEstructure, t2.Description ,t1.MarkNo, t1.Quantity FROM tblpieces t1 INNER JOIN tblpartsestructure t2 on t1.idPartsEstructure = t2.idPartsEstructure INNER JOIN tbltypetower t3  on t2.idTypeTower  = t3.idTypeTower GROUP BY 5;"; break;
  case 'PUT':
    //$sql = "update `$table` set $set where id=$key"; break;
  case 'POST':
    //$sql = "insert into `$table` set $set"; break;
  case 'DELETE':
    //$sql = "delete `$table` where id=$key"; break;
}
// excecute SQL statement
$result = mysql_query($sql);
// die if SQL statement failed
if (!$result) {
  http_response_code(404);
  die(mysql_error());
}

// $sql_details = array($user,$password,$db,$host);

// $jsonp = preg_match('/^[$A-Z_][0-9A-Z_$]*$/i', $_GET['callback']) ?
// $_GET['callback'] :
// false;

// if ( $jsonp ) {
// echo $jsonp.'('.json_encode(
//     SSP::simple( $_GET, $sql_details, $table, $primaryKey, $columns )
// ).');';
// }

//variables with a Object Json
$varJsonIni = '{"data":[';
$varJsonFinish = ']}';

// print results, insert id or affected row count
if ($method == 'GET') {
//   if (!$key);
   for ($i=0;$i<mysql_num_rows($result);$i++) {
    $varJson .= ($i>0?',':'').json_encode(mysql_fetch_object($result));
     //mysqli_fetch_object($result)
   }

// //   // if (!$key) $varJson .=  $varJsonFinish;
// //    return json_encode($varJson);
// // } elseif ($method == 'POST') {
// //   return json_encode(mysqli_insert_id($link));
// // } else {
// //   return json_encode(mysqli_affected_rows($link));
 echo $varJsonIni . $varJson . $varJsonFinish ;
//  //echo $varJson;

//   // if (mysqli_num_rows($result) > 0){

//   //     //echo json_encode($varJson . $result . $varJsonFinish);
//   //     return json_encode($varJson . $result . $varJsonFinish);
//   // }else{
//   //     return json_encode("Error No existen datos.");
//   // }


}

// close mysql connection
mysql_close($link);
$result -> close();