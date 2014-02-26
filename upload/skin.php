<?php
header("Content-type: image/png");

require('./system.php');

$showMini = (Filter::input('mini', 'get', 'int') or Filter::input('m', 'get', 'bool')) ? true : false;
$showByName = Filter::input('user_name', 'get', 'string', true);
$isFemale = Filter::input('female', 'get', 'int', true);   
$userId = Filter::input('user_id', 'get', 'int');
if ($showMini and !$userId) $userId = Filter::input('mini', 'get', 'int');

if ($showByName or $userId or $isFemale !== false) {

    if ($userId) {
        DBinit('skin_viewer');
        loadTool('user.class.php');
        $tmp_user = new User($userId);
        if (!$tmp_user->id()) exit;        
        $showByName = $tmp_user->name();     
        
        if (!file_exists($tmp_user->getSkinFName())) {
        
            if ($config['default_skin']) $tmp_user->setDefaultSkin(); 
            else { 
                $showByName = false; 
                $isFemale = 1; 
            }
        }
    } 
    
    ShowSkin($showMini, $showByName, $isFemale, $config['sbuffer']);
}

function ShowSkin($mini = false, $name = false, $isFemale = false, $saveBuffer = false)
{   
    global $site_ways;    
    loadTool('skin.class.php');

    if ($isFemale !== false) {
    
        $cloak = false;
        $skin = MCRAFT . 'tmp/skin_buffer/default/Char' . (($isFemale) ? '_female' : '') . '.png';
        $buffer = MCRAFT . 'tmp/skin_buffer/default/Char' . ($mini ? '_Mini' : '') . ($isFemale ? '_female' : '') . '.png';
    } elseif ($name) {
        $skin = MCRAFT . $site_ways['skins'] . $name . (($isFemale) ? '_female' : '') . '.png';
        $cloak = MCRAFT . $site_ways['cloaks']  . $name . '.png';
        $buffer = MCRAFT . 'tmp/skin_buffer/' . $name. ($mini ? '_Mini' : '') . '.png';
    } else exit;
        
    if (file_exists($buffer)) {
        readfile($buffer);
        exit;
    } elseif ($saveBuffer)
        $image = ($mini) ? SkinViewer2D::saveHead($buffer, $skin) : SkinViewer2D::savePreview($buffer, $skin, $cloak);
    else
        $image = ($mini) ? SkinViewer2D::createHead($skin) : SkinViewer2D::createPreview($skin, $cloak);

    if ($image) imagepng($image);
}
