<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
       'role_id', 'name', 'username','email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    public function role()
    {
        return $this->belongsTo('App\Role');
    }

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
       public function posts()
       {
           return $this->hasMany('App\Post');
       }


       public function favorite_posts()
       {
           return $this->belongsToMany('App\Post')->withTimestamps();

       }

    public function comments()
    {
        return $this->hasMany('App\Comment');
    }

    public function scopeAuthors($query)
    {
        return $query->where('role_id', 2);
    }
   }
