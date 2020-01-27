<?php

require_once __DIR__ . '/../vendor/autoload.php';

use \Peoplefone\ProvisioningRPC;

if(file_exists(__DIR__ . '/../../provisioning-rpc-settings.php')) {
	include_once __DIR__ . '/../../provisioning-rpc-settings.php';
}

// -----
// AUERSWALD
// -----

$login = (isset($auerswald_login)) ? $auerswald_login : ['username', 'password'];
$mac = (isset($auerswald_mac)) ? $auerswald_mac : "123456ABCDEF";
$url = (isset($auerswald_url)) ? $auerswald_url : "https://provisioningserver.domain.com/<MACADR>/";

$rpc = ProvisioningRPC::connect('auerswald', $login);

print_r($rpc->checkPhone($mac));
print_r($rpc->addPhone($mac, $url, true));
print_r($rpc->removePhone($mac));

// -----
// GIGASET
// -----

$login = (isset($gigaset_login)) ? $gigaset_login : ['username', 'password'];
$mac = (isset($gigaset_mac)) ? $gigaset_mac : "123456ABCDEF-1234";
$url = (isset($gigaset_url)) ? $gigaset_url : "https://provisioningserver.domain.com/%MACD/%DVID/";

$rpc = ProvisioningRPC::connect('gigaset', $login);
$rpc->setProfileName('profile_name'); // default = peoplefone

print_r($rpc->checkPhone($mac));
print_r($rpc->addPhone($mac, $url, true));
print_r($rpc->removePhone($mac));

// -----
// PANASONIC
// -----

$login = (isset($panasonic_login)) ? $panasonic_login : ['username', 'password'];
$mac = (isset($panasonic_mac)) ? $panasonic_mac : "123456ABCDEF";
$url = (isset($panasonic_url)) ? $panasonic_url : "https://provisioningserver.domain.com/{MAC}/";

$rpc = ProvisioningRPC::connect('panasonic', $login);

print_r($rpc->checkPhone($mac));
print_r($rpc->addPhone($mac, $url, true));
print_r($rpc->removePhone($mac));

// -----
// SNOM
// -----

$login = (isset($snom_login)) ? $snom_login : ['username', 'password'];
$mac = (isset($snom_mac)) ? $snom_mac : "123456ABCDEF";
$url = (isset($snom_url)) ? $snom_url : "https://provisioningserver.domain.com/{mac}/";

$rpc = ProvisioningRPC::connect('snom', $login);

print_r($rpc->checkPhone($mac));
print_r($rpc->addPhone($mac, $url, true));
print_r($rpc->removePhone($mac));

// -----
// YEALINK
// -----

$login = (isset($yealink_login)) ? $yealink_login : ['username', 'password'];
$mac = (isset($yealink_mac)) ? $yealink_mac : "123456ABCDEF";
$url = (isset($yealink_url)) ? $yealink_url : "server_name_saved_in_yealink";

$rpc = ProvisioningRPC::connect('yealink', $login);

print_r($rpc->checkPhone($mac));
print_r($rpc->addPhone($mac, $url, true));
print_r($rpc->removePhone($mac));
