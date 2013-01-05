<?php
class Martha {
  protected $inline = [
    'escape'    => '/^\\\\([\\\\`*\[\]()\#\-.>])/',
    'emphasis'  => '/^\*(?!\*)(.+?)\*(?!\*)/',
    'strong'    => '/^\*\*(?!\*)(.+?)\*\*(?!\*)/',
    'command'   => '/^`(.+?)`/',
    'link'      => '/^<(".+?") *(".+?")? *(".+?")?>/',
    'image'     => '/^\[(".+?") *(".+?")? *(".+?")?\]/',
    'tag'       => '/^<(?:(\w+)\b[\s\S]+?<\/\1>|[\s\S]+?\s+\/>)/',
    'br'        => '/^ {2,}\n(?!\n)/',
    'text'      => '/^[\s\S]+?(?=[\\\\<\[*`]| {2,}\n|$)/'
  ];

  protected $block = [
    'heading'   => '/^(\#{1,6}) *(.+)\n?/',
    'code'      => '/^( {2}.*\n?)+/',
    'bullets'   => '/^(- *.*\n?)+/',
    'numbers'   => '/^(\d+\. *.*\n?)+/',
    'quote'     => '/^(> *.*\n?)+/',
    'html'      => '/^<(?:(\w+)\b[\s\S]+?<\/\1>
                     |[\s\S]+?\s+\/>|<!--[\s\S]*?-->)/x',
    'paragraph' => '/^(.+\n?(?!<(\w+)\b[\s\S]+?<\/\2>|<[\s\S]+?\s+\/>
                     |<!--[\s\S]*?-->|(\#{1,6})\ *(.+)\n?|(\ {2}.*\n?)+
                     |(-\ *.*\n?)+|(\d+\.\ *.*\n?)+|(>\ *.*\n?)+))+\n*/x',
    'text'      => '/^.+\n*/',
    'newline'   => '/^\n+/'
  ];

  public function go($input) {
    $input = preg_replace(['/\r\n|\r/', '/\t/'], ['\n', '  '], $input);
    return $this->block($input);
  }

  protected function block($input) {
    return $this->parse($input, $this->block);
  }

  protected function inline($input) {
    return $this->parse($input, $this->inline);
  }

  protected function parse($input, $rules) {
    $output = '';

    while ($input) {
      foreach ($rules as $rule => $regex) {
        if (preg_match($regex, $input, $match)) {
          $input   = substr($input, strlen($match[0]));
          $output .= $this->$rule($match);
          break;
        }
      }
    }

    return $output;
  }

  protected function encode($html, $encode = false) {
    return str_replace(['<', '>'], ['&lt;', '&gt;'],
      preg_replace($encode ? '/&/' : '/&(?!#?\w+;)/', '&amp;', $html)
    );
  }

  protected function squash($pattern, $input) {
    return preg_replace([$pattern, '/\n+$/'], '', $input);
  }

  protected function li($input) {
    return preg_replace('/^(.+)$/m', '<li>$0</li>', $input);
  }

  protected function heading($match) {
    $level = strlen($match[1]);
    $title = $this->inline($match[2]);
    return "<h$level>$title</h$level>\n";
  }

  protected function code($match) {
    $code = $this->squash('/^ {2}/m', $match[0]);
    $code = $this->encode($code, true);
    return "<pre>\n$code\n</pre>\n";
  }

  protected function bullets($match) {
    $list = $this->squash('/^- */m', $match[0]);
    $list = $this->li($this->inline($list));
    return "<ul>\n$list\n</ul>\n";
  }

  protected function numbers($match) {
    $list = $this->squash('/^\d+\. */m', $match[0]);
    $list = $this->li($this->inline($list));
    return "<ol>\n$list\n</ol>\n";
  }

  protected function quote($match) {
    $quote = $this->squash('/^> */m', $match[0]);
    return "<blockquote>\n{$this->block($quote)}</blockquote>\n";
  }

  protected function html($match) {
    $html = "$match[0]\n";
    return isset($match[1]) && $match[1] == 'pre' ?
      $this->inline($html) : $html;
  }

  protected function paragraph($match) {
    $text = rtrim($match[0], "\n");
    return "<p>{$this->inline($text)}</p>\n";
  }

  protected function tag($match) {
    return $match[0];
  }  

  protected function text($match) {
    return $this->encode($match[0]);
  }

  protected function newline($match) {
    return;
  }

  protected function escape($match) {
    return $match[1];
  }

  protected function emphasis($match) {
    return "<em>{$this->inline($match[1])}</em>";
  }

  protected function strong($match) {
    return "<strong>{$this->inline($match[1])}</strong>";
  }

  protected function command($match) {
    return "<code>{$this->encode($match[1])}</code>";
  }

  protected function link($match) {
    $link  = "<a href={$match[1]}";
    $link .= isset($match[3]) ? " title={$match[1]}>" : ">";
    $link .= trim(isset($match[2]) ? $match[2] : $match[1], '"');
    $link .= "</a>";
    return $link;
  }

  protected function image($match) {
    $image  = "<img src={$match[1]}";
    $image .= isset($match[3]) ? " title={$match[3]}" : "";
    $image .= isset($match[2]) ? " alt={$match[2]}"   : "";
    $image .= " />";
    return $image;
  }

  protected function br($match) {
    return "<br />$match[0]";
  }
}