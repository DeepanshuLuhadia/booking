<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        // NOTE: `google_id`, `email_verified_at` and `password_set_at` are
        // deliberately absent. The first two are the identity a Google sign-in
        // is keyed on; the third records that the owner chose their own
        // password. All three are written only through forceFill, by
        // Auth\GoogleAuthController, Auth\VendorGoogleController and
        // Vendor\ProfileController::updatePassword.

        'name',
        'email',
        'mobile',
        'role',
        'status',
        'avatar',
        'password',
        'fcm_token',
        'mobile_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'mobile_verified_at' => 'datetime',
            'password_set_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Send the password reset link using our own branded template rather than
     * Laravel's default markdown notification.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function vendor()
    {
        return $this->hasOne(Vendor::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'customer_id');
    }

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isVendor()
    {
        return $this->role === 'vendor';
    }

    public function isCustomer()
    {
        return $this->role === 'customer';
    }

    public function isEmployee()
    {
        return $this->role === 'employee';
    }

    /**
     * Signed in through "Continue with Google" at some point, so the address on
     * the account has been confirmed by Google rather than just typed in.
     */
    public function usesGoogleSignIn(): bool
    {
        return filled($this->google_id) && $this->email_verified_at !== null;
    }

    /**
     * Does this account have a password its owner actually chose?
     *
     * False for an account created through "Sign up with Google": the column is
     * NOT NULL so it holds a random string, but nobody knows it. Callers use
     * this to decide whether changing the password may ask for the current one
     * — asking a Google vendor for a secret they have never seen would leave
     * them unable to ever set one.
     */
    public function hasPassword(): bool
    {
        return $this->password_set_at !== null;
    }

    /**
     * Both doors are open: they can sign in with Google *and* with an email
     * and password. This is the "two-way login" the settings page offers.
     */
    public function hasTwoWayLogin(): bool
    {
        return $this->usesGoogleSignIn() && $this->hasPassword();
    }
}
