<?php
class Note {
    function __construct($fpath) {
        $this->fpath = $fpath;
        $this->fsize = filesize($fpath);
        preg_match("/^(.*\/)?(((\d{14})-\d{5})-?((?:.+)?\.(.+)))$/", $this->fpath, $match);
        list(,
             $this->fdir,
             $this->fname,
             $this->id,
             $this->date_digitsonly,
             $this->original_filename,
             $this->extension) = $match;
        $app_root_escaped = str_replace("/", "\/", preg_quote(APP_ROOT));
        // This is the absolute path in terms of the website, i.e. `/` being the
        // app root.
        $this->fpath_absolute = preg_replace("/^$app_root_escaped/", "", $this->fpath);
        $this->location = array_search(
            pathinfo($this->fdir)["basename"],
            array(
                "main" => "contents",
                "trash" => ".trash"
            )
        );
        $this->date_human = DateTime::createFromFormat("YmdHis", $this->date_digitsonly)->format("D d M Y H:i T");
        $this->date_iso = DateTime::createFromFormat("YmdHis", $this->date_digitsonly)->format("c");
        $this->last_modified = filemtime($fpath);
        $this->last_modified_human = date("D d M Y H:i T", $this->last_modified);
        $this->last_modified_iso = date("c", $this->last_modified);
    }
    
    function get_info() {
        $info = [
            "id" => $this->id,
            "type" => $this->type,
            "location" => $this->location,
            "filepath" => $this->fpath_absolute,
            "filesize" => $this->fsize,
            "originalFilename" => $this->original_filename,
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
            return "0 B";
        }
        $units = ["KiB", "MiB", "GiB", "TiB", "PiB"];
        $magnitude = floor(log($this->fsize, 1024));
        return round($this->fsize / 1024 ** $magnitude)
               . " "
               . $units[$magnitude];
    }
    
    function as_html() {
        return "<div id=\"N$this->id\" class=\"note $this->type\" data-filepath=\"$this->fpath_absolute\">
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
                        <a class=\"download\" href=\"$this->fpath_absolute\" download><span class=\"hotkey\">d</span>ownload</a>
                        <span class=\"bump\"><span class=\"hotkey\">b</span>ump</span>
                        <span class=\"delete\"><span class=\"hotkey\">t</span>rash</span>
                    </div>
                </div>";
    }
}


class TextNote extends Note {
    function __construct($fpath) {
        parent::__construct($fpath);
        $this->type = "text";
        $this->content = rtrim(file_get_contents($this->fpath), "\n");
        $this->copy_target = "Note";
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
        $this->copy_target = "URL";
    }
    
    function content_as_html() {
        return "<a href=\"$this->fpath_absolute\"><img src=\"$this->fpath_absolute\"></a>";
    }
}


class AudioNote extends Note {
    function __construct($fpath) {
        parent::__construct($fpath);
        $this->type = "audio";
        $this->copy_target = "URL";
    }
    
    function content_as_html() {
        return "<audio controls src=\"$this->fpath_absolute\"></audio>";
    }
}


class VideoNote extends Note {
    function __construct($fpath) {
        parent::__construct($fpath);
        $this->type = "video";
        $this->copy_target = "URL";
    }
    
    function content_as_html() {
        return "<video controls src=\"$this->fpath_absolute\"></video>";
    }
}


class FileNote extends Note {
    function __construct($fpath) {
        parent::__construct($fpath);
        $this->type = "file";
        $this->copy_target = "URL";
    }
    
    function content_as_html() {
        return "File: <a href=\"$this->fpath_absolute\">$this->fname</a> (size: "
               . $this->humanreadable_fsize()
               . ")";
    }
}
?>
