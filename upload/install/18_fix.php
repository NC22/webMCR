<?php 
if (!defined('MCR')) exit;

$result = BD("SELECT `id`,`message_full`,`message` FROM `{$bd_names['news']}`");
$num = mysql_num_rows( $result );
if ($num) {
	
	while ( $line = mysql_fetch_array( $result ) ) {
	  
	    $id = $line['id'];
		$mess = TextBase::HTMLRestore($line['message']);
		$mess_full = TextBase::HTMLRestore($line['message_full']);
			
	BD("UPDATE `{$bd_names['news']}` SET `message`='$mess',`message_full`='$mess_full' WHERE `id`='$id'");
	}
}
?>