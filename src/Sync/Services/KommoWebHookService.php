<?php

namespace Sync\Services;

use Utils\Utils;
use Models\Contacts;
use Models\Emails;
use Models\Accounts;
use Interfaces\LoggerInterface;
use Abstractions\AbsService;
use Unisender\ApiWrapper\UnisenderApi;


/**
 * Class KommoWebHookService.
 * Extending AbsService.
 * Managing kommo webhooks.
 *
 * @package Sync\Services\KommoWebHookService
 */
class KommoWebHookService extends AbsService
{

    /** @var array $emailsToDelete */
    private array $emailsToDelete;

    /**
     * Constructor
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger = null)
    {
        parent::__construct($logger);
        $this->emailsToDelete = array();
    }

    /**
     *
     * Дергать его отсюда.
     *
     * @param array $dataList
     * @return void
     */
    public function execute(array $dataList = array()): void
    {
        parent::execute($dataList); 
        Utils::bootEloquent();

        error_log('HUI');
        $accountId = $dataList['account']['id'];
        error_log($accountId);

        $typeOfAction = array_key_first($dataList['contacts']);
        
        $contactId = $dataList['contacts'][$typeOfAction][0]['id'];
        

        $targetAccount = Accounts::whereAccountsKommoId($accountId)->get()->first();


        if (is_null($targetAccount)) {
            $this->makeResponseObject([
                'info' => 'You are not authorised yet.'
            ], 400);
            return;
        }

        $unisenderApiKey = $targetAccount->unisender_key;
        $unisenderApiClient = new UnisenderApi($unisenderApiKey);

        $emails = array();

        error_log($typeOfAction);

        if ($typeOfAction !== 'delete') {
            $emails = $this->extractEmails($dataList, $typeOfAction);
            error_log('Here is emails');
            foreach ($emails as $email) {
                error_log($email);
            }
        }

        if ((count($emails) == 0) and ($typeOfAction !== 'delete')) {
            $this->makeResponseObject(['Contact has no emails'], 400);
            return;
        }
        
        if ($typeOfAction == 'update') {
            $this->updateContact(
                $contactId, 
                $accountId, 
                $unisenderApiClient, 
                $emails
            );

        } else if ($typeOfAction == 'add') {
            $this->addContact(
                $contactId,
                $accountId,
                $unisenderApiClient,
                $emails
            );
        } else if ($typeOfAction == 'delete') {
            $this->deleteContact(
                $contactId, 
                $unisenderApiClient, 
                $emails
            );
        }

        $this->makeResponseObject([null], 200);

        return;
    }

    /**
     * Has add contact event processing logick
     * 
     * @param string $contactId
     * @param string $accountId
     * @param UnisenderApi $unisenderApiClient
     * @param array $emails
     * 
     * @return void
     */
    private function addContact(        
        string $contactId, 
        string $accountId, 
        UnisenderApi $unisenderApiClient, 
        array $emails): void
    {
        $targetContact = null;

        try {
            $targetContact = Contacts::find($contactId);
        } catch (\Throwable $e) {
            $this->writeErrorLogLine($e->getMessage() .'ayaa');
        }

        if (!is_null($targetContact)) {
            $contactEmails = $targetContact->emails()->get();
            
            if ($contactEmails->count() != 0) {

                foreach ($contactEmails as $emailRecord) {
                    try {
                        $currentEmail = $emailRecord->emails_email;

                        if (!in_array($currentEmail, $emails)) {
                            array_push($this->emailsToDelete, $currentEmail);
                        }

                    } catch (\Throwable $e) {
                        $this->writeErrorLogLine($e->getMessage() . 'two');
                    }
                }
            }

            $targetContact->delete();
        }

        Contacts::create([
            'contacts_kommo_id' => intval($contactId),
            'contacts_fk_account_kommo_id' => intval($accountId)
        ]);

        foreach ($emails as $email) {
            Emails::create([
                'emails_email' => $email, 
                'emails_fk_contacts_id' => intval($contactId)
            ]);
        }

        $this->sendContactsToUnisender(
            array_merge($this->emailsToDelete, $emails), 
            $unisenderApiClient
        );
    }

    /**
     * Has update contact event processing logick
     * 
     * @param string $contactId
     * @param string $accountId
     * @param UnisenderApi $unisenderApiClient
     * @param array $emails
     * 
     * @return void
     */
    private function updateContact(
        string $contactId, 
        string $accountId, 
        UnisenderApi $unisenderApiClient, 
        array $emails): void
    {
        $targetContact = null;            

        try {
            $targetContact = Contacts::find($contactId);
        } catch (\Throwable $e) {
            $this->writeErrorLogLine($e->getMessage() .'ayaa');
        }

        if (is_null($targetContact)) {
            try {
                $targetContact = Contacts::create([
                    'contacts_kommo_id' => intval($contactId),
                    'contacts_fk_account_kommo_id' => intval($accountId)               
                ]);
            } catch (\Throwable $e) {
                $this->writeErrorLogLine($e->getMessage() . 'one');
            }
        }

        $contactEmails = $targetContact->emails()->get();
        
        if ($contactEmails->count() != 0) {

            foreach ($contactEmails as $emailRecord) {
                try {
                    $currentEmail = $emailRecord->emails_email;

                    if (!in_array($currentEmail, $emails)) {
                        array_push($this->emailsToDelete, $currentEmail);
                    }

                    $emailRecord->delete();

                } catch (\Throwable $e) {
                    $this->writeErrorLogLine($e->getMessage() . 'two');
                }
            }
        }

        foreach ($emails as $email) {
            error_log($email);

            try {

                Emails::create([
                    'emails_email' => $email, 
                    'emails_fk_contacts_id' => intval($contactId)
                ]);

            } catch (\Throwable $e) {
                $this->writeErrorLogLine($e->getMessage() . 'three');
            }
            
        }

        $this->sendContactsToUnisender(
            array_merge($this->emailsToDelete, $emails), 
            $unisenderApiClient
        );
    }

    /**
     * Has delete contact event logick
     * 
     * @param string $contactId
     * @param UnisenderApi $unisenderApiClient
     * @param array $emails
     * 
     * @return void
     */
    private function deleteContact(
        string $contactId,
        UnisenderApi $unisenderApiClient,
        array $emails
    ): void
    {
        $contact = Contacts::find($contactId);
        if (!is_null($contact)) {
            $contactEmails = $contact->emails()->get();
            
            if ($contactEmails->count() != 0) {

                foreach ($contactEmails as $emailRecord) {
                    try {
                        $currentEmail = $emailRecord->emails_email;

                        if (!in_array($currentEmail, $emails)) {
                            array_push($this->emailsToDelete, $currentEmail);
                        }

                    } catch (\Throwable $e) {
                        $this->writeErrorLogLine($e->getMessage() . 'two');
                    }
                }
            }

            $this->sendContactsToUnisender($this->emailsToDelete, $unisenderApiClient);
            $contact->delete();
        }
    }

    /**
     * Extracting updated emails list
     * @param array $data
     * @return array
     */
    private function extractEmails($data, $typeOfAction): array
    {
        $emailsList = array();
        foreach ($data['contacts'][$typeOfAction][0] as $key=>$value) {
            //error_log($key . ' => ' . $value);

            if ($key == 'custom_fields') {
                //$custFields = 
                foreach($data['contacts'][$typeOfAction][0]['custom_fields'][0] as $k => $v) {
                    //error_log($k . ' => ' . $v);
                    $targetLevel = $data['contacts'][$typeOfAction][0]['custom_fields'][0];
                    if ($k == 'code' and $v == 'EMAIL') {
                        for ($i = 0; $i < count($targetLevel['values']); $i++) {
                            array_push($emailsList, $targetLevel['values'][$i]['value']);
                        }
                        //error_log($targetLevel['values'][0]['value']);
                    }
                }
            }
        }

        return $emailsList;
    }

    /**
     * This function is sending contacts to Unisender.
     * 
     * @param array $contactsList
     * @return void
     */
    private function sendContactsToUnisender(
        array $emails, 
        UnisenderApi $unisendereApiClient): void
    {
        $preparedData = array();

        for ($i = 0; $i < count($emails); $i++) {
            $preparedData[$i] = array(
                $emails[$i], 
                $this->isEmailShouldBeDeleted($emails[$i])
            );
        }

        $unisendereApiClient->importContacts([
            "field_names" => ['email', 'delete'], 
            "data" => $preparedData
        ]);
    }

    /**
     * @param string $email
     * @return string
     */
    private function isEmailShouldBeDeleted(string $email): string 
    {
        if (in_array($email, $this->emailsToDelete)) {
            return '1';
        } else {
            return '0';
        }
    }
}
