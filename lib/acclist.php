<?php

if(!empty($_POST['numOfAcc']) || !empty($_POST['new-login']) || !empty($_POST['login1'])){
	
	
$accounts = json_decode(file_get_contents("lib/accounts.txt"), true);
$accountsNew = array();
$i = 1;
//for($i = 1; $i <= $_POST['numOfAcc']; $i++){
foreach($accounts as $val){
	if(empty($_POST['deleteUser'.$i])){
		$account = array(
			'login' => $_POST['login'.$i],
			'clientId' => $_POST['clientId'.$i],
			'clientSecret' => $_POST['clientSecret'.$i],
			'accessToken' => $val['accessToken'],
			'refreshToken' => $val['refreshToken']
		);
		
		array_push($accountsNew, $account);
	}
	$i++;
}

if(!empty($_POST['new-login']) && empty($_POST['new-deleteUser'])){
	$account = array(
		'login' => $_POST['new-login'],
		'clientId' => $_POST['new-clientId'],
		'clientSecret' => $_POST['new-clientSecret'],
		'accessToken' => "",
		'refreshToken' => ""
	);
	
	array_push($accountsNew, $account);
}

file_put_contents("lib/accounts.txt", json_encode($accountsNew));

}
function GetAccs(){
	return json_decode(file_get_contents("lib/accounts.txt"), true);
}



















/*
	$accounts = array(
    array(
		'login' => 'www_fotostyle_pl',
        'clientId' => '****',
        'clientSecret' => '4bcf9a77'
    ),
	array(
		'login' => 'wik.popr@gmail.com',
		'clientId' => '87d96c0d7c7b419ab86a043affda3ab0',
		'clientSecret' => 'jVv9Fx3oOuLnmX98nMq15ITDRK9Z9q3b6Z2DJTURY9wTR97gyc1mczL2GNwbshrZ'
		)
	);*/
	?>