<?php

namespace Peoplefone;

class ProvisioningRPCDeviceSnom extends ProvisioningRPCXML
{
	private $client_url = 'https://secure-provisioning.snom.com:8083';
	private $client_headers = ['Content-Type'=>'text/xml'];
	private $client_auth;
	private $phone_types = [
			'snom300', 'snom320', 'snom360', 'snom370',
			'snom710', 'snom715', 'snom720', 'snom725', 'snom760',
			'snom820', 'snom821', 'snom870',
			'snomC520', 'snomC620',
			'snomD120', 'snomD305', 'snomD315', 'snomD335', 'snomD345', 'snomD375', 'snomD385',
			'snomD712', 'snomD717', 'snomD735', 'snomD745', 'snomD765', 'snomD785',
			'snomM100', 'snomM100KLE', 'snomM200SC', 'snomM300', 'snomM400SC', 'snomM700', 'snomM900',
	];
	private $phone_type = 'snom300';
	
	/**
	 * @param array $client_auth
	 */
	public function __construct(array $client_auth=['username','password'])
	{
		parent::__construct($this->client_url);
		$this->client_auth = $client_auth;
	}
	
	/**
	 * @return array
	 */
	public function getPhoneTypes() : array
	{
		return $this->phone_types;
	}
	
	/**
	 * @param string $phone_type
	 */
	public function setPhoneType(string $phone_type)
	{
		if(in_array($phone_type, $this->phone_types)) {
			$this->phone_type = $phone_type;
		}
	}
	
	/**
	 * @param string $mac
	 * @return ProvisioningRPCResult
	 */
	public function checkPhone($mac) : ProvisioningRPCResult
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
                    'body' => parent::createXml('redirect.checkPhone', [$mac])
			]);
			
			$xmlrpc = $response->getBody()->getContents();
		}
		catch (\GuzzleHttp\Exception\ClientException $e) {
			return ProvisioningRPCResult::connectionError($mac, $e->getMessage());
		}
		
		$data = xmlrpc_decode($xmlrpc);
		
		if(isset($data[1])) {
			switch (strtolower($data[1])) {
				case strtolower('ok'):
					return ProvisioningRPCResult::macAddressFound($mac);
					break;
				case strtolower('error:no_such_mac'):
					return ProvisioningRPCResult::macAddressNotFound($mac);
					break;
				case strtolower('error:malformed_mac'):
					return ProvisioningRPCResult::macAddressInvalid($mac);
					break;
				case strtolower('error:owned_by_other_user'):
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
                    'body' => parent::createXml('redirect.registerPhone', [$mac, $url])
			]);
			
			$xmlrpc = $response->getBody()->getContents();
		}
		catch (\GuzzleHttp\Exception\ClientException $e) {
			return ProvisioningRPCResult::connectionError($mac, $e->getMessage());
		}
		
		$data = xmlrpc_decode($xmlrpc);
		
		if(isset($data[1])) {
			switch (strtolower($data[1])) {
				case strtolower('ok'):
					return ProvisioningRPCResult::macAddressAdded($mac);
					break;
				case strtolower('error:malformed_url'):
					return ProvisioningRPCResult::provisioningUrlInvalid($mac, $url);
					break;
				case strtolower('error:malformed_mac'):
					return ProvisioningRPCResult::macAddressInvalid($mac);
					break;
				case strtolower('error:owned_by_other_user'):
					return ProvisioningRPCResult::macAddressOwnedBySomeoneElse($mac);
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
			$response = $this->client->post('/xmlrpc/',[
					'auth' => $this->client_auth,
					'headers' => $this->client_headers,
                    'body' => parent::createXml('redirect.deregisterPhone', [$mac])
			]);
			
			$xmlrpc = $response->getBody()->getContents();
		}
		catch (\GuzzleHttp\Exception\ClientException $e) {
			return ProvisioningRPCResult::connectionError($mac, $e->getMessage());
		}
		
		$data = xmlrpc_decode($xmlrpc);
		
		if(isset($data[1])) {
			switch (strtolower($data[1])) {
				case strtolower('ok'):
					return ProvisioningRPCResult::macAddressRemoved($mac);
					break;
				case strtolower('error:no_such_mac'):
					return ProvisioningRPCResult::macAddressNotFound($mac);
					break;
				case strtolower('error:malformed_mac'):
					return ProvisioningRPCResult::macAddressInvalid($mac);
					break;
				case strtolower('error:owned_by_other_user'):
					return ProvisioningRPCResult::macAddressOwnedBySomeoneElse($mac);
					break;
			}
		}
		
		return ProvisioningRPCResult::unknownError($mac);
	}
}
