<?php
/* Copyright (c) 2020 Florian H. All rights reserved. */
namespace Bridge\cloudbridge\event\cloud;
use pocketmine\event\Event;
use Bridge\cloudbridge\packets\DataPacket;


/**
 * Class CloudPacketReceive
 * @package Bridge\cloudbridge
 * @author Florian H.
 * @date 02.08.2020 - 00:45
 * @project CloudServer
 */
class CloudPacketReceive extends Event{
	/** @var DataPacket */
	protected $packet;


	/**
	 * CloudPacketReceive constructor.
	 * @param DataPacket $packet
	 */
	public function __construct(DataPacket $packet){
		$this->packet = $packet;
	}

	/**
	 * Function getPacket
	 * @return DataPacket
	 */
	public function getPacket(): DataPacket{
		return $this->packet;
	}
}
