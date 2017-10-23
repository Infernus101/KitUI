<?php

namespace Infernus101\KitUI\UI\windows;

use Infernus101\KitUI\Main;
use Infernus101\KitUI\UI\Handler;
use Infernus101\KitUI\UI\Window;
use pocketmine\utils\TextFormat;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\Player;

class KitMainMenu extends Window {
	public function process(): void {
		$title = $this->pl->language->getTranslation("mainmenu-title");
		$content = $this->pl->language->getTranslation("mainmenu-content");
		$this->data = [
			"type" => "form",
			"title" => $title,
			"content" => $content,
			"buttons" => []
		];
		foreach($this->pl->kits as $name => $data){
			$name = ucfirst($name);
			$this->data["buttons"][] = ["text" => "§f$name"];
			array_push(parent::$id, "$name");
		}
	}

	public function handle(ModalFormResponsePacket $packet): bool {
		$index = (int) $packet->formData + 1;
		$windowHandler = new Handler();
		if(isset(parent::$id[$index])) $kit = parent::$id[$index];
		else $kit = null;
		$this->navigateKit(Handler::KIT_INFO, $this->player, $windowHandler, $kit);
		return true;
	}
}
