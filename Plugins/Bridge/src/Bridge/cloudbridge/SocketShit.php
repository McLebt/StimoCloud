<?php
/* Copyright (c) 2020 Florian H. All rights reserved. */
namespace Bridge\cloudbridge;
use pocketmine\scheduler\ClosureTask;
use raklib\server\UDPServerSocket;
use raklib\utils\InternetAddress;
use Bridge\cloudbridge\event\cloud\CloudPacketReceive;
use Bridge\cloudbridge\packets\ConsoleTextPacket;
use Bridge\cloudbridge\packets\DataPacket;
use Bridge\cloudbridge\packets\LoginPacket;
use Bridge\cloudbridge\packets\RequestPacket;


/**
 * Class SocketShit
 * @package Bridge\cloudbridge
 * @author Florian H.
 * @date 01.08.2020 - 22:59
 * @project CloudServer
 */
class SocketShit{
	/** @var resource */
	static $cloudSocket   = null;
	/** @var InternetAddress */
	static $cloudAddress  = null;
	/** @var bool */
	static $closed        = true;

	/** @var resource */
	static $serverSocket  = null;
	/** @var InternetAddress */
	static $serverAddress = null;



	/**
	 * SocketShit constructor.
	 * @param InternetAddress $cloudAddress
	 * @param InternetAddress $serverAddress
	 */
	public function __construct(InternetAddress $cloudAddress, InternetAddress $serverAddress){
		WhitelistedOfTrustedServers::registerServer($cloudAddress);

		self::$cloudAddress = $cloudAddress;
		self::$serverAddress = $serverAddress;
		self::$serverSocket = new UDPServerSocket(self::$serverAddress);

		Main::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (int $currentTick): void{$this->onTick($currentTick);}), 1);
	}

	/**
	 * Function onTick
	 * @param int $tick
	 * @return void
	 */
	private function onTick(int $currentTick): void{
		if (!self::$closed) {
			$receivedAddress = self::$cloudAddress;
			$len = 1;
			while (!$len === false) {
				$len = self::$serverSocket->readPacket($buffer, $receivedAddress->ip, $receivedAddress->port);
				if (!$len === false) {
					if (!WhitelistedOfTrustedServers::isWhitelisted($receivedAddress)) {
						Main::getInstance()->getLogger()->alert("Received Packet from a server({$receivedAddress->getIp()}:{$receivedAddress->getPort()}) that isn't the Cloud.");
						return;
					}
					Main::getInstance()->getLogger()->warning("Received Packet.");
					$packet = PacketPool::getPacket($buffer);
					if (!is_null($packet)) {
						$packet->decode();
						var_dump($packet);

						if ($packet instanceof ConsoleTextPacket) {
							Main::getInstance()->getLogger()->info("§e[ §b{$packet->sender} §e]	§f{$packet->message}");
						}
						$ev = new CloudPacketReceive($packet);
						$ev->call();
					}
				}
			}
		}
	}

	/**
	 * Function connect
	 * @return bool
	 */
	public static function connectToCloud(): bool{
		if (self::$closed) {
			try {
				self::$cloudSocket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
				$socket = socket_connect(self::$cloudSocket, self::$cloudAddress->getIp(), self::$cloudAddress->getPort());
				if (!$socket) {
					return false;
				} else {
					self::$closed = false;
					return true;
				}
			} catch(\Exception $e) {
				Main::getInstance()->getLogger()->logException($e);
				return false;
			}
		}
		return false;
	}

	/**
	 * Function sendPacket
	 * @param DataPacket $pk
	 * @return void
	 */
	public static function sendPacket(DataPacket $packet): void{
		if ($packet instanceof RequestPacket) {
			$packet->requestid = ServerManager::getServerId();
			$packet->type = RequestPacket::TYPE_REQUEST;
		}
		if (!self::$closed) {
			$packet->encode();
			$buffer = $packet->getBuffer();
			socket_write(self::$cloudSocket, $buffer, strlen($buffer));
		} else {
			throw new \Exception("Selected Socket is not available.");
		}
	}

	/**
	 * Function sendDelayPacket
	 * @param DataPacket $pk
	 * @param null|int $seconds
	 * @return void
	 */
	public static function sendDelayPacket(DataPacket $packet, ?int $seconds = 1): void{
		if ($packet instanceof RequestPacket) {
			$packet->requestid = ServerManager::getServerId();
			$packet->type = RequestPacket::TYPE_REQUEST;
		}
		if (!self::$closed) {
			$packet->encode();
			$buffer = $packet->getBuffer();
			$function = function (int $currentTick) use ($buffer): void{
				socket_write(self::$cloudSocket, $buffer, strlen($buffer));
			};
			Main::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask($function), 20 * $seconds);
		} else {
			throw new \Exception("Selected Socket is not available.");
		}
	}
}
