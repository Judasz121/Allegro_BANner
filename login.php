<?php
	error_reporting(E_ERROR | E_PARSE);
	session_start();
	
	if($_SESSION['loggedin'] == true)
		header('Location: index.php');
	
	$filepass = file_get_contents("lib/pass.txt");
	if($filepass == NULL){
		header('Location: index.php');
		$_SESSION['loggedin'] = true;
	}
	if(isset($_POST['pass'])){

		if($filepass === $_POST['pass']){
			$_SESSION['loggedin'] = true;
			header('Location: index.php');
		}
		else{
			echo '<div class="api-result"><b>Nieprawidłowe hasło.</b></div>';
		}
	}
	

	
	
?>
<!DOCTYPE html>
<html>
<head>
    <title>Allegro ban</title>
    <meta charset="utf-8">
	<link rel="stylesheet" href="style.css?v=<?php echo rand(1, 1000000) ?>">
	<link rel="stylesheet" href="font/css/fontello.css">
	<style>
	h4{
		font-size: 20px;
		margin: 0 auto;
		display: table;
	}
	input{
		margin: 10px auto;
		display: table;
		text-align: center;
		font-size: 30px;
	}
	form{
		margin: 150px auto;
		display: table;
		float: none;
	}
	</style>
</head>
<body>
<form method="POST" class="loginPanel">
	<h4> Podaj hasło </h4>
	<input type="password" name="pass"/>
	
<button type="submit" name="submitPassword" class="saveUsers button">Zaloguj</button>
</form>


<script>

</script>
</body>

</html>
