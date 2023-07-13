<?php

namespace Sync\Services;

use Config\ValidationTraitsConfig;
use Interfaces\LoggerInterface;
use Interfaces\AccountsManagerInterface;
use Abstractions\AbsService;
use Unisender\ApiWrapper\UnisenderApi;

use Abstractions\Types\SetOfModelManagers;
use Sdk\AmoApiClient;
use Utils\Utils;

use Models\Integrations;
use Models\Contacts;
use Models\Accounts;
use Models\Emails;

use Traits\IntegrationValidator;


/**
 * Class SendContactsFromKommoService.
 * Extending AbsService.
 * Getting list of Kommo account contacts and sending it to Unisender.
 *
 * @package Sync\Services\SendContactsFromKommoService
 */
class SendContactsFromKommoService extends AbsService
{
    use IntegrationValidator;

    /** @var array $contactsWithEmails ...just dont touch it */
    private array $contactsWithEmails;

    /** @var array $actualEmailListInDb ... just dont touch it */
    private array $actualEmailListInDb;

    /** @var array $emailsShouldBeDeleted ... just dont touch it */
    private array $emailsShouldBeDeleted;

    /**
     * Constructor
     *
     * @param LoggerInterface $logger
     * @param SetOfModelManagers $modelManagers
     */
    public function __construct(
        LoggerInterface $logger = null,
        SetOfModelManagers $modelManagers = null,
        ValidationTraitsConfig $validationTraitsConfig = null)
    {
        parent::__construct($logger, $modelManagers, get_class($this), $validationTraitsConfig);
        $this->contactsWithEmails = array();
        $this->actualEmailListInDb = array();
        $this->emailsShouldBeDeleted = array();
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
        if ($this->isResponseSet()) { return; }

        if (!$this->validateRequestParams($dataList, ['id'])) {
            $this->makeResponseObject([null], 400);
            return;
        }

        $account = Accounts::whereAccountsKommoId($dataList['id'])->get()->first();
        if (is_null($account)) {
            $this->makeResponseObject([
                'info' => 'You are not authorised.'
            ], 401);
            return;
        }

        $unisenderApiKey = $account->unisender_key;

        $integration = Integrations::all()->first();

        $kommoApiClient = new AmoApiClient(
            $integration->integrations_id,
            $integration->integrations_secret_key,
            $integration->integrations_redirect_url
        );

        $unisenderApiClient = new UnisenderApi($unisenderApiKey);

        // 31334219
        $accountId = $dataList['id'];

        /** @var AccountsManagerInterface $accountsManager */
        $accountsManager = $this->setOfModelManagers->getManager('accounts');
        
        /** Getting account */
        $response = $accountsManager->entityWhere('accounts_kommo_id = ' . $accountId)[0];
        if (Utils::isCustomException($response)) {
            $this->writeErrorLogLine($response->asPhpJson() . ' line71');
            $this->makeResponseObject([
                'info' => 'Something went wrong while getting your account data.'
            ], 500);
            return;
        }

        /** Check if account_id is valid and registered in system */
        if (is_null($response)) {
            $this->makeResponseObject([
                'info' => 'There is no account with this account_id',
            ], 400);
            return;
        }
        $accounts = $response; // Saved it for later maneuvers

        /** Getting account accesses */
        $response = $accountsManager->getAccountAccessesWithWhere('accounts_kommo_id = ' . $accountId);
        if (Utils::isCustomException($response)) {
            $this->writeErrorLogLine($response->asPhpJson() . ' line88');
            $this->makeResponseObject([
                'info' => 'Something went wrong while getting your account accesses.'
            ], 500);
            return;
        }

        if (is_null($response)) {
            $this->makeResponseObject([
                'info' => 'This account has no access rights...'
            ], 400);
            return;
        }

        $kommoApiClient->setUpMandatoryCredentialsManually(
            $response['accesses_base_domain'],
            Utils::deCompressString($response['accesses_token']),
            Utils::deCompressString($response['accesses_refresh_token']),
            intval($response['accesses_expires']),
        );


        $nextPage = 1;
        $allContactsFromKommo = array();
        while (!is_null($nextPage)) {
            $contactCollection = $kommoApiClient->getContactsPage($nextPage);
            $nextPageLink = $contactCollection->getNextPageLink();
            $contacts = $contactCollection->toArray();

            $allContactsFromKommo = array_merge($allContactsFromKommo, $contacts);

            if ($nextPageLink == '') {
                $nextPage = null;
            } else {
                $data = parse_url($nextPageLink);
                $nextPage = intval($data['page']);
            }
        }
        $allActualContacts = array();
        $allContacts = Contacts::all();
        
        foreach ($allContacts as $contact) {

            $emailsRelated = $contact->emails()->get();
            $emailsArray = array();
            foreach ($emailsRelated as $emailRecord) {
                array_push($emailsArray, $emailRecord['emails_email']);
            }
            $allActualContacts[$contact['contacts_kommo_id']] = $emailsArray;
            $this->writeDebugLogLine(implode('..', $allActualContacts[$contact['contacts_kommo_id']]));
        }
        
        $allKommoContacts = $this->extractEmailsAndContactIdRelation($allContactsFromKommo);

        $counter = 0;
        $actualContactKeysToDelete = array();
        foreach ($allActualContacts as $key=>$value) {
            if (!in_array($key, array_keys($allKommoContacts))) {
                $contact = Contacts::find(intval($key));
                $contactEmails = $contact->with('emails')->get();
                foreach ($contactEmails as $emailRecord) {
                    array_push($this->emailsShouldBeDeleted, $emailRecord['emails_email']);
                    $emailRecord->delete();
                }
                $contact->delete();
                array_push($actualContactKeysToDelete, $key);
            }
        }
        foreach ($actualContactKeysToDelete as $key) {
            unset($actualContactKeysToDelete[$key]);
        }

        // $this->makeResponseObject($allKommoContacts, 200);
        // error_log(implode(',', $allKommoContacts));
        // return;

        $counter = 0;
        foreach ($allKommoContacts as $key => $value) {
            if (!in_array($key, array_keys($allActualContacts))) {
                Contacts::create([
                    'contacts_kommo_id' => intval($key), 
                    'contacts_fk_account_kommo_id' => $accountId
                ]);
                
                foreach ($value as $email) {
                    Emails::create([
                        'emails_email' => $email,
                        'emails_fk_contacts_id' => intval($key)
                    ]);
                }

                $allActualContacts[$key] = $value;
            } else {
                foreach($allActualContacts[$key] as $email) {
                    if (!in_array($email, $value)) {
                        array_push($this->emailsShouldBeDeleted, $email);
                    }
                }
                $allActualContacts[$key] = $value;
            }
            $counter = $counter + 1;
        }

        $emails = array();
        foreach ($allActualContacts as $key=>$value) {
            foreach ($value as $email) {
                array_push($emails, $email);
            }
        }
        $emails = array_merge($this->emailsShouldBeDeleted, $emails);

        $this->sendContactsToUnisender($emails, $unisenderApiClient);

        $this->makeResponseObject($allActualContacts, 200);
        return;
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
                $this->isShouldBeDeleted($emails[$i])
            );
        }

        $unisendereApiClient->importContacts([
            "field_names" => ['email', 'delete'], 
            "data" => $preparedData
        ]);
    }

    /**
     * This function is parsing json contacts data to emails list
     * 
     * @param array $contactsList
     * @return array
     */
    private function extractEmailsAndContactIdRelation(array $contactsList): array
    {
        $result = array();

        for ($i = 0; $i < count($contactsList); $i++) {
            if (is_null($contactsList[$i]["custom_fields_values"])) {
                continue;
            }

            $customFields = $contactsList[$i]["custom_fields_values"];
            for ($j = 0; $j < count($customFields); $j++) {
                if ($customFields[$j]["field_code"] == "EMAIL") {
                    $valuesArr = $customFields[$j]["values"];
                    
                    $currentContactEmails = array();
                    for ($c = 0; $c < count($valuesArr); $c++) {
                        array_push($currentContactEmails, $valuesArr[$c]["value"]);
                    }
                    
                    $result[$contactsList[$i]['id']] = $currentContactEmails;
                };
            }
        }

        return $result;
    }

    /**
     * Return '1' if contact should be deleted
     *        '0' if not...
     * @param string $email
     * @return string
     */
    private function isShouldBeDeleted(string $email): string
    {
        if (in_array($email, $this->emailsShouldBeDeleted)) {
            return '1';
        } else {
            return '0';
        };
    }
}
