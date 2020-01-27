<?php

namespace Peoplefone;

class ProvisioningRPCDeviceGigaset extends ProvisioningRPCXML
{
	private $client_url = 'https://prov.gigaset.net';
	private $client_headers = ['Content-Type'=>'text/xml'];
	private $client_auth;
	private $profile_name = "peoplefone";
	
	/**
	 * @param array $client_auth
	 */
	public function __construct(array $client_auth=['username','password'])
	{
		parent::__construct($this->client_url);
		$this->client_auth = $client_auth;
	}
	
	/**
	 * @param string $profile_name
	 */
	public function setProfileName(string $profile_name)
	{
		$this->profile_name = $profile_name;
	}
	
	/**
	 * @param string $macid
	 * @return ProvisioningRPCResult
	 */
	public function checkPhone(string $macid) : ProvisioningRPCResult
	{
		// FORMAT MAC
		
		$macid = parent::formatMacAddress($macid);
		if(strlen($macid)!=12 && strlen($macid)!=17) { // attention: MAC-ID
			return ProvisioningRPCResult::macAddressInvalid($macid);
		}
		
		// XMLRPC CALL
		
		try {
			$response = $this->client->post('/apxml/rpc.do',[
					'auth' => $this->client_auth,
					'headers' => $this->client_headers,
					'body' => xmlrpc_encode_request("autoprov.checkDevice", strtoupper($macid)),
			]);
			
			$xmlrpc = $response->getBody()->getContents();
		}
		catch (\GuzzleHttp\Exception\ClientException $e) {
			return ProvisioningRPCResult::connectionError($mac, $e->getMessage());
		}
		
		$data = xmlrpc_decode($xmlrpc);
		
		$mac = strlen($macid)==17 ? substr($macid, 0, 12) : $macid;
		
		if(isset($data[0]) && $data[0]) {
			return ProvisioningRPCResult::macAddressFound($mac);
		}
		
		if(isset($data[1])) {
			switch (strtolower($data[1])) {
				case strtolower($mac):
					return ProvisioningRPCResult::macAddressFound($mac);
					break;
				case strtolower('mac_not_found:'.$mac):
					return ProvisioningRPCResult::macAddressNotFound($mac);
					break;
				case strtolower('mac_invalid:'.$mac):
					return ProvisioningRPCResult::macAddressInvalid($mac);
					break;
			}
		}
		
		return ProvisioningRPCResult::unknownError($mac);
	}
	
	/**
	 * @param string $macid
	 * @param string $url
	 * @return ProvisioningRPCResult
	 */
	public function addPhone(string $macid, string $url, bool $overwrite = true) : ProvisioningRPCResult
	{
		// FORMAT MAC
		
		$macid = parent::formatMacAddress($macid);
		if(strlen($macid)!=12 && strlen($macid)!=17) {
			return ProvisioningRPCResult::macAddressInvalid($macid);
		}
		
		// CHECK PHONE
		
		$check = self::checkPhone($macid);
		
		if(!in_array($check->code, [ProvisioningRPCResult::macNotFound, ProvisioningRPCResult::resultSucceeded])) {
			return $check;
		}
		
		if($check->code==ProvisioningRPCResult::resultSucceeded) {
			if($overwrite==true) {
				$delete = self::removePhone($macid);
				if($delete->code!=ProvisioningRPCResult::resultSucceeded) {
					return $delete;
				}
			}
			else {
				return ProvisioningRPCResult::macAddressAlreadyExists($mac);
			}
		}
		
		// XMLRPC CALL
		
		try {
			$response = $this->client->post('/apxml/rpc.do',[
					'auth' => $this->client_auth,
					'headers' => $this->client_headers,
					'body' => xmlrpc_encode_request("autoprov.registerDevice", [strtoupper($macid), $url, $this->profile_name]),
			]);
			
			$xmlrpc = $response->getBody()->getContents();
		}
		catch (\GuzzleHttp\Exception\ClientException $e) {
			return ProvisioningRPCResult::connectionError($mac, $e->getMessage());
		}
		
		$data = xmlrpc_decode($xmlrpc);
		
		$mac = strlen($macid)==17 ? substr($macid, 0, 12) : $macid;
		
		if(isset($data[0]) && $data[0]) {
			return ProvisioningRPCResult::macAddressAdded($mac);
		}
		
		if(isset($data[1])) {
			switch (strtolower($data[1])) {
				case strtolower('mac_not_found:'.$mac):
					return ProvisioningRPCResult::macAddressNotFound($mac);
					break;
				case strtolower('mac_invalid:'.$mac):
				case strtolower('mac_not_exist:'.$mac):
					return ProvisioningRPCResult::macAddressInvalid($mac);
					break;
				case strtolower('mac_already_in_use:'.$mac):
					return ProvisioningRPCResult::macAddressOwnedBySomeoneElse($mac);
					break;
				case strtolower('url_invalid:'.$url):
					return ProvisioningRPCResult::provisioningUrlInvalid($mac, $url);
					break;
				case strtolower('name_invalid:'.$this->profile_name):
					return ProvisioningRPCResult::provisioningProfileNameInvalid($mac, $this->profile_name);
					break;
			}
		}
		
		return ProvisioningRPCResult::unknownError($mac);
	}
	
	/**
	 * @param string $macid
	 * @return ProvisioningRPCResult
	 */
	public function removePhone(string $macid) : ProvisioningRPCResult
	{
		// FORMAT MAC
		
		$macid = parent::formatMacAddress($macid);
		if(strlen($macid)!=12 && strlen($macid)!=17) {
			return ProvisioningRPCResult::macAddressInvalid($macid);
		}
		
		// CHECK PHONE
		
		$check = self::checkPhone($macid);
		
		if($check->code!==ProvisioningRPCResult::resultSucceeded) {
			return $check;
		}
		
		// XMLRPC CALL
		
		try {
			$response = $this->client->post('/apxml/rpc.do',[
					'auth' => $this->client_auth,
					'headers' => $this->client_headers,
					'body' => xmlrpc_encode_request("autoprov.deregisterDevice", strtoupper($macid)),
			]);
			
			$xmlrpc = $response->getBody()->getContents();
		}
		catch (\GuzzleHttp\Exception\ClientException $e) {
			return ProvisioningRPCResult::connectionError($mac, $e->getMessage());
		}
		
		$data = xmlrpc_decode($xmlrpc);
		
		$mac = strlen($macid)==17 ? substr($macid, 0, 12) : $macid;
		
		if(isset($data[1])) {
			switch (strtolower($data[1])) {
				case strtolower('ok'):
					return ProvisioningRPCResult::macAddressRemoved($mac);
					break;
				case strtolower('mac_not_found:'.$mac):
					return ProvisioningRPCResult::macAddressNotFound($mac);
					break;
				case strtolower('mac_invalid:'.$mac):
				case strtolower('mac_not_exist:'.$mac):
					return ProvisioningRPCResult::macAddressInvalid($mac);
					break;
			}
		}
		
		return ProvisioningRPCResult::unknownError($mac);
	}
}