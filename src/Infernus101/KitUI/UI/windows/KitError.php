<?php

namespace Infernus101\KitUI\UI\windows;

use Infernus101\KitUI\Main;
use Infernus101\KitUI\UI\Handler;
use Infernus101\KitUI\UI\Window;
use pocketmine\utils\TextFormat;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\Player;

class KitError extends Window {
	public function process(): void {
		if(isset($this->pl->id[strtolower($this->player->getName())]["error"])){
			$error = $this->pl->id[strtolower($this->player->getName())]["error"];
		}
		else{
			return;
		}
			$title = $this->pl->language->getTranslation("error-title");
			$this->data = [
				"type" => "modal",
				"title" => $title,
				"content" => $error,
				"button1" => "Go Back",
				"button2" => "Exit"
			];
		}
		
	private function select($index){
		$windowHandler = new Handler();
		switch($index){
			case "true\n":
			$this->navigate(Handler::KIT_MAIN_MENU, $this->player, $windowHandler);
			break;
			case "false\n":
			break;
		}
	}
	public function handle(ModalFormResponsePacket $packet): bool {
			$index = $packet->formData;
			$this->select($index);
			return true;
	}
}
