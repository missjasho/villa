# Sunrise Cove Villa: Coastal Villa Booking System

## Project overview
A dynamic villa booking web app for a fictional villa in Mirissa, Sri Lanka. Visitors browse rooms, check availability by date and guest count, and send booking requests. Registered guests track and cancel pending bookings. An administrator manages rooms and confirms or cancels bookings.

## Implemented features
**Guest:** home page with map, room list with filters (type, price, facility), availability search, registration/login/logout, booking form with live night count and cost, AJAX availability check, My Bookings with status and cancel (pending only).
**Admin:** dashboard with statistics, confirm/cancel bookings, room add/edit/deactivate/delete.
**Security:** PDO prepared statements, `password_hash`/`password_verify`, session ID regeneration on login, CSRF tokens on all POST forms, output escaping, role checks, ownership checks on bookings, server-side validation and server-side price calculation.
**Integrity:** confirmed bookings cannot overlap (checked inside a transaction with a row lock). Pending requests may overlap until the admin confirms one.
**Extras:** seasonal pricing (+20% Dec to Mar), AJAX availability, room filters, admin statistics, interactive map.

## Software requirements
XAMPP or WAMP (PHP 8.0+, MySQL/MariaDB 10.4+), and an internet connection for Bootstrap, Google Fonts and the map (CDN).

## Installation
1. Copy this folder to `htdocs/villa` (XAMPP) or `www/villa` (WAMP).
2. Start Apache and MySQL.
3. In phpMyAdmin, import `db_structure.sql` (it creates the `villa_booking` database).
4. Check the DB settings at the top of `config.php` (default: root, empty password).
5. Open `http://localhost/villa/`.
6. Optional: add room photos to `assets/img/` and enter the file name in Admin > Manage rooms. Without photos, coloured placeholders are shown.

## Sample logins
| Role  | Email             | Password  |
|-------|-------------------|-----------|
| Admin | admin@villa.test  | Admin@123 |
| Guest | guest@villa.test  | Guest@123 |

## Database (ER summary)
`users (1) ---< bookings >--- (1) rooms`, with `bookings.user_id` -> `users.user_id` and `bookings.room_id` -> `rooms.room_id`.

## Acknowledgements and AI declaration
- Bootstrap 5.3 (CSS framework), Google Fonts (DM Serif Display, Nunito Sans), OpenStreetMap embed.
- **Generative AI declaration:** *[Edit this before submitting: state that Claude (Anthropic) was used to generate the initial project code, and describe what you changed or wrote yourself. You must be able to explain every file in a demonstration.]*
