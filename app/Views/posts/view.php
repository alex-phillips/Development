<?php

$markdownParser = new Parsedown();
$post->body = $markdownParser->text(htmlspecialchars($post->body, ENT_QUOTES, 'utf-8'));
$post->body = preg_replace_callback('#\<code(.+?)\>(.+?)\<\/code\>#s', function ($matches) {
    return '<code' . $matches[1] . '>' . htmlspecialchars_decode($matches[2]) . '</code>';
}, $post->body);

$markup = <<<___HTML___
<p>{$post->body}</p>
___HTML___;

echo $markup;
