<?php 

$HOMEWORK_PROBLEM = "9";

// Check inputs for student Names and PIDs

$student1name = $_POST['student1'];
$student2name = $_POST['student2'];
$student3name = $_POST['student3'];
$student1pid = strtolower($_POST['student1pid']);
$student2pid = strtolower($_POST['student2pid']);
$student3pid = strtolower($_POST['student3pid']);
$have_student2 = false;
$have_student3 = false;

if (empty($student1name) || empty($student1pid))
{
   die("You have to enter Student 1 info!");
}

if ((empty($student2name) && !empty($student2pid)) || (!empty($student2name) && empty($student2pid)))
{
   die("Student 2 info is incomplete!");
}

if ((empty($student3name) && !empty($student3pid)) || (!empty($student3name) && empty($student3pid)))
{
   die("Student 3 info is incomplete!");
}

if (!empty($student2name))
{
   $have_student2 = true;
   if ($student1name == $student2name)
   {
      die("Students 1 and 2 cannot be the same.");
   }
}

if (!empty($student3name))
{
   $have_student3 = true;
   if ($student1name == $student3name)
   {
      die("Students 1 and 3 cannot be the same.");
   }
   if ($have_student2 && $student2name == $student3name)
   {
      die("Students 2 and 3 cannot be the same.");
   }
}


// Check input validity against the DB

mysql_connect("localhost", "testuser", "testpassword") or die(mysql_error());
mysql_select_db("assessimizer") or die(mysql_error());

$query = sprintf("SELECT * FROM students WHERE `name` = '%s';", mysql_real_escape_string($student1name));
$result = mysql_query($query) or die(mysql_error());
$row = mysql_fetch_array($result);
$stud1realpid = $row['pid'];
$stud1id = $row['id'];
if ($stud1realpid != $student1pid)
{
   die("Student 1 Name and PID do not match.");
}
$query = sprintf("SELECT * FROM solutions WHERE `homework` = %s AND `studentId` = %s;", $HOMEWORK_PROBLEM, $stud1id);
$result = mysql_query($query) or die(mysql_error());
if (mysql_num_rows($result) > 0)
{
   die($student1name." has already submitted homework ".$HOMEWORK_PROBLEM.".");
}

if ($have_student2 == true)
{
   $query = sprintf("SELECT * FROM students WHERE `name` = '%s';", mysql_real_escape_string($student2name));
   $result = mysql_query($query) or die(mysql_error());
   $row = mysql_fetch_array($result);
   $stud2realpid = $row['pid'];
   $stud2id = $row['id'];
   if ($stud2realpid != $student2pid)
   {
      die("Student 2 Name and PID do not match.");
   }
   $query = sprintf("SELECT * FROM solutions WHERE `homework` = %s AND `studentId` = %s;", $HOMEWORK_PROBLEM, $stud2id);
   $result = mysql_query($query) or die(mysql_error());
   if (mysql_num_rows($result) > 0)
   {
      die($student2name." has already submitted homework ".$HOMEWORK_PROBLEM.".");
   }
}

if ($have_student3 == true)
{
   $query = sprintf("SELECT * FROM students WHERE `name` = '%s';", mysql_real_escape_string($student3name));
   $result = mysql_query($query) or die(mysql_error());
   $row = mysql_fetch_array($result);
   $stud3realpid = $row['pid'];
   $stud3id = $row['id'];
   if ($stud3realpid != $student3pid)
   {
      die("Student 3 Name and PID do not match.");
   }
   $query = sprintf("SELECT * FROM solutions WHERE `homework` = %s AND `studentId` = %s;", $HOMEWORK_PROBLEM, $stud3id);
   $result = mysql_query($query) or die(mysql_error());
   if (mysql_num_rows($result) > 0)
   {
      die($student3name." has already submitted homework ".$HOMEWORK_PROBLEM.".");
   }
}

// Upload the file!

$MAX_FILE_SIZE = 5000000;
$newfilename = md5(rand() * time());
$ext = "";

if ((!empty($_FILES["uploadedfile"])) && ($_FILES['uploadedfile']['error'] == 0)) 
{
   $filename = strtolower(basename($_FILES['uploadedfile']['name']));
   $ext = substr($filename, strrpos($filename, '.') + 1);
   $uploaded_size = $_FILES['uploadedfile']['size'];
   $uploaded_type = $_FILES['uploadedfile']['type'];
   
   if ($uploaded_size > $MAX_FILE_SIZE)
   { 
      die("File is too large. Maximum file size is ".$MAX_FILE_SIZE." bytes.");
   } 
   
//   if ($uploaded_type != "application/pdf" && $uploaded_type != "application/download")
   if ($ext != "pdf")
   {
      die("Only PDF files are allowed.");
   } 
   
   $ext = ".".$ext;
   $newpath = "/var/assessimizer/".$HOMEWORK_PROBLEM."/".$newfilename.$ext;
   
   if (!move_uploaded_file($_FILES['uploadedfile']['tmp_name'], $newpath)) 
   { 
      die("Sorry, there was a problem uploading your file.<br/>Please contact <a href=mailto:skrstic@cs.ucsd.edu>Srdjan Krstic</a>");
   } 

   // Update DB
   $query = sprintf("INSERT INTO files (path) values ('%s');", $newpath);
   $result = mysql_query($query) or die(mysql_error());
   $fileid = mysql_insert_id();

   $query = sprintf("INSERT INTO solutions (homework, fileId, studentId) VALUES ('%s', %s, %s);",
                    $HOMEWORK_PROBLEM, $fileid, $stud1id);
   mysql_query($query) or die(mysql_error());

   if ($have_student2 == true)
   {
      $query = sprintf("INSERT INTO solutions (homework, fileId, studentId) VALUES ('%s', %s, %s);",
                       $HOMEWORK_PROBLEM, $fileid, $stud2id);
      mysql_query($query) or die(mysql_error());
   }

   if ($have_student3 == true)
   {
      $query = sprintf("INSERT INTO solutions (homework, fileId, studentId) VALUES ('%s', %s, %s);",
                       $HOMEWORK_PROBLEM, $fileid, $stud3id);
      mysql_query($query) or die(mysql_error());
   }
   
   echo "Upload successful!";
}
else
{
   die("No file chosen!");
}

?> 
