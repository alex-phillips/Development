<?php
$name = $user->name ?: $user->username;
?>

<div class="media">
    <a class="pull-left" href="#">
        <img class="media-object" src="<?php echo $user->avatar ?>" alt="avatar">
    </a>
    <div class="media-body">
        <h4 class="media-heading">Bio: <?php echo htmlspecialchars($name, ENT_QUOTES, 'utf-8'); ?></h4>
        <?php echo htmlspecialchars($user->bio, ENT_QUOTES, 'utf-8'); ?>
    </div>
</div>