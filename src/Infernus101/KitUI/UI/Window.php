<?php

namespace Infernus101\KitUI\UI;

use Infernus101\KitUI\Main;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\Player;

abstract class Window {

	/** @var int[] */
	public static $id = [];
	/** @var Main */
	protected $pl;
	/** @var Player */
	protected $player;
	/** @var object[] */
	protected $data = [];

	public function __construct(Main $pl, Player $player){
		$this->pl = $pl;
		$this->player = $player;
		if(!isset($pl->formData[strtolower($player->getName())])){
			$pl->formData[strtolower($player->getName())] = [];
		}
		$this->process();
	}

	protected abstract function process(): void;

	public function getJson(): string{
		return json_encode($this->data);
	}

	public function getLoader(): Main{
		return $this->pl;
	}

	public function getPlayer(): Player{
		return $this->player;
	}

	public function navigate(int $menu, Player $player, Handler $windowHandler): void{
		$packet = new ModalFormRequestPacket();
		$packet->formId = $windowHandler->getWindowIdFor($menu);
		$packet->formData = $windowHandler->getWindowJson($menu, $this->pl, $player);
		$player->dataPacket($packet);
	}

	public abstract function handle(ModalFormResponsePacket $packet): bool;
}
