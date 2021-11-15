<?php

function visit_website() {
    if ($_GET['hash']) { 
        clicked_link(); 
        return;
    }

    echo "Enter email: \n";
    $email = "joakim@saettem.org";
    
    // Check whether this is a valid email at all.
    validate_email($email);
    
    // Is user in database?
    $user = User::find('email', $email);

    if (!$user) {
        if ($registration_enabled) {
            $user = User::create(['email' => $email]);
            $user->save();
        }
        else {
            echo "No such user\n";
        }
    }
    
    // Create the hash
    $session = Session::create(['hash' => $user->hash(), 'user_id' => $user->id]);
    $session->save();
    
    // Send the email with login link.
    $user->emailLoginLink();
    
    // Print the message asking user to check their email for the login link.
    echo "Check your email for a link to login with!\n";
}

function clicked_link() {
    $hash = $_GET['hash'];
    $session = Session::findOrFail('hash', $hash);
    $user = User::findOrFail('user_id', $session->user_id);

    $session->login_timestamp = now();
    $session->save();

    set_hash_cookie();
    // Logged in!
}

function set_hash_cookie() {
    //
}

function User::hash() {
    $ip = $_GET['ip'];
    $email = $user->email;
    $hash = crypt($email, $ip);
    return $hash;
}