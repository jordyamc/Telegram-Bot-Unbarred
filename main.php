<?php
/**
 * Created by PhpStorm.
 * User: Jordy
 * Date: 24/05/2017
 * Time: 04:43 PM
 */

declare(strict_types=1);
include 'basics.php';

use GuzzleHttp\Exception\ClientException;
use unreal4u\TelegramAPI\Telegram\Methods\AnswerInlineQuery;
use unreal4u\TelegramAPI\Telegram\Methods\SendMessage;
use unreal4u\TelegramAPI\Telegram\Methods\SendPhoto;
use unreal4u\TelegramAPI\Telegram\Types\Custom\InputFile;
use unreal4u\TelegramAPI\Telegram\Types\Inline\Query\Result\Article;
use unreal4u\TelegramAPI\Telegram\Types\InputMessageContent\Text;
use unreal4u\TelegramAPI\Telegram\Types\Update;
use unreal4u\TelegramAPI\TgLog;


$updateData = json_decode(file_get_contents('php://input'), true);

$update = new Update($updateData);

$m = $update->message->text;

answerInline($update);

if (isCommand($m)) {
    answerCommand($update, $m);
} else {
    if (strlen($m) > 72) {
        sendImage($update, "response/tranquilo.png", "Es mucho texto para mi");
    } else {
        $text = getResponse($update, $m, $update->message->from->first_name);
        if ($text !== '')
            sendText($update, $text);
    }
}

function sendText($update, $text)
{
    $tgLog = new TgLog(BOT_TOKEN);
    $sendMessage = new SendMessage();
    $sendMessage->chat_id = $update->message->chat->id;
    $sendMessage->reply_to_message_id = $update->message->message_id;
    $sendMessage->text = $text;
    try {
        $tgLog->performApiRequest($sendMessage);
    } catch (ClientException $e) {
        sendText($update, $e->getMessage());
    }
}

function sendImage($update, $file = "response/toma.jpg", $text = "")
{
    try {
        if (0 !== strpos($file, 'public_html/img/'))
            $file = 'public_html/img/' . $file;
        $tgLog = new TgLog(BOT_TOKEN);
        $sendPhoto = new SendPhoto();
        $sendPhoto->chat_id = $update->message->chat->id;
        $sendPhoto->reply_to_message_id = $update->message->message_id;
        $sendPhoto->photo = new InputFile($file);
        $sendPhoto->caption = $text;
        $tgLog->performApiRequest($sendPhoto);
    } catch (\Exception $e) {
        sendText($update, $e->getMessage() . " dir: " . getcwd());
    }
}

function isCommand($command = "other")
{
    return startsWCommand($command);
}

function startsWCommand($command)
{
    if (starts($command, '/yuri')) {
        return true;
    } elseif (starts($command, '/yaoi')) {
        return true;
    } elseif (starts($command, '/hentai')) {
        return true;
    } elseif (starts($command, '/ecchi')) {
        return true;
    } elseif (starts($command, '/lolis')) {
        return true;
    } elseif (starts($command, '/help')) {
        return true;
    } elseif (starts($command, '/random')) {
        return true;
    } else {
        return false;
    }
}

function starts($in, $search): bool
{
    return 0 === strpos($in, $search);
}

function answerCommand($update, $command)
{
    $c = str_replace(BOT_ALIAS, "", $command);
    switch ($c) {
        case '/yuri':
            sendImage($update, random_pic('yuri'), 'Toma tu yuri prro!!!');
            break;
        case '/yaoi':
            sendImage($update, random_pic('yaoi'), 'Toma tu yaoi prro!!!');
            break;
        case '/lolis':
            sendImage($update, random_pic('lolis'), 'Toma tus lolis prro!!!');
            break;
        case '/ecchi':
            sendImage($update, random_pic('ecchi'), 'Toma tu ecchi prro!!!');
            break;
        case '/hentai':
            sendImage($update, random_pic('hentai'), 'Toma tu hentai prro!!!');
            break;
        case '/help':
            sendText($update, "Este bot esta bajo programacion....\r\n\r\nNo le hagan bullying ;)");
            break;
        case '/random':
            sendImage($update, random_pic(), 'Toma tu random prro!!!');
            break;
        default:
            sendText($update, 'Comando desconocido...');
    }
}

function getResponse($update, $message, $name): string
{
    switch (limpiar(strtolower($message))) {
        case 'hola':
            return 'Hola ' . $name;
        case 'adios':
            return 'Adios ' . $name;
        case 'te amo':
            return 'Chinga tu madre';
        case 'send lolis':
            sendImage($update, random_pic("lolis"), 'Buscalas tu puto >:v');
            return '';
    }
    /**
     * Respuesta en caso de no entrar en ningun caso
     *
     * return '';  En caso de que no quieres que conteste
     */
    return '';
}

function answerInline($update)
{
    try {
        $tgLog = new TgLog(BOT_TOKEN);
        $inline = new AnswerInlineQuery();
        $inline->inline_query_id = $update->inline_query->id;
        $inline->next_offset = "";
        $article1 = new Article();
        $article1->title = "Enviar";
        $article1->id = "art1";
        $text1 = new Text();
        $text1->message_text = $update->inline_query->query;
        $article1->input_message_content = $text1;
        $inline->addResult($article1);
        $tgLog->performApiRequest($inline);
    } catch (\Exception $e) {

    }
}

function random_pic($dir = 'random'): string
{
    $files = glob(getDirImage($dir));
    if ($dir === 'random') {
        $files = glob(getDirImage("yuri"));
        array_push($files, glob(getDirImage("yaoi")));
        array_push($files, glob(getDirImage("lolis")));
        array_push($files, glob(getDirImage("hentai")));
    }
    $file = array_rand($files);
    return $files[$file];
}

function getDirImage($name): string
{
    return DIR_IMAGES . $name . "/*.*";
}

function limpiar($s): string
{
    $s = str_replace('á', 'a', $s);
    $s = str_replace('Á', 'A', $s);
    $s = str_replace('é', 'e', $s);
    $s = str_replace('É', 'E', $s);
    $s = str_replace('í', 'i', $s);
    $s = str_replace('Í', 'I', $s);
    $s = str_replace('ó', 'o', $s);
    $s = str_replace('Ó', 'O', $s);
    $s = str_replace('Ú', 'U', $s);
    $s = str_replace('ú', 'u', $s);


    $s = str_replace('"', '', $s);
    $s = str_replace(':', '', $s);
    $s = str_replace('.', '', $s);
    $s = str_replace(',', '', $s);
    $s = str_replace(';', '', $s);
    $s = str_replace('?', '', $s);
    $s = str_replace('¿', '', $s);
    $s = str_replace('!', '', $s);
    $s = str_replace('¡', '', $s);
    return $s;
}