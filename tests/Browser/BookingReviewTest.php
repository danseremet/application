<?php

namespace Tests\Browser;

use App\Mail\CommentMailable;
use App\Models\BookingRequest;
use App\Models\User;
use App\Notifications\CommentNotification;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\RoomSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use NoelDeMartin\LaravelDusk\Browser;
use Tests\DuskTestCase;

class BookingReviewTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function setUp(): void
    {
        parent::setUp();
        (new RolesAndPermissionsSeeder())->run();
        (new RoomSeeder())->run();
        User::factory(1)->create()->first();
        User::first()->assignRole('super-admin');
    }

    public function testUserCanViewBookingReviewPage()
    {
        $booking = BookingRequest::factory()
            ->count(1)
            ->hasReservations(random_int(1, 3))
            ->create(["status" => BookingRequest::REVIEW])->first();

        $this->browse(function (Browser $browser) use ($booking)
        {
            $browser->loginAs(User::first());
            $browser->visit('/bookings/review')
                ->clickLink('Open Details')
                ->waitForText('Booking Overview')
                ->assertPathIs('/bookings/' . $booking->id . '/review')
                ->assertSee('Booking History');
        });
    }

    public function testUserPostComments()
    {
        $booking = BookingRequest::factory()
            ->count(1)
            ->hasReservations(random_int(1, 3))
            ->create(["status" => BookingRequest::REVIEW])->first();

        $user = User::first();

        $this->browse(function (Browser $browser) use ($user, $booking)
        {
            $mail = $browser->fake(Mail::class);
            $mail->assertNothingSent();


            $browser->loginAs($user);
            $browser->visit('/bookings/review')
                ->clickLink('Open Details')
                ->waitForText('Booking Overview')
                ->assertPathIs('/bookings/' . $booking->id . '/review')
                ->assertSee('Booking History');

            $browser->scrollTo('.ProseMirror')
                ->click('.ProseMirror')
                ->type('.ProseMirror', 'This is a test comment.')
                ->click('[dusk=comment-textbox] > [dusk=submit]')
                ->scrollTo('.ProseMirror')
                ->waitForText("This is a test comment.");

            $mail->assertSent(CommentMailable::class);


        });
    }
}
