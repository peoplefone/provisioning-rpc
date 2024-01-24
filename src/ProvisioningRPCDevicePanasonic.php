<?php

namespace Peoplefone;

class ProvisioningRPCDevicePanasonic extends ProvisioningRPCXML
{
	private $client_url = 'https://provisioning.e-connecting.net';
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
			$response = $this->client->post('/redirect/xmlrpc',[
					'auth' => $this->client_auth,
					'headers' => $this->client_headers,
                    'body' => parent::createXml('ipredirect.checkPhones', [$mac])
			]);
			
			$xmlrpc = $response->getBody()->getContents();
		}
		catch (\GuzzleHttp\Exception\ClientException $e) {
			return ProvisioningRPCResult::connectionError($mac, $e->getMessage());
		}
		
		$data = xmlrpc_decode($xmlrpc);
		
		if(isset($data[0]) && isset($data[0]['faultCode'])) {
			switch ($data[0]['faultCode']) {
				case 0:
					return ProvisioningRPCResult::macAddressFound($mac);
					break;
				case 201:
					return ProvisioningRPCResult::macAddressNotFound($mac);
					break;
				case 200:
					return ProvisioningRPCResult::macAddressInvalid($mac);
					break;
				case 202:
					return ProvisioningRPCResult::macAddressOwnedBySomeoneElse($mac);
					break;
			}
		}
		
		return ProvisioningRPCResult::unknownError($mac);
	}
	
	/**
	 * 
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
			$response = $this->client->post('/redirect/xmlrpc',[
					'auth' => $this->client_auth,
					'headers' => $this->client_headers,
                    'body' => parent::createXml('ipredirect.registerPhone', [$mac, $url])
			]);
			
			$xmlrpc = $response->getBody()->getContents();
		}
		catch (\GuzzleHttp\Exception\ClientException $e) {
			return ProvisioningRPCResult::connectionError($mac, $e->getMessage());
		}
		
		$data = xmlrpc_decode($xmlrpc);
		
		if($data===true) {
			return ProvisioningRPCResult::macAddressAdded($mac);
		}
		
		if(isset($data['faultCode'])) {
			switch ($data['faultCode']) {
				case 200:
				case 201:
					return ProvisioningRPCResult::macAddressInvalid($mac);
					break;
				case 202:
					return ProvisioningRPCResult::macAddressOwnedBySomeoneElse($mac);
					break;
				case 100:
					return ProvisioningRPCResult::provisioningUrlInvalid($mac, $url);
					break;
			}
		}
		
		return ProvisioningRPCResult::unknownError($mac);
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
			$response = $this->client->post('/redirect/xmlrpc',[
					'auth' => $this->client_auth,
					'headers' => $this->client_headers,
                    'body' => parent::createXml('ipredirect.unregisterPhone', [$mac])
			]);
			
			$xmlrpc = $response->getBody()->getContents();
		}
		catch (\GuzzleHttp\Exception\ClientException $e) {
			return ProvisioningRPCResult::connectionError($mac, $e->getMessage());
		}
		
		$data = xmlrpc_decode($xmlrpc);
		
		if($data===true) {
			return ProvisioningRPCResult::macAddressRemoved($mac);
		}
		
		if(isset($data['faultCode'])) {
			switch ($data['faultCode']) {
				case 200:
					return ProvisioningRPCResult::macAddressInvalid($mac);
					break;
				case 201:
					return ProvisioningRPCResult::macAddressNotFound($mac);
					break;
				case 202:
					return ProvisioningRPCResult::macAddressOwnedBySomeoneElse($mac);
					break;
			}
		}
		
		return ProvisioningRPCResult::unknownError($mac);
	}
}
