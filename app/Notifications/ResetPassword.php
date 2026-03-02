<?php

namespace App\Notifications;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Helpers\EmailHelpers;

class ResetPassword extends Notification
{
    
    use Queueable;

    /**
     * The password reset token.
     *
     * @var string
     */
    public $token;
    public $email;

    /**
     * Create a notification instance.
     *
     * @param  string  $token
     * @return void
     */
    public function __construct($token)
    {
        $this->token = $token;
        //$this->email = $email;
    }

    /**
     * Get the notification's channels.
     *
     * @param  mixed  $notifiable
     * @return array|string
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {   


            $lang_id = session('default_lang');
            $emailReplaceData = [];
            //dd(action('Auth\ResetPasswordController@reset', $this->token));
            /****set email conduction for user*****/
            $emailReplaceData['USER_NAME'] = $notifiable->first_name." ".$notifiable->last_name;
            //$emailReplaceData['CUSTOMER_LNAME'] = $notifiable->last_name;
            $emailReplaceData['RESET_URL'] = action('Auth\ResetPasswordController@reset', $this->token);

            $emailData = ['lang_id'=>$lang_id, 'relevantdata'=>$emailReplaceData,'user_email'=>$notifiable->email, 'is_cron' => 2 , 'user_type' => 'user'];


            $event_slug = 'forget_password_mail';
            EmailHelpers::sendAllEnableNotification($event_slug, $emailData);
            echo true;
            exit;



    }
}
