<?php

namespace App\Notifications;

use App\Website;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

class WebsiteIsBackUp extends Notification
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
            ->subject('🎉 Website Back Online: ' . $this->website->name . ' (' . $this->website->url . ')')
            ->markdown('mail.website-up', ['website' => $this->website]);
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
            'event' => 'Website back up'
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
            ->success()
            ->attachment(function ($attachment) {
                $attachment
                    ->title('🎉 Website Back Online: ' . $this->website->name . ' (' . $this->website->url . ')')
                    ->content('The website had been offline for ' . $this->website->time_spent_offline . '.');
            });

        $slackChannel = $this->website->slack_channel;
        if ($slackChannel) {
            $slackMessage->to($slackChannel);
        }

        return $slackMessage;
    }
}
