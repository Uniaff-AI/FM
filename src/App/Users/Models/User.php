<?php

namespace App\Users\Models;

use ByteUnits\Metric;
use Illuminate\Support\Str;
use BadMethodCallException;
use Domain\Files\Models\File;
use Domain\Folders\Models\Folder;
use Laravel\Sanctum\HasApiTokens;
use Domain\Traffic\Models\Traffic;
use Illuminate\Support\Facades\DB;
use Database\Factories\UserFactory;
use Domain\Settings\Models\Setting;
use Kyslik\ColumnSortable\Sortable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Notifications\Notifiable;
use App\Users\Notifications\ResetPassword;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Domain\UploadRequest\Models\UploadRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Users\Restrictions\RestrictionsManager;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use VueFileManager\Subscription\App\User\Traits\Billable;

/**
 * @property string id
 * @property Setting settings
 * @property string email
 * @property string role
 * @property string email_verified_at
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use TwoFactorAuthenticatable;
    use HasApiTokens;
    use Notifiable;
    use HasFactory;
    use Sortable;
    use Billable;

    protected $guarded = [
        'id',
        'role',
    ];

    protected $fillable = [
        'email',
        'password',
        'oauth_provider',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'id'                => 'string',
        'email_verified_at' => 'datetime',
    ];

    protected $appends = [
        'usedCapacity',
        'storage',
    ];

    public $sortable = [
        'id',
        'name',
        'role',
        'created_at',
    ];

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * Вернуть список всех допустимых ролей
     */
    public static function roles(): array
    {
        return ['admin', 'user', 'helper'];
    }

    /**
     * Проверить, обладает ли пользователь указанной ролью
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    public function preferredLocale(): string
    {
        return get_settings('language') ?? 'en';
    }

    public function getStorageAttribute(): array
    {
        $is_storage_limit = get_settings('storage_limitation') ?? 1;

        if (!$is_storage_limit) {
            return [
                'used'           => $this->usedCapacity,
                'used_formatted' => Metric::bytes($this->usedCapacity)->format(),
            ];
        }

        return [
            'used'               => (float)get_storage_percentage($this->usedCapacity, $this->limitations->max_storage_amount),
            'used_formatted'     => get_storage_percentage($this->usedCapacity, $this->limitations->max_storage_amount) . '%',
            'capacity'           => $this->limitations->max_storage_amount,
            'capacity_formatted' => toGigabytes($this->limitations->max_storage_amount),
        ];
    }

    public function getUsedCapacityAttribute(): int
    {
        return DB::table('files')
            ->where('user_id', $this->id)
            ->sum('filesize');
    }

    public function settings(): HasOne
    {
        return $this->hasOne(UserSetting::class);
    }

    public function limitations(): HasOne
    {
        return $this->hasOne(UserLimitation::class);
    }

    public function favouriteFolders(): BelongsToMany
    {
        return $this->belongsToMany(Folder::class, 'favourite_folder', 'user_id', 'parent_id', 'id', 'id')
            ->where('team_folder', false);
    }

    public function filesWithTrashed(): HasMany
    {
        return $this->hasMany(File::class)
            ->withTrashed();
    }

    public function latestUploads(): HasMany
    {
        return $this->hasMany(File::class)
            ->with([
                'parent:id,name',
                'shared:token,id,item_id,permission,is_protected,expire_in',
            ]);
    }

    public function files(): HasMany
    {
        return $this->hasMany(File::class);
    }

    public function folders(): HasMany
    {
        return $this->hasMany(Folder::class);
    }

    public function traffics(): HasMany
    {
        return $this->hasMany(Traffic::class);
    }

    public function uploadRequest(): HasOne
    {
        return $this->hasOne(UploadRequest::class);
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPassword($token));
    }

    public function __call($method, $parameters)
    {
        try {
            if (str_starts_with($method, 'can') || str_starts_with($method, 'get')) {
                return resolve(RestrictionsManager::class)
                    ->driver()
                    ->$method($this, ...$parameters);
            }
        } catch (BadMethodCallException $e) {
            return parent::__call($method, $parameters);
        }

        return parent::__call($method, $parameters);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            $user->id = Str::uuid();

            $user->limitations()->create([
                'max_storage_amount' => get_settings('default_max_storage_amount') ?? 1,
                'max_team_members'   => get_settings('default_max_team_member') ?? 10,
            ]);

            Storage::makeDirectory("files/$user->id");
        });

        static::updating(function ($user) {
            if (config('vuefilemanager.is_demo') && $user->email === 'howdy@hi5ve.digital') {
                $user->two_factor_secret = null;
                $user->two_factor_recovery_codes = null;
            }
        });
    }
}
