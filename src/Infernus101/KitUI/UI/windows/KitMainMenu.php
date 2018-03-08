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
		$url = "";
		parent::$id = array();
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
			$kits = $this->pl->getKit($name);
			if(isset($kits->data["kit-name"]))	
				$name = $kits->data["kit-name"];
			if(isset($kits->data["image-url"])){
			$url = $kits->data["image-url"];
			$this->data["buttons"][] = ["text" => "$name", "image" => ["type" => "url", "data" => $url]];
			}else{
			$this->data["buttons"][] = ["text" => "$name"];	
			}
			array_push(parent::$id, "$name");
		}
	}

	public function handle(ModalFormResponsePacket $packet): bool {
		$index = (int) $packet->formData;
		$windowHandler = new Handler();
		if(isset(parent::$id[$index])) $this->pl->id[strtolower($this->player->getName())]["kit"] = parent::$id[$index];
		else $this->pl->id[strtolower($this->player->getName())]["kit"] = null;
		$this->navigate(Handler::KIT_INFO, $this->player, $windowHandler);
		return true;
	}
}
