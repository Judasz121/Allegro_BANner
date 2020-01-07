<?php
	if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') 
		$thislink = "https"; 
	else
		$thislink = "http"; 
		// Append the common URL characters. 
		$thislink .= "://"; 
		// Append the host(domain name, ip) to the URL. 
		$thislink .= $_SERVER['HTTP_HOST']; 
		// Append the requested resource location to the URL 
		$thislink .= $_SERVER['REQUEST_URI']; 
		list($thisUrl, $query) = explode('?', $thislink, 2);


if (!function_exists('getAccessToken')) {
function getAccessToken($id, $secret): String
{
    $url = "https://allegro.pl/auth/oauth/token?grant_type=client_credentials";
    $clientId = $id;
    $clientSecret = $secret;

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERNAME, $clientId);
    curl_setopt($ch, CURLOPT_PASSWORD, $clientSecret);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $tokenResult = curl_exec($ch);
    $resultCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($tokenResult === false || $resultCode !== 200) {
    //    echo "Getting Token went wrong: ";
	//	echo $tokenResult;
	//	exit;
		return false;
    }

    $tokenObject = json_decode($tokenResult);
	
    return $tokenObject->access_token;
}}

	// -------------AUTHORIZATION-------------

if (!function_exists('InitAuthorization')) {
function InitAuthorization($login, $id, $secret){
	$_SESSION['auth_done'] = false;
	$_SESSION['authUserLogin'] = $login;
	$_SESSION['authUserId'] = $id;
	$_SESSION['authUserSecret'] = $secret;
	global $thisUrl;
	header('Location: '.'https://allegro.pl/auth/oauth/authorize?response_type=code&client_id='.$id.'&redirect_uri='.$thisUrl);
}
}

if (!function_exists('Authorize')) {
function Authorize($code){
	$url = "https://allegro.pl/auth/oauth/token?grant_type=authorization_code&code=$code&redirect_uri=".$GLOBALS['thisUrl'];
    $clientId = $_SESSION['authUserId'];
    $clientSecret = $_SESSION['authUserSecret'];
	$login = $_SESSION['authUserLogin'];
	$auth = base64_encode("$clientId:$clientSecret");

    $ch = curl_init($url);

	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                 "Authorization: Basic $auth"
				 
    ]);
	

    $result = curl_exec($ch);
    $resultCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $resultObject = json_decode($result ,true);
	curl_close($ch);
	
 
	

	$accounts = GetAccs();
	$accountsNew = array();
	//echo "</br>authorizing session user -->".$_SESSION['authUserLogin']."<-";
	foreach($accounts as $value){
		
		if($value['login'] == $_SESSION['authUserLogin']){
			$account = array(
				'login' => $value['login'],
				'clientId' => $value['clientId'],
				'clientSecret' => $value['clientSecret'],
				'accessToken' => $resultObject['access_token'],
				'refreshToken' => $resultObject['refresh_token']
			);
		}else{
			$account = array(
				'login' => $value['login'],
				'clientId' => $value['clientId'],
				'clientSecret' => $value['clientSecret'],
				'accessToken' => $value['accessToken'],
				'refreshToken' => $value['refreshToken']
			);
			
		}
		array_push($accountsNew, $account);
	}
	file_put_contents("lib/accounts.txt", json_encode($accountsNew));
	
	
	
	
	$_SESSION['auth_done'] = true;
	if ($result === false) {
		return $login.": Niezautoryzowano, kod błędu $resultCode";
    }
	else{
		return $login.": Zautoryzowano pomyślnie";
	}
}}

if (!function_exists('RefreshToken')) {
function RefreshToken($login, $clientId, $clientSecret, $refreshToken){
if($_SESSION['auth_done'] !== false){
	
	$url = "https://allegro.pl/auth/oauth/token?grant_type=refresh_token&refresh_token=".$refreshToken."&redirect_uri=".$GLOBALS['thisUrl'];
	$auth = base64_encode("$clientId:$clientSecret");

    $ch = curl_init($url);

	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                 "Authorization: Basic $auth"
				 
    ]);
	

    $result = curl_exec($ch);
    $resultCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $resultObject = json_decode($result ,true);
	curl_close($ch);
/*	echo"</br>";
	print_r($resultObject);*/

	
	
	$accounts = GetAccs();

	$accountsNew = array();
	foreach($accounts as $value){
		
		if($value['login'] == $login && $resultObject['access_token'] !== NULL){
			$account = array(
				'login' => $value['login'],
				'clientId' => $value['clientId'],
				'clientSecret' => $value['clientSecret'],
				'accessToken' => $resultObject['access_token'],
				'refreshToken' => $resultObject['refresh_token']
			);
		}else{
			$account = array(
				'login' => $value['login'],
				'clientId' => $value['clientId'],
				'clientSecret' => $value['clientSecret'],
				'accessToken' => $value['accessToken'],
				'refreshToken' => $value['refreshToken']
			);
			
		}
		array_push($accountsNew, $account);
	}
	file_put_contents("lib/accounts.txt", json_encode($accountsNew));
    if ($resultObject['access_token'] === NULL || empty($resultObject['access_token'])){
		return $resultCode;
	}
    else{
		return true;
		file_put_contents("lib/accounts.txt", json_encode($accountsNew));
	}
}
}
}
/////////////////////////////////////////////////////////////////////////////////////////




//------------------BAN-----------------

if (!function_exists('Ban')) {
function Ban($login, $note){
	$accounts = GetAccs();
	foreach($accounts as $account){
		$clientId = $account['clientId'];
		$clientSecret = $account['clientSecret'];
		$token = $account['accessToken'];
		
		$user = [
			"user" => [
				"login" => $login,
			],
			"note" => $note
		];
		$query = json_encode($user);
		
		
		$url = "https://api.allegro.pl/sale/blacklisted-users";
		$ch = curl_init($url);
		
		curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
					 "Authorization: Bearer $token",
					 "Accept: application/vnd.allegro.public.v1+json",
					 "Content-Type: application/vnd.allegro.public.v1+json",
		]);
		
		$result = json_decode(curl_exec($ch), true);
		$resultCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		
		
		curl_close($ch);
		
		if ($resultCode !== 201 && $resultCode !== 409 && $resultCode !== 401) {
			$functionResult .= "<div class='left'>".$account['login'].":</div><div class='right'> Niezbanowany, kod błędu: $resultCode </div></br>";
		}
		if($result == true && $resultCode === 201){
			$functionResult .= "<div class='left'>".$account['login'].":</div><div class='right'> $login Zbanowany pomyślnie. </div></br>";
		}
		if($resultCode === 409){
			$functionResult .= "<div class='left'>".$account['login'].":</div><div class='right'> Niezbanowany, $login już na czarnej liście. </div></br>";
		}
		if($resultCode === 401){
			$functionResult .= "<div class='left'>".$account['login'].":</div><div class='right'> Niezbanowany, błąd autoryzacji. </div></br>";
		}
	}
	
	return $functionResult;
}
}

if (!function_exists('UnBan')) {
function UnBan($login){
	$accounts = GetAccs();
	$functionResult = "";
	foreach($accounts as $account){
		$clientId = $account['clientId'];
		$clientSecret = $account['clientSecret'];
		$token = $account['accessToken'];
		
		
		
		$url = "https://api.allegro.pl/sale/blacklisted-users";
		$ch = curl_init($url);
		
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
					 "Authorization: Bearer $token",
					 "Accept: application/vnd.allegro.public.v1+json"
		]);
		
		$result = json_decode(curl_exec($ch), true);
		$resultCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		
		if ($resultCode !== 200 && $resultCode !== 401) {
			$functionResult .= "<div class='left'>".$account['login'].":</div><div class='right'> Nieodbanowany, kod błędu: $resultCode </div></br>";
		}
		if($resultCode === 401){
			$functionResult .= "<div class='left'>".$account['login'].":</div><div class='right'> Nieodbanowany, błąd autoryzacji. </div></br>";
		}
		
		$unbanResult = "<div class='left'>".$account['login'].":</div><div class='right'> Nie znaleziono $login na czarnej liście. </div></br>";
		
		foreach($result['blacklistedUsers'] as $val){
			if(strcasecmp($val['user']['login'], $login) == 0){
				$id = $val['user']['id'];
				
				$url = 'https://api.allegro.pl/sale/blacklisted-users/'.$id;
				$ch = curl_init($url);
				
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_HTTPHEADER, [
							 "Authorization: Bearer $token",
							 "Accept: application/vnd.allegro.public.v1+json"
				]);
				
				$result = json_decode(curl_exec($ch), true);
				$banResultCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				curl_close($ch);
				if($banResultCode === 204){
					$unbanResult = "<div class='left'>".$account['login'].":</div><div class='right'> $login Odbanowany pomyślnie. </div></br>";
				}
				if($banResultCode === 401){
					$unbanResult = "<div class='left'>".$account['login'].":</div><div class='right'> Nieodbanowany, błąd autoryzacji. </div></br>";
				}
				if($banResultCode !== 204 && $banResultCode !== 401){
					$unbanResult = "<div class='left'>".$account['login'].":</div><div class='right'> Nieodbanowany, kod błędu: $banResultCode </div></br>";
				}
			}
		}
		if ($resultCode === 200)
			$functionResult .= $unbanResult;
		
	}
	return $functionResult;
}
}
?>