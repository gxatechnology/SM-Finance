<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
function result(int $status, bool $ok, string $message, ?string $ref=null): never {
 http_response_code($status);echo json_encode(['success'=>$ok,'message'=>$message,'reference'=>$ref],JSON_UNESCAPED_UNICODE);exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') result(405,false,'POST required.');
if ((int)($_SERVER['CONTENT_LENGTH']??0)>30000) result(413,false,'Request is too large.');
if (!empty($_POST['website']??'')) result(400,false,'Invalid submission.');
if (($_POST['consent']??'') !== 'yes') result(422,false,'Please agree to the privacy notice.');
$name=trim((string)($_POST['name']??''));
$phone=preg_replace('/[^0-9]/','',(string)($_POST['phone']??''));
$email=trim((string)($_POST['email']??''));
$subject=trim((string)($_POST['subject']??'General enquiry'));
$message=trim((string)($_POST['message']??''));
$location=trim((string)($_POST['location']??''));
if(strlen($name)<2 || strlen($name)>140 || !preg_match('/^(?:91)?[6-9][0-9]{9}$/',$phone)) result(422,false,'Enter your full name and a valid 10-digit Indian mobile number.');
if ($email!=='' && (!filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($email)>180)) result(422,false,'Invalid email address.');
foreach ([$subject,$message,$location] as $val) if (strlen($val)>2000) result(422,false,'Your message is too long.');
$folder=dirname(__DIR__).'/private/data';
if(!is_dir($folder) && !mkdir($folder,0700,true)) result(503,false,'Storage unavailable.');
$ip=(string)($_SERVER['REMOTE_ADDR']??'unknown');
$hash=hash('sha256',$ip.'smf-enquiry-rate');
$rateFile=$folder.'/rate.json';
$r=fopen($rateFile,'c+');
if(!$r) result(503,false,'Storage temporarily unavailable.');
flock($r,LOCK_EX);$saved=stream_get_contents($r);$limits=json_decode($saved?:'{}',true);if(!is_array($limits))$limits=[];
$now=time();foreach($limits as $k=>$v)if(!is_array($v) || (int)($v[0]??0)<$now-3600)unset($limits[$k]);
$entry=$limits[$hash]??[$now,0];if((int)$entry[1]>=5){flock($r,LOCK_UN);fclose($r);result(429,false,'Too many enquiries. Please try again later.');}
$limits[$hash]=[$entry[0],$entry[1]+1];rewind($r);ftruncate($r,0);fwrite($r,json_encode($limits));fflush($r);flock($r,LOCK_UN);fclose($r);
$reference='SMF-'.gmdate('ymd').'-'.strtoupper(bin2hex(random_bytes(3)));
$record=['reference'=>$reference,'created_at'=>gmdate('c'),'name'=>$name,'phone'=>$phone,'email'=>$email,'subject'=>$subject,'message'=>$message,'location'=>$location,'status'=>'new'];
$file=$folder.'/enquiries.jsonl';$handle=fopen($file,'ab');
if(!$handle) result(503,false,'We could not store your enquiry. Please contact the office.');
flock($handle,LOCK_EX);$written=fwrite($handle,json_encode($record,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)."\n");fflush($handle);flock($handle,LOCK_UN);fclose($handle);
if($written===false) result(503,false,'We could not store your enquiry.');
result(200,true,'Enquiry recorded successfully.',$reference);
