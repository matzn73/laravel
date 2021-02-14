<?php

namespace App\Notifications;

use App\Mail\BareMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetNotification extends Notification
{
    use Queueable;

    public $token;
    public $mail;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(string $token, BareMail $mail)
    {
        $this->token = $token;
        $this->mail  = $mail;
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
        return $this->mail
            ->from(config('mail.from.address'), config('mail.from.name')) //config関数を使って、config/mail.phpの値を取得しています。

                ->to($notifiable->email) //toには送信先のアドレスを渡す、$notifiableに送信先のUserモデルが代入されている
                ->subject('[memo]パスワード再設定') //件名
                ->text('emails.password_reset') //resources/views/emailsディレクトリのpassword_reset.blade.phpがテンプレートとして使用されます。
                ->with([
                    'url' => route('password.reset', [
                        'token' => $this->token,
                        'email' => $notifiable->email,
                    ]),
                    'count' => config(
                        'auth.passwords.' . 
                        config('auth.defaults.passwords') .
                        '.expire'
                    ),
                ]);
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
