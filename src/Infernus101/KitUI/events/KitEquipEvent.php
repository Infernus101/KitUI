<?php

namespace Infernus101\KitUI\events;

use Infernus101\KitUI\Kit;
use pocketmine\event\Cancellable;
use pocketmine\Player;

/**
 * This event is called when the player is trying to
 * equip a kit that has been purchased.
 *
 * @package Infernus101\KitUI\events
 * @author larryTheCoder
 */
class KitEquipEvent extends KitEvent implements Cancellable {

	/** @var Player */
	private $player;

	/**
	 * @param Player $p
	 * @param Kit $kit
	 */
	public function __construct(Player $p, Kit $kit){
		parent::__construct($kit);

		$this->player = $p;
	}

	/**
	 * Get the player that equips this kit.
	 *
	 * @return Player
	 */
	public function getPlayer(): Player{
		return $this->player;
	}
}