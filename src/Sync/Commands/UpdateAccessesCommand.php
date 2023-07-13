<?php

namespace Sync\Commands;


use Models\Accounts;
use Pheanstalk\Pheanstalk;
use Sync\Workers\AccessesWorker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Utils\Utils;

include './src/Sync/Workers/AccessesWorker.php';

class UpdateAccesses extends Command
{
    /** @var AccessesWorker $worker */
    private AccessesWorker $worker;

    /**
     * Constructor UpdateAccesses
     * 
     * @param AccessesWorker $worker
     */
    public function __construct(AccessesWorker $worker)
    {
        parent::__construct();
        $this->worker = $worker;
    }

    public function configure()
    {
        Utils::bootEloquent();
        $this->addArgument('time', InputArgument::REQUIRED);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $hours = $input->getArgument('time');
        $seconds = intval($hours) * 3600;
        $possiblyExpiresIn = time() + $seconds;

        $accountIdsList = array();
        $accountsAll = Accounts::all();

        foreach ($accountsAll as $account) {
            $accessOfAccount = $account->access()->first();
            if (!is_null($accessOfAccount)) {
                if (intval($accessOfAccount['accesses_expires']) < intval($possiblyExpiresIn)) {
                    array_push($accountIdsList, $account['accounts_kommo_id']);
                    echo 'Preparing account with id = ' . $account['accounts_kommo_id'] 
                    . ' for updating token.' . "\n";
                }
            }
        }
        
        $this->createTask($accountIdsList);
        return 0;
    }

    /**
     * Delegating task to queue server.
     * 
     * @param array $data
     * @return void
     */
    public function createTask(array $data): void
    {
        Pheanstalk::create($this->worker->host, $this->worker->port)
                    ->useTube('accesses')
                    ->put(json_encode($data));

        $this->worker->execute();
    }
}