<?php

namespace Controllers;

use Controllers\Auth\User;

interface Auth
{

    /**
     * getUser
     *
     * @return User
     */
    public function getUser():?User;

    /**
     * createUserWithEmailAndPassword
     *
     * Creates a new user account associated with the specified email address and password.
     * On successful creation of the user account, this user will also be signed in to your application.
     * User account creation can fail if the account already exists or the password is invalid.
     * Note: The email address acts as a unique identifier for the user and enables an email-based password reset.
     * This function will create a new user account and set the initial user password
     *
     * @param string $email The user's email address.
     * @param string $password The user's chosen password
     *
     * @return User/null
     */
    public function createUser(array $params):?User;

    /**
     * signInWithEmailAndPassword
     *
     * Asynchronously signs in using an email and password.
     * Fails with an error if the email address and password do not match.
     * Note: The user's password is NOT the password used to access the user's email account.
     *
     * @param string $email The users email address.
     * @param string $password The users password address.
     *
     * @return User/null
     */
    public function login(string $identify, string $password):?array;

    /**
     * signOut
     *
     */
    public function signOut():void;
}
