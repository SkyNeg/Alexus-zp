<?php 

// Get phpinfo() into a variable 
ob_start(); 
phpinfo(); 
$phpinfo_full = ob_get_contents(); 
ob_end_clean(); 

// Remove all <> tags from $phpinfo 
$phpinfo = preg_replace ('/<[^>]*>/', '', $phpinfo_full); 

// Find the php.ini location 
preg_match ('/Configuration\ File\ \(php\.ini\)\ Path[ \t]*([^ \t\n]*)/', $phpinfo, $matches); 
$cfgfile = $matches[1]; 
if (!$cfgfile) { 
    echo "Unable to determine which configuration (php.ini) file is used!"; 
    exit; 
} 

// Read php.ini into $phpini 
$phpini = file_get_contents('/usr/local/Zend/etc/php.ini'); 

// Get the current value for upload_tmp_dir 
$utd_now = ini_get('upload_tmp_dir'); 

// Get the current value for session.save_path 
$ssp_now = ini_get('session.save_path'); 

// Set table cell properties 
$cfgfile_heading = 'Configuration File (php.ini)'; 
$cfgfile_color = '#FFFFFF'; 
$cfgfile_value = $cfgfile; 
if (($cfgfile !== '/usr/local/lib/php-4.4.2/lib/php.ini') && ($cfgfile !== '/usr/local/lib/php-5.1.4/lib/php.ini')) { 
    $cfgfile_heading .= ' &nbsp;-- &nbsp;CUSTOM'; 
    $cfgfile_color = '#6699FF'; 
    $cfgfile_value = '<b>' . $cfgfile_value . '</b>'; 
} 
$utd_heading = 'upload_tmp_dir'; 
$utd_color = '#FFFFFF'; 
$utd_value = $utd_now; 
if ($utd_now === '/www/tmp') { 
    $utd_heading .= ' &nbsp;-- &nbsp;INVALID!'; 
    $utd_color = '#FF0000'; 
    $utd_value = '<b>' . $utd_value . '</b>'; 
} 
$ssp_heading = 'session.save_path'; 
$ssp_value = $ssp_now; 
if ($ssp_now === '3;/www/php') { 
    $ssp_heading .= ' &nbsp;-- &nbsp;INVALID!'; 
    $ssp_color = '#FF0000'; 
    $ssp_value = '<b>' . $ssp_value . '</b>'; 
} 

// Display page 
echo "<html><body>\n"; 
echo "<table border=\"2\" cellpadding=\"2\" cellspacing=\"2\"><tbody>\n"; 
echo "  <tr>\n"; 
echo "    <td width=\"300\"><b>$cfgfile_heading</b></td>\n"; 
echo "    <td bgcolor=\"$cfgfile_color\">$cfgfile_value</td>\n"; 
echo "  </tr>\n"; 
echo "  <tr>\n"; 
echo "    <td><b>$utd_heading</b></td>\n"; 
echo "    <td bgcolor=\"$utd_color\">$utd_value</td>\n"; 
echo "  </tr>\n"; 
echo "  <tr>\n"; 
echo "    <td><b>$ssp_heading</b></td>\n"; 
echo "    <td bgcolor=\"$ssp_color\">$ssp_value</td>\n"; 
echo "  </tr>\n"; 
echo "  <tr>\n"; 
echo "    <td colspan=\"2\"><br><br></td>\n"; 
echo "  </tr>\n"; 
echo "  <tr>\n"; 
echo "    <td colspan=\"2\"><b>Contents of $cfgfile</b></td>\n"; 
echo "  </tr>\n"; 
echo "  <tr>\n"; 
echo "    <td colspan=\"2\"><pre>$phpini</pre></td>\n"; 
echo "  </tr>\n"; 
echo "</tbody></table>\n"; 
echo "</body></html>"; 

?>