<?php

namespace Litermi\ErrorNotification\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 *
 */
class ExceptionEmailNotification extends Notification implements ShouldQueue
{

    use Queueable;

    private $data;

    private $via;

    private $extraFields;

    public function __construct($via, $data)
    {
        $this->data = $data;
        $this->via = $via;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ($this->via) ? $this->via : ['mail', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        $data = $this->data['endpoint'];
        $environment = array_key_exists('environment', $data) ? $data['environment'] : 'empty environment';
        $messageError = array_key_exists('message_error', $data) ? $data['message_error'] : '';
        $subject = "ENV:$environment 👀 / Exception in: ".env('APP_NAME')." ";

        $view = config('error-notification.view-alert-email');

        return (new MailMessage())
            ->subject($subject)
            ->markdown($view, $this->data);
    }

}

