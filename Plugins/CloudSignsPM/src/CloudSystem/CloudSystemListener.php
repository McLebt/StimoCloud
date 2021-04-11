<?php

namespace CloudSystem;

use CloudSystem\groupSigns\GroupSigns;
use CloudSystem\tasks\HitDelayTask;
use Core\Core\api\WaterdogAPI;
use Core\Core\CorePlayer;
use pocketmine\event\level\LevelLoadEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\network\mcpe\protocol\ScriptCustomEventPacket;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\snooze\ThreadedSleeper;
use pocketmine\tile\Sign;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Binary;

class CloudSystemListener implements Listener{
    public $plugin;

    public function __construct(CloudSystem $plugin) {
        $this->plugin = $plugin;
    }

    public function onJoin(PlayerJoinEvent $event){
        $player = $event->getPlayer();
        $name = $player->getName();
        if (in_array($name, $this->plugin->delay)) {
            $key = array_search($name, $this->plugin->delay);
            unset($this->plugin->delay[$key]);
        }
    }

    public static function transfer(Player $player, String $server): bool
    {
        $pk = new ScriptCustomEventPacket();
        $pk->eventName = "bungeecord:main";
        $pk->eventData = Binary::writeShort(strlen("Connect")) . "Connect" . Binary::writeShort(strlen($server)) . $server;
        $player->sendDataPacket($pk);
        return true;
    }

    public function onInteract(PlayerInteractEvent $event)
    {
        $player = $event->getPlayer();

        if (!$player instanceof CorePlayer){
        	return;
		}

        $name = $player->getName();
        $block = $event->getBlock();
        $tile = Server::getInstance()->getDefaultLevel()->getTile($block);
        if (!in_array($player->getName(), $this->plugin->delay)) {
            $this->plugin->delay[$player->getName()] = $player->getName();
            $this->plugin->getScheduler()->scheduleDelayedTask(new HitDelayTask($this->plugin, $player->getName()), 20 * 1);
            if ($tile instanceof Sign) {
                if ($name === $this->plugin->who) {
                    if ($this->plugin->mode == 1) {
                        $this->plugin->who = "pimmelmann";
                        $this->plugin->mode = 0;
                        $tile->setText(
                            $this->plugin->server,
                            "§0[§2Lobby§0]",
                            "",
                            ""
                        );
                    }
                } elseif (array_key_exists($player->getName(), CloudSystem::getInstance()->create)) {
                    $group = CloudSystem::getInstance()->create[$player->getName()];
                    $tile->setText(
                        $group,
                        "Created!",
                        "",
                        ""
                    );
                    GroupSigns::addGroupSign($group, $tile);
                    unset($this->plugin->create[$player->getName()]);
                }

                $text = $tile->getText();
                if (isset($text[2]) and $text[2] === "§cSearch Server") {
                    $player->sendMessage(CloudSystem::getInstance()->prefix . "§cNo Server found.");
                    return;
                }
                if (isset($text[0])) {
                    $server = CloudSystem::getInstance()->getConfigFile($text[0]);
                    if ($server != null) {
                        $server = $server->getAll();
                        if ($server["offline"]) {
							$player->sendMessage(CloudSystem::getInstance()->prefix . "§cThis Server is offline.");
                            return;
                        } elseif ($server["ingame"]) {
							$player->sendMessage(CloudSystem::getInstance()->prefix . "§cThis Server is Ingame.");
                            return;
                        } elseif ($server["max"] <= $server["count"]) {
							$player->sendMessage(CloudSystem::getInstance()->prefix . "§cThis Server is full.");
                            return;
                        }
                        WaterdogAPI::transferPlayer($player, $text[0], $server["port"]);
                    }
                }
                return;
            }
            $item = $event->getItem();
            if ($item->getCustomName() === TextFormat::YELLOW . "Lobby Switcher") {
                $event->setCancelled(true);
                #CloudSystem::getInstance()->showLobbyForm($player);
            }
        }
    }


    public function onLevelLoad(LevelLoadEvent $event)
    {
        GroupSigns::loadGroupSigns();
    }
}