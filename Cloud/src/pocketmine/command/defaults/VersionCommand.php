<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

declare(strict_types=1);

namespace pocketmine\command\defaults;

use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;
use function count;
use function implode;
use function stripos;
use function strtolower;

class VersionCommand extends VanillaCommand {

    public function __construct(string $name) {
        parent::__construct(
            $name,
            "Shows Version",
            "/versions",
            ["ver", "about"]
        );
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if (count($args) === 0) {
            $sender->sendMessage(
                $sender->getServer()->getName() . "\n" .
                $sender->getServer()->getPocketMineVersion()
            );
        } else {
            $pluginName = implode(" ", $args);
            $exactPlugin = $sender->getServer()->getPluginManager()->getPlugin($pluginName);

            if ($exactPlugin instanceof Plugin) {
                $this->describeToSender($exactPlugin, $sender);

                return true;
            }

            $found = false;
            $pluginName = strtolower($pluginName);
            foreach ($sender->getServer()->getPluginManager()->getPlugins() as $plugin) {
                if (stripos($plugin->getName(), $pluginName) !== false) {
                    $this->describeToSender($plugin, $sender);
                    $found = true;
                }
            }

            if (!$found) {
                $sender->sendMessage("No such plugin");
            }
        }

        return true;
    }

    private function describeToSender(Plugin $plugin, CommandSender $sender) {
        $desc = $plugin->getDescription();
        $sender->sendMessage(TextFormat::DARK_GREEN . $desc->getName() . TextFormat::WHITE . " version " . TextFormat::DARK_GREEN . $desc->getVersion());

        if ($desc->getDescription() !== "") {
            $sender->sendMessage($desc->getDescription());
        }

        if ($desc->getWebsite() !== "") {
            $sender->sendMessage("Website: " . $desc->getWebsite());
        }

        if (count($authors = $desc->getAuthors()) > 0) {
            if (count($authors) === 1) {
                $sender->sendMessage("Author: " . implode(", ", $authors));
            } else {
                $sender->sendMessage("Authors: " . implode(", ", $authors));
            }
        }
    }
}
