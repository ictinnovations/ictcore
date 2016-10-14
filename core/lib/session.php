<?php
/* * ***************************************************************
 * Copyright © 2012 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 *             : Tahir Almas                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : info@ictinnovations.com                                  *
 * *************************************************************** */
// Database Session Handling Functions
// bond session management with following functions
session_set_save_handler('session_open', 'session_close', 'session_read', 'session_write', 'session_delete', 'session_gc');

/* not in use
  function session_open($path, $name) {
  return TRUE;
  } */

function session_close()
{
  //session_log("session_close");
  return TRUE;
}

function session_read($id)
{
  //session_log("session_read");
  session_write_close();
  $query = "SELECT data FROM transmission_session WHERE transmission_id='$id'";
  if (!$result = DB::query('transmission_session', $query)) {
    //session_log("MySQL error: " . mysql_error());
    return FALSE;
  }
  if (mysql_num_rows($result)) {
    $row = mysql_fetch_assoc($result);
    //session_log("session_read returned " . $row["data"]);
    return $row["data"];
  } else {
    //session_log("session_read found zero rows with id: $id");
    return "";
  }
}

function session_write($id, $data)
{
  global $_SESSION;

  //session_log("session_write");
  $sessionRs = DB::query('transmission_session', "SELECT count(*) FROM transmission_session s WHERE s.transmission_id = '$id'");
  $row = mysql_fetch_row($sessionRs);
  //session_log('Found ' . $row[0] . ' record(s).');

  if ($row[0] > 0) {
    $query = "UPDATE transmission_session SET data='%data%', time_start=UNIX_TIMESTAMP() WHERE transmission_id ='$id'";
  } else {
    $query = "INSERT INTO transmission_session (transmission_id, time_start, data) 
                VALUES ('$id', UNIX_TIMESTAMP(), '%data%')";
  }
  DB::query('transmission_session', $query, array('data' => $data));
  if (mysql_affected_rows()) {
    //session_log("session_write update affected " . mysql_affected_rows() . " rows with id: $id");
    return TRUE;
  }
}

function session_delete($id)
{
  //session_log("session_delete");
  $query = "DELETE FROM transmission_session where transmission_id='$id'";
  $result = DB::query('transmission_session', $query);
  if ($result) {
    $_SESSION = array();
    //session_log("MySQL query delete worked. " . mysql_affected_rows(). " row(s) deleted.");
    return TRUE;
  } else {
    //session_log("MySQL update error: " . mysql_error() . " with id: $id");
    return FALSE;
  }
}

function session_gc($life)
{
  //session_log("session_gc");
  $query = "DELETE FROM transmission_session WHERE time_start < " . (time() - $life);
  $result = DB::query('transmission_session', $query);
  if ($result) {
    //session_log("session_gc deleted " . mysql_affected_rows() . " rows.");
    return TRUE;
  } else {
    //session_log("session_gc error: " . mysql_error() . " with id: $id");
    return FALSE;
  }
}

function session_log($message)
{
  $file = fopen('/tmp/session.txt', "a");
  if ($file) {
    fwrite($file, gmdate("Y-m-d H:i:s ") . $message . "\n");
    fclose($file);
  }
}
