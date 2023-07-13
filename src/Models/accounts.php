<?php

namespace Models;

use Interfaces\ModelInterface;
use Models\Accesses;
use Models\Integrations;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Accounts ORM Model
 * 
 * @package Models\Accounts
 */
class Accounts extends Model implements ModelInterface
{
    /**
     * Table that associated with model.
     * 
     * @var string $table
     */
    protected $table = 'accounts';

    /**
     * The primary key associated with the table.
     *
     * @var string @primaryKey
     */
    protected $primaryKey = 'accounts_pk';

    /**
     * Disabling timestamps
     * 
     * @var bool $timestamps
     */
    public $timestamps = false;

    /**
     * Mass-assignable fields
     * As it works in this app, you should also
     * provide static method getFillableColumns which duplicates this
     * array...
     * 
     * @var array $fillable
     */
    protected $fillable = [
        'accounts_kommo_id', 'accounts_fk_integrations_id', 
    ];

    /**
     * Returning an array of fillable columns
     * 
     * @return array
     */
    public static function getFillableColumns(): array
    {
        return [
            'accounts_kommo_id', 'accounts_fk_integrations_id'
        ];
    }

    /**
     * Relationg to specific access (ONE TO ONE).
     * Getting the accesses_fk_account_kommo_id associated with those account.
     * 
     * @return HasOne
     */
    public function access(): HasOne
    {
        return $this->hasOne(
            Accesses::class, 
            'accesses_fk_account_kommo_id', 
            'accounts_kommo_id'
        );
    }

    /**
     * Getiing the contacts that owns this account.
     * 
     * @return HasMany 
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(
            Contacts::class,
            'contacts_fk_account_kommo_id',
            'accounts_kommo_id'
        );
    }

    /**
     * Getiing the integrations that owns this account.
     * 
     * @return BelongsTo 
     */
    public function integration(): BelongsTo
    {
        return $this->belongsTo(
            Integrations::class, 
            'accounts_fk_integrations_id',
            'integrations_id'
        );
    }
}