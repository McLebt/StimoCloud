<?php
/* Copyright (c) 2020 Florian H. All rights reserved. */
namespace Bridge\cloudbridge;
use pocketmine\utils\Binary;
use Bridge\cloudbridge\packets\AcceptConnectionPacket;
use Bridge\cloudbridge\packets\ConsoleTextPacket;
use Bridge\cloudbridge\packets\DataPacket;
use Bridge\cloudbridge\packets\DededePacket;
use Bridge\cloudbridge\packets\LoginPacket;
use Bridge\cloudbridge\packets\StartPrivateServerPacket;
use Bridge\cloudbridge\packets\StartServerPacket;
use Bridge\cloudbridge\packets\StopServerGroupPacket;


/**
 * Class PacketPool
 * @package Bridge\cloudbridge
 * @author xxAROX
 * @date 01.08.2020 - 23:31
 * @project CloudServer
 */
class PacketPool{
	/** @var \SplFixedArray<DataPacket> */
	protected static $pool = null;

	public static function init() {
		static::$pool = new \SplFixedArray(256);

		self::registerPacket(new LoginPacket());
		self::registerPacket(new AcceptConnectionPacket());
		self::registerPacket(new ConsoleTextPacket());
		self::registerPacket(new DededePacket());
		self::registerPacket(new StartServerPacket());
		self::registerPacket(new StartPrivateServerPacket());
		self::registerPacket(new StopServerGroupPacket());
	}

	public static function registerPacket(DataPacket $packet) {
		static::$pool[$packet->pid()] = clone $packet;
	}

	public static function getPacketById(int $pid): ?DataPacket {
		return isset(static::$pool[$pid]) ? clone static::$pool[$pid] : null;
	}

	public static function getPacket(string $buffer): ?DataPacket {
		$offset = 0;
		$pk = static::getPacketById(Binary::readUnsignedVarInt($buffer, $offset) & DataPacket::PID_MASK);
		if (!is_null($pk)) {
			$pk->setBuffer($buffer, $offset);
			return $pk;
		}
		return null;
	}
}
