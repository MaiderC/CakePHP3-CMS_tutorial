<?php
// src/Model/Table/ArticlesTable.php
namespace App\Model\Table;
// the Validator class
use Cake\Validation\Validator;
// the table class
use Cake\ORM\Table;
// the Text class
use Cake\Utility\Text;
// the Query class
use Cake\ORM\Query;

class ArticlesTable extends Table
{
    public function initialize(array $config)
    {
        $this->addBehavior('Timestamp');
        # create an association between Articles and Tags
        # This will tell the Articles table model that there is a join table associated with tags
        $this->belongsToMany('Tags', [
	        'joinTable' => 'articles_tags',
	        'dependent' => true
    	]);
    }
    # Before saving an article, we need to make sure the slug (url-save name of the article) is not null.
    public function beforeSave($event, $entity, $options)
	{
		if ($entity->tag_string) {
	        $entity->tags = $this->_buildTags($entity->tag_string);
	    }
	    
		# If we are creating a new entity with no slug
	    if ($entity->isNew() && !$entity->slug) {
	        $sluggedTitle = Text::slug($entity->title);
	        // trim slug to maximum length defined in schema
	        $entity->slug = substr($sluggedTitle, 0, 191);
	    }
	}

	protected function _buildTags($tagString)
	{
	    // Trim tags
	    $newTags = array_map('trim', explode(',', $tagString));
	    // Remove all empty tags
	    $newTags = array_filter($newTags);
	    // Reduce duplicated tags
	    $newTags = array_unique($newTags);

	    $out = [];
	    $query = $this->Tags->find()
	        ->where(['Tags.title IN' => $newTags]);

	    // Remove existing tags from the list of new tags.
	    foreach ($query->extract('title') as $existing) {
	        $index = array_search($existing, $newTags);
	        if ($index !== false) {
	            unset($newTags[$index]);
	        }
	    }
	    // Add existing tags.
	    foreach ($query as $tag) {
	        $out[] = $tag;
	    }
	    // Add new tags.
	    foreach ($newTags as $tag) {
	        $out[] = $this->Tags->newEntity(['title' => $tag]);
	    }
	    return $out;
	}

	public function validationDefault(Validator $validator)
	{
	    $validator
	        ->allowEmptyString('title', false) # The title can't be empty
	        ->minLength('title', 10) 
	        ->maxLength('title', 255)

	        ->allowEmptyString('body', false) # The body can't be empty either
	        ->minLength('body', 10);

	    return $validator;
	}

	// The $query argument is a query builder instance.
	// The $options array will contain the 'tags' option we passed
	// to find('tagged') in our controller action.
	public function findTagged(Query $query, array $options)
	{
	    $columns = [
	        'Articles.id', 'Articles.user_id', 'Articles.title',
	        'Articles.body', 'Articles.published', 'Articles.created',
	        'Articles.slug',
	    ];

	    $query = $query
	        ->select($columns)
	        ->distinct($columns);

	    if (empty($options['tags'])) {
	        // If there are no tags provided, find articles that have no tags.
	        $query->leftJoinWith('Tags')
	            ->where(['Tags.title IS' => null]);
	    } else {
	        // Find articles that have one or more of the provided tags.
	        $query->innerJoinWith('Tags')
	            ->where(['Tags.title IN' => $options['tags']]);
	    }

	    return $query->group(['Articles.id']);
	}

}