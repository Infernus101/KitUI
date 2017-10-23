<?php

namespace Infernus101\KitUI;

use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\Player;
use Infernus101\KitUI\UI\Handler;

class PlayerEvents implements Listener {
	
	public $pl;
	
	public function __construct(Main $pg) {
		$this->pl = $pg;
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
    }
	
	public function onDataPacket(DataPacketReceiveEvent $event){
		$packet = $event->getPacket();
		if($packet instanceof ModalFormResponsePacket) {
			if(json_decode($packet->formData, true) === null) {
				return;
			}
			$windowHandler = new Handler();
			$packet->formId = $windowHandler->getWindowIdFor($packet->formId);
			if(!$windowHandler->isInRange($packet->formId)) {
				return;
			}
			$window = $windowHandler->getWindow($packet->formId, $this->pl, $event->getPlayer());
			$window->handle($packet);
		}
	}
}