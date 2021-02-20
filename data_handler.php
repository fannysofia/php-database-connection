<!Doctype html>
<html>
<head>
<title>Public Forum</title>
<style type="text/css">
table, td, th {
    border: 1px solid purple;
    text-align: center;
}
th {
    text-align: center;
}
td {
    text-align: justify;
}
table {
    border-collapse:collapse;
}
table.center {
    margin-left:auto;
    margin-right:auto;
}

body {text-align:center;}
</style>
</head>
<body>
<?php
//This function tries to connect to the database server, seleect the
//specified database and create the table if it does not exist
function do_db_preparation($server, $user, $pswd, $db, $table, $create_table_query) {
    // Here we try to establish the connection
    // Here we use error control operator @ to suppress error and warning messages
    $link = @mysqli_connect($server, $user, $pswd);
    if ($link == FALSE) {
        echo mysqli_error() . "<br/>";
        return;
    }
    //This is the query to create the database if it does not exist
    $create_db_query = "create database if not exists " . $db;
    // Here we select a database.
    $result = mysqli_query($link, $create_db_query);
    if ($result == FALSE){
        echo mysqli_error($link);
        return;
    }
    // Here we try to select the database and if it fails, we exit the program.
    if (mysqli_select_db($link, $db) == FALSE) {
        echo mysqli_error($link) . "<br/>";
        return;
    }
    
    //Here we create the table if it does not exist
    $result = mysqli_query($link, $create_table_query);
    
    if ($result==FALSE){
        echo mysqli_error($link) . "<br/>";
        return;
    }
    
    //Here we return the link to the database
    return $link;
}
function insert_data($link, $table, $user, $comment){
    //$id
    //Here we use mysqli_real_escape_string() funtion to check that given data do not include
    //any invalid character and escape them taking into account the current character set of the connection.
    //$checked_id=mysqli_real_escape_string($link, $id);
    $checked_name=mysqli_real_escape_string($link, $user);
    $checked_comment=mysqli_real_escape_string($link, $comment);
    
    //Here we set the time zone to Finland
    date_default_timezone_set('Europe/Helsinki');
    
    //Here we get the current date and time and format it as 2019-12-13 10:30:15
    $date_time = date('Y.m.d H:i:s', time());
    
    //Here we define a new insert query string with each id, name and price in arrays defined above
    $insert_query = "insert into $table(date_time, user, comment) values('$date_time', '$checked_name', '$checked_comment')";
    
    //Here we execute the insert query
    $result = mysqli_query($link, $insert_query);
    if ($result == FALSE) {
        
        echo mysqli_error($link) . "<br/>";
        echo "Couldn't insert data into $table <br/>";
        
    } else {
        
        //Here we inform the results of insert query.
        echo mysqli_affected_rows($link) . " row(s) updated in table " . $table . ".<br/>";
        //echo "mysqli_info(): " . mysqli_info($link) . "<br/>";
    }
}
function read_data($link, $select_query){
    //Here we define $table_head variable
    $table_head="<table class='center'><tr><th>Date and Time</th><th>Name</th><th>comment</th></tr>";

    
    $result=mysqli_query($link, $select_query);

    if($result == FALSE) {
        echo "query select $select_query <br/>";
        echo "result $result <br/>";
        echo mysqli_error($link) . "<br/>";
    } else {
        echo "<div style='text-align:center;'>";
        echo $table_head;
        //Here we iterate automathically through all rows
        while(($row=mysqli_fetch_row($result))) {
            echo "<tr><td>{$row[0]}</td><td>{$row[1]}</td><td>{$row[2]}</td></tr>";
        }
        
        echo "</table>";
        echo "</div>";
        
    }
    
    //Here we call free memory reserved for $result variable.
    mysqli_free_result($result) ;
    
}
// Here we include data in db_config.php file.
include_once '../config/db_config.php';
// Here we create a new table in the database.
$create_table_query = "create table if not exists " . $table . " (date_time DATETIME, user VARCHAR(20), comment VARCHAR(300))";

//This is the SQL query to seelect all data from the table sorted in
//descending order according to the value of date_time column
$select_query="select * from " . $table . " ORDER BY date_time DESC";
if(isset($_POST["submit"]) && !empty($_POST["user"]) && !empty($_POST["comment"])){
    $link=do_db_preparation($server, $user, $pswd, $db, $table, $create_table_query);
    insert_data($link, $table, $_POST["user"], $_POST["comment"]);
    
    echo "<hr>";
    echo "<div style='text-align:center;'>";
    echo "Available data:<br>";
    
    read_data($link, $select_query);
    
    echo "</div>";
    //Here we close the dbConnnection.
    mysqli_close($link);
    
    
    
    
} else if(isset($_POST["submit"]) && !( !empty($_POST["user"]) && !empty($_POST["comment"]))){
    
    echo "<mark>Some fields were empty!</mark><br>";
}
if(isset($_POST["search_text"]) && !empty($_POST["search"])){
    $link=do_db_preparation($server, $user, $pswd, $db, $table, $create_table_query);
    
    echo "<hr>";
    echo "<div style='text-align:center;'>";
    echo "Search results for <mark>" . $_POST["search"] . "</mark>:<br>";
    
    //Here we check whether the search is exact or not and write the proper SQL query
    if(empty($_POST["exact_search"]))
        //This is the SQL query to select data from the table based on the approximate value of name column, sorted in
        //descending order according to the value of date_time column
        $select_query="select * from " . $table . " where user OR comment like '%" . $_POST["search"] . "%' ORDER BY date_time DESC";
        else
            //This is the SQL query to select data from the table based on the exact value of name column, sorted in
            //descending order according to the value of date_time column
            $select_query="select * from " . $table . " where user OR comment like '" . $_POST["search"] . "' ORDER BY date_time DESC";
            //echo $select_query. "</br>";
            read_data($link, $select_query);
            
            echo "</div>";
            
            //Here we close the dbConnnection.
            mysqli_close($link);
            
            
} else if(isset($_POST["search_text"]) && empty($_POST["search"])){
    
    echo "<mark>Search field was empty!</mark><br>";
}
?>
<hr>
<div style='text-align: center;'> <a href='../index.html'>Back</a></div>
</body>
</html>