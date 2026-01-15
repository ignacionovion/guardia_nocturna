<?php
use App\Models\User;
use App\Models\Novelty;
use Illuminate\Support\Facades\Auth;

// Bootstrap Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Simulate Dashboard Logic for User 11
$user = User::find(11);
echo "User: {$user->name} (ID: {$user->id})\n";
echo "Role: {$user->role}\n";
echo "Guardia ID: {$user->guardia_id}\n";

if ($user->role === 'guardia' && $user->guardia_id) {
    echo "Logic: Guardia Filter Active\n";
    
    $myStaff = User::where('guardia_id', $user->guardia_id)
        ->where('id', '!=', $user->id)
        ->get();
    
    echo "Staff Count: " . $myStaff->count() . "\n";
    
    $staffIds = $myStaff->pluck('id');
    $staffIds->push($user->id);
    
    echo "Included User IDs: " . $staffIds->implode(', ') . "\n";
    
    // Create a dummy novelty for this user if none exists recently
    $latest = Novelty::where('user_id', $user->id)->latest()->first();
    if (!$latest) {
        echo "Creating test novelty for verification...\n";
        $n = new Novelty();
        $n->user_id = $user->id;
        $n->title = "Test Visibility";
        $n->description = "Description";
        $n->type = "Informativa";
        $n->date = now();
        $n->save();
        $latest = $n;
    }
    
    echo "Checking visibility for Novelty ID: {$latest->id} (User: {$latest->user_id})\n";
    
    $novelties = Novelty::whereIn('user_id', $staffIds)->latest()->take(5)->get();
    
    echo "Novelties Found in Dashboard Query: " . $novelties->count() . "\n";
    $found = false;
    foreach($novelties as $nov) {
        echo " - [{$nov->id}] {$nov->title} (User: {$nov->user_id})\n";
        if ($nov->id === $latest->id) $found = true;
    }
    
    if ($found) {
        echo "SUCCESS: The novelty is visible in the query.\n";
    } else {
        echo "FAILURE: The novelty is NOT visible in the query.\n";
    }
    
} else {
    echo "Logic: General (No Filter)\n";
}
