<?php

use App\Http\Controllers\{
    AuthController, BookingController, ChatbotController, CommentController,
    DestinationController, EventController, HomeController, InboxController,
    MapController, NotificationController, PostController, ProfileController,
    RecommendationController, ReviewController, SearchController
};
use Illuminate\Support\Facades\Route;

/* ---------- PUBLIC ---------- */
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});
Route::get('/auth/google', [AuthController::class, 'google'])->name('google');
Route::get('/auth/google/callback', [AuthController::class, 'googleCallback']);

Route::get('/map', [MapController::class, 'index'])->name('map');
Route::get('/search', [SearchController::class, 'index'])->name('search');
Route::post('/chatbot', [ChatbotController::class, 'ask'])->name('chatbot');

Route::get('/destinations', [DestinationController::class, 'index'])->name('destinations.index');
Route::get('/destinations/{destination}', [DestinationController::class, 'show'])->whereNumber('destination')->name('destinations.show');
Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/events/{event}', [EventController::class, 'show'])->whereNumber('event')->name('events.show');
Route::get('/community', [PostController::class, 'index'])->name('community');
Route::get('/posts/{post}', [PostController::class, 'show'])->whereNumber('post')->name('posts.show');
Route::get('/owners/{user}', [ProfileController::class, 'publicPosts'])->whereNumber('user')->name('owners.show');
Route::get('/explore', [RecommendationController::class, 'explore'])->name('explore');
Route::get('/for-you', [RecommendationController::class, 'explore'])->name('recommendations');

/* ---------- MEMBERS ---------- */
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->whereNumber('post')->name('posts.destroy');
    Route::post('/posts/{post}/like', [PostController::class, 'like'])->whereNumber('post')->middleware('throttle:10,1')->name('posts.like');

    Route::post('/posts/{post}/comments', [CommentController::class, 'store'])->whereNumber('post')->name('comments.store');
    Route::post('/comments/{comment}/replies', [CommentController::class, 'reply'])->whereNumber('comment')->name('comments.reply');
    Route::post('/comments/{comment}/react', [CommentController::class, 'react'])->whereNumber('comment')->name('comments.react');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->whereNumber('comment')->name('comments.destroy');

    Route::get('/destinations/create', [DestinationController::class, 'create'])->middleware('role:destination_owner')->name('destinations.create');
    Route::post('/destinations', [DestinationController::class, 'store'])->middleware('role:destination_owner')->name('destinations.store');
    Route::get('/destinations/{destination}/edit', [DestinationController::class, 'edit'])->whereNumber('destination')->name('destinations.edit');
    Route::put('/destinations/{destination}', [DestinationController::class, 'update'])->whereNumber('destination')->name('destinations.update');
    Route::delete('/destinations/{destination}', [DestinationController::class, 'destroy'])->whereNumber('destination')->name('destinations.destroy');
    Route::post('/destinations/{destination}/book', [DestinationController::class, 'book'])->whereNumber('destination')->name('destinations.book');

    Route::get('/events/create', [EventController::class, 'create'])->middleware('role:event_creator')->name('events.create');
    Route::post('/events', [EventController::class, 'store'])->middleware('role:event_creator')->name('events.store');
    Route::get('/events/{event}/edit', [EventController::class, 'edit'])->whereNumber('event')->name('events.edit');
    Route::put('/events/{event}', [EventController::class, 'update'])->whereNumber('event')->name('events.update');
    Route::delete('/events/{event}', [EventController::class, 'destroy'])->whereNumber('event')->name('events.destroy');
    Route::post('/events/{event}/book', [EventController::class, 'book'])->whereNumber('event')->name('events.book');

    Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->whereNumber('review')->name('reviews.destroy');

    Route::get('/inbox', [InboxController::class, 'index'])->name('inbox.index');
    Route::post('/inbox', [InboxController::class, 'store'])->name('inbox.store');
    Route::get('/inbox/{conversation}', [InboxController::class, 'show'])->whereNumber('conversation')->name('inbox.show');
    Route::post('/inbox/{conversation}/send', [InboxController::class, 'send'])->whereNumber('conversation')->name('inbox.send');

    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::delete('/bookings/{booking}', [BookingController::class, 'cancel'])->whereNumber('booking')->name('bookings.cancel');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
    Route::get('/notifications/unread', [NotificationController::class, 'unread'])->name('notifications.unread');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read');
});