<?php
// Prevent multiple inclusions
if (!defined('DB_CONFIG_INCLUDED')) {
    define('DB_CONFIG_INCLUDED', true);

    $hname = 'localhost';
    $uname = 'root';
    $password = '';
    $db_name = 'lcrwebsite';

    $con = mysqli_connect($hname, $uname, $password, $db_name);

    if (!$con) {
        die("Connection failed to Database: " . mysqli_connect_error());
    }

    function filteration($data)
    {
        // Handle single values (strings)
        if (is_string($data)) {
            $data = trim($data);
            $data = stripcslashes($data);
            $data = htmlspecialchars($data);
            $data = strip_tags($data);
            return $data;
        }
        
        // Handle arrays
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $value = trim($value);
                $value = stripcslashes($value);
                $value = htmlspecialchars($value);
                $value = strip_tags($value);
                $data[$key] = $value;
            }
            return $data;
        }
        
        // Return as-is for other types
        return $data;
    }


function selectAll($table)
{
    $con = $GLOBALS['con'];
    $res = mysqli_query($con, "SELECT * FROM $table");
    return $res;
}

function select($sql, $values, $datatypes)
{
    $con = $GLOBALS['con'];
    if ($stmt = mysqli_prepare($con, $sql)) {
        // Only bind parameters if there are values and datatypes
        if (!empty($values) && !empty($datatypes)) {
            mysqli_stmt_bind_param($stmt, $datatypes, ...$values);
        }
        if (mysqli_stmt_execute($stmt)) {
            $res =  mysqli_stmt_get_result($stmt);
            mysqli_stmt_close($stmt);
            return $res;
        } else {
            die("Query not be executed - Select");
        }
    } else {
        die("Query not be prepared - Select");
    }
}

function update($sql, $values, $datatypes)
{
    $con = $GLOBALS['con'];
    if ($stmt = mysqli_prepare($con, $sql)) {
        mysqli_stmt_bind_param($stmt, $datatypes, ...$values);
        if (mysqli_stmt_execute($stmt)) {
            $res =  mysqli_stmt_affected_rows($stmt);
            mysqli_stmt_close($stmt);
            return $res;
        } else {
            mysqli_stmt_close($stmt);
            die("Query not be executed - Update");
        }
    } else {
        die("Query not be prepared - Update");
    }
}

function insert($sql, $values, $datatypes)
{
    $con = $GLOBALS['con'];
    if ($stmt = mysqli_prepare($con, $sql)) {
        mysqli_stmt_bind_param($stmt, $datatypes, ...$values);
        if (mysqli_stmt_execute($stmt)) {
            $res =  mysqli_stmt_affected_rows($stmt);
            mysqli_stmt_close($stmt);
            return $res;
        } else {
            die("Query not be executed - INSERTED");
        }
    } else {
        die("Query not be prepared - Insert");
    }
}

function delete($sql, $values, $datatypes)
{
    $con = $GLOBALS['con'];
    if ($stmt = mysqli_prepare($con, $sql)) {
        mysqli_stmt_bind_param($stmt, $datatypes, ...$values);
        if (mysqli_stmt_execute($stmt)) {
            $res =  mysqli_stmt_affected_rows($stmt);
            mysqli_stmt_close($stmt);
            return $res;
        } else {
            mysqli_stmt_close($stmt);
            die("Query not be executed - delete");
        }
    } else {
        die("Query not be prepared - delete");
    }
}

} // End of include guard
?>
