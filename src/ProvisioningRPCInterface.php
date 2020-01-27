<?php

namespace Peoplefone;

interface ProvisioningRPCInterface
{
	/**
	 * @param string $mac
	 * @return ProvisioningRPCResult
	 */
	public function checkPhone(string $mac) : ProvisioningRPCResult;
	
	/**
	 * @param string $mac
	 * @param string $url
	 * @return ProvisioningRPCResult
	 */
	public function addPhone(string $mac, string $url, bool $overwrite) : ProvisioningRPCResult;
	
	/**
	 * @param string $mac
	 * @return ProvisioningRPCResult
	 */
	public function removePhone(string $mac) : ProvisioningRPCResult;
}