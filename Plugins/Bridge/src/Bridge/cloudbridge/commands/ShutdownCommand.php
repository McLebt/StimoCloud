<?php
/* Copyright (c) 2021 Florian H. All rights reserved. */

namespace Bridge\cloudbridge\commands;

use Bridge\cloudbridge\Main;
use Bridge\cloudbridge\SocketShit;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Server;

class ShutdownCommand extends Command {

    public function __construct(string $name)
    {
        parent::__construct($name, "Shutdown Command", "/shutdown", ["kill"]);
        $this->setPermission("bridge.shutdown");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        if (!$this->testPermission($sender)){
            $sender->sendMessage("§cYou don't have the permission to execute this command.");
            return false;
        }
        try {
            SocketShit::$cloudSocket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
            $socket = socket_connect(SocketShit::$cloudSocket, SocketShit::$cloudAddress->getIp(), SocketShit::$cloudAddress->getPort());
            if ($socket) {
                SocketShit::$serverSocket->close();
            }
        } catch (\ErrorException $exception) {
            Server::getInstance()->getLogger()->logException($exception);
        }

        Server::getInstance()->forceShutdown();

        return false;
    }

}
