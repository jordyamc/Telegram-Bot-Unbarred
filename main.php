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
use unreal4u\TelegramAPI\Telegram\Methods\GetFile;
use unreal4u\TelegramAPI\Telegram\Methods\SendMessage;
use unreal4u\TelegramAPI\Telegram\Methods\SendPhoto;
use unreal4u\TelegramAPI\Telegram\Types\Custom\InputFile;
use unreal4u\TelegramAPI\Telegram\Types\File;
use unreal4u\TelegramAPI\Telegram\Types\Inline\Query\Result\Article;
use unreal4u\TelegramAPI\Telegram\Types\InputMessageContent\Text;
use unreal4u\TelegramAPI\Telegram\Types\Update;
use unreal4u\TelegramAPI\TgLog;

if (isset($_GET['set_enabled']))
    file_put_contents(ENABLED_FILE, $_GET['set_enabled']);
if (file_get_contents(ENABLED_FILE) === 'true') {
    $updateData = json_decode(file_get_contents('php://input'), true);
    $update = new Update($updateData);
    if (isset($update->message)) {
        file_put_contents(LAST_REQUEST_FILE, file_get_contents('php://input'));
        $m = $update->message->text;
        answerInline($update);
        if (isCommand($m)) {
            answerCommand($update, $m);
        } else {
            if (strlen($m) > 72) {
                sendImage($update, "response/tranquilo.png", "Es mucho texto para mi");
            } else {
                $text = getResponse($update, $m);
                if ($text !== '')
                    sendText($update, $text);
            }
        }
    } elseif (isset($update->edited_message)) {
        file_put_contents(LAST_REQUEST_FILE, file_get_contents('php://input'));
        sendTextEdited($update);
    } else {
        http_response_code(405);
        echo '<h1>No hay comandos!!!</h1>';
    }
} else {
    http_response_code(405);
    echo '<h1>BOT DESACTIVADO</h1>';
}

function sendText($update, $text)
{
    try {
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
    } catch (\Exception $e) {
        echo '<h1>' . $e->getMessage() . '</h1>';
    }
}

function sendTextEdited(Update $update)
{
    try {
        $tgLog = new TgLog(BOT_TOKEN);
        $sendMessage = new SendMessage();
        $sendMessage->chat_id = $update->edited_message->chat->id;
        $sendMessage->text = "Dat edit";
        try {
            $tgLog->performApiRequest($sendMessage);
        } catch (ClientException $e) {
            sendText($update, $e->getMessage());
        }
    } catch (\Exception $e) {
        echo '<h1>' . $e->getMessage() . '</h1>';
    }
}

function sendTextWReply($update, $text, $from_reply)
{
    $tgLog = new TgLog(BOT_TOKEN);
    $sendMessage = new SendMessage();
    $sendMessage->chat_id = $update->message->chat->id;
    $sendMessage->reply_to_message_id = $from_reply;
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
    return in_array(formatCommand($command), getCommands());
}

/**
 * Lista de comandos que reconoce el bot
 *
 * @return array
 */
function getCommands(): array
{
    return array(
        '/yuri',
        '/yaoi',
        '/hentai',
        '/echhi',
        '/lolis',
        '/ecchi',
        '/help',
        '/random',
        '/habla',
        '/add_lolis',
        '/add_yaoi',
        '/add_yuri',
        '/add_hentai',
        '/add_ecchi'
    );
}

/**Lista de Administradores del bot (En caso de que algun comando lo necesite)
 *
 * @return array
 */
function getAdmins(): array
{
    return array(
        'UnbarredStream',
        'guerra1337'
    );
}

function starts($in, $search): bool
{
    return 0 === strpos($in, $search);
}

function contains($in, $search): bool
{
    return strpos($in, $search) !== FALSE;
}

function isAdmin($name): bool
{
    return in_array($name, getAdmins());
}

function formatCommand($command): string
{
    return str_replace(BOT_ALIAS, "", $command);
}

function answerCommand(Update $update, string $command)
{
    switch (formatCommand($command)) {
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
        case '/habla':
            sendText($update, getResponse($update, "hola"));
            break;
        case '/add_lolis':
            addImage($update, "lolis", $update->message->from->username);
            break;
        case '/add_yaoi':
            addImage($update, "yaoi", $update->message->from->username);
            break;
        case '/add_yuri':
            addImage($update, "yuri", $update->message->from->username);
            break;
        case '/add_hentai':
            addImage($update, "hentai", $update->message->from->username);
            break;
        case '/add_ecchi':
            addImage($update, "ecchi", $update->message->from->username);
            break;
        default:
            sendText($update, 'Comando desconocido...');
    }
}

function addImage(Update $update, string $category, string $user)
{
    if (isAdmin($user)) {
        try {
            if (isset($update->message->reply_to_message->photo)) {
                $f_id = getBestPhoto($update);
                $getFile = new GetFile();
                $getFile->file_id = $f_id;
                $tgLog = new TgLog(BOT_TOKEN);
                $file = asFile($update, $tgLog->performApiRequest($getFile));
                $remote_file_path = $file->file_path;
                $local_file_path = getDirFile($category, basename($remote_file_path));
                $local_file_path = saveFile($local_file_path, $remote_file_path);
                if ($local_file_path !== '') {
                    $remote_file = file_get_contents($remote_file_path);
                    file_put_contents($local_file_path, $remote_file);
                    sendTextWReply($update, "Imagen guardada en " . $category, $update->message->reply_to_message->message_id);
                } else {
                    sendTextWReply($update, "La imagen ya existe!!!", $update->message->reply_to_message->message_id);
                }
            } else {
                sendText($update, "No se encontro ninguna imagen");
            }
        } catch (\Exception $e) {
            sendText($update, $e->getMessage());
        }
    } else {
        sendText($update, 'Necesitas ser admin para hacer esto!!!');
    }
}

function saveFile($local_path, $remote_path): string
{
    if (file_exists($local_path)) {
        /*if (md5(file_get_contents($local_path)) != md5(file_get_contents($remote_path))) {
            if (basename($local_path) === basename($remote_path)) {
                $local_path = str_replace(substr(basename($local_path),0,strpos(basename($local_path),".")), generateRandomString(),$local_path);
            }
            return $local_path;
        } else {
            return '';
        }*/
        return '';
    } else {
        return $local_path;
    }
}

function generateRandomString($length = 10)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function getBestPhoto($update): string
{
    try {
        $json = json_decode(file_get_contents('php://input'), true);
        $photos = $json['message']['reply_to_message']['photo'];
        $best = $photos[count($photos) - 1]['file_id'];
        return $best;
    } catch (\Exception $e) {
        sendText($update, $e->getMessage());
        return "";
    }
}

function asFile($update, $response): File
{
    try {
        return $response;
    } catch (\Exception $e) {
        sendText($update, $e->getMessage());
        return null;
    }
}

/**Buscar respuestas a mensajes concretos
 *
 * @param $update
 * @param $message
 * @return string
 */

function getResponse($update, $message): string
{
    $name = $update->message->from->first_name;
    $clean_message = limpiar(strtolower($message));
    switch ($clean_message) {
        case 'hola':
            return 'Hola ' . $name;
        case 'adios':
            return 'Adios ' . $name;
        case 'te amo':
            return 'Chinga tu madre';
        case 'flat is justice':
            sendImage($update, random_pic("lolis"), 'Delicius flat chest');
            return '';
    }
    /**
     * Respuesta en caso de no entrar en ningun caso
     *
     * return '';  En caso de que no quieres que conteste
     */
    return checkGenericCommnad($update, $clean_message);
}

/**Buscar comandos en el mensaje
 *
 * @param $update
 * @param $command
 * @return string
 */

function checkGenericCommnad($update, $command): string
{
    if (starts($command, 'send')) {
        if (contains($command, 'lolis')) {
            sendImage($update, random_pic("lolis"), 'Buscalas tu puto >:v');
        } elseif (contains($command, 'yaoi')) {
            sendImage($update, random_pic("yaoi"), 'Buscalo tu puto >:v');
        } elseif (contains($command, 'yuri')) {
            sendImage($update, random_pic("yuri"), 'Buscalo tu puto >:v');
        } elseif (contains($command, 'hentai')) {
            sendImage($update, random_pic("hentai"), 'Buscalo tu puto >:v');
        } elseif (contains($command, 'ecchi')) {
            sendImage($update, random_pic("ecchi"), 'Buscalo tu puto >:v');
        } elseif (contains($command, 'random')) {
            sendImage($update, random_pic(), 'Toma tu imagen random');
        } elseif (contains($command, 'nudes')) {
            sendImage($update, random_pic(), 'No tengo nudes... pero toma una imagen random');
        }
        return 'No reconozco ese comando...';
    } elseif (starts($command, "/")) {
        return 'No reconozco ese comando...';
    }
    return checkPredictionResponse($update, $command);
}

function checkPredictionResponse($update, $command): string
{
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
    $files = glob(getDirImageAll($dir));
    if ($dir === 'random') {
        $files = glob(getDirImageAll("yuri"));
        array_push($files, glob(getDirImageAll("yaoi")));
        array_push($files, glob(getDirImageAll("lolis")));
        array_push($files, glob(getDirImageAll("hentai")));
        array_push($files, glob(getDirImageAll("ecchi")));
    }
    $file = array_rand($files);
    return $files[$file];
}

function getDirImageAll($name): string
{
    return DIR_IMAGES . $name . "/*.*";
}

function getDirFile($type, $name): string
{
    return DIR_IMAGES . $type . "/" . $name;
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