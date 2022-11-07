<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Mail\SendMail;

class OrderCreate extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public $data;

    public function __construct($data)
    {
        $this->data = $data;
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
        return (new SendMail($this->data))
                ->to($notifiable->email)
                ->subject($this->data['message_subject']);

        /*
        return (new MailMailableSend($this->mailable, $this->data,$this->type))->to($email)
            ->bcc(isset($this->mailable->bcc)? json_decode($this->mailable->bcc) : [])
            ->cc(isset($this->mailable->cc)? json_decode($this->mailable->cc) : [])
            ->subject($this->subject);
        // return (new MailMessage)
        //             ->subject($this->data['subject'])
        //             ->line('The introduction to the notification.')
        //             ->line('Thank you for using our application!');
        */
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
