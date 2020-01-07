<?php

$debug = false;

if ($debug) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

if (isset($_COOKIE['banResults'])) {
    $results = json_decode($_COOKIE['banResults'], true);
    setcookie('banResults', false);
}

if (!empty($_POST['user'])) {
    require __DIR__.'/lib/Ban.php';
    $ban = new Ban($debug);
    if (isset($_POST['unban'])) {
        $results = $ban->unban($_POST['user']);
    } else {
        $results = $ban->ban($_POST['user'], $_POST['note']);
    }

    setcookie('banResults', json_encode($results));
    header('Location: '.$_SERVER['REQUEST_URI']);
} else {
?>
<!DOCTYPE html>
<html>
<head>
    <title>Allegro ban</title>
    <meta charset="utf-8">
    <style>
        body {margin: 50px;}
        label {display: block; margin-bottom: 10px;}
        label > span {float: left; text-align: right; width: 175px; padding-right: 15px;}
        form {display: inline-block; border: 1px solid #e8e8e8; padding: 15px;}
        form > div {text-align: center;}
        hr {height: 0; border: 0 none; border-bottom: 1px solid #e8e8e8;}
    </style>
</head>
<body>
<?php if (isset($results)): ?>
<?php if ($results['status'] !== 'ok'): ?>
<div class="error"><?php echo $results['message'] ?></div>
<?php else: ?>
<div>Użytkownik <?php echo $results['userId'] ?> (<?php echo $results['user'] ?>)</div>
<hr/>
<div class="results">
<?php foreach ($results['results'] as $result): ?>
    <div><?php echo $result['account'] ?>: <b><?php echo $result['result'] ?></b></div>
<?php endforeach ?>
</div>
<?php endif ?>
<hr/>
<div><a href="<?php echo $_SERVER['REQUEST_URI'] ?>">back</a></div>
<?php else: ?>
<form method="POST">
    <label>
        <span>Login użytkownika:</span>
        <input type="text" name="user"/>
    </label>
    <label>
        <span>Notatka:</span>
        <textarea name="note"></textarea>
    </label>
    <div>
        <button type="submit" name="ban">Zbanuj :(</button>
        <button type="submit" name="unban">Zdejmij bana :)</button>
    </div>
</form>
<?php endif ?>
</body>
</html>
<?php } ?>