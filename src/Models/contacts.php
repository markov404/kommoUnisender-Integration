<?php

namespace Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Interfaces\ModelInterface;
use Models\Accounts;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


/**
 * Class Contacts ORM Model
 * 
 * @package Models\Contacts
 */
class Contacts extends Model implements ModelInterface
{
    /**
     * Table that associated with model.
     * 
     * @var string $table
     */
    protected $table = 'contacts';

    /**
     * The primary key associated with the table.
     *
     * @var string @primaryKey
     */
    protected $primaryKey = 'contacts_kommo_id';

    /**
     * Disabling timestamps
     * 
     * @var bool $timestamps
     */
    public $timestamps = false;

    /**
     * Disabling auto-increment
     * 
     * @var bool $incrementing
     */
    public $incrementing = false;

    /**
     * Mass-assignable fields
     * As it works in this app, you should also
     * provide static method getFillableColumns which duplicates this
     * array...
     * 
     * @var array $fillable
     */
    protected $fillable = [
        'contacts_fk_account_kommo_id', 'contacts_kommo_id',
    ];

    /**
     * Returning an array of fillable columns
     * 
     * @return array
     */
    public static function getFillableColumns(): array
    {
        return [
            'contacts_fk_account_kommo_id', 'contacts_kommo_id'
        ];
    }

    /**
     * Get the accounts_kommo_id that owns this contact.
     * 
     * @return BelongsTo 
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(
            Accounts::class, 
            'contacts_fk_account_kommo_id',
            'accounts_kommo_id'
        );
    }

    /**
     * Getiing the emails that owns this contact.
     * 
     * @return HasMany 
     */
    public function emails(): HasMany
    {
        return $this->hasMany(
            Emails::class,
            'emails_fk_contacts_id',
            'contacts_kommo_id'
        );
    }
}