<?php 

$HOMEWORK_PROBLEM = "8";

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
$stud1email = $row['email'];
if ($stud1realpid != $student1pid)
{
   die("Student 1 Name and PID do not match.");
}
$query = sprintf("SELECT * FROM reviews WHERE `homework` = %s AND `studentId` = %s;", $HOMEWORK_PROBLEM, $stud1id);
$result = mysql_query($query) or die(mysql_error());
if (mysql_num_rows($result) > 0)
{
   die($student1name." has already retrieved the proof(s) for this homework!");
}

if ($have_student2 == true)
{
   $query = sprintf("SELECT * FROM students WHERE `name` = '%s';", mysql_real_escape_string($student2name));
   $result = mysql_query($query) or die(mysql_error());
   $row = mysql_fetch_array($result);
   $stud2realpid = $row['pid'];
   $stud2id = $row['id'];
   $stud2email = $row['email'];
   if ($stud2realpid != $student2pid)
   {
      die("Student 2 Name and PID do not match.");
   }
   $query = sprintf("SELECT * FROM reviews WHERE `homework` = %s AND `studentId` = %s;", $HOMEWORK_PROBLEM, $stud2id);
   $result = mysql_query($query) or die(mysql_error());
   if (mysql_num_rows($result) > 0)
   {
      die($student2name." has already retrieved the proof(s) for this homework!");
   }
}

if ($have_student3 == true)
{
   $query = sprintf("SELECT * FROM students WHERE `name` = '%s';", mysql_real_escape_string($student3name));
   $result = mysql_query($query) or die(mysql_error());
   $row = mysql_fetch_array($result);
   $stud3realpid = $row['pid'];
   $stud3id = $row['id'];
   $stud3email = $row['email'];
   if ($stud3realpid != $student3pid)
   {
      die("Student 3 Name and PID do not match.");
   }
   $query = sprintf("SELECT * FROM reviews WHERE `homework` = %s AND `studentId` = %s;", $HOMEWORK_PROBLEM, $stud3id);
   $result = mysql_query($query) or die(mysql_error());
   if (mysql_num_rows($result) > 0)
   {
      die($student3name." has already retrieved the proof(s) for this homework!");
   }
}

// Pick files!
$query = sprintf("
select f.id, f.path from files f
inner join students stud
inner join solutions sol
on sol.fileId = f.id
and stud.id = sol.studentId
where sol.homework = %s
and stud.id != %s", $HOMEWORK_PROBLEM, $stud1id);

if ($have_student2 == true)
{
   $query = $query." and stud.id != ".$stud2id;
}

if ($have_student3 == true)
{
   $query = $query." and stud.id != ".$stud3id;
}

$query = $query.";";
$result = mysql_query($query) or die(mysql_error());
$rows = mysql_num_rows($result);
$row_to_pick1 = rand(0, $rows - 1);
$row_to_pick2 = $row_to_pick1;
while ($row_to_pick2 == $row_to_pick1)
{
   $row_to_pick2 = rand(0, $rows - 1);
}
$file1id = mysql_result($result, $row_to_pick1, 'id');
$file2id = mysql_result($result, $row_to_pick2, 'id');
$file1path = mysql_result($result, $row_to_pick1, 'path');
$file2path = mysql_result($result, $row_to_pick2, 'path');


// Store info on who retrieved what

$query = sprintf("INSERT INTO reviews (studentId, homework, fileId) VALUES (%s, %s, %s);",
                 $stud1id, $HOMEWORK_PROBLEM, $file1id);
mysql_query($query) or die(mysql_error());
$query = sprintf("INSERT INTO reviews (studentId, homework, fileId) VALUES (%s, %s, %s);",
                 $stud1id, $HOMEWORK_PROBLEM, $file2id);
mysql_query($query) or die(mysql_error());

if ($have_student2 == true)
{
   $query = sprintf("INSERT INTO reviews (studentId, homework, fileId) VALUES (%s, %s, %s);",
                    $stud2id, $HOMEWORK_PROBLEM, $file1id);
   mysql_query($query) or die(mysql_error());
   $query = sprintf("INSERT INTO reviews (studentId, homework, fileId) VALUES (%s, %s, %s);",
                    $stud2id, $HOMEWORK_PROBLEM, $file2id);
   mysql_query($query) or die(mysql_error());
}

if ($have_student3 == true)
{
   $query = sprintf("INSERT INTO reviews (studentId, homework, fileId) VALUES (%s, %s, %s);",
                    $stud3id, $HOMEWORK_PROBLEM, $file1id);
   mysql_query($query) or die(mysql_error());
   $query = sprintf("INSERT INTO reviews (studentId, homework, fileId) VALUES (%s, %s, %s);",
                    $stud3id, $HOMEWORK_PROBLEM, $file2id);
   mysql_query($query) or die(mysql_error());
}

$zipfile = md5(rand() * time());
$fullPath = "/tmp/".$zipfile.".zip";
shell_exec(sprintf("zip -rj %s %s %s", $fullPath, $file1path, $file2path)) or die("Couldn't package the proofs for you. Please contact skrstic@cs.ucsd.edu");

if ($fd = fopen ($fullPath, "r")) {
   $fsize = filesize($fullPath);
   $path_parts = pathinfo($fullPath);
   $ext = strtolower($path_parts["extension"]);
   switch ($ext) {
      case "pdf":
         header("Content-type: application/pdf"); // add here more headers for diff. extensions
         header("Content-Disposition: attachment; filename=\"".$path_parts["basename"]."\""); // use 'attachment' to force a download
         break;
      default;
      header("Content-type: application/octet-stream");
      header("Content-Disposition: filename=\"".$path_parts["basename"]."\"");
   }
   header("Content-length: $fsize");
   header("Cache-control: private"); //use this to open files directly
   while(!feof($fd)) {
      $buffer = fread($fd, 2048);
      echo $buffer;
   }
}
fclose ($fd);

?> 