<?php
use Illuminate\Http\Request;
use App\Http\Controllers\NoveltyController;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

// Bootstrap Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Simulate User Login (User 11 - Guardia User)
$user = User::find(11); 
if (!$user) {
    die("User 11 not found. Using User 1.\n");
    $user = User::find(1);
}
Auth::login($user);
echo "Logged in as: " . $user->name . " (ID: " . $user->id . ")\n";

// Mock Request
$request = Request::create('/novedades', 'POST', [
    'title' => 'prueba',
    'type' => 'Informativa',
    'description' => 'pruebaaaa'
]);

// Initialize Controller
$controller = new NoveltyController();

try {
    // Call store method
    $response = $controller->store($request);

    // Analyze Response
    if ($response->isRedirect()) {
        echo "Response is Redirect.\n";
        $session = session()->all();
        
        if (session()->has('errors')) {
            echo "Validation Errors:\n";
            $errors = session('errors')->getBag('default');
            foreach ($errors->all() as $error) {
                echo " - " . $error . "\n";
            }
        } elseif (session()->has('success')) {
            echo "Success Message: " . session('success') . "\n";
        } else {
            echo "Redirected with no specific session flash.\n";
        }
    } else {
        echo "Response is NOT Redirect. Status: " . $response->status() . "\n";
    }

} catch (\Illuminate\Validation\ValidationException $e) {
    echo "Validation Exception Caught:\n";
    foreach ($e->errors() as $field => $messages) {
        foreach ($messages as $msg) {
            echo " - [$field]: $msg\n";
        }
    }
} catch (\Exception $e) {
    echo "General Exception: " . $e->getMessage() . "\n";
}

// Check DB
$novelty = App\Models\Novelty::where('title', 'prueba')->latest()->first();
if ($novelty) {
    echo "\nNovelty FOUND in DB!\n";
    echo "ID: " . $novelty->id . "\n";
    echo "Title: " . $novelty->title . "\n";
    
    // Clean up
    $novelty->delete();
    echo "Test novelty deleted.\n";
} else {
    echo "\nNovelty NOT FOUND in DB.\n";
}
