<?php
// src/Controller/ArticlesController.php

namespace App\Controller;

class ArticlesController extends AppController
{
	public function initialize()
    {
        parent::initialize();

        $this->loadComponent('Paginator');
        $this->loadComponent('Flash'); // Include the FlashComponent
    }

	public function index()
    {
    	# fetch a paginated set of articles from the database, using the Articles Model that is automatically loaded via naming conventions
        $this->loadComponent('Paginator');
        $articles = $this->Paginator->paginate($this->Articles->find());
        # Passing the articles to the template
        $this->set(compact('articles'));
    }

    public function isAuthorized($user)
	{
	    $action = $this->request->getParam('action');
	    // The add and tags actions are always allowed to logged in users.
	    if (in_array($action, ['add', 'tags'])) {
	        return true;
	    }

	    // All other actions require a slug (being owner of an article) - if you are not, yu will be redirected to the page you came from
	    $slug = $this->request->getParam('pass.0');
	    if (!$slug) {
	        return false;
	    }

	    // Check that the article belongs to the current user.
	    $article = $this->Articles->findBySlug($slug)->first();

	    return $article->user_id === $user['id'];
	}
    /**
    The slug is the post we are choosing. If we choose the article named "First post", the previous link
    in the view will redirect us to http://localhost:8765/articles/view/first-post, so this last part, "first-post", will be passed to this method as $slug
    */
    public function view($slug = null)
	{
		# See the article in which the slug matches the chosen one
			# Find by slug: This method allows us to create a basic query that finds articles by a given slug. We then use firstOrFail() to either fetch the first record, or throw a NotFoundException.
	    # $article = $this->Articles->findBySlug($slug)->firstOrFail();
	    // Update this line
	    $article = $this->Articles->findBySlug($slug)->contain(['Tags'])
	        ->firstOrFail();
	    # Pass the article to the tempate
	    $this->set(compact('article'));
	}

	public function add()
    {
        $article = $this->Articles->newEntity();
        if ($this->request->is('post')) {
        	pr($this->request->getData());
        	# First, save the data in an "article" entity
            $article = $this->Articles->patchEntity($article, $this->request->getData());

            # Set the user_id from the session
            $article->user_id = $this->Auth->user('id');
            # The, we persist the entity using the ArticlesTable
            if ($this->Articles->save($article)) {
                $this->Flash->success(__('Your article has been saved.'));
                # Send the users back to the article list --> /articles
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('Unable to add your article.'));
        }
        // Get a list of tags.
        $tags = $this->Articles->Tags->find('list');

        // Set tags to the view context
        $this->set('tags', $tags);

        $this->set('article', $article);
    }

    public function edit($slug)
	{
	    # $article = $this->Articles->findBySlug($slug)->firstOrFail();
	    $article = $this->Articles->findBySlug($slug)->contain(['Tags'])
        ->firstOrFail();
	    if ($this->request->is(['post', 'put'])) {
	    	# Get the data of the editions. We use patchEntity to update the entity
	        $this->Articles->patchEntity($article, $this->request->getData(), 
	        [ 'accessibleFields' => ['user_id' => false] ]); # Disable modification of user_id.

	        #save the article with the new data
	        if ($this->Articles->save($article)) {
	            $this->Flash->success(__('Your article has been updated.'));
	            return $this->redirect(['action' => 'index']);
	        }
	        $this->Flash->error(__('Unable to update your article.'));
	    }
	    // Get a list of tags.
	    $tags = $this->Articles->Tags->find('list');

	    // Set tags to the view context
	    $this->set('tags', $tags);

	    $this->set('article', $article);
	}

	public function delete($slug)
	{
		# If the user attempts to delete an article using a GET request, allowMethod() will throw an exception. 
	    $this->request->allowMethod(['post', 'delete']);

	    $article = $this->Articles->findBySlug($slug)->firstOrFail();

	    if ($this->Articles->delete($article)) 
	    {
	        $this->Flash->success(__('The {0} article has been deleted.', $article->title));
	        return $this->redirect(['action' => 'index']);
	    }
	}
	// Method to filter articles by tags
	public function tags()
	{
	    // The 'pass' key is provided by CakePHP and contains all
	    // the passed URL path segments in the request.
	    $tags = $this->request->getParam('pass');

	    // Use the ArticlesTable to find tagged articles.
	    $articles = $this->Articles->find('tagged', [
	        'tags' => $tags
	    ]);

	    // Pass variables into the view template context.
	    $this->set([
	        'articles' => $articles,
	        'tags' => $tags
	    ]);
	}

}