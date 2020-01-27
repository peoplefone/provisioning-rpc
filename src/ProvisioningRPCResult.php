<?php

namespace Peoplefone;

class ProvisioningRPCResult
{
	public const connectionError = -20;
	public const unknownError = -10;
	public const resultSucceeded = 1;
	public const macNotFound = 0;
	public const macInvalid = -1;
	public const macOwnedBySomeoneElse = -2;
	public const macAlreadyExists = -3;
	public const urlInvalid = -4;
	public const profileNameInvalid = -5;
	
	public $mac;
	public $result;
	public $code;
	public $message;
	
	public function __construct($mac, $result, $code, $message) {
		$this->mac = strtoupper($mac);
		$this->result = $result;
		$this->code = $code;
		$this->message = $message;
	}
	
	public static function connectionError($mac, $message) {
		return new static($mac, false, self::connectionError, preg_replace( "/\r|\n/", "", $message));
	}
	
	public static function unknownError($mac) {
		return new static($mac, false, self::unknownError, 'Unknown error');
	}
	
	public static function macAddressAdded($mac) {
		return new static($mac, true, self::resultSucceeded, 'The MAC Address has been added');
	}
	
	public static function macAddressAlreadyExists($mac) {
		return new static($mac, true, self::macAlreadyExists, 'The MAC Address has already been added');
	}
	
	public static function macAddressRemoved($mac) {
		return new static($mac, true, self::resultSucceeded, 'The MAC Address has been removed');
	}
	
	public static function macAddressFound($mac) {
		return new static($mac, true, self::resultSucceeded, 'The MAC Address is configured');
	}
	
	public static function macAddressNotFound($mac) {
		return new static($mac, false, self::macNotFound, 'The MAC Address is not configured');
	}
	
	public static function macAddressInvalid($mac) {
		return new static($mac, false, self::macInvalid, 'The MAC Address is invalid');
	}
	
	public static function macAddressOwnedBySomeoneElse($mac) {
		return new static($mac, false, self::macOwnedBySomeoneElse, 'The MAC Address is owned by someone else');
	}
	
	public static function provisioningUrlInvalid($mac, $url) {
		return new static($mac, false, self::urlInvalid, 'The Provisioning URL ('.$url.') is invalid');
	}
	
	public static function provisioningProfileNameInvalid($mac, $profile_name) {
		return new static($mac, false, self::profileNameInvalid, 'The Profile Name ('.$profile_name.') is invalid');
	}
}