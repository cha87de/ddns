<?php

  // dnsupdate.php?AUTH_TOKEN=xyz&DOMAIN=testserv[&FORCEIP=192.168.2.101]

  // configuration of user and domain
  $domainsJson = file_get_contents("/opt/data/domains.json");
  $domain_auth = json_decode($domainsJson, true);
  $domain_auth = $domain_auth[0];

  // main domain for dynamic DNS
  $dyndns = file_get_contents("/opt/data/ddns-domain");
  // DNS server to send update to
  $dnsserver = "localhost";
  // port of DNS server
  $dnsport = "";
 
  // short sanity check for given IP
  function checkip($ip){
    $iptupel = explode(".", $ip);
    foreach ($iptupel as $value)
    {
      if ($value < 0 || $value > 255)
        return false;
      }
    return true;
  }
 
  // retrieve IP
  if ( isset($_REQUEST['FORCEIP'] ) ) {
    $ip = $_REQUEST['FORCEIP'];
  }else if ( isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
    // probably behind a load balancer ...
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
  } else if ( isset($_SERVER['REMOTE_ADDR']) ){
    $ip = $_SERVER['REMOTE_ADDR'];
  } else {
    echo "failed to find remote IP.";
    exit(0);
  }
  
  // retrieve auth token
  if ( isset($_REQUEST['AUTH_TOKEN']) ){
    $auth_token = $_REQUEST['AUTH_TOKEN'];
  }else{
    echo "No auth token given by connection from $ip\n";
    exit(0);
  }
 
  // check for given domain
  if ( isset($_REQUEST['DOMAIN']) ) {
    $subdomain = $_REQUEST['DOMAIN'];
  } else {
    echo "From $ip didn't provide any domain\n";
    exit(0);
  }
  
  // check for needed variables
  if ( ! ( isset($subdomain) && isset($ip) && isset($auth_token) ) ) {
	echo "DDNS change from $ip with $subdomain failed because of missing values\n";
	exit(0);
  }
  
  // short sanity check for given IP
  if ( !( preg_match("/^(\d{1,3}\.){3}\d{1,3}$/", $ip) && checkip($ip) && $ip != "0.0.0.0" && $ip != "255.255.255.255" )) {
	echo "IP $ip with $subdomain was wrong\n";
	exit(0);
  }
  
  // short sanity check for given domain
  if ( ! preg_match("/^[\w\d-_\*\.]+$/", $subdomain) ) {
	echo "From $ip subdomain $subdomain is not allowed\n";
	exit(0);
  }
  
  // check whether user is allowed to change domain
  if ( ! ( isset($domain_auth[$subdomain]) && $domain_auth[$subdomain] == $auth_token) ) {
  echo "Domain $subdomain is not allowed with specified token\n";
  exit(0);
  }
        
  // shell escape all values
  $subdomain = escapeshellcmd($subdomain);
  $ip = escapeshellcmd($ip);
 
  // prepare command
  $data = "<<EOF
server $dnsserver $dnsport
zone $dyndns
update delete $subdomain.$dyndns A
update add $subdomain.$dyndns 300 A $ip
send
EOF";
  // run DNS update
  #echo "/usr/bin/nsupdate -k /opt/data/keys/K$dyndns*.private $data\n";
  exec("/usr/bin/nsupdate -k /opt/data/keys/K$dyndns*.private $data", $cmdout, $ret);
  // check whether DNS update was successful
  if ($ret != 0){
    echo "Changing DNS for $subdomain.$dyndns to $ip failed with code $ret";
  }else{
    echo "success";
  }

?>