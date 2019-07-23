<?php

namespace Infernus101\KitUI;

use Infernus101\KitUI\events\KitEquipEvent;
use Infernus101\KitUI\events\KitPurchaseEvent;
use onebone\economyapi\EconomyAPI;
use DaPigGuy\PiggyCustomEnchants\CustomEnchants\CustomEnchants;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\Player;

class Kit {

	public $pl;
	public $data;
	public $name;
	public $cost = 0;
	public $timer;
	public $timers = [];
	public $id = [];

	public function __construct(Main $pl, array $data, string $name){
		$this->pl = $pl;
		$this->data = $data;
		$this->name = $name;
		$this->timer = $this->getTimerMinutes();
		if(file_exists($this->pl->getDataFolder() . "timer/" . strtolower($this->name) . ".sl")){
			$this->timers = unserialize(file_get_contents($this->pl->getDataFolder() . "timer/" . strtolower($this->name) . ".sl"));
		}
		if(isset($this->data["money"]) and $this->data["money"] != 0){
			$this->cost = (int)$this->data["money"];
		}
		if(!isset($this->data["permission"])){
			$this->data["permission"] = "kit." . $this->name;
		}
	}

	public function getTimerMinutes(): int{
		$min = 0;
		if(isset($this->data["cooldown"]["minutes"])){
			$min += (int)$this->data["cooldown"]["minutes"];
		}
		if(isset($this->data["cooldown"]["hours"])){
			$min += (int)$this->data["cooldown"]["hours"] * 60;
		}

		return $min;
	}

	public function getName(): string{
		return $this->name;
	}

	/**
	 * @param Player $player
	 * @throws \ReflectionException
	 */
	public function equipKit(Player $player){
		$event = new KitEquipEvent($player, $this);
		$event->call();

		if($event->isCancelled()){
			return;
		}

		$this->add($player);
	}

	/**
	 * @param Player $player
	 * @throws \ReflectionException
	 */
	public function add(Player $player){
		$inv = $player->getInventory();
		$arm = $player->getArmorInventory();

		if(isset($this->data["money"])){
			$event = new KitPurchaseEvent($player, $this, $this->data["money"]);
			$event->call();

			if($event->isCancelled()){
				goto skipPurchase;
			}

			EconomyAPI::getInstance()->reduceMoney($player, $event->getPrice());
		}
		skipPurchase:

		if($this->pl->config->get("clear-effect")){
			$player->removeAllEffects();
		}

		if($this->pl->config->get("clear-inventory")){
			$inv->clearAll();
			$player->getCraftingGrid()->clearAll();
		}

		if(!$this->pl->config->get("start-from-first")){
			if(count($this->data["items"]) + count($inv->getContents()) > $inv->getSize()){
				$player->sendMessage($this->pl->language->getTranslation("inv-full"));

				return;
			}
			foreach($this->data["items"] as $itemString){
				$inv->setItem($inv->firstEmpty(), $i = $this->loadItem(...explode(":", $itemString)));
			}
		}else{
			$tag = 0;
			foreach($this->data["items"] as $itemString){
				$inv->setItem($tag++, $i = $this->loadItem(...explode(":", $itemString)));
			}
		}

		isset($this->data["helmet"]) and $arm->setHelmet($this->loadItem(...explode(":", $this->data["helmet"])));
		isset($this->data["chestplate"]) and $arm->setChestplate($this->loadItem(...explode(":", $this->data["chestplate"])));
		isset($this->data["leggings"]) and $arm->setLeggings($this->loadItem(...explode(":", $this->data["leggings"])));
		isset($this->data["boots"]) and $arm->setBoots($this->loadItem(...explode(":", $this->data["boots"])));
		$arm->sendContents($player);

		if(isset($this->data["effects"])){
			foreach($this->data["effects"] as $effectString){
				$e = $this->loadEffect(...explode(":", $effectString));
				if($e !== null){
					$player->addEffect($e);
				}
			}
		}

		if(isset($this->data["commands"]) and is_array($this->data["commands"])){
			foreach($this->data["commands"] as $cmd){
				$this->pl->getServer()->dispatchCommand(new ConsoleCommandSender(), str_replace("{player}", $player->getName(), $cmd));
			}
		}
		if(!$player->hasPermission("kit.freepass")){
			if($this->timer){
				$this->timers[strtolower($player->getName())] = $this->timer;
			}
		}

		$this->pl->kitUsed[strtolower($player->getName())] = $this;
		$player->sendMessage($this->pl->language->getTranslation("selected-kit", $this->getName()));
	}

	public function loadItem(int $id = 0, int $damage = 0, int $count = 1, string $name = "default", ...$enchantments): Item{
		$item = Item::get($id, $damage, $count);
		if(strtolower($name) !== "default"){
			$item->setCustomName($name);
		}
		$enchantment = null;
		foreach($enchantments as $key => $name_level){
			if($key % 2 === 0){ //Name expected
				$enchantment = Enchantment::getEnchantmentByName((string)$name_level);
				if($enchantment === null){
					$enchantment = CustomEnchants::getEnchantmentByName((string)$name_level);
				}
			}elseif($enchantment !== null){
				if($this->pl->piggyEnchants !== null && $enchantment instanceof CustomEnchants){
					$this->pl->piggyEnchants->addEnchantment($item, $enchantment->getName(), (int)$name_level);
				}else{
					$item->addEnchantment(new EnchantmentInstance($enchantment, (int)$name_level));
				}
			}
		}

		return $item;
	}

	public function loadEffect(string $name = "INVALID", int $seconds = 60, int $amplifier = 1){
		return new EffectInstance(Effect::getEffectByName($name), $seconds * 20, $amplifier);
	}

	public function getTimerLeft(Player $player): string{
		if(($minutes = $this->timers[strtolower($player->getName())]) < 60){
			return $this->pl->language->getTranslation("timer-format1", $minutes);
		}
		if(($modulo = $minutes % 60) !== 0){
			return $this->pl->language->getTranslation("timer-format2", floor($minutes / 60), $modulo);
		}

		return $this->pl->language->getTranslation("timer-format3", $minutes / 60);
	}

	public function processTimer(){
		foreach($this->timers as $player => $min){
			$this->timers[$player] -= 1;
			if($this->timers[$player] <= 0){
				unset($this->timers[$player]);
			}
		}
	}

	public function testPermission(Player $player): bool{
		return $player->hasPermission($this->data["permission"]);
	}

	public function save(){
		if(count($this->timers) > 0){
			file_put_contents($this->pl->getDataFolder() . "timer/" . strtolower($this->name) . ".sl", serialize($this->timers));
		}
	}

}
