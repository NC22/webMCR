<?php
if (!defined('MCR')) exit;

class Category
{
    private $db;
    private $id = false;
    private $name;
    private $priority;

    public function Category($id = false)
    {
        global $bd_names;

        $this->db = $bd_names['news_categorys'];
        $this->id = (int) $id;
        if (!$this->id)
            return false;

        $line = getDB()->fetchRow("SELECT `name`,`priority` FROM `" . $this->db . "` WHERE `id`='" . $this->id . "'", false, 'num');
        if (!$line) {
            $this->id = false;
            return false;
        }
        
        $this->name = $line[0];
        $this->priority = (int) $line[1];
    }

    public function Exist()
    {
        if (!$this->id)
            return false;
        else
            return true;
    }

    public function Create($name, $priority = 1, $description = '')
    {
        if ($this->Exist())
            return false;

        if (!$name or !TextBase::StringLen($name))
            return false;

        $line = getDB()->fetchRow("SELECT COUNT(*) FROM `" . $this->db . "` "
                . "WHERE `name`=:name", array('name' => $name), 'num');

        if ((int)$line[0])
            return false;

        $priority = (int) $priority;
        $result = getDB()->ask("INSERT INTO `" . $this->db . "` ( `name`, `priority`, `description`) "
                . "VALUES (:name,:priority,:description)", array(
                    'name' => $name, 
                    'priority' => $priority, 
                    'description' => $description 
        ));
        
        if ($result and $result->rowCount())
            $this->id = getDB()->lastInsertId();
        else
            return false;

        return true;
    }

    public function IsSystem()
    {
        if ($this->id == 1)
            return true;
        else
            return false;
    }

    public function GetName()
    {
        if (!$this->Exist())
            return false;
        return $this->name;
    }

    public function GetPriority()
    {
        if (!$this->Exist())
            return false;
        return $this->priority;
    }

    public function GetDescription()
    {
        $line = getDB()->fetchRow("SELECT `description` FROM `" . $this->db . "` WHERE id='{$this->id}'", false, 'num');

        return (!$line) ? false : $line[0];
    }

    public function Edit($name, $priority = 1, $description = '')
    {
        if (!$this->Exist())
            return false;

        if (!$name or !TextBase::StringLen($name))
            return false;

        $line = getDB()->fetchRow("SELECT COUNT(*) FROM `" . $this->db . "` "
                . "WHERE `name`=:name and `id`!='{$this->id}'", array('name' => $name), 'num');

        if ((int)$line[0])
            return false;

        $priority = (int) $priority;

        getDB()->ask("UPDATE `" . $this->db . "` SET "
                . "`name`=:name,"
                . "`priority`=:priority,"
                . "`description`=:description WHERE `id`='{$this->id}'", array(
                    'name' => $name, 
                    'priority' => $priority, 
                    'description' => $description 
        ));

        $this->name = $name;
        $this->priority = $priority;
        return true;
    }

    public function Delete()
    {
        global $bd_names;

        if (!$this->Exist() or $this->IsSystem())
            return false;

        $result = getDB()->ask("SELECT `id` FROM `{$bd_names['news']}` WHERE `category_id`='" . $this->id . "'");

        while ($line = $result->fetch()) {

            $news_item = new News_Item($line[0]);
            $news_item->Delete();
            unset($news_item);
        }

        getDB()->ask("DELETE FROM `" . $this->db . "` WHERE `id`='{$this->id}'");
        $this->id = false;
        return true;
    }
}

class CategoryManager
{
    public static function GetList($selected = 1)
    {
        global $bd_names;

        $result = getDB()->ask("SELECT `id`,`name` FROM {$bd_names['news_categorys']} ORDER BY `priority` DESC LIMIT 0,90");
        $cat_list = '';

        while ($line = $result->fetch())
            $cat_list .= '<option value="' . $line['id'] . '" ' . (($selected == $line['id']) ? 'selected' : '') . '>' . $line['name'] . '</option>';

        return $cat_list;
    }

    public static function GetNameByID($id)
    {
        if (!$id or $id < 0)
            return 'Без категории';

        $cat_item = new Category($id);
        $category_name = $cat_item->GetName();

        unset($cat_item);

        if (!$category_name)
            return 'Без категории';
        else
            return $category_name;
    }

    public static function ExistByID($id)
    {

        $cat_id = (int) $id;
        $cat_item = new Category($id);

        return $cat_item->Exist();
    }

}

/* Класс записи в каталоге */

class News_Item extends Item
{
    private $category_id;
    private $discus;
    private $title;
    private $vote;
    private $link;
    private $link_work;
    private $comments;

    public function __construct($id = false, $style_sd = false)
    {
        global $bd_names;

        parent::__construct($id, ItemType::News, $bd_names['news'], $style_sd);
        if (!$this->id)
            return false;

        $line = getDB()->fetchRow("SELECT `category_id`, `title`, `discus`, `comments`, `vote` "
                                  . "FROM `{$this->db}` WHERE `id`='" . $this->id . "'");
                                  
        if (!$line) {
            $this->id = false;
            return false;
        }
        
        $this->category_id = (int) $line['category_id'];
        $this->title = $line['title'];
        $this->discus = ((int) $line['discus'] == 1) ? true : false;
        $this->vote = ((int) $line['vote'] == 1) ? true : false;

        $this->link = Rewrite::GetURL(array('news', $this->id), array('', 'id'));
        $this->link_work = 'index.php?id=' . $this->id . '&amp;';

        $this->comments = (int) $line['comments'];

        return true;
    }
    
    public function Create($cat_id, $title, $message, $message_full = false, $vote = true, $discus = true)
    {
        global $user;

        if ($this->Exist() or empty($user) or !$user->getPermission('add_news'))
            return false;

        $cat_id = (int) $cat_id;
        if (!CategoryManager::ExistByID($cat_id))
            return false;

        $sql = "INSERT INTO `{$this->db}` ( `title`, `message`, `message_full`, `time`, `category_id`, `user_id`, `discus`, `vote`) "
            . " VALUES ( :title, :message, :message_full, NOW(), :category_id, :userid, :discus, :vote)";
        
        $result = getDB()->ask($sql, array(
            'title' => $title, 
            'message' => TextBase::StringLen($message) ? (string)$message : '', 
            'message_full' => TextBase::StringLen($message_full) ? (string)$message_full : '', 
            'category_id' => $cat_id,
            'userid' => $user->id(),
            'discus' => ($discus) ? '1' : '0',
            'vote' => ($vote) ? '1' : '0',
        ));

        if (!$result) return false;
        
        $this->id = getDB()->lastInsertId();

        $this->category_id = $cat_id;
        $this->title = $title;

        $this->discus = ($discus == 1) ? true : false;
        $this->vote = ($vote == 1) ? true : false;

        $this->comments = 0;

        return true;
    }

    public function Like($dislike = false)
    {
        global $user;

        if (!$this->Exist() or empty($user) or !$user->lvl())
            return 0;

        $like = new ItemLike(ItemType::News, $this->id, $user->id());

        return $like->Like($dislike);
    }

    public function OnComment()
    {
        if (!$this->Exist())
            return false;

        getDB()->ask("UPDATE `{$this->db}` SET `comments` = comments + 1 WHERE `id`='" . $this->id . "'");
        $this->comments++;
    }

    public function OnDeleteComment()
    {
        if (!$this->Exist())
            return false;

        getDB()->ask("UPDATE `{$this->db}` SET `comments` = comments - 1 WHERE `id`='" . $this->id . "'");
        $this->comments--;
    }

    public function categoryID()
    {
        if (!$this->Exist())
            return false;
        return $this->category_id;
    }

    public function title()
    {
        if (!$this->Exist())
            return false;
        return $this->title;
    }

    public function getInfo()
    {
        if (!$this->Exist())
            return false;

        $line = getDB()->fetchRow("SELECT `message`, `message_full` FROM `{$this->db}` WHERE `id`='" . $this->id . "'");
        if (!$line) return '';

        return array('id' => $this->id,
            'type' => $this->type(),
            'title' => $this->title,
            'vote' => $this->vote,
            'discus' => $this->discus,
            'comments' => $this->comments,
            'text' => $line['message'],
            'text_full' => $line['message_full'],
            'category_id' => $this->category_id,);
    }

    private function countView() 
    {      
        if (!isset($_COOKIE['PRTViews'])) {
            $data = array($this->id);
        } else {
            $data = explode('%', $_COOKIE['PRTViews']);
            
            if (!sizeof($data)) $data = array();
            foreach($data as $key => $value) {
                if (!(int) $value) unset($data[$key]);
            }
            
            if (in_array($this->id, $data)) return;
            
            if (sizeof($data) >= 20) {
                array_shift($data);                
            }
            
            array_push($data, $this->id);
        }   
            
        getDB()->ask("UPDATE `{$this->db}` SET `hits` = LAST_INSERT_ID( `hits` + 1 ) WHERE `id`='" . $this->id . "'");
        setcookie("PRTViews", "", time() - 3600);
        setcookie("PRTViews", implode('%', $data), strtotime( '+30 days' ), '/');
        
    }
    
    public function Show($full_text = false)
    {
        global $config, $user, $bd_names;

        if (!$this->Exist())
            return $this->ShowPage('news_not_found.html');

        $sql_text = ( $full_text ) ? ' `message_full`,' : '';

        $sql_hits = ' `hits`,';

        if ($full_text) {

            $this->countView();
            $sql_hits = " LAST_INSERT_ID() AS hits,";
        }

        $sql_likes = ( $this->vote ) ? ' `likes`, `dislikes`,' : '';

        $line = getDB()->fetchRow("SELECT DATE_FORMAT(time,'%d.%m.%Y') AS `date`, DATE_FORMAT(time,'%H:%i') AS `time`, " . $sql_hits . $sql_likes . $sql_text . " `message` FROM `{$this->db}` WHERE `id`='" . $this->id . "'");
        if (!$line)
            return '';
        
        if ($full_text)
            $line['message_full'] = TextBase::CutWordWrap($line['message_full']);
        $line['message'] = TextBase::CutWordWrap($line['message']);

        $text = ( $full_text and TextBase::StringLen($line['message_full']) ) ? $line['message_full'] : $line['message'];

        $id = $this->id;
        $title = $this->title;
        $date = $line['date'];
        $time = $line['time'];

        $vote = ( $this->vote ) ? true : false;

        $likes = ( $this->vote ) ? $line['likes'] : '-';
        $dlikes = ( $this->vote ) ? $line['dislikes'] : '-';

        $hits = $line['hits'];

        $link = $this->link;

        $comments = $this->comments;

        $category_id = $this->category_id;
        $category = CategoryManager::GetNameByID($category_id);

        $category_link = Rewrite::GetURL(array('category', $category_id), array('', 'cid'));

        $admin_buttons = '';

        if (!empty($user) and $user->getPermission('add_news')) {

            ob_start();
            include $this->GetView('news_admin.html');
            $admin_buttons = ob_get_clean();
        }

        ob_start();

        if ($full_text)
            include $this->GetView('news_full.html');
        else
            include $this->GetView('news.html');

        return ob_get_clean();
    }

    public function ShowFull($comment_list = false)
    {
        global $config, $bd_names;

        $link = Rewrite::GetURL(array('news', $this->id), array('', 'id'));

        $item_exist = $this->Exist();

        $title = ($item_exist) ? $this->title() : 'Новость не найдена';
        $category_id = ($item_exist) ? $this->categoryID() : 0;
        $category = ($item_exist) ? CategoryManager::GetNameByID($category_id) : 'Без категории';
        $category_link = Rewrite::GetURL(array('category', $category_id), array('', 'cid'));

        ob_start();
        include $this->GetView('news_full_header.html');
        $html = ob_get_clean();

        $html .= $this->Show(true);

        if (!$item_exist)
            return $html;

        loadTool('comment.class.php');

        $comments = new CommentList($this, $this->link_work, $this->st_subdir . 'comments/');
        $html .= $comments->Show($comment_list);

        if ($this->discus)
            $html .= $comments->ShowAddForm();

        return $html;
    }

    public function Edit($cat_id, $title, $message, $message_full = false, $vote = true, $discus = true)
    {
        global $user;

        if (!$this->Exist() or empty($user) or !$user->getPermission('add_news'))
            return false;

        $cat_id = (int) $cat_id;
        if (!CategoryManager::ExistByID($cat_id))
            return false;

        $sql = "UPDATE `{$this->db}` SET "
        . "`message`=:message,"
        . "`title`=:title,"
        . "`message_full`=:message_full,"
        . "`category_id`=:category_id, "
        . "`discus`=:discus,"
        . "`vote`=:vote WHERE `id`='" . $this->id . "'";
        
        $result = getDB()->ask($sql, array(
            'title' => $title, 
            'message' => TextBase::StringLen($message) ? (string)$message : '', 
            'message_full' => TextBase::StringLen($message_full) ? (string)$message_full : '', 
            'category_id' => $cat_id,
            'discus' => ($discus) ? '1' : '0',
            'vote' => ($vote) ? '1' : '0',
        ));
        
        $this->category_id = (int) $cat_id;
        $this->title = $title;

        $this->discus = ($discus) ? true : false;
        $this->vote = ($vote) ? true : false;

        return true;
    }

    public function Delete()
    {
        global $user, $bd_names;

        if (empty($user) or
                !$user->getPermission('add_news') or
                !$this->Exist())
            return false;

        $result = getDB()->ask("SELECT `id` FROM `{$bd_names['comments']}` "
                . "WHERE `item_id`='" . $this->id . "' AND `item_type` = '" . $this->type() . "'");

        loadTool('comment.class.php');

        while ($line = $result->fetch('num')) {

            $comments_item = new Comments_Item($line[0], false);
            $comments_item->Delete();
            unset($comments_item);
        }

        getDB()->ask("DELETE FROM `{$bd_names['likes']}` "
                . "WHERE `item_id` = '" . $this->id . "' AND `item_type` = '" . $this->type() . "'");

        return parent::Delete();
    }

}

/* Менеджер вывода записей из каталога */

class NewsManager extends View
{

    private $work_link;
    private $category_id;

    public function NewsManager($category = 1, $style_sd = false, $work_link = 'index.php?')
    { // category = -1 -- all last news
        parent::View($style_sd);

        if ((int) $category <= 0)
            $category = 0;

        $this->category_id = (int) $category;
        $this->work_link = $work_link;
    }

    public function destroy()
    {

        unset($this->work_link);
        unset($this->category_id);
    }

    public function ShowCategorySelect()
    {
        $cat_list = '<option value="0">Последние новости</option>';
        $cat_list .= CategoryManager::GetList($this->category_id);

        ob_start();
        include $this->GetView('categorys.html');

        return ob_get_clean();
    }

    public function ShowNewsEditor()
    {
        global $bd_names;

        $editorTitle = 'Добавить новость';
        $editorButton = 'Добавить';

        $editInfo = array(
            'vote' => !Filter::input('hide_vote', 'post', 'bool'),
            'discus' => !Filter::input('hide_discus', 'post', 'bool')
        );
        $editCategory = Filter::input('cid', 'post', 'int', true);
        $editMode = Filter::input('editMode', 'post', 'int');
        $editTitle = Filter::input('title', 'post', 'string', true);
        $editMessage = Filter::input('message', 'post', 'html', true);
        $editMessage_Full = Filter::input('message_full', 'post', 'html', true);
        $error = '';
        
        if ($editCategory !== false and $editTitle !== false and $editMessage !== false) {

            ob_start();
            $state = 'error';

            if (!$editCategory or !$editMessage or !$editTitle)
                $text_str = 'Заполните необходимые поля.';

            else {
                
                if ($editMode > 0) {

                    $news_item = new News_Item($editMode, $this->st_subdir);

                    if ($news_item->Edit(
                            $editCategory, 
                            $editTitle, 
                            $editMessage, 
                            $editMessage_Full, 
                            $editInfo['vote'], 
                            $editInfo['discus']
                    )) {
                        $state = 'success';
                        $text_str = 'Новость обновлена';
                    } else
                        $text_str = 'Недостаточно прав';

                    $editMode = 0;
                } else {

                    $news_item = new News_Item();
                    $news_item->Create(
                            $editCategory, 
                            $editTitle, 
                            $editMessage, 
                            $editMessage_Full, 
                            $editInfo['vote'], 
                            $editInfo['discus']
                    );

                    $state = 'success';
                    $text_str = 'Новость добавлена';
                }
            }

            include $this->GetView('news_admin_mess.html');
            $error = ob_get_clean();
        } elseif (Filter::input('delete', 'get', 'int')) {

            $news_item = new News_Item(Filter::input('delete', 'get', 'int'));
            $news_item->Delete();

            header("Location: " . $this->work_link . "ok");
        } elseif (Filter::input('edit', 'get', 'int')) {

            $editorTitle = 'Обновить новость';
            $editorButton = 'Изменить';

            $news_item = new News_Item(Filter::input('edit', 'get', 'int'));

            if (!$news_item->Exist())
                return '';

            $editInfo = $news_item->getInfo();

            $editMode = $editInfo['id'];
            $editCategory = $editInfo['category_id'];
            $editTitle = TextBase::HTMLDestruct($editInfo['title']);
            $editMessage = TextBase::HTMLDestruct($editInfo['text']);
            $editMessage_Full = TextBase::HTMLDestruct($editInfo['text_full']);
        }

        ob_start();

        $cat_list = CategoryManager::GetList($editCategory);

        include $this->GetView('news_add.html');

        return ob_get_clean();
    }

    public function ShowNewsListing($list = 1)
    {
        global $bd_names, $config;

        $sql = '';
        if ($this->category_id > 0)
            $sql = ' WHERE category_id=' . $this->category_id . ' ';

        $list = (int) $list;

        if ($list <= 0)
            $list = 1;

        if ($this->category_id > 0)
            $category = CategoryManager::GetNameByID($this->category_id);
        else
            $category = 'Последние новости';

        $category_id = $this->category_id;
        $category_link = Rewrite::GetURL(array('category', $category_id), array('', 'cid'));

        ob_start();
        include $this->GetView('news_header.html');
        $html_news = ob_get_clean();
        $news_pnum = $config['news_by_page'];

        $line = getDB()->fetchRow("SELECT COUNT(*) FROM `{$bd_names['news']}`" . $sql, false, 'num');

        $newsnum = (int)$line[0];
        if (!$newsnum) {

            $html_news .= $this->ShowPage('news_empty.html');
            return $html_news;
        }

        $result = getDB()->ask("SELECT `id` FROM `{$bd_names['news']}`" . $sql . "ORDER BY `time` DESC LIMIT " . ($news_pnum * ($list - 1)) . "," . $news_pnum);

        while ($line = $result->fetch('num')) {

            $news_item = new News_Item($line[0], $this->st_subdir);

            $html_news .= $news_item->Show();
            unset($news_item);
        }

        if ($newsnum)
        $html_news .= $this->arrowsGenerator($this->work_link, $list, $newsnum, $news_pnum, 'news');

        return $html_news;
    }
}
