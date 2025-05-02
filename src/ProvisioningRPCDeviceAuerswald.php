<?php

namespace Peoplefone;

class ProvisioningRPCDeviceAuerswald extends ProvisioningRPCXML
{
	private $client_url = 'https://provisioning.auerswald.de';
	private $client_headers = ['Content-Type'=>'text/xml'];
	private $client_auth;
	
	/**
	 * @param array $client_auth
	 */
	public function __construct(array $client_auth=['username','password'])
	{
		parent::__construct($this->client_url);
		$this->client_auth = $client_auth;
	}
	
	/**
	 * @param string $mac
	 * @return ProvisioningRPCResult
	 */
	public function checkPhone(string $mac) : ProvisioningRPCResult
	{
		// FORMAT MAC
		
		$mac = parent::formatMacAddress($mac);
		if(strlen($mac)!=12) {
			return ProvisioningRPCResult::macAddressInvalid($mac);
		}
		
		// XMLRPC CALL
		
		try {
			$response = $this->client->post('/xmlrpc/',[
					'auth' => $this->client_auth,
					'headers' => $this->client_headers,
                    'body' => parent::createXml('DeviceInfo', [$mac])
			]);
			
			$xmlrpc = $response->getBody()->getContents();
		}
		catch (\GuzzleHttp\Exception\ClientException $e) {
			return ProvisioningRPCResult::connectionError($mac, $e->getMessage());
		}
		
		$data = $this->decodeXml($xmlrpc);
		
		if(is_array($data) && isset($data['mac'])) {
			return ProvisioningRPCResult::macAddressFound($mac);
		}
		else {
			return ProvisioningRPCResult::macAddressNotFound($mac);
		}
	}
	
	/**
	 * @param string $mac
	 * @param string $url
	 * @return ProvisioningRPCResult
	 */
	public function addPhone(string $mac, string $url, bool $overwrite = true) : ProvisioningRPCResult
	{
		// FORMAT MAC
		
		$mac = parent::formatMacAddress($mac);
		if(strlen($mac)!=12) {
			return ProvisioningRPCResult::macAddressInvalid($mac);
		}
		
		// CHECK PHONE
		
		$check = self::checkPhone($mac);
		if(!in_array($check->code, [ProvisioningRPCResult::macNotFound, ProvisioningRPCResult::resultSucceeded])) {
			return $check;
		}
		
		if($check->code==ProvisioningRPCResult::resultSucceeded && $overwrite==false) {
			return ProvisioningRPCResult::macAddressAlreadyExists($mac);
		}
		
		// XMLRPC CALL
		
		try {
			$response = $this->client->post('/xmlrpc/',[
					'auth' => $this->client_auth,
					'headers' => $this->client_headers,
                    'body' => parent::createXml('DeviceRegister', [$mac, $url])
			]);
			
			$xmlrpc = $response->getBody()->getContents();
		}
		catch (\GuzzleHttp\Exception\ClientException $e) {
			return ProvisioningRPCResult::connectionError($mac, $e->getMessage());
		}
		
		$data = $this->decodeXml($xmlrpc);
		
		if($data) {
			return ProvisioningRPCResult::macAddressAdded($mac);
		}
		else {
			return ProvisioningRPCResult::macAddressOwnedBySomeoneElse($mac);
		}
	}
	
	/**
	 * @param string $mac
	 * @return ProvisioningRPCResult
	 */
	public function removePhone(string $mac) : ProvisioningRPCResult
	{
		// FORMAT MAC
		
		$mac = parent::formatMacAddress($mac);
		if(strlen($mac)!=12) {
			return ProvisioningRPCResult::macAddressInvalid($mac);
		}
		
		// CHECK PHONE
		
		$check = self::checkPhone($mac);
		if($check->code!==ProvisioningRPCResult::resultSucceeded) {
			return $check;
		}
		
		// XMLRPC CALL
		
		try {
			$response = $this->client->post('/xmlrpc/',[
					'auth' => $this->client_auth,
					'headers' => $this->client_headers,
                    'body' => parent::createXml('DeviceDeregister', [$mac])
			]);
			
			$xmlrpc = $response->getBody()->getContents();
		}
		catch (\GuzzleHttp\Exception\ClientException $e) {
			return ProvisioningRPCResult::connectionError($mac, $e->getMessage());
		}
		
		$data = $this->decodeXml($xmlrpc);
		
		if($data) {
			return ProvisioningRPCResult::macAddressRemoved($mac);
		}
		else {
			return ProvisioningRPCResult::macAddressOwnedBySomeoneElse($mac);
		}
	}
}
