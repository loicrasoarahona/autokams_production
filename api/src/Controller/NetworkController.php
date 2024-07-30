<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class NetworkController extends AbstractController
{

    #[Route('/network/wlanIPV4', methods: ['GET'])]
    public function getWifiNetworkIpv4()
    {
        // // Get the network interface name for the WiFi network
        // $wifiInterface = 'wlan0'; // Replace with the actual interface name

        // // Get the IPv4 address of the WiFi network
        // $ipv4Address = '';

        // $output = shell_exec("ip -4 addr show $wifiInterface");
        // if (preg_match('/inet (\d+\.\d+\.\d+\.\d+)/', $output, $matches)) {
        //     $ipv4Address = $matches[1];
        // }

        // return new JsonResponse($ipv4Address);

        // $ip_address = $_SERVER['SERVER_ADDR'];S

        $ifconfig_output = shell_exec('/sbin/ifconfig');

        dd($ifconfig_output);

        // Recherche de l'adresse IPv4 associée à l'interface WiFi
        preg_match('/en0:?\s+inet\s+([0-9.]+)/', $ifconfig_output, $matches);

        if (isset($matches[1])) {
            $ipv4_address = $matches[1];
            dd($ipv4_address);
            echo "Adresse IPv4 du réseau WiFi : $ipv4_address";
        } else {
            echo "Impossible de récupérer l'adresse IPv4 du réseau WiFi.";
        }
    }
}
