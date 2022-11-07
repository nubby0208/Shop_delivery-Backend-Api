<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\OneSignal\OneSignalChannel;
use NotificationChannels\OneSignal\OneSignalMessage;
use NotificationChannels\OneSignal\OneSignalWebButton;
use App\Models\AppSetting;
use Exception;
use Benwilkins\FCM\FcmMessage;

class CommonNotification extends Notification
{
    use Queueable;

    public $type, $data, $subject , $notification_message;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($type, $data)
    {
        $this->type = $type;
        $this->data = $data;
        $this->subject = str_replace("_"," ",ucfirst($this->data['subject']));
        $this->notification_message = $this->data['message'] != '' ? $this->data['message'] : __('message.default_notification_body');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $app_setting = AppSetting::first();
        $notificationSettings = $app_setting != null ? $app_setting->notification_settings : null; 
        $notifications = [];
        $notification_access =isset($notificationSettings[$this->type]) ? $notificationSettings[$this->type] : [];

        // \Log::info('Order data'.$this->data);
        foreach(config('config.notification') as $key => $notification) {
            if(isset($notification_access[$key]) && $notification_access[$key]) {
                switch($key) {
                    case 'IS_ONESIGNAL_NOTIFICATION':
                        if(ENV('IS_ONESIGNAL_NOTIFICATION') == 1){
                            array_push($notifications, OneSignalChannel::class);
                        }
                    break;
                    case 'IS_FIREBASE_NOTIFICATION':
                        if(ENV('IS_FIREBASE_NOTIFICATION') == 1 && $notifiable->user_type == 'admin' ){
                            array_push($notifications, 'fcm');
                        }
                    break;
                }
            }
        }
        return $notifications;
    }

    public function toOneSignal($notifiable)
    {
        $msg = strip_tags($this->notification_message);
        if (!isset($msg) && $msg == ''){
            $msg = __('message.default_notification_body');
        }

        $type = 'create';
        if (isset($this->data['type']) && $this->data['type'] !== ''){
            $type = $this->data['type'];
        }

        // \Log::info('onesignal notifiable'.json_encode($notifiable));
        return OneSignalMessage::create()
            ->setSubject($this->subject)
            ->setBody($msg) 
            ->setData('id',$this->data['id'])
            ->setData('type',$type);
    }

    public function toFcm($notifiable)
    {
        $message = new FcmMessage();
        $msg = strip_tags($this->notification_message);
        if (!isset($msg) && $msg == ''){
            $msg = __('message.default_notification_body');
        }
        $notification = [
            'body' => $msg,
            'title' => $this->subject,
        ];
        $data = [
            'click_action' => "FLUTTER_NOTIFICATION_CLICK",
            'sound' => 'default',
            'status' => 'done',
            'id' => $this->data['id'],
            'type' => $this->data['type'],
            'message' => $notification,
        ];
        // \Log::info('fcm notifiable'.json_encode($notifiable));
        $message->content($notification)->data($data)->priority(FcmMessage::PRIORITY_HIGH);

        return $message;
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
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
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
