<?php

namespace Models;

use Models\Contacts;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


/**
 * Class Emails ORM Model
 * 
 * @package Models\Emails
 */
class Emails extends Model
{
    /**
     * Table that associated with model.
     * 
     * @var string $table
     */
    protected $table = 'emails';

    /**
     * The primary key associated with the table.
     *
     * @var string @primaryKey
     */
    protected $primaryKey = 'emails_pk';

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
        'emails_email', 'emails_fk_contacts_id', 
    ];

    /**
     * Returning an array of fillable columns
     * 
     * @return array
     */
    public static function getFillableColumns(): array
    {
        return [
            'emails_email', 'emails_fk_contacts_id', 
        ];
    }

    /**
     * Get the contacts_pk that owns this contact.
     * 
     * @return BelongsTo 
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(
            Contacts::class,
            'emails_fk_contacts_id',
            'contacts_kommo_id'
        );
    }
}