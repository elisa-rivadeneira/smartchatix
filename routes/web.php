<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AssistantController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MessengerController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\FineTuneController;
use App\Http\Controllers\AIModelController;
use App\Http\Controllers\CourseController;


Auth::routes();

Route::get('/', function () {
    return view('landing');
});

// Route::get('/register', function () {
//     return view('landing');
// });

Route::post('/api/generate-response/{id}', [AssistantController::class, 'publicGenerateResponse'])
    ->withoutMiddleware(['auth', 'throttle', \App\Http\Middleware\VerifyCsrfToken::class]);


Route::post('/messenger/webhook', [MessengerController::class, 'handleMessenger'])
    ->withoutMiddleware(['auth', 'throttle', \App\Http\Middleware\VerifyCsrfToken::class]);
    
Route::get('/messenger/webhook', [MessengerController::class, 'verifyWebhook']);





Route::get('/upload-dataset', [FineTuneController::class, 'uploadDataset'])
->withoutMiddleware(['auth', 'throttle', \App\Http\Middleware\VerifyCsrfToken::class]);


Route::post('/start-fine-tuning/{fileId}', [FineTuneController::class, 'startFineTuning']);
Route::get('/fine-tuning-status/{fineTuneId}', [FineTuneController::class, 'getFineTuningStatus']);


Route::middleware(['auth'])->group(function () {
    Route::resource('assistants', AssistantController::class); // Rutas RESTful para el controlador Assistant
    Route::post('/assistants/{id}/generate-response', [AssistantController::class, 'generateResponse'])->name('assistants.generateResponse');
    Route::post('/assistants/{assistant}/upload-document', [DocumentController::class, 'uploadDocument'])->name('assistants.upload-document');
    
    Route::post('/assistants/{assistant}/upload-training-document', [AssistantController::class, 'uploadDocumentTraining'])->name('assistants.upload-training-document');

    Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
    Route::resource('users', UserController::class)->only(['index', 'edit', 'update']);

    
    Route::get('/conversations', [ConversationController::class, 'index'])->name('conversations.index');
    Route::get('/conversations/user', [ConversationController::class, 'getUserConversations']); // Para obtener conversaciones de un usuario
    Route::post('/conversations', [ConversationController::class, 'startConversation']);
    Route::post('/conversations/{conversationId}/messages', [ConversationController::class, 'storeMessage']);
    Route::get('/conversations/create', [ConversationController::class, 'create'])->name('conversations.create');
    Route::get('/conversations/{id}', [ConversationController::class, 'show'])->name('conversations.show');
    Route::delete('/conversations/{id}', [ConversationController::class, 'destroy'])->name('conversations.destroy');

    Route::resource('courses', CourseController::class);


    Route::get('/assistants/{id}/monitor-finetuning', [AssistantController::class, 'monitorFineTuning'])->name('assistants.monitorFineTuning');

    Route::resource('admin/a-i-models', AIModelController::class);


    // Route::get('/', function () {
    //     return view('welcome');
    // });    
    // Route::get('/home', function () {
    //     return view('welcome');
    // });    
    Route::get('/welcome', function () {
        return view('welcome');
    });    
    
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

});

Route::middleware([\App\Http\Middleware\IsAdmin::class])->group(function () {
    Route::resource('users', UserController::class)->only(['index', 'edit', 'update']);
});