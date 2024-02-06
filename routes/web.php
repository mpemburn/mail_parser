<?php

use App\Parsers\ChangeOrderParser;
use App\Parsers\PagelyParser;
use App\Services\EmailParserService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/dev', function () {
    (new EmailParserService())
        ->setParser(new PagelyParser())
        ->setProject('Press3')
        ->setDirectory('JSON/Press3')
        ->parseDirectory();
});

Route::get('/', function () {
    return view('welcome');
});
