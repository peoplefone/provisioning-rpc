<?php

namespace Peoplefone;

class ProvisioningRPCDeviceYealink extends ProvisioningRPCXML
{
	private $client_url = 'https://api-dm.yealink.com:8443';
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
                    'body' => parent::createXml('redirect.checkDevice', [$mac])
			]);
			
			$xmlrpc = $response->getBody()->getContents();
		}
		catch (\GuzzleHttp\Exception\ClientException $e) {
			return ProvisioningRPCResult::connectionError($mac, $e->getMessage());
		}
		
		$data = $this->decodeXml($xmlrpc);
		
		if(isset($data[1])) {
			switch (strtolower($data[1])) {
				case strtolower('registered'):
					return ProvisioningRPCResult::macAddressFound($mac);
					break;
				case strtolower('unregistered'):
				case strtolower('unknown'):
					return ProvisioningRPCResult::macAddressNotFound($mac);
					break;
				case strtolower('error:invalid mac'):
					return ProvisioningRPCResult::macAddressInvalid($mac);
					break;
				case strtolower('registered elsewhere'):
					return ProvisioningRPCResult::macAddressOwnedBySomeoneElse($mac);
					break;
			}
		}
		
		return ProvisioningRPCResult::unknownError($mac);
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
                    'body' => parent::createXml('redirect.registerDevice', [$mac, $url, '1'])
					// 1 = force overriding as we checked the mac first
			]);
			
			$xmlrpc = $response->getBody()->getContents();
		}
		catch (\GuzzleHttp\Exception\ClientException $e) {
			return ProvisioningRPCResult::connectionError($mac, $e->getMessage());
		}
		
		$data = $this->decodeXml($xmlrpc);
		
		if(isset($data[1])) {
			switch (strtolower($data[1])) {
				case strtolower('ok'):
					return ProvisioningRPCResult::macAddressAdded($mac);
					break;
				case strtolower('error:invalid mac(s):'.$mac):
					return ProvisioningRPCResult::macAddressInvalid($mac);
					break;
				case strtolower('error:invalid server'):
				case strtolower("error:the server name can only contain 'A-z 0-9 - _ . '(include space)"):
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
            $response = $this->client->post('/xmlrpc/', [
                'auth' => $this->client_auth,
                'headers' => $this->client_headers,
                'body' => parent::createXml('redirect.deRegisterDevice', [$mac])
            ]);
			
			$xmlrpc = $response->getBody()->getContents();
		}
		catch (\GuzzleHttp\Exception\ClientException $e) {
			return ProvisioningRPCResult::connectionError($mac, $e->getMessage());
		}
		
		$data = $this->decodeXml($xmlrpc);
		
		if(isset($data[1])) {
			switch (strtolower($data[1])) {
				case strtolower('ok'):
					return ProvisioningRPCResult::macAddressRemoved($mac);
					break;
				case strtolower('error:invalid mac(s):'.$mac):
					return ProvisioningRPCResult::macAddressInvalid($mac);
					break;
			}
		}
		
		return ProvisioningRPCResult::unknownError($mac);
	}
}
