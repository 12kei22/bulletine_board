<?php
session_start();
require('dbconnect.php');



if (isset($_SESSION['id']) && ($_SESSION['time'] + 3600 > time())) {
	$_SESSION['time'] = time();

	$members = $db->prepare('SELECT * FROM members WHERE id=?');
	$members->execute(array($_SESSION['id']));
	$member = $members->fetch();
} else {
	header('Location: login.php');
	exit();
}

if (!empty($_POST)) {
	if (isset($_POST['token']) && $_POST['token'] === $_SESSION['token']) {
		if (empty($_POST['post'])) {
			$errors['post'] = '投稿が未入力です。';
		} elseif (isset($_POST['post'])) {
			$post = $db->prepare('INSERT INTO posts SET created_by=?, post=?, created=NOW()');
			$post->execute(array($member['id'], $_POST['post']));
			header('Location: post.php');
			exit();

		}

	} else {
		header('Location: login.php');
		exit();
	}
}


$posts = $db->query('SELECT m.name, p.* FROM members m  JOIN posts p ON m.id=p.created_by ORDER BY p.created DESC');


$TOKEN_LENGTH = 16;
$tokenByte = openssl_random_pseudo_bytes($TOKEN_LENGTH);
$token = bin2hex($tokenByte);
$_SESSION['token'] = $token;







?>

<!DOCTYPE html>
<html lang="ja">
<link rel="stylesheet" href="style.css">

<body>
	<!-- ★ログアウト★ -->
	<header>
		<div class="head">
			<h1>ふぉおらむ掲示板</h1>
			<span class="logout"><a href="login.php">ログアウト</a></span>

		</div>
	</header>
	<div class="error-messages">
		<?php if (!empty($errors)): ?>
			<ul>
				<?php foreach ($errors as $error): ?>
					<li>
						<?php echo $error; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>

	<form action='' method="post">
		<input type="hidden" name="token" value="<?= $token ?>">
		<?php if (isset($error['login']) && ($error['login'] == 'token')): ?>
			<p class="error">不正なアクセスです。</p>
		<?php endif; ?>
		<div class="edit">
			<p>
				<?php echo htmlspecialchars($member['name'], ENT_QUOTES); ?>さん、ようこそ
			</p>
			<textarea name="post" cols='70' rows='10'><?php echo htmlspecialchars($post ?? "", ENT_QUOTES); ?></textarea>
		</div>

		<input type="submit" value="投稿する" class="button02">
	</form>


	<?php foreach ($posts as $post): ?>

		<div class="post">
			<?php echo htmlspecialchars($post['post'], ENT_QUOTES); ?> |
			<span class="name">
				<?php echo htmlspecialchars($post['name'], ENT_QUOTES); ?> |
				<?php echo htmlspecialchars($post['created'], ENT_QUOTES); ?> |

				<?php if ($_SESSION['id'] == $post['created_by']): ?>
					[<a href="delete.php?post.id=<?php echo htmlspecialchars($post['id'], ENT_QUOTES); ?>">削除</a>]
				<?php endif; ?>
			</span>
		</div>

		<form action='' method="post">
			<input type="hidden" name="token" value="<?= $token ?>">
			<input type="hidden" name="post_id" value="<?= $post['id'] ?>">

			<span class="edit2">
				<textarea name="comment" class="text"></textarea>
				<input type="submit" value="コメントする" class="button03">
			</span>

		</form>

		<?php
		if (empty($_POST['comment'])) {
			$errors['comment'] = '投稿が未入力です。';
		} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
			// コメントを保存
			$post_id = $_POST['post_id'];

			$comment = $db->prepare('INSERT INTO comments SET post_id=?, commenter_id=?, comment=?, created=NOW()');
			$comment->execute(array($post_id, $member['id'], $_POST['comment']));
			header('Location: post.php');
			exit();
		}
		// コメントを表示
		$comments = $db->prepare('SELECT m.name, c.* FROM members m JOIN comments c ON m.id=c.commenter_id WHERE c.post_id=? ORDER BY c.created ASC');
		$comments->execute(array($post['id']));
		foreach ($comments as $comment):


			?>
			<?php if ($comment['post_id'] === $post['id']): ?>

				<div class="comments">
					&nbsp;
					<?php echo htmlspecialchars($comment['comment'], ENT_QUOTES); ?>|
					<span class="name">
						<?php echo htmlspecialchars($comment['name'], ENT_QUOTES); ?> |
						<?php echo htmlspecialchars($comment['created'], ENT_QUOTES); ?>

						<?php if ($_SESSION['id'] == $comment['commenter_id']): ?>
							[<a href="delete2.php?id=<?php echo htmlspecialchars($comment['id'], ENT_QUOTES); ?>">削除</a>]
						<?php endif; ?>
					</span>
				</div>
			<?php endif; ?>
		<?php endforeach; ?>
	<?php endforeach; ?>



</body>

</html>
