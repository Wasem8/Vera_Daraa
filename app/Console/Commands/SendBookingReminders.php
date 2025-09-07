<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Notifications\BookingStatusChanged;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendBookingReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'booking:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send booking reminders to users one day before their booking date';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tomorrow = Carbon::tomorrow()->toDateString();

        $bookings = Booking::whereDate('booking_date', $tomorrow)->get();

        foreach ($bookings as $booking) {
            $user = $booking->user;

            if ($user) {
                $message = "Reminder: You have a booking for {$booking->service->name} on {$booking->booking_date}.";
                $user->notify(new BookingStatusChanged($booking, 'reminder', $message,'booking reminder'));
            }
        }

        $this->info('Reminders sent successfully.');
    }

}
