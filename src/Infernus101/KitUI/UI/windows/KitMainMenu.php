<?php

namespace Infernus101\KitUI\UI\windows;

use Infernus101\KitUI\UI\Handler;
use Infernus101\KitUI\UI\Window;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;

class KitMainMenu extends Window {
	public function process(): void{
		parent::$id = [];
		$title = $this->pl->language->getTranslation("mainmenu-title");
		$content = $this->pl->language->getTranslation("mainmenu-content");
		$this->data = [
			"type"    => "form",
			"title"   => $title,
			"content" => $content,
			"buttons" => [],
		];
		foreach($this->pl->kits as $name => $data){
			$name = ucfirst($name);
			$name2 = $name;
			$kits = $this->pl->getKit($name);
			if(isset($kits->data["kit-name"]))
				$name2 = $kits->data["kit-name"];
			if(isset($kits->data["image-url"])){
				$url = $kits->data["image-url"];
				$this->data["buttons"][] = ["text" => "$name2", "image" => ["type" => "url", "data" => $url]];
			}else{
				$this->data["buttons"][] = ["text" => "$name2"];
			}
			array_push(parent::$id, "$name");
		}
	}

	public function handle(ModalFormResponsePacket $packet): bool{
		$index = (int)$packet->formData;
		$windowHandler = new Handler();
		if(isset(parent::$id[$index])) $this->pl->formData[strtolower($this->player->getName())]["kit"] = parent::$id[$index];
		else $this->pl->formData[strtolower($this->player->getName())]["kit"] = null;
		$this->navigate(Handler::KIT_INFO, $this->player, $windowHandler);

		return true;
	}
}
