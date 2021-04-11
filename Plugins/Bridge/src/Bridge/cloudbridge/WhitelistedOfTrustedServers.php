<?php
/* Copyright (c) 2020 Florian H. All rights reserved. */
namespace Bridge\cloudbridge;
use raklib\utils\InternetAddress;


/**
 * Class WhitelistedOfTrustedServers
 * @package Bridge\cloudbridge
 * @author Florian H.
 * @date 02.08.2020 - 00:02
 * @project CloudServer
 */
class WhitelistedOfTrustedServers{
	/** @var InternetAddress */
	protected static $list = [];

	/**
	 * Function registerServer
	 * @param InternetAddress $address
	 * @return void
	 * @throws \Exception
	 */
	static function registerServer(InternetAddress $address): void{
		if (isset(self::$list[$address->getIp()][$address->getPort()])) {
			throw new \Exception("Server {$address->getIp()}:{$address->getPort()} is already added in our whitelist.");
		}
		if (!isset(self::$list[$address->getIp()])) {
			self::$list[$address->getIp()] = [];
		}
		self::$list[$address->getIp()][] = $address->getPort();
	}

	/**
	 * Function unregisterServer
	 * @param InternetAddress $address
	 * @return void
	 * @throws \Exception
	 */
	static function unregisterServer(InternetAddress $address): void{
		if (isset(self::$list[$address->getIp()]) && isset(self::$list[$address->getIp()][$address->getPort()])) {
			unset(self::$list[$address->getIp()][$address->getPort()]);
		} else {
			throw new \Exception("Server {$address->getIp()}:{$address->getPort()} not found in our whitelist.");
		}
	}

	/**
	 * Function isWhitelisted
	 * @param InternetAddress $address
	 * @return bool
	 */
	static function isWhitelisted(InternetAddress $address): bool{
		return isset(self::$list[$address->getIp()][$address->getPort()]);
	}
}
