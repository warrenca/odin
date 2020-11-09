<?php

namespace App\Notifications;

use App\Website;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

class WebsiteIsDown extends Notification
{
    use Queueable;

    /**
     * @var Website
     */
    private $website;

    /**
     * Create a new notification instance.
     *
     * @param Website $website
     */
    public function __construct(Website $website)
    {
        $this->website = $website;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['slack', 'mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('💥 Website Offline: ' . $this->website->name . '(' . $this->website->url . ')')
            ->markdown('mail.website-down', ['website' => $this->website]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'website_id' => $this->website->id,
            'url' => $this->website->url,
            'event' => 'Website down'
        ];
    }

    /**
     * Get the Slack representation of the notification.
     *
     * @param mixed $notifiable
     * @return SlackMessage
     */
    public function toSlack($notifiable)
    {
        $slackMessage = (new SlackMessage)
            ->error()
            ->attachment(function ($attachment) {
                $attachment
                    ->title('💥 Website Offline: ' . $this->website->name . '(' . $this->website->url . ')')
                    ->content('We could not find the defined keyword of "' . $this->website->uptime_keyword . '" on the page.');
            });

        $slackChannel = $this->website->slack_channel;
        if ($slackChannel) {
            $slackMessage->to($slackChannel);
        }

        return $slackMessage;
    }
}
