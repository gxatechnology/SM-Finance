<?php
declare(strict_types=1);
header('X-Content-Type-Options: nosniff');
$root=dirname(__DIR__).'/private';$config=$root.'/config.php';$tokenFile=$root.'/setup-token.txt';
if(!is_file($tokenFile)){header('Location: index.php');exit;}
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 $given=(string)($_POST['setup_token']??'');$pass=(string)($_POST['password']??'');
 if(!hash_equals(trim((string)file_get_contents($tokenFile)),$given))$error='Invalid setup token.';
 elseif(strlen($pass)<14)$error='Choose an administrator password of at least 14 characters.';
 else{
   $hash=password_hash($pass,PASSWORD_DEFAULT);
   $cfg="<?php\nreturn ['admin_hash' => ".var_export($hash,true)."] ;\n";
   if(file_put_contents($config,$cfg,LOCK_EX)===false)$error='Unable to write config; check private/ permissions.';
   else { @unlink($tokenFile);header('Location: index.php?setup=ok');exit; }
 }
}
?><!doctype html><html lang="en"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>SMF Admin Setup</title><style>body{background:#edf3f7;color:#102d4d;font-family:system-ui;display:grid;place-items:center;min-height:95vh}.panel{background:#fff;padding:2rem;border-radius:14px;max-width:430px;width:88%;box-shadow:0 12px 40px #082c5315}input,button{padding:12px;width:100%;box-sizing:border-box;margin:9px 0;border-radius:7px;border:1px solid #cbd5e1}button{background:#087b38;color:#fff;font-weight:700;cursor:pointer}</style><div class="panel"><h2>SMF Admin Initial Setup</h2><p>Use the one-time token from the included deployment instructions. This page disables itself after setup.</p><?php if($error)echo '<p style="color:#b42318">'.htmlspecialchars($error).'</p>';?><form method="post"><label>Setup Token<input required name="setup_token" autocomplete="off"></label><label>New Admin Password<input required type="password" name="password" minlength="14" autocomplete="new-password"></label><button>Initialize Secure Admin</button></form></div></html>
