# **Artship - Secure Art Learning Platform**

<p align="center">
<a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a>
</p>

<p align="center">
<img src="https://img.shields.io/badge/Laravel-10.x-FF2D20?style=for-the-badge&logo=laravel" alt="Laravel 10">
<img src="https://img.shields.io/badge/PHP-8.0+-777BB4?style=for-the-badge&logo=php" alt="PHP 8.0+">
<img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql" alt="MySQL">
<img src="https://img.shields.io/badge/ECC-Secp256k1-4B0082?style=for-the-badge" alt="ECC">
<img src="https://img.shields.io/badge/RSA-2048bit-FF6B6B?style=for-the-badge" alt="RSA">
<img src="https://img.shields.io/badge/Security-From%20Scratch-00C853?style=for-the-badge" alt="From Scratch">
</p>

---

## **Overview**

**Artship** is a comprehensive Laravel-based e-learning platform designed specifically for art education. It enables artists to teach and learn art through structured courses while providing tools for course management, progress tracking, and community engagement through artwork sharing.

**But more than that** — Artship is a **security-first application** that demonstrates **from-scratch implementation of modern cryptographic algorithms**. Every piece of user data is protected with custom-built encryption, ensuring confidentiality, integrity, and authenticity at every layer of the application.

This project was developed as a **CSE447 (Computer Security) Lab Project** at **BRAC University**, combining full-stack web development with practical, hands-on cryptography.

---

## **Art Learning Features**

### **Course Management**
- **Admin Course Control** — Create, edit, and delete art courses
- **Category Filtering** — Browse courses by artistic categories (digital painting, watercolor, sculpture, etc.)
- **Wishlist System** — Save courses for later enrollment

### **Learning Experience**
- **Course Enrollment** — One-click enrollment/unenrollment system
- **Progress Tracker** — Visual progress tracking for each enrolled course
- **Course Materials** — Upload and manage lecture videos and downloadable PDFs
- **Course Reviews** — Rate and review completed courses

### **Artwork Portfolio & Community**
- **Artwork Submission** — Students can upload their final artworks at course completion
- **Featured Gallery** — Admins can showcase exceptional student artworks
- **Social Engagement** — Users can like and appreciate featured artworks

---

## **Security Features (From Scratch)**

> **All cryptographic algorithms are implemented from scratch. No built-in `openssl_*`, `hash_*`, `sodium_*`, `Crypt::*`, or `Hash::*` functions were used for encryption/hashing.**

### **Cryptographic Algorithms Implemented**

| Algorithm | Purpose | Implementation |
|-----------|---------|----------------|
| **RSA (2048-bit)** | Digital signatures, key exchange |  From scratch |
| **ECC (secp256k1)** | Data encryption, ECDH key exchange |  From scratch |
| **SHA-256** | Hashing for integrity, HMAC |  From scratch |
| **SHA-1** | TOTP for 2FA |  From scratch |
| **HMAC-SHA256** | Message Authentication Codes |  From scratch |
| **PBKDF2** | Password hashing with salting |  From scratch |
| **TOTP** | Two-Factor Authentication |  From scratch |
| **Base32** | TOTP secret encoding |  From scratch |

---

### ** Security Architecture**

```
┌──────────────────────────────────────────────────────────────────────────┐
│                        ARTSHIP SECURITY LAYERS                           │
├──────────────────────────────────────────────────────────────────────────┤
│                                                                          │
│  1. AUTHENTICATION                                                       │
│     ├── Custom Password Hashing (PBKDF2 + Salt + SHA-256)               │
│     ├── Two-Factor Authentication (TOTP + SHA-1 + HMAC)                 │
│     ├── Secure Session Management (AES-256 + MAC)                       │
│     └── Role-Based Access Control (Admin/User)                          │
│                                                                          │
│  2. ENCRYPTION (Asymmetric Only)                                         │
│     ├── ECC for data encryption (secp256k1 curve)                       │
│     ├── RSA for digital signatures (2048-bit)                           │
│     ├── Key Management Module (Generate, Rotate, Revoke, Export)        │
│     └── Koblitz encoding for ECC message embedding                      │
│                                                                          │
│  3. INTEGRITY                                                            │
│     ├── HMAC-SHA256 for every encrypted field                           │
│     ├── Automatic tamper detection on retrieval                         │
│     ├── Integrity failure logging & flagging                            │
│     └── MAC verification on every request                               │
│                                                                          │
│  4. DATA PROTECTION                                                      │
│     ├── All user info encrypted at rest (name, email, contact)          │
│     ├── All posts/courses/artwork encrypted before storage              │
│     ├── Keys stored encrypted (private keys never plaintext)            │
│     └── Automatic encryption/decryption via model traits                │
│                                                                          │
└──────────────────────────────────────────────────────────────────────────┘
```

---

### ** Key Management Module**

- **Key Generation** — Generate RSA and ECC key pairs on demand
- **Key Storage** — Keys stored with metadata (fingerprint, version, purpose)
- **Key Rotation** — Rotate keys when needed; old keys remain for decryption
- **Key Revocation** — Revoke compromised keys
- **Key Export** — Export public keys for sharing
- **Key Fingerprinting** — SHA-256 fingerprint for each key

---

### ** Encryption at Every Layer**

| Data Type | Algorithm | Protection |
|-----------|-----------|------------|
| **User Name** | ECC (secp256k1) |  Encrypted + MAC |
| **User Email** | ECC (secp256k1) |  Encrypted + MAC |
| **Password** | PBKDF2 (SHA-256) |  Hashed + Salted |
| **Course Title** | ECC (secp256k1) |  Encrypted + MAC |
| **Course Description** | ECC (secp256k1) |  Encrypted + MAC |
| **Artwork Title** | ECC (secp256k1) |  Encrypted + MAC |
| **Artwork Description** | ECC (secp256k1) |  Encrypted + MAC |
| **Course Reviews** | ECC (secp256k1) |  Encrypted + MAC |
| **Session Data** | AES-256 + MAC |  Encrypted + MAC |
| **2FA Secrets** | ECC (secp256k1) |  Encrypted |
| **Private Keys** | ECC (secp256k1) |  Encrypted |

---

### ** Requirements Satisfied (CSE447 Lab)**

| # | Requirement | Implementation |
|---|-------------|----------------|
| 1 | Login/Registration |  Custom auth with encrypted data |
| 2 | User info encrypted before storage |  ECC encryption on all fields |
| 3 | Passwords hashed + salted |  PBKDF2 with SHA-256 + random salt |
| 4 | Two-factor authentication |  TOTP from scratch with Google Authenticator |
| 5 | Key Management Module |  Generate, rotate, revoke, export keys |
| 6 | Posts encrypted/decrypted |  All models use EncryptableFields trait |
| 7 | All critical data encrypted |  Database contains only ciphertext |
| 8 | MAC integrity verification |  HMAC-SHA256 on every protected field |
| 9 | Asymmetric encryption only |  ECC for data, RSA for signatures |
| 10 | Two asymmetric algorithms |  ECC (encryption) + RSA (signing) |
| 11 | Role-Based Access Control |  Admin vs User privileges |
| 12 | Secure session management |  Encrypted sessions + regeneration |

---

##  **Technology Stack**

### **Backend**
- **Laravel 10** — PHP Framework
- **MySQL** — Database
- **Eloquent ORM** — Database Management
- **Blade Templates** — Server-side rendering

### **Frontend**
- **Bootstrap 5** — Responsive design
- **JavaScript** — Interactive features
- **CSS3** — Custom styling

### **Custom Security Services**
- **RSAEncryptionService** — RSA from scratch
- **ECCEncryptionService** — ECC from scratch
- **HashingService** — SHA-256 + SHA-1 from scratch
- **MACService** — HMAC from scratch
- **CustomHashService** — PBKDF2 password hashing from scratch
- **TwoFactorService** — TOTP from scratch
- **EncryptionHelper** — Orchestrates encryption with key management
- **IntegrityService** — MAC generation and verification
- **SessionEncryption** — Session data encryption

---

##  **Architecture**

### **MVC Pattern with Security Layers**

```
┌─────────────────────────────────────────────────────────────────────────┐
│                            REQUEST FLOW                                 │
│                                                                         │
│  Request → Middleware → Controller → Model → Trait → Service → DB      │
│                                                                         │
│  ┌──────────────┐                                                       │
│  │  Middleware  │ ← VerifyMAC, TwoFactorVerified, AdminMiddleware      │
│  └──────┬───────┘                                                       │
│         ↓                                                               │
│  ┌──────────────┐                                                       │
│  │  Controller  │ ← Uses EncryptionHelper for encryption/decryption     │
│  └──────┬───────┘                                                       │
│         ↓                                                               │
│  ┌──────────────┐                                                       │
│  │    Model     │ ← Uses EncryptableFields + IntegrityProtected traits  │
│  └──────┬───────┘                                                       │
│         ↓                                                               │
│  ┌──────────────┐                                                       │
│  │    Trait     │ ← Auto-encrypt/decrypt + MAC generation              │
│  └──────┬───────┘                                                       │
│         ↓                                                               │
│  ┌──────────────┐                                                       │
│  │   Service    │ ← RSA, ECC, SHA-256, HMAC, PBKDF2, TOTP              │
│  └──────┬───────┘                                                       │
│         ↓                                                               │
│  ┌──────────────┐                                                       │
│  │   Database   │ ← Only encrypted data + MACs                          │
│  └──────────────┘                                                       │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

### **Key Design Patterns**

- **Traits** — `EncryptableFields`, `IntegrityProtected` for model-level encryption
- **Services** — Separate services for each cryptographic function
- **Key Envelope** — Stores algorithm, key ID, and ciphertext together
- **Middleware** — Integrity verification at request level
- **Events** — Laravel model events (`creating`, `updating`, `retrieved`) for automatic encryption/decryption

---

## 📁 **Project Structure**

```
artship/
├── app/
│   ├── Console/Commands/
│   │   └── GenerateKeysCommand.php          # CLI key generation
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/                         # Authentication controllers
│   │   │   ├── KeyManagementController.php   # Key management UI
│   │   │   └── TwoFactorController.php       # 2FA setup/verification
│   │   └── Middleware/
│   │       ├── AdminMiddleware.php           # RBAC
│   │       ├── TwoFactorVerified.php         # 2FA enforcement
│   │       └── VerifyMAC.php                 # Integrity checks
│   ├── Models/
│   │   ├── Key.php                           # Encryption keys
│   │   ├── TwoFactorAuth.php                 # 2FA data
│   │   ├── User.php                          # Encrypted user data
│   │   └── ...                               # Other models
│   ├── Services/
│   │   ├── RSAEncryptionService.php          # RSA from scratch
│   │   ├── ECCEncryptionService.php          # ECC from scratch
│   │   ├── HashingService.php                # SHA-256/SHA-1 from scratch
│   │   ├── MACService.php                    # HMAC from scratch
│   │   ├── CustomHashService.php             # PBKDF2 from scratch
│   │   ├── TwoFactorService.php              # TOTP from scratch
│   │   ├── EncryptionHelper.php              # Encryption orchestration
│   │   ├── IntegrityService.php              # MAC verification
│   │   └── SessionEncryption.php             # Session encryption
│   └── Traits/
│       ├── EncryptableFields.php             # Auto-encryption trait
│       └── IntegrityProtected.php            # Auto-MAC trait
├── database/migrations/
│   ├── ...                                   # Standard migrations
│   └── 2026_01_01_*_add_encryption_*.php     # Encryption columns
├── resources/views/
│   └── admin/keys/                           # Key management UI
├── routes/
│   ├── web.php                               # Web routes with security
│   └── auth.php                              # Authentication routes
└── .env                                      # APP_KEY, MAC_SECRET_KEY
```

---

##  **Getting Started**

### **Prerequisites**
- PHP 8.0+
- Composer
- MySQL
- Node.js & NPM

### **Installation**

```bash
# Clone the repository
git clone https://github.com/yourusername/artship.git
cd artship

# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Generate MAC secret key (for integrity)
php -r "echo 'MAC_SECRET_KEY=' . bin2hex(random_bytes(32)) . PHP_EOL;" >> .env

# Configure database in .env
# DB_DATABASE=artship_db
# DB_USERNAME=root
# DB_PASSWORD=

# Run migrations
php artisan migrate

# Generate encryption keys
php artisan keys:generate ecc --purpose=field-encryption
php artisan keys:generate rsa --purpose=signing

# Build assets
npm run dev

# Start the server
php artisan serve
```

### **Access the Application**
- **URL:** `http://127.0.0.1:8000`
- **Admin Panel:** `http://127.0.0.1:8000/admin/keys`

---

##  **Testing Security Features**

### **Test Encryption**
```bash
php artisan tinker
```
```php
$user = App\Models\User::first();
echo $user->name;                    // Decrypted: "John Doe"
echo $user->getEncryptedValue('name'); // Encrypted: "eyJhbGci..."
```

### **Test MAC Integrity**
1. Open phpMyAdmin
2. Find user in `users` table
3. Modify `name` field (add a character)
4. Refresh profile page
5. Check `storage/logs/laravel.log` for integrity failure

### **Test 2FA**
1. Go to `/2fa/setup`
2. Scan QR code with Google Authenticator
3. Enter code to enable
4. Logout and login again → 2FA verification required

### **Test Key Management**
1. Go to `/admin/keys`
2. Generate new key
3. Rotate existing key
4. Export public key

---

## 📊 **Security Proof**

### **Database View (Encrypted)**
```
id | name (encrypted)                                    | name_mac
1  | eyJhbGciOiJlY2MiLCJrZXlfaWQiOjEsImN0IjoiVHh1...  | 77182100e215...
```

### **Browser View (Decrypted)**
```
Name: John Doe
Email: john@example.com
```

### **Session Cookie (Encrypted)**
```
artship_session: eyJpdiI6ImFIczR2ZHRFaTFKYmhKeG5XdW5ScFE9PSIs...
```

---

##  **Cryptographic Implementation Details**

### **RSA (From Scratch)**
- Miller-Rabin primality testing (40 rounds)
- Modular exponentiation (binary method)
- Extended Euclidean algorithm for modular inverse
- PKCS#1 v1.5 padding for encryption/signing

### **ECC (From Scratch)**
- secp256k1 curve parameters
- Point addition, doubling, scalar multiplication
- Koblitz encoding for message embedding
- ECDH for key exchange

### **SHA-256 (From Scratch)**
- FIPS 180-4 compliant
- Message schedule (W[0..63])
- Compression function with bitwise operations
- No `hash()` or `openssl_*` used

### **HMAC (From Scratch)**
- RFC 2104 compliant
- Inner/outer padding with XOR
- SHA-256 based

### **PBKDF2 (From Scratch)**
- 3000 iterations
- Random 32-byte salt
- HMAC-SHA256 based

### **TOTP (From Scratch)**
- RFC 6238 compliant
- 30-second time step
- 6-digit codes
- ±1 window for clock skew

---


##  **Course Information**

- **Course:** CSE447 — Computer Security
- **Institution:** BRAC University
- **Semester:** [Your Semester]
- **Instructor:** [Instructor Name]

---

##  **Important Notes**

1. **All cryptographic algorithms are implemented from scratch** — no built-in encryption/hashing functions were used for the core security features.
2. **Only asymmetric encryption is used** for data protection (ECC for data, RSA for signatures).
3. **Private keys are never stored in plaintext** — they are encrypted before storage.
4. **Every encrypted field has a corresponding MAC** for integrity verification.
5. **Session data is encrypted** and protected with MAC.

---

##  **License**

This project is for **educational purposes** as part of the CSE470 and CSE447 Lab Project at BRAC University. All rights reserved.

---
<p align="center">
  <em>Artship — Where Creativity Meets Security</em>
</p>