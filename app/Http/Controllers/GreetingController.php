<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;

class GreetingController extends Controller
{
    public function greet(Request $request)
    {
        $visitorName = $request->query('visitor_name', 'Guest');
        $clientIp = $request->ip();

        // Use a service to get the location based on IP
        $location = $this->getLocationByIp($clientIp);

        // Check if location is valid
        if ($location['city'] === 'Unknown') {
            return response()->json([
                'client_ip' => $clientIp,
                'location' => 'Unknown',
                'greeting' => "Hello, $visitorName! Unfortunately, we couldn't determine your location or the temperature.",
            ]);
        }

        // Use a weather API to get the current temperature
        $temperature = $this->getTemperature($location['city']);

        return response()->json([
            'client_ip' => $clientIp,
            'location' => $location['city'],
            'greeting' => "Hello, $visitorName!, the temperature is $temperature degrees Celsius in {$location['city']}",
        ]);
    }

    private function getLocationByIp($ip)
    {
        $client = new Client();
        $response = $client->get("http://ip-api.com/json/$ip");
        $data = json_decode($response->getBody(), true);

        return [
            'city' => $data['city'] ?? 'Unknown',
            'country' => $data['country'] ?? 'Unknown'
        ];
    }

    private function getTemperature($city)
    {
        $client = new Client();
        $apiKey = '0f4a23c9847e4f23900165432240207'; // Use the provided API key
        $response = $client->get("http://api.weatherapi.com/v1/current.json?key=$apiKey&q=$city");
        $data = json_decode($response->getBody(), true);

        return $data['current']['temp_c'] ?? 'Unknown';
    }
}
