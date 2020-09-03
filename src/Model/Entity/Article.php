<?php
// src/Model/Entity/Article.php
namespace App\Model\Entity;
// the Collection class
use Cake\Collection\Collection;
use Cake\ORM\Entity;

class Article extends Entity
{
    protected $_accessible = [
        '*' => true,
        'id' => false,
        'slug' => false,
        'tag_string' => true
    ];
    # This will let us access the $article->tag_string computed property
    protected function _getTagString()
	{
	    if (isset($this->_properties['tag_string'])) {
	        return $this->_properties['tag_string'];
	    }
	    if (empty($this->tags)) {
	        return '';
	    }
	    $tags = new Collection($this->tags);
	    $str = $tags->reduce(function ($string, $tag) {
	        return $string . $tag->title . ', ';
	    }, '');
	    return trim($str, ', ');
	}
}