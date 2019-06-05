<?php

namespace Infernus101\KitUI\UI\windows;

use Infernus101\KitUI\UI\Handler;
use Infernus101\KitUI\UI\Window;
use onebone\economyapi\EconomyAPI;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;

class KitInfo extends Window {

	public function process(): void{
		if(isset($this->pl->formData[strtolower($this->player->getName())]["kit"])){
			$kit = $this->pl->formData[strtolower($this->player->getName())]["kit"];
		}else{
			return;
		}

		$info = "";
		if($kit != null){
			$kits = $this->pl->getKit($kit);
			if(isset($kits->data["info"])) $info = $kits->data["info"];
		}
		$title = $this->pl->language->getTranslation("select-option");
		$this->data = [
			"type"    => "modal",
			"title"   => $title,
			"content" => $info,
			"button1" => "Yes",
			"button2" => "No",
		];
	}

	public function handle(ModalFormResponsePacket $packet): bool{
		$index = $packet->formData;
		$this->select($index);

		return true;
	}

	private function select($index){
		$windowHandler = new Handler();
		switch($index){
			case "true\n":
				if(isset($this->pl->formData[strtolower($this->player->getName())]["kit"])){
					$kit = $this->pl->formData[strtolower($this->player->getName())]["kit"];
				}else{
					$kit = null;
				}
				if($kit == null){
					$error = "Wrong Session! Try again!";
					$this->pl->formData[strtolower($this->player->getName())]["error"] = $error;
					$this->navigate(Handler::KIT_ERROR, $this->player, $windowHandler);
					break;
				}
				$kits = $this->pl->getKit($kit);
				if($kits != null){
					$name = $kits->getName();
				}else{
					$error = "Kit not found! Try again!";
					$this->pl->formData[strtolower($this->player->getName())]["error"] = $error;
					$this->navigate(Handler::KIT_ERROR, $this->player, $windowHandler);
					break;
				}
				if(!$kits->testPermission($this->player)){
					$error = $this->pl->language->getTranslation("noperm", $name);
					$this->pl->formData[strtolower($this->player->getName())]["error"] = $error;
					$this->navigate(Handler::KIT_ERROR, $this->player, $windowHandler);
					break;
				}
				if(isset($kits->data["money"])){
					$money = $kits->data["money"];
					if(EconomyAPI::getInstance()->myMoney($this->player) < $money){
						$error = $this->pl->language->getTranslation("cant-afford", $name, $money);
						$this->pl->formData[strtolower($this->player->getName())]["error"] = $error;
						$this->navigate(Handler::KIT_ERROR, $this->player, $windowHandler);
						break;
					}
				}
				if(isset($kits->timers[strtolower($this->player->getName())]) and !$this->player->hasPermission("kit.freepass")){
					$left = $kits->getTimerLeft($this->player);
					$error = $this->pl->language->getTranslation("timer1", $name) . "\n" . $this->pl->language->getTranslation("timer2", $left);
					$this->pl->formData[strtolower($this->player->getName())]["error"] = $error;
					$this->navigate(Handler::KIT_ERROR, $this->player, $windowHandler);
					break;
				}
				if(($this->pl->config->get("one-kit-per-life")) and (isset($kits->pl->kitUsed[strtolower($this->player->getName())])) and !$this->player->hasPermission("kit.freepass." . strtolower($name))){
					$error = $this->pl->language->getTranslation("one-per-life");
					$this->pl->formData[strtolower($this->player->getName())]["error"] = $error;
					$this->navigate(Handler::KIT_ERROR, $this->player, $windowHandler);
					break;
				}
				/** @noinspection PhpUnhandledExceptionInspection */
				$kits->equipKit($this->player);
				break;
			case "false\n":
				$this->navigate(Handler::KIT_MAIN_MENU, $this->player, $windowHandler);
				break;
		}
	}
}
