<?php

namespace Tests\Feature;

use App\Models\Availability;
use App\Models\BookingRequest;
use App\Models\Comment;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CommentControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * @test
     */
    public function user_can_post_comments_on_booking_request()
    {
        $room = Room::factory()->create(['status' => 'available']);
        $user = $this->createUserWithPermissions(['bookings.create']);

        $date = $this->faker->dateTimeInInterval('+' . $room->min_days_advance . ' days', '+' . ($room->max_days_advance - $room->min_days_advance) . ' days');

        $this->createReservationAvailabilities($date, $room);

        $start = Carbon::parse($date);
        $end = $start->copy()->addMinutes(4);

        $response = $this->actingAs($user)->post('/bookings', [
            'room_id' => $room->id,
            'reservations' => [
                [
                    'start_time' => $start->format('Y-m-d H:i:00'),
                    'end_time' => $end->format('Y-m-d H:i:00'),
                    'duration' => $this->faker->numberBetween(100)
                ]
            ],
            'event' => [
                'start_time' => $start->copy()->addMinute()->format('H:i'),
                'end_time' => $end->copy()->subMinute()->format('H:i'),
                'title' => $this->faker->word,
                'type' => $this->faker->word,
                'description' => $this->faker->paragraph,
                'guest_speakers' => $this->faker->name,
                'attendees' => $this->faker->numberBetween(100),
            ]
        ]);
        $response->assertStatus(302);
        $booking = BookingRequest::first();

        $this->assertDatabaseCount('comments', 0);

        $comment = '<p>test</p>';
        $response = $this->actingAs($user)->post("/bookings/{$booking->id}/comment/",
            ['comment' => $comment]
        );
        $response->assertStatus(302);
        $this->assertDatabaseCount('comments', 1);
        $this->assertDatabaseHas('comments', [
            'system' => false,
            'body' => $comment,
        ]);
    }

    private function createReservationAvailabilities($start, $room)
    {
        $openingHours = Carbon::parse($start)->subMinutes(5)->toTimeString();
        $closingHours = Carbon::parse($start)->addMinutes(10)->toTimeString();

        Availability::create([
            'room_id' => $room->id,
            'opening_hours' => $openingHours,
            'closing_hours' => $closingHours,
            'weekday' => Carbon::parse($start)->format('l')
        ]);
    }
}
