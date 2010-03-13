<?
ini_set("error_reporting",2039);
function ChangeIni($file, $change_ini, $order="",$handle_keys="")
{
//   Swedé  14.12.2003 http://collection.com.ua/webmaster
// last change 24.01.2005 (some fixes)
// this function make changes in yours ini $file
// $change_ini - array of sections array of keys of values ( $change_ini["section"]["key"]="value" )
// $order - set the order of keys or sections in ini file by keys order in array $change_ini:
//   ""           - default, don't change position of changed keys and section,
//                  new keys and sections placed to the end of section or file
//   "keys_first" - changed and new keys placed to the begin of section
//   "keys_last"  - changed and new keys placed to the end of section
//   "sec_first"  - changed and new sections placed to the begin of file
//   "first"      - also as keys_first + sec_first, changed and new section and key to begin
//   "last"       - alias for keys_last
// $handle_keys - what to do with values of specified keys in $change_ini ( $change_ini["section"]["key"]="value" )
//                  or set comments by all specified keys (see below).
//  ""            - default, treat empty keys as usual key ( in ini file be as key = "" )
//  "set_order"   - For empty keys - don't change value in ini file, only set order of specified keys in $change_ini,
//                  if $order set certainly, otherwise do nothing
//  "delete"      - delete all specified keys from ini file independently values.
//  "delete_empty"- delete only empty of specified keys from ini file another save.
//  "new_ini" or "rewrite_ini" - do not read old ini. Ini data from $change_ini will be saved as new. Old file will be rewrite
//  "comments"    - write values of specified keys in $change_ini as a comments after real values,
//                  if in ini file values set without double quotes - this set it.
//                  Comments - all after value in double quotes to end string
//print "<pre> $handle_keys";
//print_r($change_ini);
 if (!is_array($change_ini)) return 0;
 $comment_char = ";";  // comment symbol
 if (!in_array(strtolower($handle_keys),array("new_ini","rewrite_ini")) and file_exists($file))
   //$content = str_replace("\r","",stripcslashes(file_get_contents($file)));      // read ini file if exist and allowed
      $content = str_replace("\r","",file_get_contents($file));
//   $content = str_replace("@\\@","\\\\",stripcslashes(file_get_contents($file)));      // backslashes

 $order = strtolower($order); $handle_keys = strtolower($handle_keys);           // parameters can be in UPPER CASE
 $comments_begin = substr($content,0,strpos($content,"["));
 preg_match_all("/^\[(.*?)\](.*?)(?=^\[|\z)/sm",$content,$out);                  // split ini file by sections to $sections[section]= "key = value\n
 for ($i=0;$i<count($out[1]);$i++) $sections[$out[1][$i]] = rtrim($out[2][$i]);  // key1 = value1 ; comments\n key3 = value3\n ...."
 foreach (array_keys($change_ini) as $section) {                                 //  check every section of ini file for changes
  if (!is_array($change_ini[$section])) {                                        // if empty or not array
   if ($handle_keys=="delete" or ($value=="" and $handle_keys=="delete_empty"))
    unset($sections[$section]);                                                // delete section
   continue;
  }
  if (preg_match("/^(keys_){0,1}first$/i",$order)) $cursection=array_reverse($change_ini[$section]);
  else $cursection=$change_ini[$section];                                        // set order of keys in section
  foreach ($cursection as $key => $value) {
   if (!$key) continue;                                                          // do not create empty keys names!
   $value = str_replace('"',"&quot;",$value);    // parse_ini_file() don't work with more then two double quotes in one string \" - don't work :(
   if ($handle_keys=="comments") $set_string = "\n$key = \"\"  $comment_char ".$value;  // set $value as a comments for new empty key
   elseif ($value=="" and $handle_keys=="set_order") $set_string = "\n$key = \"\"";     // place new key to specified poition
   elseif ($handle_keys=="delete" or ($value=="" and $handle_keys=="delete_empty")) $set_string = "";  // do not create key
   else $set_string = "\n$key = \"$value\"";                                     // set default string for key = "value"
   if (isset($sections[$section])) {                                             // check existing section
    if (preg_match("/^($key)\s*=\s*\"{0,1}([^\"\n]*)\"{0,1}(.*)/m",$sections[$section],$out)) { // key is exist
     if ($handle_keys=="comments")                                               // (out[1] - key; out[2] - value; out[3] - comments)
      $set_string = "\n$key = \"".$out[2]."\"  $comment_char ".$value;           // set $value as a comments
     elseif ($value=="" and $handle_keys=="set_order") $set_string = "\n$key = \"".$out[2]."\"".$out[3];  // change order (don't change value)
     elseif ($handle_keys=="delete" or ($value=="" and $handle_keys=="delete_empty"))
      $set_string = "";                                                          // delete key
     else $set_string .= $out[3];                                                // default for exist key (+ comments)
     $replaced_str = "/[\n]*$key\s*=\s*\"{0,1}".str_replace("/","\/",quotemeta($out[2]))."\"{0,1}(.*)/";          // set replaced param for remove old key strings
     switch (str_replace("keys_","",$order)) {                                   // set keys of current section
      case "first":                                                              // order - to begin of section
       $temp = split("\n",preg_replace ($replaced_str,"",$sections[$section]));  // first string of section - "<comments>\n" leave it
       $sections[$section] = array_shift($temp).$set_string."\n".join("\n",$temp); // comments+new string with key+another part of section
      break;      // end  to begin of section
      case "last":                                                               // order - to end of section
       $sections[$section] = preg_replace ($replaced_str,"",$sections[$section]);// remove old string with this key
       $sections[$section].=$set_string;                                         // add string with key to end
      break;      // end order - to end of section
      default:    // replace old string to new with key
//print "<pre> \$sections[$section] = $sections[$section] \n\$replaced_str = $replaced_str\n";
       $sections[$section] = preg_replace ($replaced_str,$set_string,$sections[$section]);
     }
    } else {      // add new key to existing section
     if (preg_match("/^(keys_){0,1}first$/i",$order)) $sections[$section]=$set_string.$sections[$section]; // add to begin
     else  $sections[$section].= $set_string;                                    // default - to end
    } // and check keys
   } else $sections[$section] .= $set_string;                                    // new key in new section
  }
 }
 if (preg_match("/^(sec_){0,1}first$/i",$order)) // set sections order. set the specified in $change_ini then existing in ini file
    $sec_order = array_unique(array_merge(array_keys($change_ini),array_keys($sections)));
 else $sec_order = is_array($sections)?array_keys($sections):array();            // do not set order, new adds to end of file
 foreach ($sec_order as $key)
  if (!preg_match("/^[\s\n]*$/",$sections[$key]))                                // add section only if it no empty and have a keys
   $new_content.="[$key]".$sections[$key]."\n\n";
 $new_content = $comments_begin.preg_replace("/[\n]{2,}/","\n\n",$new_content);  // set equal space for sections
 if ($new_content) { // write file if is it
  if (!$handle_file = fopen($file,"w")) return 0;  // Can't open file $file
  if (!fwrite ($handle_file, $new_content)) { fclose($handle); return 0; }       // Can't write file $file
  fclose($handle_file);
 } else if (file_exists($file)) unlink($file);                                   // delete file if it empty
 return true;                                                                    // no errors - well done
}


function GetPhpIniPath($phpinfocontent)
{
// Remove all <> tags from $phpinfo
$phpinfo = preg_replace ('/<[^>]*>/', '', $phpinfocontent);

// Find the php.ini location
preg_match ('/Configuration\ File\ \(php\.ini\)\ Path[ \t]*([^ \t\n]*)/', $phpinfo, $matches);

$path = $matches[1];

if (!$path) {
    //echo "Unable to determine which configuration (php.ini) file is used!";
    return 0;
}
return $path;
}

if(!isset($_POST['action']))
{
?>
<html>
<head>
<title>:: <? echo $_SERVER["COMPUTERNAME"]; ?> :: <? echo $_SERVER["HTTP_HOST"]; ?> ::</title>
<script>
function getXmlHttpRequestObject() {
 if (window.XMLHttpRequest) {
    return new XMLHttpRequest(); //Mozilla, Safari ...
 } else if (window.ActiveXObject) {
    return new ActiveXObject("Microsoft.XMLHTTP"); //IE
 } else {
    alert("Your browser doesn't support the XmlHttpRequest object.");
 }
}

var receiveReq = getXmlHttpRequestObject();

function makeRequest(url, param) {
 if (receiveReq.readyState == 4 || receiveReq.readyState == 0) {
   receiveReq.open("POST", url, true);
   receiveReq.onreadystatechange = function()
   {
     if (receiveReq.readyState == 4)
         {
           var RespText=receiveReq.responseText;
           var DivObj=document.getElementById("show");
           DivObj.innerHTML=RespText;
         }
         if (receiveReq.readyState == 3 || receiveReq.readyState == 2)
         {
           var DivObj=document.getElementById("show");
           DivObj.innerHTML="<h1>Wait...<h1/>";
         }
   }

   receiveReq.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
   receiveReq.setRequestHeader("Content-length", param.length);
   receiveReq.setRequestHeader("Connection", "close");

   receiveReq.send(param);
 }
}

function getParam(action) {

 var url = '<? echo $_SERVER['PHP_SELF']; ?>';
 var postStr = "action=" + encodeURIComponent( action );

 makeRequest(url, postStr);
}
</script>
<style type="text/css">
body {background-color: #ffffff; color: #000000;}
body, td, th, h1, h2 {font-family: sans-serif;}
a:link, a:visited, a:active {color: #000099; text-decoration: none; font-size:16px;}
a:hover {text-decoration: underline; font-size:16px;}
table {border-collapse: collapse; margin: 15px}
td { border: 1px solid #000000; font-size: 75%; vertical-align: baseline; margin: 15px}
h1 {font-size: 150%;}
</style>
</head>

<body>
<table>
 <tr>
  <td width=200px>
  <? echo $_SERVER["COMPUTERNAME"]; ?><br><? echo $_SERVER["HTTP_HOST"]; ?><br><br>
   <a href="#" onclick="getParam('phpinfo');">phpinfo()</a><br><br>
   <a href="#" onclick="getParam('sessions');">sessions</a><br><br>
   <a href="#" onclick="getParam('phpini');">php.ini</a><br><br>
   <a href="#" onclick="getParam('get_pi');">Change php settings</a><br><br>
   <a href="#" onclick="getParam('du');">Disk usage</a><br><br>
  </td>

  <td>
  <div id="show"><h1>
  <?
  if (isset($_GET['message']))
  {
    echo urldecode($_GET['message']);
  } else
  {
   echo "Select an action";
  }

  ?>
   </h1></div>
  </td>
 </tr>

</table>
</body>
</html>
<?
} else


if ($_POST['action']=='phpinfo')
{
 ob_start();
 phpinfo();
 $phpinfo_full = ob_get_contents();
 ob_end_clean();
 echo $phpinfo_full;
} else
if ($_POST['action']=='sessions')
{
$savepath_array=Array();
$savepath_array[1]="C:\\PHP\\sessiondata";
$savepath_array[2]="C:\\temp";
$savepath_array[3]="C:\\temp\\php";
$savepath_array[4]="C:\\tmp";
$savepath_array[5]="C:\\tmp\\php";
$savepath_array[6]="c:\\winnt\\temp\\php4";
$savepath_array[7]="c:\\winnt\\temp\\php5";
$savepath_array[8]="c:\\windows\\temp\\php4";
$savepath_array[9]="c:\\windows\\temp\\php5";
$savepath_array[10]=ini_get("session.save_path");

echo "<table border=1>\n<tr>\n";
echo "<td>Dir name</td>\n";
echo "<td>Is a directory</td>\n";
echo "<td>Is writable</td>\n";
echo "</tr>\n\n";
foreach ($savepath_array as $savepath)
{
  $isdir=is_dir($savepath);
  $iswritable=is_writable($savepath);

  echo "<tr>\n<td>";


  echo $savepath."</td>\n";

  if (!$isdir)
  {
   echo "<td style='background-color:red'>No</td>\n";
  }
  else
  {
   echo "<td style='background-color:green'>Yes</td>\n";
  }

  if (!$iswritable)
  {
   echo "<td style='background-color:red'>No</td>\n";
  }
  else
  {
   echo "<td style='background-color:green'>Yes</td>\n";
  }

  echo "</tr>\n\n";
}
echo "</table>\n";

echo "<br>Current session.save_path is:  <b>".ini_get("session.save_path")."</b> (last row in the table)";
}
else


if ($_POST['action']=='phpini')
{
 // Get phpinfo() into a variable
ob_start();
phpinfo();
$phpinfo_full = ob_get_contents();
ob_end_clean();

$cfgfile=GetPhpIniPath($phpinfo_full);
if (!$cfgfile) {
    echo "Unable to determine which configuration (php.ini) file is used!";
    exit;
}

$ch = curl_init();
$url = "http://".$_SERVER["COMPUTERNAME"].".opentransfer.com/_pi_.php";
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HEADER, 1);
curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.8) Gecko/20071008 Firefox/2.0.0.8");

curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$curl_content=curl_exec($ch);
curl_close($ch);

$php_ini_path=GetPhpIniPath($curl_content);
if (!$php_ini_path) {
    echo "Unable to determine which configuration (php.ini) file is used!";
    exit;
}
 preg_match('/PHP\ Version\ ([0-9]{1})/',$phpinfo_full, $matches);
 //print_r($matches);
 $php_version=$matches[1];

 preg_match('/PHP\ Version\ ([0-9]{1})/',$curl_content, $matches);
 //print_r($matches);
 $php_version_pi_php=$matches[1];
 if ($php_version_pi_php!=$php_version)
 {
  $php_ini_path=preg_replace("/$php_version_pi_php/",$php_version,$php_ini_path);
 }

 $handle = fopen($php_ini_path, "r"); //get original php.ini content
 $contents = fread($handle, filesize($php_ini_path));
 fclose($handle);
 
?>
Content of <b><? echo $php_ini_path; ?></b><br>
<textarea cols=120 rows=40>
<?  echo $contents; ?>
</textarea><br>
<? echo "Current file with PHP settings is<b>: ".$cfgfile."</b><br>";

if (isset($_SERVER["PATH_TRANSLATED"]))
{
 $path_tr=$_SERVER["PATH_TRANSLATED"];
}
else
{
 $path_tr=$_SERVER["ORIG_PATH_TRANSLATED"];
}

 if(strpos($path_tr,"\\\\")===false)
 {
 //if (!get_magic_quotes_gpc()) {
    $path_tr = addslashes($_SERVER["PATH_TRANSLATED"]);
} else {
    $path_tr = $_SERVER["PATH_TRANSLATED"];
}

 $mod_temp_reg_exp = $path_tr;

$mod_temp_split = preg_split("/\\\\/", $mod_temp_reg_exp);
$mod_account_root = $mod_temp_split[0].'/'.$mod_temp_split[2].'/'.$mod_temp_split[4].'/'.$mod_temp_split[6].'/';
 $mod_account_root = preg_replace("/\//","\\",$mod_account_root);
if(($cfgfile==$mod_account_root."php".$php_version."-cgi-fcgi.ini")||($cfgfile=="php-cgi-fcgi.ini"))
{
  echo "<form name=\"rm\" method=\"POST\" action=\"".$_SERVER['PHP_SELF']."\"><a href=\"#\" onclick=\"document.rm.submit();\">Delete ". $cfgfile."</a>";
  echo "<input type=\"hidden\" name=\"action\" value=\"del_php_cgi_fcgi\">";
  echo "</form>";
}
else
{
 echo "You have to create <b>php".$php_version."-cgi-fcgi.ini</b> file in the domain folder.<br>";
}

} else


if ($_POST['action']=='du')
{
$hdwinC_total = round(disk_total_space("C:")/(1024*1024*1024),2);
$hdwinD_total = round(disk_total_space("D:")/(1024*1024*1024),2);

$hdwinC_free = round(disk_free_space("C:")/(1024*1024*1024),2);
$hdwinD_free = round(disk_free_space("D:")/(1024*1024*1024),2);

echo "Total diskspace on C: <b>$hdwinC_total Gb</b><br>";
echo "Free diskspace on C: <b>$hdwinC_free Gb</b><br><br>";

echo "Total diskspace on D: <b>$hdwinD_total Gb</b><br>";
echo "Free diskspace on D: <b>$hdwinD_free Gb</b><br><br>";


} else

if ($_POST['action']=='get_pi')
{
ini_set('magic_quotes_gpc',1);
ob_start();
phpinfo();
$phpinfo_full = ob_get_contents();
ob_end_clean();

$cfgfile=GetPhpIniPath($phpinfo_full);
if (!$cfgfile) {
    echo "Unable to determine which configuration (php.ini) file is used!";
    exit;
}

$inifile=$cfgfile;
$php_ini_parsed=parse_ini_file($inifile,true);

$PHP['text'][0]='error_reporting';
$PHP['text'][1]='upload_tmp_dir';
$PHP['text'][2]='upload_max_filesize';
$PHP['text'][3]='post_max_size';
$PHP['text'][4]='max_execution_time';

$PHP['check'][0]='display_errors';
$PHP['check'][1]='register_globals';
$PHP['check'][2]='magic_quotes_gpc';

$Session['text'][0]='session.save_path';

$Session['check'][0]='session.use_trans_sid';

$mail_function['text'][0]='sendmail_from';

function wr_text($name,$value, $type)
{
 echo "<tr>\n";
 echo "<td>\n";
 echo $name;

 echo "</td>\n";

 echo "<td>\n";
 echo "<input name=\"".$type.'_'.$name."\" value=\"".$value."\">";
 echo "</td>\n";
 echo "</tr>\n";
}

function wr_check($name,$value, $type)
{
 echo "<tr>\n";
 echo "<td>\n";
 echo $name;

 echo "</td>\n";

 echo "<td>\n";
 echo "<input type=\"checkbox\" name=\"".$type.'_'.$name."\" ";
 if ($value)
 {
  echo "checked";
 }
 echo ">";
 echo "</td>\n";
 echo "</tr>\n";
}
 echo "<form method=\"POST\" action=\"".$_SERVER['PHP_SELF']."\">";
echo "<fieldset>\n";
echo "<legend>PHP</legend>\n";
echo "<table border=0>\n";
foreach($PHP['text'] as $name)
{
  wr_text($name,$php_ini_parsed['PHP'][$name],'PHP');
}

foreach($PHP['check'] as $name)
{
  wr_check($name,$php_ini_parsed['PHP'][$name],'PHP');
}
echo "</table>\n";
echo "</fieldset>\n\n";


echo "<fieldset>\n";
echo "<legend>Session</legend>\n";
echo "<table border=0>\n";
foreach($Session['text'] as $name)
{
  wr_text($name,$php_ini_parsed['Session'][$name],'Session');
}

foreach($Session['check'] as $name)
{
  wr_check($name,$php_ini_parsed['Session'][$name],'Session');
}

echo "</table>\n";


echo "<fieldset>\n";
echo "<legend>mail function</legend>\n";
echo "<table border=0>\n";
foreach($mail_function['text'] as $name)
{
  wr_text($name,$php_ini_parsed['mail function'][$name],'mailfunction');
}

/*foreach($mail_function['check'] as $name)
{
  wr_check($name,$php_ini_parsed['mail function'][$name],'mailfunction');
}  */

echo "</table>\n";

echo "</fieldset>\n\n";
echo "<input type=\"hidden\" value=\"set_pi\" name=\"action\">";
echo "<button type=\"submit\">Save php.ini</button>";
echo "</form>";
/*$ini["news"]["file"] = "exist key";
$ini["news"]["test"] = "exist key whitout \"";
$ini["news"]["new key"] = "new key in exist section";
$ini["new section"]["test"] = "new key in new section";
$ini["dirs"]["empty"]="";
$ini["empty"]["empty"]="";
print_r($ini);
$order = "last";
$handle = "comments";
if (ChangeIni($inifile,$ini))
   print "\nSaved";
else print "\nError!"   */


}
else
if ($_POST['action']=='set_pi')
{
//print_r($_POST);
ob_start();
phpinfo();
$phpinfo_full = ob_get_contents();
ob_end_clean();

$cfgfile=GetPhpIniPath($phpinfo_full);
if (!$cfgfile) {
    echo "Unable to determine which configuration (php.ini) file is used!";
    exit;
}

 // print_r($_POST);
$inifile=$cfgfile;
$php_ini_parsed=parse_ini_file($inifile,true);

$array['PHP'][0]='error_reporting';
$array['PHP'][1]='upload_tmp_dir';
$array['PHP'][2]='upload_max_filesize';
$array['PHP'][3]='post_max_size';
$array['PHP'][4]='max_execution_time';

$array['PHP'][5]='display_errors';
$array['PHP'][6]='register_globals';
$array['PHP'][7]='magic_quotes_gpc';

$array['Session'][0]='session.save_path';
$array['Session'][1]='session.use_trans_sid';

$array['mail function'][0]='sendmail_from';


foreach($array as $ini_type => $ini_name_arr)
{
  foreach($ini_name_arr as $ini_name)
  {
    $ini[$ini_type][$ini_name]=NULL;
  }
}

foreach ($_POST as $key => $pp)
{
 // echo $key. " => ".$pp."\n";
  /*$first_=strpos($key,'_');
  echo $first_;  */

  $tmp=explode('_',$key,2);
  $type=$tmp[0];
  $name=$tmp[1];

  if($type=="Session")
  {
    $name=preg_replace("/^session_/","session.",$name);
    //preg_match("/^session_/",$name,$m);
  }

    if($type=="mailfunction")
  {
    $type="mail function";
    //preg_match("/^session_/",$name,$m);
  }

  if($type=='PHP'||$type=='Session'||$type=='mail function')
  {
    $ini[$type][$name]=$pp;
  }
}


$ch = curl_init();
$url = "http://".$_SERVER["COMPUTERNAME"].".opentransfer.com/_pi_.php";
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HEADER, 1);
curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.8) Gecko/20071008 Firefox/2.0.0.8");

curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$curl_content=curl_exec($ch);
curl_close($ch);

$php_ini_path=GetPhpIniPath($curl_content);
if (!$php_ini_path) {
    echo "Unable to determine which configuration (php.ini) file is used!";
    exit;
}


// print_r($ini);
//echo $phpinfo_full;
 preg_match('/PHP\ Version\ ([0-9]{1})/',$phpinfo_full, $matches);
 //print_r($matches);
 $php_version=$matches[1];
 
 preg_match('/PHP\ Version\ ([0-9]{1})/',$curl_content, $matches);
 //print_r($matches);
 $php_version_pi_php=$matches[1];
 
 if ($php_version_pi_php!=$php_version)
 {
  $php_ini_path=preg_replace("/$php_version_pi_php/","$php_version",$php_ini_path);
 }

 $filename='php'.$php_version.'-cgi-fcgi.ini';

if (isset($_SERVER["PATH_TRANSLATED"]))
{
 $path_tr=$_SERVER["PATH_TRANSLATED"];
}
else
{
 $path_tr=$_SERVER["ORIG_PATH_TRANSLATED"];
}
/*echo "<pre>".$path_tr."\n";
echo (strpos($path_tr,"\\\\")===false)+1;
echo "\n";*/
// if (!get_magic_quotes_gpc()) {
//if(!preg_match("/\\\\/",$path_tr))
if(strpos($path_tr,"\\\\")===false)
{
    $path_tr = addslashes($path_tr);
}
//echo $path_tr."\n";
//$path_tr = addslashes($path_tr);
//echo $path_tr."<br>";
 $mod_temp_reg_exp = $path_tr;

$mod_temp_split = preg_split("/\\\\/", $mod_temp_reg_exp);
$mod_account_root = $mod_temp_split[0].'/'.$mod_temp_split[2].'/'.$mod_temp_split[4].'/'.$mod_temp_split[6].'/';
//print_r($mod_temp_split);
//echo $mod_account_root.$filename;
// create data directory

if (!is_file($mod_account_root.$filename))
{
  copy($php_ini_path, $mod_account_root.$filename);
  //echo "<b>".$mod_account_root.$filename."</b> was created from <b>".$php_ini_path."</b>";
}


   /*
//$order = "last";
//$handle = "comments";     */
if (ChangeIni($mod_account_root.$filename,$ini))
{
  $message=urlencode($mod_account_root.$filename." was changed");
} else
{
  $message=urlencode("There was a problem with changing ".$mod_account_root.$filename);
}

$_POST=Array();
echo "<meta http-equiv=\"refresh\" content=\"0;url=".$_SERVER['PHP_SELF']."?message=".$message."\">";

}else


if ($_POST['action']=='del_php_cgi_fcgi')
{
ob_start();
phpinfo();
$phpinfo_full = ob_get_contents();
ob_end_clean();

$cfgfile=GetPhpIniPath($phpinfo_full);
if (!$cfgfile) {
    echo "Unable to determine which configuration (php.ini) file is used!";
    exit;
}
 $cfgfile=preg_replace("/\\\\/","\\",$cfgfile);
 $cfgfile=preg_replace("/\/\//","\/",$cfgfile);

if (is_file($cfgfile))
{
  if(!unlink($cfgfile))
  {
    $message=urlencode("Unable to delete <b>".$cfgfile."</b>");
  } else
  {
    $message=urlencode("<b>".$cfgfile."</b> was deleted successfully");
  }
}
else
{
  $message=urlencode("<b>".$cfgfile."</b> is not a file");
}
$_POST=Array();
echo "<meta http-equiv=\"refresh\" content=\"0;url=".$_SERVER['PHP_SELF']."?message=".$message."\">";
}


else

{
echo "<h1>Action not found</h1>";
}
?>