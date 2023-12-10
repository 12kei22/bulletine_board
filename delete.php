<?php
session_start();
require('dbconnect.php');

if (isset($_SESSION['id'])) {
    if (isset($_GET['post_id'])) {
        $post_id = $_GET['post_id'];

        // ログインユーザーが投稿の作成者であることを確認
        $checkUser = $db->prepare('SELECT created_by FROM posts WHERE id = ?');
        $checkUser->execute(array($post_id));
        $post = $checkUser->fetch();

        if ($post['created_by'] == $_SESSION['id']) {
            // 投稿に紐づくコメントを削除
            $deleteComments = $db->prepare('DELETE FROM comments WHERE post_id = ?');
            $deleteComments->execute(array($post_id));

            // 投稿を削除
            $deletePost = $db->prepare('DELETE FROM posts WHERE id = ?');
            $deletePost->execute(array($post_id));
        }
    }
}

header('Location: post.php');
exit();
?>
