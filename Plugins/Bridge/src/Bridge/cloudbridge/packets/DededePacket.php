<?php
/* Copyright (c) 2020 xxAROX. All rights reserved. */
namespace Bridge\cloudbridge\packets;


/**
 * Class DededePacket
 * @package Bridge\cloudbridge\packets
 * @author xxAROX
 * @date 18.06.2020 - 22:19
 * @project StimoCloud
 */
class DededePacket extends RequestPacket{
	public const NETWORK_ID = self::PACKET_DISCONNECT;

	/** @var int */
	public $reason = 0;
	/** @var string */
	public $requestId = "";

	public const REASON_UNKNOWN         = 0;
	public const REASON_SERVER_SHUTDOWN = 1;
	public const REASON_WRONG_PASSWORD  = 2;
	public const REASON_CLOUD_SHUTDOWN  = 3;



	/**
	 * Function decodePayload
	 * @return void
	 */
	protected function decodePayload(): void{
		$this->requestId = $this->getString();
		$this->reason = $this->getInt();
	}

	/**
	 * Function encodePayload
	 * @return void
	 */
	protected function encodePayload(): void{
		$this->putString($this->requestId);
		$this->putInt($this->reason);
	}
}
