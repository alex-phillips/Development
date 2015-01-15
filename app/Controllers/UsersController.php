<?php

class UsersController extends AppController
{
    public function beforeFilter()
    {
        Auth::allow(array(
            'index',
            'login',
            'logout',
            'view',
            'add',
            'showCaptcha',
            'reset_password',
            'forgot_password',
            'verify',
        ));
    }

    public function login()
    {
        $this->set('title', 'Login');
        // Redirect if user is already logged in
        if (Session::isUserLoggedIn()) {
            Response::redirect('/');
        }

        $form = Form::create('login');

        $obj = $form->text('username', 'Username', '', array('autocompete' => 'off'));
        $obj->setRule(array(
                'required' => array('error', 'Please enter your username'),
            ));

        $obj = $form->password('password', 'Password', '', array('autocompete' => 'off'));
        $obj->setRule(array(
                'required' => array('error', 'Please enter your password'),
            ));

        $form->add('checkbox', 'rememberme', 'yes');
        $form->add('label', 'label_rememberme', 'rememberme_yes', 'Remember Me', array('style' => 'font-weight:normal'));

        $form->submit('btnsubmit', 'Login');

        if ($form->validate()) {
            $this->User = User::findFirst(array(
                    'conditions' => array(
                        'username' => Request::post()->get('username'),
                    ),
                ));
            if ($this->User && $this->User->id && Security::verifyHash(Request::post()->get('password'), $this->User->password)) {
                if ($this->User->active == 1) {
                    Auth::login($this->User);

                    // Set remember me token and cookie
                    if (Request::post()->get('rememberme')) {

                        // generate 64 char random string
                        $random_token_string = hash('sha256', mt_rand());

                        Request::post()->set('rememberme_token', $random_token_string);
                        Request::post()->Set('id', $this->User->id);
                        $this->User = User::create(Request::post()->getAll());
                        $this->User->save();

                        // generate cookie string that consists of userid, randomstring and combined hash of both
                        $cookie_string_first_part = $this->User->id . ':' . $random_token_string;
                        $cookie_string_hash = hash('sha256', $cookie_string_first_part);
                        $cookie_string = base64_encode($cookie_string_first_part . ':' . $cookie_string_hash);

                        // set cookie (2 weeks)
                        Response::setCookie(Cookie::make('rememberme', $cookie_string, time() + 1209600, "/"));
                    }

                    Session::setFlash('Welcome, ' . $this->User->username, 'success');

                    if ($referrer = Request::query()->get('forward_to')) {
                        Response::redirect($referrer);
                    }
                    Response::redirect('/invitees/');
                }
                else {
                    $form->addError('error', 'Your account is not activated yet. Please click on the confirm link in the mail.');
                }
            }
            else {
                $form->addError('error', 'Username or password is incorrect');
            }
        }

        $this->set('form', $form);
    }

    public function logout()
    {
        Auth::logout();
        Response::redirect('/');
    }

    public function view($id = null)
    {
        if ($id == null) {
            Response::redirect('/users/view/' . Session::read('Auth.id'));
        }

        if (is_numeric($id)) {
            $this->User = User::findById($id);
        }
        else {
            $this->User = User::findByUsername($id);
        }

        if (!$this->User) {
            Session::setFlash("That user does not exist", 'failure');
            Response::redirect('/');
        }

        if ($this->User->id == '') {
            Router::abort();
        }

        Primer::setValue('rendering_object', $this->User);

        $this->set('title', $this->User->username);
        $this->set('user', $this->User);
    }

    public function edit($id = null)
    {
        // If no ID is passed, use currently logged in user
        if ($id == null) {
            Response::redirect('/users/edit/' . Session::read('Auth.id'));
        }

        if ($id != Session::read('Auth.id') && !Session::isAdmin()) {
            Session::setFlash("You are not authorized to edit that user", 'failure');
            Response::redirect('/users/index');
        }

        $this->User = User::findById($id);
        $this->set('title', 'Edit ' . $this->User->username . '\'s Account');

        if ($this->User->id == '') {
            Session::setFlash('User does not exist', 'failure');
            Response::redirect('/');
        }

        $form = Form::create('user_edit');
        $form->hidden('id', $this->User->id);

        $obj = $form->text('email', 'Email', $this->User->email);
        $obj->setRule(array(
                'required' => array('error', 'Please enter your email address'),
                'email' => array('error', 'Please enter a valid email address'),
            ));

        $obj = $form->text('name', 'Name', $this->User->name);

        $obj = $form->textarea('bio', 'Bio', $this->User->bio);

        $obj = $form->file('profile_image', 'Profile Image', array(
                'disabled' => 'disabled'
            )
        );
        $obj->setRule(
            array(
                'upload'   => array(
                    APP_ROOT . '/public/tmp/',
                    ZEBRA_FORM_UPLOAD_RANDOM_NAMES,
                    'error',
                    'Unable to upload file'
                ),
                'filetype' => array(
                    'jpg, jpeg, gif, png',
                    'error',
                    'Please choose a valid picture'
                ),
            )
        );

        $form->add('label', 'label_current_password', 'current_password', 'Current Password');
        $obj = $form->password('current_password', 'Current Password');
        $obj->setRule(array(
                'required' => array('error', 'Please enter your current password to save changes'),
            ));

        $obj = $form->password('new_password1', 'New Password');

        $obj = $form->password('new_password2', 'Repeat New Password');

        $form->submit('btnsubmit', 'Save Changes');

        if ($form->validate()) {
            // Some form SPECIFIC validation

            // Require current password to save changes
            if (!Request::post()->get('current_password')) {
                Session::setFlash("Please enter your current password to save changes", 'failure');
            }
            // Verify current password is correct
            else if (!password_verify(Request::post()->get('current_password'), $this->User->password)) {
                Session::setFlash("Current password is incorrect", 'failure');
            }
            else if (Request::post()->get('new_password1') != Request::post()->get('new_password2')) {
                Session::setFlash("The new passwords don't match", 'failure');
            }
            else {
                // Escape email address
                Request::post()->set('email', htmlentities(Request::post()->get('email'), ENT_QUOTES));

                // TODO: better way to go about doing this, for security reasons. For ALL models...
                // We are already checking ownership on one of the ID's, but which is best, and they
                // either BOTH need to equal, or make the SQL query on the one we check...
                if ($id != Request::post()->get('id')) {
                    Session::setFlash('User IDs do not match. Please try again.', 'failure');
                }
                else {
                    // Attempt to update the user in the database
                    $this->User->update(Request::post()->getAll());
                    if ($this->User->save()) {
                        // Find user again to get updated information into the Session
                        $this->User = $this->User->findById($id);
                        Auth::login($this->User);
                        Session::setFlash('Your account has been successfully updated', 'success');
                        Response::redirect('/users/view/' . $id);
                    }
                    else {
                        Session::setFlash($this->User->getErrors(), 'failure');
                    }
                }
            }
        }

        $this->set('form', $form);
        // Set default text in textarea to current bio
        Primer::setJSValue('bio', $this->User->bio, 'user');
    }

    public function delete($id = null)
    {
        if (Request::is('post') && Session::isAdmin()) {
            $this->User->deleteById(Request::post()->get('data.user.id'));
            Response::redirect('/users/');
        }
    }

    // register page
    // TODO: need this function to define captcha. find a way to integrate this into register()
    public function add()
    {
        $this->set('title', 'Register');

        $form = Form::create('add');

        $obj = $form->text('username', 'Username');
        $obj->setRule(array(
                'required' => array('error', 'Username is required'),
            ));

        $obj = $form->text('email', 'Email');
        $obj->setRule(array(
                'required' => array('error', 'Email address is required'),
                'email' => array('error', 'Please enter a valid email address'),
            ));

        $obj = $form->password('password1', 'Password');
        $obj->setRule(array(
                'required' => array('error', 'Please enter a password'),
            ));

        $obj = $form->password('password2', 'Repeat Password');
        $obj->setRule(array(
                'required' => array('error', 'Please repeat your password'),
            ));


        // "captcha"
        $form->add('captcha', 'captcha_image', 'captcha_code');
        $form->add('label', 'label_captcha_code', 'captcha_code', 'Are you human?');
        $obj = $form->add('text', 'captcha_code');
        $form->add('note', 'note_captcha', 'captcha_code', 'You must enter the characters with black color that stand out from the other characters');
        $obj->setRule(array(
                'required'  => array('error', 'Enter the characters from the image above!'),
                'captcha'   => array('error', 'Characters from image entered incorrectly!')
            ));

        $form->submit('btnsubmit', 'Register');

        if ($form->validate()) {
            if (Request::post()->get('password1') !== Request::post()->get('password2')) {
                Session::setFlash("Passwords do not match", 'failure');
            }
            else {
                // Set password field
                Request::post()->set('data.user.password', Request::post()->get('data.user.password1'));

                $this->User = User::create(Request::post()->getAll());
                $this->User->password = Request::post()->get('password1');
                // generate random hash for email verification (40 char string)
                $this->User->activation_hash = sha1(uniqid(mt_rand(), true));

                if ($this->User->save()) {
                    Session::setFlash('An activation e-mail has been sent', 'success');
                    Response::redirect('/posts/');
                }
                else {
                    Session::setFlash('There was a problem creating the user. Please try again.', 'failure');
                }
            }
        }

        $this->set('form', $form);
    }

    /**
     * Verify new user creation with e-mailed link and make account active
     *
     * @param $email
     * @param $user_verification_code
     */
    public function verify($email, $user_verification_code)
    {
        if ($email && $user_verification_code) {
            $users = $this->User->find(array(
                'conditions' => array(
                    'AND' => array(
                        'email' => urldecode($email),
                        'activation_hash' => urldecode($user_verification_code),
                    )
                )
            ));
            if (!empty($users) && sizeof($users) === 1) {
                $this->User = $users[0];
                $this->User->active = 1;
                $this->User->activation_hash = null;
                if ($this->User->save()) {
                    Session::setFlash('You may now log in', 'success');
                }
                else {
                    Session::setFlash($this->User->getErrors(), 'failure');
                }
            }
            else {
                Session::setFlash('There was a problem verifying that account. Please contact support.', 'failure');
            }
        }

        Response::redirect('/posts/');
    }

    public function forgot_password()
    {
        $this->set('title', 'Request Password Reset');

        $form = Form::create('users');

        $obj = $form->text('username', 'Username');
        $obj->setRule(array(
                'required' => array('error', 'Please enter your username'),
            ));

        $form->submit('btnsubmit', 'Reset Password');

        if ($form->validate()) {
            $username = htmlentities(Request::post()->get('username'), ENT_QUOTES, 'utf-8');
            $users = $this->User->find(array(
                'conditions' => array(
                    'username' => $username
                )
            ));
            if (!empty($users)) {
                $this->User = array_shift($users);
            }
            if ($this->User->username === $username) {
                $timestamp = time();
                $this->User->password_reset_hash = sha1(uniqid(mt_rand(), true));
                $this->User->password_reset_timestamp = $timestamp;

                if ($this->_sendPasswordResetMail() == true) {
                    $this->User->save();
                }
                else {
                    Session::setFlash('There was a problem sending you your reset password. Please contact webmaster', 'failure');
                    Response::redirect('/');
                }
            }

            // Regardless of whether a user was found, output the same message so that this form can't be used
            // to determine if a username actually exists or not.
            Session::setFlash('Please check your email with instructions on resetting your password', 'success');
            Response::redirect('/');
        }

        $this->set('form', $form);
    }

    private function _sendPasswordResetMail()
    {
        $link = 'http://www.wootables.com/users/verifypasswordrequest/' . urlencode($this->User->username) . '/' . urlencode($this->User->password_reset_hash);
        $body = 'Please click on this link to reset your password: <a href="' . $link . '">' . $link . '</a>';

        return Mail::send(
            array(
                'from'      => 'noreply@wootables.com',
                'fromName'  => 'noreply@wootables.com',
                'to' => $this->User->email,
                'subject'   => 'Password Reset for wootables.com',
                'body'      => $body,
            )
        );
    }

    public function reset_password($username, $verification_code)
    {
        if (Request::is('post')) {
            $users = $this->User->find(array(
                'conditions' => array(
                    'username' => Request::post()->get('data.user.username')
                )
            ));
            if (!empty($users)) {
                $this->User = $users[0];
                if ($this->User->password_reset_hash == Request::post()->get('data.user.password_reset_hash')) {
                    if (Request::post()->get('data.user.newpass1') === Request::post()->get('data.user.newpass2')) {
                        $this->User->password = Request::post()->get('data.user.newpass1');
                        $this->User->password_reset_hash = null;
                        $this->User->password_reset_timestamp = null;
                        $this->User->save();
                        Session::setFlash('Your password has been successfully updated', 'success');
                        Response::redirect('/');
                    }
                }
            }
        }
        else {
            $username = htmlspecialchars($username, ENT_QUOTES, 'utf-8');
            $verification_code = htmlentities($verification_code, ENT_QUOTES);

            $users = $this->User->find(array(
                'conditions' => array(
                    'AND' => array(
                        'username' => $username,
                        'password_reset_hash' => $verification_code
                    )
                )
            ));
            if (!empty($users)) {
                $this->User = $users[0];
                // 3600 seconds are 1 hour
                $timestamp_one_hour_ago = time() - 3600;

                if ($this->User->password_reset_timestamp > $timestamp_one_hour_ago) {
                    $this->set('username', $this->User->username);
                    $this->set('password_reset_hash', $this->User->password_reset_hash);
                    return;
                } else {
                    Session::setFlash('Your reset link has expired. Please try again.');
                    Response::redirect('/login/');
                }
            }
            else {
                Response::redirect('/');
            }
        }
    }

    /**
     * special helper method:
     * showCaptcha() returns an image, so we can use it in img tags in the views, like
     * <img src="/users/showCaptcha" />
     */
    public function showCaptcha()
    {
        Security::generateCaptcha();
        Security::showCaptcha();
    }
}