<?
namespace giftcode\Anhkhoaaa;

use giftcode\Anhkhoaaa\Main;
use pocketmine\Player;
use giftcode\Anhkhoaaa\Code;
use giftcode\Anhkhoaaa\ItemData;
use pocketmine\utils\Config;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\LevelEventPacket;

class Form {
	const PREFIX = "§6§6[§6GIFTCODE§6]: §a";
	public $plugin;
	public function __construct(Main $plugin)
	{$this->plugin = $plugin;}

	public function claimGiftcode($p){
		$form = $this->plugin->api->createCustomForm(function(Player $p, $data){
			if($data === null){
				return true;	
			} 
			if($this->plugin->code->exists($data[0])){
				$duLieu = new Code($this->plugin);
				$code = $duLieu->getCode($data[0]);
				$money = $code[1];$time = $code[2];$point = $code[3];$command = $code[4];$all = $code[5];$configItem = $code[6];
	      if((string)$code[5] == "full"){
					$config = new Config($this->plugin->getDataFolder() ."Codes/". $data[0] . ".yml", Config::YAML);
					if((!$config->exists($p->getName()))){
	        			$duLieu->giveGift($p, $data[0], $money, $point, $command);
	        			$this->playSoundSuccess($p);
	        			$config->set($p->getName(), true);
	        			$config->save();
	        			if($configItem === null){
	        				return;
	        			} 
        				for($i = 0; $i < count($configItem); $i++){
        					$newItem = $configItem[$i];
        					$editItem = new ItemData($this->plugin);
        					$godItem = $editItem->dataToItem($newItem);
        					$p->getInventory()->addItem($godItem);
        				} 
        				return;
	            	}
            		$p->sendMessage(self::PREFIX."Bạn đã sử dụng code này rồi!");
            		$this->playSoundFail($p);
            		return;
            	}
        		if($code[5] === 1){
        			$duLieu->giveGift($p, $data[0], $money, $point, $command);
        			$this->playSoundSuccess($p);
            	$duLieu->setCode($data[0], $money, $time, $point, $command, 0, $configItem);
							if($configItem === null){
			        				return;
			        			} 
    					for($i = 0; $i < count($configItem); $i++){
    					$newItem = $configItem[$i];
    					$editItem = new ItemData($this->plugin);
    					$godItem = $editItem->dataToItem($newItem);
    					$p->getInventory()->addItem($godItem);
        			}
        			return;
        		}
        		$p->sendMessage(self::PREFIX."Code này đã được sử dụng rồi!");
    			$this->playSoundFail($p);
    			return;
			}
			$p->sendMessage(self::PREFIX."Code này không tồn tại.");
			$this->playSoundFail($p);
		});
		$form->setTitle("Nhận code");
		$form->addInput("Nhập tên code");
		$form->sendToPlayer($p);
		return $form;
	}

	#Tạo code
	public function create($p){
		$form = $this->plugin->api->createCustomForm(function(Player $p, $data){
			if($data === null){
				return true;	
			} 
			if($this->plugin->code->exists($data[0])){
				$p->sendMessage(self::PREFIX."! Code này đã tồn tại.");
				return;
			}
			if($data[0] == null || $data[1] == null || $data[2] == null || $data[3] == null){
               $p->sendMessage(self::PREFIX."! Không được bỏ trống thông tin");
               return;
            }
           	$data[4] == true ? $count = (string)"full" : $count = 1;
           	$time = "no";
           	if($data[5] == true){
           		$time = "yes";
           		$this->plugin->config->set($p->getName(), true);
           		$this->plugin->config->save();
           		$this->plugin->initTask();
           	}
            if(!(is_numeric($data[1]) || is_numeric($data[2]))){
         	 	$p->sendMessage(self::PREFIX."! Dữ liệu phải là số.");
              return;
			} 
			$duLieu = new Code($this->plugin);
			$duLieu->setCode($data[0],$data[1],$time,$data[2],$data[3],$count,null);          
			$fileName = $this->plugin->getDataFolder() ."Codes/". $data[0] . ".yml";
			$config = new Config($fileName, Config::YAML);
			$p->sendMessage(self::PREFIX."Tạo code thành công. Hãy dùng §6[/gc additem] §ađể additem vào giftcode");
			$this->playSoundSuccess($p);
		});
		$form->setTitle("Tạo code");
		$form->addInput("Nhập tên code");
		$form->addInput("sẽ nhận bao được nhiêu money khi claim code (0 để bỏ qua)");
		$form->addInput("sẽ nhận được bao nhiêu point khi claim code (0 để bỏ qua)");
		$form->addInput("lệnh được thực hiện khi claim code (0 để bỏ qua)");
		$form->addToggle("Mọi người chơi có thể dùng 1 lần?\nNếu không bật, chỉ sử dụng được 1 lần duy nhất.\nCó thể chỉnh sửa trong config.");
		$form->addToggle("Thời gian tồn tại §c5 §fphút?\nBật là §aTrue");
		$form->sendToPlayer($p);
		return $form;
	} 
	public function playSoundSuccess(Player $p){
		$volume = mt_rand();
		$p->getlevel()->broadcastLevelSoundEvent($p, LevelSoundEventPacket::SOUND_LEVELUP, (int) $volume);
	}
	public function playSoundFail(Player $p){
		$volume = mt_rand();
		$p->getLevel()->broadcastLevelEvent($p, LevelEventPacket::EVENT_SOUND_ANVIL_FALL, (int) $volume);
	}
}