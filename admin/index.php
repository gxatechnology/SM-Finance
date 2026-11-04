<?php
declare(strict_types=1);
header('Cache-Control: no-store');header('X-Content-Type-Options: nosniff');
ini_set('session.use_strict_mode','1');
session_set_cookie_params(['httponly'=>true,'secure'=>!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off','samesite'=>'Strict']);
session_start();
$root=dirname(__DIR__).'/private';$config=is_file($root.'/config.php')?require $root.'/config.php':[];
if(empty($config['admin_hash'])){http_response_code(503);exit('Administrator account is not configured. Complete the private deployment setup first.');}
if(isset($_GET['logout'])){$_SESSION=[];session_destroy();header('Location: index.php');exit;}
$error='';
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['password'])){
 if(password_verify((string)$_POST['password'],$config['admin_hash'])){session_regenerate_id(true);$_SESSION['smf_admin']=true;$_SESSION['csrf']=bin2hex(random_bytes(18));}
 else{$error='Invalid password.';usleep(150000);}
}
$logged=($_SESSION['smf_admin']??false)===true;
if(!$logged):?><!doctype html><html lang="en"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>SMF Admin</title><style>body{background:#eef3f7;color:#132c45;font-family:system-ui;display:grid;place-items:center;min-height:90vh}.box{background:#fff;padding:36px;border-radius:16px;min-width:280px;box-shadow:0 15px 45px #1233}input,button{padding:12px;border-radius:8px;width:100%;box-sizing:border-box;margin:8px 0;border:1px solid #ccd6e0}button{background:#087b38;color:white;border:0;cursor:pointer}</style><div class="box"><h2>SMF Enquiry Dashboard</h2><p>Authorized access only.</p><?php if($error)echo '<p style="color:#b91c1c">'.htmlspecialchars($error).'</p>';?><form method="post"><input type="password" name="password" placeholder="Admin password" required><button>Sign In</button></form></div></html><?php exit;endif;
$file=$root.'/data/enquiries.jsonl';$records=[];
if(is_file($file))foreach(file($file,FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES) as $line){$obj=json_decode($line,true);if(is_array($obj))$records[]=$obj;}
$records=array_reverse($records);
if(isset($_GET['csv'])){
 header('Content-Type: text/csv; charset=utf-8');header('Content-Disposition: attachment; filename="smf-enquiries.csv"');
 $out=fopen('php://output','w');fputcsv($out,['reference','created_at','name','phone','email','subject','location','message','status']);
 foreach($records as $rec)fputcsv($out,array_map(fn($k)=>str_replace(["\r","\n"],' ',(string)($rec[$k]??'')),['reference','created_at','name','phone','email','subject','location','message','status']));fclose($out);exit;
}
function e(mixed $s):string{return htmlspecialchars((string)$s,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');}
?><!doctype html><html lang="en"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>SMF Enquiry Dashboard</title><style>body{margin:0;font-family:Inter,system-ui;background:#f6f9fb;color:#163251}header{display:flex;justify-content:space-between;gap:20px;align-items:center;background:#082c53;color:white;padding:22px 5vw}a{color:#087b38}header a{color:#d7f6e5}main{padding:30px 5vw}h1{font-size:1.65rem}.pill{background:#e4f7eb;border-radius:30px;padding:4px 12px;color:#095d35}.panel{overflow:auto;background:white;border-radius:13px;box-shadow:0 8px 28px #07284b13}table{border-collapse:collapse;width:100%;min-width:950px}th,td{padding:13px;text-align:left;border-bottom:1px solid #e5eaf0;vertical-align:top;font-size:13px}th{background:#e7eff7}td:last-child{max-width:250px;white-space:pre-wrap}tr:hover{background:#f8fbfd}.bar{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px}</style><header><strong>SMF Enquiry Dashboard</strong><nav><a href="../index.html">Website</a> · <a href="?csv=1">Export CSV</a> · <a href="?logout=1">Log Out</a></nav></header><main><div class="bar"><h1>Customer Enquiries</h1><span class="pill"><?=count($records)?> received</span></div><div class="panel"><table><thead><tr><th>Reference</th><th>Date</th><th>Name</th><th>Phone</th><th>Email</th><th>Subject</th><th>Location</th><th>Message</th></tr></thead><tbody><?php foreach($records as $r):?><tr><td><?=e($r['reference']??'')?></td><td><?=e($r['created_at']??'')?></td><td><?=e($r['name']??'')?></td><td><?=e($r['phone']??'')?></td><td><?=e($r['email']??'')?></td><td><?=e($r['subject']??'')?></td><td><?=e($r['location']??'')?></td><td><?=e($r['message']??'')?></td></tr><?php endforeach; if(!$records):?><tr><td colspan="8">No enquiries received yet.</td></tr><?php endif;?></tbody></table></div><p>Enquiries are stored locally on this server. Use HTTPS and protect admin credentials.</p></main></html>
