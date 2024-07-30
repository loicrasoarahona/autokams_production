<?php

namespace App\Service;

use DateTime;
use Exception;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Validator\Constraints\Date;

class MeteoService
{
    public function __construct()
    {
        // $dotenv = Dotenv::createImmutable(__DIR__);
        // $dotenv->load();
    }

    static $appelDemander = 0;

    public function revalidate($date)
    {
        // supprimer l'ancien fichier
        $dateDate = new DateTime($date);
        $dir = $dateDate->format('Y-m');
        $dir = __DIR__ . "/../../public/meteo/" . $dir;
        if (file_exists($dir)) {
            $fileList = scandir($dir);
            foreach ($fileList as $file) {
                $firstSplit = explode(".", $file);
                if (count($firstSplit) >= 2 && $firstSplit[1] == "json") {
                    $secondSplit = explode(" ", $firstSplit[0]);
                    if (count($secondSplit) == 2) {
                        $date1 = new DateTime($secondSplit[0]);
                        $date2 = new DateTime($secondSplit[1]);
                        if ($dateDate >= $date1 && $dateDate <= $date2) {
                            $filePath = $dir . "/" . $file;
                            if (file_exists($filePath))
                                unlink($filePath);
                            break;
                        }
                    } else {
                        continue;
                    }
                }
            }
        }

        return $this->getMeteoSemaine($date);
    }

    public function getMeteoSemaine($date, $fetchOnTheInternet = 1)
    {
        // la date est forcement un lundi
        // rechercher cette date dans les fichiers
        $dateDate = new DateTime($date);
        $mois = $dateDate->format('Y-m');
        $dossier = __DIR__ . "/../../public/meteo/" . $mois;
        if (file_exists($dossier)) {
            $fileList = scandir($dossier);
            foreach ($fileList as $file) {
                $file = str_replace(".", "$", $file);
                $firstSplit = explode("$", $file);
                if (count($firstSplit) >= 2 && $firstSplit[1] == "json") {
                    $secondSplit = explode(" ", $firstSplit[0]);
                    if (count($secondSplit) == 2) {
                        $date1 = new DateTime($secondSplit[0]);
                        $date2 = new DateTime($secondSplit[1]);
                        if ($dateDate >= $date1 && $dateDate <= $date2) {
                            $fichier = __DIR__ . ("/../../public/meteo/" . $mois . "/" . str_replace("$", ".", $file));
                            $fileHandle = fopen($fichier, 'r');
                            $lines = fread($fileHandle, filesize($fichier));
                            fclose($fileHandle);

                            // télécharger l'icone sur internet
                            try {
                                $normalized = json_decode($lines);
                                foreach ($normalized->forecast->forecastday as $row) {
                                    $icon = $row->day->condition->icon;
                                    $icon = str_replace("//", "https://", $icon);
                                    $this->telechargerIcone($icon);
                                }
                            } catch (\Throwable $th) {
                                //throw $th;
                            }
                            //$$

                            return $lines;
                        }
                    } else {
                        continue;
                    }
                }
            }
        }
        // si un fichier correspond à cette date, la retourner
        // sinon, rechercher sur le web et enregistrer dans les fichiers, et recommencer la fonction
        if ($fetchOnTheInternet)
            $this->demanderMeteo($date, 7);
        else {
            throw new Exception("Impossible");
        }
        return $this->getMeteoSemaine($date, 0);
    }

    function getListeGenerations($mois)
    {
        $dir = __DIR__ . "/../../public/meteo/" . $mois;
        if (!file_exists($dir)) {
            return [];
        }
        $fileList = scandir($dir);
        $retour = [];
        foreach ($fileList as $row) {
            if (str_ends_with($row, "json")) {
                array_push($retour, $row);
            }
        }
        return $retour;
    }

    function getByFileName($fileName)
    {
        if (!str_ends_with($fileName, "json")) {
            throw new Exception("invalid filename");
        }

        $split = explode(" ", $fileName);
        $date = new DateTime($split[0]);
        $mois = $date->format('Y-m');
        $dir = __DIR__ . "/../../public/meteo/" . $mois;
        $filePath = $dir . "/" . $fileName;

        $fileHandle = fopen($filePath, 'r');
        $lines = fread($fileHandle, filesize($filePath));
        fclose($fileHandle);
        return $lines;
    }

    function getForecastADay(DateTime $date)
    {
        $key = $_ENV['METEO_KEY'];
        $location = $_ENV['METEO_LOCATION'];
        $url = $_ENV['METEO_FORECAST_URL'];
        $dt = $date->format('Y-m-d');
        $dateNow = new DateTime();
        if ($date < $dateNow) {
            $url = $_ENV['METEO_HISTORY_URL'];
        }
        $httpClient = HttpClient::create();

        $response = $httpClient->request('GET', $url . "?key=" . $key . "&q=" . $location . "&dt=" . $dt);
        $content = $response->getContent();
        $denormalized = json_decode($content);
        if (!empty($denormalized->forecast->forecastday[0])) {
            return $denormalized->forecast->forecastday[0];
        }

        throw new Exception("L'entité obtenu n'est pas traitable (getForecastADay)");
    }

    function demanderMeteo($dateDebut, $durreeJour)
    {
        $dateLundiNow = (new DateTime())->modify("this week monday");
        $dateLundiLimite = (new DateTime($dateLundiNow->format('Y-m-d')))->modify("+14 days");

        // transformer la date en lundi
        $date1 = new DateTime($dateDebut);
        $date1->modify('this week monday');
        if ($date1 >= $dateLundiLimite) {
            throw new Exception("Aucune prévision n'est disponible à cette date");
        }

        $key = $_ENV['METEO_KEY'];
        $location = $_ENV['METEO_LOCATION'];
        $url = $_ENV['METEO_FORECAST_URL'];


        // si la semaine est passée
        if ($date1 < $dateLundiNow) {
            // tsotra
            $date2 = new DateTime($date1->format('Y-m-d'));
            $date2->modify('this week sunday');

            $url = $_ENV['METEO_HISTORY_URL'];
            $dt = $date1->format('Y-m-d');
            $end_dt = $date2->format('Y-m-d');
            $httpClient = HttpClient::create();

            $reponse = $httpClient->request('GET', $url . "?key=" . $key . "&q=" . $location . "&dt=" . $dt . "&end_dt=" . $end_dt);
            $content = $reponse->getContent();


            $mois = $date1->format('Y-m');
            $filename = $date1->format('Y-m-d');

            $date1->modify('+' . ($durreeJour - 1) . ' days');
            $filename .= " " . $date1->format('Y-m-d') . ".json";


            $dossier = __DIR__ . '/../../public/meteo/' . $mois;
            if (!file_exists($dossier)) {
                mkdir($dossier, 0777, true);
            }

            $fichier = $dossier . "/" . $filename;
            file_put_contents($fichier, $content);
        }
        // si la semaine est cette semaine
        // si la semaine est future
        if ($date1 >= $dateLundiNow) {
            $dateDebut = new DateTime($date1->format('Y-m-d'));
            // jerena tsirairay, àry atambatra
            $retour = ["location" => ["localtime" => (new DateTime())->format('Y-m-d H:i:s')], "forecast" => ["forecastday" => []]];

            $date2 = (new DateTime($date1->format('Y-m-d')))->modify('this week sunday');
            while ($date1 <= $date2) {

                $forecast = $this->getForecastADay($date1);
                array_push($retour["forecast"]["forecastday"], $forecast);

                $date1->modify("+1 day");
            }
            $serialized = json_encode($retour);
            $mois = $dateDebut->format('Y-m');
            $filename = $dateDebut->format('Y-m-d');

            $dateDebut->modify('+' . ($durreeJour - 1) . ' days');
            $filename .= " " . $dateDebut->format('Y-m-d') . ".json";


            $dossier = __DIR__ . '/../../public/meteo/' . $mois;
            if (!file_exists($dossier)) {
                mkdir($dossier, 0777, true);
            }

            $fichier = $dossier . "/" . $filename;
            file_put_contents($fichier, $serialized);
        }



        return;


        try {
            $reponse = $httpClient->request('GET', $url . "?key=" . $key . "&q=" . $location . "&days=" . $durreeJour);

            $content = $reponse->getContent();


            $mois = $date1->format('Y-m');
            $filename = $date1->format('Y-m-d');

            $date1->modify('+' . ($durreeJour - 1) . ' days');
            $filename .= " " . $date1->format('Y-m-d') . ".json";


            $dossier = __DIR__ . '/../../public/meteo/' . $mois;
            if (!file_exists($dossier)) {
                mkdir($dossier, 0777, true);
            }

            $fichier = $dossier . "/" . $filename;
            file_put_contents($fichier, $content);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    function telechargerIcone($url)
    {
        // vérifier que l'image n'existe pas déjà

        try {
            $split = explode("/", $url);
            $count = count($split);
            $fileName = $split[$count - 1];

            $chemin = __DIR__ . "/../../public/meteo/icons";
            if (!file_exists($chemin)) {
                mkdir($chemin);
            }

            $path = $chemin . "/" . $fileName;

            if (!file_exists($path))
                file_put_contents($path, file_get_contents($url));
        } catch (\Throwable $th) {
        }
    }
}
