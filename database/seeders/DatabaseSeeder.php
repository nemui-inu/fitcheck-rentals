<?php

namespace Database\Seeders;

use App\Enums\BookingStatus;
use App\Enums\ItemStatus;
use App\Enums\UnitCondition;
use App\Enums\UnitStatus;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Item;
use App\Models\ItemUnit;
use App\Models\OwnerProfile;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed demo accounts, items in every status, and bookings in every status.
     */
    public function run(): void
    {
        $admin = User::factory()->admin()->create([
            'name' => 'Admin',
            'email' => 'admin@fitcheck.test',
        ]);

        $this->call(CategorySeeder::class);
        $categories = Category::pluck('id', 'code');

        $owner = User::factory()->create(['name' => 'Olivia Owner', 'email' => 'owner@fitcheck.test', 'phone' => '09171234567']);
        $renter = User::factory()->create(['name' => 'Rico Renter', 'email' => 'renter@fitcheck.test', 'phone' => '09181234567']);
        $both = User::factory()->create(['name' => 'Bea Both', 'email' => 'both@fitcheck.test', 'phone' => '09191234567']);
        $suspended = User::factory()->suspended()->create(['name' => 'Sam Suspended', 'email' => 'suspended@fitcheck.test', 'phone' => '09201234567']);

        $ownerShop = OwnerProfile::factory()->for($owner)->create(['shop_name' => 'Wig Closet', 'meetup_area' => 'Cubao, Quezon City']);
        $bothShop = OwnerProfile::factory()->for($both)->create(['shop_name' => 'Prop Forge', 'meetup_area' => 'SM Megamall, Mandaluyong']);
        $suspendedShop = OwnerProfile::factory()->for($suspended)->create(['shop_name' => 'Hidden Rack', 'meetup_area' => 'Makati']);

        $items = [
            [$ownerShop, 'WIG', 'Frieren twin tail wig', 'Frieren: Beyond Journey\'s End', 'Frieren', 'Free', 25000, 50000, ItemStatus::Active, ['WIG-001', 'WIG-002', 'WIG-003']],
            [$ownerShop, 'COS', 'Frieren mage robe set', 'Frieren: Beyond Journey\'s End', 'Frieren', 'S', 60000, 150000, ItemStatus::Active, ['COS-001', 'COS-002']],
            [$ownerShop, 'COS', 'Makima office suit', 'Chainsaw Man', 'Makima', 'M', 55000, 120000, ItemStatus::Active, ['COS-003']],
            [$ownerShop, 'WIG', 'Gojo white spiky wig', 'Jujutsu Kaisen', 'Gojo Satoru', 'Free', 20000, 40000, ItemStatus::Paused, ['WIG-004']],
            [$ownerShop, 'SHO', 'Sailor boots, red', 'Sailor Moon', 'Sailor Mars', '38', 30000, 80000, ItemStatus::Draft, ['SHO-001']],
            [$bothShop, 'PRP', 'Nichirin sword replica', 'Demon Slayer', 'Tanjiro Kamado', null, 40000, 100000, ItemStatus::Active, ['PRP-001', 'PRP-002']],
            [$bothShop, 'PRP', 'Keyblade foam prop', 'Kingdom Hearts', 'Sora', null, 35000, 90000, ItemStatus::Active, ['PRP-003']],
            [$bothShop, 'ACC', 'Spy x Family hair pins', 'Spy x Family', 'Anya Forger', null, 8000, 20000, ItemStatus::Active, ['ACC-001', 'ACC-002', 'ACC-003']],
            [$bothShop, 'PRP', 'Metal chain scythe', 'Original', null, null, 50000, 150000, ItemStatus::PendingReview, ['PRP-004']],
            [$suspendedShop, 'COS', 'Naruto jumpsuit', 'Naruto', 'Naruto Uzumaki', 'L', 45000, 100000, ItemStatus::Active, ['COS-001']],
        ];

        foreach ($items as [$shop, $code, $name, $series, $character, $size, $rate, $deposit, $status, $labels]) {
            $item = new Item(['category_id' => $categories[$code], 'name' => $name, 'series' => $series, 'character' => $character, 'size' => $size, 'description' => "Clean, fitted, and ready for your next con. Meetup only at {$shop->meetup_area}."]);
            $item->owner_profile_id = $shop->id;
            $item->forceFill(['daily_rate' => $rate, 'deposit' => $deposit, 'status' => $status]);

            if ($status === ItemStatus::PendingReview) {
                $item->forceFill(['takedown_reason' => 'Photos made the scythe look like a real weapon. Show the foam core.', 'taken_down_at' => now()->subDays(2), 'taken_down_by' => $admin->id]);
            }

            $item->save();

            foreach ($labels as $index => $label) {
                $unit = new ItemUnit(['label' => $label, 'condition' => $index === 0 ? UnitCondition::Excellent : UnitCondition::Good]);
                $unit->item_id = $item->id;
                $unit->save();
            }
        }

        $retired = Item::where('name', 'Frieren twin tail wig')->firstOrFail()->units()->where('label', 'WIG-003')->firstOrFail();
        $retired->status = UnitStatus::Retired;
        $retired->save();

        $takenDown = new Item(['category_id' => $categories['ACC'], 'name' => 'Replica police badge', 'description' => 'Looks official.']);
        $takenDown->owner_profile_id = $ownerShop->id;
        $takenDown->forceFill(['daily_rate' => 10000, 'deposit' => 30000, 'status' => ItemStatus::TakenDown, 'taken_down_at' => now()->subDay(), 'taken_down_by' => $admin->id, 'takedown_reason' => 'Replica police badges are not allowed. Remove the badge or relist as a costume patch.'])->save();

        $bookings = [
            [$renter, 'Frieren twin tail wig', 0, 5, 7, BookingStatus::Pending, null],
            [$both, 'Frieren mage robe set', 0, 10, 12, BookingStatus::Pending, null],
            [$renter, 'Frieren mage robe set', 1, 3, 4, BookingStatus::Approved, null],
            [$renter, 'Makima office suit', 0, -1, 2, BookingStatus::Active, null],
            [$both, 'Frieren twin tail wig', 1, -10, -8, BookingStatus::Returned, -7],
            [$renter, 'Nichirin sword replica', 0, -6, -5, BookingStatus::Returned, -5],
            [$renter, 'Keyblade foam prop', 0, 14, 15, BookingStatus::Rejected, null],
            [$renter, 'Spy x Family hair pins', 0, 8, 9, BookingStatus::Cancelled, null],
            [$renter, 'Spy x Family hair pins', 1, 20, 22, BookingStatus::Pending, null],
        ];

        foreach ($bookings as [$bookingRenter, $itemName, $unitIndex, $startOffset, $endOffset, $status, $returnedOffset]) {
            $item = Item::where('name', $itemName)->firstOrFail();
            $booking = new Booking;
            $booking->item_unit_id = $item->units()->orderBy('id')->skip($unitIndex)->value('id');
            $booking->renter_id = $bookingRenter->id;
            $booking->start_date = today()->addDays($startOffset);
            $booking->end_date = today()->addDays($endOffset);
            $booking->total = ($endOffset - $startOffset + 1) * $item->daily_rate;
            $booking->deposit = $item->deposit;
            $booking->status = $status;
            $booking->returned_at = $returnedOffset === null ? null : now()->addDays($returnedOffset);
            $booking->save();
        }
    }
}
