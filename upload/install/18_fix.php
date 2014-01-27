<?php 
if (!defined('MCR')) exit;

$result = getDB()->ask("SELECT `id`,`message_full`,`message` FROM `{$bd_names['news']}`");

while ( $line = $result->fetch() ) {

    $id = (int) $line['id'];
    $mess = TextBase::HTMLRestore($line['message']);
    $mess_full = TextBase::HTMLRestore($line['message_full']);

    $sql_where = "`item_id`='". $id ."' AND `item_type`='" . ItemType::News . "'";
    $commentnum = getDB()->fetchRow("SELECT COUNT(*) FROM {$bd_names['comments']} WHERE " . $sql_where, false, 'num');

    $sql = "UPDATE `{$bd_names['news']}` SET `message`=:mess,`message_full`=:full,`comments`=:comments "
         . "WHERE `id`='$id'";

    getDB()->ask($sql, array('mess' => $mess, 'full' => $mess_full, 'comments' => $commentnum[0]));
}
