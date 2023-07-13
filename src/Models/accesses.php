<?php

namespace Models;


use Illuminate\Database\Eloquent\Relations\HasOne;
use Interfaces\ModelInterface;
use Models\Accounts;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Accesses ORM Model
 * 
 * @package Models\Accesses
 */
class Accesses extends Model implements ModelInterface
{
    /**
     * Table that associated with model.
     * 
     * @var string $table
     */
    protected $table = 'accesses';

    /**
     * The primary key associated with the table.
     *
     * @var string @primaryKey
     */
    protected $primaryKey = 'accesses_pk';

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
        'accesses_token', 'accesses_base_domain',
        'accesses_refresh_token', 'accesses_expires',
        'accesses_fk_account_kommo_id', 
    ];

    /**
     * Returning an array of fillable columns
     * 
     * @return array
     */
    public static function getFillableColumns(): array
    {
        return [
            'accesses_token', 'accesses_base_domain',
            'accesses_refresh_token', 'accesses_expires',
            'accesses_fk_account_kommo_id', 
        ];
    }

    /**
     * Relationg to specific account (ONE TO ONE).
     * Getting the accounts_kommo_id associated with those accesses.
     * 
     * @return HasOne
     */
    public function account(): HasOne
    {
        return $this->hasOne(Accounts::class, 'accesses_fk_account_kommo_id', 'accounts_kommo_id');
    }
}