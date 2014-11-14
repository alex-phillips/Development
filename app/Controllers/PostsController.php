<?php

class PostsController extends AppController
{
    public $name = 'post';

    // Override default config for pagination
    public $paginationConfig = array(
        'order' => array(
            'Post.created DESC',
        ),
    );

    public function beforeFilter()
    {
        Auth::allow(
            array(
                'index',
                'view',
                'quotes',
            )
        );
    }

    public function index()
    {
        $params = array(
            'type' => 'post',
        );

        // If not admin, only view publishable posts
        if (!Session::isAdmin()) {
            $params['no_publish'] = 0;
        }

        View::set('title', 'Home');
        View::set('posts', View::paginate('Post', array('AND' => $params)));
    }

    public function my_posts()
    {
        $params = array(
            'type' => 'post',
        );

        // If not admin, only view publishable posts
        if (!Session::isAdmin()) {
            $params['id_user'] = Session::read('Auth.id');
        }

        View::set('posts', View::paginate(
            'Post',
            array('id_user' => Session::read('Auth.id'))
        ));
        View::set('title', 'My Posts');
        View::render('posts.index');
    }

    public function add()
    {
        View::set('title', 'New Post');
        View::addJS('posts/add');

        $form = Form::create('add');

        $form->add('label', 'label_type', 'type', 'Type');
        $obj = $form->add('select', 'type');
        $options = array();
        foreach (Post::$types as $type) {
            $options[$type] = $type;
        }
        $obj->add_options($options);
        $obj->setRule(
            array(
                'required' => array('error', 'Please select a post type'),
            )
        );

        $checked = $this->Post->no_publish ? array('checked' => 'checked') : array();
        $obj = $form->add('checkbox', 'no_publish', '1', $checked);
        $form->add('label', 'label_no_publish', 'no_publish_1', 'No publish');

        $form->add('label', 'label_title', 'title', 'Title');
        $obj = $form->add('text', 'title');
        $obj->setRule(array(
                'required' => array('error', 'Please include a title'),
            ));

        $form->add('label', 'label_body', 'body', 'Body');
        $obj = $form->add('textarea', 'body');
        $obj->setRule(array(
                'required' => array('error', 'Please include a a body'),
            ));

        $form->add('submit', 'btnsubmit', 'Save');

        if ($form->validate()) {

            $post = Post::create(Request::post()->getAll());
            // Set currently signed-in user as creator
            $post->id_user = Session::read('Auth.id');

            if ($post->save()) {
                Session::setFlash('Post created successfully', 'success');
                Response::redirect('/posts/');
            }
        }

        View::set('form', $form);
    }

    public function view($id)
    {
        if (is_numeric($id)) {
            $this->Post = $this->Post->findById($id);
        }
        else {
            $posts = $this->Post->find(array(
                'conditions' => array(
                    'slug' => $id
                )
            ));
            $this->Post = array_shift($posts);
        }

        if ($this->Post->id == '') {
            Session::setFlash('That post does not exist', 'failure');
            Response::redirect('/posts/');
        }
        if (($this->Post->no_publish && !Session::isAdmin()) && $this->Post->id_user !== Session::read('Auth.id')) {
            Session::setFlash('That post does not exist', 'failure');
            Response::redirect('/posts/');
        }

        View::set(
            array(
                'post'     => $this->Post,
                'title'    => $this->Post->title,
                'subtitle' => date('F d, Y', strtotime($this->Post->created)),
            )
        );
    }

    public function edit($id)
    {
        View::set('title', 'Edit Post');

        View::addJS('posts/edit');
        $this->Post = Post::findById($id);

        if (!$this->Post) {
            Session::setFlash('That post does not exist', 'failure');
            Response::redirect('/posts/');
        }

        if ($this->Post->id_user != Session::read('Auth.id') && !Session::isAdmin()) {
            Session::setFlash('You are not authorized to edit that post', 'warning');
            Response::redirect('/posts/');
        }

        // TODO: better way to go about doing this, for security reasons. For ALL models...
        // We are already checking ownership on one of the ID's, but which is best, and they
        // either BOTH need to equal, or make the SQL query on the one we check...
        if (Request::post()->get('data.post.id') && $id != Request::post()->get('data.post.id')) {
            Session::setFlash('Post IDs do not match. Please try again.', 'failure');
            Response::redirect('/posts/edit/' . $id);
        }

        $form = Form::create('edit');

        $form->add('label', 'label_type', 'type', 'Type');
        $obj = $form->add('select', 'type', $this->Post->type);
        $options = array();
        foreach (Post::$types as $type) {
            $options[$type] = $type;
        }
        $obj->add_options($options);
        $obj->setRule(
            array(
                'required' => array('error', 'Please select a post type'),
            )
        );

        $checked = $this->Post->no_publish ? array('checked' => 'checked') : array();
        $obj = $form->add('checkbox', 'no_publish', '1', $checked);
        $form->add('label', 'label_no_publish', 'no_publish_1', 'No publish');

        $form->add('label', 'label_title', 'title', 'Title');
        $obj = $form->add('text', 'title', $this->Post->title);
        $obj->setRule(array(
                'required' => array('error', 'Please include a title'),
            ));

        $form->add('label', 'label_body', 'body', 'Body');
        $obj = $form->add('textarea', 'body', $this->Post->body);
        $obj->setRule(array(
                'required' => array('error', 'Please include a a body'),
            ));

        $form->add('submit', 'btnsubmit', 'Save');

        if ($form->validate()) {
            $this->Post->set(Request::post()->getAll());
            if ($this->Post->save()) {
                Session::setFlash('Post was updated successfully', 'success');
                Response::redirect('/posts/view/' . $id);
            }
            Session::setFlash('There was a problem updating the post', 'failure');
        }

        Primer::setJSValue('post', $this->Post);
        View::set('form', $form);
        View::set('post', $this->Post);
    }

    public function delete($id = null)
    {
        $form = Form::create('delete');
        $form->add('hidden', 'id', $id);
        $form->add('submit', 'btnsubmit', 'Delete Post');

        if ($form->validate() && Session::isAdmin()) {
            if (PostdeleteById($id)) {
                Session::setFlash('Post has been successfully deleted', 'success');
                Response::redirect('/');
            }
            else {
                Session::setFlash('There was a problem deleting that post', 'failure');
                Response::redirect('/');
            }
        }
        else if (Session::isAdmin()) {
            View::set('post', Post::findById($id));
        }
        else {
            Session::setFlash('You are not authorized to delete posts', 'warning');
            Response::redirect('/');
        }
    }

}