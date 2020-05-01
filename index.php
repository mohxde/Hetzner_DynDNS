<?php
#
#   Script (c) by mohx.de
#   info@mohx.de
#


#################### Config ####################

$script_password    = "strongPassword";

$hetzner_record_name= "";
$hetzner_zone_id    = "";
$hetzner_record_id  = "";
$hetzner_api_token  = "";


$last_ip_file       = "last_ip.txt";

################################################



# Check password from HTTP request
if(!isset($_GET["p"]) || $_GET["p"] != $script_password) 
    die("Wrong Password");


# Check if current IP address is new
$current_ip = $_SERVER['REMOTE_ADDR'];

if(file_exists($last_ip_file))
{
    $last_ip = file_get_contents($last_ip_file);
    
    if($current_ip == $last_ip) 
        die("No updates");
}


# Safe new IP 
file_put_contents($last_ip_file, $current_ip);


# Update DNS Record
$json_array = [
  'value' => $current_ip,
  'ttl' => 600,
  'type' => 'A',
  'name' => $hetzner_record_name,
  'zone_id' => $hetzner_zone_id
]; 
$request_body = json_encode($json_array);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://dns.hetzner.com/api/v1/records/'.$hetzner_record_id);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $request_body);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  'Content-Type: application/json',
  'Auth-API-Token: '.$hetzner_api_token,
]);

$response = curl_exec($ch);

if (!$response) {
  die('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
}

echo "IP updated";

curl_close($ch);
