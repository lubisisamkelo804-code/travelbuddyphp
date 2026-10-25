================================================================================
                              TRAVELBUDDY - README
              A Social Travel Platform built with Laravel 12 + PHP 8.2+
================================================================================

--------------------------------------------------------------------------------
1. WHAT IS TRAVELBUDDY?
--------------------------------------------------------------------------------
TravelBuddy is a full-featured social travel web application. It combines
destination discovery, event creation and booking, a community feed (posts,
comments, reactions), real-time-style direct messaging with notifications,
personalized recommendations based on interests, an interactive map with
location ratings, weather integration, and a chatbot assistant.

Main capabilities:
  * User accounts: register / login / logout, forgot-password & password reset,
    Google Sign-In (OAuth via Laravel Socialite).
  * Interests: pick interest chips (Beach, Hiking, Food, etc.) that drive the
    "For You" personalized recommendations page.
  * Destinations: browse, view details, read/write reviews, create/edit your
    own (role: destination_owner), and book trips.
  * Events: browse and book events; event creators (role: event_creator) can
    create, edit and delete their events with capacity enforcement.
  * Community feed: create posts with media, like posts, comment, reply to
    comments, react to comments; authors can delete their own content.
  * Inbox / Direct Messaging: search for any user, start a conversation, send
    messages; profile pictures are shown in messages and replies.
  * Notifications: users are alerted when they receive a message or other
    activity; unread badges update live; a notifications page lists history.
  * Map: Leaflet-based map of destinations with popups showing average
    (overall) ratings computed from reviews.
  * Weather: live weather widget configured for JOHANNESBURG, South Africa
    (config/travelbuddy.php).
  * Bookings: view and cancel your bookings (/bookings).
  * Chatbot: simple Q&A assistant on the home page (/chatbot endpoint).
  * Public profiles: view another traveler's public posts at /owners/{id}.

--------------------------------------------------------------------------------
2. TECH STACK
--------------------------------------------------------------------------------
  Backend     : PHP >= 8.2, Laravel 12
  Packages    : laravel/socialite (Google login), laravel/reverb (websockets),
                fakerphp/faker, laravel/pint, phpunit 11
  Frontend    : Blade templates, Vite (vite.config.js, package.json),
                TailwindCSS, Font Awesome icons, Leaflet maps
  Database    : SQLite by default (database/database.sqlite); MySQL supported
  Auth        : Session-based (Laravel guards) + Google OAuth
  Mail        : log driver by default (emails written to storage/logs/laravel.log)

--------------------------------------------------------------------------------
3. PROJECT STRUCTURE (KEY FOLDERS)
--------------------------------------------------------------------------------
  app/Http/Controllers/   16 controllers (Auth, Booking, Chatbot, Comment,
                          Destination, Event, Home, Inbox, Map, Notification,
                          Post, Profile, Recommendation, Review, Search)
  app/Models/             User, Destination, Event, Booking, Post, PostMedia,
                          Comment, CommentReaction, Review, Conversation,
                          ConversationUser, Message, Notification, Interest
  database/migrations/    users/cache/jobs tables + travelbuddy tables +
                          last_read_at on conversation_user
  database/seeders/       DatabaseSeeder.php (seeds the 12 interests)
  resources/views/        auth/, destinations/, events/, inbox/, owners/,
                          posts/, layouts/, partials/, plus home, map,
                          community, explore, bookings, notifications,
                          profile, search pages
  routes/web.php          All HTTP routes (see section 6)
  config/travelbuddy.php  Rail widgets: weather city = Johannesburg (-26.2041,
                          28.0473), currency list (base ZAR), widget toggles
  config/services.php     Google OAuth keys (GOOGLE_CLIENT_ID/SECRET/REDIRECT)
  tests/                  PHPUnit feature/unit tests

--------------------------------------------------------------------------------
4. REQUIREMENTS
--------------------------------------------------------------------------------
  * PHP >= 8.2 (with extensions: pdo_sqlite, mbstring, openssl, curl, fileinfo)
  * Composer 2.x
  * Node.js 18+ and npm
  * Git (optional)

On Windows with XAMPP, make sure php.exe is on your PATH, e.g.:
  C:\xampp\php\php.exe  ->  add C:\xampp\php to System Environment PATH.

--------------------------------------------------------------------------------
5. INSTALLATION - STEP BY STEP
--------------------------------------------------------------------------------
STEP 1: Get the code
  Option A (clone):   git clone <repo-url> travelbuddy && cd travelbuddy
  Option B (zip):     unzip travelbuddyphp-main.zip && cd travelbuddyphp-main

STEP 2: Install PHP dependencies
  composer install
  (If it complains about a lock file mismatch: composer update)

STEP 3: Install JavaScript dependencies
  npm install

STEP 4: Environment file
  copy .env.example .env          (Windows CMD)
  cp .env.example .env            (Linux/macOS/PowerShell: Copy-Item)

STEP 5: Generate application key
  php artisan key:generate

STEP 6: Create the database (SQLite - default)
  From the project root run:
      mkdir -p database            (already exists)
      touch database/database.sqlite
  On Windows CMD:   type nul > database\database.sqlite
  On PowerShell:    New-Item -ItemType File database\database.sqlite

  NOTE: If you run "php artisan migrate" before the file exists, Laravel asks:
  "The SQLite database ... does not exist. ... would you like to create it?"
  Type: yes

STEP 6b (alternative): Use MySQL instead of SQLite
  In .env set:
      DB_CONNECTION=mysql
      DB_HOST=127.0.0.1
      DB_PORT=3306
      DB_DATABASE=travelbuddy
      DB_USERNAME=root
      DB_PASSWORD=
  Then in MySQL/MariaDB (e.g. XAMPP phpMyAdmin) create the database:
      CREATE DATABASE travelbuddy CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

STEP 7: Run migrations and seed data
  php artisan migrate --seed
  (Seeds the 12 interest records used by registration/onboarding.)

STEP 8: Build frontend assets
  Development (hot reload, keep running):   npm run dev
  Production build:                          npm run build

STEP 9: Start the app
  php artisan serve
  App is now at: http://localhost:8000

  One-liner for all dev processes (PHP >= 8.2, Laravel 11+):
  composer dev   (runs serve + queue worker + vite + pail logs)

--------------------------------------------------------------------------------
6. ROUTES / URL MAP
--------------------------------------------------------------------------------
PUBLIC
  GET  /                        Home page (weather rail, chatbot, highlights)
  GET  /login  POST /login      Sign in (also "Sign in with Google")
  GET  /register POST /register Create account + pick interests
  GET  /forgot-password         Request a password-reset link
  POST /forgot-password         Send reset email (throttled 6/min)
  GET  /reset-password/{token}  Set a new password
  POST /reset-password          Apply the reset (throttled 6/min)
  GET  /auth/google             Redirect to Google OAuth consent screen
  GET  /auth/google/callback    Google returns here after consent
  GET  /map                     Leaflet map with rated destination popups
  GET  /search?q=               Global search (destinations, events, users)
  POST /chatbot                 Chatbot question endpoint
  GET  /destinations            Destination listing
  GET  /destinations/{id}       Destination detail + reviews + book button
  GET  /events                  Event listing
  GET  /events/{id}             Event detail + booking states
  GET  /community               Feed (posts, likes, comments)
  GET  /posts/{id}              Single post with comment thread
  GET  /owners/{id}             Public profile: a user's posts
  GET  /explore  /for-you       Recommendations (interest-based)

MEMBER (requires login)
  POST /logout
  POST /posts                   Create post            DELETE /posts/{id}
  POST /posts/{id}/like         Like a post (throttle 10/min)
  POST /posts/{id}/comments     Comment                POST /comments/{id}/replies
  POST /comments/{id}/react     React to comment       DELETE /comments/{id}
  GET  /destinations/create     (role: destination_owner)
  POST /destinations            Create destination
  GET|PUT|DELETE /destinations/{id}[/edit]  Edit/delete own destination
  POST /destinations/{id}/book  Book a destination
  GET  /events/create           (role: event_creator)
  POST /events                  Create event
  GET|PUT|DELETE /events/{id}[/edit]  Edit/delete own event
  POST /events/{id}/book        Book event seat (capacity enforced atomically)
  POST /reviews                 Review a destination   DELETE /reviews/{id}
  GET  /inbox                   Conversations list     POST /inbox  (new conv)
  POST /inbox/users/{id}        Start/find conversation with a user
  GET  /inbox/{id}              Open a conversation    POST /inbox/{id}/send
  GET  /profile  PUT /profile   View/edit profile, avatar, interests
  GET  /bookings                My bookings            DELETE /bookings/{id}
  GET  /notifications           Notification history
  GET  /notifications/unread    Poll endpoint for unread badge
  POST /notifications/read-all  Mark all as read

--------------------------------------------------------------------------------
7. GOOGLE SIGN-IN SETUP (REQUIRED FOR THAT FEATURE)
--------------------------------------------------------------------------------
The code path is complete (AuthController::google / googleCallback with error
handling and account linking), but you MUST supply real OAuth credentials:

  1. Go to https://console.cloud.google.com/apis/credentials
  2. Create an "OAuth client ID" (type: Web application).
  3. Authorized redirect URI:  http://localhost:8000/auth/google/callback
     (add your production URL too when you deploy).
  4. Add to .env:
        GOOGLE_CLIENT_ID=xxxxxxxx.apps.googleusercontent.com
        GOOGLE_CLIENT_SECRET=your-secret
        GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
  5. Clear config:  php artisan config:clear
  6. Click "Sign in with Google" on /login.

Without these keys the button will show a configuration error message instead
of crashing - that is expected behaviour.

--------------------------------------------------------------------------------
8. HOW FEATURES WORK (USER WALKTHROUGH)
--------------------------------------------------------------------------------
A) REGISTER & INTERESTS
   - Visit /register, fill name/email/password (min 8 chars, letters+numbers).
   - Click interest chips (they toggle on/off - fixed bug) and submit.
   - You are logged in and redirected to /for-you with suggestions matching
     your interests.

B) FORGOT PASSWORD
   - On /login click "Forgot password?" -> enter email -> a reset link is
     emailed. With MAIL_MAILER=log the link is NOT sent anywhere; open
     storage/logs/laravel.log and copy the URL into your browser:
         storage/logs/laravel.log  -> look for "Reset Password" mail content.
   - Set the new password, then sign in.

C) DISCOVER ON THE MAP
   - /map shows pins for every destination with coordinates. Clicking a pin
     opens a popup with the destination name, price, and the OVERALL RATING
     (average of all review stars) plus review count.

D) DESTINATIONS, REVIEWS & BOOKING
   - Open /destinations/{id}: see gallery, description, star rating summary,
     and existing reviews. Write your own review (1-5 stars).
   - Press "Book" to create a booking; manage them under /bookings and cancel
     any time.

E) EVENTS
   - Browse /events; open one to see status: upcoming / already booked /
     full / past. Booking uses a DB transaction so two users cannot take the
     last seat simultaneously (fixed race-condition bug).
   - Users with the event_creator role see "Create event" (/events/create);
     they can edit/delete their own events. Date validation was fixed so
     editing an event no longer fails on the "after:now" rule.

F) COMMUNITY
   - /community: publish text/media posts. Like posts (one like per user,
     spam-throttled), comment, reply to comments, and react (emoji) to
     comments. Profile pictures appear next to every message/comment/reply.
   - Only the author can delete their post/comment/review.

G) MESSAGING & NOTIFICATIONS
   - Use /search to find a user by name, press "Message" on the result to
     open (or create) a conversation, and chat.
   - When a message arrives, the recipient gets a notification row; the bell
     icon badge refreshes via /notifications/unread polling, and the alert is
     also raised in-app. Full history is on /notifications; "mark all read"
     clears the badge. Read state per conversation is tracked with
     last_read_at (migration 2026_10_25_000001).

H) WEATHER RAIL
   - The home-page rail shows live weather for JOHANNESBURG
     (lat -26.2041, lng 28.0473). To change city, edit
     config/travelbuddy.php -> rail.weather.

--------------------------------------------------------------------------------
9. TESTING THE PROJECT
--------------------------------------------------------------------------------
AUTOMATED TESTS
  php artisan test
  Tests use an in-memory SQLite DB (see phpunit.xml). Coverage includes auth
  flows, posts/likes/comments, bookings/events and messaging basics.
  Run a single file:  php artisan test --filter=EventTest

MANUAL SMOKE TEST (recommended after install)
  1. Register a user with 2-3 interests -> land on /for-you.
  2. Log out; use Forgot password -> grab link from storage/logs/laravel.log
     -> reset -> log back in.
  3. Open /map, click a pin, verify rating appears in popup.
  4. As a destination_owner seed user: create a destination, then book it as
     a second user, review it, and confirm the average rating updates.
  5. As an event_creator: create an event, book seats until full, verify the
     "full" state and that double-booking the last seat is impossible.
  6. Post -> like -> comment -> reply -> react. Verify avatars render.
  7. Search for the second user -> Message -> send. Verify the first user's
     unread badge increments and /notifications lists the alert.
  8. Run "php artisan tinker" and check: User::count(), Destination::count().

SEED DEMO USERS (roles)
  Roles are stored on the users table (destination_owner, event_creator).
  Promote a test account quickly via tinker:
      php artisan tinker
      >>> $u = App\Models\User::where('email','you@example.com')->first();
      >>> $u->role = 'event_creator'; $u->save();

--------------------------------------------------------------------------------
10. TROUBLESHOOTING
--------------------------------------------------------------------------------
* "SQLite database does not exist" prompt -> type yes, or pre-create the file
  (section 5, Step 6).
* Syntax error "unexpected end of file, expecting elseif/else/endif" in views
  -> stale compiled Blade cache. Fix: php artisan view:clear
  (the events/show.blade.php conditional chain itself is already corrected).
* CSS/JS missing or blank styling -> Vite not running. Run npm run dev, or
  npm run build and php artisan serve again.
* Changes to .env not applied -> php artisan config:clear (and
  php artisan cache:clear).
* Google login errors -> verify GOOGLE_* env values and that the exact
  redirect URI is registered in Google Cloud Console.
* Reset email never arrives -> expected with MAIL_MAILER=log; read the link
  from storage/logs/laravel.log, or configure SMTP in .env.
* Permission errors on Windows/XAMPP -> ensure storage/ and bootstrap/cache/
  are writable by Apache/PHP user.
* Port 8000 busy -> php artisan serve --port=8080.

--------------------------------------------------------------------------------
11. HANDY COMMANDS CHEAT-SHEET
--------------------------------------------------------------------------------
  composer install              npm install
  cp .env.example .env          php artisan key:generate
  php artisan migrate --seed    php artisan migrate:fresh --seed
  php artisan serve             npm run dev | npm run build
  php artisan test              php artisan tinker
  php artisan view:clear        php artisan config:clear
  php artisan cache:clear       php artisan route:list
  php artisan pail  (tail logs) pint (format code): ./vendor/bin/pint

--------------------------------------------------------------------------------
12. LICENSE
--------------------------------------------------------------------------------
MIT (same as the Laravel skeleton this project builds upon).

================================================================================
Happy travels with TravelBuddy! For issues, check section 10 first, then open
an issue with steps to reproduce, your OS, PHP and Node versions.
================================================================================
