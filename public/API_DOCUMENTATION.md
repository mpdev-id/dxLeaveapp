# Cutikuy API Documentation

Welcome to the **Cutikuy API Documentation**. This document provides comprehensive information about all available API endpoints for the Cutikuy Management System.

## 📚 Table of Contents

1. [Introduction](#introduction)
2. [Authentication](#authentication)
3. [Base URL](#base-url)
4. [Response Format](#response-format)
5. [Rate Limiting](#rate-limiting)
6. [Security](#security)
7. [Endpoints](#endpoints)
   - [Authentication](#auth-endpoints)
   - [User Profile](#user-endpoints)
   - [Leave Requests](#leave-request-endpoints)
   - [Admin: Dashboard](#admin-dashboard-endpoints)
   - [Admin: Users](#admin-user-endpoints)
   - [Admin: Departments](#admin-department-endpoints)
   - [Admin: Leave Types](#admin-leave-type-endpoints)
   - [Admin: Public Holidays](#admin-holiday-endpoints)
   - [Admin: Entitlements](#admin-entitlement-endpoints)
   - [Admin: Leave Requests](#admin-leave-request-endpoints)

---

## Introduction

The Cutikuy API is a RESTful API that provides endpoints for managing:
- Employee leave requests
- User management
- Department management
- Leave types and entitlements
- Approval workflows
- Public holidays

All responses follow a consistent JSON structure powered by our `ResponseFormatter`.

---

## Authentication

The API uses **Laravel Sanctum** for authentication. After logging in, you'll receive a Bearer token that must be included in subsequent requests.

### Including the Token

Add the token to your request headers:

```
Authorization: Bearer YOUR_TOKEN_HERE
```

---

## Base URL

**Local Development:**
```
http://127.0.0.1:8000
```

**Production:**
```
https://your-domain.com
```

All endpoints are prefixed with `/api/`.

---

## Response Format

All API responses follow this structure:

### Success Response (200 OK)
```json
{
  "meta": {
    "code": 200,
    "status": "success",
    "message": "Operation successful"
  },
  "data": {
    // Response data here
  }
}
```

### Error Response (4xx/5xx)
```json
{
  "meta": {
    "code": 422,
    "status": "error",
    "message": "Validation failed"
  },
  "data": {
    "errors": {
      "field_name": ["Error message"]
    }
  }
}
```

---

## Rate Limiting

To prevent abuse, the API implements rate limiting:

| Endpoint Type | Limit |
|--------------|-------|
| Authentication (login, register) | 10 requests/minute |
| Authenticated User Routes | 60 requests/minute |
| Admin Routes | 120 requests/minute |

When rate limit is exceeded, you'll receive a `429 Too Many Requests` response.

---

## Security

The API implements several security measures:

✅ **HTTPS** (required in production)  
✅ **Laravel Sanctum** token authentication  
✅ **Role-based access control** (RBAC)  
✅ **Rate limiting**  
✅ **Input validation**  
✅ **Security headers** (X-Frame-Options, CSP, etc.)  

---

## Endpoints

### Auth Endpoints

#### 1. Register
**POST** `/api/register`

Create a new user account.

**Request Body:**
```json
{
  "name": "John Doe",
  "employee_code": "EMP001",
  "email": "john@example.com",
  "phone_number": "081234567890",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Response:**
```json
{
  "meta": {
    "code": 200,
    "status": "success",
    "message": "User Registered & Entitlement Created Successfully"
  },
  "data": {
    "access_token": "1|xxxxx",
    "token_type": "Bearer",
    "user": { ... }
  }
}
```

---

#### 2. Login
**POST** `/api/login`

Authenticate and receive a token.

**Request Body:**
```json
{
  "identifier": "john@example.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "meta": {
    "code": 200,
    "status": "success",
    "message": "Authenticated"
  },
  "data": {
    "access_token": "1|xxxxx",
    "token_type": "Bearer",
    "user": { ... }
  }
}
```

---

#### 3. Logout
**POST** `/api/logout`

🔒 **Requires Authentication**

Invalidate the current token.

**Headers:**
```
Authorization: Bearer YOUR_TOKEN
```

**Response:**
```json
{
  "meta": {
    "code": 200,
    "status": "success",
    "message": "Token Revoked"
  },
  "data": true
}
```

---

#### 4. Forgot Password
**POST** `/api/forgot-password`

Request a password reset link.

**Request Body:**
```json
{
  "identifier": "john@example.com"
}
```

---

#### 5. Reset Password
**POST** `/api/reset-password`

Reset password with token.

**Request Body:**
```json
{
  "email": "john@example.com",
  "token": "reset_token_here",
  "password": "newPassword123",
  "password_confirmation": "newPassword123"
}
```

---

### User Endpoints

#### Get User Profile
**GET** `/api/user`

🔒 **Requires Authentication**

Fetch the authenticated user's profile.

**Response:**
```json
{
  "meta": {
    "code": 200,
    "status": "success",
    "message": "Data profile user berhasil diambil"
  },
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": ["Employee"],
    "department": { ... }
  }
}
```

---

### Leave Request Endpoints

#### 1. List Leave Requests
**GET** `/api/leave-requests`

🔒 **Requires Authentication**

Get user's leave requests and requests needing approval.

---

#### 2. Create Leave Request
**POST** `/api/leave-requests`

🔒 **Requires Authentication**

Submit a new leave request.

**Request Body:**
```json
{
  "leave_type_id": 1,
  "start_date": "2025-12-24",
  "end_date": "2025-12-26",
  "leave_period": "full_day",
  "reason": "Family vacation",
  "supporting_document": "file.pdf"
}
```

**Leave Period Options:**
- `full_day`
- `half_day_morning`
- `half_day_afternoon`

---

#### 3. Update Leave Request
**PATCH** `/api/leave-requests/{id}`

🔒 **Requires Authentication**

Update a draft leave request.

---

#### 4. Approve/Reject Leave
**PATCH** `/api/leave-requests/{id}/approve`

🔒 **Requires Manager/Approver Role**

Approve or reject a leave request.

**Request Body:**
```json
{
  "action": "Approved",
  "comments": "Approved. Enjoy your leave!"
}
```

**Action Options:**
- `Approved`
- `Rejected`

---

### Admin Dashboard Endpoints

All admin endpoints require **Super Admin** role.

#### 1. Get Dashboard Stats
**GET** `/api/admin/dashboard/stats`

🔒 **Requires Super Admin**

Get key statistics for the dashboard.

**Response:**
```json
{
  "data": {
    "total_users": 50,
    "total_departments": 5,
    "pending_requests": 10,
    "approved_this_month": 25
  }
}
```

---

#### 2. Recent Activity
**GET** `/api/admin/dashboard/recent-activity`

🔒 **Requires Super Admin**

Get recent system activities.

---

#### 3. Upcoming Leaves
**GET** `/api/admin/dashboard/upcoming-leaves`

🔒 **Requires Super Admin**

Get upcoming approved leaves.

---

#### 4. Leave Calendar
**GET** `/api/admin/dashboard/leave-calendar`

🔒 **Requires Super Admin**

Get leave data for calendar view.

---

#### 5. Leave Chart Data
**GET** `/api/admin/dashboard/leave-chart-data`

🔒 **Requires Super Admin**

Get data for monthly leave charts.

**Query Parameters:**
- `year` (optional): Year to fetch data for
- `month` (optional): Month to fetch data for

---

### Admin User Endpoints

#### 1. List Users
**GET** `/api/admin/master/users`

🔒 **Requires Super Admin**

Get all users with pagination.

---

#### 2. Create User
**POST** `/api/admin/master/users`

🔒 **Requires Super Admin**

Create a new user.

**Request Body:**
```json
{
  "name": "Jane Doe",
  "email": "jane@example.com",
  "employee_code": "EMP002",
  "password": "password123",
  "roles": ["Employee"]
}
```

---

#### 3. Get User
**GET** `/api/admin/master/users/{id}`

🔒 **Requires Super Admin**

Get a single user by ID.

---

#### 4. Update User
**PUT** `/api/admin/master/users/{id}`

🔒 **Requires Super Admin**

Update user details.

---

#### 5. Delete User
**DELETE** `/api/admin/master/users/{id}`

🔒 **Requires Super Admin**

Delete a user.

---

#### 6. Get User Status
**GET** `/api/admin/master/users/{id}/status`

🔒 **Requires Super Admin**

Get user's current status.

---

#### 7. Get Roles
**GET** `/api/admin/master/roles`

🔒 **Requires Super Admin**

Get all available roles.

---

### Admin Department Endpoints

#### 1. List Departments
**GET** `/api/admin/master/departments`

🔒 **Requires Super Admin**

---

#### 2. Create Department
**POST** `/api/admin/master/departments`

🔒 **Requires Super Admin**

**Request Body:**
```json
{
  "name": "Engineering"
}
```

---

#### 3. Get Department
**GET** `/api/admin/master/departments/{id}`

🔒 **Requires Super Admin**

---

#### 4. Update Department
**PUT** `/api/admin/master/departments/{id}`

🔒 **Requires Super Admin**

---

#### 5. Delete Department
**DELETE** `/api/admin/master/departments/{id}`

🔒 **Requires Super Admin**

---

### Admin Leave Type Endpoints

#### 1. List Leave Types
**GET** `/api/admin/master/leave-types`

🔒 **Requires Super Admin**

---

#### 2. Create Leave Type
**POST** `/api/admin/master/leave-types`

🔒 **Requires Super Admin**

**Request Body:**
```json
{
  "name": "Annual Leave",
  "default_entitlement_days": 12
}
```

---

#### 3. Get Leave Type
**GET** `/api/admin/master/leave-types/{id}`

🔒 **Requires Super Admin**

---

#### 4. Update Leave Type
**PUT** `/api/admin/master/leave-types/{id}`

🔒 **Requires Super Admin**

---

#### 5. Delete Leave Type
**DELETE** `/api/admin/master/leave-types/{id}`

🔒 **Requires Super Admin**

---

### Admin Holiday Endpoints

#### 1. List Public Holidays
**GET** `/api/admin/master/public-holidays`

🔒 **Requires Super Admin**

---

#### 2. Create Public Holiday
**POST** `/api/admin/master/public-holidays`

🔒 **Requires Super Admin**

**Request Body:**
```json
{
  "name": "Independence Day",
  "date": "2025-08-17"
}
```

---

#### 3. Get Public Holiday
**GET** `/api/admin/master/public-holidays/{id}`

🔒 **Requires Super Admin**

---

#### 4. Update Public Holiday
**PUT** `/api/admin/master/public-holidays/{id}`

🔒 **Requires Super Admin**

---

#### 5. Delete Public Holiday
**DELETE** `/api/admin/master/public-holidays/{id}`

🔒 **Requires Super Admin**

---

### Admin Entitlement Endpoints

#### 1. List Entitlements
**GET** `/api/admin/master/employee-entitlements`

🔒 **Requires Super Admin**

---

#### 2. Create Entitlement
**POST** `/api/admin/master/employee-entitlements`

🔒 **Requires Super Admin**

**Request Body:**
```json
{
  "user_id": 1,
  "leave_type_id": 1,
  "initial_balance": 12
}
```

---

#### 3. Get Entitlement
**GET** `/api/admin/master/employee-entitlements/{id}`

🔒 **Requires Super Admin**

---

#### 4. Update Entitlement
**PUT** `/api/admin/master/employee-entitlements/{id}`

🔒 **Requires Super Admin**

---

#### 5. Delete Entitlement
**DELETE** `/api/admin/master/employee-entitlements/{id}`

🔒 **Requires Super Admin**

---

### Admin Leave Request Endpoints

#### 1. List All Leave Requests
**GET** `/api/admin/master/leave-requests`

🔒 **Requires Super Admin**

Get all leave requests with filtering and pagination.

**Query Parameters:**
- `search`: Search by user name or leave type
- `sort_by`: Field to sort by
- `sort_dir`: `asc` or `desc`
- `page`: Page number
- `per_page`: Items per page

---

#### 2. Create Leave Request (Admin)
**POST** `/api/admin/master/leave-requests`

🔒 **Requires Super Admin**

Create a leave request on behalf of a user.

---

#### 3. Get Leave Request
**GET** `/api/admin/master/leave-requests/{id}`

🔒 **Requires Super Admin**

---

#### 4. Update Leave Request (Admin)
**PUT** `/api/admin/master/leave-requests/{id}`

🔒 **Requires Super Admin**

---

#### 5. Delete Leave Request
**DELETE** `/api/admin/master/leave-requests/{id}`

🔒 **Requires Super Admin**

---

#### 6. Handle Approval (Admin Override)
**PATCH** `/api/admin/master/leave-requests/{id}/handle-approval`

🔒 **Requires Super Admin**

Approve or reject a leave request as admin.

**Request Body:**
```json
{
  "action": "Approved",
  "comments": "Approved by admin",
  "approver_id": 2
}
```

---

#### 7. Submit Leave Request
**POST** `/api/admin/master/leave-requests/{id}/submit`

🔒 **Requires Super Admin**

Submit a draft leave request for approval.

---

## Error Codes

| Code | Meaning |
|------|---------|
| 200 | Success |
| 401 | Unauthorized (invalid/missing token) |
| 403 | Forbidden (insufficient permissions) |
| 404 | Not Found |
| 422 | Validation Error |
| 429 | Too Many Requests (rate limit exceeded) |
| 500 | Internal Server Error |

---

## Support

For issues or questions, please contact:
- **Email:** support@Cutikuy.com
- **GitHub:** [github.com/mnprasetya/CutikuyApp](https://github.com/mnprasetya/CutikuyApp)

---

**Last Updated:** November 23, 2025  
**API Version:** 1.0  
**Laravel Version:** 11.x
