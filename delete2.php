<?php
session_start();
require('dbconnect.php');
if (isset($_SESSION['id'])) {
	$id = $_REQUEST['id'];
	$comments = $db->prepare('SELECT * FROM comments WHERE id=?');
	$comments -> execute(array($id));
	$comment = $comments->fetch();
	if ($comment['commenter_id'] == $_SESSION['id']) {
		$del = $db->prepare('DELETE FROM comments WHERE id=?');
		$del->execute(array($id));
	}
}
header('Location: post.php');
exit();
?>
