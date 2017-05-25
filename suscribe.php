<?php
/**
 * Created by PhpStorm.
 * User: Jordy
 * Date: 24/05/2017
 * Time: 04:41 PM
 */
declare(strict_types=1);
include 'basics.php';

use unreal4u\TelegramAPI\Telegram\Methods\SetWebhook;
use unreal4u\TelegramAPI\TgLog;

$setWebhook = new SetWebhook();
$setWebhook->url = WEB_HOOK;

$tgLog = new TgLog(BOT_TOKEN);
$tgLog->performApiRequest($setWebhook);

echo '<h1>WEBHOOK CONFIGURADO!!!</h1>';