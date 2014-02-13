<?php

header("Content-type: image/png");
require('./system.php');

function ShowSkinWithBuff()
{ // auto generate default skin way if not enough params
    global $uInfo, $config;

    $skin = GetVisual('skin');
    if ($skin === false and $uInfo['name'] !== false)
        return; // user not exists - all users have default skin in skins dir
    
    
    loadTool('skin.class.php');
    $dir = MCRAFT . ( ($uInfo['female'] === false and $uInfo['name']) ? 'tmp/skin_buffer/' : 'tmp/skin_buffer/default/' );
    $buffer = $dir . ($uInfo['name'] ? $uInfo['name'] : 'Char') . ($uInfo['mini'] ? '_Mini' : '') . ($uInfo['female'] ? '_female' : '') . '.png';

    if (file_exists($buffer)) {
        readfile($buffer);
        return;
    } elseif ($config['sbuffer'])
        $image = ($uInfo['mini']) ? SkinViewer2D::saveHead($buffer, $skin) : SkinViewer2D::savePreview($buffer, $skin, GetVisual('cloak'));
    else
        $image = ($uInfo['mini']) ? SkinViewer2D::createHead($skin) : SkinViewer2D::createPreview($skin, GetVisual('cloak'));

    if ($image) {
        imagepng($image);
        imagedestroy($image);
    }
}

function GetVisual($type = 'skin')
{
    global $site_ways, $uInfo;

    if ($uInfo['name'] === false and $type == 'cloak')
        return false; // default cloak not supported
    $dir = MCRAFT . ( ( $uInfo['female'] === false and $uInfo['name'] ) ? $site_ways[$type . 's'] : 'tmp/default_skins/' );
    $way = $dir . ($uInfo['name'] ? $uInfo['name'] : 'Char') . ($uInfo['female'] ? '_female' : '') . '.png';
    return (file_exists($way)) ? $way : false;
}

$userId = Filter::input('user_id', 'get', 'int');
if (!$userId) $userId = Filter::input('mini', 'get', 'int');

$uInfo = array(
    'mini' => (Filter::input('mini', 'get', 'int') or Filter::input('m', 'get', 'bool')) ? true : false,
    'female' => Filter::input('female', 'get', 'int', true),
    'name' => Filter::input('user_name', 'get'),
);

if ($userId) {

    DBinit('skin_viewer');
    loadTool('user.class.php');
    $tmp_user = new User($userId);
    if (!$tmp_user->id())
        exit;

    $uInfo['name'] = ($tmp_user->defaultSkinTrigger()) ? '' : $tmp_user->name();
}
ShowSkinWithBuff();
