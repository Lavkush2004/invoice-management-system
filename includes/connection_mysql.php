<?php

$dbUrl = getenv('MYSQL_URL') ?: getenv('DATABASE_URL') ?: '';
$parsedHost = '';
$parsedUser = '';
$parsedPass = '';
$parsedDb = '';
$parsedPort = 3306;

if (!empty($dbUrl)) {
    $parts = parse_url($dbUrl);
    if ($parts) {
        $parsedHost = $parts['host'] ?? '';
        $parsedUser = $parts['user'] ?? '';
        $parsedPass = $parts['pass'] ?? '';
        $parsedDb = isset($parts['path']) ? ltrim($parts['path'], '/') : '';
        $parsedPort = isset($parts['port']) ? (int)$parts['port'] : 3306;
    }
}

$dbHost = $parsedHost ?: getenv('MYSQLHOST') ?: getenv('DB_HOST') ?: getenv('MYSQL_HOST') ?: 'localhost';
$dbUser = $parsedUser ?: getenv('MYSQLUSER') ?: getenv('DB_USER') ?: getenv('MYSQL_USER') ?: 'root';
$dbPass = $parsedPass ?: getenv('MYSQLPASSWORD') ?: getenv('DB_PASS') ?: getenv('MYSQL_PASSWORD') ?: '';
$dbName = $parsedDb ?: getenv('MYSQLDATABASE') ?: getenv('DB_NAME') ?: getenv('MYSQL_DATABASE') ?: 'billing';
$dbPort = $parsedPort ?: (int)(getenv('MYSQLPORT') ?: getenv('DB_PORT') ?: getenv('MYSQL_PORT') ?: 3306);

define("MYSQL_HOST", $dbHost);
define("MYSQL_USER", $dbUser);
define("MYSQL_PASS", $dbPass);
define("MYSQL_DB", $dbName);
define("MYSQL_PORT", $dbPort);

$con = mysqli_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_DB, MYSQL_PORT);
if (!$con) {
    http_response_code(500);
    error_log('Database connection failed: ' . mysqli_connect_error());
    exit('Database unavailable.');
}
mysqli_set_charset($con, 'utf8mb4');

$mysql_con = $con;

function insert_mysql($data,$table)
{
    global $con;
    if(isset($data['id'])){unset($data['id']);}
    $insert_string1=array();
    $insert_string2=array();
    foreach($data as $key=>$value)
    {
        array_push($insert_string1,"`".$key."`");
        array_push($insert_string2,"'".$value."'");
    }
    $insert_string1=implode(",",$insert_string1);
    $insert_string2=implode(",",$insert_string2);
    $query="insert into $table ($insert_string1) VALUES($insert_string2)";
    $insertQuery=mysqli_query($con,$query);
    if($insertQuery)
    {
        return array("success"=>"true","data"=>mysqli_insert_id($con));
    }
    else
    {
        return array("success"=>"false","data"=>mysqli_error($con));
    }
}

function update_mysql($data,$table,$condition)
{
    global $con;
    $id='';
    if(isset($data['id'])){
        $id=$data['id'];
        unset($data['id']);}
    $update_string=array();
    foreach($data as $key=>$value)
    {
        array_push($update_string,"`$key`='$value'");
    }
    $update_string=implode(",",$update_string);
    $query="update $table set $update_string where $condition";
    $updateQuery=mysqli_query($con,$query);
    if($updateQuery)
    {
        return array("success"=>"true","data"=>$id);
    }
    else
    {
        return array("success"=>"false","data"=>mysqli_error($con));
    }
}

function update_counter($field,$value,$table,$condition)
{
    global $con;
    $id="";
    $query="update $table set $field=$field+$value where $condition";
    $updateQuery=mysqli_query($con,$query);
    if($updateQuery)
    {
        return array("success"=>"true","data"=>$id);
    }
    else
    {
        return array("success"=>"false","data"=>mysqli_error($con));
    }
}

function Select_Some($fields,$table,$conditions)
{
    global $con;
    $query="Select $fields from $table where $conditions";
    $get_data=mysqli_query($con,$query);
    if($get_data)
    {
        return $get_data;
    }
    else
    {
        return array("success"=>"false","data"=>mysqli_error($con));
    }
}

function Select_All($fields,$table)
{
    global $con;
    $query="Select $fields from $table";
    $get_data=mysqli_query($con,$query);
    if($get_data)
    {
        return $get_data;
    }
    else
    {
        return array("success"=>"false","data"=>mysqli_error($con));
    }
}

function get_ids_to_delete($table,$condition)
{
    global $con;
    $ids=array();
    $get_ids=mysqli_query($con,"select id from $table where $condition");
    while($fet_ids=mysqli_fetch_assoc($get_ids))
    {
        array_push($ids,$fet_ids['id']);
    }
    return $ids;
}

function delete_mysql($table,$condition)
{
    global $con;
    $query="delete from $table where $condition";
    $delete=mysqli_query($con,$query);
    if($delete)
    {
        return array("success"=>"true","data"=>$query);
    }
    else
    {
        return array("success"=>"false","data"=>mysqli_error($con));
    }
}

function select_record($tbl, $select = '*', $join = '', $where = '', $group_by = '', $start = 0, $limit = 'ALL', $order_by = '') {
    global $con;
    $query = "Select {$select} from `{$tbl}`";
    if (!empty($join)) {
        $query .= " $join";
    }
    if (!empty($where)) {
        $query .= " WHERE {$where}";
    }
    if (!empty($group_by)) {
        $query .= " GROUP BY {$group_by}";
    }
    if (!empty($order_by)) {
        $query .= " ORDER BY {$order_by}";
    }
    if ($limit != 'ALL') {
        $query .= " LIMIT {$start}, {$limit}";
    }
    $get_data = mysqli_query($con, $query);
    if ($get_data) {
        if (mysqli_num_rows($get_data) > 0) {
            return array("success" => "true", "data" => $get_data);
        } else {
            return array("success" => "false", "data" => "No data available");
        }
    } else {
        return array("success" => "false", "data" => mysqli_error($con));
    }
}

function sql_query($query) {
    global $con;
    $get_data = mysqli_query($con, $query);
    if($get_data) {
        return array("success" => "true","data" => $get_data);
    }
    else {
        return array("success"=>"false","data" => mysqli_error($con));
    }
}

function select_records($tbl, $select = '*', $join = '', $where = '', $group_by = '', $start = 0, $limit = 'ALL', $order_by = '') {
    global $con;
    $courses=array();
    $query = "Select {$select} from `{$tbl}`";
    if(!empty($join)) {
        $query .= " $join";
    }
    if(!empty($where)) {
        $query .= " WHERE {$where}";
    }
    if(!empty($group_by)) {
        $query .= " GROUP BY {$group_by}";
    }
    if(!empty($order_by)) {
        $query .= " ORDER BY {$order_by}";
    }
    if($limit != 'ALL') {
        $query .= " LIMIT {$start}, {$limit}";
    }
    $get_data = mysqli_query($con, $query);
    if($get_data) {
        return array("success" => "true","data" => $get_data);
    }
    else {
        return array("success"=>"false","data" => mysqli_error($con));
    }
}

function direct_mysql_query($query) {
    global $con;
    $result = mysqli_query($con, $query);
    if ($result) {
        return array("success" => "true", "data" => $result);
    } else {
        return array("success" => "false", "data" => mysqli_error($con));
    }
}

?>