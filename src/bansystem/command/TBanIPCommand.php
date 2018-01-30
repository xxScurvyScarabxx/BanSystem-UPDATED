<?php

namespace bansystem\command;

use bansystem\translation\Translation;
use bansystem\util\date\Countdown;
use DateTime;
use InvalidArgumentException;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class TBanIPCommand extends Command {
    
    public function __construct() {
        parent::__construct("tban-ip");
        $this->description = "Temporarily prevents the given IP address from this server";
        $this->usageMessage = "/tban-ip <player | address> <timeFormat> [reason...]";
        $this->setPermission("bansystem.command.tempbanip");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if ($this->testPermissionSilent($sender)) {
            if (count($args) <= 1) {
                $sender->sendMessage(Translation::translateParams("usage", array($this)));
                return false;
            }
            $player = $sender->getServer()->getPlayer($args[0]);
            $banList = $sender->getServer()->getIPBans();
            if ($banList->isBanned($args[0])) {
                $sender->sendMessage(Translation::translate("ipAlreadyBanned"));
                return false;
            }
            try {
                $expiry = new Countdown($args[1]);
                $expiryToString = Countdown::expirationTimerToString($expiry->getDate(), new DateTime());
                $ip = filter_var($args[0], FILTER_VALIDATE_IP);
                if (count($args) == 2) {
                    if ($ip != null) {
                        $banList->addBan($ip, null, $expiry->getDate(), $sender->getName());
                        foreach ($sender->getServer()->getOnlinePlayers() as $onlinePlayers) {
                            if ($onlinePlayers->getAddress() == $ip) {
                                $onlinePlayers->kick(TextFormat::RED . "You have been temporarily IP banned. IP Banned by: $sender->getName() Reason: Not provided\n§6Did you get banned unfairly? §5Please appeal your ban! §3http://tinyurl.com/vmpebanappeal"
                                        . " your IP ban expires in " . TextFormat::AQUA . $expiryToString . TextFormat::RED . ".", false);
                            }
                        }
                        $sender->getServer()->broadcastMessage(TextFormat::RED . "Address " . TextFormat::AQUA . $ip . TextFormat::RED . " has been IP banned! IP banned by: $sender->getName() Their ban expires in " . TextFormat::AQUA . $expiryToString . TextFormat::RED . "\n§6Did you get banned unfairly? §5Please appeal your ban! §3http://tinyurl.com/vmpebanappeal");
                    } else {
                        if ($player != null) {
                            $player->kick(TextFormat::RED . "You have been temporarily IP banned, IP Banned by: $sender->getName()"
                                        . " your IP ban expires in " . TextFormat::AQUA . $expiryToString . TextFormat::RED . ".", false);
                            $banList->addBan($player->getName(), null, $expiry->getDate(), $sender->getName());
                            $sender->getServer()->broadcastMessage(TextFormat::AQUA . $player->getName() . TextFormat::RED . " has been IP banned. IP Banned by: $sender->getName() until " . TextFormat::AQUA . $expiryToString . TextFormat::RED . ".");
                        } else {
                            $sender->sendMessage(Translation::translate("playerNotFound"));
                        }
                    }
                } else if (count($args) >= 3) {
                    $reason = "";
                    for ($i = 2; $i < count($args); $i++) {
                        $reason .= $args[$i];
                        $reason .= " ";
                    }
                    $reason = substr($reason, 0, strlen($reason) - 1);
                    if ($ip != null) {
                        $banList->addBan($ip, $reason, $expiry->getDate(), $sender->getName());
                        foreach ($sender->getServer()->getOnlinePlayers() as $players) {
                            if ($players->getAddress() == $ip) {
                                $players->kick(TextFormat::RED . "You have been temporarily IP banned. IP banned by: $sender->getName() Reason - " . TextFormat::AQUA . $reason . TextFormat::RED . "\n§6Did you get banned unfairly? §5Please appeal your ban! §3http://tinyurl.com/vmpebanappeal"
                                    . " your IP ban expires in " . TextFormat::AQUA . $expiryToString . TextFormat::RED . ".", false);
                            }
                        }
                        $sender->getServer()->broadcastMessage(TextFormat::RED . "Address Not showing for security reasons" . TextFormat::RED . " has been IP banned. IP Banned by: $sender->getName() Reason - " . TextFormat::AQUA . $reason . TextFormat::RED . " their ban expires in " . TextFormat::AQUA . $expiryToString . TextFormat::RED . "\n§6Did you get banned unfairly? §5Please appeal your ban! §3http://tinyurl.com/vmpebanappeal");
                    } else {
                        if ($player != null) {
                            $banList->addBan($player->getAddress(), $reason, $expiry->getDate(), $sender->getName());
                            $player->kick(TextFormat::RED . "You have been temporarily IP banned. IP Banned by $sender->getName() . Reason - " . TextFormat::AQUA . $reason . TextFormat::RED . "\n§6Did you get banned unfairly? §5Please appeal your ban! §3http://tinyurl.com/vmpebanappeal"
                                    . " your IP ban expires in " . TextFormat::AQUA . $expiryToString . TextFormat::RED . ".", false);
                            $sender->getServer()->broadcastMessage(TextFormat::AQUA . $player->getName() . TextFormat::RED . " has been IP banned. IP banned by: $sender->getName() Reason - " . TextFormat::AQUA . $reason . TextFormat::RED . " uTheir ban expires in " . TextFormat::AQUA . $expiryToString . TextFormat::RED . "\n§6Did you get banned unfairly? §5Please appeal your ban! §3http://tinyurl.com/vmpebanappeal");
                        } else {
                            $sender->sendMessage(Translation::translate("playerNotFound"));
                        }
                    }
                }
            } catch (InvalidArgumentException $e) {
                $sender->sendMessage(TextFormat::RED . $e->getMessage());
            }
        } else {
            $sender->sendMessage(Translation::translate("noPermission"));
        }
        return true;
    }
}
