<?php
/**
 * Class ReplaySelectorItem
 * @author Jibix
 * @date 20.04.2025 - 13:46
 * @project Replay
 */
namespace Jibix\ReplayExample\item;
use Jibix\Forms\element\type\Input;
use Jibix\Forms\element\type\Label;
use Jibix\Forms\form\type\CustomForm;
use Jibix\Forms\form\type\MenuForm;
use Jibix\Forms\menu\Button;
use Jibix\Forms\menu\Image;
use Jibix\Forms\menu\type\BackButton;
use Jibix\Replay\replay\data\ReplayInformation;
use Jibix\Replay\replay\replayer\Replay;
use Jibix\Replay\util\Utils;
use Jibix\ReplayExample\session\ReplaySession;
use Jibix\ReplayExample\Main;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;


class ReplaySelectorItem extends ReplayItem{

    protected static function getItem(Player $player): Item{
        return VanillaItems::COMPASS()->setCustomName("§bReplays");
    }

    public static function getSlot(): int{
        return 4;
    }

    public function onUse(Player $player, ?Vector3 $useVector = null): bool{
        if ($player->hasPermission("gamereplay.search")) {
            $player->sendForm(new MenuForm("Replays", "", [
                new Button("§bSearch replay", fn (Player $player) => $player->sendForm($this->getReplaySearchForm()), Image::path("textures/ui/magnifyingGlass")),
                new Button("§bYour replays", fn (Player $player) => $player->sendForm($this->getPlayerReplaysForm($player)), Image::path("textures/ui/camera-small")),
                new BackButton()
            ]));
        } else {
            $player->sendForm($this->getPlayerReplaysForm($player));
        }
        return parent::onUse($player, $useVector);
    }

    private function getPlayerReplaysForm(Player $player, string $message = ""): MenuForm{
        $provider = Main::getInstance()->getSettings()->getProvider();
        foreach (ReplaySession::get($player)->getAvailableReplays() as $identifier) {
            $information = $provider->getReplay($identifier);
            if ($information === null) continue;
            $buttons[] = new Button(
                "§d{$identifier}§r -§b " . $information->getTimestamp()->format("m.d H:i") . "\n§6{$information->getGameDetails()}",
                fn (Player $player) => $player->sendForm($this->manageReplayForm($player, $information))
            );
        }
        $buttons[] = new BackButton();
        return new MenuForm("Your Replays", $message, $buttons);
    }

    private function manageReplayForm(Player $player, ReplayInformation $information): MenuForm{
        $buttons = [new Button("§aPlay", fn (Player $player) => Replay::play(Main::getInstance()->getSettings(), $player, $information), Image::path("textures/ui/icon_trailer"))];
        if ($player->hasPermission("gamereplay.delete")) $buttons[] = new Button("§cDelete", function (Player $player) use ($information): void{
            Main::getInstance()->getSettings()->getProvider()->deleteReplay(
                $identifier = $information->getIdentifier(),
                fn () => $player->sendForm($this->getPlayerReplaysForm($player, "§aYou have successfully deleted the replay§6 {$identifier}§a!"))
            );
            ReplaySession::get($player)->deleteAvailableReplay($identifier);
        }, Image::path("textures/ui/trash"));
        $buttons[] = new BackButton();
        return new MenuForm("Manage Replay§6 {$information->getIdentifier()}",
            "§8-------§aReplay Information§8-------\n" .
            "§bIdentifier:§6 {$information->getIdentifier()}\n" .
            "§bDuration:§6 " . Utils::formatReplayDuration(intval($information->getDuration() / 20)) . "\n" .
            "§bTimestamp:§6 " . $information->getTimestamp()->format("H:i:s Y.m.d") . "\n" .
            "§bGame Details:§6 " . $information->getGameDetails() . "\n" .
            "§8---------------------------",
        $buttons);
    }

    private function getReplaySearchForm(string $default = "", ?string $error = null): CustomForm{
        if ($error !== null) $elements = [new Label($error)];
        $elements[] = new Input(
            "§bReplay ID",
            ReplayInformation::generateIdentifier(),
            $default,
            function (Player $player, Input $input): void{
                if (empty($text = $input->getValue())) {
                    $player->sendForm($this->getReplaySearchForm("", "§cA replay with this ID could not be found!"));
                    return;
                }
                Main::getInstance()->getSettings()->getProvider()->searchReplay($text, function (?ReplayInformation $information) use ($text, $player): void{
                    if ($information === null) {
                        $player->sendForm($this->getReplaySearchForm($text, "§cA replay with this ID could not be found!"));
                        return;
                    }
                    $player->sendForm($this->manageReplayForm($player, $information));
                });
            }
        );
        return new CustomForm("Search Replay", $elements);
    }
}