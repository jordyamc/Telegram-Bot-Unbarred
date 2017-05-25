<?php
/**
 * Created by PhpStorm.
 * User: Jordy
 * Date: 24/05/2017
 * Time: 06:47 PM
 */

declare(strict_types=1);
include 'basics.php';

use unreal4u\TelegramAPI\Telegram\Methods\GetUpdates;
use unreal4u\TelegramAPI\TgLog;

$tgLog = new TgLog(BOT_TOKEN);
$getUpdates = new GetUpdates();
#$getUpdates->offset = 328221148;
echo '<pre>';
try {
    $updates = $tgLog->performApiRequest($getUpdates);
    /* @var \unreal4u\TelegramAPI\Telegram\Types\Custom\UpdatesArray $updates */
    foreach ($updates->traverseObject() as $update) {
        var_dump($update);
        #var_dump(sprintf('Chat id is #%d', $update->message->chat->id));
    }
} catch (\Exception $e) {
    $actualProblem = json_decode((string)$e->getResponse()->getBody());
    print_r('[EXCEPTION] ' . $actualProblem->description . '; original response:');
    print_r($actualProblem);
}
echo '</pre>';