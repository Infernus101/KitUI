<?php

namespace Infernus101\KitUI\UI\windows;

use Infernus101\KitUI\Main;
use Infernus101\KitUI\UI\Handler;
use Infernus101\KitUI\UI\Window;
use pocketmine\utils\TextFormat;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\Player;
use onebone\economyapi\EconomyAPI;

class KitInfo extends Window {
	public function process(): void {
		if(parent::$kit != null){
			$kits = $this->pl->getKit(parent::$kit);
			if(isset($kits->data["info"])) $info = $kits->data["info"];
		}
		$title = $this->pl->language->getTranslation("select-option");
			$this->data = [
				"type" => "modal",
				"title" => $title,
				"content" => $info,
				"button1" => "Yes",
				"button2" => "No"
			];
		}
		
	private function select($index){
		$windowHandler = new Handler();
		switch($index){
			case "true\n":
			if(parent::$kit == null){
				$error = "Wrong Session! Try again!";
				$this->navigateError(Handler::KIT_ERROR, $this->player, $windowHandler, $error);
				break;
			}
				$kits = $this->pl->getKit(parent::$kit);
			if($kits != null){
				$name = $kits->getName();
			}else{
				$error = "Kit not found! Try again!";
				$this->navigateError(Handler::KIT_ERROR, $this->player, $windowHandler, $error);
				break;
			}
			if(!$kits->testPermission($this->player)){
				$error = $this->pl->language->getTranslation("noperm", $name);
				$this->navigateError(Handler::KIT_ERROR, $this->player, $windowHandler, $error);
				break;
			}
			if(isset($kits->timers[strtolower($this->player->getName())])){
				$left = $kits->getTimerLeft($this->player);
				$error = $this->pl->language->getTranslation("timer1", $name) . "\n" . $this->pl->language->getTranslation("timer2", $left);
				$this->navigateError(Handler::KIT_ERROR, $this->player, $windowHandler, $error);
				break;
			}
			if(isset($kits->data["money"])){
				$money = $kits->data["money"];
				if(EconomyAPI::getInstance()->reduceMoney($this->player, $money) === EconomyAPI::RET_INVALID){
					$error = $this->pl->language->getTranslation("cant-afford", $name, $money);
					$this->navigateError(Handler::KIT_ERROR, $this->player, $windowHandler, $error);
					break;
				}
			}
			if(($this->pl->config->get("one-kit-per-life")) and (isset($kits->pl->kitused[strtolower($this->player->getName())]))){
				$error = $this->pl->language->getTranslation("one-per-life");
				$this->navigateError(Handler::KIT_ERROR, $this->player, $windowHandler, $error);
				break;
			}
			$kits->add($this->player);
			$this->player->sendMessage($this->pl->language->getTranslation("selected-kit", $name));
			break;
			case "false\n":
			$this->navigate(Handler::KIT_MAIN_MENU, $this->player, $windowHandler);
			break;
		}
	}
	public function handle(ModalFormResponsePacket $packet): bool {
			$index = $packet->formData;
			$this->select($index);
			return true;
	}
}