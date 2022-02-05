<?php
class Note {
    function __construct($fpath) {
        $this->fpath = $fpath;
        $this->fsize = filesize($fpath);
        preg_match("/^(.*\/)?((\d{14})-\d{5})-?(.+)?\.(.+)$/", $this->fpath, $match);
        list(,
             $this->fdir,
             $this->id,
             $this->date_digitsonly,
             $this->original_basename_urlencoded,
             $this->extension) = $match;
        $this->location = array_search(
            pathinfo($this->fdir)["basename"],
            array(
                "main" => "contents",
                "trash" => ".trash"
            )
        );
        $this->url = "/note/$this->id/raw";
        $this->date = DateTime::createFromFormat("YmdHis", $this->date_digitsonly)->format("U");
        $this->date_human = date("D d M Y H:i T", $this->date);
        $this->date_iso = date("c", $this->date);
        $this->last_modified = filemtime($fpath);
        $this->last_modified_human = date("D d M Y H:i T", $this->last_modified);
        $this->last_modified_iso = date("c", $this->last_modified);
        $this->original_basename = urldecode($this->original_basename_urlencoded);
        $this->fname = ($this->original_basename ?: "Note from $this->date_human") . ".$this->extension";
    }
    
    function get_info() {
        $info = [
            "id" => $this->id,
            "type" => $this->type,
            "location" => $this->location,
            "filename" => $this->fname,
            "filesize" => $this->fsize,
            "created" => $this->date_iso,
            "lastModified" => $this->last_modified_iso
        ];
        return $info;
    }
    
    static function of_unknown_type($fpath) {
        $extension = pathinfo($fpath, PATHINFO_EXTENSION);
        switch ($extension) {
            case "txt":
            case "md":
                $note = new TextNote($fpath);
                break;
            case "jpeg":
            case "jpg":
            case "png":
            case "bmp":
            case "svg":
                $note = new ImageNote($fpath);
                break;
            case "wav":
            case "mp3":
            case "ogg":
            case "3gpp":
                $note = new AudioNote($fpath);
                break;
            case "mp4":
                $note = new VideoNote($fpath);
                break;
            default:
                $note = new FileNote($fpath);
                break;
        }
        return $note;
    }
    
    function humanreadable_fsize() {
        if ($this->fsize == 0) {
            // This prevents a division by zero error
            return "0 B";
        }
        $units = ["B", "KiB", "MiB", "GiB", "TiB", "PiB"];
        $magnitude = floor(log($this->fsize, 1024));
        return round($this->fsize / 1024 ** $magnitude)." ".$units[$magnitude];
    }
    
    function as_html() {
        return "<div id=\"N$this->id\" class=\"note $this->type\">
                    <div class=\"date\">
                        <time datetime=\"$this->last_modified_iso\">$this->last_modified_human</time>" .
                        ($this->last_modified_iso != $this->date_iso
                            ? " (created: <time datetime=\"$this->date_iso\">$this->date_human</time>)"
                            : "")
                    . "</div>
                    <div class=\"content\">" . $this->content_as_html() . "</div>
                    <div class=\"controls\">
                        <!-- <span class=\"edit\">edit/info</span> -->
                        <span class=\"copy\"><span class=\"hotkey\">c</span>opy</span>
                        <a class=\"download\" href=\"/note/$this->id/download\"><span class=\"hotkey\">d</span>ownload</a>
                        <span class=\"bump\"><span class=\"hotkey\">b</span>ump</span>
                        <span class=\"trash\"><span class=\"hotkey\">t</span>rash</span>
                    </div>
                </div>";
    }
}


class TextNote extends Note {
    function __construct($fpath) {
        parent::__construct($fpath);
        $this->type = "text";
        $this->content = rtrim(file_get_contents($this->fpath), "\n");
    }
    
    function get_info() {
        $info = parent::get_info();
        $info["content"] = $this->content;
        return $info;
    }
    
    function content_as_html() {
        $this->content = nl2br(htmlspecialchars($this->content));
        $this->content = preg_replace_callback(
            '/(https?:\/\/)' .                           // protocol
            '([A-Za-z0-9\.-]+)' .                        // domain
            '(\/[A-Za-z0-9\.\(\)~%:#,?(&amp;)=_-]+)*\/?/', // path
            function($match) {
                // link to url and replace link text with
                // title of the linked website
                $url = $match[0];
                // if (preg_match('/<title>(.+)<\/title>/i',
                //               file_get_contents($url),
                //               $title)) {
                //     $title = $title[1];
                // } else {
                //     // if title could not be found,
                //     // use url for title
                    $title = $url;
                // }
                $link = "<a href=\"$url\"
                            target=\"_blank\"
                            rel=\"noopener noreferrer\">$title</a>";
                return $link;
            },
            $this->content);
        return $this->content;
    }
}


class ImageNote extends Note {
    function __construct($fpath) {
        parent::__construct($fpath);
        $this->type = "image";
    }
    
    function content_as_html() {
        return "<a href=\"$this->url\"><img src=\"$this->url\"></a>";
    }
}


class AudioNote extends Note {
    function __construct($fpath) {
        parent::__construct($fpath);
        $this->type = "audio";
    }
    
    function content_as_html() {
        return "<audio controls src=\"$this->url\"></audio>";
    }
}


class VideoNote extends Note {
    function __construct($fpath) {
        parent::__construct($fpath);
        $this->type = "video";
    }
    
    function content_as_html() {
        return "<video controls src=\"$this->url\"></video>";
    }
}


class FileNote extends Note {
    function __construct($fpath) {
        parent::__construct($fpath);
        $this->type = "file";
    }
    
    function content_as_html() {
        return "File: <a href=\"$this->url\">$this->fname</a> (size: "
               . $this->humanreadable_fsize()
               . ")";
    }
}
?>
