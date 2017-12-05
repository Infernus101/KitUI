<?php

namespace Infernus101\KitUI;

use pocketmine\block\Block;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\Player;
use pocketmine\tile\Sign;
use Infernus101\KitUI\UI\Handler;

class PlayerEvents implements Listener {
	
	public $pl;
	
	public function __construct(Main $pg) {
		$this->pl = $pg;
	}
	
	public function onTap(PlayerInteractEvent $event){
        $id = $event->getBlock()->getId();
        if($id === Block::SIGN_POST or $id === Block::WALL_SIGN){
            $tile = $event->getPlayer()->getLevel()->getTile($event->getBlock());
            if($tile instanceof Sign){
                $text = $tile->getText();
                if(strtolower(TextFormat::clean($text[0])) === strtolower($this->pl->config->get("text-on-sign"))){
			$event->setCancelled();
			$handler = new Handler();
			$packet = new ModalFormRequestPacket();
			$packet->formId = $handler->getWindowIdFor(Handler::KIT_MAIN_MENU);
			$packet->formData = $handler->getWindowJson(Handler::KIT_MAIN_MENU, $this->pl, $event->getPlayer());
			$event->getPlayer()->dataPacket($packet);
                }
            }
        }
    }
    public function onSignChange(SignChangeEvent $event){
        if(strtolower(TextFormat::clean($event->getLine(0))) === strtolower($this->pl->config->get("text-on-sign")) and !$event->getPlayer()->hasPermission("kitui.sign")){
            $event->getPlayer()->sendMessage($this->pl->language->getTranslation("no-sign-perm"));
            $event->setCancelled();
        }
    }
	
    public function onDeath(PlayerDeathEvent $event){
        if(isset($this->pl->kitused[strtolower($event->getEntity()->getName())])){
            unset($this->pl->kitused[strtolower($event->getEntity()->getName())]);
        }
    }

    public function onLogOut(PlayerQuitEvent $event){
        if($this->pl->config->get("reset-on-logout") and isset($this->pl->kitused[strtolower($event->getPlayer()->getName())])){
            unset($this->pl->kitused[strtolower($event->getPlayer()->getName())]);
        }
	if(isset($this->pl->id[strtolower($event->getPlayer()->getName())])) unset($this->pl->id[strtolower($event->getPlayer()->getName())]);
    }
	
	public function onDataPacket(DataPacketReceiveEvent $event){
		$packet = $event->getPacket();
		if($packet instanceof ModalFormResponsePacket) {
			$windowHandler = new Handler();
			$formId = $windowHandler->getWindowIdFor($packet->formId);
			if(!$windowHandler->isInRange($formId)) {
				return;
			}
			if(json_decode($packet->formData, true) === null) {
				return;
			}
			if(!isset($this->pl->id[strtolower($event->getPlayer()->getName())])){
				return;
			}
			$window = $windowHandler->getWindow($formId, $this->pl, $event->getPlayer());
			$window->handle($packet);
		}
	}
}

