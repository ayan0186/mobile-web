# CRUD Operations Implementation Guide

This document outlines all CRUD (Create, Read, Update, Delete) operations implemented for the Glamor Salon booking system.

## Overview

### Create (C)
- **Register User** - Sign up new users
- **Book Appointment** - Create new appointments

### Read (R)
- **Get Appointments** - Retrieve user's appointments
- **Get User Info** - Retrieve user profile information

### Update (U)
- **Reschedule Appointment** - Update appointment date and time

### Delete (D)
- **Delete Appointment** - Remove appointment from database
- **Delete Account** - Permanently delete user account and all associated data

---

## API Endpoints

### CREATE Operations

#### 1. Register User
- **File:** `register.php`
- **Method:** POST
- **Content-Type:** application/json
- **Description:** Register a new user account
- **Request Body:**
```json
{
  "firstName": "John",
  "lastName": "Doe",
  "email": "john@example.com",
  "phone": "+1234567890",
  "password": "password123",
  "confirmPassword": "password123"
}
```
- **Response:**
```json
{
  "success": true,
  "message": "Registration successful! You can now log in."
}
```

#### 2. Book Appointment (Create)
- **File:** `booking.php`
- **Method:** POST
- **Content-Type:** application/json
- **Description:** Create a new appointment booking
- **Authentication:** Required (session-based)
- **Request Body:**
```json
{
  "appointmentDate": "2024-05-15",
  "appointmentTime": "10:30",
  "beauticianSelect": "1"
}
```
- **Response:**
```json
{
  "success": true,
  "message": "Booking confirmed!"
}
```

---

### READ Operations

#### 1. Get User Appointments
- **File:** `get-appointments.php`
- **Method:** GET
- **Description:** Retrieve all appointments for the logged-in user
- **Authentication:** Required (session-based)
- **Response:**
```json
{
  "success": true,
  "appointments": [
    {
      "AppointmentID": 1,
      "UserID": 5,
      "BeauticianID": 1,
      "Appointment_Date": "2024-05-15",
      "Appointment_Time": "10:30",
      "Status": "scheduled"
    }
  ]
}
```

#### 2. Get User Profile Information
- **File:** `get-user.php`
- **Method:** GET
- **Description:** Retrieve logged-in user's profile information
- **Authentication:** Required (session-based)
- **Response:**
```json
{
  "success": true,
  "user": {
    "UserID": 5,
    "FirstName": "John",
    "LastName": "Doe",
    "Email": "john@example.com",
    "Phone": "+1234567890"
  }
}
```

---

### UPDATE Operations

#### 1. Reschedule Appointment
- **File:** `update-appointment.php`
- **Method:** POST
- **Content-Type:** application/json
- **Description:** Update an existing appointment's date and time
- **Authentication:** Required (session-based)
- **Authorization:** User can only update their own appointments
- **Request Body:**
```json
{
  "appointmentId": 1,
  "date": "2024-05-20",
  "time": "14:00"
}
```
- **Response:**
```json
{
  "success": true,
  "message": "Appointment rescheduled successfully"
}
```

---

### DELETE Operations

#### 1. Delete Appointment
- **File:** `delete-appointment.php`
- **Method:** POST
- **Content-Type:** application/json
- **Description:** Remove an appointment from the database
- **Authentication:** Required (session-based)
- **Authorization:** User can only delete their own appointments
- **Request Body:**
```json
{
  "appointmentId": 1
}
```
- **Response:**
```json
{
  "success": true,
  "message": "Appointment deleted successfully"
}
```

#### 2. Delete User Account
- **File:** `delete-account.php`
- **Method:** POST
- **Content-Type:** application/json
- **Description:** Permanently delete user account and all associated appointments
- **Authentication:** Required (session-based)
- **Authorization:** User can only delete their own account
- **Request Body:**
```json
{
  "password": "user_password"
}
```
- **Response:**
```json
{
  "success": true,
  "message": "Account deleted successfully"
}
```
- **Note:** Password confirmation is required for security. Session is destroyed after deletion.

---

## Frontend Integration

### Pages and Components

1. **dashboard.html**
   - Display user's appointments with actions (Reschedule, Delete)
   - Link to profile page
   - Account deletion option

2. **profile.html**
   - View user profile information
   - Account management options
   - Delete account functionality

3. **booking.html**
   - Book new appointments
   - Navigation back to dashboard

### JavaScript Files

1. **js/dashboard.js**
   - Load and display appointments
   - Handle reschedule functionality
   - Handle appointment deletion
   - Handle account deletion
   - Modal management

2. **js/profile.js**
   - Load user information
   - Handle account deletion
   - Modal management

3. **js/booking.js**
   - Handle appointment booking

4. **js/utils.js**
   - Authentication checks
   - User info management
   - Navigation helpers

---

## Security Considerations

1. **Session Management**
   - All operations require active session(s)
   - Sessions stored in PHP `$_SESSION`

2. **Authorization**
   - Users can only access their own data
   - User ID verified before operations
   - Password required for account deletion

3. **Data Validation**
   - Input validation on all endpoints
   - Empty field checks
   - Date/time format validation

4. **SQL Injection Prevention**
   - Prepared statements using PDO
   - Parameter binding for all queries

5. **Error Handling**
   - Proper HTTP status codes (401, 403, 404, 500)
   - Detailed error messages in JSON responses
   - Error logging for debugging

---

## Database Schema

Tables used:

### Users Table
```sql
CREATE TABLE Users (
    UserID INTEGER PRIMARY KEY AUTOINCREMENT,
    FirstName TEXT,
    LastName TEXT,
    Email TEXT UNIQUE NOT NULL,
    Phone TEXT,
    Password_Hash TEXT NOT NULL,
    Role TEXT DEFAULT 'customer'
)
```

### Appointments Table
```sql
CREATE TABLE Appointments (
    AppointmentID INTEGER PRIMARY KEY AUTOINCREMENT,
    UserID INTEGER NOT NULL,
    BeauticianID INTEGER,
    Appointment_Date TEXT NOT NULL,
    Appointment_Time TEXT NOT NULL,
    Status TEXT DEFAULT 'scheduled',
    FOREIGN KEY(UserID) REFERENCES Users(UserID)
)
```

---

## Testing the CRUD Operations

### Create (C)
1. Test user registration with valid data
2. Test appointment booking with valid date/time

### Read (R)
1. Fetch appointments after login
2. Retrieve user profile information

### Update (U)
1. Reschedule appointment to different date/time
2. Verify appointment update in database

### Delete (D)
1. Delete an appointment and verify removal
2. Delete account and verify all data removal

---

## Error Handling Examples

### Missing Required Fields
```json
{
  "success": false,
  "message": "Missing required fields"
}
```

### Unauthorized Access
```json
{
  "success": false,
  "message": "Login required"
}
```

### Permission Denied
```json
{
  "success": false,
  "message": "You do not have permission to update this appointment"
}
```

### Database Error
```json
{
  "success": false,
  "message": "Failed to reschedule appointment"
}
```

---

## File Structure

```
glamor-webpage/
├── Index files
│   ├── index.html
│   ├── login.php
│   ├── register.php
│   ├── dashboard.html
│   ├── profile.html
│   └── booking.html
├── API Endpoints
│   ├── config.php
│   ├── booking.php
│   ├── get-appointments.php
│   ├── get-user.php
│   ├── update-appointment.php
│   ├── delete-appointment.php
│   ├── delete-account.php
│   └── process_booking.php (backup)
├── Assets
│   ├── css/
│   ├── img/
│   └── js/
│       ├── utils.js
│       ├── dashboard.js
│       ├── profile.js
│       ├── booking.js
│       ├── login.js
│       └── register.js
└── Database
    └── salon.db (SQLite)
```

---

## Summary

All CRUD operations have been successfully implemented with:
- ✅ Complete CREATE functionality (users and appointments)
- ✅ Complete READ functionality (appointments and user info)
- ✅ Complete UPDATE functionality (reschedule appointments)
- ✅ Complete DELETE functionality (delete appointments and accounts)
- ✅ Full authentication and authorization
- ✅ Security best practices
- ✅ User-friendly interfaces
- ✅ Error handling and validation
