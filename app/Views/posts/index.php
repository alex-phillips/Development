<?php

$markup = '';

if (isset($posts)) {
    foreach ($posts as $post) {
        $edit_link = '';
        if (Session::read('Auth.role') === 'admin') {
            $edit_link = '<a href="/posts/edit/' . $post->id . '">Edit</a>';
        }
        $date = $post->created->diffForHumans();
        $panel = 'panel-default';
        if ($post->no_publish == 1) {
            $panel = 'panel-danger';
        }

        $admin_links = '';
        if (Session::read('Auth.role') === 'admin' || Session::read('Auth.id') == $post->id_user) {
            $admin_links = <<<__TEXT__
                <br/><br/>
                <a href="/posts/edit/$post->id">Edit</a> |
                <a href="/posts/delete/$post->id">Delete</a>
__TEXT__;

        }

        $markdownParser = new Parsedown();
        $body = preg_match('#\A(.+)#', $post->body, $matches);
        $body = $markdownParser->text(htmlspecialchars($matches[1], ENT_QUOTES, 'utf-8'));

        $markup .= <<<___HTML___
            <article>
                <h3><a href="/post/$post->slug">$post->title</a></h3>
                <h6>$date</h6>
                <p>$body</p>
                <a href="/post/$post->slug">Read More</a>
                $admin_links
            </article>
            <hr/>
___HTML___;

    }
}
else {
    $markup = '<h1>No content</h1>';
}

echo $markup;
