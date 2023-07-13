# Sync
## _Integration for synchronising contacts between your Kommo account and Unisender_

Sync is listening webhooks on contact events in kommo account and sending all
information about `em to Unisender profile.

- Firstly, you should connect it in yout Kommo account in integrations section.
- Next we need to add integration data to database usin POST request, to https://<your_domain>/integration
- Then we going to https://<your_domain>/auth?id=<your_account_id> and providing credentials of our account.
- Next, you are submitting your Unisender API key yousing widget in Kommo integration.
- As final step, you are sending POST request to https://<your_domain>/sync/contacts to let our integration load all of your accounts and storing it.
- Voila! All of your contacts action will be mirroring to your Unisender account. 
## Endpoints

This is test endpoint it should return json response with sum of at least 2 query parameters, which you have passed to it.
```sh
GET /sum?first=200&second=300
```

Go to this endpoint for starting authentification process
```sh
GET /auth?id=%account_id%
```

Using this endpoint you can get all contacts from your Kommo account.
```sh
GET /kommo/contacts?id=%account_id%
```

This endpoint is returning contacts information from Unisender account via email.
```sh
GET /unisender/contacts?id=%account_id%&z=%target_contact_email%
```

Use this endpoint to add or delete contacts from unisender.
In all post request you should sending information via Json in Body, and
add Content-Type: 'application/json' in headers.

Example 
```
{
    "id": %account_id%,
    "emails": [],
    "delete": 1
}
```

Delete is optional parameter and if you are not pass it it will not delete account and will not throw any exceptions.

```sh
POST /unisender/contacts
```

Next endpoint is for set up of synchronising contacts process.
It tells our integration to load the contacts from account and store it in database.

Example of Body:

```
{
    "id": %account_id%
}
```

```sh
POST /sync/contacts
```

End first endpoint that you will use after connection 'Sync' integration is setting up integration data.
```sh
POST /integration
```

## For developers

### Schematic view of system design

All the Handlers should get an instance of corresponding Service which will execute all business logick. After Handler->handle() getting request it is extracting data that we need into PHP associative array and deligates processing of logick to Service. Any Service in its case has a AbsLogger instance, which will logging all the response data and all the request input data automatically.
Any Service should be an instance of AbsService.

![image](https://drive.google.com/uc?export=view&id=1j2UI2_JltYr6r3aT4zj0dl3muCDwbQ9O)

### Uml:

![image](https://drive.google.com/uc?export=view&id=1meNb_GhFn3ZmntAr9LZuR94OSiGR6q4V)

### Logging:

Example of logging format, if you want to change you should go to AsbLogger class.

```
[2023-06-28T09:18:09.436727+00:00] Sync\Services\PingUnisenderService . Logger.INFO: [(email=>Array),(delete=>1)] [] []

[2023-06-28T09:18:09.762751+00:00] Sync\Services\PingUnisenderService . Logger.INFO: Creating response with 200 status code. [] []

[2023-06-28T09:18:09.762833+00:00] Sync\Services\PingUnisenderService . Logger.INFO: Created response object => |Abstractions\AbsResponse with data: [Array] ; status code: 200 ; and message: OK| [] []
```

- First it logging the INPUT data.
- Second it logging that it starting process of creating an instance of AbsResponse object.
- It is also writing WHAT Service is processing the request.
- When response is created it writes what data contains.

We decide that it is very useful for metrics, and also AbsService contains method for writing DEBUG and ERROR logs if we fixing bugs.

### ORM
We have used the Eloquent ORM and MySql databse for the project. In the Sync/ModelManagers we have classes which is kind of Repository object oriented programming pattern, but unfortunately did not manage to use it everywhere. So some methods and some Services is using model without getaway.

All of the models should have getFillableColumns() method which is mirroring fillable columns.

```
    /**
     * Mass-assignable fields
     * As it works in this app, you should also
     * provide static method getFillableColumns which duplicates this
     * array...
     * 
     * @var array $fillable
     */
    protected $fillable = [
        'column1', 'column2',
    ];

    /**
     * Returning an array of fillable columns
     * 
     * @return array
     */
    public static function getFillableColumns(): array
    {
        return [
            'column1', 'column2'
        ];
    }
```

### Pheanstalk and Beanstalk

Config should be an implementation of file in config/autoload/%something%.global.php which is returning an array with key='beanstalk' and value as ['host'->%host%, 'port'=>%port%, 'timeout'=>%timeout%].

Than all of you should do is passing BeanstalkConfig class object (that contains Dependency Injection container that u use) to your Worker.

As example, this is factory for command which is updating all of the tokens that will expire soon.

```
class UpdateAccessesCommandFactory
{
    public function __invoke(ContainerInterface $container): UpdateAccesses
    {
        Utils::bootEloquent();
        $command = new UpdateAccesses(
            new AccessesWorker(new BeanstalkConfig($container))
        );

        return $command;
    }
}
```

### Validation traits

Same process we can use with validation traits, any trait which you want to create should have ONLY ONE static public attribute that is should be message of bad validation, and ONLY ONE static public method that is validating something state.

For example this feature is used to check if the integration is setted up on any endpoint where it is need. You just write code below in you service.

```
use $traitName;
```
If you do that you should check if your execute() function of abstract service is already create a response, and if it is, you just returning.

```
        parent::execute($dataList);
        if ($this->isResponseSet()) { return; }
```

The way to inject proper if you are using traits.

```
    /**
     * Magick method __invoke
     * Returning new instance of ContactsHandler
     *
     * @param ContainerInterface $container
     * @return SendContactsFromKommoHandler
     */
    public function __invoke(ContainerInterface $container): SendContactsFromKommoHandler
    {
        Utils::loadEnvIfNotloadedAlready();
        $logsRootPath = $_ENV['LOGS_FOLDER_PATH'];
        $date = Utils::getCurrentDateForLogging();


        $service = new SendContactsFromKommoService();
        $logger = new AbsCustomLogger($service, $logsRootPath, $date);

        $modelManagers = new SetOfModelManagers();
        $modelManagers->addManagerToList('contacts', new AbsModelManager(Contacts::class));
        $modelManagers->addManagerToList('accounts', new AccountsManager(Accounts::class));
        $modelManagers->addManagerToList('emails', new AbsModelManager(Emails::class));

        $service->setLogger($logger);
        $service->setModelManagersList($modelManagers);
        $service->setTraitsConfig(new ValidationTraitsConfig($container));

        return new SendContactsFromKommoHandler($service);
    }
}
```
Take a closelook into $service->setTraitsConfig(new ValidationTraitsConfig($container)); 
Thats the important part.

### Db scheme

![image](https://drive.google.com/uc?export=view&id=1I3DHOOdFAjFnVZWOVFYoFbO5z6id4yMC)

Long story short as simple as that.