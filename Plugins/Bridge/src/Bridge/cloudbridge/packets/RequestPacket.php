<?php
/* Copyright (c) 2020 Florian H. All rights reserved. */
namespace Bridge\cloudbridge\packets;


/**
 * Class RequestPacket
 * @package Bridge\cloudbridge\packets
 * @author Florian H.
 * @date 01.08.2020 - 21:27
 * @project CloudServer
 */
class RequestPacket extends Packet{
	/** @var int */
	public $type;
	/** @var string */
	public $requestid;
}
