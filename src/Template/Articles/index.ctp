<!-- File: src/Template/Articles/index.ctp -->
<h1>Articles</h1>
<!-- Add button -->
<?= $this->Html->link('Add Article', ['action' => 'add']) ?>

<table>
    <tr>
        <th>Title</th>
        <th>Created</th>
    </tr>

    <!-- Here is where we iterate through our $articles query object, printing out article info -->

    <?php foreach ($articles as $article): ?>
    <tr>
        <td>
            <?= $this->Html->link($article->title, ['action' => 'view', $article->slug]) ?>
        </td>
        <td>
            <?= $article->created->format(DATE_RFC850) ?>
        </td>
        <!-- Edit button -->
        <td>
            <?= $this->Html->link('Edit', ['action' => 'edit', $article->slug]) ?>
        
         <!-- Delete button (using POST method, as deleting with GET is dangerous)-->
            <?= $this->Form->postLink(
                'Delete',
                ['action' => 'delete', $article->slug],
                ['confirm' => 'Are you sure?'])
            ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<?= $this->Html->link('Log user in', ['action' => 'login', 'controller' => 'users']) ?>