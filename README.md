## For Run PHP File:- php -S localhost:8000

# Business Listing & Rating System
A simple yet powerful listing and rating system built using Core PHP, MySQL, and AJAX.

## Features
- **Business Management**: Add, Edit, and Delete businesses without page refresh.
- **Rating System**: Users can rate businesses on a scale of 0.5 to 5 stars.
- **Smart Update**: If a user with the same Email or Phone tries to rate the same business, their existing rating is updated.
- **Real-time Average**: Average ratings are calculated and updated in the table instantly via AJAX.
- **Premium UI**: Built with Bootstrap 5 and FontAwesome for a clean, modern look.

### 1. Database Configuration
1. Create a database named `business`.
2. Import the `database.sql` file provided in the project root.
3. If your MySQL root password is not `123456`, update it in `includes/db_connect.php`:
   ```php
   $host = 'localhost';
   $user = 'root';
   $pass = '123456'; // Change this
   $dbname = 'business';
   ```
## How to Test
1. **Add Business**: Click "Add Business", fill the form, and save. It appears in the table immediately.
2. **Rate Business**: Click "Rate" on any business. Fill in your details and select stars.
3. **Update Rating**: Rate the same business again using the same email or phone. Notice the rating updates instead of creating a new entry.
4. **Edit/Delete**: Use the icons in the Actions column to modify or remove businesses.



