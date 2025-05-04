<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
// use Microsoft\Graph\GraphServiceClient;
// use Microsoft\Graph\Generated\Users\Item\AssignLicense\AssignLicensePostRequestBody;
// use Microsoft\Graph\Generated\Models\AssignedLicense;

class MicrosoftGraphService
{
    protected $client;
    protected $graphUrl = "https://graph.microsoft.com/v1.0/";

    public function __construct()
    {
        $this->client = new Client();
    }

    public function getAccessToken(): mixed
    {
        $response = $this->client->post('https://login.microsoftonline.com/' . env('MICROSOFT_TENANT_ID') . '/oauth2/v2.0/token', [
            'form_params' => [
                'client_id' => env('MICROSOFT_CLIENT_ID'),
                'client_secret' => env('MICROSOFT_CLIENT_SECRET'),
                'scope' => 'https://graph.microsoft.com/.default',
                'grant_type' => 'client_credentials',
            ],
        ]);

        $data = json_decode($response->getBody(), true);
        return $data['access_token'];
    }

    public function createUser($userDetails)
    {
        $token = $this->getAccessToken();

        $jsonData = [
            'accountEnabled' => true,
            'displayName' => $userDetails['name']  . " " . $userDetails['usercode'],
            'mailNickname' => $userDetails['usercode'],
            'userPrincipalName' => $userDetails['usercode'] . '@anasacademy.uk',
            'givenName' =>  $userDetails['name'],
            'surname' =>  $userDetails['usercode'],
            'passwordProfile' => [
                'forceChangePasswordNextSignIn' => true,
                'password' => $userDetails['password'],
            ],
            'usageLocation' => "US"
        ];

        // if (!empty($userDetails['first_name'])) {
        //     $jsonData['givenName'] = $userDetails['name'];
        // }

        // if (!empty($userDetails['last_name'])) {
        //     $jsonData['surname'] = $userDetails['usercode'];
        // }

        $response = $this->client->post('https://graph.microsoft.com/v1.0/users', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ],
            'json' => $jsonData,
        ]);

        return json_decode($response->getBody(), true);
    }

    public function userExists($email)
    {
        $token = $this->getAccessToken();

        // Make a GET request to check if the user exists
        $response = $this->client->get('https://graph.microsoft.com/v1.0/users?$filter=userPrincipalName eq ' . "'$email'", [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ],
        ]);

        $data = json_decode($response->getBody(), true);

        // Check if the user exists
        return !empty($data['value']);
    }

    public function getAvailableLicenses()
    {
        $token = $this->getAccessToken();

        $response = $this->client->get('https://graph.microsoft.com/v1.0/subscribedSkus', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    public function assignLicense($userId, $skuId)
    {
        $token = $this->getAccessToken();

        $response = $this->client->post("https://graph.microsoft.com/v1.0/users/$userId/assignLicense", [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'addLicenses' => [
                    [
                        'skuId' => $skuId,

                    ]
                ],
                'removeLicenses' => []
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    public function getUserLicenses($userId)
    {
        $token = $this->getAccessToken();

        // Make a Graph API call to get licenses
        $accessToken = $this->getAccessToken();

        // Make a Graph API call to get licenses
        $response = Http::withToken($accessToken)->get("{$this->graphUrl}users/{$userId}/licenseDetails");

        return $response['value'];
    }

    public function getLicensesService($skuId)
    {
        $token = $this->getAccessToken();

        // Make a Graph API call to get licenses
        $accessToken = $this->getAccessToken();

        // Make a Graph API call to get licenses
        $response = Http::withToken($accessToken)->get("https://graph.microsoft.com/v1.0/me/licenseDetails/{$skuId}/servicePlans");

        return $response->json();
    }


    public function assignLicenseWithServicePlans($userId, $skuId, $enabledServicePlans, $disabledServicePlans)
    {
        $token = $this->getAccessToken();

        // Build the servicePlans object by including enabled and disabled service plans
        $servicePlans = [];

        // // Enable services
        // foreach ($enabledServicePlans as $enabledPlanId) {
        //     $servicePlans[] = [
        //         'servicePlanId' => $enabledPlanId,
        //         'capabilityStatus' => 'Enabled',  // Mark as enabled
        //     ];
        // }

        // Disable services
        foreach ($disabledServicePlans as $disabledPlanId) {
            $servicePlans[] = [
                'servicePlanId' => $disabledPlanId,
                'provisioningStatus' => 'Disabled',  // Mark as disabled
            ];
        }

        // Prepare the request body
        $response = $this->client->post("https://graph.microsoft.com/v1.0/users/$userId/assignLicense", [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'addLicenses' => [
                    [
                        'skuId' => $skuId,  // The license SKU you want to assign
                        'servicePlans' => $servicePlans  // Enable/Disable service plans
                    ]
                ],
                'removeLicenses' => [],  // Optionally, you can remove licenses here
            ],
        ]);

        return json_decode($response->getBody(), true);
    }



    public function assignLicenseToUser($userId, $skuId, $servicePlans)
    {
        $accessToken = $this->getAccessToken(); // Ensure this retrieves a valid access token

        // Prepare the payload to assign the license
        $licenseAssignments = [
            'addLicenses' => [
                [
                    'skuId' => $skuId, // License SKU ID
                ]
            ],
            'removeLicenses' => [] // Licenses to be removed if any (leave empty if none)
        ];

        // Send the request to Microsoft Graph API
        $response = Http::withToken($accessToken)
            ->post("https://graph.microsoft.com/v1.0/users/{$userId}/assignLicense", $licenseAssignments);

        // Return the response JSON
        return $response->json();
    }
}

