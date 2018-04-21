<?php



/*
 * Provides necessary classes and functions for handling and manulating files 
 *
 */


Class File {
	var $filename;
	var $fh;
	var $pos = 0;
	var $contents = array();

	function File($filename) {
		//constructor
		$this->filename = $filename;
	}

	function Load() {
		if( file_exists($this->filename) === FALSE)
		{
			return FALSE;
		}
		if (!$this->fh = fopen($this->filename, "r"))
		{
			return FALSE; //couldn't open file
		}
		$this->Clear();

		while(!feof($this->fh)){
			$this->contents[] = str_replace("\n", "", fgets($this->fh, 4096));
		}
		fclose($this->fh);
		$this->contents=array_slice($this->contents, 0, count($this->contents)-1); //remove the last empty line

		return TRUE;
	}

	function Save() {
		$fp = popen("export LANG=C;/usr/bin/sudo /usr/bin/tee " . $this->filename, "w");
		foreach ($this->contents as $line){
			fwrite($fp, $line . "\n");
		}

		pclose($fp);
	}

	function GetLine() {
		if ($this->EOF())
			return FALSE;
		$line = $this->contents[$this->pos];
		$this->pos++;
		return $line;
	}

	function EOF() {
		if ($this->pos >= count($this->contents)){
			return TRUE;
		}
		return FALSE;
	}

	function Clear() {
		$this->pos = 0;
		unset($this->contents);
		$this->contents = array();
	}

	function Top() {
		$this->pos = 0;
	}

	function AddLine($line) {
		$this->contents[] = $line;
	}
	
	/*
	 * 说明：添加内容在文件的第一行
	 * 参数：$line：内容
	 * 返回：返回TRUE。
	 * CREATED BY 王大典，2010-07-01
	 */
	function AddLineStart($line)
	{
		$contents_buffer = $this->contents;
		$this->contents = array();
		$this->contents[] = $line;
		for($i=0; $i<count($contents_buffer); $i++)
		{
			$this->contents[] = $contents_buffer[$i];
		}
		
		if( $this->pos !== 0 )
		{
			$this->pos = $this->pos + 1;
		}
		return TRUE;
	}

	function EditLine($search, $replace) {
		for ($i=0; $i < count($this->contents); $i++){
			if (preg_match("|" . $search . "|i", $this->contents[$i])){
				$this->contents[$i] = preg_replace("|" . $search . ".*|i", $replace, $this->contents[$i]);
				return TRUE;
			}
		}
		$this->AddLine($replace); //line not found, add it
	}

	function FindLine($search) {
		for ($i=0; $i < count($this->contents); $i++){
			if (preg_match("|" . $search . "|i", $this->contents[$i])){
				return $this->contents[$i];
			}
		}
		return FALSE;
	}

	function DeleteLine($search, $all=TRUE){
		$match = FALSE;
		for ($i=0; $i < count($this->contents); $i++){
			if (preg_match("|" . $search . "|i", $this->contents[$i])){
				$this->contents = array_merge(array_slice($this->contents, 0,$i), array_slice($this->contents, $i+1));
				$match = TRUE;
				$i--; //fix the counter because the array shunk
				if (!$all) return TRUE;  //only return if not removing all occurances
			}
		}
		return $match;
	}

	function Delete(){
		exec("export LANG=C; /usr/bin/sudo /bin/rm -f " . $this->filename . " 2>&1", $output, $retval );
		if ($retval){
			//chown error occured
			$error = implode(" ", $output);
			return FALSE;
		}
	}

	function chmod($val){
		if ($val){
			exec("export LANG=C; /usr/bin/sudo /bin/chmod " . $val . " " . $this->filename . " 2>&1", $retval, $output);
			if ($retval){
				//chown error occured
				$error = implode(" ", $output);
				return FALSE;
			}
		}
	}

	function chown($user, $group){
		if ($user){
			exec("export LANG=C; /usr/bin/sudo /bin/chown " . $user . " " . $this->filename . " 2>&1", $retval, $output);
			if ($retval){
				//chown error occured
				$error = implode(" ", $output);
				return FALSE;
			}
		}
		return $this->chgrp($group);
	}
	
	function chgrp($group){
		if ($group){
			exec("export LANG=C; /usr/bin/sudo /bin/chgrp " . $group . " " . $this->filename . " 2>&1", $retval, $output);
			if ($retval){
				//chgrp error occured
				$error = implode(" ", $output);
				return FALSE;
			}
		}
	}
}
?>
