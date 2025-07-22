<?php
<?php
session_start();
session_destroy();
session_write_close();
setcookie(session_name(),'',0,'/');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Location: loginpage.php");
exit();
?>