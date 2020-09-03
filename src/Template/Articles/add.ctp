<!-- File: src/Template/Articles/add.ctp -->

<h1>Add Article</h1>
<?php
    echo $this->Form->create($article); # This is equivalent to <form method="post" action="/articles/add">
    # Because we called create() without a URL option, FormHelper assumes we want the form to submit back to the current action.
    // Hard code the user for now.
    echo $this->Form->control('user_id', ['type' => 'hidden', 'value' => 1]);
    echo $this->Form->control('title');
    echo $this->Form->control('body', ['rows' => '3']);
    #echo $this->Form->control('tags._ids', ['options' => $tags]); # For the user to choose the tag
    echo $this->Form->control('tag_string', ['type' => 'text']);
    echo $this->Form->button(__('Save Article'));
    echo $this->Form->end();
?>