<?php 
if (!defined('MCR')) exit;

$result = BD("SELECT `id`,`message_full`,`message` FROM `{$bd_names['news']}`");
$num = mysql_num_rows( $result );
if ($num) {
	
	while ( $line = mysql_fetch_array( $result ) ) {
	  
	    $id = $line['id'];
		$mess = TextBase::HTMLRestore($line['message']);
		$mess_full = TextBase::HTMLRestore($line['message_full']);
		
		$sql_where = "`item_id`='". $id ."' AND `item_type`='" . ItemType::News . "'";
		$commentnum = mysql_result(mysql_query("SELECT COUNT(*) FROM {$bd_names['comments']} WHERE " . $sql_where), 0);
		
	BD("UPDATE `{$bd_names['news']}` SET `message`='".TextBase::SQLSafe($mess)."',`message_full`='".TextBase::SQLSafe($mess_full)."',`comments`='$commentnum' WHERE `id`='$id'");
	}
}
?>