<?php
print "<pre style=\"font:13px;color:#444444;\">\n";
system("export LANG=C; /usr/bin/sudo /usr/bin/top -b -n 1");
print "</pre>";
ob_flush();
?>