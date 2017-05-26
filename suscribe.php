<?php
/**
 * Created by PhpStorm.
 * User: Jordy
 * Date: 24/05/2017
 * Time: 04:41 PM
 */
declare(strict_types=1);
include 'basics.php';

use unreal4u\TelegramAPI\Telegram\Methods\GetWebhookInfo;
use unreal4u\TelegramAPI\Telegram\Methods\SetWebhook;
use unreal4u\TelegramAPI\Telegram\Types\WebhookInfo;
use unreal4u\TelegramAPI\TgLog;

if (!WebHookSeted()) {
    $setWebhook = new SetWebhook();
    $setWebhook->url = WEB_HOOK;
    $tgLog = new TgLog(BOT_TOKEN);
    echo '<h1>WEBHOOK CONFIGURADO CORRECTAMENTE!!!</h1>';
}

function WebHookSeted(): bool
{
    if (!isset($_GET['force'])) {
        $webhook = getCurrentWebHook();
        $seted = $webhook->url === WEB_HOOK;
        if ($seted)
            echo '<h1>WEBHOOK CONFIGURADO: ' . $webhook->url . '</h1>';
        return $seted;
    } else {
        return false;
    }
}

function getCurrentWebHook(): WebhookInfo
{
    $getwebhook = new GetWebhookInfo();
    $tgLog = new TgLog(BOT_TOKEN);
    return $tgLog->performApiRequest($getwebhook);
}