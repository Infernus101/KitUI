<?php

namespace Infernus101\KitUI\events;

use Infernus101\KitUI\Kit;
use pocketmine\event\Cancellable;
use pocketmine\Player;

/**
 * This event is called when there is a purchase from
 * the player itself.
 *
 * @package Infernus101\KitUI\events
 * @author larryTheCoder
 */
class KitPurchaseEvent extends KitEvent implements Cancellable {

	/** @var int */
	private $price;
	/** @var Player */
	private $player;

	/**
	 * KitPurchaseEvent constructor.
	 *
	 * @param Player $pl
	 * @param Kit $kit
	 * @param int $price
	 */
	public function __construct(Player $pl, Kit $kit, int $price){
		parent::__construct($kit);

		$this->player = $pl;
		$this->price = $price;
	}

	/**
	 * Get the player that purchased this
	 * kit.
	 *
	 * @return Player
	 */
	public function getPlayer(): Player{
		return $this->player;
	}

	/**
	 * Get the amount of money that is required to
	 * purchase this kit.
	 *
	 * @return int
	 */
	public function getPrice(): int{
		return $this->price;
	}

	/**
	 * Set the price of the kit.
	 *
	 * @param int $price
	 */
	public function setPrice(int $price){
		$this->price = $price;
	}
}