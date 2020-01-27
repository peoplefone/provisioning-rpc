<?php

namespace Peoplefone;

abstract class ProvisioningRPCXML implements ProvisioningRPCInterface
{
	protected $client;
	
	public function __construct($base_uri)
	{
		$this->client = new \GuzzleHttp\Client([
				'base_uri' => $base_uri,
		]);
	}
	
	/**
	 * @param string $mac
	 * @return string
	 */
	public function formatMacAddress(string $mac) : string
	{
		$macid = null;
		
		if(substr_count($mac, '-')==1) // GIGASET
		{
			list($mac, $macid) = explode("-", $mac);
		}
		
		$mac = preg_replace("/[^a-f0-9\-]/", "", strtolower($mac));
		
		return strlen($macid)>0 ? $mac."-".$macid : $mac;
	}
}