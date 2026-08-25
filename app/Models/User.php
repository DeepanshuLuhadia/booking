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
        // NOTE: `google_id` and `email_verified_at` are deliberately absent.
        // They are the identity a Google sign-in is keyed on, and are written
        // only by Auth\GoogleAuthController via forceFill.

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
}
