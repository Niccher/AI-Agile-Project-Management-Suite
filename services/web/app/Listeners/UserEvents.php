<?php

namespace App\Listeners;

use CodeIgniter\Events\Events;
use CodeIgniter\Email\Email;
use CodeIgniter\Shield\Models\UserModel;

class UserEvents
{
    /**
     * Send a welcome email when a user registers or activates
     */
    public static function onUserRegistered($user)
    {
        // $user is typically the UserModel entity in Shield
        // Let's send a welcome email
        $email = service('email');
        
        $email->setTo($user->email);
        $email->setSubject('Welcome to Chege OS!');
        
        // Use a view for the email body
        $body = view('emails/welcome', ['user' => $user]);
        $email->setMessage($body);
        
        $email->send();
    }
}
