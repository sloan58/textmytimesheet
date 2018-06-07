<?php

namespace App;

use App\Models\TimeEntry;
use App\Jobs\WelcomeNewUser;
use Backpack\CRUD\CrudTrait;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable, CrudTrait, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'phone_number', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];


    public static function boot() {

        /**
         *  User created event
         */
        static::created(function(User $user) {
            \Log::info('User@boot: calling User Created hook', [
                $user->name, $user->email, $user->phone_number
                ]);
            WelcomeNewUser::dispatch($user);
        });

        parent::boot();
    }

    /**
     *  A User has many Time Entries
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function timeEntries()
    {
        return $this->hasMany(TimeEntry::class);
    }
}
