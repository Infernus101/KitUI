<?php

namespace Infernus101\KitUI\UI;

use Infernus101\KitUI\Main;
use Infernus101\KitUI\UI\windows\KitError;
use Infernus101\KitUI\UI\windows\KitInfo;
use Infernus101\KitUI\UI\windows\KitMainMenu;
use pocketmine\Player;

class Handler {

	const KIT_MAIN_MENU = 0;
	const KIT_INFO = 1;
	const KIT_ERROR = 2;

	private $types = [
		KitMainMenu::class,
		KitInfo::class,
		KitError::class,
	];

	public function getWindowJson(int $windowId, Main $loader, Player $player): string{
		return $this->getWindow($windowId, $loader, $player)->getJson();
	}

	public function getWindow(int $windowId, Main $loader, Player $player): Window{
		if(!isset($this->types[$windowId])){
			throw new \OutOfBoundsException("Tried to get window of non-existing window ID.");
		}

		return new $this->types[$windowId]($loader, $player);
	}

	public function isInRange(int $windowId): bool{
		if(isset($this->types[$windowId]) || isset($this->types[$windowId + 3200])){
			return true;
		}

		return false;
	}

	public function getWindowIdFor(int $windowId): int{
		if($windowId >= 10100){
			return $windowId - 10100;
		}

		return 10100 + $windowId;
	}
}