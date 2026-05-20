<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MicrosoftGraphMailer
{
    private string $tenantId;
    private string $clientId;
    private string $clientSecret;
    private string $senderEmail;

    public function __construct()
    {
        $this->tenantId     = config('services.microsoft_graph.tenant_id');
        $this->clientId     = config('services.microsoft_graph.client_id');
        $this->clientSecret = config('services.microsoft_graph.client_secret');
        $this->senderEmail  = config('services.microsoft_graph.sender_email');
    }

    /**
     * Send an HTML email to one or more recipients via Microsoft Graph.
     *
     * @param  string[]  $to      Array of recipient email addresses
     * @param  string    $subject
     * @param  string    $html    HTML body content
     */
    public function send(array $to, string $subject, string $html): void
    {
        $token = $this->getAccessToken();

        $response = Http::withToken($token)
            ->post("https://graph.microsoft.com/v1.0/users/{$this->senderEmail}/sendMail", [
                'message' => [
                    'subject' => $subject,
                    'body'    => [
                        'contentType' => 'HTML',
                        'content'     => $html,
                    ],
                    'toRecipients' => array_map(
                        fn (string $email) => ['emailAddress' => ['address' => $email]],
                        $to
                    ),
                ],
                'saveToSentItems' => false,
            ]);

        if ($response->failed()) {
            Log::error('Microsoft Graph sendMail failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            throw new \RuntimeException('Microsoft Graph sendMail failed: ' . $response->body());
        }
    }

    private function getAccessToken(): string
    {
        $response = Http::asForm()->post(
            "https://login.microsoftonline.com/{$this->tenantId}/oauth2/v2.0/token",
            [
                'grant_type'    => 'client_credentials',
                'client_id'     => $this->clientId,
                'client_secret' => $this->clientSecret,
                'scope'         => 'https://graph.microsoft.com/.default',
            ]
        );

        if ($response->failed() || !$response->json('access_token')) {
            Log::error('Microsoft Graph token request failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            throw new \RuntimeException('Could not obtain Microsoft Graph access token.');
        }

        return $response->json('access_token');
    }
}
