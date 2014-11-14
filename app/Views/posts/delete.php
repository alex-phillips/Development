
<h1>Confirm Delete</h1>
<p>Are you sure you want to delete this post?</p>

<?php

echo $this->post->title;
echo $this->post->body;

$this->form->render();