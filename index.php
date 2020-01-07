<?php
	error_reporting(E_ERROR | E_PARSE);
	session_start();
	if($_SESSION['loggedin'] == false || !isset($_SESSION['loggedin']))
			header('Location: login.php');
	
	if(isset($_POST['logout'])){
		$_SESSION['loggedin'] = false;
		header('Location: login.php');
	}
	
	if(isset($_POST['changePswdSubmit'])){
		$newpass = $_POST['newpass'];
		$oldpass = $_POST['oldpass'];
		
		if($oldpass !== file_get_contents("lib/pass.txt")){
			echo '<div class="api-result"><b>Stare hasło jest nieprawidłowe.</b></div>';
		}else{
			file_put_contents("lib/pass.txt", $newpass);
			if($newpass == "")
				echo '<div class="api-result"><b>Logowanie dezaktywowane.</b></div>';
			else
				echo '<div class="api-result"><b>Hasło zmienione.</b></div>';
		}
		
	}
	
	
	require 'lib/acclist.php';
	require 'lib/allegroapi.php'; 

	$accounts = GetAccs();
	
	
	
	
	
	
	
	
	if(isset($_GET['code']) && $_SESSION['auth_done'] == false){
		Authorize($_GET['code']); 
	}
	
	if(isset($_GET['authAcc']))
	{
		$accounts = GetAccs();
		foreach($accounts as $value){
			if($value['login'] == $_GET['authAcc']){
				InitAuthorization($value['login'], $value['clientId'], $value['clientSecret']);
			}
		}
	}
	
	$accounts = GetAccs();
	$authInfo = array();
	$i = 1;
	foreach($accounts as $value){
		$RT = RefreshToken($value['login'],$value['clientId'],$value['clientSecret'],$value['refreshToken']);
		if($RT !== true && $RT !== 401){
			$authInfo[$i] = "kod błędu $RT";
		}elseif ($RT === 401){
			$authInfo[$i] = '<i class="icon-cancel"></i>';
		}
		else{
			$authInfo[$i] = '<i class="icon-ok"></i>';
		}
		$i++;
	}
	
	$banResult = "";
	if(!empty( $_POST['user']))
	{
		if(isset($_POST['ban']))
			$banResult = Ban($_POST['user'], $_POST['note']);
		else
			$banResult = UnBan($_POST['user']);
		echo '<div class="api-result" >';
		echo $banResult;
		echo'</div>';
	}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Allegro ban</title>
    <meta charset="utf-8">
	<link rel="stylesheet" href="style.css?v=<?php echo rand(1, 1000000) ?>">
	<link rel="stylesheet" href="font/css/fontello.css?v=<?php echo rand(1, 1000000) ?>">
</head>
<body>
<div class="logout">
	<form method="POST" class="logout">
		<button type="submit" name="logout" class="button"><b>Wyloguj</b></button>
	</form>

	<button type="button" name="changePswd" class="button" onclick="changePswd()">Zmień Hasło</button>

	<form method="POST" class="change-pswd">
		<label>
			<span>Stare Hasło: </span>
			<input type="text" name="oldpass"/>
		</label>
		<label>
			<span>Nowe Hasło: </span>
			<input type="text" name="newpass"/>
		</label>
		<button type="submit" class="button" name="changePswdSubmit">Zapisz</button>
	</form>
</div>
<form method="POST" class="ban-panel">
    <label>
        <span>Login: </span>
        <input type="text" name="user"/>
    </label>
    <label>
        <span style="clear: both;"> Notatka:</span>
        <textarea name="note"></textarea>
    </label>
    <div>
        <button type="submit" name="ban" class="button" >Zbanuj :(</button>
        <button type="submit" name="unban" class="button" >Zdejmij bana :)</button>
    </div>
</form>
<form method="POST" class="usersPanel">
<?php
$accounts = GetAccs();
$i = 1;
foreach($accounts as $value){
	
	
	
	
	
	
	if(isset($_GET))
		$authlink = "?authAcc=".$value['login'];
	else
		$authlink = "&authAcc=".$value['login'];
  echo'
	<div class="user">
		<div class="acc-auth">
			<i>Autoryzacja:</i>
			</br>
			<label> '.$authInfo[$i].'</label>
			<label class="button"><a href="'.$authlink.'"class="authorizeLink">Autoryzuj</a></label>
			
			
		</div>
		<label>
			<span>Konto:</span>
			<input type="text" name="login'.$i.'" value="'.$value['login'].'" class="login"/>
		</label>
		<label>
			<input type="checkbox" name="deleteUser'.$i.'" class="deleteUser'.$i.'"/> Usuń konto
		</label>
		<label>
			<span>Client id:</span>
			<input type="text" name="clientId'.$i.'" value="'.$value['clientId'].'" class="id"/>
		</label>
		<label>
			<span>Client secret:</span>
			<input type="text" name="clientSecret'.$i.'" value="'.$value['clientSecret'].'" class="secret"/>
		</label>
	</div>
  ';
  $i++;
  
}
echo' <input type="number" name="numOfAcc" style="display:none;" value="'.($i - 1).'"/>';
?>
	<div class="new user">
		<label>
			<span>Login:</span>
			<input type="text" name="new-login" value="" class="login"/>
		</label>
		<label>
			<input type="checkbox" name="new-deleteUser" class="new-deleteUser"/> Usuń konto
		</label>
		<label>
			<span>Client id:</span>
			<input type="text" name="new-clientId" value="" class="id"/>
		</label>
		<label>
			<span>Client secret:</span>
			<input type="text" name="new-clientSecret" value="" class="secret"/>
		</label>
	</div>

<button type="button" onclick="addUser()" class="new button">Dodaj</button>
<button type="submit" name="saveUsers" class="saveUsers button">Zapisz</button>
</form>


<script>
	function addUser(){
		document.querySelector(".new.user").classList.remove("new");
		document.querySelector("button.new").classList.add("hidden");
	}
	function changePswd(){
		target = document.querySelector(".change-pswd");
		
		if(target.classList.contains("active")){
			target.classList.remove("active");
		}else{
			target.classList.add("active");
		}
	}
	if ( window.history.replaceState ) {
        window.history.replaceState( null, null, window.location.href );
    }
</script>
</body>

</html>
