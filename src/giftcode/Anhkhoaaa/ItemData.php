<?
namespace giftcode\Anhkhoaaa;

use giftcode\Anhkhoaaa\Main;
use giftcode\Anhkhoaaa\customenchants\PiggyCustomEnchantsLoader;
use DaPigGuy\PiggyCustomEnchants\CustomEnchantManager;
use DaPigGuy\PiggyCustomEnchants\CustomEnchants\CustomEnchants;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;

class ItemData {
	public $plugin;
	public const ITEM_FORMAT = [
        "id" => 1,
        "damage" => 0,
        "count" => 1,
        "display_name" => "",
        "lore" => [

        ],
        "enchants" => [

        ],
    ];
    public function __construct(Main $plugin)
	{$this->plugin = $plugin;}

	public static function dataToItem(array $itemData) : Item {
        $item = ItemFactory::get($itemData["id"], $itemData["damage"] ?? 0, $itemData["count"] ?? 1);
        if(isset($itemData["enchants"])) {
            foreach($itemData["enchants"] as $ename => $level) {
                $ench = Enchantment::getEnchantment((int)$ename);
                if(PiggyCustomEnchantsLoader::isPluginLoaded() && $ench === null) {

                    if(!PiggyCustomEnchantsLoader::isNewVersion()) $ench = CustomEnchants::getEnchantment((int)$ename);
                    else $ench = CustomEnchantManager::getEnchantment((int)$ename);

                }
                if($ench === null) continue;
                if(!PiggyCustomEnchantsLoader::isNewVersion() && $ench instanceof CustomEnchants) {
                    PiggyCustomEnchantsLoader::getPlugin()->addEnchantment($item, $ench->getName(), $level);
                } else {
                    $item->addEnchantment(new EnchantmentInstance($ench, $level));
                }
            }
        }
        if(isset($itemData["display_name"])) $item->setCustomName(TextFormat::colorize($itemData["display_name"]));
        if(isset($itemData["lore"])) {
            $lore = [];
            foreach($itemData["lore"] as $key => $ilore) {
                $lore[$key] = TextFormat::colorize($ilore);
            }
            $item->setLore($lore);
        }
        return $item;

    }

    public static function itemToData(Item $item) : array {
        $itemData = self::ITEM_FORMAT;
        $itemData["id"] = $item->getId();
        $itemData["damage"] = $item->getDamage();
        $itemData["count"] = $item->getCount();
            if($item->hasCustomName()) {
                $itemData["display_name"] = $item->getCustomName();
            } else {
                unset($itemData["display_name"]);
            }
            if($item->getLore() !== []) {
                $itemData["lore"] = $item->getLore();
            } else {
                unset($itemData["lore"]);
            }
            if($item->hasEnchantments()) {
                foreach($item->getEnchantments() as $enchantment) {
                    $itemData["enchants"][(string)$enchantment->getId()] = $enchantment->getLevel();
                }
            } else {
                unset($itemData["enchants"]);
            }
        return $itemData;
    }
}