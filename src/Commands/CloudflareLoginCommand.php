<?php

namespace Cumulus\Cumulus\Commands;

use Exception;
use Cumulus\Cumulus\Helpers;
use Cloudflare\API\Auth\APIToken;
use Laravel\VaporCli\Helpers as VaporHelpers;
use Cloudflare\API\Auth\APIKey;
use Cloudflare\API\Adapter\Guzzle;
use Cloudflare\API\Endpoints\User;
use Laravel\VaporCli\Commands\Command;
use Cumulus\Cumulus\CloudflareEndpoints\UserApiToken;

class CloudflareLoginCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('cloudflare:login')
            ->setDescription('Authenticate with Cloudflare');
    }

    /**
     * Execute the command.
     *
     * @return void
     * @throws Exception
     */
    public function handle()
    {
        $apiToken = VaporHelpers::secret('API Token');

        $key = new APIToken($apiToken);
        $adapter = new Guzzle($key);

        $userApiTokens = new UserApiToken($adapter);

        try {
            $response = $userApiTokens->verifyToken();

            if ($response->status == 'success') {
                throw new Exception('Invalid API Token');
            }
        } catch (Exception $e) {
            if ($e->getMessage() === 'Invalid request headers') {
                VaporHelpers::abort(
                    'Invalid credentials'
                );
            }
            throw $e;
        }

        Helpers::config(
            [
                'apiToken' => $apiToken,
            ]
        );

        VaporHelpers::info('Authenticated successfully.'.PHP_EOL);
    }
}