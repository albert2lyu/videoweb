<html>
<style>
*{font-family:ËÎÌå; font-size:9pt}
.dir{background-color:#eeeeee;margin-left:5px}
.file{background-color:white;margin-left:15px}
</style>
<body>
<?php
 function showdir($iter)
{
	print "<ul>";
	for( ; $iter->valid(); $iter->next())
	{
		if($iter->isDir() && !$iter->isDot())
		{
			printf('<li class="dir">%s</li>', $iter->current());
		}
		else if($iter->isFile())
		{
			print '<li class="file">' . $iter->current() . ' (' . $iter->getSize() . '×Ö½Ú)</li>';
		}
	}
	print "</ul>";
}
if(isset($_GET['dir']) && is_dir($_GET['dir']))
{
	showdir(new DirectoryIterator($_GET['dir']));
}
?>
</body>
</html>
