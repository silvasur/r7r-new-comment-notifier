<?php

class new_comment_notifier extends RatatoeskrPlugin
{
    private $config;
    private $config_modified = False;

    public function on_comment_store($comment)
    {
        global $rel_path_to_root;

        $baseurl = explode("/",self_url());
        $baseurl = array_slice($baseurl, 0, -1);
        foreach(explode("/", $rel_path_to_root) as $part)
        {
            if($part == "..")
                $baseurl = array_slice($baseurl, 0, -1);
        }
        $baseurl = implode("/", $baseurl);

        $article = $comment->get_article();

        $msg = "Hello,
Someone posted a comment to this article:
  Title: " . @$article->title[$comment->get_language()]->text . "
  URL:   " . $baseurl . "/" . $comment->get_language() . "/" . $article->get_section()->name . "/" . $article->urlname . "
The comment:
  Author Name: " . $comment->author_name . "
  Author Mail: " . $comment->author_mail . "
  Comment Text:
" . implode("\n", array_map(function($l){return "    " . $l;}, explode("\n", $comment->text))) . "
If you are logged in to the Ratatöskr backend, you can view the comment here:
  " . $baseurl . "/backend/content/comments/" . $comment->get_id();

        $moreheaders = "From: " . $this->config["from"] . "\r\nX-Mailer: new_comment_notifier plugin for Ratatoeskr\r\nContent-Transfer-Encoding: 8bit\r\nContent-Type: text/plain; charset=\"UTF-8\"";

        foreach($this->config["to"] as $to)
            mail($to, "Comment received", $msg, $moreheaders);
    }

    public function backend_page(&$data, $url_now, &$url_next)
    {
        $this->prepare_backend_pluginpage();

        if(isset($_POST["send_general_settings"]))
        {
            if(empty($_POST["mail_from"]))
                $this->ste->vars["error"] = "Mail From must not be empty.";
            else
            {
                $this->config["from"]       = $_POST["mail_from"];
                $this->config_modified      = True;
                $this->ste->vars["success"] = "Settings saved.";
            }
        }

        if(isset($_POST["add_new_recv"]) and (!empty($_POST["new_recv"])))
        {
            $this->config["to"][]       = $_POST["new_recv"];
            $this->config_modified      = True;
            $this->ste->vars["success"] = "Address added.";
        }

        if(isset($_POST["delete_recvs"]) and ($_POST["really_delete"] == "yes"))
        {
            $this->config["to"]         = array_diff($this->config["to"], $_POST["recvs_multiselect"]);
            $this->config_modified      = True;
            $this->ste->vars["success"] = "Receivers deleted.";
        }

        $this->ste->vars["mail_to"]   = $this->config["to"];
        $this->ste->vars["mail_from"] = $this->config["from"];

        echo $this->ste->exectemplate($this->get_template_dir() . "/config.html");
    }

    public function install()
    {
        $this->kvstorage["config"] = array("from" => "nobody@example.com", "to" => array());
    }

    public function init()
    {
        $this->config = $this->kvstorage["config"];
        $this->register_on_comment_store(array($this, "on_comment_store"));
        $this->register_backend_pluginpage("New comment notifier", array($this, "backend_page"));
    }

    public function atexit()
    {
        if($this->config_modified)
            $this->kvstorage["config"] = $this->config;
    }
}
