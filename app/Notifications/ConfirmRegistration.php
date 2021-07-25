<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ConfirmRegistration extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public $pin;
    public $name;
    public $id;

    public function __construct($newPin, $newName, $newId)
    {
        //
        $this->pin = $newPin;
        $this->name = $newName;
        $this->id = $newId;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    
                    ->greeting('Hi '.$this->name.'!')
                    ->line('We are glad you`ve come this far. To continue, kindly use this PIN in your registration confirmation')
                    ->line($this->pin)
                    ->line(url('/').'/api/confirm-registration?id='.$this->id)
                    ->line('use that URL/endpoint in POSTMAN and do a POST Request')
                    ->line('Thank you for using our application!');
                   
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
