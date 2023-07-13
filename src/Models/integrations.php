<?php

namespace Models;

use Models\Accounts;
use Interfaces\ModelInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


/**
 * Class Integrations ORM Model
 * 
 * @package Models\Integrations
 */
class Integrations extends Model implements ModelInterface
{
    /**
     * Table that associated with model.
     * 
     * @var string $table
     */
    protected $table = 'integrations';

    /**
     * The primary key associated with the table.
     *
     * @var string $primaryKey
     */
    protected $primaryKey = 'integrations_id';

    /**
     * Disabling auto-increment
     * 
     * @var bool $incrementing
     */
    public $incrementing = false;

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
        'integrations_secret_key', 'integrations_redirect_url', 
        'integrations_domain', 'integrations_id'
    ];

    /**
     * Returning an array of fillable columns
     * 
     * @return array
     */
    public static function getFillableColumns(): array
    {
        return [
            'integrations_secret_key', 'integrations_redirect_url', 
            'integrations_domain', 'integrations_id'
        ];
    }

    /**
     * Getiing the accounts wchich related to this integration.
     * 
     * @return HasMany 
     */
    public function integration(): HasMany
    {
        return $this->hasMany(Accounts::class);
    }
}
