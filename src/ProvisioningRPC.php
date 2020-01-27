<?php

namespace Peoplefone;

abstract class ProvisioningRPC
{
	public static function connect($model, $login)
	{
		$classname = get_class().'Device'.ucfirst(strtolower($model));
		
		try
		{
			return new $classname($login);
		}
		catch (\Throwable $t)
		{
			die($t->getMessage().PHP_EOL);
		}
	}
}