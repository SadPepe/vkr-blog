<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$u = User::find(1);
$u->password = Hash::make('Test1234');
$u->save();
echo 'password reset for user ' . $u->id . PHP_EOL;