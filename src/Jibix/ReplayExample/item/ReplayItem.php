<?php
/**
 * Class ReplayItem
 * @author Jibix
 * @date 21.04.2025 - 13:41
 * @project ReplayExample
 */
namespace Jibix\ReplayExample\item;
use Jibix\FunctionalItem\item\FunctionalItem;


abstract class ReplayItem extends FunctionalItem{

    abstract public static function getSlot(): int;
}